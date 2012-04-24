# -*- coding: utf-8 -*-

# Main imports
import math
# External libraries
from externals.progressbar import ProgressBar, Percentage, Bar, ETA
# Trueskill module is loaded dynamically

""" Class: Trueskills """
class Trueskills:
    trueskillModuleName = "internals.trueskill.trueskill"
    initial_mu = 500
    initial_sigma = initial_mu / 3.0

    """ Init Trueskills """
    def Main(self, dbc):
        # Localize dbc
        self.dbc = dbc

        # Try to load the trueskill module
        self.trueskillModule = loadModule(self.trueskillModuleName, "TrueSkill module cannot be loaded.")
        if self.trueskillModule == None:
            return
        # ... and initialize it
        self.trueskillModule.INITIAL_MU = self.initial_mu
        self.trueskillModule.INITIAL_SIGMA = self.initial_sigma
        self.trueskillModule.SetParameters()

        # Update TrueSkills -----------------------------------
        #self.dbc.execute("""SELECT MAX(g) FROM (SELECT GREATEST(COALESCE(p.`player_first_game_id`, 0), COALESCE(t.`gid`,0)) AS g FROM `players` p LEFT JOIN (SELECT MAX(`trueskill_game_id`) AS gid, trueskill_player_id FROM `trueskill`) AS t ON p.`player_id` = t.`trueskill_player_id`) AS t1""");
        # Find the least game which is missing trueskill stats.
        self.dbc.execute("""SELECT MIN(stats_game_id) FROM per_game_stats p WHERE NOT EXISTS (SELECT * FROM trueskill t WHERE p.stats_player_id = t.trueskill_player_id AND p.stats_game_id = t.trueskill_game_id)""");
        result = self.dbc.fetchone()
        ts_last_game = result[0]

        self.dbc.execute("""SELECT game_id, game_length, game_winner FROM games WHERE game_id > %s""", ts_last_game);
        games = self.dbc.fetchall();
        print "Last TrueSkills game %s, deleting newer stats and recomputing %s games." % (ts_last_game, len(games));

        self.dbc.execute("""DELETE FROM `trueskill` WHERE `trueskill_game_id` > %s""", ts_last_game);

        progress(games, lambda row: self.trueskillStats(self.dbc, row[0], totalSeconds(row[1]), row[2] == 'aliens', row[2] == 'humans'))


    def trueskillStats(self, dbc, game_id, game_time, asWon, hsWon):
        #print "--- Updating stats for game %s that took %s (humans won: %s)." % (game_id, game_time, hs)
        halfgame = game_time / 2
        # For each player select the last computed skill before the given game
        players = []
        self.dbc.execute("""
            SELECT p.stats_player_id, p.stats_time_alien, p.stats_time_human,
                COALESCE(t.trueskill_mu, %s) AS mu, COALESCE(t.trueskill_sigma, %s) AS sigma,
                COALESCE(t.trueskill_alien_mu, %s) AS mu_a, COALESCE(t.trueskill_alien_sigma, %s) AS sigma_a,
                COALESCE(t.trueskill_human_mu, %s) AS mu_h, COALESCE(t.trueskill_human_sigma, %s) AS sigma_h
            FROM per_game_stats p
              LEFT JOIN trueskill t ON t.trueskill_game_id IN (SELECT MAX(s.trueskill_game_id) FROM trueskill s WHERE s.trueskill_player_id = t.trueskill_player_id AND s.trueskill_game_id < %s) AND t.trueskill_player_id = p.stats_player_id
              WHERE p.stats_game_id = %s
            """, (self.initial_mu, self.initial_sigma,
                    self.initial_mu, self.initial_sigma,
                    self.initial_mu, self.initial_sigma,
                    game_id, game_id));
        # self.dbc.execute("""SELECT ... FROM per_game_stats p WHERE p.stats_game_id = %s""", game_id);
        for row in self.dbc.fetchall():
            player = Player( Skill(row[3], row[4])
                           , Skill(row[5], row[6])
                           , Skill(row[7], row[8])
                           )
            player.id = row[0]
            if row[1] > halfgame: # Alien at least 1/2 of the game time.
                player.team = player.alien
                if asWon:
                    player.rank(1)
                else:
                    player.rank(2)
            elif row[2] > halfgame: # Human at least 1/2 of the game time.
                player.team = player.human
                if hsWon:
                    player.rank(1)
                else:
                    player.rank(2)
            else:
                continue # disregard this player - didn't play long enough
            players.append(player)

        if (len(players) < 2):
            return # not enough players
        # Perform the computation
        try:
            # Adjust the overall skill:
            self.trueskillModule.AdjustPlayers(map(lambda p: p.total, players))
            # Adjust the skill corresponding to the team each player was in:
            self.trueskillModule.AdjustPlayers(map(lambda p: p.team, players))
        except Exception as e:
            print "Recomputation for game %s failed, please report to the develper.\n%s" % (game_id, e)
        # Update the database
        for player in players:
            #print "Player %s with skill %s/%s and rank %s." % (player.id, player.skill[0], player.skill[1], player.rank)
            self.dbc.execute("""INSERT INTO `trueskill`
              (`trueskill_player_id`, `trueskill_game_id`,
               `trueskill_mu`, `trueskill_sigma`,
               `trueskill_alien_mu`, `trueskill_alien_sigma`,
               `trueskill_human_mu`, `trueskill_human_sigma`)
              VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""",
              (player.id, game_id, 
                  player.total.skill[0], player.total.skill[1],
                  player.alien.skill[0], player.alien.skill[1],
                  player.human.skill[0], player.human.skill[1] )
            )

def loadModule(name, msg):
    try:
        return __import__(name, fromlist='*')
    except ImportError, e:
        print "%s\n%s" % (msg, str(e.args))
        return None

def totalSeconds(td):
    return td.seconds + td.days * 24 * 3600

def progress(lst, fn):
    # Start the progressbar
    l = len(lst)
    try:
        pbar = ProgressBar(l, [Percentage(), ' ', Bar(), ' ', ETA()]).start()
    except:
        pbar = None
    i = 0

    for row in lst:
        fn(row)
        i = i + 1
        if pbar != None:
            try:
                pbar.update(i)
            except:
                pass

    if pbar != None:
        try:
            pbar.finish()
        except:
            pass


class Player(object):
    def __init__(self, total, alien, human):
        self.id = None
        self.total = total
        self.alien = alien
        self.human = human
    def rank(self, r):
        self.total.rank = r
        self.alien.rank = r
        self.human.rank = r

class Skill(object):
    def __init__(self, mu, sigma):
        self.skill = (mu, sigma)
        self.rank = None

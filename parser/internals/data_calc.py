# -*- coding: utf-8 -*-

# Main imports
import math

# External libraries
from externals.progressbar import ProgressBar, Percentage, Bar, ETA

""" Class: Calculator """
class Calculator:
	""" Init Calculator """
	def Main(self, dbc, players_to_update):
		# Localize dbc
		self.dbc = dbc

		# Here we do calculations, which are to heavy for real-time
		print "Calculating data ..."

		# Update the game time factor and total says
		print " - Calculating game time factor"
		self.dbc.execute("""UPDATE `players` SET
		                    `player_game_time_factor` = POW(
		                                                   SQRT(`player_games_played`)
		                                                   /
		                                                   (
		                                                     SQRT(
		                                                           (
		                                                             SELECT COUNT(*)
		                                                             FROM `games`
		                                                             WHERE `game_id` >= `player_first_game_id`
		                                                                   AND `game_winner` != 'undefined'
		                                                           )
		                                                         )
		                                                   ), 2
		                                                   )""")
		# note: removed from above to slightly speed up nightly parsing - value is unused
		#                    `player_total_says`       = (SELECT COUNT(*) FROM `says` WHERE `say_player_id` = `player_id`),

		# Calculate efficiencies
		print " - Calculating kill- and destruction-efficiency"

		player_count = len(players_to_update)

		# Start the progressbar
		try:
			pbar = ProgressBar(player_count, [Percentage(), ' ', Bar(), ' ', ETA()]).start()
		except:
			pbar = None

		# Iterate the stack
		while len(players_to_update) > 0:
			player_id = players_to_update.pop()

			if player_id == 0:
				continue

			# Calculate kill- and destruction efficiency
			self.dbc.execute("""UPDATE players SET
			                    `player_kill_efficiency` = (
			                      `player_total_kills` -
			                      `player_total_teamkills` -
			                      (`player_deaths_by_world` / 10)
			                    ) / (`player_deaths_by_enemy` + 1),
			                    
			                    `player_destruction_efficiency` = IFNULL((
			                      SELECT SUM(`building_efficiency_multiplier`)
			                      FROM `buildings`
			                      LEFT JOIN `destructions`
			                             ON `destruct_building_id` = `building_id`
			                            AND `destruct_player_id` = %s
			                      WHERE `destruct_id` IS NOT NULL
			                    ) / ((SELECT COUNT(*) FROM `destructions` WHERE `destruct_player_id` = %s) + 1), 0)
			                    WHERE `player_id` = %s
			                 """, (player_id, player_id, player_id))

			# Update the progressbar
			if pbar != None:
				try:
					pbar.update(player_count - len(players_to_update))
				except:
					pass

		# Finish the progressbar
		if pbar != None:
			try:
				pbar.finish()
			except:
				pass

		# Calculate total efficiency
		print " - Calculating total-efficiency"
		self.dbc.execute("""UPDATE `players` SET
		                      `player_total_efficiency` = (`player_kill_efficiency` + 0.1)
		                                                * (`player_destruction_efficiency` + 0.1)
                            """)

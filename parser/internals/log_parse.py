# -*- coding: utf-8 -*-

# Main imports
import sys, os, re
import time, datetime
import shutil

# External libraries
from externals.progressbar import ProgressBar, Percentage, Bar, ETA

# Config
from config import CONFIG

""" Class: Parser """
class Parser:
	""" Init Parser """
	def Main(self, dbc, Check_map_in_database, Add_player_to_update, games_log):
		# Regular expressions
					# MM:SS LOGTYPE: data
		self.RE_LOGTYPE       = re.compile("^[ ]*([0-9]+):([0-9]{2}) ([^:]+):[ ]*")

		self.RE_GAMETIME      = re.compile("([0-9]+):([0-9]{2}):([0-9]{2})")

		self.RE_UNCOLOR_NAME  = re.compile("\\^[^\\^]")

					# ClientConnect: ID [IP] (GUID) "NAME" "COLORNAME"
		self.RE_CONNECT      = re.compile("^([0-9]+) \\[([^\\]]+)\\] \\(([^\\)]+)\\) \"([^\"]*)\"(?: \")?([^\"]+)?\"?$")

					# ClientDisconnect: ID [ip] (guid) "name"
		self.RE_DISCONNECT   = re.compile("^([0-9]+) \\[[^\\]]+\\] \\([^\\)]+\\) \"[^\"]+\"$")

					# ClientRename: ID [ip] (GUID) "oldname" -> "NEWNAME" "COLORNAME"
		self.RE_RENAME       = re.compile("^([0-9]+) \\[[^\\]]+\\] \\(([^\\)]+)\\) \"[^\"]+\" -> \"([^\"]+)\"(?: \")?([^\"]+)?\"?$")

					# ChangeTeam: ID TEAMNAME: NAME switched teams
		self.RE_CHANGETEAM   = re.compile("^([0-9]+) (alien|human|spectator): (.+)$")

					# Stage: T STAGE: Team reached Stage stage
		self.RE_STAGE        = re.compile("^(A|H) ([2-3]): (Aliens|Humans) reached Stage ([2-3])?$")

					# Beginning Sudden Death
		self.RE_SUDDENDEATH  = re.compile("^[ ]*([0-9]+):([0-9]{2}) Beginning Sudden Death")

					# Exit: RESULT MISC
		self.RE_EXIT         = re.compile("^(Aliens|Humans|Timelimit|Evacuation).*\.$")

					# score: SCORE  ping: PING  client: ID NAME
		self.RE_SCORE        = re.compile("^([0-9]+)  ping: ([0-9]+)  client: ([0-9]+) (.+)$")

					# Say: ID "NAME": MESSAGE
		self.RE_SAY          = re.compile("^([0-9]+) \"([^\"]*)\": (.+)$")

					# Die: KILLERId VICTIMId MOD: killername killed victimname
		self.RE_DIE          = re.compile("^([0-9]+) ([0-9]+) ([^:]+): .+ killed .+$")

					# Deconstruct: ID entitynum BUILDING MOD: buildingname action by name
		self.RE_DECON        = re.compile("^([0-9]+) [0-9]+ ([^ ]+) ([^:]+): .+ (deconstructed|destroyed) by .+$")

					# Construct: ID entitynum BUILDING entityreplacelist: name is building buildmesssage
		self.RE_BUILD        = re.compile("^([0-9]+) [0-9]+ ([a-zA-Z_]+)[0-9 ]*?: .+ is building .+$")

					# RealTime: YYYY/MM/DD HH:MM:SS
		self.RE_REALTIME     = re.compile("^([0-9]+)/([0-9]+)/([0-9]+) ([0-9]+):([0-9]+):([0-9]+)$")

					# CallVote: ID "name": VOTETYPE VOTEDATA
		self.RE_VOTE         = re.compile("^([0-9]+) \"[^\"]*\": ([a-zA-Z0-9_]+)[ \"]*([^\" ]*)[ \"]*([^\"]*).*$");

					# EndVote: TEAM pass|fail YESCOUNT NOCOUNT MAXVOTERS
		self.RE_ENDVOTE      = re.compile("^(global|alien|human) (pass|fail) ([0-9]+) ([0-9]+) ([0-9]+)$");

		# Localize parents function
		self.Check_map_in_database = Check_map_in_database
		self.Add_player_to_update  = Add_player_to_update

		# Internal datas
		self.dbc                 = dbc
		self.games_log           = games_log

		# initialize game
		self.Clear_game()

		# Collect data
		self.Get_weapon_ids()
		self.Get_building_ids()

		# Read the logfile
		result = self.Log_read()
		return result

	""" clear game state """
	def Clear_game(self):
		self.game_id             = None
		self.game_map_id         = None
		self.game_timestamp      = None
		self.game_sudden_death   = None
		self.game_stage_alien2   = None
		self.game_stage_alien3   = None
		self.game_stage_human2   = None
		self.game_stage_human3   = None
		self.game_alien_kills    = 0
		self.game_human_kills    = 0
		self.game_alien_deaths   = 0
		self.game_human_deaths   = 0
		self.game_exit           = None
		self.game_exit_time      = None
		self.players             = {}
		self.game_players        = {}
		self.vote                = {}

	""" Get weapon ids """
	def Get_weapon_ids(self):
		self.weapons       = {}
		self.weapons_teams = {}
		self.dbc.execute("SELECT `weapon_id`, `weapon_constant`, `weapon_team` FROM `weapons`")
		rows = self.dbc.fetchall()
		for row in rows:
			self.weapons[row[1]] = row[0]
			self.weapons_teams[row[0]] = row[2]

	""" Get building ids """
	def Get_building_ids(self):
		self.buildings = {}
		self.dbc.execute("SELECT `building_id`, `building_constant` FROM `buildings`")
		rows = self.dbc.fetchall()
		for row in rows:
			self.buildings[row[1]] = row[0]

	""" Remove colors from string """
	def Remove_colors(self, string):
		string = re.sub(self.RE_UNCOLOR_NAME, '', string)
		return string

	def Player_is_unnamed(self, name):
		for part in CONFIG['UNNAMED_PLAYER']:
			if name.find(part) != -1:
				return True
		return False

	""" Get data of registered player """
	def Get_player_data_by_name(self, player_name):
		# Correct the player's name if needed
		uncolored_name = self.Remove_colors(player_name)

		# Lookup the internal list for player's name
		for player_id in self.players:
			if self.players[player_id]['name_uncolored'] == uncolored_name:
				return self.players[player_id]

		# No player found, return None
		return None

	""" Update a player time count """
	def Update_player_time(self, player_id, gametime):
		if not self.players.has_key(player_id):
			return

		mysql_id = self.players[player_id]['id']
		if not self.game_players.has_key(mysql_id):
			return

		t = self.Gametime_seconds( gametime )
		if self.players[player_id]['team_time'] != None:
			s = t - self.players[player_id]['team_time']
			if self.players[player_id]['team'] == 'alien':
				self.game_players[mysql_id]['time_alien'] += s
			elif self.players[player_id]['team'] == 'human':
				self.game_players[mysql_id]['time_human'] += s
			else:
				self.game_players[mysql_id]['time_spec'] += s

		self.players[player_id]['team_time'] = t

	""" update all player time counts """
	def Update_all_player_time(self, gametime):
		for player_id in self.players:
			self.Update_player_time(player_id, gametime)

	""" Build a gametime string """
	def Build_gametime(self, hours, minutes, seconds):
		if minutes > 59:
			hours = minutes / 60
			minutes = minutes - hours * 60

		return "%02d:%02d:%02d" % (hours, minutes, seconds)

	""" Build a gametime string """
	def Gametime_seconds(self, gametime):
		match = self.RE_GAMETIME.search(gametime)
		if match == None:
			return 0

		result = match.groups()
		h = int(result[0])
		m = int(result[1])
		s = int(result[2])
		s += ((h * 60) + m) * 60

		return s

	""" Start reading from games.log """
	def Log_read(self):
		logstart = 0
		logstop = 0
		oldlength = 0
		lines = 0

		source = self.games_log
		filesize = os.path.getsize(source)

		# run through log file to find start and end positions

		print "Looking for start and stop position within log file..."

		self.dbc.execute("SELECT `log_offset`, `log_filesize` FROM `state` WHERE `log_id` = 0")
		result = self.dbc.fetchone()
		if result != None:
			logstart = result[0]
			oldlength = result[1]
			print "previous run stopped parsing at offset", logstart, "with length", oldlength

		if oldlength > filesize:
			print "log file offset reset to zero due to truncated (rotated?) log file"
			logstart = 0

		# Start the progressbar
		try:
			pbar = ProgressBar(filesize - logstart, [Percentage(), ' ', Bar(), ' ', ETA()]).start()
			sizedone = 0
		except:
			pbar = None

		# Read the file line per line
		log = open(source, 'r')
		log.seek(logstart);
		
		while True:
			line = log.readline()
			
			if not line:
				break

			if line.find(" ShutdownGame:") != -1:
				logstop = log.tell()

			if pbar != None:
				sizedone += len(line)
				try:
					pbar.update(sizedone)
				except:
					pass

		log.close()

		# Finish the progressbar
		if pbar != None:
			try:
				pbar.finish()
			except:
				pass

		if logstop < logstart:
			logstop = logstart
			print "nothing new to parse."
			return None

		print "log parse offsets are", logstart, "to", logstop, "(", logstop - logstart, "bytes )"

		# Everything OK, parse the file to the database
		print "Parsing logfile ..."

		# Start the progressbar
		try:
			pbar = ProgressBar(filesize - logstart, [Percentage(), ' ', Bar(), ' ', ETA()]).start()
			sizedone = 0
		except:
			pbar = None

		# Read the file line per line
		log = open(source, 'r')
		log.seek(logstart)
		
		while True:
			# Get current line
			line = log.readline()
			
			# If line is EOF, break
			if not line:
				break

			# If end of parsing, break
			if log.tell() > logstop:
				break

			# Truncate the line
			if pbar != None:
				sizedone += len(line)

			line = line[:-1]
			lines += 1
			
			# Parse the line
			self.Log_parse_line(line)

			# Update the progressbar
			if pbar != None:
				try:
					pbar.update(sizedone)
				except:
					pass

		log.close()

		# Finish the progressbar
		if pbar != None:
			try:
				pbar.finish()
			except:
				pass

		# Store log stop point, to use as start on next run
		self.dbc.execute("SELECT `log_id` FROM `state` WHERE `log_id` = 0")
		result = self.dbc.fetchone()
		if result == None:
 			self.dbc.execute("INSERT INTO `state` (`log_id`, `log_offset`, `log_filesize`, `log_runcount`) VALUES (%s, %s, %s, %s)", (0, logstop, filesize, 1))
		else:
			dt = datetime.datetime.now()
			stamp = dt.isoformat()
			self.dbc.execute("UPDATE `state` SET `log_runcount` = `log_runcount` + 1, `log_offset` = %s, `log_filesize` = %s, `log_timestamp` = %s WHERE `log_id` = 0", (logstop, filesize, stamp))

		return lines

	""" Parse a line of the log """
	def Log_parse_line(self, line):
		# Get the logtype
		match = self.RE_LOGTYPE.search(line)
		if match == None:
			# If there is no game-id yet, return now
			if self.game_id == None:
				return

			match = self.RE_SUDDENDEATH.search(line)
			if match != None:
				result = match.groups()
				gametime = self.Build_gametime(0, int(result[0]), int(result[1]))
				self.game_sudden_death = gametime

			return

		# The line is ok, truncate it
		result = match.groups()
		gametime = self.Build_gametime(0, int(result[0]), int(result[1]))
		logtype  = result[2]
		line = re.sub(self.RE_LOGTYPE, '', line)

		if logtype == 'InitGame':
			self.Log_InitGame(gametime, line)

		# If there is no game-id yet, return now
		if self.game_id == None:
			return

		# Check for a log match, in order of chances to occur
		if logtype == 'Die':
			self.Log_Die(gametime, line)
		elif logtype == 'Construct':
			self.Log_Build(gametime, line)
		elif logtype == 'Deconstruct':
			self.Log_Decon(gametime, line)

		elif logtype == 'Say':
			self.Log_Say(gametime, 'public', line)
		elif logtype == 'SayTeam':
			self.Log_Say(gametime, 'team', line)

		elif logtype == 'ClientConnect':
			self.Log_ClientConnect(gametime, line)
		elif logtype == 'ClientDisconnect':
			self.Log_ClientDisconnect(gametime, line)
		elif logtype == 'ChangeTeam':
			self.Log_ClientTeamClass(gametime, line)
		elif logtype == 'ClientRename':
			self.Log_ClientRename(gametime, line)
		elif logtype == 'Stage':
			self.Log_Stage(gametime, line)
		elif logtype == 'CallVote':
			self.Log_Vote(gametime, line, None)
		elif logtype == 'CallTeamVote':
			self.Log_Vote(gametime, line, 'team')
		elif logtype == 'EndVote':
			self.Log_EndVote(gametime, line)

		elif logtype == 'RealTime':
			self.Log_RealTime(gametime, line)
		elif logtype == 'Exit':
			self.Log_Exit(gametime, line)
		elif logtype == 'ShutdownGame':
			self.Log_Shutdown(gametime, line)
		elif logtype == 'score':
			self.Log_Score(gametime, line)
	
	""" Log played games for each player """
	def Log_PlayerUpdate(self):
		for player_id in self.game_players:
			if player_id > 0:
				self.Log_PlayerSingleUpdate(player_id, self.game_players[player_id])

	""" Reset a player's counts """
	def Player_Reset(self, player_mysql_id, joins):
		self.game_players[player_mysql_id] = {'joins': joins,
		  'kills': 0, 'kills_alien':0, 'kills_human':0,
		  'teamkills': 0, 'teamkills_alien':0, 'teamkills_human':0,
		  'deaths': 0, 'deaths_enemy':0,
		  'deaths_world_alien':0, 'deaths_world_human':0,
		  'deaths_team_alien':0, 'deaths_team_human':0,
		  'deaths_enemy_alien':0, 'deaths_enemy_human':0,
		  'score': 0,
		  'time_alien': 0, 'time_human': 0, 'time_spec': 0}

	""" Update a single player """
	def Log_PlayerSingleUpdate(self, player_id, player):
		self.dbc.execute("""UPDATE `players` SET
		                      `player_games_played`    = `player_games_played` + IF (%s = 1, 1, 0),
		                      `player_kills`           = `player_kills` + %s,
		                      `player_kills_alien`         = `player_kills_alien` + %s,
		                      `player_kills_human`         = `player_kills_human` + %s,
		                      `player_teamkills`       = `player_teamkills` + %s,
		                      `player_teamkills_alien`     = `player_teamkills_alien` + %s,
		                      `player_teamkills_human`     = `player_teamkills_human` + %s,
		                      `player_deaths`          = `player_deaths` + %s,
		                      `player_deaths_enemy`    = `player_deaths_enemy` + %s,
		                      `player_deaths_enemy_alien`  = `player_deaths_enemy_alien` + %s,
		                      `player_deaths_enemy_human`  = `player_deaths_enemy_human` + %s,
		                      `player_deaths_team_alien`   = `player_deaths_team_alien` + %s,
		                      `player_deaths_team_human`   = `player_deaths_team_human` + %s,
		                      `player_deaths_world_alien`  = `player_deaths_world_alien` + %s,
		                      `player_deaths_world_human`  = `player_deaths_world_human` + %s,
		                      `player_time_spec`       = `player_time_spec` + %s,
		                      `player_time_alien`      = `player_time_alien` + %s,
		                      `player_time_human`      = `player_time_human` + %s,
		                      `player_score_total`     = `player_score_total` + %s
		                    WHERE player_id = %s""",
		                    (player['joins'],
		                     player['kills'],
		                     player['kills_alien'],
		                     player['kills_human'],
		                     player['teamkills'],
		                     player['teamkills_alien'],
		                     player['teamkills_human'],
		                     player['deaths'],
		                     player['deaths_enemy'],
		                     player['deaths_enemy_alien'],
		                     player['deaths_enemy_human'],
		                     player['deaths_team_alien'],
		                     player['deaths_team_human'],
		                     player['deaths_world_alien'],
		                     player['deaths_world_human'],
		                     player['time_spec'],
		                     player['time_alien'],
		                     player['time_human'],
		                     player['score'],
		                     player_id))
		self.dbc.execute("""INSERT INTO `per_game_stats`
		  (`stats_player_id`, `stats_game_id`,
		   `stats_kills`, `stats_teamkills`, `stats_deaths`, `stats_score`,
		   `stats_time_spec`, `stats_time_alien`, `stats_time_human`)
		  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)""",
		  (player_id, self.game_id,
		   player['kills'], player['teamkills'], player['deaths'], player['score'],
		   player['time_spec'], player['time_alien'], player['time_human']))

		self.Player_Reset(player_id, player['joins'])

		self.Add_player_to_update(player_id)

	""" A new map started """
	def Log_InitGame(self, gametime, line):
		# Reset game state
		self.Clear_game()

		# Get map name
		game_map = None

		# Check all data
		data = line.split("\\")[1:]
		if data != None:
			for i in range(0, len(data), 2):
				if data[i] == 'mapname':
					game_map = data[i+1]
					break
		# No time stamp yet, use current time
		dt = datetime.datetime.now()
		self.game_timestamp = dt.isoformat()

		# If no map-name was given, return
		if game_map == None:
			self.game_id = None
			return

		# Insert game into database
		self.game_map_id = self.Check_map_in_database(game_map)
		self.dbc.execute("INSERT INTO `games` (`game_map_id`) VALUES (%s)", (self.game_map_id))
		self.dbc.execute("SELECT LAST_INSERT_ID()")
		(self.game_id, ) = self.dbc.fetchone()

	""" Real time given """
	def Log_RealTime(self, gametime, line):
		match = self.RE_REALTIME.search(line)
		if match == None:
			return

		result = match.groups()
		dt = datetime.datetime(int(result[0]), int(result[1]), int(result[2]), int(result[3]), int(result[4]), int(result[5]))
		self.game_timestamp = dt.isoformat()

		# Update game id's timestamp
		self.dbc.execute("UPDATE `games` SET `game_timestamp` = %s WHERE `game_id` = %s", (self.game_timestamp, self.game_id))

	""" A game ends """
	def Log_Exit(self, gametime, line):
		winner = 'none'

		# Parse the line
		match = self.RE_EXIT.search(line)
		if match != None:
			# We have a winner
			result = match.groups()
			game_winner = result[0]

			if game_winner == 'Timelimit':
				# only count ties when there was action
				if len(self.players) > 0 and self.game_alien_kills + self.game_human_kills + self.game_alien_deaths + self.game_human_deaths > 0:
					winner = 'tie'
			elif game_winner == 'Evacuation':
				winner = 'draw'
			elif game_winner == 'Aliens':
				winner = 'aliens'
			elif game_winner == 'Humans':
				winner = 'humans'

		self.game_exit = winner
		self.game_exit_time = gametime
		self.Update_all_player_time(gametime)

	""" Map has ended """
	def Log_Shutdown(self, gametime, line):
		if self.game_exit_time == None:
			self.game_exit = 'none'
			self.game_exit_time = gametime
			self.Update_all_player_time(gametime)

		# Update statistics for players
		self.Log_PlayerUpdate()

		self.dbc.execute("""UPDATE `games` SET
		                         `game_winner` = %s,
		                         `game_alien_kills` = %s,
		                         `game_human_kills` = %s,
		                         `game_alien_deaths` = %s,
		                         `game_human_deaths` = %s,
		                         `game_total_kills` = %s,
		                         `game_total_deaths` = %s,
		                         `game_length` = %s,
		                         `game_sudden_death` = %s,
		                         `game_stage_alien2` = %s,
		                         `game_stage_alien3` = %s,
		                         `game_stage_human2` = %s,
		                         `game_stage_human3` = %s
		                  WHERE `game_id` = %s""",
				  (self.game_exit,
				   self.game_alien_kills,
				   self.game_human_kills,
				   self.game_alien_deaths,
				   self.game_human_deaths,
				   self.game_alien_kills + self.game_human_kills,
				   self.game_alien_deaths + self.game_human_deaths,
				   self.game_exit_time,
				   self.game_sudden_death,
				   self.game_stage_alien2,
				   self.game_stage_alien3,
				   self.game_stage_human2,
				   self.game_stage_human3,
				   self.game_id))

		self.dbc.execute("SELECT `mapstat_id` FROM `map_stats` WHERE `mapstat_id` = %s", (self.game_map_id))
		result = self.dbc.fetchone()
		if result == None:
			self.dbc.execute("INSERT INTO `map_stats` (`mapstat_id`) VALUES (%s)", (self.game_map_id))

		self.dbc.execute("""UPDATE `map_stats` SET
		                        `mapstat_games` = `mapstat_games` + 1,
		                        `mapstat_time` = `mapstat_time` + %s,
		                        `mapstat_alien_wins` = `mapstat_alien_wins` + IF (%s = 'aliens', 1, 0),
		                        `mapstat_human_wins` = `mapstat_human_wins` + IF (%s = 'humans', 1, 0),
		                        `mapstat_ties` = `mapstat_ties` + IF (%s = 'tie', 1, 0),
		                        `mapstat_draws` = `mapstat_draws` + IF (%s = 'draw', 1, 0),
		                        `mapstat_alien_kills` = `mapstat_alien_kills` + %s,
		                        `mapstat_human_kills` = `mapstat_human_kills` + %s,
		                        `mapstat_alien_deaths` = `mapstat_alien_deaths` + %s,
		                        `mapstat_human_deaths` = `mapstat_human_deaths` + %s
		                  WHERE `mapstat_id` = %s""",
				  (self.Gametime_seconds(self.game_exit_time),
		                   self.game_exit,
		                   self.game_exit,
		                   self.game_exit,
		                   self.game_exit,
				   self.game_alien_kills,
				   self.game_human_kills,
				   self.game_alien_deaths,
				   self.game_human_deaths,
				   self.game_map_id))

		# logging for this game is done
		self.game_id = None

	""" A player score is logged """
	def Log_Score(self, gametime, line):
		# Parse the line
		match = self.RE_SCORE.search(line)
		if match == None:
			return

		result = match.groups()
		score     = result[0]
		player_id = result[2]

		if self.players.has_key(player_id):
			player_mysql_id = self.players[player_id]['id']
			if self.game_players.has_key(player_mysql_id):
				self.game_players[player_mysql_id]['score'] = score

	""" A client connects """
	def Log_ClientConnect(self, gametime, line):
		# Parse the line
		match = self.RE_CONNECT.search(line)
		if match == None:
			return

		result = match.groups()
		player_id             = result[0]
		player_ip             = result[1]
		player_qkey           = result[2]
		player_name_uncolored = self.Remove_colors(result[3])
		if result[4] != None:
			player_name   = result[4]
		else:
			player_name   = result[3]
		t = self.Gametime_seconds(gametime)

		# Check against MySQL
		self.dbc.execute("SELECT `player_id`, `player_name` FROM `players` WHERE `player_qkey` = %s", (player_qkey))
		result = self.dbc.fetchone()
		if result == None:
			# We have to insert this as new player
			self.dbc.execute("INSERT INTO `players` (`player_name`, `player_qkey`, `player_name_uncolored`, `player_first_game_id`, `player_first_gametime`, `player_last_game_id`, `player_last_gametime`) VALUES (%s, %s, %s, %s, %s, %s, %s)", (player_name, player_qkey, player_name_uncolored, self.game_id, self.game_timestamp, self.game_id, self.game_timestamp))
			self.dbc.execute("SELECT LAST_INSERT_ID()")
			result = self.dbc.fetchone()
			mysql_id = result[0]
			self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s)", (mysql_id, player_name_uncolored, player_name))
		else:
			mysql_id = result[0]
			if result[1] != player_name:
				self.dbc.execute("UPDATE `players` SET `player_name` = %s, `player_name_uncolored` = %s, `player_last_game_id` = %s, `player_last_gametime` = %s WHERE `player_id` = %s", (player_name, player_name_uncolored, self.game_id, self.game_timestamp, mysql_id))
			else:
				self.dbc.execute("UPDATE `players` SET `player_last_game_id` = %s, `player_last_gametime` = %s WHERE `player_id` = %s", (self.game_id, self.game_timestamp, mysql_id))
			self.dbc.execute("SELECT `nick_name`, `nick_id` FROM `nicks` WHERE `nick_player_id` = %s AND `nick_name_uncolored` = %s", (mysql_id, player_name_uncolored))
			result = self.dbc.fetchone()
			if result == None:
				self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s)", (mysql_id, player_name_uncolored, player_name))
			elif result[0] != player_name:
				self.dbc.execute("UPDATE `nicks` SET `nick_name` = %s WHERE `nick_id` = %s", (player_name, result[1]))

		# Add the player to internal list
		self.players[player_id] = {'name': player_name, 'name_uncolored': player_name_uncolored, 'id': mysql_id, 'qkey': player_qkey, 'ip': player_ip, 'team': 'spectator', 'team_time': t}

		# If the player wasn't in the current game, we set his gamecount up by one
		if not self.game_players.has_key(mysql_id):
			self.Player_Reset(mysql_id, 1)
		else:
			self.game_players[mysql_id]['joins'] += 1;

	""" A client disconnects """
	def Log_ClientDisconnect(self, gametime, line):
		# Parse the line
		match = self.RE_DISCONNECT.search(line)
		if match == None:
			return

		result = match.groups()
		player_id = result[0]

		# Remove the player from internal list
		if self.players.has_key(player_id):
			if self.game_exit == None:
				self.Update_player_time(player_id, gametime)

			mysql_player_id = self.players[player_id]['id']

			# If this is a known game player, update it
			# don't update till end of map so that only 1 per_game_stat is generated if they come back
			# if self.game_players.has_key(mysql_player_id):
			#	self.Log_PlayerSingleUpdate(mysql_player_id, self.game_players[mysql_player_id])

			del self.players[player_id]

	""" A client renames """
	def Log_ClientRename(self, gametime, line):
		# Parse the line
		match = self.RE_RENAME.search(line)
		if match == None:
			return

		result = match.groups()
		player_id   = result[0]
		player_qkey = result[1]
		player_name_uncolored = self.Remove_colors(result[2])
		if result[3] != None:
			player_name = result[3]
		else:
			player_name = result[2]

		if not self.players.has_key(player_id):
			return

		if self.Player_is_unnamed(player_name_uncolored):
			return

		mysql_id = self.players[player_id]['id']

		# Add the new nick for this TJW player
		if mysql_id != 0 and len(player_qkey) == 32:
			self.dbc.execute("SELECT `nick_name`, `nick_id` FROM `nicks` WHERE `nick_player_id` = %s AND `nick_name_uncolored` = %s", (mysql_id, player_name_uncolored))
			result = self.dbc.fetchone()
			if result == None:
				# Add new nick
				self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s)", (mysql_id, player_name_uncolored, player_name))
			elif result[0] != player_name:
				self.dbc.execute("UPDATE `nicks` SET `nick_name` = %s WHERE `nick_id` = %s", (player_name, result[1]))

			# Update new name
			self.dbc.execute("UPDATE `players` SET `player_name` = %s, `player_name_uncolored` = %s WHERE `player_id` = %s", (player_name, player_name_uncolored, mysql_id))

		# Rename the player in the internal list
		self.players[player_id]['name']           = player_name
		self.players[player_id]['name_uncolored'] = player_name_uncolored
		
	""" A client changes team or class """
	def Log_ClientTeamClass(self, gametime, line):
		# Parse the line
		match = self.RE_CHANGETEAM.search(line)
		if match == None:
			return

		result = match.groups()
		player_id    = result[0]
		player_team  = result[1]

		if self.players.has_key(player_id):
			self.Update_player_time(player_id, gametime)
			self.players[player_id]['team'] = player_team

	""" Someone says something """
	def Log_Say(self, gametime, mode, line):
		# Parse the line
		match = self.RE_SAY.search(line)
		if match == None:
			return

		result = match.groups()
		player_id   = result[0]
		message     = result[2]

		# Check string length
		if len(message) < 1:
			return
		if len(message) > 255:
			message = message[0:255]

		if self.players.has_key(player_id):

			if mode == 'team':
				if self.players[player_id]['team'] == 'alien':
					channel = 'alien'
				elif self.players[player_id]['team'] == 'human':
					channel = 'human'
				else:
					channel = 'spectator'
			else:
				channel = 'public'

			mysql_player_id = self.players[player_id]['id']
			if self.game_players.has_key(mysql_player_id):
				self.dbc.execute("""INSERT INTO `says` (`say_game_id`, `say_gametime`, `say_mode`, `say_player_id`, `say_message`)
				                    VALUES (%s, %s, %s, %s, %s)""", (self.game_id, gametime, channel, mysql_player_id, message))

	""" A kill was done """
	def Log_Die(self, gametime, line):
		# Parse the line
		match = self.RE_DIE.search(line)
		if match == None:
			return

		result = match.groups()
		player_source_id   = result[0]
		player_target_id   = result[1]
		weapon_constant    = result[2]

		# Check weapon
		if self.weapons.has_key(weapon_constant):
			# Weapon is known
			weapon_id = self.weapons[weapon_constant]
		else:
			# No known weapon
			return

		# Check source player
		if player_source_id == '1022':
			# The player is <world>, so we take 0 as MySQL value
			player_source_mysql_id = 0
			player_source_team     = None
		elif self.players.has_key(player_source_id):
			# The player is in the internal list
			player_source_mysql_id = self.players[player_source_id]['id']

			# Get player's team
			if self.players[player_source_id].has_key('team'):
				player_source_team = self.players[player_source_id]['team']
			else:
				player_source_team = None
		else:
			# Seems to be no valid player, return
			return

		# Check target player
		if self.players.has_key(player_target_id):
			# The player is in the internal list
			player_target_mysql_id = self.players[player_target_id]['id']

			# Get player's team
			if self.players[player_target_id].has_key('team'):
				player_target_team = self.players[player_target_id]['team']
			else:
				player_target_team = None
		else:
			# Seems to be no valid player, return
			return

		# Get kill type (kill / teamkill)
		if player_source_team == None or player_target_team == None:
			killtype = 'world'
		elif player_source_team != player_target_team:
			killtype = 'enemy'
		else:
			killtype = 'team'

		# Insert the kill into the database
		self.dbc.execute("""INSERT INTO `kills` (`kill_game_id`, `kill_gametime`, `kill_type`, `kill_source_player_id`, `kill_target_player_id`, `kill_weapon_id`)
		                    VALUES (%s, %s, %s, %s, %s, %s)""", (self.game_id, gametime, killtype, player_source_mysql_id, player_target_mysql_id, weapon_id))

		# Update the internal list
		if self.game_players.has_key(player_source_mysql_id):
			if killtype == 'team':
				self.game_players[player_source_mysql_id]['teamkills'] += 1
				if player_source_team == 'alien':
					self.game_players[player_source_mysql_id]['teamkills_alien'] += 1
				else:
					self.game_players[player_source_mysql_id]['teamkills_human'] += 1
			elif killtype == 'enemy':
				self.game_players[player_source_mysql_id]['kills'] += 1
				if player_source_team == 'alien':
					self.game_players[player_source_mysql_id]['kills_alien'] += 1
				else:
					self.game_players[player_source_mysql_id]['kills_human'] += 1

		if self.game_players.has_key(player_target_mysql_id):
			self.game_players[player_target_mysql_id]['deaths'] += 1
			if killtype == 'world':
				if player_target_team == 'alien':
					self.game_players[player_target_mysql_id]['deaths_world_alien'] += 1
				else:
					self.game_players[player_target_mysql_id]['deaths_world_human'] += 1
			elif killtype == 'team':
				if player_target_team == 'alien':
					self.game_players[player_target_mysql_id]['deaths_team_alien'] += 1
				else:
					self.game_players[player_target_mysql_id]['deaths_team_human'] += 1
			elif killtype == 'enemy':
				self.game_players[player_target_mysql_id]['deaths_enemy'] += 1
				if player_target_team == 'alien':
					self.game_players[player_target_mysql_id]['deaths_enemy_alien'] += 1
				else:
					self.game_players[player_target_mysql_id]['deaths_enemy_human'] += 1

		if player_source_team == 'alien':
			self.game_alien_kills += 1
		elif player_source_team == 'human':
			self.game_human_kills += 1

		if player_target_team == 'alien':
			self.game_alien_deaths += 1
		elif player_target_team == 'human':
			self.game_human_deaths += 1

	""" Someone built something """
	def Log_Build(self, gametime, line):
		# Parse the line
		match   = self.RE_BUILD.search(line)
		if match == None:
			return

		result = match.groups()
		player_id         = result[0]
		building_constant = result[1]

		# Check player
		if player_id == '1022':
			# The player is <world>, so we take 0 as MySQL value
			player_mysql_id = 0
		elif self.players.has_key(player_id):
			player_mysql_id = self.players[player_id]['id']
		else:
			return

		# check building
		if self.buildings.has_key(building_constant):
			building_id = self.buildings[building_constant]
		else:
			return

		self.dbc.execute("""INSERT INTO `builds` (`build_game_id`, `build_gametime`, `build_player_id`, `build_building_id`)
			                    VALUES (%s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, building_id))

	""" Someone deconed or destroyed something """
	def Log_Decon(self, gametime, line):
		# Parse the line
		match = self.RE_DECON.search(line)
		if match == None:
			return

		result = match.groups()
		player_id         = result[0]
		building_constant = result[1]
		weapon_constant   = result[2]

		# Check player
		if player_id == '1022':
			# The player is <world>, so we take 0 as MySQL value
			player_mysql_id = 0
		elif self.players.has_key(player_id):
			player_mysql_id = self.players[player_id]['id']
		else:
			return

		# check building
		if self.buildings.has_key(building_constant):
			building_id = self.buildings[building_constant]
		else:
			return

		# Check weapon
		if self.weapons.has_key(weapon_constant):
			# Weapon is known
			weapon_id = self.weapons[weapon_constant]
		else:
			# No known weapon
			return

		if weapon_constant == 'MOD_DECONSTRUCT':
			self.dbc.execute("""INSERT INTO `decons` (`decon_game_id`, `decon_gametime`, `decon_player_id`, `decon_building_id`)
			                    VALUES (%s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, building_id))
		else:
			self.dbc.execute("""INSERT INTO `destructions` (`destruct_game_id`, `destruct_gametime`, `destruct_player_id`, `destruct_building_id`, `destruct_weapon_id`)
			                    VALUES (%s, %s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, building_id, weapon_id))

	""" A team stages """
	def Log_Stage(self, gametime, line):
		# Parse the line
		match = self.RE_STAGE.search(line)
		if match == None:
			return

		result = match.groups()
		team  = result[0]
		stage = result[1]

		if team == 'A':
			if stage == '2':
				self.game_stage_alien2 = gametime
			elif stage == '3':
				self.game_stage_alien3 = gametime
		elif team == 'H':
			if stage == '2':
				self.game_stage_human2 = gametime
			elif stage == '3':
				self.game_stage_human3 = gametime

	""" A Player called a vote """
	def Log_Vote(self, gametime, line, team):
		# Parse the line
		match = self.RE_VOTE.search(line)
		if match == None:
			return

		result = match.groups()
		player_id = result[0]
		votetype  = result[1]
		votearg1  = result[2]
		votearg2  = result[3]
		victim_mysql_id = 0;
		victim_id = None;
		
		if not self.players.has_key(player_id):
			return

		player_mysql_id = self.players[player_id]['id']
		if not self.game_players.has_key(player_mysql_id):
			return

		if team == 'team' and self.players[player_id]['team'] != 'spectator':
			team = self.players[player_id]['team'];
		else:
			team = 'public'

		if votetype == 'denybuild' or votetype == 'allowbuild' or votetype == 'mute' or votetype == 'unmute':
			if self.players.has_key(votearg1):
				victim_id = votearg1
		elif votetype == 'ban':
			votetype = 'kick'
			for id in self.players:
				if self.players[id]['ip'] == votearg1:
					victim_id = id
					break
		elif votetype == 'evacuation':
			votetype = 'draw'
		elif votetype == 'set' and votearg1 == 'g_nextMap':
			votetype = 'nextmap'
			votearg1 = votearg2
		elif votetype == 'echo' and votearg1 == 'poll':
			votetype = 'poll'
			votearg1 = votearg2

		if victim_id != None and self.players[victim_id].has_key('id'):
			victim_mysql_id = self.players[victim_id]['id']
			votearg1 = None

		self.dbc.execute("""INSERT INTO `votes` (`vote_game_id`, `vote_gametime`, `vote_player_id`, `vote_victim_id`, `vote_type`, `vote_arg`, `vote_mode`) VALUES (%s, %s, %s, %s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, victim_mysql_id, votetype, votearg1, team))
		self.dbc.execute("SELECT LAST_INSERT_ID()")
		( self.vote[team], ) = self.dbc.fetchone()

	""" A vote ended """
	def Log_EndVote(self, gametime, line):
		# Parse the line
		match = self.RE_ENDVOTE.search(line)
		if match == None:
			return

		result   = match.groups()
		team     = result[0]
		passed   = result[1]
		yescount = result[2]
		nocount  = result[3]
		count    = result[4]

		if passed == 'pass':
			passed = 'yes'
		else:
			passed = 'no'

		# Too late to change the database enum now
		if team == 'global':
			team = 'public'

		if not self.vote.has_key(team):
			return

		self.dbc.execute("""UPDATE `votes` SET
		                        `vote_pass` = %s,
		                        `vote_yes` = %s,
		                        `vote_no` = %s,
		                        `vote_count` = %s,
		                        `vote_endtime` = %s
		                    WHERE `vote_id` = %s""",
		                    (passed, yescount, nocount, count, gametime, self.vote[team]))
		self.vote[team] = None


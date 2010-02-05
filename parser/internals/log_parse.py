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
	def Main(self, dbc, Check_map_in_database, Add_player_to_update, games_log, static_log, archive_log, archive_dir):
		# Regular expressions
		self.RE_LOGTYPE       = re.compile("^[ ]*([0-9]+:[0-9]{2}) (.*?): ")
		self.RE_UNCOLOR_NAME  = re.compile("\\^[^\\^]")
		self.RE_CORRECT_NAME  = re.compile("\\^[^\\^]$")
		self.RE_MESSAGE_CHECK = re.compile("^[ ]*(\\^[^\\^])?!")
		
		self.RE_CONNECT      = re.compile("^([0-9]+) \\[[0-9\.]+\\] \\((.+)\\) \"(.*?)\"$")
		self.RE_CONNECT2     = re.compile("^([0-9]+) \\[[0-9\.]+\\] \\((.+)\\) \"(.*?)\" \\(\"(.*?)\"\\)$")
		self.RE_DISCONNECT   = re.compile("^([0-9]+)")
		self.RE_RENAME       = re.compile("^([0-9]+) \\[[0-9\.]+\\] \\((.+)\\) \".*?\" -> \"(.*?)\"$")
		self.RE_RENAME2      = re.compile("^([0-9]+) \\[[0-9\.]+\\] \\((.+)\\) \".*?\" -> \"(.*?)\" \\(\".*?\" -> \".*?\"\\)$")
		self.RE_TEAMCLASS    = re.compile("^([0-9]+) (alien|human) (.+)$")
		self.RE_EXIT         = re.compile("^(Aliens|Humans) win\.$")

		self.RE_SAY          = re.compile("^(?:\\[[HSA]\\] )?(.*?): (.+)$")
		self.RE_KILL         = re.compile("^([0-9]+) ([0-9]+) [0-9]+: .+ killed .+ by (.*)$")
		self.RE_DECON        = re.compile("^([0-9]+) [0-9]+ [0-9]+: .+ deconstructed (.+)$")
		self.RE_DESTROY      = re.compile("^([0-9]+) [0-9]+ [0-9]+: .+ destroyed (.*?) by (.+)$")

		self.RE_REALTIME     = re.compile("([0-9]+)/([0-9]+)/([0-9]+) ([0-9]+):([0-9]+):([0-9]+)$")

		# Localize parents function
		self.Check_map_in_database = Check_map_in_database
		self.Add_player_to_update  = Add_player_to_update

		# Internal datas
		self.dbc                 = dbc
		self.games_log           = games_log
		self.static_log          = static_log
		self.archive_log         = archive_log
		self.archive_dir         = archive_dir
		self.game_id             = None
		self.game_timestamp      = None
		self.game_action         = False
		self.has_clientteamclass = False
		self.players             = {}
		self.game_players        = {}

		# Collect data
		self.Get_weapon_ids()
		self.Get_building_ids()

		# Read the logfile
		result = self.Log_read()
		return result

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

	""" Correct player's name """
	def Correct_player_name(self, name):
		name = re.sub(self.RE_CORRECT_NAME, '', name)
		return name

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
			return

		# The line is ok, truncate it
		result = match.groups()
		gametime = result[0]
		logtype  = result[1]
		line = re.sub(self.RE_LOGTYPE, '', line)

		# Switch the different logtypes
		if logtype == 'InitGame':
			self.Log_InitGame(gametime, line)

		# If there is no game-id yet, return now
		if self.game_id == None:
			return

		# Check all game based logtypes
		if logtype == 'ClientConnect':
			self.Log_ClientConnect(gametime, line)
		elif logtype == 'ClientDisconnect':
			self.Log_ClientDisconnect(gametime, line)
		elif logtype == 'ClientRename':
			self.Log_ClientRename(gametime, line)
		elif logtype == 'ClientTeamClass':
			self.Log_ClientTeamClass(gametime, line)
		elif logtype == 'Exit':
			self.Log_Exit(gametime, line)

		elif logtype == 'say':
			self.Log_Say(gametime, 'public', line)
		elif logtype == 'sayteam':
			self.Log_Say(gametime, 'team', line)

		elif logtype == 'Kill':
			self.Log_Kill(gametime, line)
		elif logtype == 'Decon':
			self.Log_Decon(gametime, line)
		elif logtype == 'RealTime':
			self.Log_RealTime(gametime, line)
	
	""" Log played games for each player """
	def Log_PlayerUpdate(self):
		for player_id in self.game_players:
			if player_id > 0 and (self.game_players[player_id]['kills'] > 0 or self.game_players[player_id]['teamkills'] > 0 or self.game_players[player_id]['deaths'] > 0):
				self.Log_PlayerSingleUpdate(player_id, self.game_players[player_id])

	""" Update a single player """
	def Log_PlayerSingleUpdate(self, player_id, player):
		self.dbc.execute("""UPDATE `players` SET
		                      `player_games_played`    = `player_games_played` + IF (%s = 1, 1, 0),
		                      `player_games_paused`    = 0,
		                      `player_total_kills`     = `player_total_kills` + %s,
		                      `player_total_teamkills` = `player_total_teamkills` + %s,
		                      `player_total_deaths`    = `player_total_deaths` + %s,
		                      `player_deaths_by_enemy` = `player_deaths_by_enemy` + %s,
		                      `player_deaths_by_team`  = `player_deaths_by_team` + %s,
		                      `player_deaths_by_world` = `player_deaths_by_world` + %s
		                    WHERE player_id = %s""",
		                    (player['joins'],
		                     player['kills'],
		                     player['teamkills'],
		                     player['deaths'],
		                     player['deaths_by_enemy'],
		                     player['deaths_by_team'],
		                     player['deaths_by_world'],
		                     player_id))
		self.dbc.execute("INSERT INTO `per_game_stats` (`stats_player_id`, `stats_game_id`, `stats_kills`, `stats_teamkills`, `stats_deaths`) VALUES (%s, %s, %s, %s, %s)", (player_id, self.game_id, player['kills'], player['teamkills'], player['deaths']))

		self.game_players[player_id] = {'joins': player['joins'], 'kills': 0, 'teamkills': 0, 'deaths': 0, 'deaths_by_world': 0, 'deaths_by_team': 0, 'deaths_by_enemy': 0}

		self.Add_player_to_update(player_id)

	""" A new map started """
	def Log_InitGame(self, gametime, line):
		# Reset game players
		self.game_players = {}

		# Get map name
		game_map = None

		# Check all data
		data = line.split("\\")[1:]
		if data != None:
			for i in range(0, len(data), 2):
				if data[i] == 'mapname':
					game_map = data[i+1]
					break

		# This game had no action yet
		self.game_action = False

		# No time stamp yet, use current time
		dt = datetime.datetime.now()
		self.game_timestamp = dt.isoformat()

		# If no map-name was given, return
		if game_map == None:
			self.game_id = None
			return

		# Insert game into database
		game_map_id = self.Check_map_in_database(game_map)
		self.dbc.execute("INSERT INTO `games` (`game_map_id`) VALUES (%s)", (game_map_id))
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
		# Set all players to one more game paused
		self.dbc.execute("UPDATE `players` SET `player_games_paused` = `player_games_paused` + 1")

		# Update statistics for players
		self.Log_PlayerUpdate()

		winner = None

		# Parse the line
		match = self.RE_EXIT.search(line)
		if match == None:
			# No winner, but maybe this game had action?
			if self.game_action == True:
				# Yes it has, so count it as tied
				winner = 'none'
		else:

			# We have a winner, update the database
			result = match.groups()
			game_winner = result[0]

			if game_winner == 'Timelimit':
				winner = 'tie'
			elif game_winner == 'Aliens':
				winner = 'aliens'
			elif game_winner == 'Humans':
				winner = 'humans'

		if winner != None:
			self.dbc.execute("UPDATE `games` SET `game_winner` = %s, `game_length` = %s WHERE `game_id` = %s", (winner, gametime, self.game_id))
		else:
			self.dbc.execute("UPDATE `games` SET `game_length` = %s WHERE `game_id` = %s", (gametime, self.game_id))

	""" A client connects """
	def Log_ClientConnect(self, gametime, line):
		# Parse the line
		match = self.RE_CONNECT2.search(line)
		if match == None:
			match = self.RE_CONNECT.search(line)
		if match == None:
			return

		result = match.groups()
		player_id             = result[0]
		player_qkey           = result[1]
		player_name           = self.Correct_player_name(result[2])
		player_name_uncolored = self.Remove_colors(result[2])

		# Check against MySQL (non TJW and `unnamed player`)
		if player_qkey == 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' and self.Player_is_unnamed(player_name_uncolored):
			self.players[player_id] = {'name': 'UnnamedPlayer', 'name_uncolored': 'UnnamedPlayer', 'id': 0, 'qkey': player_qkey}
		else:
			# Check against MySQL (TJW)
			if player_qkey != 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX':
				self.dbc.execute("SELECT `player_id`, `player_name` FROM `players` WHERE `player_qkey` = %s", (player_qkey))
				result = self.dbc.fetchone()
				if result == None:
					# We have to insert this as new player (tjw client)
					self.dbc.execute("INSERT INTO `players` (`player_name`, `player_qkey`, `player_name_uncolored`, `player_first_game_id`, `player_first_gametime`) VALUES (%s, %s, %s, %s, %s)", (player_name, player_qkey, player_name_uncolored, self.game_id, self.game_timestamp))
					self.dbc.execute("SELECT LAST_INSERT_ID()")
					result = self.dbc.fetchone()
					mysql_id = result[0]
					self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `player_qkey`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s, %s)", (mysql_id, player_qkey, player_name_uncolored, player_name))
				else:
					mysql_id = result[0]
					if result[1] != player_name:
						self.dbc.execute("UPDATE `players` SET `player_name` = %s, `player_name_uncolored` = %s WHERE `player_id` = %s", (player_name, player_name_uncolored, mysql_id))
					self.dbc.execute("SELECT `nick_name`, `nick_id` FROM `nicks` WHERE `nick_player_id` = %s AND `nick_name_uncolored` = %s", (mysql_id, player_name_uncolored))
					result = self.dbc.fetchone()
					if result == None:
						self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `player_qkey`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s, %s)", (mysql_id, player_qkey, player_name_uncolored, player_name))
					elif result[0] != player_name:
						self.dbc.execute("UPDATE `nicks` SET `nick_name` = %s WHERE `nick_id` = %s", (player_name, result[1]))

			else:
				# vanilla client
				self.dbc.execute("SELECT `player_id` FROM `players` WHERE `player_name_uncolored` = %s", (player_name_uncolored, ))
				result = self.dbc.fetchone()
				if result == None:
					# We have to insert this as new player (vanilla client)
					self.dbc.execute("INSERT INTO `players` (`player_name`, `player_name_uncolored`, `player_first_game_id`, `player_first_gametime`) VALUES (%s, %s, %s, %s)", (player_name, player_name_uncolored, self.game_id, self.game_timestamp))
					self.dbc.execute("SELECT LAST_INSERT_ID()")
					result = self.dbc.fetchone()

				mysql_id = result[0]
			
			# Add the player to internal list
			self.players[player_id] = {'name': player_name, 'name_uncolored': player_name_uncolored, 'id': mysql_id, 'qkey': player_qkey}

			# If the player wasn't in the current game, we set his gamecount up by one
			if not self.game_players.has_key(mysql_id):
				self.game_players[mysql_id] = {'joins': 1, 'kills': 0, 'teamkills': 0, 'deaths': 0, 'deaths_by_world': 0, 'deaths_by_team': 0, 'deaths_by_enemy': 0}
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
			mysql_player_id = self.players[player_id]['id']

			# If this is a known game player, update it
			if self.game_players.has_key(mysql_player_id):
				self.Log_PlayerSingleUpdate(mysql_player_id, self.game_players[mysql_player_id])

			del self.players[player_id]

	""" A client renames """
	def Log_ClientRename(self, gametime, line):
		# Parse the line
		match = self.RE_RENAME2.search(line)
		if match == None:
			match = self.RE_RENAME.search(line)
		if match == None:
			return

		result = match.groups()
		player_id   = result[0]
		player_qkey = result[1]
		player_name = self.Correct_player_name(result[2])
		player_name_uncolored = self.Remove_colors(result[2])

		if not self.players.has_key(player_id):
			return

		if self.Player_is_unnamed(player_name_uncolored):
			return

		mysql_id = self.players[player_id]['id']

		# Add the new nick for this TJW player
		if mysql_id != 0 and player_qkey != 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX' and len(player_qkey) == 32:
			self.dbc.execute("SELECT `nick_name`, `nick_id` FROM `nicks` WHERE `nick_player_id` = %s AND `nick_name_uncolored` = %s", (mysql_id, player_name_uncolored))
			result = self.dbc.fetchone()
			if result == None:
				# Add new nick
				self.dbc.execute("INSERT INTO `nicks` (`nick_player_id`, `player_qkey`, `nick_name_uncolored`, `nick_name`) VALUES (%s, %s, %s, %s)", (mysql_id, player_qkey, player_name_uncolored, player_name))
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
		match = self.RE_TEAMCLASS.search(line)
		if match == None:
			return

		self.Has_ClientTeamClass = True

		result = match.groups()
		player_id    = result[0]
		player_team  = result[1]
		player_class = result[2]	

		if self.players.has_key(player_id):
			# Set internal player data
			self.players[player_id]['team']  = player_team
			self.players[player_id]['class'] = player_class

	""" Guess the team of a player """
	def Log_GuessClientTeam(self, player_id, weapon_id):
		# If the log has clientteamclass, return 
		if self.has_clientteamclass == True:
			return

		# Get the weapon's team
		weapons_team = self.weapons_teams[weapon_id]

		# If the weapon's team isn't world, change the player's team
		if weapons_team != 'world':
			self.players[player_id]['team'] = weapons_team

	""" Someone says something """
	def Log_Say(self, gametime, mode, line):
		# Parse the line
		match = self.RE_SAY.search(line)
		if match == None:
			return

		result = match.groups()
		player_name = result[0]
		message     = result[1]

		# Check string length
		if len(message) < 10:
			return
		if len(message) > 255:
			message = message[0:255]

		# Check string content
		if self.RE_MESSAGE_CHECK.search(message) != None:
			return

		# Evaluate the data
		player = self.Get_player_data_by_name(player_name)
		
		# If player isn't in internal list, return
		if player == None:
			return
		
		# TODO: fix this space waste
		# Insert everything into database
		self.dbc.execute("""INSERT INTO `says` (`say_game_id`, `say_gametime`, `say_mode`, `say_player_id`, `say_message`)
		                    VALUES (%s, %s, %s, %s, %s)""", (self.game_id, gametime, mode, player['id'], message))

	""" A kill was done """
	def Log_Kill(self, gametime, line):
		# Parse the line
		match = self.RE_KILL.search(line)
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

			# Guess the player's team if needed
			self.Log_GuessClientTeam(player_source_id, weapon_id)

			# Get player's team
			if self.players[player_source_id].has_key('team'):
				# Team is logged
				player_source_team = self.players[player_source_id]['team']
			else:
				# Team is not logged (unpatched server)
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
				# Team is logged
				player_target_team = self.players[player_target_id]['team']
			else:
				# Team is not logged (unpatched server)
				player_target_team = None
		else:
			# Seems to be no valid player, return
			return

		# Get kill type (kill / teamkill)
		if player_source_team == None or player_target_team == None:
			killtype = 'enemy'
		elif player_source_team != player_target_team:
			killtype = 'enemy'
		else:
			killtype = 'team'

		# Insert the kill into the database
		self.dbc.execute("""INSERT INTO `kills` (`kill_game_id`, `kill_gametime`, `kill_type`, `kill_source_player_id`, `kill_target_player_id`, `kill_weapon_id`)
		                    VALUES (%s, %s, %s, %s, %s, %s)""", (self.game_id, gametime, killtype, player_source_mysql_id, player_target_mysql_id, weapon_id))

		# Update the internal list
		if killtype == 'team':
			if self.game_players.has_key(player_source_mysql_id):
				self.game_players[player_source_mysql_id]['teamkills'] += 1
		elif killtype == 'enemy':
			if self.game_players.has_key(player_source_mysql_id):
				self.game_players[player_source_mysql_id]['kills'] += 1

		if self.game_players.has_key(player_target_mysql_id):
			self.game_players[player_target_mysql_id]['deaths'] += 1

		if player_source_id == '1022':
			if self.game_players.has_key(player_target_mysql_id):
				self.game_players[player_target_mysql_id]['deaths_by_world'] += 1
		elif killtype == 'team':
			if self.game_players.has_key(player_target_mysql_id):
				self.game_players[player_target_mysql_id]['deaths_by_team'] += 1
		elif killtype == 'enemy':
			if self.game_players.has_key(player_target_mysql_id):
				self.game_players[player_target_mysql_id]['deaths_by_enemy'] += 1

		# This game has action!
		self.game_action = True

	""" Someone deconed or destroyed something """
	def Log_Decon(self, gametime, line):
		# Parse the line
		decon_match   = self.RE_DECON.search(line)
		destroy_match = self.RE_DESTROY.search(line)

		# Check type of 'decon'
		if decon_match != None:
			# Its a deconstruction
			result = decon_match.groups()
			player_id         = result[0]
			building_constant = result[1]

			# Check building
			if self.buildings.has_key(building_constant):
				# Building is known
				building_id = self.buildings[building_constant]
			else:
				# No known building
				return

			# Check player
			if self.players.has_key(player_id):
				# The player is in the internal list
				player_mysql_id = self.players[player_id]['id']
			else:
				# Seems to be no valid player, return
				return

			# Insert it into database
			self.dbc.execute("""INSERT INTO `decons` (`decon_game_id`, `decon_gametime`, `decon_player_id`, `decon_building_id`)
			                    VALUES (%s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, building_id))

		elif destroy_match != None:
			# Its a destruction
			result = destroy_match.groups()
			player_id         = result[0]
			building_constant = result[1]
			weapon_constant   = result[2]

			# Check weapon
			if self.weapons.has_key(weapon_constant):
				# Weapon is known
				weapon_id = self.weapons[weapon_constant]
			else:
				# No known weapon
				return

			# Check building
			if self.buildings.has_key(building_constant):
				# Building is known
				building_id = self.buildings[building_constant]
			else:
				# No known building
				return

			# Check player
			if player_id == '1022':
				# The player is <world>, so we take 0 as MySQL value
				player_mysql_id = 0
			elif self.players.has_key(player_id):
				# The player is in the internal list
				player_mysql_id = self.players[player_id]['id']

				# Guess the player's team if needed
				self.Log_GuessClientTeam(player_id, weapon_id)
			else:
				# Seems to be no valid player, return
				return

			# Insert it into databse
			self.dbc.execute("""INSERT INTO `destructions` (`destruct_game_id`, `destruct_gametime`, `destruct_player_id`, `destruct_building_id`, `destruct_weapon_id`)
			                    VALUES (%s, %s, %s, %s, %s)""", (self.game_id, gametime, player_mysql_id, building_id, weapon_id))

			# This game has action!
			self.game_action = True


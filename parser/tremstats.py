#!/usr/bin/python
# -*- coding: utf-8 -*-

"""
" Project:     Tremstats
" File:        tremstats.py
"
" This program is free software; you can redistribute it and/or
" modify it under the terms of the GNU Lesser General Public
" License as published by the Free Software Foundation; either
" version 2.1 of the License, or (at your option) any later version.
"
" This program is distributed in the hope that it will be useful,
" but WITHOUT ANY WARRANTY; without even the implied warranty of
" MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
" Lesser General Public License for more details.
"
" You should have received a copy of the GNU Lesser General Public
" License along with this library; if not, write to the Free Software
" Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
"
" @link http://tremstats.dasprids.de/
" @author Ben 'DASPRiD' Scholzen <mail@dasprids.de>
" @package Tremstats
" @version 0.6.0 ~ slux`s Mod <slux83@gmail.com>
"""

# Main imports
import sys, os

# Additional site-packages
import MySQLdb

# Internal libraries
from internals.log_parse import Parser
from internals.data_calc import Calculator
from internals.pk3_read import Reader

# Config
from config import CONFIG

""" Mainclass: Tremstats """
class Tremstats:
	""" Init Tremstats """
	def Main(self):
		# Internal datas
		self.games_log         = CONFIG['GAMES_LOG']
		self.static_log        = False
		self.pk3_dir           = CONFIG['PK3_DIR']
		self.archive_dir       = CONFIG['ARCH_DIR']
		self.calconly          = False
		self.pk3only           = False
		self.parseonly         = False
		self.one_pk3           = None
		self.reparse           = False
		self.archive_log       = True
		self.maps              = {}
		self.players_to_update = []

		# Set the archive dir to default if requested
		if self.archive_dir == None:
			self.archive_dir = os.path.abspath(sys.path[0]) + '/archived_logs/'

		# Check for command line arguments
		self.Check_command_line_arguments()
							
		# Connect to MySQL
		self.MySQL_connect()

		# Single pk3
		if self.one_pk3 != None:
			pk3reader = Reader()
			pk3reader.Main(self.dbc, self.Check_map_in_database, None, self.one_pk3)
			return;

		# Check for reparsing
		if self.reparse == True:
			# Set variables for reparsing
			self.static_log  = True
			self.calconly    = False
			self.pk3only     = False
			self.archive_log = False
			self.games_log   = os.path.abspath(sys.path[0]) + '/games.log.reparse'

			# Clear the database
			self.dbc.execute("TRUNCATE `decons`")
			self.dbc.execute("TRUNCATE `destructions`")
			self.dbc.execute("TRUNCATE `games`")
			self.dbc.execute("TRUNCATE `kills`")
			self.dbc.execute("TRUNCATE `per_game_stats`")
			self.dbc.execute("TRUNCATE `players`")
			self.dbc.execute("TRUNCATE `nicks`")
			self.dbc.execute("TRUNCATE `says`")

			# Create the log to reparse
			print "Creating reparse-log ..."

			reparse_log = open(self.games_log, 'w')

			archived_logs = os.listdir(self.archive_dir)
			archived_logs.sort()

			for archived_log in archived_logs:
				log_abs_path = self.archive_dir + '/' + archived_log
				if not os.path.isfile(log_abs_path):
					continue

				log = open(log_abs_path, 'r')	
				lines = log.readlines()
				log.close()

				for line in lines:
					reparse_log.write(line)

			reparse_log.close()

		# Parse log
		if self.calconly == False and self.pk3only == False:
			parser = Parser()
			result = parser.Main(self.dbc, self.Check_map_in_database, self.Add_player_to_update, self.games_log, self.static_log, self.archive_log, self.archive_dir)
			if result == None:
				# nothing parsed, exit fast
				self.pk3only = True
				self.calconly = True

		# Remove log if reparsing
		if self.reparse == True:
			os.remove(self.games_log)

		# Calculate data out of the parsed log
		if self.pk3only == False and self.parseonly == False:
			calculator = Calculator()
			calculator.Main(self.dbc, self.players_to_update)

		# Read PK3 files
		if self.calconly == False:
			pk3reader = Reader()
			pk3reader.Main(self.dbc, self.Check_map_in_database, self.pk3_dir, None)

	""" Check command line arguments """
	def Check_command_line_arguments(self):
		args = sys.argv[1:]
		for arg in args:
			arg_data = arg.split('=', 1)

			if len(arg_data) == 1:
				if arg_data[0] == '--help':
					print "Usage of tremstats.py:"
					print "----------------------------------------------------"
					print "--help:        Print this help"
					print "--reparse:     Reparses all archived logs"
					print "--calconly:    Only calculate data for MySQL"
					print "--parseonly:   Only parse the log file (debugging)"
					print "--pk3only:     Only fetch data from PK3s"
					print "--noarchive:   Don't archive the log"
					print "--log=<file>:  Parse another log than default"
					print "--pk3=<dir>:   Read another dir than default"
					print "--map=<file>:  Parse a single map pk3 for levelshot"
					sys.exit(-1)

				elif arg_data[0] == '--calconly':
					self.calconly = True
				elif arg_data[0] == '--parseonly':
					self.parseonly = True
				elif arg_data[0] == '--pk3only':
					self.pk3only = True
				elif arg_data[0] == '--reparse':
					self.reparse = True
				elif arg_data[0] == '--noarchive':
					self.archive_log = False
				else:
					sys.exit("Invalid arguments, see `tremstats.py --help`")
			elif len(arg_data) == 2:
				if arg_data[0] == '--log':
					self.games_log  = arg_data[1]
					self.static_log = True
				elif arg_data[0] == '--pk3':
					self.pk3_dir = arg_data[1]
				elif arg_data[0] == '--map':
					self.one_pk3 = arg_data[1]
				else:
					sys.exit("Invalid arguments, see `tremstats.py --help`")

	""" Connect to MySQL """
	def MySQL_connect(self):
		# Try to connect to MySQL, else exit
		try:
			self.db = MySQLdb.connect(CONFIG['MYSQL_HOSTNAME'], CONFIG['MYSQL_USERNAME'], CONFIG['MYSQL_PASSWORD'], CONFIG['MYSQL_DATABASE'])
			self.dbc = self.db.cursor()
		except:
			sys.exit("Connection to MySQL failed")

	""" Check if a specific map exists in the database """
	def Check_map_in_database(self, mapname):
		# Check internal dict first
		if self.maps.has_key(mapname):
			return self.maps[mapname]

		# Not in internal dict, check database
		self.dbc.execute("SELECT `map_id` FROM `maps` WHERE `map_name` = %s", (mapname, ))
		result = self.dbc.fetchone()

		# If map does not exist yet, insert it
		if result == None:
			self.dbc.execute("INSERT INTO `maps` (`map_name`) VALUES (%s)", (mapname, ))
			self.dbc.execute("SELECT LAST_INSERT_ID()")
			result = self.dbc.fetchone()

		# Return map id
		map_id = result[0]

		self.maps[mapname] = map_id
		return map_id

	""" Add a player to the update stack """
	def Add_player_to_update(self, player_id):
		if self.players_to_update.count(player_id) == 0:
			self.players_to_update.append(player_id)


""" Init Application """
if __name__ == '__main__':
	app = Tremstats()
	app.Main()

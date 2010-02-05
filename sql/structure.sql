DROP TABLE IF EXISTS `buildings`;
CREATE TABLE IF NOT EXISTS `buildings` (
  `building_id` tinyint(3) unsigned NOT NULL auto_increment,
  `building_constant` varchar(100) NOT NULL default '',
  `building_name` varchar(100) NOT NULL default '',
  `building_icon` varchar(50) NOT NULL default '',
  `building_efficiency_multiplier` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`building_id`),
  UNIQUE KEY `building_constant` (`building_constant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `decons`;
CREATE TABLE IF NOT EXISTS `decons` (
  `decon_id` int(11) unsigned NOT NULL auto_increment,
  `decon_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `decon_game_id` int(11) unsigned NOT NULL default '0',
  `decon_gametime` time NOT NULL default '00:00:00',
  `decon_player_id` int(11) unsigned NOT NULL default '0',
  `decon_building_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`decon_id`),
  KEY `decon_player_id` (`decon_player_id`),
  KEY `decon_building_id` (`decon_building_id`),
  KEY `decon_game_id` (`decon_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `destructions`;
CREATE TABLE IF NOT EXISTS `destructions` (
  `destruct_id` int(11) unsigned NOT NULL auto_increment,
  `destruct_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `destruct_game_id` int(11) unsigned NOT NULL default '0',
  `destruct_gametime` time NOT NULL default '00:00:00',
  `destruct_player_id` int(11) unsigned NOT NULL default '0',
  `destruct_building_id` tinyint(3) unsigned NOT NULL default '0',
  `destruct_weapon_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`destruct_id`),
  KEY `kill_source_player_id` (`destruct_player_id`,`destruct_building_id`),
  KEY `destruct_weapon_id` (`destruct_weapon_id`),
  KEY `destruct_building_id` (`destruct_building_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `game_id` int(10) unsigned NOT NULL auto_increment,
  `game_timestamp` timestamp NOT NULL default '0000-00-00 00:00:00',
  `game_map_id` int(11) unsigned NOT NULL default '0',
  `game_winner` enum('undefined','none','aliens','humans') NOT NULL default 'undefined',
  `game_length` varchar(10) NOT NULL default '00:00',
  PRIMARY KEY  (`game_id`),
  KEY `game_map_id` (`game_map_id`),
  KEY `game_winner` (`game_winner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `kills`;
CREATE TABLE IF NOT EXISTS `kills` (
  `kill_id` int(11) unsigned NOT NULL auto_increment,
  `kill_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `kill_game_id` int(11) unsigned NOT NULL default '0',
  `kill_gametime` time NOT NULL default '00:00:00',
  `kill_type` enum('enemy','team') NOT NULL default 'enemy',
  `kill_source_player_id` int(11) unsigned NOT NULL default '0',
  `kill_target_player_id` int(11) unsigned NOT NULL default '0',
  `kill_weapon_id` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`kill_id`),
  KEY `kill_source_player_id` (`kill_source_player_id`),
  KEY `kill_target_player_id` (`kill_target_player_id`),
  KEY `kill_weapon_id` (`kill_weapon_id`),
  KEY `kill_type` (`kill_type`),
  KEY `kill_game_id` (`kill_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `maps`;
CREATE TABLE IF NOT EXISTS `maps` (
  `map_id` int(11) unsigned NOT NULL auto_increment,
  `map_name` varchar(100) NOT NULL default '',
  `map_longname` varchar(200) default NULL,
  `map_levelshot` longblob,
  PRIMARY KEY  (`map_id`),
  UNIQUE KEY `map_name` (`map_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `nicks`;
CREATE TABLE `nicks` (
  `nick_id` int(11) unsigned NOT NULL auto_increment,
  `player_qkey` varchar(32) NOT NULL default '',
  `nick_player_id` int(11) unsigned NOT NULL default '0',
  `nick_name_uncolored` varchar(200) NOT NULL default '',
  `nick_name` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`nick_id`),
  KEY `player_qkey` (`player_qkey`),
  KEY `nick_player_id` (`nick_player_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `per_game_stats`;
CREATE TABLE IF NOT EXISTS `per_game_stats` (
  `stats_id` int(11) unsigned NOT NULL auto_increment,
  `stats_player_id` int(11) unsigned NOT NULL default '0',
  `stats_game_id` int(11) unsigned NOT NULL default '0',
  `stats_kills` int(11) unsigned NOT NULL default '0',
  `stats_teamkills` int(11) unsigned NOT NULL default '0',
  `stats_deaths` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`stats_id`),
  KEY `stats_player_id` (`stats_player_id`,`stats_game_id`),
  KEY `stats_game_id` (`stats_game_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `player_id` int(11) unsigned NOT NULL auto_increment,
  `player_qkey` varchar(32) NOT NULL default 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
  `player_name` varchar(200) NOT NULL default '',
  `player_name_uncolored` varchar(200) NOT NULL default '',
  `player_games_played` int(11) NOT NULL default '0',
  `player_games_paused` int(11) unsigned NOT NULL default '0',
  `player_first_game_id` int(11) unsigned NOT NULL default '0',
  `player_first_gametime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `player_game_time_factor` float(5,2) NOT NULL default '0.00',
  `player_total_says` int(11) unsigned NOT NULL default '0',
  `player_kill_efficiency` float(5,2) NOT NULL default '0.00',
  `player_destruction_efficiency` float(5,2) NOT NULL default '0.00',
  `player_total_efficiency` float(5,2) NOT NULL default '0.00',
  `player_total_kills` int(11) NOT NULL default '0',
  `player_total_teamkills` int(11) NOT NULL default '0',
  `player_deaths_by_enemy` int(11) NOT NULL default '0',
  `player_deaths_by_team` int(11) NOT NULL default '0',
  `player_deaths_by_world` int(11) NOT NULL default '0',
  `player_total_deaths` int(11) NOT NULL default '0',
  PRIMARY KEY  (`player_id`),
  KEY `player_qkey` (`player_qkey`),
  KEY `player_name` (`player_name`(15)),
  FULLTEXT KEY `player_name_uncolored` (`player_name_uncolored`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `says`;
CREATE TABLE IF NOT EXISTS `says` (
  `say_id` int(11) unsigned NOT NULL auto_increment,
  `say_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `say_game_id` int(11) unsigned NOT NULL default '0',
  `say_gametime` time NOT NULL default '00:00:00',
  `say_mode` enum('public','team') NOT NULL default 'public',
  `say_player_id` int(11) unsigned NOT NULL default '0',
  `say_message` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`say_id`),
  KEY `say_player_id` (`say_player_id`),
  KEY `say_game_id` (`say_game_id`),
  KEY `say_mode` (`say_mode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `weapons`;
CREATE TABLE IF NOT EXISTS `weapons` (
  `weapon_id` tinyint(3) unsigned NOT NULL auto_increment,
  `weapon_constant` varchar(100) NOT NULL default '',
  `weapon_name` varchar(100) NOT NULL default '',
  `weapon_icon` varchar(50) NOT NULL default '',
  `weapon_team` enum('alien','human','world') NOT NULL default 'world',
  PRIMARY KEY  (`weapon_id`),
  UNIQUE KEY `weapon_constant` (`weapon_constant`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `state`;
CREATE TABLE IF NOT EXISTS `state` (
  `log_id` int(11) unsigned NOT NULL default '0',
  `log_filesize` int(11) unsigned NOT NULL default '0',
  `log_offset` int(11) unsigned NOT NULL default '0',
  `log_runcount` int(11) unsigned NOT NULL default '0',
  `log_timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


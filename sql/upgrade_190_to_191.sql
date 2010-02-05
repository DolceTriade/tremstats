ALTER TABLE `votes` ADD (`vote_yes` int(11) unsigned NOT NULL default '0',
                         `vote_no` int(11) unsigned NOT NULL default '0',
                         `vote_count` int(11) unsigned NOT NULL default '0',
                         `vote_endtime` time default NULL );

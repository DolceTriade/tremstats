Upgrading from 1.9.3 to 2.0.0
=============================

The only thing needed for upgrading is to create a new database table `trueskill` and a new view `trueskill_last`. On the next run the parser automatically computes the skills of the players by examining the whole history stored in the other tables.

To create the table and the view, execute the following SQL statements in your database (if you're viewing the unformatted text file, ignore the lines starting with \`\`\`, they are just delimiters for the SQL code):

```sql
CREATE TABLE `trueskill` (
  `trueskill_id` int(11) unsigned NOT NULL auto_increment,
  `trueskill_player_id` int(11) unsigned NOT NULL,
  `trueskill_game_id` int(11) unsigned NOT NULL default '0',
  `trueskill_mu` double precision NOT NULL,
  `trueskill_sigma` double precision NOT NULL,
  PRIMARY KEY  (`trueskill_id`),
  KEY `trueskill_player_game` (`trueskill_player_id`, `trueskill_game_id`),
  KEY `trueskill_game_player` (`trueskill_game_id`, `trueskill_player_id`),
  KEY `trueskill_mu` (`trueskill_mu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE VIEW `trueskill_last` AS
  SELECT t.trueskill_id, t.trueskill_player_id, t.trueskill_game_id, t.trueskill_mu, t.trueskill_sigma
    FROM trueskill t
   WHERE t.trueskill_game_id IN
     ( SELECT MAX(s.trueskill_game_id) FROM trueskill s
        WHERE s.trueskill_player_id = t.trueskill_player_id )
;
```

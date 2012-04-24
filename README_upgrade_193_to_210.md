Upgrading from 1.9.3 to 2.1.0
=============================

**Note:** This file is written using the Markdown markup. To view the formatted result, visit the address https://github.com/ppetr/tremstats/blob/master/README_upgrade_193_to_200.md at the project repository.

**WARNING: Before taking any steps, backup your database and your working version of TremStats! I take no responsibility for any damage or loss of your data.**

------------------------------------------------------------------------

The only thing needed for upgrading is to create a new database table `trueskill` and a new view `trueskill_last`. On the next run the parser will detect that the skills have not been computed yet, and will automatically examine the whole history stored in the database and compute the skills of the players based on all the games in your database. __This means that after upgrading the computed skills will reflect all your stored history.__

To create the table and the view, execute the following SQL statements in your database (if you're viewing the unformatted text file, ignore the lines starting with \`\`\`, they are just Markdown delimiters for the SQL code):

```sql
CREATE TABLE `trueskill` (
  `trueskill_id` int(11) unsigned NOT NULL auto_increment,
  `trueskill_player_id` int(11) unsigned NOT NULL,
  `trueskill_game_id` int(11) unsigned NOT NULL default '0',
  `trueskill_mu` double precision NOT NULL,
  `trueskill_sigma` double precision NOT NULL,
  `trueskill_alien_mu` double precision NOT NULL,
  `trueskill_alien_sigma` double precision NOT NULL,
  `trueskill_human_mu` double precision NOT NULL,
  `trueskill_human_sigma` double precision NOT NULL,
  PRIMARY KEY  (`trueskill_id`),
  KEY `trueskill_player_game` (`trueskill_player_id`, `trueskill_game_id`),
  KEY `trueskill_game_player` (`trueskill_game_id`, `trueskill_player_id`),
  KEY `trueskill_mu` (`trueskill_mu`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE VIEW `trueskill_last` AS
  SELECT t.trueskill_id, t.trueskill_player_id, t.trueskill_game_id, 
        t.trueskill_mu, t.trueskill_sigma,
        t.trueskill_alien_mu, t.trueskill_alien_sigma,
        t.trueskill_human_mu, t.trueskill_human_sigma
    FROM trueskill t
   WHERE t.trueskill_game_id IN
     ( SELECT MAX(s.trueskill_game_id) FROM trueskill s
        WHERE s.trueskill_player_id = t.trueskill_player_id )
;
```

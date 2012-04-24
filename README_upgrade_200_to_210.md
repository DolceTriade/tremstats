Upgrading from 2.0.0 to 2.1.0
=============================

**Note:** This file is written using the Markdown markup. To view the formatted result, visit the address https://github.com/ppetr/tremstats/blob/master/README_upgrade_200_to_210.md at the project repository.

**WARNING: Before taking any steps, backup your database and your working version of TremStats! I take no responsibility for any damage or loss of your data.**

------------------------------------------------------------------------

Issue the following SQL statements:

```sql
TRUNCATE TABLE `trueskill` ;

ALTER TABLE `trueskill`
  ADD COLUMN `trueskill_alien_mu` double precision NOT NULL ,
  ADD COLUMN `trueskill_alien_sigma` double precision NOT NULL ,
  ADD COLUMN `trueskill_human_mu` double precision NOT NULL ,
  ADD COLUMN `trueskill_human_sigma` double precision NOT NULL ;

DROP VIEW IF EXISTS `trueskill_last`;
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

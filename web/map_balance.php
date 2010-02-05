<?php
/**
 * Project:     Tremstats
 * File:        map_balance.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$maps_by_wins = $db->GetAll("SELECT map_id,
                                    map_name,
                                    if (map_longname != '', map_longname, map_name) AS map_text_name,
                                    mapstat_alien_wins,
                                    mapstat_human_wins,
                                    mapstat_ties + mapstat_draws AS ties,
                                    mapstat_alien_wins / IFNULL( mapstat_alien_wins + mapstat_human_wins, 1 ) AS map_balance
                             FROM map_stats
                             INNER JOIN maps ON map_id = mapstat_id
                             ORDER BY map_balance DESC, ties DESC");

$maps_by_kills = $db->GetAll("SELECT map_id,
                                    map_name,
                                    if (map_longname != '', map_longname, map_name) AS map_text_name,
                                    mapstat_alien_kills,
                                    mapstat_human_kills,
                                    mapstat_alien_kills / IFNULL( mapstat_alien_kills + mapstat_human_kills, 1 ) AS map_balance
                             FROM map_stats
                             INNER JOIN maps ON map_id = mapstat_id
                             ORDER BY map_balance DESC, map_name ASC");

$maps_by_deaths = $db->GetAll("SELECT map_id,
                                    map_name,
                                    if (map_longname != '', map_longname, map_name) AS map_text_name,
                                    mapstat_alien_deaths,
                                    mapstat_human_deaths,
                                    mapstat_alien_deaths / IFNULL( mapstat_alien_deaths + mapstat_human_deaths, 1 ) AS map_balance
                             FROM map_stats
                             INNER JOIN maps ON map_id = mapstat_id
                             ORDER BY map_balance DESC, map_name ASC");

// Assign variables to template
$tpl->assign('maps_by_wins', $maps_by_wins);
$tpl->assign('maps_by_kills', $maps_by_kills);
$tpl->assign('maps_by_deaths', $maps_by_deaths);
                                     
// Show the template
$tpl->display('map_balance.tpl.php');
?>

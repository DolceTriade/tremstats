<?php
/**
 * Project:     Tremstats
 * File:        player_details.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['player_id'])) {
  die('No player id given');
}

// Player details
$player_details = $db->GetRow("SELECT player_id,
                                      player_name,
                                      player_qkey,
                                      player_games_played,
                                      player_games_paused,
                                      player_game_time_factor,
                                      player_total_kills,
                                      player_total_teamkills,
                                      player_total_deaths,
                                      player_deaths_by_enemy,
                                      player_deaths_by_team,
                                      player_deaths_by_world,
                                      player_kill_efficiency,
                                      player_destruction_efficiency,
                                      player_total_efficiency,
                                      COUNT(kill_id) AS player_selfkills,
                                      DATE_FORMAT(player_first_gametime, '%d.%m.%Y | %H:%i') AS player_first_seen
                               FROM players
                               LEFT JOIN kills ON kill_source_player_id = player_id AND kill_target_player_id = player_id
                               WHERE player_id = ?
                               GROUP BY player_id
                               LIMIT 0, 1",
                               array($_GET['player_id']));

// Other nicks used by this player
$player_nicks = $db->GetAll("SELECT nick_name
                              FROM `nicks`
                              WHERE player_qkey = ?
                              ORDER BY nick_name_uncolored ASC",
                              array($player_details['player_qkey']));

// Random quote
$random_quote = $db->GetRow("SELECT say_mode, say_message
                             FROM says
                             WHERE say_player_id = ?
                             ORDER BY RAND()
                             LIMIT 0, 1",
                             array($player_details['player_id']));

// Prefered weapons
$prefered_weapons = $db->GetAll("SELECT COUNT(kill_id) AS weapon_used,
                                        weapon_name,
                                        weapon_icon
                                 FROM kills
                                 INNER JOIN weapons ON weapon_id = kill_weapon_id
                                 WHERE kill_source_player_id = ?
                                       AND kill_source_player_id != kill_target_player_id
                                 GROUP BY kill_weapon_id
                                 ORDER BY weapon_used DESC",
                                 array($player_details['player_id']));

// Destroyed structures
$destroyed_structures = $db->GetAll("SELECT COUNT(destruct_id) AS building_destroyed,
                                            building_name,
                                            building_icon
                                     FROM destructions
                                     INNER JOIN buildings ON building_id = destruct_building_id
                                     WHERE destruct_player_id = ?
                                     GROUP BY destruct_building_id
                                     ORDER BY building_destroyed DESC",
                                     array($player_details['player_id']));

// Assign variables to template
$tpl->assign('player_details',       $player_details);
$tpl->assign('player_nicks',         $player_nicks);
$tpl->assign('random_quote',         $random_quote);
$tpl->assign('prefered_weapons',     $prefered_weapons);
$tpl->assign('destroyed_structures', $destroyed_structures);
                                     
// Show the template
$tpl->display('player_details.tpl.php');
?>

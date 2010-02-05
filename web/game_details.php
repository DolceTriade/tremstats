<?php
/**
 * Project:     Tremstats
 * File:        top_players.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

$game_details = $db->GetRow("SELECT game_id,
                                    game_timestamp,
                                    game_map_id,
                                    game_winner,
                                    game_length
                             FROM games
                             WHERE game_id = ?",
                             array($_GET['game_id']));

$map = $db->GetRow("SELECT map_name,
                           if (map_longname != '', map_longname, map_name) AS game_map_name
                    FROM maps
                    WHERE map_id = ? ",
                    array($game_details['game_map_id']));

$players = $db->GetAll("SELECT player_name,
                               player_id,
                               stats_kills,
                               stats_teamkills,
                               stats_deaths
                        FROM per_game_stats
                        INNER JOIN players ON stats_player_id = player_id
                        WHERE stats_game_id = ?
                        GROUP BY player_id
                        ORDER BY stats_kills DESC",
                        array($_GET['game_id']));

// Assign variables to template
$tpl->assign('game_details', $game_details);
$tpl->assign('map', $map);
$tpl->assign('players', $players);

// Show the template
$tpl->display('game_details.tpl.php');
?>

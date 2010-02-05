<?php
/**
 * Project:     Tremstats
 * File:        index.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link http://tremstats.dasprids.de/
 * @author Ben 'DASPRiD' Scholzen <mail@dasprids.de>
 * @package Tremstats
 * @version 0.6.0
 */

require_once 'core/init.inc.php';
require_once 'core/tremreport.class.php';

// Get current data
$reporter      = new TremulousReporter(TREMULOUS_ADDRESS);
$server_status = $reporter->getStatus();

$running_map = $db->GetRow("SELECT COUNT(game_map_id) AS game_map_played,
                                   game_map_id,
                                   if (map_longname != '', map_longname, map_name) AS game_map_name,
                                   (SELECT COUNT(*) FROM games awc WHERE awc.game_map_id = games.game_map_id AND awc.game_winner = 'aliens') AS game_alien_wins,
                                   (SELECT COUNT(*) FROM games hwc WHERE hwc.game_map_id = games.game_map_id AND hwc.game_winner = 'humans') AS game_human_wins
                            FROM games
                            INNER JOIN maps ON map_id = game_map_id
                            WHERE map_name = ? AND game_winner != 'undefined'
                            GROUP BY game_map_id
                            ORDER BY game_map_played DESC
                            LIMIT 0, 1",
                            array($server_status['server_vars']['mapname']));

// Get saved data
$games      = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE game_winner != 'undefined'");
$alien_wins = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE game_winner = 'aliens'");
$human_wins = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE game_winner = 'humans'");
$tied       = $db->GetRow("SELECT COUNT(*) AS count FROM games WHERE game_winner = 'none'");

$top_player = $db->GetRow("SELECT player_id,
                                  player_name,
                                  player_total_efficiency
                           FROM players
                           WHERE player_games_played >= ?
                                 AND player_games_paused <= ?
                           ORDER BY player_total_efficiency DESC
                           LIMIT 0, 1",
                           array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));
$top_feeder = $db->GetRow("SELECT player_id,
                                  player_name,
                                  IF (player_games_played = 0, 0, player_deaths_by_enemy / player_games_played) AS average_deaths_by_enemy
                           FROM players
                           WHERE player_games_played >= ?
                                 AND player_games_paused <= ?
                           ORDER BY average_deaths_by_enemy DESC
                           LIMIT 0, 1",
                           array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));
$top_teamkiller = $db->GetRow("SELECT player_id,
                                      player_name,
                                      IF (player_games_played = 0, 0, player_total_teamkills / player_games_played) AS average_kills_to_team
                               FROM players
                               WHERE player_games_played >= ?
                                     AND player_games_paused <= ?
                               ORDER BY average_kills_to_team DESC
                               LIMIT 0, 1",
                               array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));
$most_active_player = $db->GetRow("SELECT player_id,
                                          player_name,
                                          player_game_time_factor
                                   FROM players
                                   WHERE player_games_played >= ?
                                         AND player_games_paused <= ?
                                   ORDER BY player_game_time_factor DESC
                                   LIMIT 0, 1",
                                   array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));

$most_played_map = $db->GetRow("SELECT COUNT(game_map_id) AS game_map_played,
                                       game_map_id,
                                       if (map_longname != '', map_longname, map_name) AS game_map_name,
                                       (SELECT COUNT(*) FROM games awc WHERE awc.game_map_id = games.game_map_id AND awc.game_winner = 'aliens') AS game_alien_wins,
                                       (SELECT COUNT(*) FROM games hwc WHERE hwc.game_map_id = games.game_map_id AND hwc.game_winner = 'humans') AS game_human_wins
                                FROM games
                                INNER JOIN maps ON map_id = game_map_id
                                WHERE game_winner != 'undefined'
                                GROUP BY game_map_id
                                ORDER BY game_map_played DESC
                                LIMIT 0, 1");

$state = $db->GetRow("SELECT log_timestamp FROM state WHERE log_id = '0'");

$overview = array();
$overview['games']      = $games['count'];
$overview['alien_wins'] = $alien_wins['count'];
$overview['human_wins'] = $human_wins['count'];
$overview['tied']       = $tied['count'];

$overview['top_player']         = $top_player;
$overview['top_feeder']         = $top_feeder;
$overview['top_teamkiller']     = $top_teamkiller;
$overview['most_active_player'] = $most_active_player;

$overview['most_played_map'] = $most_played_map;

// Assign variables to template
$tpl->assign('server_status', $server_status);
$tpl->assign('running_map',   $running_map);
$tpl->assign('overview',      $overview);

$tpl->assign('state',         $state);

// Show the template
$tpl->display('overview.tpl.php');
?>

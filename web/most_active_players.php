<?php
/**
 * Project:     Tremstats
 * File:        most_active_players.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

$custom_orders = array (
  'rank'           => 'player_rank',
  'player'         => 'player_name_uncolored',
  'kills'          => 'player_total_kills',
  'deaths'         => 'player_total_deaths',
  'efficiency'     => 'player_total_efficiency',
  'games'          => 'player_games_played',
  'gametimefactor' => 'player_game_time_factor',
);
$order = get_custom_sort($custom_orders, 'rank');

$db->Execute("SET @n := 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
               SELECT player_id,
                      @n := @n + 1 AS player_rank,
                      player_name,
                      player_name_uncolored,
                      player_total_kills,
                      player_total_deaths,
                      player_total_efficiency,
                      player_games_played,
                      player_game_time_factor
               FROM players
               WHERE player_games_played >= ?
                     AND player_games_paused <= ?
               ORDER BY player_game_time_factor DESC
             )", array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));


$pagelister->SetQuery("SELECT player_id,
                              player_rank,
                              player_name,
                              player_total_kills,
                              player_total_deaths,
                              player_total_efficiency,
                              player_games_played,
                              player_game_time_factor
                       FROM tmp
                       ORDER BY ".$order."");

$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);

// Show the template
$tpl->display('most_active_players.tpl.php');
?>
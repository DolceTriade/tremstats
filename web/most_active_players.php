<?php
/**
 * Project:     Tremstats
 * File:        most_active_players.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$last_game  = $db->GetRow("SELECT game_id FROM games ORDER BY game_id DESC LIMIT 0, 1");
$game_cutoff= $last_game['game_id'] - TRESHOLD_MAX_GAMES_PAUSED;

$custom_orders = array (
  'rank'           => 'player_rank',
  'player'         => 'player_name_uncolored',
  'kills'          => 'player_kills',
  'deaths'         => 'player_deaths',
  'efficiency'     => 'player_total_efficiency',
  'games'          => 'player_games_played',
  'gametimefactor' => 'player_game_time_factor',
  'skill'          => 'skill'
);
$order = get_custom_sort($custom_orders, 'rank');

$db->Execute("SET @n := 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
               SELECT player_id,
                      @n := @n + 1 AS player_rank,
                      player_name,
                      player_name_uncolored,
                      player_kills,
                      player_deaths,
                      player_total_efficiency,
                      player_games_played,
                      player_game_time_factor,
                      t.trueskill_mu - 3 * t.trueskill_sigma AS skill
               FROM players
                 LEFT OUTER JOIN trueskill_last t
                   ON t.trueskill_player_id = players.player_id
               WHERE player_games_played >= ?
                     AND player_last_game_id > ?
               ORDER BY player_game_time_factor DESC
             )", array(TRESHOLD_MIN_GAMES_PLAYED, $game_cutoff));


$pagelister->SetQuery("SELECT player_id,
                              player_rank,
                              player_name,
                              player_kills,
                              player_deaths,
                              player_total_efficiency,
                              player_games_played,
                              player_game_time_factor,
                              skill
                       FROM tmp
                       ORDER BY ".$order."");

$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);

// Show the template
$tpl->display('most_active_players.tpl.php');
?>

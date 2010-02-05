<?php
/**
 * Project:     Tremstats
 * File:        top_players.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

$custom_orders = array (
  'rank'       => 'player_rank',
  'player'     => 'player_name_uncolored',
  'kills'      => 'player_total_kills',
  'team_kills' => 'player_total_teamkills',
  'deaths'     => 'player_total_deaths',
  'efficiency' => 'player_total_efficiency'
);
$order = get_custom_sort($custom_orders, 'rank');

$db->Execute("SET @n := 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
                SELECT player_id,
                       @n := @n + 1 AS player_rank,
                       player_name,
                       player_name_uncolored,
                       player_total_kills,
                       player_total_teamkills,
                       player_total_deaths,
                       player_total_efficiency
                FROM players
                WHERE player_games_played >= ?
                      AND player_games_paused <= ?
                ORDER BY player_total_efficiency DESC
              )", array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));

$pagelister->SetQuery("SELECT player_id,
                              player_rank,
                              player_name,
                              player_total_kills,
                              player_total_teamkills,
                              player_total_deaths,
                              player_total_efficiency
                       FROM tmp
                       ORDER BY ".$order);
$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);

// Show the template
$tpl->display('top_players.tpl.php');
?>
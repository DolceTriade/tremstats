<?php
/**
 * Project:     Tremstats
 * File:        top_teamkillers.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

$custom_orders = array (
  'rank'               => 'player_rank',
  'player'             => 'player_name_uncolored',
  'average_kills'      => 'average_kills_to_enemy',
  'average_team_kills' => 'average_kills_to_team',
  'average_deaths'     => 'average_deaths_by_enemy',
);
$order = get_custom_sort($custom_orders, 'rank');

$db->Execute("SET @n := 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
                SELECT player_id,
                       @n := @n + 1 AS player_rank,
                       player_name,
                       player_name_uncolored,
                       IF (player_games_played = 0, 0, player_total_kills / player_games_played) AS average_kills_to_enemy,
                       IF (player_games_played = 0, 0, player_total_teamkills / player_games_played) AS average_kills_to_team,
                       IF (player_games_played = 0, 0, player_deaths_by_enemy / player_games_played) AS average_deaths_by_enemy
                FROM players
                WHERE player_games_played >= ?
                      AND player_games_paused <= ?
                ORDER BY average_kills_to_team DESC
              )", array(TRESHOLD_MIN_GAMES_PLAYED, TRESHOLD_MAX_GAMES_PAUSED));

$pagelister->SetQuery("SELECT player_id,
                             player_rank,
                             player_name,
                             average_kills_to_enemy,
                             average_kills_to_team,
                             average_deaths_by_enemy
                      FROM tmp
                      ORDER BY ".$order);
$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);

// Show the template
$tpl->display('top_teamkillers.tpl.php');
?>
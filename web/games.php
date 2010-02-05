<?php
/**
 * Project:     Tremstats
 * File:        top_players.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (isset($_GET['map_id'] )) {
  $map_search="WHERE game_map_id = '".$_GET['map_id']."'";
  $tpl->assign('map_id', $_GET['map_id']);
}
else
{
  $map_search="";
}

if (isset($_GET['order'])) {
  switch ($_GET['order']) {
    case "kills":
      $order="stats_kills";
      $order_name="Kills";
      break;
    case "deaths":
      $order="stats_deaths";
      $order_name="Deaths";
      break;
    case "teamkills":
      $order="stats_teamkills";
      $order_name="Teamkills";
      break;
    }
}

if (isset($order_name)) {
  $tpl->assign('order_name', $order_name);
}

if (isset($order)) {
  $pagelister->SetQuery("SELECT game_id,
                                game_timestamp,
                                game_winner,
                                game_length,
                                if (map_longname != '', map_longname, map_name) AS game_map_name,
                                (SELECT SUM(".$order.") FROM per_game_stats WHERE stats_game_id = game_id) AS stats_count
                         FROM games
                         INNER JOIN maps ON game_map_id = map_id
                         INNER JOIN per_game_stats ON stats_game_id = game_id
                         ".$map_search."
                         GROUP BY game_id
                         ORDER BY stats_count DESC");
}
else {
  $pagelister->SetQuery("SELECT game_id,
                                game_timestamp,
                                game_winner,
                                game_length,
                                if (map_longname != '', map_longname, map_name) AS game_map_name
                         FROM games
                         INNER JOIN maps ON game_map_id = map_id
                         ".$map_search."
                         ORDER BY game_id DESC");
}

$games = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('games', $games);

// Show the template
$tpl->display('games.tpl.php');
?>

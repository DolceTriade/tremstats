<?php
/**
 * Project:     Tremstats
 * File:        most_active_players.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (isset($_GET['sort'])):
	switch( $_GET['sort']):
		case "alienwins":
			$orderby = "game_alien_wins DESC";
			break;
		case "humanwins":
			$orderby = "game_human_wins DESC";
			break;
		case "mapname":
			$orderby = "map_name ASC";
			break;
	endswitch;
	$order = $_GET['sort'];
endif;

if (!isset($orderby)):
	$orderby = "game_map_played DESC";
	$order="";
endif;

$pagelister->SetQuery("SELECT COUNT(game_map_id) AS game_map_played,
                              game_map_id,
                              map_name,
                              if (map_longname != '', map_longname, map_name) AS game_map_name,
                              (SELECT COUNT(*) FROM games awc WHERE awc.game_map_id = games.game_map_id AND awc.game_winner = 'aliens') AS game_alien_wins,
                              (SELECT COUNT(*) FROM games hwc WHERE hwc.game_map_id = games.game_map_id AND hwc.game_winner = 'humans') AS game_human_wins
                       FROM games
                       INNER JOIN maps ON map_id = game_map_id
                       WHERE game_winner != 'undefined'
                       GROUP BY game_map_id
                       ORDER BY ".$orderby);
$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);
$tpl->assign('order', $order);

// Show the template
$tpl->display('most_played_maps.tpl.php');
?>

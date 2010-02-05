<?php
/**
 * Project:     Tremstats
 * File:        most_played_maps.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (isset($_GET['sort'])):
	switch( $_GET['sort']):
		case "alienwins":
			$orderby = "mapstat_alien_wins DESC";
			break;
		case "humanwins":
			$orderby = "mapstat_human_wins DESC";
			break;
		case "mapname":
			$orderby = "map_name ASC";
			break;
	endswitch;
	$order = $_GET['sort'];
endif;

if (!isset($orderby)):
	$orderby = "mapstat_games DESC";
	$order="";
endif;

$db->Execute("SET @n = 0");
$db->Execute("CREATE TEMPORARY TABLE tmp (
                SELECT map_id,
                       @n := @n + 1 AS map_rank,
                       map_name,
                       if (map_longname != '', map_longname, map_name) AS map_text_name,
                       mapstat_games,
                      mapstat_alien_wins,
                      mapstat_human_wins
                FROM map_stats
                INNER JOIN maps ON map_id = mapstat_id
                ORDER BY ".$orderby.")");

$pagelister->SetQuery("SELECT map_id,
                              map_rank,
                              map_name,
                              map_text_name,
                              mapstat_games,
                              mapstat_alien_wins,
                              mapstat_human_wins
                       FROM tmp
                       ORDER BY ".$orderby);
$top = $db->GetAll($pagelister->GetQuery());

// Assign variables to template
$tpl->assign('top', $top);
$tpl->assign('order', $order);

// Show the template
$tpl->display('most_played_maps.tpl.php');
?>

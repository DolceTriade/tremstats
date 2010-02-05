<?php
/**
 * Project:     Tremstats
 * File:        player_getsig.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['player_id'])) {
  die('No player id given');
}

// Player details
$player_details = $db->GetRow("SELECT player_id,
                                      player_name
                               FROM players
                               WHERE player_id = ?
                               LIMIT 0, 1",
                               array($_GET['player_id']));

if( !isset($player_details['player_id']) ):
  die ("player id not found");
endif;

// Assign variables to template
$tpl->assign('player_details',       $player_details);
                                     
// Show the template
$tpl->display('player_getsig.tpl.php');
?>

<?php
/**
 * Project:     Tremstats
 * File:        _levelshot.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['map_id'])) {
  die('No map id given');
}

// Get the data
$levelshot = $db->GetOne("SELECT map_levelshot FROM maps WHERE map_id = ?", array($_GET['map_id']));

if (strlen($levelshot) > 0) {
  header('Content-Type: image/jpeg');
  echo $levelshot;
} else {
  header('Content-Type: image/png');
  readfile('images/no_levelshot.png');
}
?>
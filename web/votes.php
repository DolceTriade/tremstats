<?php
/**
 * Project:     Tremstats
 * File:        votes.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';


// maps
$map_votes = $db->GetAll("SELECT map_id, map_longname,
                                 SUM( vote_pass = 'yes' ) AS count_pass,
                                 SUM( vote_pass = 'no' ) AS count_fail
                          FROM votes
                          INNER JOIN maps ON map_name = vote_arg
                          WHERE vote_type = 'map' OR vote_type = 'nextmap'
                          GROUP BY vote_arg
                          ORDER BY count_pass DESC, map_name ASC
                          LIMIT 0, 10");

$map_skips = $db->GetAll("SELECT map_id, map_longname,
                                 SUM( vote_pass = 'yes' ) AS count_pass,
                                 SUM( vote_pass = 'no' ) AS count_fail
                          FROM votes
                          INNER JOIN games ON game_id = vote_game_id
                          INNER JOIN maps ON map_id = game_map_id
                          WHERE vote_type = 'map' OR vote_type = 'draw'
                          GROUP BY map_name
                          ORDER BY count_pass DESC, map_name ASC
                          LIMIT 0, 10");

$kick_votes = $db->GetAll("SELECT player_id, player_name,
                                  SUM( vote_pass = 'yes' ) AS count_pass,
                                  SUM( vote_pass = 'no' ) AS count_fail
                           FROM votes
                           INNER JOIN players ON player_id = vote_victim_id
                           WHERE vote_type = 'kick'
                           GROUP BY vote_victim_id
                           ORDER BY count_pass DESC
                           LIMIT 0, 10");

$mute_votes = $db->GetAll("SELECT player_id, player_name,
                                  SUM( vote_pass = 'yes' ) AS count_pass,
                                  SUM( vote_pass = 'no' ) AS count_fail
                           FROM votes
                           INNER JOIN players ON player_id = vote_victim_id
                           WHERE vote_type = 'mute'
                           GROUP BY vote_victim_id
                           ORDER BY count_pass DESC
                           LIMIT 0, 10");

$kick_happy = $db->GetAll("SELECT player_id, player_name,
                                  COUNT(*) AS votes
                           FROM votes
                           INNER JOIN players ON player_id = vote_player_id
                           WHERE vote_type = 'kick'
                           GROUP BY vote_player_id
                           ORDER BY votes DESC
                           LIMIT 0, 10");

$mute_happy = $db->GetAll("SELECT player_id, player_name,
                                  COUNT(*) AS votes
                           FROM votes
                           INNER JOIN players ON player_id = vote_player_id
                           WHERE vote_type = 'mute'
                           GROUP BY vote_player_id
                           ORDER BY votes DESC
                           LIMIT 0, 10");

$summary = $db->GetAll("SELECT vote_type,
                               SUM( vote_pass = 'yes' ) AS count_pass,
                               COUNT(*) AS count
                        FROM votes
                        GROUP BY vote_type
                        ORDER BY count DESC");

// Assign variables to template
$tpl->assign('map_votes',  $map_votes);
$tpl->assign('map_skips',  $map_skips);
$tpl->assign('kick_votes', $kick_votes);
$tpl->assign('mute_votes', $mute_votes);
$tpl->assign('kick_happy', $kick_happy);
$tpl->assign('mute_happy', $mute_happy);
$tpl->assign('summary',    $summary);
                                     
// Show the template
$tpl->display('votes.tpl.php');
?>

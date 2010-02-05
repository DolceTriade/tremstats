<?php
/**
 * Project:     Tremstats
 * File:        game_log.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

$game_details = $db->GetRow("SELECT game_id,
                                    game_timestamp,
                                    game_map_id,
                                    game_winner,
                                    game_length,
                                    game_sudden_death,
                                    game_stage_alien2,
                                    game_stage_alien3,
                                    game_stage_human2,
                                    game_stage_human3
                             FROM games
                             WHERE game_id = ?",
                             array($_GET['game_id']));

if( !isset($game_details['game_map_id']) ):
  die ("game id not found");
endif;

$map = $db->GetRow("SELECT map_name,
                           if (map_longname != '', map_longname, map_name) AS game_map_name
                    FROM maps
                    WHERE map_id = ? ",
                    array($game_details['game_map_id']));

$says = $db->GetAll("SELECT player_name,
                            player_id,
                            say_gametime,
                            say_mode,
                            say_message
                        FROM says
                        INNER JOIN players ON say_player_id = player_id
                        WHERE say_game_id = ?",
                        array($_GET['game_id']));

$kills = $db->GetAll("SELECT players.player_name AS victim_name,
                             players.player_id AS victim_id,
                             source.player_name AS killer_name,
                             source.player_id AS killer_id,
                             kill_gametime,
                             kill_type,
                             weapon_name,
                             weapon_icon
                        FROM kills
                        INNER JOIN players ON kill_target_player_id = player_id
                        LEFT JOIN players AS source ON kill_source_player_id = source.player_id
                        INNER JOIN weapons ON weapon_id = kill_weapon_id
                        WHERE kill_game_id = ?",
                        array($_GET['game_id']));

$destructs = $db->GetAll("SELECT player_name,
                                player_id,
                                destruct_gametime,
                                building_name,
                                building_icon,
                                building_team,
                                weapon_name,
                                weapon_icon,
                                weapon_team
                        FROM destructions
                        INNER JOIN players ON destruct_player_id = player_id
                        INNER JOIN buildings ON building_id = destruct_building_id
                        INNER JOIN weapons ON weapon_id = destruct_weapon_id
                        WHERE destruct_game_id = ?",
                        array($_GET['game_id']));

$builds = $db->GetAll("SELECT player_name,
                              player_id,
                              build_gametime,
                              building_name,
                              building_icon
                        FROM builds
                        INNER JOIN players ON build_player_id = player_id
                        INNER JOIN buildings ON building_id = build_building_id
                        WHERE build_game_id = ?",
                        array($_GET['game_id']));

$decons = $db->GetAll("SELECT player_name,
                              player_id,
                              decon_gametime,
                              building_name,
                              building_icon
                        FROM decons
                        INNER JOIN players ON decon_player_id = player_id
                        INNER JOIN buildings ON building_id = decon_building_id
                        WHERE decon_game_id = ?",
                        array($_GET['game_id']));

$votes = $db->GetAll("SELECT players.player_name AS caller_name,
                             players.player_id AS caller_id,
                             victim.player_name AS victim_name,
                             victim.player_id AS victim_id,
                             vote_gametime,
                             vote_mode,
                             vote_type,
                             vote_arg,
                             vote_endtime,
                             vote_pass,
                             vote_yes,
                             vote_no
                        FROM votes
                        INNER JOIN players ON vote_player_id = player_id
                        LEFT JOIN players AS victim ON vote_victim_id = victim.player_id
                        WHERE vote_game_id = ?",
                        array($_GET['game_id']));


$N=1;
foreach ($kills as $kill):
  $logs[$kill['kill_gametime'].'.'.$N++]=$kill;
endforeach;

foreach ($builds as $build):
  $logs[$build['build_gametime'].'.'.$N++]=$build;
endforeach;

foreach ($decons as $decon):
  $logs[$decon['decon_gametime'].'.'.$N++]=$decon;
  $N++;
endforeach;

foreach ($destructs as $destruct):
  $logs[$destruct['destruct_gametime'].'.'.$N++]=$destruct;
endforeach;

if( constant('PRIVACY_CHAT') != '1' ):
  foreach ($says as $say):
    $logs[$say['say_gametime'].'.'.$N++]=$say;
  endforeach;
endif;

foreach ($votes as $vote):
  $logs[$vote['vote_gametime'].'.'.$N++]=$vote;
  if( !empty( $vote['vote_endtime'] ) ):
    $logs[$vote['vote_endtime'].'.'.$N++]=
      array( 'endvote_gametime' => $vote['vote_endtime'],
             'endvote_mode' => $vote['vote_mode'],
             'endvote_pass' => $vote['vote_pass'],
             'endvote_yes' => $vote['vote_yes'],
             'endvote_no' => $vote['vote_no'] );
  endif;
endforeach;

$logs[$game_details['game_length'].'.'.$N++]=$game_details;

function add_misc($gametime, $action, $text, $salt) {
  global $logs;
  if (!empty($gametime)):
    $key = $gametime.'.'.$salt;
    $data['misc_gametime'] = $gametime;
    $data['misc_action'] = $action;
    $data['misc_text'] = $text;
    $logs[$key] = $data;
  endif;
}

add_misc($game_details['game_stage_alien2'], 'stage', 'Aliens Stage 2', $N++);
add_misc($game_details['game_stage_alien3'], 'stage', 'Aliens Stage 3', $N++);
add_misc($game_details['game_stage_human2'], 'stage', 'Humans Stage 2', $N++);
add_misc($game_details['game_stage_human3'], 'stage', 'Humans Stage 3', $N++);
add_misc($game_details['game_sudden_death'], 'time', 'Sudden Death', $N++);

// Assign variables to template
$tpl->assign('game_details', $game_details);
$tpl->assign('map', $map);
$tpl->assign('says', $says);
$tpl->assign('kills', $kills);

if (isset($logs) ):
  ksort ($logs, SORT_STRING);
  $tpl->assign('logs', $logs);
endif;

// Show the template
$tpl->display('game_log.tpl.php');
?>

<?php
/**
 * Project:     Tremstats
 * File:        player_details.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';

if (!isset($_GET['player_id'])) {
  die('No player id given');
}

// Player details
$player_details = $db->GetRow("SELECT player_id,
                                      player_name,
                                      player_games_played,
                                      player_kills,
                                      player_kills_alien,
                                      player_kills_human,
                                      player_teamkills,
                                      player_teamkills_alien,
                                      player_teamkills_human,
                                      player_deaths,
                                      player_deaths_enemy_alien,
                                      player_deaths_enemy_human,
                                      player_deaths_team_alien,
                                      player_deaths_team_human,
                                      player_deaths_world_alien,
                                      player_deaths_world_human,
                                      player_score_total,
                                      player_kill_efficiency,
                                      player_destruction_efficiency,
                                      player_total_efficiency,
                                      SEC_TO_TIME(player_time_spec) AS player_total_spec,
                                      SEC_TO_TIME(player_time_alien) AS player_total_alien,
                                      SEC_TO_TIME(player_time_human) AS player_total_human,
                                      SEC_TO_TIME(player_time_spec + player_time_alien + player_time_human) AS player_total_time,
                                      player_first_gametime AS player_first_seen,
                                      player_last_gametime AS player_last_seen,
                                      t.trueskill_mu - 3 * t.trueskill_sigma AS skill,
                                      t.trueskill_sigma AS skill_sigma,
                                      t.trueskill_alien_mu - 3 * t.trueskill_alien_sigma AS skill_a,
                                      t.trueskill_alien_sigma AS skill_a_sigma,
                                      t.trueskill_human_mu - 3 * t.trueskill_human_sigma AS skill_h,
                                      t.trueskill_human_sigma AS skill_h_sigma
                               FROM players
                                 LEFT OUTER JOIN trueskill_last t
                                   ON t.trueskill_player_id = players.player_id
                               WHERE player_id = ?
                               LIMIT 0, 1",
                               array($_GET['player_id']));

if( !isset($player_details['player_id']) ):
  die ("player id not found");
endif;

// Other nicks used by this player
$player_nicks = $db->GetAll("SELECT nick_name
                              FROM `nicks`
                              WHERE nick_player_id = ?
                              ORDER BY nick_name_uncolored ASC",
                              array($player_details['player_id']));

// Random quote
if( constant('PRIVACY_QUOTE') == '1' ):
  $random_quote = '';
else:
  $random_quote = $db->GetRow("SELECT say_mode, say_message
                               FROM says
                               WHERE say_player_id = ?
                               ORDER BY RAND()
                               LIMIT 0, 1",
                               array($player_details['player_id']));
endif;

// Prefered weapons
$weapon_kills = $db->GetAll("SELECT COUNT(kill_id) AS weapon_count,
                                        weapon_name,
                                        weapon_icon
                                 FROM kills
                                 INNER JOIN weapons ON weapon_id = kill_weapon_id
                                 WHERE kill_source_player_id = ?
                                       AND kill_type = 'enemy'
                                 GROUP BY kill_weapon_id
                                 ORDER BY weapon_count DESC, weapon_name ASC",
                                 array($player_details['player_id']));

$weapon_deaths = $db->GetAll("SELECT COUNT(kill_id) AS weapon_count,
                                        weapon_name,
                                        weapon_icon
                                 FROM kills
                                 INNER JOIN weapons ON weapon_id = kill_weapon_id
                                 WHERE kill_target_player_id = ?
                                       AND kill_type != 'team'
                                 GROUP BY kill_weapon_id
                                 ORDER BY weapon_count DESC, weapon_name ASC",
                                 array($player_details['player_id']));

// Destroyed structures
$destroyed_structures = $db->GetAll("SELECT COUNT(destruct_id) AS building_count,
                                            building_name,
                                            building_icon
                                     FROM destructions
                                     INNER JOIN buildings ON building_id = destruct_building_id
                                     INNER JOIN weapons ON weapon_id = destruct_weapon_id
                                     WHERE destruct_player_id = ?
                                           AND building_team != weapon_team
                                           AND weapon_constant != 'MOD_NOCREEP'
                                     GROUP BY destruct_building_id
                                     ORDER BY building_count DESC, building_name ASC",
                                     array($player_details['player_id']));

$built_structures = $db->GetAll("SELECT COUNT(build_id) AS building_count,
                                            building_name,
                                            building_icon
                                     FROM builds
                                     INNER JOIN buildings ON building_id = build_building_id
                                     WHERE build_player_id = ?
                                     GROUP BY build_building_id
                                     ORDER BY building_count DESC, building_name ASC",
                                     array($player_details['player_id']));

$votes_called = $db->GetAll("SELECT COUNT(vote_id) AS vote_count,
                                            vote_type
                                     FROM votes
                                     WHERE vote_player_id = ?
                                     GROUP BY vote_type
                                     ORDER BY vote_count DESC, vote_type ASC",
                                     array($player_details['player_id']));

$votes_against = $db->GetAll("SELECT COUNT(vote_id) AS vote_count,
                                            vote_type
                                     FROM votes
                                     WHERE vote_victim_id = ?
                                     GROUP BY vote_type
                                     ORDER BY vote_count DESC, vote_type ASC",
                                     array($player_details['player_id']));

$favorite_target = $db->GetRow("SELECT player_id,
                                     player_name,
                                     COUNT(*) AS kill_count
                                     FROM kills
                                     LEFT JOIN players ON kill_target_player_id = player_id
                                     WHERE kill_source_player_id = ?
                                     GROUP BY kill_target_player_id
                                     ORDER BY kill_count desc
                                     LIMIT 0,1",
                                     array($player_details['player_id']));
$favorite_nemesis = $db->GetRow("SELECT player_id,
                                     player_name,
                                     COUNT(*) AS kill_count
                                     FROM kills
                                     LEFT JOIN players on kill_source_player_id = player_id
                                     WHERE kill_target_player_id = ? AND kill_type = 'enemy'
                                     GROUP BY kill_source_player_id
                                     ORDER BY kill_count desc
                                     LIMIT 0,1",
                                     array($player_details['player_id']));

// Assign variables to template
$tpl->assign('player_details',       $player_details);
$tpl->assign('player_nicks',         $player_nicks);
$tpl->assign('random_quote',         $random_quote);
$tpl->assign('weapon_kills',         $weapon_kills);
$tpl->assign('weapon_deaths',        $weapon_deaths);
$tpl->assign('destroyed_structures', $destroyed_structures);
$tpl->assign('built_structures',     $built_structures);
$tpl->assign('votes_called',         $votes_called);
$tpl->assign('votes_against',        $votes_against);
$tpl->assign('favorite_target',      $favorite_target);
$tpl->assign('favorite_nemesis',     $favorite_nemesis);
                                     
// Show the template
$tpl->display('player_details.tpl.php');
?>

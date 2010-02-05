<?php
/**
 * Project:     Tremstats
 * File:        _graph.php
 *
 * For license and version information, see /index.php
 */

require_once 'core/init.inc.php';
require_once 'core/jpgraph/jpgraph.php';

switch ($_GET['type']) {
  case 'kills_per_game':
    if (!isset($_GET['player_id'])) {
      die('No player id given');
    }

    require_once 'core/jpgraph/jpgraph_line.php';
    require_once 'core/jpgraph/jpgraph_regstat.php';

    // Get the data
    $stats = $db->GetAll("SELECT stats_kills,
                                 stats_teamkills,
                                 stats_deaths
                          FROM per_game_stats
                          WHERE stats_player_id = ?",
                          array($_GET['player_id']));
                          
    $i = 1;
    $kill_games = array();
    $kill_data  = array();
    $teamkill_games = array();
    $teamkill_data  = array();
    $death_games = array();
    $death_data  = array();
    foreach ($stats AS $stat) {
      $kill_games[] = $i;
      $kill_data[]  = $stat['stats_kills'];
      $teamkill_games[]  = $i;
      $teamkill_data[]   = $stat['stats_teamkills'];
      $death_games[] = $i;
      $death_data[]  = $stat['stats_deaths'];
      
      $i++;
    }

    // Check data
    if ($i == 1) {
      $kill_games = array(1, 2);
      $kill_data  = array(0, 0);
      $teamkill_games = array(1, 2);
      $teamkill_data  = array(0, 0);
      $death_games = array(1, 2);
      $death_data  = array(0, 0);
      $i = 3;
    } elseif ($i == 2) {
      $kill_games[] = 2;
      $kill_data[]  = $kill_data[0];
      $teamkill_games[] = 2;
      $teamkill_data[]  = $teamkill_data[0];
      $death_games[] = 2;
      $death_data[]  = $death_data[0];
      $i = 3;
    }

    // Get points
    $kill_spline = new Spline($kill_games, $kill_data);
    list($kill_newx, $kill_newy) = $kill_spline->Get(500);
    
    $teamkill_spline = new Spline($teamkill_games, $teamkill_data);
    list($teamkill_newx, $teamkill_newy) = $teamkill_spline->Get(500);
    
    $death_spline = new Spline($death_games, $death_data);
    list($death_newx, $death_newy) = $death_spline->Get(500);

    // Create the graph
    $g = new Graph(573,200);
    $g->SetMargin(30,10,10,10);
    $g->SetMarginColor('#22262a');
    $g->SetColor('#22262a');
    $g->SetFrame(true, array(0, 0, 0), 0);
    $g->SetBox(false);
    $g->img->SetAntiAliasing();

    // We need a linlin scale since we provide both
    // x and y coordinates for the data points.
    $g->SetScale('linlin', 0, 100);

    // Set the grid
    $g->yaxis->SetLabelFormat('%d');
    $g->xaxis->HideLabels();
    $g->xaxis->SetColor('#FFFFFF');
    $g->yaxis->SetColor('#FFFFFF');
    $g->ygrid->SetColor('#57616b');

    // And a line plot to stroke the smooth curve we got
    // from the original control points
    $kill_lplot = new LinePlot($kill_newy, $kill_newx);
    $kill_lplot->SetColor('#00FF00');
    $kill_lplot->SetLegend ("Kills");
    
    $teamkill_lplot = new LinePlot($teamkill_newy, $teamkill_newx);
    $teamkill_lplot->SetColor('#FF0000');
    $teamkill_lplot->SetLegend ("Teamkills");
    
    $death_lplot = new LinePlot($death_newy, $death_newx);
    $death_lplot->SetColor('#0000FF');
    $death_lplot->SetLegend ("Deaths");
    
    // Add the plots to the graph and stroke
    $g->Add($kill_lplot);
    $g->Add($teamkill_lplot);
    $g->Add($death_lplot);
    $g->Stroke();
    break;

  case 'kills_in_game':
    if (!isset($_GET['game_id'])) {
      die('No game id given');
    }

    require_once 'core/jpgraph/jpgraph_line.php';
    require_once 'core/jpgraph/jpgraph_regstat.php';

    // Get the data
    $stats = $db->GetAll("SELECT kill_type,
                                 kill_weapon_id,
                                 kill_gametime,
                                 kill_id
                          FROM kills
                          WHERE kill_game_id = ?
                          ORDER BY kill_id",
                          array($_GET['game_id']));
    $weapons = $db->GetAll("SELECT weapon_id,
                                   weapon_team
                            FROM weapons");
    $game = $db->GetRow("SELECT game_length
                         FROM games
                         WHERE game_id = ?",
                         array($_GET['game_id']));

    sscanf($game['game_length'], "%d:%d:%d", $hh, $mm, $ss);
    $maxoffset = (($hh * 60 + $mm) * 60 + $ss) / 60;
    $yscale = 0;

    $length = count($stats);
    $offset = 1;

    $alien_numb = array();
    $alien_data = array();
    $human_numb = array();
    $human_data = array();
    $world_numb = array();
    $world_data = array();
    foreach ($stats AS $stat) {
      sscanf($stat['kill_gametime'], "%d:%d:%d", $hh, $mm, $ss);
      $stamp = (($hh * 60 + $mm) * 60 + $ss) / 60;
      while ($offset < 2 || $offset < $stamp) {
        $alien_numb[] = $offset;
        $alien_data[] = 0;
        $human_numb[] = $offset;
        $human_data[] = 0;
        $world_numb[] = $offset;
        $world_data[] = 0;
        $offset++;
      }
      $id = $stat['kill_weapon_id'];
      $team = $weapons[$id]['weapon_team'];
      if ($team == 'alien' ) {
        end($alien_data);
        $k = key($alien_data);
        $alien_data[$k]++;
        if ($alien_data[$k] > $yscale) $yscale = $alien_data[$k];
      }
      else if($team == 'human' ) {
        end($human_data);
        $k = key($human_data);
        $human_data[$k]++;
        if ($human_data[$k] > $yscale) $yscale = $human_data[$k];
      }
      else {
        end($world_data);
        $k = key($world_data);
        $world_data[$k]++;
        if ($world_data[$k] > $yscale) $yscale = $world_data[$k];
      }
    }
    while ($offset < 3 || $offset <= $maxoffset ) {
      $alien_numb[] = $offset;
      $alien_data[] = 0;
      $human_numb[] = $offset;
      $human_data[] = 0;
      $world_numb[] = $offset;
      $world_data[] = 0;
      $offset++;
    }

    if ($yscale < 10 ) $yscale = 10;

    $alien_spline = new Spline($alien_numb, $alien_data);
    list($alien_newx, $alien_newy) = $alien_spline->Get(500);

    $human_spline = new Spline($human_numb, $human_data);
    list($human_newx, $human_newy) = $human_spline->Get(500);

    $world_spline = new Spline($world_numb, $world_data);
    list($world_newx, $world_newy) = $world_spline->Get(500);

    // create graph
    $g = new Graph(573,200);
    $g->SetMargin(30,10,10,30);
    $g->SetMarginColor('#22262a');
    $g->SetColor('#22262a');
    $g->SetFrame(true, array(0, 0, 0), 0);
    $g->SetBox(false);
    $g->img->SetAntiAliasing();

    // We need a linlin scale since we provide both
    // x and y coordinates for the data points.
    $g->SetScale('linlin', 0, $yscale);

    // Set the grid
    $g->yaxis->SetLabelFormat('%d');
    $g->xaxis->SetLabelFormat('%d');
    $g->xaxis->SetColor('#FFFFFF');
    $g->yaxis->SetColor('#FFFFFF');
    $g->ygrid->SetColor('#57616b');

    // And a line plot to stroke the smooth curve we got
    // from the original control points
    $alien_lplot = new LinePlot($alien_newy, $alien_newx);
    $alien_lplot->SetColor('#FF0000');
    $alien_lplot->SetLegend ("Alien Kills");

    $human_lplot = new LinePlot($human_newy, $human_newx);
    $human_lplot->SetColor('#0000FF');
    $human_lplot->SetLegend ("Human Kills");

    $world_lplot = new LinePlot($world_newy, $world_newx);
    $world_lplot->SetColor('#00FF');
    $world_lplot->SetLegend ("Misc. Deaths");

    // Add the plots to the graph and stroke
    $g->Add($alien_lplot);
    $g->Add($human_lplot);
    $g->Add($world_lplot);
    $g->Stroke();
    break;

  case 'wins_in_game':
    if (!isset($_GET['map_id'])) {
      die('No map id given');
    }

    require_once 'core/jpgraph/jpgraph_pie.php';

    // Get data
    $wins = $db->GetRow("SELECT mapstat_alien_wins,
                                mapstat_human_wins,
                                mapstat_ties + mapstat_draws AS ties
                         FROM map_stats WHERE mapstat_id = ?",
                         array($_GET['map_id']));
    if ($wins['mapstat_alien_wins'] + $wins['mapstat_human_wins'] + $wins['ties'] > 0):
      $data  = array($wins['mapstat_alien_wins'], $wins['mapstat_human_wins'], $wins['ties']);
    else:
      $data  = array(0, 0, 1);
    endif;


    // Build graph
    $g  = new PieGraph (200,120);
    $g->SetMargin(30,30,10,25);
    $g->SetMarginColor('#22262a');
    $g->SetColor('#22262a');
    $g->SetFrame(true, array(0, 0, 0), 0);
    $g->SetBox(false);

    // Build plot
    $p1 = new PiePlot( $data);
    $p1->SetCenter(40, 60);
    $p1->SetSliceColors(array('#CC0000', '#0055FF', '#888888'));
    $p1->value->show(false);
    $legends = array('Aliens (%d%%)', 'Humans (%d%%)', 'Tied (%d%%)');
    $p1->SetLegends($legends);
    $g->legend->SetShadow(false);
    $g->legend->SetAbsPos(10, 60, 'right', 'center');

    // Stroke
    $g->Add($p1);
    $g->Stroke();
    break;

  case 'map_balance':
    if (!isset($_GET['map_id'])) {
      die('No map id given');
    }

    require_once 'core/jpgraph/jpgraph_bar.php';

    // Get data
    $map = $db->GetRow("SELECT map_name,
                               mapstat_alien_wins,
                               mapstat_human_wins,
                               mapstat_ties + mapstat_draws AS ties
                        FROM map_stats
                        INNER JOIN maps ON map_id = mapstat_id
                        WHERE map_id = ?",
                        array($_GET['map_id']));

    $total = $map['mapstat_alien_wins'] + $map['mapstat_human_wins'] + $map['ties'];
    $data_a  = array(($total) ? $map['mapstat_alien_wins'] * 100.0 / $total : 0);
    $data_h  = array(($total) ? $map['mapstat_human_wins'] * 100.0 / $total : 0);
    $data_t  = array(($total) ? $map['ties'] * 100.0 / $total : 100);

    // Build graph
    $graph = new Graph (400,30);
    $graph->SetScale('textlin', 0, 100);
    $graph->Set90AndMargin(1, 1, 1, 1);
    $graph->SetMarginColor('#22262a');
    $graph->SetColor('#22262a');
    $graph->SetFrame(true, '#FFFFFF', 0);

    $bpa = new BarPlot($data_a);
    $bpa->SetFillColor ('#CC0000');
    $bpt = new BarPlot($data_t);
    if ($total == 0):
      $bpt->SetFillColor ('#222222');
    else:
      $bpt->SetFillColor ('#888888');
    endif;
    $bph = new BarPlot($data_h);
    $bph->SetFillColor ('#0055FF');
    $accplot = new AccBarPlot (array($bpa ,$bpt, $bph));
    $accplot->SetWidth(16);

    $graph->xaxis->Hide();
    $graph->yaxis->HideLine();
    $graph->yaxis->HideTicks();
    $graph->yaxis->SetMajTickPositions(array(25, 50, 75));
    $graph->ygrid->SetColor('#555555');

    // Stroke
    $graph->Add($accplot);
    $graph->Stroke();
    break;

  case 'balance_bar':
    require_once 'core/jpgraph/jpgraph_bar.php';

    // Get data
    $a = (isset($_GET['a'])) ? $_GET['a'] : 0;
    $b = (isset($_GET['b'])) ? $_GET['b'] : 0;
    $c = (isset($_GET['c'])) ? $_GET['c'] : 0;

    $total = $a + $b + $c;
    $data_a  = array(($total) ? $a * 100.0 / $total : 0);
    $data_b  = array(($total) ? $b * 100.0 / $total : 100);
    $data_c  = array(($total) ? $c * 100.0 / $total : 0);

    // Build graph
    $graph = new Graph (400,30);
    $graph->SetScale('textlin', 0, 100);
    $graph->Set90AndMargin(1, 1, 1, 1);
    $graph->SetMarginColor('#22262a');
    $graph->SetColor('#22262a');
    $graph->SetFrame(true, '#FFFFFF', 0);

    $bpa = new BarPlot($data_a);
    $bpa->SetFillColor ('#CC0000');
    $bpt = new BarPlot($data_b);
    if ($total == 0):
      $bpt->SetFillColor ('#222222');
    else:
      $bpt->SetFillColor ('#888888');
    endif;
    $bph = new BarPlot($data_c);
    $bph->SetFillColor ('#0055FF');
    $accplot = new AccBarPlot (array($bpa ,$bpt, $bph));
    $accplot->SetWidth(16);

    $graph->xaxis->Hide();
    $graph->yaxis->HideLine();
    $graph->yaxis->HideTicks();
    $graph->yaxis->SetMajTickPositions(array(25, 50, 75));
    $graph->ygrid->SetColor('#555555');

    // Stroke
    $graph->Add($accplot);
    $graph->Stroke();
    break;

}
?>

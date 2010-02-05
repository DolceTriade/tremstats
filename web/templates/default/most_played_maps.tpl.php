<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div id="box_header">
    <span class="box_title"><h2><?php if ($this->order == 'alienwins'): echo "Maps by Alien Wins"; elseif ($this->order == 'humanwins'): echo "Maps by Human Wins"; elseif ($this->order == 'mapname'): echo "Maps by Name"; else: echo "Most Played Maps"; endif; ?></h2></span>
    <span class="box_select">
      Sort by: <a href="most_played_maps.php">Most Played</a> | <a href="most_played_maps.php?sort=alienwins">Alien Wins</a> | <a href="most_played_maps.php?sort=humanwins">Human Wins</a> | <a href="most_played_maps.php?sort=mapname">Map Name</a>
    </span>
  </div>

  <table>
    <colgroup>
      <col width="170" />
      <col width="120" />
      <col />
      <col width="210" />
    </colgroup>

    <thead>
      <tr>
        <th>Levelshot</th>
        <th colspan="2">Data</th>
        <th>Winners in Games</th>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="4">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php $pos = 1; ?>
      <?php foreach ($this->top AS $game): ?>
        <tr>
          <td rowspan="5">
            <img width="160" height="120" alt="<?php echo htmlspecialchars($game['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($game['game_map_id']); ?>" />
          </td>
          <td><strong>Map Name</strong></td>
          <td><strong><a href="games.php?map_id=<?php echo $game['game_map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($game['game_map_name'])); ?></a></strong></td>
          <td rowspan="5">
            <img width="200" height="120" alt="Winners in Games" src="_graph.php?type=wins_in_game&amp;map_id=<?php echo ($game['game_map_id']); ?>" />
          </td>
        </tr>
        <tr>
          <?php if($this->order == "mapname"): ?>
            <td>Simple Name</td>
            <td><?php echo $game['map_name']; ?></td>
          <?php else: ?>
            <td>Position</td>
            <td><?php echo $pos; ?></td>
          <?php endif; ?>
        </tr>
        <tr>
          <td>Times Played</td>
          <td><?php echo $game['game_map_played']; ?></td>
        </tr>
        <tr>
          <td>Alien Wins</td>
          <td><?php echo $game['game_alien_wins']; ?></td>
        </tr>
        <tr>
          <td>Human Wins</td>
          <td><?php echo $game['game_human_wins']; ?></td>
        </tr>
        <?php $pos++; ?>
      <?php endforeach; ?>

      <?php if (!count($this->top)): ?>
        <tr>
          <td colspan="4">No games played yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>

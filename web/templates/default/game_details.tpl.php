<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Game #<?php echo $this->game_details['game_id']; ?></h2>

  <table>
    <colgroup>
      <col width="50" />
      <col width="100" />
    </colgroup>

    <tbody>
     <th colspan="2">Game info</td>
     <tr>
      <td>Date</td>
      <td><?php echo $this->game_details['game_timestamp']; ?></td>
     </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="170" />
      <col />
    </colgroup>

    <tbody>
     <th colspan="2">Map</td>
     <tr>
      <td rowspawn="2">
        <img width="160" height="120" alt="<?php echo htmlspecialchars($this->map['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->game_details['game_map_id']); ?>" />
      </td>
      <td valign="top"><strong><?php echo replace_color_codes(htmlspecialchars($this->map['game_map_name'])); ?></strong></td>
     </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="50" />
      <col width="100" />
    </colgroup>

    <tbody>
     <th colspan="2">Results</td>
     <tr>
      <td>Winner</td>
      <td><?php echo $this->game_details['game_winner']; ?></td>
     </tr>
     <tr>
      <td>Game time</td>
      <td><?php echo $this->game_details['game_length']; ?></td>
     </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="100" />
      <col width="40" />
      <col width="40" />
      <col width="120" />
    </colgroup>

    <tbody>
     <th>Player</td>
     <th>Kills</td>
     <th>Team Kills</td>
     <th>Deaths</td>
      <?php foreach ($this->players AS $player): ?>
       <tr>
        <td><a href="player_details.php?player_id=<?php echo $player['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])) ?></a></td>
        <td><?php echo $player['stats_kills'] ?></td>
        <td><?php echo $player['stats_teamkills'] ?></td>
        <td><?php echo $player['stats_deaths'] ?></td>
       </tr>
      <?php endforeach; ?>
      <?php if (!count($this->players)): ?>
        <tr>
          <td colspan="4">No players</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

    <table>
    <colgroup>
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stats per minute</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><img src="_graph.php?type=kills_in_game&amp;game_id=<?php echo $this->game_details['game_id']; ?>" /></td>
      </tr>
    </tbody>
  </table>

 </div>

 <?php include '__footer__.tpl.php'; ?>

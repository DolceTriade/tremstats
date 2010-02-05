<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div class="heading">
    <span class="heading"><h2>Game #<?php echo $this->game_details['game_id']; ?> Summary</h2></span>
    <span class="headinglink"> ( <a href="game_log.php?game_id=<?php echo $this->game_details['game_id'] ?>">show game log</a> )</span>
  </div>

  <table>
    <colgroup>
      <col class="levelshot" />
      <col class="item" />
      <col />
    </colgroup>

    <tbody>
     <th colspan="3">Game info</td>
     <tr>
      <td rowspan="10">
        <img width="160" height="120" alt="<?php echo htmlspecialchars($this->map['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->game_details['game_map_id']); ?>" />
      </td>
      <td><strong>Map Name</strong></td>
      <td><strong><a href="map_details.php?map_id=<?php echo $this->game_details['game_map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($this->map['game_map_name'])); ?></a></strong></td>
     </tr>
     <tr>
      <td>Winner</td>
      <td><?php echo $this->game_details['game_winner']; ?></td>
     </tr>
     <tr>
      <td>Game time</td>
      <td><?php echo $this->game_details['game_length']; ?></td>
     </tr>
     <tr>
      <td>Date</td>
      <td><?php echo $this->game_details['game_timestamp']; ?></td>
     </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col />
      <col />
      <col />
      <col />
    </colgroup>

    <tbody>
     <th>Aliens (stage <?php if (!empty($this->game_details['game_stage_alien3'])): echo 3; elseif (!empty($this->game_details['game_stage_alien2'])): echo 2; else: echo 1; endif; ?>)</td>
     <th>Score</td>
     <th>Kills</td>
     <th>Team Kills</td>
     <th>Deaths</td>
     <th>Time</td>
      <?php $count = 0; foreach ($this->players AS $player): ?>
       <?php if ($player['time_alien'] > $player['time_human']): ?>
        <tr class="list" >
         <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])) ?></a></td>
         <td><?php echo $player['stats_score'] ?></td>
         <td><?php echo $player['stats_kills'] ?></td>
         <td><?php echo $player['stats_teamkills'] ?></td>
         <td><?php echo $player['stats_deaths'] ?></td>
         <td><?php echo $player['time_alien'] ?></td>
        </tr>
       <?php $count += 1; endif; ?>
      <?php endforeach; ?>
      <?php if ($count == 0): ?>
        <tr>
          <td colspan="6">No players</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col />
      <col />
      <col />
      <col />
    </colgroup>

    <tbody>
     <th>Humans (stage <?php if (!empty($this->game_details['game_stage_human3'])): echo 3; elseif (!empty($this->game_details['game_stage_human2'])): echo 2; else: echo 1; endif; ?>)</td>
     <th>Score</td>
     <th>Kills</td>
     <th>Team Kills</td>
     <th>Deaths</td>
     <th>Time</td>
      <?php $count = 0; foreach ($this->players AS $player): ?>
       <?php if ($player['time_human'] > $player['time_alien']): ?>
        <tr class="list" >
         <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])) ?></a></td>
         <td><?php echo $player['stats_score'] ?></td>
         <td><?php echo $player['stats_kills'] ?></td>
         <td><?php echo $player['stats_teamkills'] ?></td>
         <td><?php echo $player['stats_deaths'] ?></td>
         <td><?php echo $player['time_human'] ?></td>
        </tr>
       <?php $count += 1; endif; ?>
      <?php endforeach; ?>
      <?php if ($count == 0): ?>
        <tr>
          <td colspan="6">No players</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col />
      <col />
      <col />
      <col />
    </colgroup>

    <tbody>
     <th colspan="2">Spectators</th>
     <th>Kills</td>
     <th>Team Kills</td>
     <th>Deaths</td>
     <th>Time</td>
      <?php $count = 0; foreach ($this->players AS $player): ?>
       <?php if (empty($player['time_human']) and empty($player['time_alien'])): ?>
        <tr class="list" >
         <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])) ?></a></td>
         <td></td>
         <td><?php echo $player['stats_kills'] ?></td>
         <td><?php echo $player['stats_teamkills'] ?></td>
         <td><?php echo $player['stats_deaths'] ?></td>
         <td><?php echo $player['time_spec'] ?></td>
        </tr>
       <?php $count += 1; endif; ?>
      <?php endforeach; ?>
      <?php if ($count == 0): ?>
        <tr>
          <td colspan="6">None</td>
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

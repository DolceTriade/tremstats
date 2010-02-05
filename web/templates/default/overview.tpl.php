<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Server Status</h2>

  <table>
    <colgroup>
      <col />
      <col width="70" />
      <col width="70" />
      <col />
      <col width="70" />
      <col width="70" />
    </colgroup>

    <thead>
      <tr>
        <th colspan="3">Aliens</th>
        <th colspan="3">Humans</th>
      </tr>
    </thead>

    <tbody>
      <?php if ($this->server_status === false): ?>
        <tr>
          <td colspan="6">Server not reachable</td>
        </tr>
      <?php elseif (!count($this->server_status['aliens']) && !count($this->server_status['humans']) && !count($this->server_status['specs'])): ?>
        <tr>
          <td colspan="6">No players online</td>
        </tr>
      <?php else: ?>
        <tr>
          <th>Player</th>
          <th>Kills</th>
          <th>Ping</th>
          <th>Player</th>
          <th>Kills</th>
          <th>Ping</th>
        </tr>

        <?php $rows = max(count($this->server_status['aliens']), count($this->server_status['humans'])); ?>
        <?php for ($i=0; $i<$rows; $i++): ?>
          <?php
          $alien = (isset($this->server_status['aliens'][$i]) ? $this->server_status['aliens'][$i]: null);
          $human = (isset($this->server_status['humans'][$i]) ? $this->server_status['humans'][$i]: null);
          ?>
          <tr>
            <?php if (!is_null($alien)): ?>
              <td><?php echo replace_color_codes(htmlspecialchars($alien['name'])); ?></td>
              <td><?php echo $alien['kills']; ?></td>
              <td><?php echo $alien['ping']; ?></td>
            <?php else: ?>
              <td colspan="3">&nbsp;</td>
            <?php endif; ?>

            <?php if (!is_null($human)): ?>
              <td><?php echo replace_color_codes(htmlspecialchars($human['name'])); ?></td>
              <td><?php echo $human['kills']; ?></td>
              <td><?php echo $human['ping']; ?></td>
            <?php else: ?>
              <td colspan="3">&nbsp;</td>
            <?php endif; ?>
          </tr>
        <?php endfor; ?>
      <?php endif; ?>

      <?php if (count($this->server_status['specs'])): ?>
        <tr>
          <th colspan="6">Spectators</th>
        </tr>

        <?php foreach ($this->server_status['specs'] AS $spec): ?>
          <tr>
            <td colspan="6"><?php echo replace_color_codes(htmlspecialchars($spec['name'])); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="170" />
      <col width="120" />
      <col />
      <col width="210" />
    </colgroup>

    <thead>
      <tr>
        <th colspan="4">Running map</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td rowspan="4">
          <img width="160" height="120" alt="<?php echo htmlspecialchars($this->running_map['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->running_map['game_map_id']); ?>" />
        </td>
        <td><strong>Map Name</strong></td>
        <td><strong><?php echo replace_color_codes(htmlspecialchars($this->running_map['game_map_name'])); ?></strong></td>
        <td rowspan="4">
          <img width="200" height="120" alt="Winners in Games" src="_graph.php?type=wins_in_game&amp;map_id=<?php echo ($this->running_map['game_map_id']); ?>" />
        </td>
      </tr>
      <tr>
        <td>Times Played</td>
        <td><?php echo $this->running_map['game_map_played']; ?></td>
      </tr>
      <tr>
        <td>Alien Wins</td>
        <td><?php echo $this->running_map['game_alien_wins']; ?></td>
      </tr>
      <tr>
        <td>Human Wins</td>
        <td><?php echo $this->running_map['game_human_wins']; ?></td>
      </tr>
    </tbody>
  </table>

  <h2>Overview</h2>

  <table>
    <colgroup>
      <col width="140" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="2">General data</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Games Played</td>
        <td><?php echo $this->overview['games']; ?></td>
      </tr>
      <tr>
        <td>Alien Wins</td>
        <td><?php echo $this->overview['alien_wins']; ?></td>
      </tr>
      <tr>
        <td>Human Wins</td>
        <td><?php echo $this->overview['human_wins']; ?></td>
      </tr>
      <tr>
        <td>Tied Matches</td>
        <td><?php echo $this->overview['tied']; ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="140" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="2">Popular Players</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Top Player</td>
        <td><span class="playername"><a href="player_details.php?player_id=<?php echo $this->overview['top_player']['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($this->overview['top_player']['player_name'])); ?></a></span> (Efficiency: <?php echo $this->overview['top_player']['player_total_efficiency']; ?>)</td>
      </tr>
      <tr>
        <td>Top Feeder</td>
        <td><span class="playername"><a href="player_details.php?player_id=<?php echo $this->overview['top_feeder']['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($this->overview['top_feeder']['player_name'])); ?></a></span> (Average Deaths: <?php echo $this->overview['top_feeder']['average_deaths_by_enemy']; ?>)</td>
      </tr>
      <tr>
        <td>Top Teamkiller</td>
        <td><span class="playername"><a href="player_details.php?player_id=<?php echo $this->overview['top_teamkiller']['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($this->overview['top_teamkiller']['player_name'])); ?></a></span> (Average Team Kills: <?php echo $this->overview['top_teamkiller']['average_kills_to_team']; ?>)</td>
      </tr>
      <tr>
        <td>Most Active Player</td>
        <td><span class="playername"><a href="player_details.php?player_id=<?php echo $this->overview['most_active_player']['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($this->overview['most_active_player']['player_name'])); ?></a></span> (Game-time factor: <?php echo $this->overview['most_active_player']['player_game_time_factor']; ?>)</td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col width="170" />
      <col width="120" />
      <col />
      <col width="210" />
    </colgroup>

    <thead>
      <tr>
        <th colspan="4">Most Played Map</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td rowspan="4">
          <img width="160" height="120" alt="<?php echo htmlspecialchars($this->overview['most_played_map']['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->overview['most_played_map']['game_map_id']); ?>" />
        </td>
        <td><strong>Map Name</strong></td>
        <td><strong><?php echo replace_color_codes(htmlspecialchars($this->overview['most_played_map']['game_map_name'])); ?></strong></td>
        <td rowspan="4">
          <img width="200" height="120" alt="Winners in Games" src="_graph.php?type=wins_in_game&amp;map_id=<?php echo ($this->overview['most_played_map']['game_map_id']); ?>" />
        </td>
      </tr>
      <tr>
        <td>Times Played</td>
        <td><?php echo $this->overview['most_played_map']['game_map_played']; ?></td>
      </tr>
      <tr>
        <td>Alien Wins</td>
        <td><?php echo $this->overview['most_played_map']['game_alien_wins']; ?></td>
      </tr>
      <tr>
        <td>Human Wins</td>
        <td><?php echo $this->overview['most_played_map']['game_human_wins']; ?></td>
      </tr>
    </tbody>
  </table>

 <div id="update">Last update: <?php echo $this->state['log_timestamp']; ?></div>

</div>

<?php include '__footer__.tpl.php'; ?>

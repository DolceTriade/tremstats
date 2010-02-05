<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div class="heading">
    <span class="heading"><h2>Voting</h2></span>
  </div>

  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" />
      <col />
      <col class="playername" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th >Most Voted Maps</th>
        <th >Pass</th>
        <th >Fail</th>
        <th >Most Skipped Maps</th>
        <th >Pass</th>
        <th >Fail</th>
      </tr>
    </thead>

    <tbody>
      <?php $rows = max(count($this->map_votes), count($this->map_skips)); $rows = max($rows, 1) ?>
      <?php for ($i=0; $i<$rows; $i++): ?>
      <tr>
        <?php if (!empty($this->map_votes[$i])): $map = $this->map_votes[$i]; ?>
          <td><a href="map_details.php?map_id=<?php echo $map['map_id'] ; ?>"><?php echo replace_color_codes($map['map_longname']); ?></a></td>
          <td><?php echo $map['count_pass']; ?></td>
          <td><?php echo $map['count_fail']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="3">No Votes</td>
        <?php else: ?>
          <td colspan="3"></td>
        <?php endif; ?>

        <?php if (!empty($this->map_skips[$i])): $map = $this->map_skips[$i]; ?>
          <td><a href="map_details.php?map_id=<?php echo $map['map_id'] ; ?>"><?php echo replace_color_codes($map['map_longname']); ?></a></td>
          <td><?php echo $map['count_pass']; ?></td>
          <td><?php echo $map['count_fail']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="3">No Votes</td>
        <?php else: ?>
          <td colspan="3"></td>
        <?php endif; ?>

      </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" />
      <col />
      <col class="playername" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th >Most Kicked Player</th>
        <th >Pass</th>
        <th >Fail</th>
        <th >Most Muted Player</th>
        <th >Pass</th>
        <th >Fail</th>
      </tr>
    </thead>

    <tbody>
      <?php $rows = max(count($this->kick_votes), count($this->mute_votes)); $rows = max($rows, 1) ?>
      <?php for ($i=0; $i<$rows; $i++): ?>
      <tr>
        <?php if (!empty($this->kick_votes[$i])): $kick = $this->kick_votes[$i]; ?>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $kick['player_id']; ?>"><?php echo replace_color_codes($kick['player_name']) ?></a></td>
          <td><?php echo $kick['count_pass']; ?></td>
          <td><?php echo $kick['count_fail']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="3">No Votes</td>
        <?php else: ?>
          <td colspan="3"></td>
        <?php endif; ?>

        <?php if (!empty($this->mute_votes[$i])): $mute = $this->mute_votes[$i]; ?>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $mute['player_id']; ?>"><?php echo replace_color_codes($mute['player_name']) ?></a></td>
          <td><?php echo $mute['count_pass']; ?></td>
          <td><?php echo $mute['count_fail']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="3">No Votes</td>
        <?php else: ?>
          <td colspan="3"></td>
        <?php endif; ?>

      </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col />
      <col class="playername" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th >Kick Happy Players</th>
        <th >Called Kicks</th>
        <th >Mute Happy Players</th>
        <th >Called Mutes</th>
      </tr>
    </thead>

    <tbody>
      <?php $rows = max(count($this->kick_happy), count($this->mute_happy)); $rows = max($rows, 1) ?>
      <?php for ($i=0; $i<$rows; $i++): ?>
      <tr>
        <?php if (!empty($this->kick_happy[$i])): $kick = $this->kick_happy[$i]; ?>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $kick['player_id']; ?>"><?php echo replace_color_codes($kick['player_name']) ?></a></td>
          <td><?php echo $kick['votes']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="2">No Votes</td>
        <?php else: ?>
          <td colspan="2"></td>
        <?php endif; ?>

        <?php if (!empty($this->mute_happy[$i])): $mute = $this->mute_happy[$i]; ?>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $mute['player_id']; ?>"><?php echo replace_color_codes($mute['player_name']) ?></a></td>
          <td><?php echo $mute['votes']; ?></td>
        <?php elseif ($i == 0): ?>
          <td colspan="2">No Votes</td>
        <?php else: ?>
          <td colspan="2"></td>
        <?php endif; ?>

      </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="playername" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th >Vote Summary by Type</th>
        <th >Called</th>
        <th >Passed</th>
        <th >Failed</th>
        <th >Success Rate</th>
      </tr>
    </thead>

    <tbody>
      <?php $total = 0; $pass = 0; ?>
      <?php foreach ($this->summary AS $vote): ?>
      <tr>
          <td><?php echo $vote['vote_type']; ?></td>
          <td><?php echo $vote['count']; $total += $vote['count']; ?></td>
          <td><?php echo $vote['count_pass']; $pass += $vote['count_pass']; ?></td>
          <td><?php echo ($vote['count'] - $vote['count_pass']); ?></td>
          <td><?php echo (int)(100 * $vote['count_pass'] / $vote['count']); ?> %</td>
      </tr>
      <?php endforeach; ?>
      <tr class="spacer"></td>
      <tr>
        <td>Totals</td>
        <td><?php echo $total; ?></td>
        <td><?php echo $pass; ?></td>
        <td><?php echo ($total - $pass); ?></td>
        <td><?php echo (int)(100 * $pass / $total); ?> %</td>
      </tr>
    </tbody>
  </table>

</div>

<?php include '__footer__.tpl.php'; ?>

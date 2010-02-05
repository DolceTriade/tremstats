<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Top Team Killers</h2>

  <table>
    <colgroup>
      <col class="id" />
      <col class="playernamewide" />
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th title="Team Kill Rank"><?php echo custom_sort('#',      'rank'); ?></th>
        <th><?php echo custom_sort('Player', 'player'); ?></th>
        <th title="Average Kills to Enemy per Round"><?php echo custom_sort('AVG Kills',     'average_kills'); ?></a></th>
        <th title="Average Deaths by Enemy per Round"><?php echo custom_sort('AVG Deaths',   'average_deaths'); ?></th>
        <th title="Average Kills to Team per Round"><?php echo custom_sort('AVG Team Kills', 'average_team_kills'); ?></th>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="5">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($this->top AS $player): ?>
        <tr class="list" >
          <td><?php echo $player['player_rank']; ?></td>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])); ?></a></td>
          <td><?php echo $player['average_kills_to_enemy']; ?></td>
          <td><?php echo $player['average_deaths_by_enemy']; ?></td>
          <td><?php echo $player['average_kills_to_team']; ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->top)): ?>
        <tr>
          <td colspan="5">No players yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>


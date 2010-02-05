<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Top Feeders</h2>

  <table>
    <colgroup>
      <col width="60" />
      <col />
      <col width="140" />
      <col width="140" />
    </colgroup>

    <thead>
      <tr>
        <th><?php echo custom_sort('#',      'rank'); ?></th>
        <th><?php echo custom_sort('Player', 'player'); ?></th>
        <th title="Average Kills to Enemy per Round"><?php echo custom_sort('Average Kills',   'average_kills'); ?></th>
        <th title="Average Deaths by Enemy per Round"><?php echo custom_sort('Average Deaths', 'average_deaths'); ?></th>
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
      <?php foreach ($this->top AS $player): ?>
        <tr>
          <td><?php echo $player['player_rank']; ?></td>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])); ?></a></td>
          <td><?php echo $player['average_kills_to_enemy']; ?></td>
          <td><?php echo $player['average_deaths_by_enemy']; ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->top)): ?>
        <tr>
          <td colspan="4">No players yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>
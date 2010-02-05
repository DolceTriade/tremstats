<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Most Active Players</h2>

  <table>
    <colgroup>
      <col width="60" />
      <col />
      <col width="100" />
      <col width="100" />
      <col width="100" />
      <col width="100" />
      <col width="60" />
    </colgroup>

    <thead>
      <tr>
        <th><?php echo custom_sort('#',          'rank'); ?></th>
        <th><?php echo custom_sort('Player',     'player'); ?></th>
        <th><?php echo custom_sort('Kills',      'kills'); ?></th>
        <th><?php echo custom_sort('Deaths',     'deaths'); ?></th>
        <th><?php echo custom_sort('Efficiency', 'efficiency'); ?></th>
        <th><?php echo custom_sort('Games',      'games'); ?></th>
        <th title="Game-time factor"><?php echo custom_sort('GFT',        'gametimefactor'); ?></th>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="7">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($this->top AS $player): ?>
        <tr>
          <td><?php echo $player['player_rank']; ?></td>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])); ?></a></td>
          <td><?php echo $player['player_total_kills']; ?></td>
          <td><?php echo $player['player_total_deaths']; ?></td>
          <td><?php echo $player['player_total_efficiency']; ?></td>
          <td><?php echo $player['player_games_played']; ?></td>
          <td><?php echo $player['player_game_time_factor']; ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->top)): ?>
        <tr>
          <td colspan="7">No players yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>
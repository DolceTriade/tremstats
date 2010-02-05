<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Search result</h2>

  <table>

    <colgroup>
      <col class="playername" />
      <col class="playername" />
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th><?php echo custom_sort('Player',     'player'); ?></th>
        <th><?php echo 'Known as'; ?></th>
        <th><?php echo custom_sort('Kills',      'kills'); ?></th>
        <th><?php echo custom_sort('Deaths',     'deaths'); ?></th>
        <th><?php echo custom_sort('Efficiency', 'efficiency'); ?></th>
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
      <?php foreach ($this->players AS $player): ?>
        <tr class="list" >
          <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id'] ?>"><?php echo replace_color_codes(htmlspecialchars($player['player_name'])); ?></a></td>
          <td class="playername"><?php if(isset($player['player_tjw_name'])) { echo replace_color_codes(htmlspecialchars($player['player_tjw_name'])); }?></td>
          <td><?php echo $player['player_kills']; ?></td>
          <td><?php echo $player['player_deaths']; ?></td>
          <td><?php echo $player['player_total_efficiency']; ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->players)): ?>
        <tr>
          <td colspan="5">No matching players found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>

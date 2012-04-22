<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Top Players</h2>

  <table>
    <colgroup>
      <col class="id" />
      <col class="playernamewide" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th title="Efficiency Rank"><?php echo custom_sort('#',          'rank'); ?></th>
        <th><?php echo custom_sort('Player',     'player'); ?></th>
        <th title="Total Score"><?php echo custom_sort('Score',      'score'); ?></th>
        <th title="Total Kills"><?php echo custom_sort('Kills',      'kills'); ?></th>
        <th title="Total Deaths"><?php echo custom_sort('Deaths',     'deaths'); ?></th>
        <th title="Total Team Kills"><?php echo custom_sort('TKs', 'team_kills'); ?></th>
        <th title="Player Efficiency Rating"><?php echo custom_sort('Efficiency', 'efficiency'); ?></th>
        <th title="Player Skill Rating"><?php echo custom_sort('Skill', 'skill'); ?></th>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="8">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($this->top AS $player): ?>
        <tr class="list" >
          <td><?php echo $player['player_rank']; ?></td>
          <td class="playername"><a href="player_details.php?player_id=<?php echo $player['player_id'] ?>"><?php echo replace_color_codes($player['player_name']); ?></a></td>
          <td><?php echo $player['player_score_total']; ?></td>
          <td><?php echo $player['player_kills']; ?></td>
          <td><?php echo $player['player_deaths']; ?></td>
          <td><?php echo $player['player_teamkills']; ?></td>
          <td><?php echo $player['player_total_efficiency']; ?></td>
          <td><?php echo round($player['skill'], 1); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->top)): ?>
        <tr>
          <td colspan="8">No players yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>

<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div class="heading">
    <span class="heading"><h2>Map Details for <?php echo replace_color_codes(htmlspecialchars($this->map_details['map_text_name'])); ?></h2></span>
    <span class="headinglink"><a href="games.php?map_id=<?php echo $this->map_details['map_id'] ?>">See game list for this map</a></span>
  </div>

  <table>
    <colgroup>
      <col class="item" />
      <col />
      <col class="levelshot" />
    </colgroup>

    <thead>
      <tr>
        <th colspan="3">Summary</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Short Name</td>
        <td><?php echo $this->map_details['map_name']; ?></td>
        <td rowspan="7" style="vertical-align:top"><img width="160" height="120" alt="<?php echo htmlspecialchars($this->map_details['map_text_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->map_details['map_id']); ?>" /></td>
      </tr>
      <tr>
        <td>Games</td>
        <td><?php echo $this->map_details['mapstat_games']; ?></td>
      </tr>
      <tr>
        <td>Alien Wins</td>
        <td><?php echo $this->map_details['mapstat_alien_wins']; ?></td>
      </tr>
      <tr>
        <td>Human Wins</td>
        <td><?php echo $this->map_details['mapstat_human_wins']; ?></td>
      </tr>
      <tr>
        <td>Ties</td>
        <td><?php echo $this->map_details['mapstat_ties']; ?></td>
      </tr>
      <tr>
        <td>Draws</td>
        <td><?php echo $this->map_details['mapstat_draws']; ?></td>
      </tr>
      <tr>
        <td>Total Time</td>
        <td><?php echo $this->map_details['mapstat_text_time']; ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="item" />
      <col class="data" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Team Breakdown</th>
        <th>Wins</th>
        <th>Kills</th>
        <th>Deaths</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Aliens</td>
        <td><?php echo $this->map_details['mapstat_alien_wins']; ?></td>
        <td><?php echo $this->map_details['mapstat_alien_kills']; ?></td>
        <td><?php echo $this->map_details['mapstat_alien_deaths']; ?></td>
      </tr>
      <tr>
        <td>Humans</td>
        <td><?php echo $this->map_details['mapstat_human_wins']; ?></td>
        <td><?php echo $this->map_details['mapstat_human_kills']; ?></td>
        <td><?php echo $this->map_details['mapstat_human_deaths']; ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="item" />
      <col class="data" />
      <col class="data" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stage Breakdown</th>
        <th>1</th>
        <th>2</th>
        <th>3</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Alien Wins</td>
        <td><?php echo $this->map_details['mapstat_alien_wins'] - $this->stage_alien2['count']; ?></td>
        <td><?php echo $this->stage_alien2['count'] - $this->stage_alien3['count']; ?></td>
        <td><?php echo $this->stage_alien3['count']; ?></td>
      </tr>
      <tr>
        <td>Human Wins</td>
        <td><?php echo $this->map_details['mapstat_human_wins'] - $this->stage_human2['count']; ?></td>
        <td><?php echo $this->stage_human2['count'] - $this->stage_human3['count']; ?></td>
        <td><?php echo $this->stage_human3['count']; ?></td>
      </tr>
      <tr>
        <td>Fastest Alien Stage</td>
        <td></td>
        <td><?php echo $this->stage_speeds['alien_s2']; ?></td>
        <td><?php echo $this->stage_speeds['alien_s3']; ?></td>
      </tr>
      <tr>
        <td>Fastest Human Stage</td>
        <td></td>
        <td><?php echo $this->stage_speeds['human_s2']; ?></td>
        <td><?php echo $this->stage_speeds['human_s3']; ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
      <col class="data" />
      <col />
      <col class="data" />
    </colgroup>

    <thead>
      <?php $rows = max(count($this->weapon_kills), count($this->votes_called)); $rows = max($rows, 1) ?>
      <tr>
        <th colspan="2">Kills by Weapon</th>
        <th colspan="2">Called Votes</th>
      </tr>
    </thead>

    <tbody>
      <?php for ($i=0; $i<$rows; $i++): ?>
        <tr>
          <?php if (!empty($this->weapon_kills[$i])): $weapon = $this->weapon_kills[$i]; ?>
            <td><?php $icon = "images/icons/".(!empty($weapon['weapon_icon']) ? $weapon['weapon_icon'] : "blank.png"); ?><img src="<?php echo $icon; ?>" <?php list($width, $height, $type, $attr) = getimagesize($icon); echo $attr; ?> <?php if ($width == 15): ?>style="margin-right: 15px;"<?php endif; ?> alt="<?php echo $weapon['weapon_name']; ?>" ><?php echo $weapon['weapon_name']; ?></td>
            <td><?php echo $weapon['weapon_count']; ?></td>
          <?php elseif ($i == count($this->weapon_kills)): ?>
            <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No kills<?php endif; ?></td>
          <?php endif; ?>

         <?php if(!empty($this->votes_called[$i])): ?>
           <td><?php echo $this->votes_called[$i]['vote_type']; ?></td>
           <td><?php echo $this->votes_called[$i]['vote_count']; ?></td>
         <?php elseif($i == count($this->votes_called)): ?>
           <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No votes called<?php endif; ?></td>
         <?php endif; ?>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
      <col class="data" />
      <col />
      <col class="data" />
    </colgroup>

    <thead>
      <?php $rows = max(count($this->built_structures), count($this->destroyed_structures)); $rows = max($rows, 1) ?>
      <tr>
        <th colspan="2">Built Structures</th>
        <th colspan="2">Destroyed Structures</th>
      </tr>
    </thead>

    <tbody>
      <?php for ($i=0; $i<$rows; $i++): ?>
        <tr>
          <?php if (!empty($this->built_structures[$i])): $build = $this->built_structures[$i]; ?>
            <td><?php if (!empty($build['building_icon'])): ?><img src="images/icons/<?php echo $build['building_icon']; ?>" width="15" height="15" alt="<?php echo $build['building_name']; ?>" /> <?php endif; ?><?php echo $build['building_name']; ?></td>
            <td><?php echo $build['building_count']; ?></td>
          <?php elseif ($i == count($this->built_structures)): ?>
            <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No built structures<?php endif; ?></td>
          <?php endif; ?>

          <?php if (!empty($this->destroyed_structures[$i])): $build = $this->destroyed_structures[$i]; ?>
            <td><?php if (!empty($build['building_icon'])): ?><img src="images/icons/<?php echo $build['building_icon']; ?>" width="15" height="15" alt="<?php echo $build['building_name']; ?>" /> <?php endif; ?><?php echo $build['building_name']; ?></td>
            <td><?php echo $build['building_count']; ?></td>
          <?php elseif ($i == count($this->destroyed_structures)): ?>
            <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No destroyed structures<?php endif; ?></td>
          <?php endif; ?>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

</div>

<?php include '__footer__.tpl.php'; ?>

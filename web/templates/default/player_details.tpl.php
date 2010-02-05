<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div class="heading">
    <span class="heading"><h2>Player Details for <?php echo replace_color_codes($this->player_details['player_name']); ?></h2></span>
    <span class="headinglink"> ( <a href="player_getsig.php?player_id=<?php echo $this->player_details['player_id'] ?>">get a player signature</a> )</span>
  </div>

  <table>
    <colgroup>
      <col class="item" />
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th ></th>
        <th >Total</th>
        <th >Alien</th>
        <th >Human</th>
        <th >Spectator</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Games</td>
        <td><?php echo $this->player_details['player_games_played']; ?></td>
        <td colspan="2"></td>
        <td></td>
      </tr>
      <tr>
        <td>Time</td>
        <td><?php echo $this->player_details['player_total_time']; ?></td>
        <td><?php echo $this->player_details['player_total_alien']; ?></td>
        <td><?php echo $this->player_details['player_total_human']; ?></td>
        <td><?php echo $this->player_details['player_total_spec']; ?></td>
      </tr>
      <tr>
        <td>Score</td>
        <td><?php echo $this->player_details['player_score_total']; ?></td>
        <td colspan="2"></td>
        <td rowspan="7"></td>
      </tr>
      <tr>
        <td>Kills</td>
        <td><?php echo $this->player_details['player_kills']; ?></td>
        <td><?php echo $this->player_details['player_kills_alien']; ?></td>
        <td><?php echo $this->player_details['player_kills_human']; ?></td>
      </tr>
      <tr>
        <td>Team Kills</td>
        <td><?php echo $this->player_details['player_teamkills']; ?></td>
        <td><?php echo $this->player_details['player_teamkills_alien']; ?></td>
        <td><?php echo $this->player_details['player_teamkills_human']; ?></td>
      </tr>
      <tr>
        <td>Deaths</td>
        <td rowspan="4" style="vertical-align:top"><?php echo $this->player_details['player_deaths']; ?></td>
        <td colspan="2"></td>
      </tr>
      <tr>
        <td>Deaths by enemy</td>
        <td><?php echo $this->player_details['player_deaths_enemy_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_enemy_human']; ?></td>
      </tr>
      <tr>
        <td>Deaths by team</td>
        <td><?php echo $this->player_details['player_deaths_team_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_team_human']; ?></td>
      </tr>
      <tr>
        <td>Deaths by &lt;world&gt;</td>
        <td><?php echo $this->player_details['player_deaths_world_alien']; ?></td>
        <td><?php echo $this->player_details['player_deaths_world_human']; ?></td>
      </tr>
      <tr>
        <td>First seen</td>
        <td colspan="4"><?php echo $this->player_details['player_first_seen']; ?></td>
      </tr>
      <tr>
        <td>Last seen</td>
        <td colspan="4"><?php echo $this->player_details['player_last_seen']; ?></td>
      </tr>
      <tr>
        <td>Random Quote</td>
        <td colspan="4" class="<?php if (!$this->random_quote): ?>noquote<?php elseif ($this->random_quote['say_mode'] == 'public'): ?>quote_public<?php else: ?>quote_team<?php endif; ?>">
          <?php if (!$this->random_quote): ?>No quote available<?php else: ?><?php echo replace_color_codes(wordwrap($this->random_quote['say_message'], 60, '<br \>', true)); ?><?php endif; ?>
       </td>
      </tr>

      <tr>
        <td>Efficiencies</td>
        <td><?php echo $this->player_details['player_total_efficiency']; ?> total</td>
        <td><?php echo $this->player_details['player_kill_efficiency']; ?> kill</td>
        <td><?php echo $this->player_details['player_destruction_efficiency']; ?> destruction</td>
        <td></td>
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
      <?php $rows = max(count($this->weapon_kills), count($this->weapon_deaths)); $rows = max($rows, 1) ?>
      <tr>
        <th colspan="2">Kills by Weapon</th>
        <th colspan="2">Deaths by Weapon</th>
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

          <?php if (!empty($this->weapon_deaths[$i])): $weapon = $this->weapon_deaths[$i]; ?>
            <td><?php $icon = "images/icons/".(!empty($weapon['weapon_icon']) ? $weapon['weapon_icon'] : "blank.png"); ?><img src="<?php echo $icon; ?>" <?php list($width, $height, $type, $attr) = getimagesize($icon); echo $attr; ?> <?php if ($width == 15): ?>style="margin-right: 15px;"<?php endif; ?> alt="<?php echo $weapon['weapon_name']; ?>" ><?php echo $weapon['weapon_name']; ?></td>
            <td><?php echo $weapon['weapon_count']; ?></td>
          <?php elseif ($i == count($this->weapon_deaths)): ?>
            <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No deaths<?php endif; ?></td>
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

  <table>
    <colgroup>
      <col />
      <col class="data" />
      <col />
      <col class="data" />
    </colgroup>

    <thead>
      <?php $rows = max(count($this->votes_called), count($this->votes_against)); $rows = max($rows, 1) ?>
      <tr>
        <th colspan="2">Called Votes</th>
        <th colspan="2">Votes Against</th>
      </tr>
    </thead>

    <tbody>
      <?php for ($i=0; $i<$rows; $i++): ?>
       <tr>
         <?php if(!empty($this->votes_called[$i])): ?>
           <td><?php echo $this->votes_called[$i]['vote_type']; ?></td>
           <td><?php echo $this->votes_called[$i]['vote_count']; ?></td>
         <?php elseif($i == count($this->votes_called)): ?>
           <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No votes called<?php endif; ?></td>
         <?php endif; ?>

         <?php if(!empty($this->votes_against[$i])): ?>
           <td><?php echo $this->votes_against[$i]['vote_type']; ?></td>
           <td><?php echo $this->votes_against[$i]['vote_count']; ?></td>
         <?php elseif($i == count($this->votes_against)): ?>
           <td colspan="2" rowspan="<?php echo $rows - $i; ?>" style="vertical-align:top" ><?php if ($i == 0): ?>No votes<?php endif; ?></td>
         <?php endif; ?>
       </tr>
      <?php endfor ?>
    <tbody>
  </table>

  <table>
    <colgroup>
      <col />
    </colgroup>

    <thead>
      <tr>
        <th>Stats per Game</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td><img src="_graph.php?type=kills_per_game&amp;player_id=<?php echo $this->player_details['player_id']; ?>" width="573" height="200" /></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
      <col />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="3">Aliases</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <?php $column = 0; ?>
        <?php foreach($this->player_nicks as $other_nick): ?>
          <td class="playername">
            <?php echo replace_color_codes($other_nick['nick_name']); ?>
            <?php if ( $column == 2):  ?>
              </tr><tr>
            <?php $column = 0; else: $column++; endif; ?>
          </td>
        <?php endforeach; ?>
      </tr>
    </tbody>
  </table>
</div>

<?php include '__footer__.tpl.php'; ?>

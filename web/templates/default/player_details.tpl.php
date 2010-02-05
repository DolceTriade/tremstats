<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Player Details</h2>
  <table>
    <colgroup>
      <col width="170" />
      <col />
    </colgroup>

    <thead>
      <tr>
        <th colspan="2">Summary</th>
      </tr>
    </thead>

    <tbody>
      <tr>
        <td>Player Name</td>
        <td class="playername"><?php echo replace_color_codes(htmlspecialchars($this->player_details['player_name'])); ?></td>
      </tr>
      <tr>
        <td class="playerothernames">Other Names</td>
        <td>
         <table frame="void" rules="cols">
          <tr>
          <?php $column = 0; ?>
          <?php foreach($this->player_nicks as $other_nick): ?>
           <td>
            <?php echo replace_color_codes(htmlspecialchars($other_nick['nick_name'])); ?>
            <?php if ( $column == 2):  ?>
             </tr><tr>
            <?php $column = 0; else: $column++; endif;  ?>
           </td>
          <?php endforeach; ?>
          </tr>
         </table>
        </td>
      </tr>
      <tr>
        <td>Games played</td>
        <td><?php echo $this->player_details['player_games_played']; ?></td>
      </tr>
      <tr>
        <td>Games paused</td>
        <td><?php echo $this->player_details['player_games_paused']; ?></td>
      </tr>
      <tr>
        <td>Game-time factor</td>
        <td><?php echo $this->player_details['player_game_time_factor']; ?></td>
      </tr>
      <tr>
        <td>First seen</td>
        <td><?php echo $this->player_details['player_first_seen']; ?></td>
      </tr>
      <tr>
        <td>Random Quote</td>
        <td class="<?php if (!$this->random_quote): ?>noquote<?php elseif ($this->random_quote['say_mode'] == 'public'): ?>quote_public<?php elseif ($this->random_quote['say_mode'] == 'team'): ?>quote_team<?php endif; ?>">
          <?php if (!$this->random_quote): ?>No quote available<?php else: ?><?php echo replace_color_codes(wordwrap(htmlspecialchars($this->random_quote['say_message']), 60, '<br \>', true)); ?><?php endif; ?>
       </td>
      </tr>

      <tr>
        <td>Kills</td>
        <td><?php echo $this->player_details['player_total_kills']; ?></td>
      </tr>
      <tr>
        <td>Team Kills</td>
        <td><?php echo $this->player_details['player_total_teamkills']; ?></td>
      </tr>
      <tr>
        <td>Suicides</td>
        <td><?php echo $this->player_details['player_selfkills']; ?></td>
      </tr>
      <tr>
        <td>Total Deaths</td>
        <td><?php echo $this->player_details['player_total_deaths']; ?></td>
      </tr>
      <tr>
        <td>Deaths by Enemy</td>
        <td><?php echo $this->player_details['player_deaths_by_enemy']; ?></td>
      </tr>
      <tr>
        <td>Deaths by Team</td>
        <td><?php echo $this->player_details['player_deaths_by_team']; ?></td>
      </tr>
      <tr>
        <td>Deaths by &lt;world&gt;</td>
        <td><?php echo $this->player_details['player_deaths_by_world']; ?></td>
      </tr>

      <tr>
        <td>Kill Efficiency</td>
        <td><?php echo replace_color_codes(htmlspecialchars($this->player_details['player_kill_efficiency'])); ?></td>
      </tr>
      <tr>
        <td>Destruction Efficiency</td>
        <td><?php echo replace_color_codes(htmlspecialchars($this->player_details['player_destruction_efficiency'])); ?></td>
      </tr>
      <tr>
        <td>Total Efficiency</td>
        <td><?php echo replace_color_codes(htmlspecialchars($this->player_details['player_total_efficiency'])); ?></td>
      </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col />
      <col width="60" />
      <col />
      <col width="60" />
    </colgroup>

    <thead>
      <?php $rows = max(count($this->prefered_weapons), count($this->destroyed_structures)); ?>
      <tr>
        <th colspan="2">Preferred Weapons</th>
        <th colspan="2">Destroyed Structures</th>
      </tr>
    </thead>

    <tbody>
      <?php for ($i=0; $i<$rows; $i++): ?>
        <?php
        $weapon    = (isset($this->prefered_weapons[$i]) ? $this->prefered_weapons[$i]: null);
        $structure = (isset($this->destroyed_structures[$i]) ? $this->destroyed_structures[$i]: null);
        ?>
        <tr>
          <?php if (is_null($weapon) && $i == 0): ?>
            <td colspan="2">No prefered weapons yet</td>
          <?php elseif (!is_null($weapon)): ?>
            <td><?php if (!empty($weapon['weapon_icon'])): ?><img src="images/icons/<?php echo $weapon['weapon_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$weapon['weapon_icon']); echo $attr; ?> <?php if ($width == 15): ?>style="margin-right: 15px;"<?php endif; ?> alt="<?php echo $weapon['weapon_name']; ?>" /> <?php endif; ?><?php echo $weapon['weapon_name']; ?></td>
            <td><?php echo $weapon['weapon_used'] ?></td>
          <?php else: ?>
            <td colspan="2">&nbsp;</td>
          <?php endif; ?>

          <?php if (is_null($structure) && $i == 0): ?>
            <td colspan="2">No destroyed structures yet</td>
          <?php elseif (!is_null($structure)): ?>
            <td><?php if (!empty($structure['building_icon'])): ?><img src="images/icons/<?php echo $structure['building_icon']; ?>" width="15" height="15" alt="<?php echo $structure['building_name']; ?>" /> <?php endif; ?><?php echo $structure['building_name']; ?></td>
            <td><?php echo $structure['building_destroyed'] ?></td>
          <?php else: ?>
            <td colspan="2">&nbsp;</td>
          <?php endif; ?>
        </tr>
      <?php endfor; ?>
    </tbody>
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
        <td><img src="_graph.php?type=kills_per_game&amp;player_id=<?php echo $this->player_details['player_id']; ?>" /></td>
      </tr>
    </tbody>
  </table>
</div>

<?php include '__footer__.tpl.php'; ?>

<?php include '__header__.tpl.php'; ?>

<div id="box">
  <div class="heading">
    <span class="heading"><h2>Game #<?php echo $this->game_details['game_id']; ?> Log</h2></span>
    <span class="headinglink"> ( <a href="game_details.php?game_id=<?php echo $this->game_details['game_id'] ?>">show game summary</a> )</span>
  </div>

  <table>
    <colgroup>
      <col class="levelshot" />
      <col class="item" />
      <col />
    </colgroup>

    <tbody>
     <th colspan="3">Game info</td>
     <tr>
      <td rowspan="10">
        <img width="160" height="120" alt="<?php echo htmlspecialchars($this->map['game_map_name']); ?>" src="_levelshot.php?map_id=<?php echo ($this->game_details['game_map_id']); ?>" />
      </td>
      <td><strong>Map Name</strong></td>
      <td><strong><a href="map_details.php?map_id=<?php echo $this->game_details['game_map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($this->map['game_map_name'])); ?></a></strong></td>
     </tr>
     <tr>
      <td>Winner</td>
      <td><?php echo $this->game_details['game_winner']; ?></td>
     </tr>
     <tr>
      <td>Game time</td>
      <td><?php echo $this->game_details['game_length']; ?></td>
     </tr>
     <tr>
      <td>Date</td>
      <td><?php echo $this->game_details['game_timestamp']; ?></td>
     </tr>
    </tbody>
  </table>

  <table>
    <colgroup>
      <col class="data" />
      <col class="datadouble" />
      <col class="weaponicon" />
      <col class="data" />
      <col />
    </colgroup>

    <tbody>
     <th>Time</td>
     <th>Player</td>
     <th></td>
     <th>Action</td>
     <th></td>
      <?php if (isset($this->logs) and count($this->logs)): ?>

      <?php foreach ($this->logs AS $log): ?>
       <tr class="list" >
        <?php if (isset($log['say_gametime'])): ?>
         <td><?php echo $log['say_gametime'] ?></td>
         <td class="playername"><a href="player_details.php?player_id=<?php echo $log['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['player_name'])) ?></a></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_<?php if ($log['say_mode'] == 'alien'): echo 'red'; elseif ($log['say_mode'] == 'human'): echo 'cyan'; elseif ($log['say_mode'] == 'spectator'): echo 'yellow'; else: echo 'green'; endif; ?>"><?php echo $log['say_message'] ?></span></td>

        <?php elseif (isset($log['kill_gametime'])): ?>
         <td><?php echo $log['kill_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['killer_id'])): ?><a href="player_details.php?player_id=<?php echo $log['killer_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['killer_name'])) ?></a><?php endif; ?></td>
         <td><?php if (!empty($log['weapon_icon'])): ?><img src="images/icons/<?php echo $log['weapon_icon']; ?>" alt="<?php echo $log['weapon_name']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['weapon_icon']); echo $attr; ?>><?php endif; ?></td>
         <td><?php if ($log['kill_type'] == 'team'): ?><span class="quakecolor_red">teamkilled</a><?php elseif ($log['kill_type'] == 'enemy'): ?>killed<?php elseif (!empty($log['weapon_icon'])): ?>killed<?php endif; ?></td>
         <td><?php if (!empty($log['victim_id'])): ?><a href="player_details.php?player_id=<?php echo $log['victim_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['victim_name'])) ?></a><?php endif; ?><?php if (empty($log['weapon_icon'])): ?> died by <?php echo $log['weapon_name']; endif;?></td>

        <?php elseif (isset($log['destruct_gametime'])): ?>
         <td><?php echo $log['destruct_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])): ?><a href="player_details.php?player_id=<?php echo $log['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['player_name'])) ?></a><?php endif; ?></td>
         <td><?php if (!empty($log['weapon_icon'])): ?><img src="images/icons/<?php echo $log['weapon_icon']; ?>" alt="<?php echo $log['weapon_name']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['weapon_icon']); echo $attr; ?>><?php endif; ?></td>
         <td><?php if ($log['weapon_team'] == $log['building_team'] ): ?><span class="quakecolor_yellow">destroyed</span> <?php else: ?>destroyed <?php endif; ?></td>
         <td><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?>
             <?php if( empty($log['weapon_icon'] )): ?> by <?php echo $log['weapon_name']; endif; ?>
         </td>

        <?php elseif (isset($log['build_gametime'])): ?>
         <td><?php echo $log['build_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])): ?><a href="player_details.php?player_id=<?php echo $log['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['player_name'])) ?></a><?php endif; ?></td>
         <td></td>
         <td><span class="quakecolor_green">built</span></td>
         <td><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?></td>

        <?php elseif (isset($log['decon_gametime'])): ?>
         <td><?php echo $log['decon_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['player_id'])): ?><a href="player_details.php?player_id=<?php echo $log['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['player_name'])) ?></a><?php endif; ?></td>
         <td></td>
         <td><span class="quakecolor_yellow">deconned</a></td>
         <td><img src="images/icons/<?php echo $log['building_icon']; ?>" <?php list($width, $height, $type, $attr) = getimagesize('images/icons/'.$log['building_icon']); echo $attr; ?>> <?php echo $log['building_name'] ?></td>

        <?php elseif (isset($log['vote_gametime'])): ?>
         <td><?php echo $log['vote_gametime'] ?></td>
         <td class="playername"><?php if (!empty($log['caller_id'])): ?><a href="player_details.php?player_id=<?php echo $log['caller_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['caller_name'])) ?></a><?php endif; ?></td>
         <td></td>
         <td><span class="quakecolor_magenta"><?php if ($log['vote_mode'] == 'public'): echo "vote"; else: echo "teamvote"; endif; ?></span></td>
         <td>
          <?php echo $log['vote_type']; ?>
          <?php if (!empty($log['victim_id'])): ?><a href="player_details.php?player_id=<?php echo $log['victim_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($log['victim_name'])) ?></a><?php endif; ?>
          <?php if (!empty($log['vote_arg'])): echo $log['vote_arg']; endif; ?>
        </td>

        <?php elseif (isset($log['endvote_gametime'])): ?>
         <td><?php echo $log['endvote_gametime'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_white"><?php if ($log['endvote_mode'] == 'public'): echo "vote"; else: echo "teamvote"; endif; ?></span></td>
         <td>
          <?php if( $log['endvote_pass'] == 'yes' ): echo "passed"; else: echo "failed"; endif; echo " ( " . $log['endvote_yes'] . " - " . $log['endvote_no'] . " )" ?>
        </td>

        <?php elseif (isset($log['game_length'])): ?>
         <td><?php echo $log['game_length'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_cyan">map end</span></td>
         <td><b><?php echo $log['game_winner'] ?> <?php if ($log['game_winner'] == 'aliens' || $log['game_winner'] == 'humans'): ?> win<?php endif;?></b></td>

        <?php elseif (isset($log['misc_gametime'])): ?>
         <td><?php echo $log['misc_gametime'] ?></td>
         <td></td>
         <td></td>
         <td><span class="quakecolor_cyan"><?php echo $log['misc_action'] ?></span></td>
         <td><?php echo $log['misc_text'] ?></td>
        <?php endif; ?>

       </tr>
      <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">No action occured</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

 </div>

 <?php include '__footer__.tpl.php'; ?>

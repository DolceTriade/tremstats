<?php include '__header__.tpl.php'; ?>

<div id="box">
  <h2>Map Balance by Wins</h2>

  <table>
    <colgroup>
      <col class="item" />
      <col class="balancebar" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>Map</th>
        <th></th>
        <th>Games</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($this->maps_by_wins AS $map): ?>
        <tr>
          <td>
            <a href="map_details.php?map_id=<?php echo $map['map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($map['map_text_name'])); ?></a>
          </td>
          <td>
            <img width="400" height="30" alt="Balance" title="Alien Wins: <?php echo $map['mapstat_alien_wins']; ?>, Human Wins: <?php echo $map['mapstat_human_wins']; ?>, Ties: <?php echo $map['ties'];?>" src="_graph.php?type=balance_bar&amp;a=<?php echo ($map['mapstat_alien_wins']); ?>&amp;b=<?php echo ($map['ties']); ?>&amp;c=<?php echo ($map['mapstat_human_wins']); ?>" />
          </td>
          <td><?php echo $map['mapstat_alien_wins'] + $map['mapstat_human_wins'] + $map['ties']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="legend">
    <colgroup>
      <col class="item"/>
    </colgroup>

    <thead>
      <tr>
        <th>Legend</th>
      </tr>
    </thead>

    <tbody>
        <tr><td class="a">Alien Wins</td></tr>
        <tr><td class="b">Ties / Draws</td></tr>
        <tr><td class="c">Human Wins</td></tr>
    </tbody>
  </table>

  <h2>Map Balance by Kills</h2>

  <table>
    <colgroup>
      <col class="item" />
      <col class="balancebar" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>Map</th>
        <th></th>
        <th>Kills</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($this->maps_by_kills AS $map): ?>
        <tr>
          <td>
            <a href="map_details.php?map_id=<?php echo $map['map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($map['map_text_name'])); ?></a>
          </td>
          <td>
            <img width="400" height="30" alt="Balance" title="Alien Kills: <?php echo $map['mapstat_alien_kills'];?>, Human Kills: <?php echo $map['mapstat_human_kills']; ?>" src="_graph.php?type=balance_bar&amp;a=<?php echo ($map['mapstat_alien_kills']); ?>&amp;c=<?php echo ($map['mapstat_human_kills']); ?>" />
          </td>
          <td><?php echo $map['mapstat_alien_kills'] + $map['mapstat_human_kills']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="legend">
    <colgroup>
      <col class="item"/>
    </colgroup>

    <thead>
      <tr>
        <th>Legend</th>
      </tr>
    </thead>

    <tbody>
        <tr><td class="a">Alien Kills</td></tr>
        <tr><td class="c">Human Kills</td></tr>
    </tbody>
  </table>

  <h2>Map Balance by Deaths</h2>

  <table>
    <colgroup>
      <col class="item" />
      <col class="balancebar" />
      <col class="data" />
    </colgroup>

    <thead>
      <tr>
        <th>Map</th>
        <th></th>
        <th>Deaths</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($this->maps_by_deaths AS $map): ?>
        <tr>
          <td>
            <a href="map_details.php?map_id=<?php echo $map['map_id'] ; ?>"><?php echo replace_color_codes(htmlspecialchars($map['map_text_name'])); ?></a>
          </td>
          <td>
            <img width="400" height="30" alt="Balance" title="Alien Deaths: <?php echo $map['mapstat_alien_deaths'];?>, Human Deaths: <?php echo $map['mapstat_human_deaths']; ?>" src="_graph.php?type=balance_bar&amp;a=<?php echo ($map['mapstat_alien_deaths']); ?>&amp;c=<?php echo ($map['mapstat_human_deaths']); ?>" />
          </td>
          <td><?php echo $map['mapstat_alien_deaths'] + $map['mapstat_human_deaths']; ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <table class="legend">
    <colgroup>
      <col class="item"/>
    </colgroup>

    <thead>
      <tr>
        <th>Legend</th>
      </tr>
    </thead>

    <tbody>
        <tr><td class="a">Alien Deaths</td></tr>
        <tr><td class="c">Human Deaths</td></tr>
    </tbody>
  </table>

 </div>

 <?php include '__footer__.tpl.php'; ?>

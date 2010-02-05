<?php include '__header__.tpl.php'; ?>

<?php $columns=5;
      if(isset($this->order_name)):
        $columns+=1;
      endif;
      if(isset($this->map_id)):
        $game_search="&map_id=".$this->map_id;
      else:
        $game_search="";
      endif;
?>

<div id="box"
  <div id="box_header">
    <span class="box_title"><h2><?php if(isset($this->order_name)): echo "Games with most ".$this->order_name; else: echo "Recent Games"; endif; ?></h2></span>
    <span class="box_select">
      Sort by: <a href="games.php<?php if(isset($this->map_id)): echo "?".$this->map_id; endif; ?>">Game ID</a> | <a href="games.php?order=kills<?php echo $game_search; ?>">Kills</a> | <a href="games.php?order=teamkills<?php echo $game_search; ?>">Teamkills</a> | <a href="games.php?order=deaths<?php echo $game_search; ?>">Deaths</a>
    </span>
  </div>

  <table>
    <colgroup>
      <col width="50" />
      <col width="100" />
      <col width="120" />
      <col width="40" />
      <col width="60" />
      <?php if(isset($this->order_name)): ?>
        <col width="50" />
      <?php endif; ?>
    </colgroup>

    <thead>
      <tr>
        <th>Game ID</th>
        <th>Date</th>
        <th>Map</th>
        <th>Time</th>
        <th>Winner</th>
        <?php if(isset($this->order_name)): ?>
          <th><?php echo $this->order_name; ?></th>
        <?php endif; ?>
      </tr>
    </thead>

    <tfoot>
      <tr>
        <td colspan="<?php echo $columns; ?>">
          Pages: <?php echo $this->pagelister->GetHTML(); ?>
        </td>
      </tr>
    </tfoot>

    <tbody>
      <?php foreach ($this->games AS $game): ?>
        <tr>
          <td><a href="game_details.php?game_id=<?php echo $game['game_id']; ?>"><?php echo $game['game_id']; ?></a></td>
          <td><?php echo $game['game_timestamp']; ?></td>
          <td><?php echo replace_color_codes(htmlspecialchars($game['game_map_name'])); ?></td>
          <td align="right"><?php echo $game['game_length']; ?></td>
          <td align="center"><?php echo $game['game_winner']; ?></td>
          <?php if(isset($this->order_name)): ?>
            <td><?php echo $game['stats_count']; ?></td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>

      <?php if (!count($this->games)): ?>
        <tr>
          <td colspan="<?php echo $columns; ?>">No games yet</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
 </div>

 <?php include '__footer__.tpl.php'; ?>

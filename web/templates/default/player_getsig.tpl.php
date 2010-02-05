<?php include '__header__.tpl.php'; ?>

<?php
 $styles = array (
   array ( 'id' => '0', 'width' => 400, 'height' => 46 ),
   array ( 'id' => '1', 'width' => 500, 'height' => 40 ),
   array ( 'id' => '2', 'width' => 500, 'height' => 40 ),
 );
?>

<div id="box">
  <h2>Player Signature for <a href="player_details.php?player_id=<?php echo $this->player_details['player_id']; ?>"><?php echo replace_color_codes(htmlspecialchars($this->player_details['player_name'])); ?></a></h2>

  <table>
    <colgroup>
      <col class="item" />
      <col />
    </colgroup>

    <?php foreach ($styles AS $style):
      $base      = "http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], "/")+1);
      $playerurl = $base . "player_details.php?player_id=" . $this->player_details['player_id'];
      $sigurl    = $base . "player_sig.php?player_id=" . $this->player_details['player_id'] . "&amp;style=" . $style['id'];
      $imgurl    = "&lt;img src=&quot;" . $sigurl . "&quot; width=&quot;" . $style['width'] . "&quot; height=&quot;" . $style['height'] . "&quot;&gt;";
      $linkurl   = "&lt;a href=&quot;" . $playerurl ."&quot;&gt;" . $imgurl . "&lt;/a&gt;";
      $bbcode    = "[url=" . $playerurl . "][img]" . $sigurl . "[/img][/url]";
    ?>

    <thead>
      <tr>
        <th colspan="2">Style <?php echo $style['id']; ?></th>
      </tr>
    </thead>

    <tbody class="padded" >
      <tr>
        <td>Sample</td>
        <td><?php echo "<img src=\"player_sig.php?player_id=" . $this->player_details['player_id'] . "&amp;style=" . $style['id'] . "\" width=\"" . $style['width'] . "\" height=\"" . $style['height'] . "\" >"; ?></td>
      </tr>
      <tr>
        <td>URL to image</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $sigurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>HTML for image</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $imgurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>HTML with link</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $linkurl; ?>" style="width:100%" ></td>
      </tr>
      <tr>
        <td>BBCODE</td>
        <td><input class="sig" type="text" readonly="readonly" value="<?php echo $bbcode; ?>" style="width:100%" ></td>
      </tr>
    </tbody>

    <?php endforeach; ?>
  </table>

</div>

<?php include '__footer__.tpl.php'; ?>

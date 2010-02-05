<?php
/**
 * Project:     Tremstats
 * File:        player_sig.php
 *
 * For licence and version information, see /index.php
 */

require_once 'core/init.inc.php';

header("Content-type: image/png");

if (!isset($_GET['player_id'])) {
  die('No player id given');
}

// basic info
$servername = "^1House of ^2Nuts";

switch( $_GET['style'] ) {
  case 1:
  case 2:
    $w = 500;
    $h = 40;
    break;
  default:
    $w = 400;
    $h = 46;
    break;
}

// Get the data
$stats = $db->GetRow("SELECT player_name,
                             player_total_kills,
                             player_total_deaths
                      FROM players
                      WHERE player_id = ?",
                      array($_GET['player_id']));

$random_quote = $db->GetRow("SELECT say_mode, say_message
                             FROM says
                             WHERE say_player_id = ?
                             ORDER BY RAND()
                             LIMIT 0, 1",
                             array($_GET['player_id']));

$colors = array(
  '0' => array( 0 => 60, 60, 60 ),  // Black
  '1' => array( 0 => 255, 0, 0 ),    // Red
  '2' => array( 0 => 0, 255, 0 ),  // Green
  '3' => array( 0 => 255, 255, 0 ), // Yellow
  '4' => array( 0 => 0, 0, 255 ),   // Blue
  '5' => array( 0 => 0, 255, 255 ),   // Cyan
  '6' => array( 0 => 255, 0, 255 ),   // Pink
  '7' => array( 0 => 255, 255, 255 ),  // White
);

$im = imagecreatetruecolor($w, $h);

for($i = 0; $i < 8; $i++)
{
  $colors[$i]['alloc'] = imagecolorallocate($im, $colors[$i][0], $colors[$i][1], $colors[$i][2]);
  $colors[$i]['alpha'] = imagecolorallocatealpha($im, $colors[$i][0], $colors[$i][1], $colors[$i][2], 64);
}

function color_print( $x, $y, $string, $im, $colors, $size, $alpha ) {
  if($alpha == 1) {
    $color = $colors['7']['alpha'];
  } else {
    $color = $colors['7']['alloc'];
  }

  for ($i = 0, $l = strlen($string); $i < $l; $i++) {
    $c = $string[$i];

    if($c == '^') {
      if($i < $l) {
        $i++;
        $c = $string[$i];
        if(!array_key_exists($c, $colors)) {
          $c = ((ord($c)) - (ord('0'))) & 7;
        }
        if(array_key_exists($c, $colors)) {
          if($alpha == 1) {
            $color = $colors[$c]['alpha'];
          } else {
            $color = $colors[$c]['alloc'];
          }
        }
      }
      continue;
    }
    imagechar( $im, $size, $x, $y, $c, $color );
    switch( $size ) {
      case 1:
        $x += 5;
        break;
      case 2:
        $x += 6;
        break;
      case 3:
        $x += 7;
        break;
      case 4:
        $x += 8;
        break;
      default:
        $x += 9;
        break;
    }
  }
}

switch( $_GET['style'] ) {
 case 1:
 case 2:
  if( $_GET['style'] == 1 ) {
    $b = imagecreatefrompng( "images/background1.png" );
    $ny = 4;
    $ty = 4;
  } else {
    $b = imagecreatefrompng( "images/background2.png" );
    $ny = 8;
    $ty = 6;
  }
  imagecopy( $im, $b, 0, 0, 0, 0, $w, $h );

  color_print( 10, $ny, $stats['player_name'], $im, $colors, 5, 0 );
  color_print( 370, 19, $servername, $im, $colors, 5, 1 );

  $msg = "^2".substr( $random_quote['say_message'], 0, 70 );
  color_print( 14, 24, $msg, $im, $colors, 1, 1 );

  $colorstat = imagecolorallocatealpha($im, 0xFF, 0xFF, 0xEE, 40);

  imagestring( $im, 5, 220, $ty, "Frags:", $colorstat );
  imagestring( $im, 5, 280, $ty, $stats['player_total_kills'], $colorstat );

  imagestring( $im, 5, 370, $ty, "Fubars:", $colorstat );
  imagestring( $im, 5, 440, $ty, $stats['player_total_deaths'], $colorstat );

  break;
default:
  $background = imagecolorallocate($im, 0x22, 0x26, 0x2a);
  imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $background);

  $border1 = imagecolorallocate($im, 0x57, 0x61, 0x6b);
  $border2 = imagecolorallocate($im, 0x04, 0x05, 0x05);

  imageline($im, 0, 0, $w - 1, 0, $border1);
  imageline($im, 0, 0, 0, $h - 1, $border1);
  imageline($im, 0, $h - 1, $w - 1, $h - 1, $border2);
  imageline($im, $w - 1, 0, $w - 1, $h - 1, $border2);

  color_print( 10, 5, $stats['player_name'], $im, $colors, 5, 0 );
  color_print( 10, 25, $servername, $im, $colors, 5, 1 );

  $colorstat = imagecolorallocatealpha($im, 0xFF, 0xFF, 0xFF, 40);

  imagestring( $im, 5, 250, 5, "Kills:", $colorstat );
  imagestring( $im, 5, 320, 5, $stats['player_total_kills'], $colorstat );

  imagestring( $im, 5, 250, 25, "Deaths:", $colorstat );
  imagestring( $im, 5, 320, 25, $stats['player_total_deaths'], $colorstat );

  break;
}


imagepng($im);
imagedestroy($im);

?>

<?php
/**
 * Project:     Tremstats
 * File:        lib.inc.php
 *
 * For licence and version information, see /index.php
 */

function AdoDB_Count_Handler ($query, $args) {
  $rs = $args[0]->Execute($query);

  $entriesTotal = $rs->RecordCount();
  $rs->Close();

  return $entriesTotal;
}

function replace_color_codes ($string) {
  // Define colors for codes
  $colors = array(
    '0' => 'black',  // Black
    '1' => 'red',    // Red
    '2' => 'green',  // Green
    '3' => 'yellow', // Yellow
    '4' => 'blue',   // Blue
    '5' => 'cyan',   // Cyan
    '6' => 'pink',   // Pink
    '7' => 'white',  // White
  );

  // Search first token
  $pos = strpos($string, '^');

  // If there is no token, return the original string
  if ($pos === false) return $string;

  // Get first part
  if ($pos === 0) {
    $result = '';
  } else {
    $result = substr($string, 0, $pos);
  }

  // Loop through all tokens
  $color_open = false;
  while ($pos !== false) {
    $next = strpos($string, '^', $pos+1);
    if ($next === false) {
      $part = substr($string, $pos+1);
    } else {
      $part = substr($string, $pos+1, $next-$pos-1);
    }

    if (!$part) {
      $result .= '^';
    } else {
      // Get first character after the token
      $num = substr($part, 0, 1);

      // Check if the first character exists in the color array
      if (!array_key_exists($num, $colors)) {
        $num = ((ord($num)) - (ord('0'))) & 7;
      }

      if ($color_open) $result .= '</span>';

      $result .= '<span class="quakecolor_'.$colors[$num].'">'.substr($part, 1);
      $color_open = true;
    }

    // Get next token
    $pos = strpos($string, '^', $pos+1);
  }
  if ($color_open) $result .= '</span>';

  return $result;
}

function custom_sort ($sort_title, $sort_name) {
  $current_file = $_SERVER['SCRIPT_NAME'];
  $custom_sort  = (isset($_GET['custom_sort']) ? $_GET['custom_sort']: null);
  $custom_dir   = (isset($_GET['custom_dir']) ? $_GET['custom_dir']: 'desc');

/*
  if ($return) {
    return array(
      'custom_sort' => $custom_sort,
      'custom_dir'  => $custom_dir
    );
  }
*/

  if ($custom_sort == $sort_name) {
    $new_dir = ($custom_dir == 'desc' ? 'asc': 'desc');

    $arrow = ($custom_dir == 'desc' ? '↑': '↓');
  } else {
    $arrow = '';
    $new_dir = 'desc';
  }

  $additional_string = '';
  if (is_array($_GET)) {
    foreach ($_GET AS $key => $value) {
      if ($key == 'custom_sort' || $key == 'custom_dir') continue;

      $additional_string .= '&amp;'.htmlspecialchars($key).'='.htmlspecialchars(($value));
    }
  }

  return '<a href="'.$current_file.'?custom_sort='.urlencode($sort_name).'&amp;custom_dir='.$new_dir.$additional_string.'">'.$arrow.' '.$sort_title.'</a>';
}

function get_custom_sort ($custom_orders, $default_order) {
  $custom_sort  = (isset($_GET['custom_sort']) ? $_GET['custom_sort']: null);
  $custom_dir   = (isset($_GET['custom_dir']) ? $_GET['custom_dir']: 'asc');

  if (!in_array($custom_dir, array('asc', 'desc'))) $custom_dir = 'asc';

  if (is_null($custom_sort)) {
    $_GET['custom_sort'] = $default_order;
    $_GET['custom_dir']  = 'asc';

    return $custom_orders[$default_order].' ASC';
  } else {
    if (!array_key_exists($custom_sort, $custom_orders)) {
      $_GET['custom_sort'] = $default_order;
      $_GET['custom_dir']  = 'asc';

      return $custom_orders[$default_order].' ASC';
    } else {
      return $custom_orders[$custom_sort].' '.strtoupper($custom_dir);
    }
  }
}
?>

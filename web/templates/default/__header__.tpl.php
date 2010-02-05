<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
  <head>
    <title>Tremstats</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Publisher" content="DASPRiD's" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->css_file(); ?>" />
  </head>

  <body>
    <div id="header">
      <form class="search" method="get" accept-charset="utf-8" action="search_player.php">
        <fieldset>
          <label for="query">Player search:</label>
          <input type="text" name="query" id="query" value="<?php if (isset($_GET['query'])): ?><?php echo htmlspecialchars($_GET['query'],ENT_QUOTES) ?><?php endif; ?>" />
          <input type="submit" value="search" />
        </fieldset>
      </form>

      <h1>Tremstats<sup class="too">(Too)</sup><span class="for"><br></span><?php echo replace_color_codes(TREMULOUS_SERVER_NAME); ?></h1>
    </div>

    <ul class="menu">
      <li><a href="index.php">Overview</a>
      </li><li><a href="top_players.php">Top Players</a>
      </li><li><a href="top_feeders.php">Feeders</a>
      </li><li><a href="top_teamkillers.php">Team Killers</a>
      </li><li><a href="most_active_players.php">Most Active Players</a>
      </li><li><a href="votes.php">Votes</a>
      </li><li><a href="most_played_maps.php">Maps</a>
      </li><li><a href="map_balance.php">Balance</a>
      </li><li><a href="games.php">Games</a></li>
    </ul>

    <?php include '__pagelister__.php'; ?>

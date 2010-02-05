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
      <h1>Tremstats</h1>

      <form method="get" accept-charset="utf-8" action="search_player.php">
        <fieldset>
          <label for="query">Player search:</label>
          <input type="text" name="query" id="query" value="<?php if (isset($_GET['query'])): ?><?php echo htmlspecialchars($_GET['query']) ?><?php endif; ?>" />
          <input type="submit" value="search" />
        </fieldset>
      </form>
    </div>

    <ul id="menu">
      <li><a href="index.php">Overview</a></li>
      <li><a href="top_players.php">Top Players</a></li>
      <li><a href="top_feeders.php">Feeders</a></li>
      <li><a href="top_teamkillers.php">Teamkillers</a></li>
      <li><a href="most_active_players.php">Most Active Players</a></li>
      <li><a href="most_played_maps.php">Most Played Maps</a></li>
      <li><a href="games.php">Games</a></li>
    </ul>

    <?php include '__pagelister__.php'; ?>

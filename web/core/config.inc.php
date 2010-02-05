<?php
/**
 * Project:     Tremstats
 * File:        config.inc.php
 *
 * For license and version information, see /index.php
 */


/**
 * The MySQL Hostname
 */
define('MYSQL_HOSTNAME', '');

/**
 * MySQL Username
 */
define('MYSQL_USERNAME', '');

/**
 * MySQL Password
 */
define('MYSQL_PASSWORD', '');

/**
 * Database to use
 */
define('MYSQL_DATABASE', '');


/**
 * Template to use from /templates
 */
define('TREMSTATS_TEMPLATE', 'default');

/**
 * Skin to use from /css/%template%/
 */
define('TREMSTATS_SKIN', 'default');

// Entries per page
define('TREMSTATS_EPP', 50);


/**
 * Tresholds
 */
// Min games played to appear in a list
define('TRESHOLD_MIN_GAMES_PLAYED', 25);

// Max games paused to still appear in a list
define('TRESHOLD_MAX_GAMES_PAUSED', 500);

// Tremulous server name
define('TREMULOUS_SERVER_NAME', 'Tremulous 1.2 server');

/**
 * Address of the tremulous server to report from, should
 * be something like 'serverip:port'. The default Tremulous
 * port is 30720, so if you want to watch your local server,
 * use 'localhost:30720'.
 */
define('TREMULOUS_ADDRESS', '');

/**
 * Privacy settings
 * set to 1 to hide information
 */
// Disable games log
define('PRIVACY_LOGS', '0');

// Hide chat in games log
define('PRIVACY_CHAT', '0');

// Hide random quote
define('PRIVACY_QUOTE', '0');

// Hide alias names in player details
define('PRIVACY_NAME', '0');

?>

<?php
/**
 * Project:     Tremstats
 * File:        init.inc.php
 *
 * For license and version information, see /index.php
 */

$calculation_start = microtime(true);

define('VERSION', '2.0.0');

require_once dirname(__FILE__).'/config.inc.php';
require_once dirname(__FILE__).'/lib.inc.php';
require_once dirname(__FILE__).'/tiny_templating.class.php';
require_once dirname(__FILE__).'/adodb/adodb-exceptions.inc.php';
require_once dirname(__FILE__).'/adodb/adodb.inc.php';
require_once dirname(__FILE__).'/pagelister/PageLister.class.php';

// Connect to MySQL
try {
  $db = NewADOConnection('mysql');
  $db->Connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
} catch (exception $e) {
  die;
}

// Set the page lister
$pagelister = new PageLister();
$pagelister->SetEntriesPerPage(TREMSTATS_EPP);

$counthandler = new PageLister_CountHandler();
$counthandler->SetHandler('AdoDB_Count_Handler');
$counthandler->SetArgs(array($db));

$pagelister->SetCountHandler($counthandler);

// Initiate the template engine
$tpl = new tiny_templating(TREMSTATS_TEMPLATE, TREMSTATS_SKIN);
$tpl->assign('calculation_start', $calculation_start);
$tpl->assign('pagelister',        $pagelister);
?>

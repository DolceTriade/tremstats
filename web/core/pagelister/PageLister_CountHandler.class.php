<?php
/**
 * Project:     PageLister
 * File:        PageLister_CountHandler.class.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @link      http://dev.dasprids.de/
 * @copyright 2006 DASPRiD's
 * @author    Ben 'DASPRiD' Scholzen <mail@dasprids.de>
 * @package   PageLister
 * @version   0.1.4
 */

/**
 * Count handler for entries
 *
 * @package PageLister
 */
class PageLister_CountHandler {
  /**
   * Handler function to count entries.
   *
   * @var string
   */
  private $handler = null;

  /**
   * Custom arguments for the count function
   *
   * @var array
   */
  private $args = array();


  /**
   * Creates a default handler on-the-fly.
   *
   * If you do not need a custom handler, you can use this static function
   * to create a default handler. at the moment, MySQL and MySQLi is supported.
   * If you need you own handler, you can use <b>SetHandler()</b>.
   *
   * You can pass additional arguments to every default handler. MySQL can have
   * a link identifier, if you don't work with a single connection. For MySQLi,
   * you have to deliver the resource of the MySQLi-object. So there are three
   * options at the moment:
   *
   * <code>
   * $handler = PageLister::CountHandler('MySQL');
   * $handler = PageLister::CountHandler('MySQL', $db);
   * $handler = PageLister::CountHandler('MySQLi', $db);
   * </code>
   *
   * @param string $handler
   * @return object
   */
  public static function DefaultHandler ($handler) {
    $args = array_slice(func_get_args(), 1);

    $countHandler = new PageLister_CountHandler;

    if (!method_exists($countHandler, 'DefaultHandler_'.$handler)) {
      trigger_error('PageLister_CountHandler: Invalid default handler.', E_USER_ERROR);
      return false;
    }

    call_user_func(array('PageLister_CountHandler', 'DefaultHandler_'.$handler), $countHandler, $args);

    return $countHandler;
  }

  /**
   * Sets a custom handler for counting.
   *
   * You may want to use you own handler for the counting. You cann pass a function
   * name, which should be called for this. PageLister_CountHandler will then call
   * those function with one argument, the query.
   *
   * @param string $function
   * @return boolean
   */
  public function SetHandler ($function) {
    if (!function_exists($function)) {
      trigger_error('PageLister_CountHandler: Function does not exist.', E_USER_ERROR);
      return false;
    }

    $this->handler = $function;

    return true;
  }

  /**
   * Sets arguments for calling the count function
   *
   * If you add a custom handler, you may want to add custom args to the function,
   * which you cant deliver as strings, such as resources. Default handlers will
   * use this function by themself, so don't call it after creating a default
   * handler.
   *
   * @param array $args
   * @return boolean
   */
  public function SetArgs ($args) {
    if (!is_array($args)) {
      trigger_error('PageLister_CountHandler: Argument has to be an array.', E_USER_ERROR);
      return false;
    }

    $this->args = $args;

    return true;
  }

  /**
   * Default handler for MySQL.
   *
   * @param resource $handler
   * @param array $args
   * @return boolean
   */
  public static function DefaultHandler_MySQL ($handler, $args) {
    $handler->SetArgs($args);
    
    if (isset($args[0])) {
      $mysql_query_addition = ', $args[0]';
    } else {
      $mysql_query_addition = '';
    }

    $handler->SetHandler(
      create_function('$query, $args',
                      '$mysqlQuery = mysql_query($query'.$mysql_query_addition.')
                                     or trigger_error(\'PageLister_CountHandler: SQL-Error: \'.mysql_error().\'\', E_USER_ERROR);
                       $entriesTotal = mysql_num_rows($mysqlQuery);
                       return $entriesTotal;
      ')
    );

    return true;
  }

  /**
   * Default handler for MySQLi.
   *
   * @param resource $handler
   * @param array $args
   * @return boolean
   */
  public static function DefaultHandler_MySQLi ($handler, $args) {
    try {
      if (!isset($args[0])) {
        throw new Exception();
      }
      
      if (!is_resource($args[0])) {
        throw new Exception();
      }
    } catch (Exception $e) {
      trigger_error('PageLister_CountHandler: Argument 1 has to be a ressource.', E_USER_ERROR);
      return false;
    }

    $handler->SetArgs(array('result', $args[0]));

    $handler->SetHandler(
      create_function('$query, $args',
                      '$result = $args["result"]->query($query)
                                 or trigger_error(\'PageLister_CountHandler: SQL-Error: \'.mysqli_error().\'\', E_USER_ERROR);
                       $entriesTotal = $result->num_rows;
                       $result->close();
                       return $entriesTotal;
      ')
    );

    return true;
  }

  /**
   * Returns the total amount of entries.
   *
   * After setting an handler, this method will count the total amount of
   * entries for the given query.
   *
   * @param string $query
   * @return int
   */
  public function CountTotalEntries ($query) {
    if (is_null($this->handler)) {
      trigger_error('PageLister_CountHandler: No handler set yet.', E_USER_ERROR);
      return false;
    }

    $entries = call_user_func($this->handler, $query, $this->args);

    return $entries;
  }
}
?>
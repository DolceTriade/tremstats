<?php
/**
 * Project:     PageLister
 * File:        PageLister.class.php
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
 * Include the handlers
 */
require_once 'PageLister_CountHandler.class.php';
require_once 'PageLister_HtmlHandler.class.php';


/**
 * @package PageLister
 */
class PageLister {
  /**
   * Name of the pagenumbers in $_GET.
   *
   * @var string
   */
  private $getName = 'pagenumber';

  /**
   * The URL to link to.
   *
   * @var string
   */
  private $url = null;

  /**
   * Total entries per page.
   *
   * @var int
   */
  private $entriesPerPage  = 30;

  /**
   * The query to execute.
   *
   * @var string
   */
  private $query = null;

  /**
   * The count handler for the entries.
   *
   * @var resource
   */
  private $countHandler = null;

  /**
   * The html handler for the output.
   *
   * @var resource
   */
  private $htmlHandler = null;

  /**
   * Wether calculation is done or not.
   *
   * @var boolean
   */
  private $calculationDone = false;

  /**
   * Total entries.
   *
   * @var int
   */
  private $entriesTotal;

  /**
   * Total pages.
   *
   * @var int
   */
  private $pages;

  /**
   * The current page.
   *
   * @var int
   */
  private $currentPage;

  /**
   * The limit-offset for the query.
   *
   * @var int
   */
  private $limitOffset;


  /**
   * Sets the handler to count all entries.
   *
   * The PageLister uses an external handler for counting all entries. This way
   * don't have to define everytime a new handler, when you want to use more than
   * one PageLister.
   *
   * @param resource $resource
   * @return boolean
   */
  public function SetCountHandler ($object) {
    if (!is_object($object)) {
      trigger_error('PageLister: This is no object.', E_USER_ERROR);
      return false;
    }

    if (!$object instanceof PageLister_CountHandler) {
      trigger_error('PageLister: This is no instance of PageLister_CountHandler.', E_USER_ERROR);
      return false;
    }

    $this->countHandler = clone $object;

    return true;
  }

  /**
   * Sets the handler to output the html.
   *
   * The PageLister uses an external handler for outputting the HTML. This way
   * don't have to define everytime a new handler, when you want to use more than
   * one PageLister.
   *
   * @param resource $resource
   * @return boolean
   */
  public function SetHtmlHandler ($object) {
    if (!is_object($object)) {
      trigger_error('PageLister: This is no resource.', E_USER_ERROR);
      return false;
    }

    if (!$object instanceof PageLister_HtmlHandler) {
      trigger_error('PageLister: This is no instance of PageLister_HtmlHandler.', E_USER_ERROR);
      return false;
    }

    $this->htmlHandler = clone $object;

    return true;
  }

  /**
   * Sets the URL, where the HTML-Code should link to.
   *
   * If you want to link to another URL than the current one, you can specify
   * an URL with this function and change the name of the pagenumber in <var>$_GET</var>.
   * If you only want to change the name of the pagenumber, you may pass `null`
   * as URL. If you use searchengine-friendly URLS, you can pass an URL like:
   *
   * http://www.something.com/articles/page_%PN%/
   *
   * The %PN% will be replaced by the pagenumber in the HTML-Code. If there is
   * no placeholder for the pagenumber in the URL, PageLister will automaticaly
   * add the pagenumber to the end of the GET-variables.
   *
   * @param string $url
   * @param string $getName
   * @return boolean
   */
  public function SetURL ($url, $getName = 'pagenumber') {
    $this->url     = $url;
    $this->getName = $getName;

    return true;
  }

  /**
   * Sets entries per page.
   *
   * If you want to display more or less entries than defaultvalue, you
   * can change the amount with this method.
   *
   * @param int $entries
   * @return boolean
   */
  public function SetEntriesPerPage ($entries) {
    if ($this->calculationDone) {
      trigger_error('PageLister: Calculation done, you may not modifie this settings yet.', E_USER_ERROR);
      return false;
    }

    $this->entriesPerPage = max(1, (int)$entries);

    return true;
  }

  /**
   * Sets the Query to count.
   *
   * This method must be called before you call any of the Get-methods. Here you
   * can set the query to count. Remember, to not add an <i>LIMIT x,x</i> to the query,
   * this will be automaticaly done by the PageLister. The query will later be
   * handled by PageLister_CountHandler.
   *
   * @param string $query
   * @return boolean
   */
  public function SetQuery ($query) {
    if ($this->calculationDone) {
      trigger_error('PageLister: Calculation done, you may not modifie this settings yet.', E_USER_ERROR);
      return false;
    }

    $this->query = $query;

    return true;
  }

  /**
   * Returns the calculated HTML-Code.
   *
   * After setting the query, you may want to get the HTML-Code. To do this,
   * you have to define a HTML-handler with the method SetHtmlHandler(). You
   * may change the HTML-handler after calling this and get different outputs
   * for the pagelist.
   *
   * @return string
   */
  public function GetHTML () {
    // Do calculation, of not done yet
    if (!$this->calculationDone) {
      $this->DoCalculation();
    }

    // If the user has not give us an URL, take the REQUEST_URI
    if (is_null($this->url)) {
      $preparedURL = $this->PrepareURL($_SERVER['REQUEST_URI']);
    } else {
      $preparedURL = $this->PrepareURL($this->url);
    }


    $html = $this->htmlHandler->GenerateHTML($this->pages, $this->currentPage, $preparedURL);

    return $html;
  }

  /**
   * Returns the modified query.
   *
   * This method returns the modified query you may use to get the entries of
   * the current page. If you selected MySQLi as handler, you may use this as
   * following:
   *
   * <code>
   * $query  = $PageLister->GetQuery();
   * $result = $mysqli->query($query);
   * </code>
   *
   * @return string
   */
  public function GetQuery () {
    // Do calculation, of not done yet
    if (!$this->calculationDone) {
      $this->DoCalculation();
    }

    return $this->query.' LIMIT '.$this->limitOffset.','.$this->entriesPerPage;
  }

  /**
   * Returns the total entries.
   *
   * @return int
   */
  public function GetTotalEntries () {
    // Do calculation, of not done yet
    if (!$this->calculationDone) {
      $this->DoCalculation();
    }

    return $this->entriesTotal;
  }

  /**
   * Returns a prepared URL.
   *
   * This method will verify and/or modify an URL, so it fits the needs of
   * the HTML-handler.
   *
   * @param string $url
   * @return string
   */
  private function PrepareURL ($url) {
    // If this is still a prepared URL, return it as it is
    if (strpos($url, '%PN%') !== false) {
      return $url;
    } else {
      // Check if there is an anker given
      if (preg_match("#(\\#.*)$#", $url, $ankerResult)) {
        $anker = $ankerResult[1];
      } else {
        $anker = '';
      }
      $url = preg_replace("#(\\#.*)$#", '', $url);

      // Parse the GetName, if needed, out
      $url = preg_replace("#(\\?|&)".preg_quote($this->getName, '#')."=([^&\\#]*)#", '', $url);

      // Check wether you hase to use ? oder &
      if (strpos($url, '?') === false) {
        return $url.'?'.$this->getName.'=%PN%'.$anker;
      } else {
        return $url.'&'.$this->getName.'=%PN%'.$anker;
      }
    }
  }

  /**
   * Calculate the things, which the Get*-methods need.
   *
   * Here we calculate the most important things, like total entries, how
   * many pages we have, the current page and the offset for the query.
   *
   * @return boolean
   */
  private function DoCalculation () {
    // Check if the user has give us a query
    if (is_null($this->query)) {
      trigger_error('PageLister: No query given. Use SetQuery() first.', E_USER_ERROR);
    }

    // Get total entries
    $this->entriesTotal = $this->countHandler->CountTotalEntries($this->query);

    // Calculate some data
    $this->pages       = ceil($this->entriesTotal / $this->entriesPerPage);
    $this->currentPage = max(1, min($this->pages, (isset($_GET[$this->getName]) ? (int)$_GET[$this->getName]: 1)));
    $this->limitOffset = ($this->currentPage - 1) * $this->entriesPerPage;

    // Calculation is done, remember it
    $this->calculationDone = true;

    // Simply return true
    return true;
  }
}
?>
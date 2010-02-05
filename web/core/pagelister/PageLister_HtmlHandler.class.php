<?php
/**
 * Project:     PageLister
 * File:        PageLister_HtmlHandler.class.php
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
 * HTML handler for output
 *
 * @package PageLister
 */
class PageLister_HtmlHandler {
  /**
   * Objects to replace.
   *
   * @var array
   */
  private $objects = array(
                       'first'               => '<a href="%URL%">&laquo;</a>',
                       'prev'                => '<a href="%URL%">&lt;</a>',
                       'next'                => '<a href="%URL%">&gt;</a>',
                       'last'                => '<a href="%URL%">&raquo;</a>',
                       'placeholder'         => '... ',
                       'spacer'              => ' ',
                       'passive_page'        => '<a href="%URL%">%PAGE%</a>',
                       'current_page'        => '<b>%PAGE%</b>',
                     );

  /**
   * Format of the HTML-output.
   *
   * @var string
   */
  private $format = '%FIRST% %PREV% %PAGES% %NEXT% %LAST%';

  /**
   * How many pages should be shown beside the current one.
   *
   * @var int
   */
  private $pagesBeside = 3;


  /**
   * Sets the format of the HTML-output.
   *
   * You may want to modifie the HTML-output, so have have to change the format.
   * You can use any HTML-code, and use the following placeholders in it:
   *
   * %FIRST% - Jump to the first page
   * %PREV%  - Jump to the previews page
   * %NEXT%  - Jump to the next page
   * %LAST$  - Jump to the last page
   * %PAGES% - The pages list
   *
   * @param string $format
   * @return boolean
   */
  public function SetFormat ($format) {
    $this->format = $format;

    return true;
  }

  /**
   * Sets the value of an object.
   *
   * Here you may change the single objects. The objects `first`, `prev`,
   * `next` and `last` can have the placeholder %URL% in it. This is the
   * HTML-escaped url for links. The object `placeholder` holds the placeholder
   * before and after the pages list and the `spacer` holds the space between
   * the pages. The object `passive_page` contains the style of a single
   * passive page with the placeholders %URL% and %PAGE%. The `current_page`
   * object contains the current page with the placeholders %URL% and %PAGE%.
   *
   * @param string $object
   * @param string $value
   * @return boolean
   */
  public function SetObject ($object, $value) {
    if (!array_key_exists($object, $this->objects)) {
      trigger_error('PageLister_HtmlHandler: Invalid object.', E_USER_ERROR);
      return false;
    }

    $this->objects[$object] = (string)$value;

    return true;
  }

  /**
   * Sets how many pages should be sown beside the current one.
   *
   * If you set a value of 4 for example, there will be shown 4 pages
   * before and 4 pages after the current one. If we are at the last page
   * for example, 8 pages will be shown before the current one.
   *
   * @param int $int
   */
  public function SetPagesBeside ($int) {
    $this->pagesBeside = max(1, (int)$int);
  }

  /**
   * Generates the HTML-code.
   *
   * @param int $pages
   * @param int $currentPage
   * @param string $url
   * @return string
   */
  public function GenerateHTML ($pages, $currentPage, $url) {
    // Escape the given URL
    $url = htmlspecialchars($url);

    // Init the html-string
    $html = $this->format;

    // Replace the 4 static placeholders
    $replaces = array();
    if ($currentPage > 1) {
      $replaces['first'] = $this->ReplacePlaceholder($this->objects['first'], array('url' => $url, 'pn' => 1));
      $replaces['prev']  = $this->ReplacePlaceholder($this->objects['prev'],  array('url' => $url, 'pn' => max(1, $currentPage - 1)));
    } else {
      $replaces['first'] = '';
      $replaces['prev']  = '';
    }
    if ($pages > $currentPage) {
      $replaces['next'] = $this->ReplacePlaceholder($this->objects['next'],  array('url' => $url, 'pn' => min($pages, $currentPage + 1)));
      $replaces['last'] = $this->ReplacePlaceholder($this->objects['last'],  array('url' => $url, 'pn' => $pages));
    } else {
      $replaces['next'] = '';
      $replaces['last'] = '';
    }
    $html = $this->ReplacePlaceholder($html, $replaces);

    // Check how many pages before and after you have to print
    if ($currentPage > $this->pagesBeside && $currentPage + $this->pagesBeside <= $pages) {
      // (...) 2 3 4 [5] 6 7 8 (...)
      $pagesBefore = $pagesAfter = $this->pagesBeside;
    } else if ($currentPage <= $this->pagesBeside) {
      // 1 2 [3] 4 5 6 7 (...)
      // 1 2 [3] 4 5
      $pagesBefore = $currentPage - 1;
      $pagesAfter  = min($this->pagesBeside * 2 - $pagesBefore, $pages - $currentPage);
    } else if ($currentPage > $this->pagesBeside) {
      // (...) 2 3 4 [5] 6 7 8
      $pagesAfter  = $pages - $currentPage;
      $pagesBefore = min($this->pagesBeside * 2 - $pagesAfter, $currentPage - 1);
    }

    // Init the pages string
    $pagesString = '';

    // If the list does not start with the first page, add a placeholder
    if ($currentPage - $pagesBefore > 1) {
      $pagesString .= $this->objects['placeholder'].$this->objects['spacer'];
    }

    // Add all pages to the list
    for ($i = $currentPage - $pagesBefore; $i <= $currentPage + $pagesAfter; $i++) {
      $pagesString .= $this->ReplacePlaceholder($this->objects[($i == $currentPage ? 'current_page': 'passive_page')], array('page' => $i, 'url' => $url, 'pn' => $i));

      // If this is not the last page, add a spacer after
      if ($i < $currentPage + $pagesAfter) {
        $pagesString .= $this->objects['spacer'];
      }
    }

    // If the list does not end with the last page, add a placeholder
    if ($currentPage + $pagesAfter < $pages) {
      $pagesString .= $this->objects['spacer'].$this->objects['placeholder'];
    }

    $html = $this->ReplacePlaceholder($html, array('pages' => $pagesString));

    return $html;
  }

  /**
   * Replaces placeholders in a string.
   *
   * @param string $string
   * @param array $placeholders
   * @return string
   */
  private function ReplacePlaceholder ($string, $placeholders) {
    foreach ($placeholders AS $search => $replace) {
      $string = str_replace('%'.strtoupper($search).'%', $replace, $string);
    }

    return $string;
  }
}
?>

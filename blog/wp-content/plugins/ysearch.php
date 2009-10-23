<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Search Plugin. The Yahoo Search Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Search plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Search plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin Name: Yahoo! Search Box
Plugin URI: http://search.yahoo.com/
Description: Activating this plug-in adds Yahoo! Search to the right side of your blog pages. Your site visitors will be able to use this tool to search the Web directly from your site.
Author: Yahoo! Search
Version: 1.0
Author URI: http://search.yahoo.com/
*/

function attach_yahoo_search_box() {
  if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['HTTP_HOST'])) {
     echo <<<YAHOO_SEARCH_BOX
<li>
<!-- Begin Yahoo Search Form -->
<form method="get" action="http://search.yahoo.com/search">
   <img src="http://us.i1.yimg.com/us.yimg.com/i/yahootogo/ytg_search.gif" width="98" height="23" align="top" alt="Yahoo!"/>
   <input type="text" name="p" value="" size="18"/>
   <input type="submit" name="name"/>
   <font size="1">
   <a href="http://search.yahoo.com/search/options">options</a>
   </font>
</form>
<!-- End Yahoo Search Form -->
</li>
YAHOO_SEARCH_BOX;
  }
}

add_action('wp_meta', 'attach_yahoo_search_box');
?>

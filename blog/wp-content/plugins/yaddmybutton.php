<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Add to MyYahoo Plugin. The Add to MyYahoo Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Add to MyYahoo  plugin is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Add to MyYahoo plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin Name: Add to My Yahoo! Button
Plugin URI: http://my.yahoo.com/
Description: Help your readers keep up with your blog by adding a small "Add to My Yahoo!" button to the right side of your blog pages. Your visitors can click the button to automatically add your blog's RSS feed to their My Yahoo! pages, allowing them to easily access your latest postings anytime. (To ensure your RSS feed is submitted to Yahoo!'s database, try out the Yahoo! RSS Feed plug-in.)
Author: Yahoo! - My Yahoo!
Version: 1.0
Author URI: http://my.yahoo.com/
*/

function attach_add_to_my_yahoo_button() {
  $rss2path = ABSPATH."wp-rss2.php";
  if (isset($_SERVER['REQUEST_URI'])) {
     echo <<<ADD_MY_YAHOO
<li><a href="http://add.my.yahoo.com/content?url=http://$_SERVER[HTTP_HOST]$rss2path"><img src="http://us.i1.yimg.com/us.yimg.com/i/us/my/addtomyyahoo4.gif" width="91" height="17" border="0" align="middle" alt="Add to My Yahoo!"/></a></li>
ADD_MY_YAHOO;
  }
}

add_action('wp_meta', 'attach_add_to_my_yahoo_button');
?>

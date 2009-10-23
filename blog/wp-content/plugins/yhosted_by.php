<?php
/* 
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo HostedBy Plugin. The Yahoo HostedBy Plugin
 * is free software; you can redistribute it and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software 
 * Foundation under version 2 of the License, and no other version. The Yahoo 
 * HostedBy plugin is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo HostedBy plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin Name: Hosted by Yahoo! Badge
Plugin URI: http://smallbusiness.yahoo.com/webhosting/
Description: Let your visitors know that you're as proud to be a part of the Yahoo! Web Hosting community as you are to be part of WordPress by using this plug-in to place a "Hosted by Yahoo!" badge on the right side of your blog pages. Your viewers can click on this badge to learn more about Yahoo! Web Hosting.
Author: Yahoo! Web Hosting
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/webhosting/
*/

function attach_hosted_by_yahoo_button() {
  if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['HTTP_HOST'])) {
     echo <<<HOSTED_BY_YAHOO
<li><a href="http://smallbusiness.yahoo.com/webhosting/" target="_top"><img src="http://us.i1.yimg.com/us.yimg.com/i/us/smallbiz/gr/hostedby1_small.gif" width="82" height="29" border="0" align="middle" alt="Hosting by Yahoo!" /></a></li>
HOSTED_BY_YAHOO;
  }
}

add_action('wp_meta', 'attach_hosted_by_yahoo_button');
?>

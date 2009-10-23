<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Masthead Plugin. The Yahoo Masthead Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Masthead plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Masthead plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin URI: http://smallbusiness.yahoo.com/#
Description: Plugin to display Yahoo Webhosting Masthead
Author: Y! Small Business
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/
*/

function attach_ywh_mast() {
  if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['HTTP_HOST'])) {
     echo <<<YWH_MAST
<style type="text/css">
.whhead {font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px;width:100%;height:35px;border-bottom:1px solid #DCDCDC;padding:6px;}
.whhead a {border-bottom:none;}
.whlogo {width:362px;;display:block; float:left;}
.rightside {width:50%;display:block; float:right; height:35px; text-align:right;}
#user_info {
	position: absolute;
	right: 1em;
	top: 50px;
	color: #fff;
	font-size: .9em;
}
</style>
<div class="whhead"><div class="whlogo"><a href="http://webhosting.yahoo.com"><img src="http://us.i1.yimg.com/us.yimg.com/i/us/smbiz/b/ma_smbiz_wh_2.gif" width=362 height=33 border=0 alt="Yahoo! Small Business - Web Hosting"></a></div>
<div class="rightside"><a href="http://smallbusiness.yahoo.com">Small Business Home</a> - <a href="http://help.yahoo.com/help/us/webhosting">Help</a>&nbsp;&nbsp;</div></div>
YWH_MAST;
  }
}

add_action('admin_head', 'attach_ywh_mast');
?>

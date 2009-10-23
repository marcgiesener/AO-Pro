<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Alerts Plugin. The Yahoo Alerts Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Alerts plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Alerts plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */


/*
Plugin URI: http://smallbusiness.yahoo.com/#
Description: Plugin to display Javascript warning messages on error conditions
Author: Y! Small Business
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/
*/


function attach_blog_wrn() {
  if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['HTTP_HOST'])) {
     echo <<<YJSBLOCK
<script>
//<![CDATA[
function addEvent(obj, evType, fn){
if (obj.addEventListener){
obj.addEventListener(evType, fn, true);
return true;
} else if (obj.attachEvent){
var r = obj.attachEvent("on"+evType, fn);
return r;
} else {
return false;
}
}
function blog_addr_warn() {
  alert ("Warning: Modifying this field will cause your permalinks to malfunction. We strongly recommend that you do not change this field.\\n\\nPlease note that this field should match the web address of your blog installation directory. If you chose to make your blog your home page when you installed WordPress, your blog was automatically configured to display at your domain. You should NOT change the value of this field to match your domain.");
}

function admin_email_warn() {
  alert ("Note: In an effort to combat spam, we require that this email address match your domain (e.g. you@$_SERVER[HTTP_HOST]).");
}

function permalink_struct_warn() {
 alert ("Warning: Selecting options or entering information on this page without activating the Customizable Permalinks plug-in may cause other links on your blog to break. If you have already activated Customizable Permalinks, you can ignore this message. To activate now, click the \"Plugins\" link above.");
}

if (document.getElementById('home')) {
  addEvent(document.getElementById('home'), 'focus', blog_addr_warn);
}
if (document.getElementById('admin_email')) {
  addEvent(document.getElementById('admin_email'), 'focus', admin_email_warn);
}
if (document.getElementsByName('permalink_structure')[0]) {
  addEvent(document.getElementsByName('permalink_structure')[0], 'focus', permalink_struct_warn);
}
if (document.getElementsByName('category_base')[0]) {
  addEvent(document.getElementsByName('category_base')[0], 'focus', permalink_struct_warn);
}
if (document.getElementsByName('selection')) {
  for (var i=0;i<document.getElementsByName('selection').length;i++) {
   addEvent(document.getElementsByName('selection')[i], 'focus', permalink_struct_warn);
  }
}
//]]>
</script>
YJSBLOCK;
  }
}

add_action('admin_footer', 'attach_blog_wrn');
?>

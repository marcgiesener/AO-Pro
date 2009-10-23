<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Stats Plugin. The Yahoo Stats Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Stats plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Stats plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin Name: Yahoo! Blog Statistics
Plugin URI: http://smallbusiness.yahoo.com/webhosting/
Description: Enabling Yahoo! blog statistics allows you to collect and view basic statistics, including page views, visitor profiles, and referrers, for your WordPress blog. With a Yahoo! Web Hosting or Merchant Solutions plan, you can review all of your site statistics reports, including those for your blog, on your Web Hosting Control Panel. (For more in-depth statistics about your blog, we recommend installing a blog-specific stats package like BAStats.)
Author: Yahoo! Web Hosting
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/webhosting/
*/

function get_wphome_path() {
        $home = get_settings('home');
        if ( $home != '' && $home != get_settings('siteurl') ) {
                $home_path = parse_url($home);
                $home_path = $home_path['path'];
                $root = str_replace($_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"]);
                $home_path = trailingslashit($root . $home_path);
        } else {
                $home_path = ABSPATH;
        }

        return $home_path;
}

function attach_stats_tracker() {
  $TimeStamp = time();
  $VisitURL = "\"http://visit.webhosting.yahoo.com/wisit.gif?".$TimeStamp."\"";
  $home_path = get_wphome_path();
  $wp_path = "/";

#echo "home_path=" . $home_path . ";";
#echo "self=" . $_SERVER['PHP_SELF'] . ";";
#echo "uri=" . $_SERVER['REQUEST_URI'] . ";";
#echo "url=" . $_SERVER['REQUEST_URL'] . ";";
#echo "pinfo=" . $_SERVER['PATH_INFO'] . ";";
#echo "droot=" . $_SERVER['DOCUMENT_ROOT'] . ";";

  if ( defined('WP_IN_ROOTDIR') && constant('WP_IN_ROOTDIR') ) {
    $wp_path ="/";
  } else {
    $pos = strstr($_SERVER['REQUEST_URI'],$home_path);
    if($pos === false) {
      $wp_path = "/";
    } else {
       $wp_path = $home_path;
    }
  }
		
echo <<<TRACKER_CODE
<script type="text/javascript" language="JavaScript">
//<![CDATA[
var wpdone;
function wpvisit()
{
  var z;
  z="&r="+escape(document.referrer);
  z=z+"&b="+escape(navigator.appName+" "+navigator.appVersion);
  w=parseFloat(navigator.appVersion);
  if (w > 2.0) {
    z=z+"&s="+screen.width+"x"+screen.height;
    z=z+"&o="+navigator.platform;
    v="1.2";
    if (navigator.appName != "Netscape") {
      z=z+"&c="+screen.colorDepth;
    } else {
      z=z+"&c="+screen.pixelDepth
    }
    z=z+"&j="+navigator.javaEnabled();
  } else {
    v=1.0;
  }
  z=z+"&v="+v;

  document.writeln("<img border=\"0\" src=\"http://visit.webhosting.yahoo.com/wisit.gif"+"$wp_path"+"?"+z+"\" />");
}
  wpvisit();
//]]>
</script>

<noscript><img src=$VisitURL border="0" width="1" height="1" alt="visit" /></noscript>
TRACKER_CODE;
}

// Now we set that function up to execute when the admin_footer action is called
add_action('wp_footer', 'attach_stats_tracker');
?>

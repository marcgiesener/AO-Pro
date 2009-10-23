<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Permalink Plugin. The Yahoo Permalink Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Permalink plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Permalink plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */

/*
Plugin Name: Customizable Permalinks
Plugin URI: http://smallbusiness.yahoo.com/webhosting/
Description: Help drive and retain traffic to your site with the Customizable Permalinks plug-in, which enables you to give your readers easily understandable, static web addresses (URLs) for your pages. Customized permalinks make it easier for your readers to bookmark and share your blog posts.
Author: Yahoo! Web Hosting
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/webhosting/
*/

function update_permalink_file() {
  if (isset($_POST['permalink_structure']) ) {
    if(empty($_POST['permalink_structure']) ) {
      unlink(get_home_path() . '.plink');
    } else {
      touch(get_home_path() . '.plink');
    }
  }

  if(strcmp($_GET['deactivate'],"true")==0){
      unlink(get_home_path() . '.plink');
  } else if(strcmp($_GET['activate'],"true")==0){
      touch(get_home_path() . '.plink');
  }
  unlink(get_home_path() . '.htaccess');
}

// Now we set that function up to execute when the admin_footer action is called
add_filter('mod_rewrite_rules', 'update_permalink_file');
add_action('mod_rewrite_rules', 'update_permalink_file');
?>

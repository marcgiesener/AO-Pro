<?php
/*
Plugin Name: FAlbum
Version: 0.4.2
Plugin URI: http://www.randombyte.net/
Description: A plugin for displaying your <a href="http://www.flickr.com/">Flickr</a> photosets and photos in a gallery format on your Wordpress site.
Author: Elijah Cornell
Author URI: http://www.randombyte.net/

Change log:
0.4.2 -	Friendly URL fixes, XML error fixes
0.4.1 -	Localization clean up, added option to disable dropshadows
0.4 -	Localization, many bug fixes
0.3 -	Switched to use Flickr new auth api
0.2 -	Added Admin page
Switched caching to be stored in the database
0.1 - 	Init Release

Copyright (c) 2004
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

This file is part of WordPress.
WordPress is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

include_once(ABSPATH . 'wp-includes/streams.php');
include_once(ABSPATH . 'wp-includes/gettext.php');

require_once('falbum.php');

define ('FALBUM_DOMAIN', '/falbum/lang/falbum');

load_plugin_textdomain(FALBUM_DOMAIN);

// plugin menu
function falbum_add_pages () {
	if (function_exists('add_options_page')) {
//		add_submenu_page('plugins.php', 'FAlbum', 'FAlbum', 10, "/wp-content/plugins/falbum/".basename(__FILE__), 'falbum_options_page');
                add_options_page('FAlbum', 'FAlbum', 10, "/wp-content/plugins/falbum/".basename(__FILE__), 'falbum_options_page');

	}
}


function falbum_options_page() {

	global $is_apache;
	global $wpdb;

	//

	$wpdb->query("CREATE TABLE IF NOT EXISTS falbum_cache (
			ID varchar(40) PRIMARY KEY,
			data text,
			expires datetime
			)");

	//

	$falbum_options = get_option('falbum_options');

	//

	//$home_path = parse_url("/");
	//$home_path = $home_path['path'];
	//$root2 = str_replace($_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"]);
	//$home_path = trailingslashit($root2 . $home_path);

	//

	$urlinfo = parse_url(get_settings('siteurl'));
	$path = $urlinfo['path'];
	$domain = $urlinfo['host'];

	$furl = trailingslashit($falbum_options['falbum_url_root']);
	if (strpos($furl, "/") == 0) {
		$furl =  substr($furl, 1);
	}

	$pos = strpos('/'.$furl, $path.'/');
	if (strpos('/'.$furl, $path.'/') === false) {
		$home_path = parse_url("/");
		$home_path = $home_path['path'];
		$root2 = str_replace($_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"]);
		$home_path = trailingslashit($root2 . $home_path);
	} else {
		$furl = str_replace($path.'/', '', '/'.$furl);
		$home_path = get_home_path();
	}
	if ( (!file_exists($home_path.'.htaccess') && is_writable($home_path)) || is_writable($home_path.'.htaccess') ) {
		$writable = true;
	} else {
		$writable = false;
	}
	if (strpos($furl, "/") == 0) {
		$furl =  substr($furl, 1);
	}

	$rewriteRule =
	"AddType text/x-component .htc\n".
	"<IfModule mod_rewrite.c>\n".
	"RewriteEngine On\n".
	"RewriteRule ^".$furl."?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?$ ".$path."/wp-content/plugins/falbum/falbum-wp.php?$1=$2&$3=$4&$5=$6&$7=$8 [QSA,L]\n".
	"</IfModule>";

	//echo '<pre>$path-'.$path.'/'.'</pre>';
	//echo '<pre>$furl-'.'/'.$furl.'</pre>';
	//echo '<pre>1-'.strpos('/'.$furl, $path.'/').'</pre>';
	//echo '<pre>$furl-'.$furl.'</pre>';
	//echo '<pre>'.$rewriteRule.'</pre>';


	// posting logic
	if (isset($_POST['Submit'])) {

		$falbum_options['falbum_tsize'] = $_POST['falbum_tsize'];
		$falbum_options['falbum_show_private'] = $_POST['falbum_show_private'];
		$falbum_options['falbum_friendly_urls'] = $_POST['falbum_friendly_urls'];
		$falbum_options['falbum_url_root'] = $_POST['falbum_url_root'];
		$falbum_options['falbum_albums_per_page'] = $_POST['falbum_albums_per_page'];
		$falbum_options['falbum_photos_per_page'] = $_POST['falbum_photos_per_page'];
		$falbum_options['falbum_max_photo_width'] = $_POST['falbum_max_photo_width'];
		$falbum_options['falbum_display_dropshadows'] = $_POST['falbum_display_dropshadows'];

		$furl = $falbum_options['falbum_url_root'];
		$pos = strpos($furl, '/');
		if ($pos === false || $pos != 0) {
			$furl =  '/'.$furl;
		}
		$pos = strpos($furl, '.php');
		if ($pos === false) {
			$furl =  trailingslashit($furl);
		}
		
		$falbum_options['falbum_url_root'] = $furl;

		update_option('falbum_options', $falbum_options);

		$updateMessage .= __('Options saved', FALBUM_DOMAIN)."<br /><br />";

		if ($falbum_options['falbum_friendly_urls'] == 'true') {

			if ( $is_apache ) {

				insert_with_markers($home_path.'.htaccess', 'FAlbum', '');

				$urlinfo = parse_url(get_settings('siteurl'));
				$path = $urlinfo['path'];
				$domain = $urlinfo['host'];

				//$furl = trailingslashit($falbum_options['falbum_url_root']);
				if (strpos($furl, "/") == 0) {
					$furl =  substr($furl, 1);
				}

				//echo '<pre>$path-'.$path.'/'.'</pre>';
				//echo '<pre>$furl-'.'/'.$furl.'</pre>';
				//echo '<pre>1-'.strpos('/'.$furl, $path.'/').'</pre>';

				$pos = strpos('/'.$furl, $path.'/');

				if ($path != '/' && strpos('/'.$furl, $path.'/') === false) {
					//use root .htaccess file
					//echo '<pre>root</pre>';
					$home_path = parse_url("/");
					$home_path = $home_path['path'];
					$root2 = str_replace($_SERVER["PHP_SELF"], '', $_SERVER["SCRIPT_FILENAME"]);
					$home_path = trailingslashit($root2 . $home_path);
				} else {
					//use wp .htaccess file
					//echo '<pre>wp</pre>';
					if (strlen($path) > 1) {
						$furl = str_replace($path.'/', '', '/'.$furl);
					}
					$home_path = get_home_path();
				}
				if ( (!file_exists($home_path.'.htaccess') && is_writable($home_path)) || is_writable($home_path.'.htaccess') ) {
					$writable = true;
				} else {
					$writable = false;
				}
				if (strpos($furl, "/") == 0) {
					$furl =  substr($furl, 1);
				}

				$rewriteRule =
				"AddType text/x-component .htc\n".
				"<IfModule mod_rewrite.c>\n".
				"RewriteEngine On\n".
				"RewriteRule ^".$furl."?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?([^/]*)?/?$ ".$path."/wp-content/plugins/falbum/falbum-wp.php?$1=$2&$3=$4&$5=$6&$7=$8 [QSA,L]\n".
				"</IfModule>";

				//echo '<pre>$home_path-'.$home_path.'</pre>';
				//echo '<pre>'.$rewriteRule.'</pre>';

				if ( $writable) {
					$rules = explode("\n", $rewriteRule);

					insert_with_markers($home_path.'.htaccess', 'FAlbum', $rules);

					$updateMessage .= __('Mod rewrite rules updated', FALBUM_DOMAIN)."<br /><br />";

					//

					$wpdb->query("DELETE from falbum_cache");

					$updateMessage .= __('Cache cleared', FALBUM_DOMAIN)."<br />";

				}

			}
		}
	}


	if (isset($_POST['ClearToken'])) {

		$falbum_options['falbum_token'] = null;
		update_option('falbum_options', $falbum_options);

		$updateMessage .= __('Flickr authorization reset', FALBUM_DOMAIN)."<br />";

	}



	if (isset($_POST['ClearCache'])) {

		$wpdb->query("DELETE from falbum_cache");

		$updateMessage .= __('Cache cleared', FALBUM_DOMAIN)."<br />";

	}


	if (isset($_POST['GetToken'])) {

		$frob2 = $_POST['frob'];

		$url = 'http://flickr.com/services/rest/?method=flickr.auth.getToken&api_key='.FALBUM_API_KEY.'&frob='.$frob2;
		$parms = 'api_key'.FALBUM_API_KEY.'frob'.$frob2.'methodflickr.auth.getToken';
		$url = $url.'&api_sig='.md5(FALBUM_SECRET.$parms);

		//echo '<pre>'.htmlentities($url).'</pre>';

		$resp = fa_fopen_url($url, 0);

		//echo '<pre>'.htmlentities($resp).'</pre>';

		$xpath = fa_parseXPath($resp);
		$token = $xpath->getData("/rsp/auth/token");
		$nsid = $xpath->getData("/rsp/auth/user/@nsid");

		$falbum_options['falbum_token'] = $token;
		$falbum_options['falbum_nsid'] = $nsid;

		update_option('falbum_options', $falbum_options);

		$updateMessage .= __('Successfully set token', FALBUM_DOMAIN)."<br />";

	}


	if (isset($updateMessage)) {

		?> <div class="updated"><p><strong><?php echo $updateMessage?></strong></p></div> <?php

	}


	//Init Settings
	if (!isset($falbum_options['falbum_tsize']) || $falbum_options['falbum_tsize'] == "") {
		$falbum_options['falbum_tsize'] = "s";
	}
	if (!isset($falbum_options['falbum_show_private']) || $falbum_options['falbum_show_private'] == "") {
		$falbum_options['falbum_show_private'] = "false";
	}
	if (!isset($falbum_options['falbum_friendly_urls']) || $falbum_options['falbum_friendly_urls'] == "") {
		$falbum_options['falbum_friendly_urls'] = "false";
	}
	if (!isset($falbum_options['falbum_url_root']) || $falbum_options['falbum_url_root'] == "") {
		$falbum_options['falbum_url_root'] = $path."/wp-content/plugins/falbum/falbum-wp.php";
	}
	if (!isset($falbum_options['falbum_albums_per_page']) || $falbum_options['falbum_albums_per_page'] == "") {
		$falbum_options['falbum_albums_per_page'] = "10";
	}
	if (!isset($falbum_options['falbum_photos_per_page']) || $falbum_options['falbum_photos_per_page'] == "") {
		$falbum_options['falbum_photos_per_page'] = "45";
	}
	if (!isset($falbum_options['falbum_max_photo_width']) || $falbum_options['falbum_max_photo_width'] == "") {
		$falbum_options['falbum_max_photo_width'] = "0";
	}
	if (!isset($falbum_options['falbum_display_dropshadows']) || $falbum_options['falbum_display_dropshadows'] == "") {
		$falbum_options['falbum_display_dropshadows'] = "-nods";
	}




?>


<div class="wrap">
<?php
//echo '<pre>data-'.htmlentities($falbum_options['falbum_token']).'</pre>';
//echo '<pre>data-'.htmlentities($falbum_options['falbum_nsid']).'</pre>';
?>

  <h2><? _e('FAlbum Options', FALBUM_DOMAIN);?></h2>
    <form method=post action="<?php echo $_SERVER['PHP_SELF']; ?>?page=falbum/falbum-plugin.php">
        <input type="hidden" name="update" value="true">
                       
        <?php if (!isset($falbum_options['falbum_token']) || $falbum_options['falbum_token'] == '' ) { ?>

       <fieldset class="options">
       <legend><? _e('Initial Setup', FALBUM_DOMAIN);?></legend>
        
               <?php

               $url = 'http://flickr.com/services/rest/?method=flickr.auth.getFrob&api_key='.FALBUM_API_KEY;
               $parms = 'api_key'.FALBUM_API_KEY.'methodflickr.auth.getFrob';
               $url = $url.'&api_sig='.md5(FALBUM_SECRET.$parms);

               //echo '<pre>$url-'.htmlentities($url).'</pre>';

               $resp = fa_fopen_url($url, 0);

               //echo '<pre>$resp-'.htmlentities($resp).'</pre>';

               $xpath = fa_parseXPath($resp);
               $frob = $xpath->getData("/rsp/frob");

               //echo '<pre>$frob-'.htmlentities($frob).'</pre>';

               $link = 'http://flickr.com/services/auth/?api_key='.FALBUM_API_KEY.'&frob='.$frob.'&perms=read';
               $parms = 'api_key'.FALBUM_API_KEY.'frob'.$frob.'permsread';
               $link .= '&api_sig='.md5(FALBUM_SECRET.$parms);

       ?>
       
	      <input type="hidden" name="frob" value="<?php echo $frob?>">
	      
	      <p>	      
	      <? _e('Please complete the following step to allow FAlbum to access your Flickr photos.', FALBUM_DOMAIN);?>
	      </p>
       
        <p>
         <? _e('Step 1:', FALBUM_DOMAIN);?> <a href="<?php echo $link?>" target="_blank"><? _e('Authorize FAlbum with access your Flickr account', FALBUM_DOMAIN);?></a>
       	</p>
       	       	
       	 
       	 <p>
         <? _e('Step 2:', FALBUM_DOMAIN);?> <input type="submit" name="GetToken" value="Get Authentication Token" />
       	</p>
       	
                       
      </fieldset>
      
      	<?php } ?>
      
      
 <fieldset class="options">
       <legend><? _e('FAlbum Admin', FALBUM_DOMAIN);?></legend>
         
        <p>
         <input type="submit" name="ClearCache" value="Clear Cache" />
         &nbsp;&nbsp;&nbsp;
         
         <?php if (isset($falbum_options['falbum_token'])) { ?>
         <input type="submit" name="ClearToken" value="Reset Flickr Authorization" />
         <?php } ?>
         
       </p>
       </fieldset>
       
       <hr />
       
        <fieldset class="options">
            <legend><? _e('Set Up', FALBUM_DOMAIN);?></legend>
            <table width="100%" cellspacing="2" cellpadding="5" class="editform">

            	<tr valign="top">
            	<th width="33%" scope="row"><? _e('Thumbnail Size', FALBUM_DOMAIN);?>:</th>
                    <td>
                    <select name="falbum_tsize">
                    <option value="s"<?php if ($falbum_options['falbum_tsize'] == 's') { ?> selected="selected"<?php } ?> ><? _e('Square', FALBUM_DOMAIN);?> (75px x 75px)</option>
                    <option value="t"<?php if ($falbum_options['falbum_tsize'] == 't') { ?> selected="selected"<?php } ?> ><? _e('Thumbnail', FALBUM_DOMAIN);?> (100px x 75px)</option>
                    <option value="m"<?php if ($falbum_options['falbum_tsize'] == 'm') { ?> selected="selected"<?php } ?> ><? _e('Small', FALBUM_DOMAIN);?> (240px x 180px)</option>
                    </select><br />
                    <? _e('Size of the thumbnail you want to appear in the album thumbnail page', FALBUM_DOMAIN);?><br /></td>
                </tr>
                
                <tr valign="top">
                    <th width="33%" scope="row"><? _e('Albums Per Page', FALBUM_DOMAIN);?>:</th>
                    <td><input type="text" name="falbum_albums_per_page" size="3" value="<?php echo $falbum_options['falbum_albums_per_page'] ?>"/><br />
                   <? _e('How many albums to show on a page (0 for no paging)', FALBUM_DOMAIN);?></td>
                </tr>
                <tr valign="top">
                    <th width="33%" scope="row"><? _e('Photos Per Page', FALBUM_DOMAIN);?>:</th>
                    <td><input type="text" name="falbum_photos_per_page" size="3" value="<?php echo $falbum_options['falbum_photos_per_page'] ?>"/><br />
                   <? _e('How many photos to show on a page (0 for no paging)', FALBUM_DOMAIN);?></td>
                </tr>
				
				<tr valign="top">
                    <th width="33%" scope="row"><? _e('Max Photo Width', FALBUM_DOMAIN);?>:</th>
                    <td><input type="text" name="falbum_max_photo_width" size="3" value="<?php echo $falbum_options['falbum_max_photo_width'] ?>"/><br />
                   <? _e('Maximum photo width in pixels (0 for resizing).  The default size of the images returned from Flickr is 500 pixels.', FALBUM_DOMAIN);?></td>
                </tr>  
                
                 <tr valign="top">
                    <th width="33%" scope="row"><? _e('Display Drop Shadows', FALBUM_DOMAIN);?>:</th>
                    <td>
                    <select name="falbum_display_dropshadows">
                    <option value="-ds"<?php if ($falbum_options['falbum_display_dropshadows'] == '-ds') { ?> selected="selected"<?php } ?> ><? _e('true', FALBUM_DOMAIN);?></option>
                    <option value="-nods"<?php if ($falbum_options['falbum_display_dropshadows'] == '-nods') { ?> selected="selected"<?php } ?> ><? _e('false', FALBUM_DOMAIN);?></option>
                    </select>
                    <br />
                    <? _e('Whether or not to show drop shadows under photos', FALBUM_DOMAIN);?></td>
                </tr>
                
                 <tr valign="top">
                    <th width="33%" scope="row"><? _e('Show Private', FALBUM_DOMAIN);?>:</th>
                    <td>
                    <select name="falbum_show_private">
                    <option value="true"<?php if ($falbum_options['falbum_show_private'] == 'true') { ?> selected="selected"<?php } ?> ><? _e('true', FALBUM_DOMAIN);?></option>
                    <option value="false"<?php if ($falbum_options['falbum_show_private'] == 'false') { ?> selected="selected"<?php } ?> ><? _e('false', FALBUM_DOMAIN);?></option>
                    </select>
                    <br />
                    <? _e('Whether or not to show your "private" Flickr photos', FALBUM_DOMAIN);?></td>
                </tr>
                
                <tr valign="top">
                    <th width="33%" scope="row"><? _e('Use Friendly URLS', FALBUM_DOMAIN);?>:</th>
                    <td>
                    <select name="falbum_friendly_urls">
                    <option value="true"<?php if ($falbum_options['falbum_friendly_urls'] == 'true') { ?> selected="selected"<?php } ?> ><? _e('true', FALBUM_DOMAIN);?></option>
                    <option value="false"<?php if ($falbum_options['falbum_friendly_urls'] == 'false') { ?> selected="selected"<?php } ?> ><? _e('false', FALBUM_DOMAIN);?></option>
                    </select>
                    <br />
                    <? _e('Set to true if you want to use "friendly" URLs (requires mod_rewrite), false otherwise', FALBUM_DOMAIN);?>
                    
                    <?php if ( !$writable && $is_apache) : ?>
  <p><?php _e('If your', FALBUM_DOMAIN);?><code><?php echo $home_path?>.htaccess</code><?php _e('file was <a href="http://codex.wordpress.org/Make_a_Directory_Writable">writable</a> we could do this automatically, but it isn\'t so these are the mod_rewrite rules you should have in your <code>.htaccess</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all.') ?></p>
  <p><textarea rows="5" style="width: 98%;" name="rules"><?php echo $rewriteRule; ?>
  </textarea></p><?php endif; ?>    
					</td>
                </tr>        

				<tr valign="top">
                    <th width="33%" scope="row">URL Root:</th>
                    <td><input type="text" name="falbum_url_root" size="60" value="<?php echo $falbum_options['falbum_url_root'] ?>"/><br />
                   <? _e('URL to use as the root for all navigational links<br />
Friendly URLs enabled - /photos/<br /> 
Friendly URLs disabled - ', FALBUM_DOMAIN); echo $path."/wp-content/plugins/falbum/falbum-wp.php" ?>
				   </td>
                </tr>
                
                    
                
            </table>
     

       <p class="submit">
         <input type="submit" name="Submit" value="<? _e('Update Options', FALBUM_DOMAIN);?> &raquo;" />
       </p>
       
	</fieldset>
   
    </form>
</div>

<?php

}



// function for outputting header information
//
function falbum_header() {
	$hHead = "<meta name=\"generator\" content=\"FAlbum 0.2\" />\n";
	$hHead .= "\n<style type=\"text/css\">\n";
	$hHead .= "@import url(".get_settings('siteurl')."/wp-content/plugins/falbum/falbum.css);\n";
	$hHead .= "</style>\n";
	print($hHead);
}

// output styles to the <head> section of the page
add_action('wp_head', 'falbum_header');
add_action('admin_menu', 'falbum_add_pages');

?>

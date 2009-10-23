<?php
/*
 * Copyright (c) Yahoo! Inc. 2005. All Rights Reserved.
 *
 * This file is part of Yahoo Services Plugin. The Yahoo Services Plugin
 * is free software; you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software
 * Foundation under version 2 of the License, and no other version. The Yahoo
 * Services plugin is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with the Yahoo Services plugin; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 */


/*
Plugin URI: http://smallbusiness.yahoo.com/webhosting/
Description: This plugin inplements Yahoo internal functionality
Author: Yahoo! Web Hosting
Version: 1.0
Author URI: http://smallbusiness.yahoo.com/webhosting/
*/

add_filter('option_home', 'ywh_domainpending');
add_filter('option_siteurl', 'ywh_domainpending');
add_filter('option_rewrite_rules', 'ywh_rewrite_rules');


// function to enable correct function of Wordpress with Yahoo!
// Webhosting domain pending url
function ywh_domainpending ($text) {
    global $cookiepath, $sitecookiepath;

    // does url match domain pending url
    if (preg_match("/p(\d*).hostingprod.com/", $_SERVER['HTTP_HOST'])) {
       $wp_url = "http://".$_SERVER['HTTP_HOST']."/@".str_replace("http://", "", $text).get_settings('blogdir');
       // set correct cookie path for domain pending urls
       if (!defined('PENDINGSITECOOKIEPATH')) {
          define('PENDINGSITECOOKIEPATH', str_replace("http://", "/@", $text).get_settings('blogdir'));
       }

       // remove trailing slash
       if (strrpos($wp_url, "/") == strlen($wp_url)-1)
          $wp_url = substr($wp_url, 0, strlen($wp_url)-1);
       return $wp_url;
    }
    return $text;
}

// function to enable correct working of trackback url
function ywh_rewrite_rules ($text) {
    $tb_url    = $_POST['url'];
    $title     = $_POST['title'];
    $blog_name   = $_POST['blog_name'];

    // may be valid trackback so ignore this filter
    if (! empty($tb_url) && !empty($title) && empty($blog_name))
       return $text;

    // setup working redirect for trackback
    if (is_array($text)) {
      $rules = $trackback_rules = NULL;
      foreach ($text as $match => $query) {
         if (stristr($match, "trackback")===FALSE) {
            $rules[str_replace("/?$", "(?:/trackback){0,1}/?$", $match)] = $query;
         }
         else {
            $trackback_rules[$match] = $query;
         }
      }
      return array_merge($rules, $trackback_rules);
    }
    return $text;
}

if ( !function_exists('wp_setcookie') ) :
function wp_setcookie($username, $password, $already_md5 = false, $home = '', $siteurl = '') {
        if ( !$already_md5 )
                $password = md5( md5($password) ); // Double hash the password in the cookie.

       // Added if statement for domain pending support
       if (preg_match("/p(\d*).hostingprod.com/", $_SERVER['HTTP_HOST'])) {
           $home = get_settings('home');
           $siteurl = get_settings('site_url');
           if (!defined('PENDINGSITECOOKIEPATH')) {
             define('PENDINGSITECOOKIEPATH', str_replace("http://", "/@", get_settings('site_url')).get_settings('blogdir'));
           }
           $cookiehash = COOKIEHASH;
           setcookie('wordpressuser_'. $cookiehash, $username, time() + 31536000, PENDINGSITECOOKIEPATH);
           setcookie('wordpresspass_'. $cookiehash, $password, time() + 31536000, PENDINGSITECOOKIEPATH);
           return;
        }

        if ( empty($home) )
                $cookiepath = COOKIEPATH;
        else
                $cookiepath = preg_replace('|https?://[^/]+|i', '', $home . '/');

        if ( empty($siteurl) ) {
                $sitecookiepath = SITECOOKIEPATH;
                $cookiehash = COOKIEHASH;
        } else {
                $sitecookiepath = preg_replace('|https?://[^/]+|i', '', $siteurl . '/' );
                $cookiehash = md5($siteurl);
        }

        setcookie('wordpressuser_'. $cookiehash, $username, time() + 31536000, $sitecookiepath);
        setcookie('wordpresspass_'. $cookiehash, $password, time() + 31536000, $sitecookiepath);

        if ( $cookiepath != $sitecookiepath ) {
                setcookie('wordpressuser_'. $cookiehash, $username, time() + 31536000, $sitecookiepath);
                setcookie('wordpresspass_'. $cookiehash, $password, time() + 31536000, $sitecookiepath);
        }
}
endif;

if ( !function_exists('wp_clearcookie') ) :
function wp_clearcookie() {
        // Added if statement for domain pending support
        if (preg_match("/p(\d*).hostingprod.com/", $_SERVER['HTTP_HOST'])) {
           $home = get_settings('home');
           $siteurl = get_settings('site_url');
           if (!defined('PENDINGSITECOOKIEPATH')) {
             define('PENDINGSITECOOKIEPATH', str_replace("http://", "/@", get_settings('site_url')).get_settings('blogdir'));
           }
           setcookie('wordpressuser_'. COOKIEHASH, ' ', time() + 31536000, PENDINGSITECOOKIEPATH);
           setcookie('wordpresspass_'. COOKIEHASH, ' ', time() + 31536000, PENDINGSITECOOKIEPATH);
           return;
        }
        setcookie('wordpressuser_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH);
        setcookie('wordpresspass_' . COOKIEHASH, ' ', time() - 31536000, COOKIEPATH);
        setcookie('wordpressuser_' . COOKIEHASH, ' ', time() - 31536000, SITECOOKIEPATH);
        setcookie('wordpresspass_' . COOKIEHASH, ' ', time() - 31536000, SITECOOKIEPATH);
}
endif;


if ( !function_exists('wp_redirect') ) :
function wp_redirect($location) {
        global $is_IIS;

        // Added if statement for domain pending support
        if (preg_match("/p(\d*).hostingprod.com/", $_SERVER['HTTP_HOST'])) {
           $loc2 = $location;
           if ($loc2[0] != "/" && strstr($loc2, "http://") == NULL) {
              $loc2 = get_settings('home')."/".$location;
           }
           if (strstr($loc2, "hostingprod.com/") == NULL) {
             $loc2 = "http://".$_SERVER['HTTP_HOST'].$location;
           }
           if (strstr($loc2, "hostingprod.com/@") == NULL) {
             $loc2 = str_replace("hostingprod.com/", "hostingprod.com/@", $loc2);
           }
           if ($is_IIS)
              header("Refresh: 0;url=$loc2");
           else
              header("Location: $loc2");
           return;
        }

        if ($is_IIS)
                header("Refresh: 0;url=$location");
        else
                header("Location: $location");
}
endif;

// turn on/off permalinks implementation
add_filter('option_active_plugins', 'update_ywh_plink_status');

function update_ywh_plink_status ($text) {
  if (!isset($_GET['plugin']) || !isset($_GET['action'])) return $text;

  $plugin_name = $_GET['plugin'];
  if ($plugin_name != "yplink.php") return $text;

  $plugin_action = $_GET['action'];
  if ($plugin_action == "activate") {
     touch(get_home_path() . '.plink');
     update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/'
);
  }
  else
  if ($plugin_action == "deactivate") {
     unlink(get_home_path() . '.plink');
     update_option('permalink_structure', '');
  }

  return $text;
}

if ( !function_exists('wp_mail') ) :
function wp_mail($to, $subject, $message, $headers = '') {
        if( $headers == '' ) {
                $uri = parse_url( get_option('home') );
                $home_domain = $uri['host'];

                $headers = "MIME-Version: 1.0\n" .
                        "From: admin@" . $home_domain . "\n" .
                        "Content-Type: text/plain; charset=\"" . get_settings('blog_charset') . "\"\n";
        }

        return @mail($to, $subject, $message, $headers);
}
endif;

?>

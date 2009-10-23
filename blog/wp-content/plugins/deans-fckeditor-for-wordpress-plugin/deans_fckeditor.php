<?php
/*
Plugin Name: Dean's FCKEditor For Wordpress
Plugin URI: http://www.deanlee.cn/wordpress/fckeditor-for-wordpress-plugin/
Description: Replaces the default Wordpress editor with <a href="http://www.fckeditor.net/"> FCKeditor</a> 2.4.3
Version: 2.2
Author: Dean Lee
Author URI: http://www.deanlee.cn/
*/
/*
Copyright (C) 2007  Dean Lee  (email : deanlee2@hotmail.com)

 This program is free software; you can redistribute it and/or modify
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
require_once('deans_fckeditor_class.php');
add_action('admin_menu', array(&$deans_fckeditor, 'add_option_page'));
add_action('admin_head', array(&$deans_fckeditor, 'add_admin_head'));
add_action('personal_options_update', array(&$deans_fckeditor, 'user_personalopts_update'));
add_action('edit_form_advanced', array(&$deans_fckeditor, 'load_fckeditor'));
add_action('edit_page_form', array(&$deans_fckeditor, 'load_fckeditor'));
add_action('simple_edit_form', array(&$deans_fckeditor, 'load_fckeditor'));
add_action('admin_footer', array(&$deans_fckeditor, 'disable_preview'));
register_activation_hook(basename(dirname(__FILE__)).'/' . basename(__FILE__), array(&$deans_fckeditor, 'activate'));
register_deactivation_hook(basename(dirname(__FILE__)).'/' . basename(__FILE__), array(&$deans_fckeditor, 'deactivate'));

?>
<?php
/*
Copyright (c) 2005
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

require_once('../../../wp-blog-header.php');

$tdir = get_template_directory();

//echo '<pre>'.$tdir.'</pre>';

if (file_exists($tdir."/falbum-wp.php")) {
	
	include_once($tdir."/falbum-wp.php");

} else {
	
$_SERVER['PATH_INFO'] = '';

get_header(); ?>

<script type="text/javascript" src="<?php echo get_settings('siteurl'); ?>/wp-content/plugins/falbum/falbum.js"></script>
<script type="text/javascript" src="<?php echo get_settings('siteurl'); ?>/wp-content/plugins/falbum/overlib.js"></script>

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

	<div id="content" class="narrowcolumn">
	
	 <?php fa_show_photos($_GET['album'], $_GET['photo'], $_GET['page'], $_GET['tags'], $_GET['show']); ?>

	</div>
	
<?php get_sidebar(); ?>

<?php get_footer(); 

}

?>



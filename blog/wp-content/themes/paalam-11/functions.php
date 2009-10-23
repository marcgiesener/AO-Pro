<?php if ( function_exists('register_sidebar') ) {register_sidebar();register_sidebar();}
// Custom Header Image Support

define('HEADER_TEXTCOLOR', '336');
define('HEADER_IMAGE', '%s/img/header.jpg'); // %s is theme dir uri
define('HEADER_IMAGE_WIDTH', 900);
define('HEADER_IMAGE_HEIGHT', 180);


function theme_admin_header_style() {
?>
<style type="text/css">
#headimg {
	background:#fff url(<?php header_image() ?>) no-repeat center;  
	height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
	width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
}
#headimg h1{
	margin:0;
	padding: 10px 0 0 10px;
	font-size: 1.8em;
	font-variant:small-caps;
}
#headimg #desc {
	margin:0;
	padding:  10px 0 0 10px;
	font-size: 1em;
	font-family:Tahoma, Verdana, Arial, Serif;
}

#headimg a {
	text-decoration: none;
}
#headimg * 
{
	color: #<?php header_textcolor();?>;
}
</style>
<?php
}
function theme_header_style() {
?>
<style type="text/css">
#header
{
	background:url(<?php header_image(); ?>) no-repeat center;  
}
#header * 
{
	color: #<?php header_textcolor();?>;
}
</style>
<?php
}
if ( function_exists('add_custom_image_header') ) {
	add_custom_image_header('theme_header_style', 'theme_admin_header_style');
}
?>
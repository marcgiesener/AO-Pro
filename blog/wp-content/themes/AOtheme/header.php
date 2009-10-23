<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>
<script src="http://ao.andrewertell.com/scripts/mootools.v1.11.js" type="text/javascript"></script>
<script src="http://aoproductions.net/scripts/listsubscribe.js" type="text/javascript"></script>
<script src="http://ao.andrewertell.com/scripts/jd.gallery.js" type="text/javascript"></script>
<LINK REL="SHORTCUT ICON" HREF="http://www.aoproductions.net/aoicon.ico" />
<link rel="stylesheet" href="http://ao.andrewertell.com/css/jd.gallery.css" type="text/css" media="screen" /> 
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<style type="text/css" media="screen">

<?php
// Checks to see whether it needs a sidebar or not
if ( !empty($withcomments) && !is_single() ) {
?>
	#page { background: url("<?php bloginfo('stylesheet_directory'); ?>/images/kubrickbg-<?php bloginfo('text_direction'); ?>.jpg") repeat-y top; border: none; }
<?php } else { // No sidebar ?>
	
<?php } ?>

</style>
<script type="text/javascript">
function startGallery() {
var myGallery = new gallery($('myGallery'), {
timed: true,
showArrows: false,
showCarousel: false,
embedLinks: false
});
}
window.addEvent('domready', startGallery);
</script>

<?php wp_head(); ?>
</head>
<body class="page_home">
<div class="frame1">
<div id="page">


<div id="header">
			<div id="banner">
			<img src="/images/topbanner.png" />
			</div>
	
			
		<div id="topNavbar">
			<ul>
			<li><a class="homebutt" href="<?php bloginfo ( 'home'  );  ?>"><span class="nodis">&nbsp;</span></a></li>
			<li><a class="concertsbutt" href="/?cat=2"><span class="nodis">&nbsp;</span></a></li>
<li><a class="speakersbutt" href="/?cat=7"><span class="nodis">&nbsp;</span></a></li>			
<li><a class="filmsbutt" href="/?cat=3"><span class="nodis">&nbsp;</span></a></li>
			
			</ul>
		</div>
		<div id="smallNavbar">
			<ul>
			<li><a href="/?page_id=162">Board</a></li>
			<li>|</li>
			<li><a href="/?cat=5">Events</a></li>
			<li>|</li>
			<li><a href="/?page_id=163">Calendar</a></li>
			<li>|</li>
			<li><a href="/?page_id=167">Sponsor</a></li>
			<li>|</li>
			<li><a href="/?page_id=165">Contact</a></li>
			</ul>
		</div>	
</div>


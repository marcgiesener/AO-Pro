<?php
/*
Template Name: Directory
*/
?>

<?php get_header(); ?>

<div class="directoryshell">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<h2 style="clear:both;"><?php the_title(); ?></h2>
<?php the_content(__('(more...)')); ?>
<?php endwhile; endif; ?>



<div id="grouppost">
<!-- posts w/o comments -->
<h2><?php if (have_posts() && isset($post->post_title)) : ?></h2>
<?php query_posts('cat='.get_cat_id_by_name($post->post_title)); ?>
<?php while (have_posts()) : the_post(); ?>


<div class="directory">
<h2 style="clear:both;"><?php the_title(); ?></h2>
<?php the_content('Read the rest of this entry &raquo;'); ?>
</div>

<?php endwhile; ?>
<?php endif; ?>

</div>
</div>


<?php get_footer(); ?>
<?php
function get_cat_id_by_name($strcatname) {
	$ncatid=-1;
	
	global $wpdb, $wp_query;

	$query = "select cat_ID, cat_name from wp_categories where cat_name='".$strcatname."'";
	$allcats = $wpdb->get_results($query);

	if ( is_array($allcats) )
	foreach ($allcats as $thiscat) {
		$ncatid=$thiscat->cat_ID;
	}	
	
	return $ncatid;
}
?>
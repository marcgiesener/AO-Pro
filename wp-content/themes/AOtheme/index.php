<?php get_header(); ?>

<div class="content"> //all content on the page
	
<div class="containedcontent"> //content contained on the page 

	<?php get_sidebar(); ?> //Left sidebar of page

	<div id="maincol"> //Right side of page (main page)

		<div id="featured_story"> //Featured box with most recent date

			<h3 class="normal_head">IMPORTANT</h3>

			<div class="boxpadding">

				<?php $feature_query = new WP_Query('cat=14&showposts=1'); ?>

				<?php while ($feature_query->have_posts()) : $feature_query->the_post(); ?>

				<h2><?php the_title(); ?></h2>

				<h5><?php the_time('F jS, Y'); ?></h5>

					<div class="copyspace">

						<?php if(get_post_meta($post->ID, "frontPageImg", true)) ; ?>

						<img class="smimage" src="<?php echo get_post_meta($post->ID, "frontPageImg", true); ?>" />

						<?php the_content(); ?>
<hr width="80%" />
					</div>

				<?php endwhile; ?>

			</div>
	</div>
	
		

</div>
	


<?php get_footer(); ?>

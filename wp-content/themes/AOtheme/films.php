<?php

/*

Template Name: Films

*/

?>



<?php get_header(); ?>



	<?php $films_query = new WP_Query('cat=5&showposts=1'); ?>

				<?php while ($films_query->have_posts()) : $films_query->the_post(); ?>

				<h4><?php the_title(); ?></h4>

				<h5><?php the_time('F jS, Y'); ?></h5>

				<div class="copyspace">

					<img class="smimage" src="<?php echo get_post_meta($post->ID, "frontPageImg", true); ?>" />

					<?php the_content_limit(350, "[Read more]"); ?>

				<?php endwhile; ?>





<?php get_footer(); ?>
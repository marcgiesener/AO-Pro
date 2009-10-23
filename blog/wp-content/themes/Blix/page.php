<?php get_header(); ?>

<!-- content ................................. -->
<div id="content">

<?php if (have_posts()) : ?>

<?php while (have_posts()) : the_post(); ?>

		<h2><?php the_title(); ?></h2>

		<?php the_content(); ?>

<?php endwhile; ?>

<?php endif; ?>

</div> <!-- /content -->

<?php get_footer(); ?>
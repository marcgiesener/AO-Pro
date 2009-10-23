<?php get_header(); ?>



<div class="content">

	

<div class="containedcontent">



	<?php get_sidebar(); ?>



	<div id="maincol">


<?php $ftop_query = new WP_Query('cat=7&showposts=1'); ?>

		<?php if ($ftop_query->have_posts()) { ?>
		<div id="featured_story">



			<span class="headstyle3"><img src="/images/featuredheader.jpg" /></span>



			<div class="boxpadding">



				<?php $feature_query = new WP_Query('cat=7&showposts=1'); ?>



				<?php while ($feature_query->have_posts()) : $feature_query->the_post(); ?>



				<h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>



				<h5><?php the_time('F jS, Y'); ?></h5>



					<div class="copyspace">


						<?php the_content(); ?>



					</div>



				<?php endwhile; ?>



			</div>

	</div>
<?php } ?>
	

		


		<div id="films">

			<span class="headstyle3"><img src="/images/filmsheader.jpg" /></span>

			<div class="boxpadding">

				<div class="copyspace">
				<?php $films_query = new WP_Query('cat=4&showposts=1'); ?>

				<?php while ($films_query->have_posts()) : $films_query->the_post(); ?>

				<h2><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

				<h5>Showing on <?php echo get_post_meta($post->ID, "Date", true); ?></h5>

				

					<img class="smimage" width="150" src="<?php echo get_post_meta($post->ID, "frontPageImg", true); ?>" />

					<p><b>Where/When:</b> <?php echo get_post_meta($post->ID, "Location", true); ?></p>

					<p><b>Cost:</b> <?php echo get_post_meta($post->ID, "Cost", true); ?></p>

					<p>&nbsp;</p>

					<?php the_content(); ?>

					

				<?php endwhile; ?>

				<hr width="60%"/>

				<h5>Upcoming Films</h5>

				<?php



					 $querystr = "

						SELECT * FROM $wpdb->posts

						LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)

						LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)

						LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)

						WHERE $wpdb->term_taxonomy.term_id = 4

						AND $wpdb->term_taxonomy.taxonomy = 'category'

					    AND $wpdb->posts.post_status = 'future' 

						AND $wpdb->posts.post_type = 'post'

						AND $wpdb->postmeta.meta_key = '_edit_lock'

					    ORDER BY $wpdb->posts.post_date ASC

					 ";





					 $pageposts = $wpdb->get_results($querystr, OBJECT);



															?>

<?php $x = 0; ?>

					 <?php if ($pageposts): ?>

					  <?php foreach ($pageposts as $post): ?>

					    <?php setup_postdata($post); ?>


<?php if($x < 4): ?>

					    <div class="smpost" id="post-<?php the_ID(); ?>">

					      <h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">

					      <?php the_title(); ?></a></h2>

					      <small>Showing On: <?php echo get_post_meta($post->ID, "Date", true); ?></small><br />

						  <small>Cost: <?php echo get_post_meta($post->ID, "Cost", true); ?></small>

					      

					    </div>

<?php $x++; ?>
<?php endif; ?>

					  <?php endforeach; ?>

					  

					  <?php else : ?>

					    <h2 class="center">No Upcoming Films Right Now</h2>

					 


 <?php endif; ?>



				</div>

				

			</div>

		</div>

		


</div>



</div>

	





<?php get_footer(); ?>

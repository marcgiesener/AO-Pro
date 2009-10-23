	<div id="sidebar">

		<div id="picture_Area">

			<span class="headstyle1"><img src="/images/photosheader.jpg" /></span>

				<div id="myGallery">

				<?php $photo_query = new WP_Query('cat=8&showposts=10'); ?>

				<?php while ($photo_query->have_posts()) : $photo_query->the_post(); ?>

					<div class="imageElement">

						<h3><?php the_title(); ?></h3>

						<p><?php echo get_post_meta($post->ID, "photosubtitle", true); ?></p>

						<img src="<?php echo get_post_meta($post->ID, "photolink", true); ?>" class="full" />

					</div>

				<?php endwhile; ?>

				</div> 		

		</div>

	

		<?php $bntop_query = new WP_Query('cat=9&showposts=1'); ?>

		<?php if ($bntop_query->have_posts()) { ?>

		<div id="breakingnews">

			<span class="headstyle2"><img src="/images/breaking-newsheader.jpg" /></span>

			<div class="boxpadding">

				<?php $bn_query = new WP_Query('cat=9&showposts=1'); ?>

				<?php while ($bn_query->have_posts()) : $bn_query->the_post(); ?>

				<h2><?php the_title(); ?></h2>

				<h5>Posted: <?php the_time('F jS, Y'); ?></h5>

				<div class="breaking_copy"><?php the_content('Read the rest of this entry &raquo;'); ?></div>

				<?php endwhile; ?>

			</div>

		</div>

		

		<?php } ?>

				
		
		</div>

		</div>

	



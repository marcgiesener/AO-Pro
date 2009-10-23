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


		<div id="films">

			<span class="headstyle1"><img src="/images/filmsheader.jpg" /></span>

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

		

		<div id="upcomingevents">

			<span class="headstyle1"><img src="/images/eventsheader.jpg" /></span>

			<div class="boxpadding">

				<?php



					 $querystr = "

						SELECT * FROM $wpdb->posts

						LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)

						LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)

						LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)

						WHERE $wpdb->term_taxonomy.term_id = 5

						AND $wpdb->term_taxonomy.taxonomy = 'category'

					    AND $wpdb->posts.post_status = 'future' 

						AND $wpdb->posts.post_type = 'post'

						AND $wpdb->postmeta.meta_key = '_edit_lock'

					    ORDER BY $wpdb->posts.post_date ASC

					 ";





					 $pageposts = $wpdb->get_results($querystr, OBJECT);



															?>

					 <?php if ($pageposts): ?>

					  <?php foreach ($pageposts as $post): ?>

					    <?php setup_postdata($post); ?>

<?php $y = 0; ?>
<?php if ($y < 5): ?>

					    <div class="smpost" id="post-<?php the_ID(); ?>">

					      <h2>

					      <?php the_title(); ?></h2>

					      <small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>

					      <p><?php echo get_post_meta($post->ID, "eventteaser", true); ?></p>

					    </div>

<?php $y++; ?>
<?php endif; ?>

					  <?php endforeach; ?>

					  

					  <?php else : ?>

					    <h2 class="center">No Upcoming Events Right Now</h2>

					  <?php endif; ?>

				

				

				

				

				

				

				

				

				

				

				<!--

				<?php $films_query = new WP_Query('cat=6&showposts=4'); ?>

				<?php while ($films_query->have_posts()) : $films_query->the_post(); ?>

				<li><div class="smpost"><h4><?php if (get_post_meta($post->ID, "link", true)) { ?>

					<a href="<?php echo get_post_meta($post->ID, "link", true); ?>">

					<?php the_title(); ?>

					</a></h4><h5><?php echo get_post_meta($post->ID, "date", true); ?></h5>

					<?php }else { ?>

					<h4> <?php the_title(); ?> </h4><h5><?php echo get_post_meta($post->ID, "date", true); ?></h5>

					<?php } ?>

					

					<?php the_content('Read the rest of this entry &raquo;'); ?>

					</div>

				<?php endwhile; ?>

				-->

			</div>

		</div>

		

		<div id="listserv">

		<h3>Sign up for the A&O Listserve</h3>

			<span style="font-weight:800;">Sign up to keep up to date with what's going on within A&amp;O<br /><br /></span>

			<div id="response_div">

			<form>

				<table><tr><td>First Name</td></tr>

				<tr><td><input type="text" id="list_firstname" name="firstname" /></td></tr>

				<tr><td>Last Name</td></tr>

				<tr><td><input type="text" id="list_lastname" name="lastname" /></td></tr>

				<tr><td>E-mail</td></tr>

				<tr><td><input type="text" id="list_email" name="email" /></td></tr>

				<tr><td><br /><input type="button" onclick="javascript:signup();" value="Sign up!" /></td></tr>

				</table>

			</form>

			</div>

		</div>

		</div>

	




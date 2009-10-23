<?php get_header(); ?>

	<div id="content" class="singlepost">

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		

		<div class="post" id="post-<?php the_ID(); ?>">
			<h2><?php the_title(); ?></h2>

			<div class="entry">
			<?php $dakey = get_post_meta($post->ID, "Date", true); ?>
				<?php if ($dakey) { ?>
				<?php echo "<div class='filminfo'>"; ?>
				<?php echo "<div style = 'display:inline;' ><img class='smimage' width='150' src ='" . get_post_meta($post->ID, "frontPageImg", true) . "' /></div><div style = 'display:inline;'><span style = 'font-weight:900; font-size:10pt;'>Screening Info</span><br /><b>Date:</b> " . get_post_meta($post->ID, "Date", true) . "<br /><b>Location: </b>" . get_post_meta($post->ID, "Location", true) . "<br /><b>Cost: </b>" . get_post_meta($post->ID, "Cost", true) . "</div>"; ?>
				<?php echo "</div><span style = 'font-weight:900; font-size:10pt; margin-top:10px;'>About the Film</span>"; ?>
				<?php }else { ?>
				<small><?php the_time('l, F jS, Y') ?></small>
				<?php } ?>
				<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				<?php the_tags( '<p>Tags: ', ', ', '</p>'); ?>

				<p class="postmetadata alt">
					<small>
						
						See more <?php the_category(', ') ?>.
						

						<?php  edit_post_link('Edit this entry','','.'); ?>

					</small>
				</p>

			</div>
		</div>

	<?php comments_template(); ?>

	<?php endwhile; else: ?>

		<p>Sorry, no posts matched your criteria.</p>

<?php endif; ?>

	</div>

<?php get_footer(); ?>

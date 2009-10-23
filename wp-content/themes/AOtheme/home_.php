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
	

		

		<div id="post_area">



		<span class="headstyle3"><img src="/images/whats-happeningheader.jpg" /></span>



		<div class="boxpadding">



		

	<?php rewind_posts(); ?>



	<?php if (have_posts()) : ?>



	

		<?php while (have_posts()) : the_post(); ?>



			<?php if (in_category('9') || in_category('5') || in_category('7') || in_category('4') || in_category('8')) continue; ?>



		

			<div class="post" id="post-<?php the_ID(); ?>">



				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>



				<small><?php the_time('F jS, Y') ?> <!-- by <?php the_author() ?> --></small>





				<div class="entry">



					<?php the_content('Read the rest of this entry &raquo;'); ?>



				</div>





				<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>



			</div>



<hr width="80%" />

		<?php endwhile; ?>





		<div class="navigation">



			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>



			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>



		</div>

		



	<?php else : ?>





		<h2 class="center">Not Found</h2>



		<p class="center">Sorry, but you are looking for something that isn't here.</p>



		<?php include (TEMPLATEPATH . "/searchform.php"); ?>





	<?php endif; ?>



		</div>



	</div>





</div>



</div>



</div>

	





<?php get_footer(); ?>


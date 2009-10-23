<?php get_header(); ?>

	
    <div id="content" class="singlepost">
<?php if (is_category('3') || is_category('6')) {include('sidebar2.php'); ?>
<?php echo "<div style='width:520px; float:left;' class='postcontainer'>";} ?>
		<?php if (is_category('3')) {echo "<h1>A&O Concerts</h1>";} ?>
        <?php if (is_category('6')) {echo "<h1>A&O Speakers</h1>";} ?>
        
		<?php if (have_posts()) : ?>

 	  <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
 	  

		<?php while (have_posts()) : the_post(); ?>
		<div class="post padtop">
			
				<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				

				<div class="entry">
				<?php $dakey = get_post_meta($post->ID, "Date", true); ?>
				<?php if ($dakey) { ?>
				<?php echo "<div class='filminfo'>"; ?>
				<?php echo "<div style = 'display:inline;' ><img class='smimage' width='150' src ='" . get_post_meta($post->ID, "frontPageImg", true) . "' /></div><div style = 'display:inline;'><span style = 'font-weight:900; font-size:10pt;'>Screening Info</span><br /><b>Date:</b> " . get_post_meta($post->ID, "Date", true) . "<br /><b>Location: </b>" . get_post_meta($post->ID, "Location", true) . "<br /><b>Cost: </b>" . get_post_meta($post->ID, "Cost", true) . "</div>"; ?>
				<?php echo "</div><span style = 'font-weight:900; font-size:10pt; margin-top:5px;'>About the Film</span>"; ?>
				<?php }else { ?>
				<small><?php the_time('l, F jS, Y') ?></small>
				<?php } ?>
					<?php the_content() ?>
				</div>

				<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
                
                <hr />

			</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>

	<?php endif; ?>

<?php if (is_category('3') || is_category('6')) {echo "</div>";} ?>
	</div>


<?php get_footer(); ?>

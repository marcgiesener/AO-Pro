<!-- comments ................................. -->
<div id="comments">

<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>
				<p class="nocomments"><?php _e("This post is password protected. Enter the password to view comments."); ?><p>
				<?php
				return;
            }
        }
		$commentalt = '';
		$commentcount = 1;
?>

<?php if ($comments) : ?>

	<h2><?php comments_number('No Comments yet', '1 Comment', '% Comments' ); if($post->comment_status == "open") { ?> <a href="#commentform" class="more">Add your own</a><?php } ?></h2>

	<ul>

	<?php foreach ($comments as $comment) : ?>

	<li id="comment-<?php echo $commentcount ?>" class="<?php comment_type('comment','trackback','pingback'); ?>">
	<p class="header<?php echo $commentalt; ?>"><strong><?php echo $commentcount ?>.</strong>

	<?php if (function_exists("gravatar")) { ?><img src="<?php gravatar("R", 23); ?>" alt="" class="gravatar" /><?php } ?>

	<?php if ($comment->comment_type == "comment") comment_author_link();
		  else {
		  		strlen($comment->comment_author)?$author=substr($comment->comment_author,0,25)."&hellip":$author=substr($comment->comment_author,0,25);
		  		echo '<a href="'.$comment->comment_author_url.'">'.$author.'</a>';

		  }
	?> &nbsp;|&nbsp; <?php comment_date('F jS, Y') ?> at <?php comment_time() ?></p>
	<?php if ($comment->comment_approved == '0') : ?><p><em>Your comment is awaiting moderation.</em></p><?php endif; ?>
	<?php comment_text() ?>
	<?php edit_comment_link('Edit Comment','<span class="editlink">','</span>'); ?>
	</li>

	<?php
	($commentalt == " alt")?$commentalt="":$commentalt=" alt";
	$commentcount++;
	?>

<?php endforeach; /* end for each comment */ ?>

</ul>

<?php endif; ?>

<?php if ($post->comment_status == "open") : ?>

	<h2>Leave a Comment</h2>

	<?php if (get_option('comment_registration') && !$user_ID) : ?>
	<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>

<?php else : ?>

	<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
		<fieldset>

	<?php if ($user_ID) : ?>

		<p class="info">Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>">Logout</a>.</p>

<?php else : ?>

			<p><label for="author">Name</label> <input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" tabindex="1" /> <?php if ($req) echo "<em>Required</em>"; ?></p>
			<p><label for="email">Email</label> <input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" tabindex="2" /> <em><?php if ($req) echo "Required, "; ?>hidden</em></p>
			<p><label for="url">Url</label> <input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" tabindex="3" /></p>

<?php endif; ?>

			<p><label for="comment">Comment</label> <textarea name="comment" id="comment" cols="45" rows="10" tabindex="4"></textarea></p>
			<p><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
			<input type="submit" name="submit" value="Submit" class="button" tabindex="5" /></p>

	  	</fieldset>
	<?php do_action('comment_form', $post->ID); ?>
	</form>

	<p><strong>Some HTML allowed:</strong><br/><?php echo allowed_tags(); ?></p>

<?php endif; // If registration required and not logged in ?>

<?php endif; // if you delete this the sky will fall on your head ?>

<?php if ($post-> comment_status == "open" && $post->ping_status == "open") { ?>
	<p><a href="<?php trackback_url(display); ?>">Trackback this post</a> &nbsp;|&nbsp; <?php comments_rss_link('Subscribe to the comments via RSS Feed'); ?></p>
<?php } elseif ($post-> comment_status == "open") {?>
	<p><?php comments_rss_link('Subscribe to the comments via RSS Feed'); ?></p>
<?php } elseif ($post->ping_status == "open") {?>
	<p><a href="<?php trackback_url(display); ?>">Trackback this post</a></p>
<?php } ?>

</div> <!-- /comments -->
</div>

<div id="sidebar">

<ul>
	<?php wp_list_pages('title_li=<h2>' . __('Pages') . '</h2>' ); ?>
	<li>
		<h2><?php _e('Archives'); ?></h2>
		<ul>
		<?php wp_get_archives('type=monthly'); ?>
		</ul>
	</li>
	<li>
		<h2><?php _e('Categories'); ?></h2>
		<ul>
		<?php wp_list_cats(); ?> 
		</ul>
	</li>
	<li>
		<h2><?php _e('Search'); ?></h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>
	</li>
	
	<?php if (function_exists('wp_theme_switcher')) { ?>
	<li>
		<h2><?php _e('Themes'); ?></h2>
		<?php wp_theme_switcher(); ?>
	</li>
	<?php } ?>

	<?php if ( is_home() ) { get_links_list(); } ?>	
	
	<li>
		<h2><?php _e('Meta'); ?></h2>
		<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS 2.0'); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php _e('The latest comments to all posts in RSS'); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="http://wordpress.org" title="<?php _e('Powered by Wordpress, state-of-the-art semantic personal publishing platform.'); ?>">Wordpress</a></li>
			<?php wp_meta(); ?>
		</ul>
	</li>
</ul>

</div>

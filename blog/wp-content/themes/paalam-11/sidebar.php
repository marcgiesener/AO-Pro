	<div id="sidebar" class="sidecol">
	<ul>
<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar() ) : else : ?>
  <?php if(is_home()) {?>
<li>
	<h2>About</h2>
	<p>
	<strong><?php bloginfo('name');?></strong><br/>
	<?php bloginfo('description');?><br/>
	</p>	
</li>
<li>
<h2>Sign up for the A&O Listserv!</h2>

<p>To sign up for the A&O Listserv, e-mail <a href="mailto:listserv@listserv.it.northwestern.edu">listserv@listserv.it.northwestern.edu</a> with no subject and no other text, and write "SUBSCRIBE A+OPRODUCTIONS firstname lastname" (replacing with your name). E-mail <a href="mailto:aoproductions@northwestern.edu">aoproductions@northwestern.edu</a> if you have trouble subscribing.</p>

</li>
<!--<li>
    <h2>A&O Feeds</h2>
    <ul>
    
    <h3>Coming soon!</h3>
      
<li class="feed"><a title="A&O News" href="<?php bloginfo('rss2_url'); ?>">A&O News</a></li>
      <li class="feed"><a title="Films" href="<?php bloginfo('rss2_url'); ?>">Films</a></li>
      <li class="feed"><a title="Concerts" href="<?php bloginfo('comments_rss2_url'); ?>">Concerts</a></li>

   </ul>
  </li>--> 
<?php }?>
<li>
  <h2><?php _e('Search'); ?></h2>
	<form id="searchform" method="get" action="<?php bloginfo('siteurl')?>/">
		<input type="text" name="s" id="s" class="textbox" value="<?php echo wp_specialchars($s, 1); ?>" />
		<input id="btnSearch" type="submit" name="submit" value="<?php _e('Go'); ?>" />
	</form>
  </li>  
<?php if(is_home()) get_links_list(); ?>        
  <li>
    <h2>
      <?php _e('Categories'); ?>
    </h2>
    <ul>
      <?php wp_list_cats('optioncount=1&hierarchical=1');    ?>
    </ul>
  </li>
  <li>
    <h2>
      <?php _e('Monthly'); ?>
    </h2>
    <ul>
      <?php wp_get_archives('type=monthly&show_post_count=true'); ?>
    </ul>
  </li>

    <?php if(is_home()) { ?>
        <?php } ?>
    <?php endif; ?>
</ul>
	</div>
	<div style="clear:both;"></div>
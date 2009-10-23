<?php get_header();?>
<div id="main">
	<div id="content">
	        <div class="post">
            <p class="date">
              <span class="month">
                <?php echo date('M'); ?>
              </span>
              <span class="day">
                <?php echo date('d'); ?>
              </span>
              <span class="year">
                <?php echo date('Y'); ?>
              </span>
            </p>
            <h2 class="title">404 - The Server can not find it !</h2>
            <div class="entry">
              <p>The post or the page that you are looking for, is not available at this time.
              It could have been moved / deleted.</p>
              <p>Please browse through the archives / search through the site.</p>
      			</div>
            <p class="comments">
              Posted as "Not Found"  
            </p>	          
	        </div>      
	</div>
  <?php get_sidebar();?>
  <?php get_footer();?>
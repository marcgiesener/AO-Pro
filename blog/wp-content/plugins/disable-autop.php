<?php
/*
Plugin Name: Disable wpautop
Plugin URI: http://www.urbangiraffe.com/plugins/disable-wpautop
Description: Disables WordPress automatic paragraph formatting
Author: John Godley
Version: 1.0
Author URI: http://www.urbangiraffe.com/
*/

remove_filter ('the_content',  'wpautop');
remove_filter ('comment_text', 'wpautop');

?>
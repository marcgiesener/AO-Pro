<?php
/*
Plugin Name: WP Limit Posts Automatically
Plugin URI: http://www.jenst.se
Description: Limit your posts automatically. This way you don't need the use of the more tag. You can limit posts by <em>letter, word or paragraph</em> and apply it to <em>home, categories, archive and search</em>.
Version: 0.7
Author: Jens T&ouml;rnell
Author URI: http://www.jenst.se
*/

function lpa_replace_content($content)
{
	// Get data from database
	$lpa_post_wordcut = get_option("lpa_post_wordcut");
	
	$lpa_post_letters = get_option("lpa_post_letters");
	$lpa_post_linktext = get_option("lpa_post_linktext");
	$lpa_post_ending = get_option("lpa_post_ending");
	
	$lpa_post_home = get_option("lpa_post_home");
	$lpa_post_category = get_option("lpa_post_category");
	$lpa_post_archive = get_option("lpa_post_archive");
	$lpa_post_search = get_option("lpa_post_search");
	$lpa_striptags = get_option("lpa_striptags");

	// If post letters are not set, default is set to 300
	if ($lpa_post_letters == ""){
		$lpa_post_letters = 300;
	}
	if ($lpa_post_wordcut == "Wordcut")
	{
		// Check what options is set
		if ( (is_home() && $lpa_post_home == "on") || (is_category() && $lpa_post_category == "on") || (is_archive() && $lpa_post_archive == "on") || (is_search() && $lpa_post_search == "on") ) {
		
			// Get data to see if more tag is used
			global $post;
			$ismoretag = explode('<!--',$post->post_content);
			$ismoretag2 = explode('-->', $ismoretag[1]);
			
			if ($lpa_striptags == "on") {
				$content2 = "<p>" . strip_tags($content, '');
			}
		
			// Limit the post by wordwarp to check for more tag
			$prev_content = wordwrap($content, $lpa_post_letters, "[lpa]");
			$cuttext = explode ('[lpa]', $prev_content);
			$end_string = substr($cuttext[0], -5);
			$endingp = "";
			
			// Limit the post by wordwarp
			$prev_content2 = wordwrap($content2, $lpa_post_letters, "[lpa]");
			$cuttext2 = explode ('[lpa]', $prev_content2);
			$end_string2 = substr($cuttext2[0], -5);
			$endingp2 = "";
			
			// If end of p-tag is missing create one
			if ($end_string == "</p>\n") {
				$cuttext[0]=substr($cuttext[0],0,(strlen($cuttext[0])-5));
			}
			// Check if more tag is used
			if ($ismoretag2[0] != "more") {
				if ($lpa_striptags == "on") {
					echo $cuttext2[0]; // Add limited post
				}
				else {
					echo $cuttext[0]; // Add limited post
				}
				// Add link if link text exists
				if ($lpa_post_linktext != ""){
					echo " <a href='" .get_permalink(). "' rel=\"nofollow\">".utf8_encode($lpa_post_linktext)."</a>";
				}
				echo "</p>";
			}
			else {
				return $content;
			}
		}
		else {
			return $content;
		}
	}
	else if ($lpa_post_wordcut == "Lettercut") {
		// Check what options is set
		if ( (is_home() && $lpa_post_home == "on") || (is_category() && $lpa_post_category == "on") || (is_archive() && $lpa_post_archive == "on") || (is_search() && $lpa_post_search == "on") ) {
			
			// Get data to see if more tag is used
			global $post;
			$ismoretag = explode('<!--',$post->post_content);
			$ismoretag2 = explode('-->', $ismoretag[1]);
			
			if ($lpa_striptags == "on") {
				$content2 = "<p>" . strip_tags($content, '');
			}
			
			// Limit the post by letter to check for more tag
			$new_string2 = substr($content2, 0,$lpa_post_letters+3);
			$end_string2 = substr($new_string2, -5);
			$endingp = "";
			
			// Limit the post by letter
			$new_string = substr($content, 0,$lpa_post_letters+3);
			$end_string = substr($new_string, -5);
			$endingp = "";
			
			// If end of p-tag is missing create one
			if ($end_string == "</p>\n") {
				$new_string=substr($new_string,0,(strlen($new_string)-5));
			}

			// Check if more tag is used
			if ($ismoretag2[0] != "more") {
				
				if ($lpa_striptags == "on") {
					echo $new_string2; // Add limited post
				}
				else {
					echo $new_string; // Add limited post
				}
				
				echo $lpa_post_ending; // Add limited ending
				// Add link if link text exists
				if ($lpa_post_linktext != ""){
					echo " <a href='" .get_permalink(). "' rel=\"nofollow\">".utf8_encode($lpa_post_linktext)."</a>";
				}
				echo "</p>";
			}
			else {
				return $content;
			}
		}
		else {
			return $content;
		}
	}
	else if ($lpa_post_wordcut == "Paragraphcut") {
		if ( (is_home() && $lpa_post_home == "on") || (is_category() && $lpa_post_category == "on") || (is_archive() && $lpa_post_archive == "on") || (is_search() && $lpa_post_search == "on") ) {
			$paragraphcut = explode('</p>', $content);
			global $post;
			$ismoretag = explode('<!--',$post->post_content);
			$ismoretag2 = explode('-->', $ismoretag[1]);
			if ($ismoretag2[0] != "more") {
				echo $paragraphcut[0];
				echo $lpa_post_ending;
				if ($lpa_post_linktext != ""){
					echo " <a href='" .get_permalink(). "' rel=\"nofollow\">".utf8_encode($lpa_post_linktext)."</a>";
				}
				echo "</p>";
			}
			else {
				return $content;
			}
		}
		else {
			return $content;
		}
	}
	else {
		return $content;
	}
}
add_filter('the_content','lpa_replace_content');

function lpa_admin(){
    if(isset($_POST['submitted'])){
		// Get data from input fields
        $wordcut = $_POST['lpa_post_wordcut'];
		
		$letters = $_POST['lpa_post_letters'];
		$linktext = $_POST['lpa_post_linktext'];
		$ending = $_POST['lpa_post_ending'];
		
		$home = $_POST['lpa_post_home'];
		$category = $_POST['lpa_post_category'];
		$archive = $_POST['lpa_post_archive'];
		$search = $_POST['lpa_post_search'];
		$striptags = $_POST['lpa_striptags'];
        
		// Upload / update data to database
		update_option("lpa_post_wordcut", $wordcut);
		
		update_option("lpa_post_letters", $letters);
		update_option("lpa_post_linktext", $linktext);
		update_option("lpa_post_ending", $ending);
		
		update_option("lpa_post_home", $home);
		update_option("lpa_post_category", $category);
		update_option("lpa_post_archive", $archive);
		update_option("lpa_post_search", $search);
		
		update_option("lpa_striptags", $striptags);
		
        //Options updated message
        echo "<div id=\"message\" class=\"updated fade\"><p><strong>WP Limit Post Automatically Options updated.</strong></p></div>";
    }
	?>
    <div class="wrap">
    <h2>Limit Posts Options</h2>
	<?php
	$limitpostby = get_option("lpa_post_wordcut");
	$input_letters = get_option("lpa_post_letters");
	$input_linktext = get_option("lpa_post_linktext");
	$input_ending = get_option("lpa_post_ending");
	$lpa_home = get_option("lpa_post_home");
	$lpa_category = get_option("lpa_post_category");
	$lpa_archive = get_option("lpa_post_archive");
	$lpa_search = get_option("lpa_post_search");
	$lpa_striptags = get_option("lpa_striptags");
	?>
	
    <form method="post" name="options" target="_self">
	<h3 style="font-weight: normal;">Limit post by:</h3>
	<table width="100%" border="0" cellspacing="2" cellpadding="2">
		<tr>
			<td width="25%"><input type="radio" name="lpa_post_wordcut" value="Lettercut" <?php if ($limitpostby == "Lettercut"){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('letternumber').style.display='';" /> Letter</td>
			<td width="25%"><input type="radio" name="lpa_post_wordcut" value="Wordcut" <?php if ($limitpostby == "Wordcut"){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('letternumber').style.display='';" /> Word</td>
			<td width="25%"><input type="radio" name="lpa_post_wordcut" value="Paragraphcut" <?php if ($limitpostby == "Paragraphcut"){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('letternumber').style.display='none';" /> First paragraph (recommended)</td>
			<td width="25%"><input type="radio" name="lpa_post_wordcut" value="Nocut" <?php if ($limitpostby == "Nocut"){ echo 'checked="checked"'; } ?> onclick="javascript:document.getElementById('letternumber').style.display='none';" /> None</td>  
		</tr>
	</table>
	<h3 style="font-weight: normal;">Post display:</h3>
	<table>
		<tr id="letternumber" <?php if ($limitpostby=="Paragraphcut" || $limitpostby=="Nocut"){ echo 'style="display: none;"'; }?>>
			<td colspan="4"><input name="lpa_post_letters" type="text" value="<?php echo $input_letters; ?>" /> <strong>Number of letters</strong> (if blank <em>300</em> is set)</td>
		</tr>
		<tr>
			<td colspan="4"><input name="lpa_post_ending" type="text" value="<?php echo $input_ending; ?>" /> <strong>Text ending</strong></td>
		</tr>
		<tr>
			<td colspan="4"><input name="lpa_post_linktext" type="text" value="<?php echo $input_linktext; ?>" /> <strong>Read more linktext</strong></td>
		</tr>
	</table>
	<h3 style="font-weight: normal;">Automatically limit post in:</h3>
	<table>
		<tr>
			<td><input type="checkbox" name="lpa_post_home" <?php if($lpa_home == "on"){ echo 'checked="checked"'; } ?>/> Home &nbsp;</td>
			<td><input type="checkbox" name="lpa_post_category" <?php if($lpa_category == "on"){ echo 'checked="checked"'; } ?>/> Category &nbsp;</td>
			<td><input type="checkbox" name="lpa_post_archive" <?php if($lpa_archive == "on"){ echo 'checked="checked"'; } ?>/> Archive &nbsp;</td>
			<td><input type="checkbox" name="lpa_post_search" <?php if($lpa_search == "on"){ echo 'checked="checked"'; } ?>/> Search &nbsp;</td>
		</tr>
	</table>
	<h3 style="font-weight: normal;">Avoid break through code errors:</h3>
	<table>
		<tr>
			<td><input type="checkbox" name="lpa_striptags" <?php if($lpa_striptags == "on"){ echo 'checked="checked"'; } ?>/> Strip tags (disables images, videos, links in post preview)</td>
		</tr>
	</table>
	<br />
	<em>To enable / disable Wordpress default more-tag, go to <a href="options-reading.php">Options / Reading</a></em><br /><br />
	<h2>Usage</h2>
	<ul>
		<li>I recommend to use limit by paragraph. That way it limit the posts by &lt;/p&gt;</li>
		<li>"Strip tags" removes the coded tags and just displays the text. That way the images, videos and links are not shown in the page preview. This option prevents code errors when posts are cut. This option only apply to limit by letter or word.</li>
	</ul>
<p class="submit">
<input name="submitted" type="hidden" value="yes" />
<input type="submit" name="Submit" value="Update Options &raquo;" />
</p>
</form>

</div>

<?php } 
//Add the options page in the admin panel
function lpa_addpage() {
    add_submenu_page('options-general.php', 'Limit Posts Options', 'Limit Posts Options', 10, __FILE__, 'lpa_admin');
}
add_action('admin_menu', 'lpa_addpage');
?>
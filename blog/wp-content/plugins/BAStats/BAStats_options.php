<?php
/*
Version: 1.0&beta; build 8

BAStats - calculates statistics for a WordPress weblog.
Copyright (c) 2004 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/


global $wpdb;

function check_option($opt)
{
	$settings = get_settings('bas_options');
	if(in_array($opt, $settings)) echo ' checked="checked"';
}

$default_options = array();
$default_settings = array (
	'referer_spam' => "nutzu.com",
);

switch($_POST['action'])
{
case 'label':
	$wpdb->query("UPDATE {$wpdb->pages} SET page_label = '{$_POST['Label']}' WHERE page_string = '{$_POST['URI']}';");
	$result = "Added Label '{$_POST['Label']}' to URI '{$_POST['URI']}'";
	break;
case 'options':
	update_option('bas_options', $_POST['bas_options']);
	update_option('bas_settings', $_POST['bas_settings']);
	break;
case 'purge':
	switch($_POST['range'])
	{
	case 'all':
		$wpdb->query('TRUNCATE {$wpdb->visitors};');
		$wpdb->query('TRUNCATE {$wpdb->refer};');
		$wpdb->query('TRUNCATE {$wpdb->os};');
		$wpdb->query('TRUNCATE {$wpdb->ua};');
		$wpdb->query('TRUNCATE {$wpdb->log};');
		$wpdb->query('TRUNCATE {$wpdb->pages};');
		$wpdb->query('TRUNCATE {$wpdb->searches};');
		$result = "Purged all stats.";
		break;
	case 'hits':
		$wpdb->query('TRUNCATE {$wpdb->log};');
		$result = "Purged all hits.";
		break;
	case 'oldhits':
		$monthago = date('Y-m-d', time()-60*60*24*30);
		$wpdb->query("DELETE FROM {$wpdb->log} WHERE stamp < '{$monthago}';");
		$result = "Purged all hits older than 30 days.";
		break;
	}
default:
	add_option('bas_options', $default_options);
	add_option('bas_settings', $default_settings);
}

$options = get_settings('bas_options');
$settings = get_settings('bas_settings');

if($result != '')
{
	echo "<div class=\"updated\"><p>{$result}</p></div>";
}
?>

<div class="wrap">
<form method="post">
<input type="hidden" name="action" value="options" />
<h2>BA Stats Options</h2>
<p>Set the options below according to your loggin preferences, then click Update Options to commit your choices.</p>

<h3>Basic Tracking Options</h3>

<ul>
	<li>
		<label><input type="checkbox" name="bas_options[]" value="log_admins" <?php check_option('log_admins'); ?>/> Track page hits for logged in WordPress users of level 8+.</label>
	</li>
	<li>
		<label><input type="checkbox" name="bas_options[]" value="log_console" <?php check_option('log_console'); ?>/> Track page hits for the WordPress admin console pages.</label>
	</li>
	<li>
		<label><input type="checkbox" name="bas_options[]" value="log_content" <?php check_option('log_content'); ?>/> Track page hits for <code>/wp-content/</code> area.</label>
	</li>
	<li>
	<?php
	$serveraddr = $_SERVER['LOCAL_ADDR'];
	if($serveraddr == '') $serveraddr = $_SERVER['SERVER_ADDR'];	
	?>
		<label><input type="checkbox" name="bas_options[]" value="log_self" <?php check_option('log_self'); ?>/> Track page hits from this site's IP address (<?php echo $serveraddr; ?> ).</label>
	</li>
</ul>

<!-- hr style="border:0;color:#6699CC;height:1px;background-color:#6699CC;"/>
<h3>Advanced File Tracking</h3>

<ul>
	<li>
		<label><input type="checkbox" name="bas_options[]" value="log_images" <?php check_option('log_images'); ?>/> Track hits on local images (jpg, gif, png).</label>
	</li>
</ul -->


<hr style="border:0;color:#6699CC;height:1px;background-color:#6699CC;"/>
<h3>Referer Spam</h3>

<p>Each of the following lines is indicative of referer spam when BAStats finds it anywhere in the referer header field:</p>
<textarea name="bas_settings[referer_spam]" style="width:50%;height:5em;"><?php echo $settings['referer_spam']; ?></textarea>
<p>What to do about referer spam:</p>
<ul>
	<li><label><input type="checkbox" name="bas_options[]" value="log_spam" <?php check_option('log_spam'); ?>/> Don't log referers that match the spam entries.</label></li>
	<li><label><input type="checkbox" name="bas_options[]" value="die_spam" <?php check_option('die_spam'); ?>/> Immediately stop further script processing of requests that match the spam list.</label></li>
</ul>
<p class="submit"><input type="submit" value="Update Options"></p>
</form>
</div>

<div class="wrap">
<h2>Label Page</h2>
<p>Submit the values in this form to provide a recognizable label for a page that typically appears only as a URI.</p>
<p>For example, you might want to label the page <code>/index.php</code> as "<code>Home Page</code>".</p>
<form method="post">
<input type="hidden" name="action" value="label" />
<label for="label_URI">URI to label: <input type="text" id="label_URI" name="URI" /></label>
<label for="label_text">Label to use: <input type="text" id="label_text" name="Label" /></label>
<p class="submit"><input type="submit" value="Apply Label"></p>
</form>
</div>

<div class="wrap">
<h2>Clear Stats</h2>
<p>Submit this form to purge old stats from your stats tables.</p>
<p>Select the action that best suits your needs from the dropdown, then click Purge.</p>
<form method="post" onsubmit="return confirm('Are you sure you want to perform this action on your stats?  It can\'t be undone.');">
<input type="hidden" name="action" value="purge" />
<label>Action:
<select name="range">
<option value="">Choose one</option>
<option value="all" style="background-color:red;color:white;font-weight:bold;">Purge All Stats</option>
<option value="hits">Purge Only Hits</option>
<option value="oldhits">Purge Only Hits Older Than 30 Days</option>
</select>
</label>
<p class="submit"><input type="submit" value="Purge"></p>
</form>
</div>

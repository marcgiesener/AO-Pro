<?php

/** Diable here because PHP4.3 does not make the global
 Serious bug?

$mutex_filename = 'wp_cache_mutex.lock';
$new_cache = false;
*/

function wp_cache_phase2() {
	global $cache_filename, $cache_acceptable_files;

	wp_cache_mutex_init();
	if(function_exists('add_action')) {
		// Post ID is received
		add_action('publish_post', 'wp_cache_post_change', 0);
		add_action('edit_post', 'wp_cache_post_change', 0);
		add_action('delete_post', 'wp_cache_post_change', 0);
		add_action('publish_phone', 'wp_cache_post_change', 0);
		// Coment ID is received
		add_action('trackback_post', 'wp_cache_get_postid_from_comment', 0);
		add_action('pingback_post', 'wp_cache_get_postid_from_comment', 0);
		add_action('comment_post', 'wp_cache_get_postid_from_comment', 0);
		add_action('edit_comment', 'wp_cache_get_postid_from_comment', 0);
		add_action('wp_set_comment_status', 'wp_cache_get_postid_from_comment', 0);
		// No post_id is available
		add_action('delete_comment', 'wp_cache_no_postid', 0);
		add_action('switch_theme', 'wp_cache_no_postid', 0); 
	}
	//$script = basename($_SERVER['SCRIPT_NAME']);
	if( $_SERVER["REQUEST_METHOD"] == 'POST' || get_settings('gzipcompression')) 
		return;
	$script = basename($_SERVER['PHP_SELF']);
	if (!in_array($script, $cache_acceptable_files) && 
			wp_cache_is_rejected($_SERVER["REQUEST_URI"]))
		return;
	if (wp_cache_user_agent_is_rejected()) return;
	ob_start('wp_cache_ob_callback'); 
	register_shutdown_function('wp_cache_ob_end');
}

function wp_cache_is_rejected($uri) {
	global $cache_rejected_uri;

	if (strstr($uri, '/wp-admin/'))
		return true; //we don't allow cacheing wp-admin for security
	foreach ($cache_rejected_uri as $expr) {
		if (strlen($expr) > 0 && strstr($uri, $expr))
			return true;
	}
	return false;
}

function wp_cache_user_agent_is_rejected() {
	global $cache_rejected_user_agent;

	if (!function_exists('apache_request_headers')) return false;
	$headers = apache_request_headers();
	if (!isset($headers["User-Agent"])) return false;
	foreach ($cache_rejected_user_agent as $expr) {
		if (strlen($expr) > 0 && stristr($headers["User-Agent"], $expr))
			return true;
	}
	return false;
}


function wp_cache_mutex_init() {
	global $use_flock, $mutex, $cache_path, $mutex_filename, $sem_id;

	if(!is_bool($use_flock)) {
		if(function_exists('sem_get')) 
			$use_flock = false;
		else
			$use_flock = true;
	}

	if ($use_flock) 
		$mutex = fopen($cache_path . $mutex_filename, 'w');
	else
		$mutex = sem_get($sem_id, 1, 0644 | IPC_CREAT, 1);
}

function wp_cache_writers_entry() {
	global $use_flock, $mutex, $cache_path, $mutex_filename;

	if ($use_flock)
		flock($mutex,  LOCK_EX);
	else
		sem_acquire($mutex);
}

function wp_cache_writers_exit() {
	global $use_flock, $mutex, $cache_path, $mutex_filename;

	if ($use_flock)
		flock($mutex,  LOCK_UN);
	else
		sem_release($mutex);
}

function wp_cache_ob_callback($buffer) {
	global $cache_path, $cache_filename, $meta_file, $wp_start_time;
	global $new_cache, $meta_object, $file_expired;

	/* Mode paranoic, check for closing tags 
	 * we avoid caching incomplete files */
	if (!preg_match('/(<\/html>|<\/rss>|<\/feed>)/i',$buffer) ) {
		$new_cache = false;
		return $buffer;
	}

	$meta_object = new CacheMeta;
	$meta_object->uri = $_SERVER['REQUEST_URI'];
	$meta_object->post = wp_cache_post_id();
	$duration = wp_cache_microtime_diff($wp_start_time, microtime());
	$duration = sprintf("%0.3f", $duration);
	$buffer .= "\n<!-- Dynamic Page Served (once) in $duration seconds -->\n";

	wp_cache_writers_entry();
	$mtime = @filemtime($cache_path . $cache_filename);
	/* Return if:
		the file didn't exist before but it does exist now (another connection created)
		OR
		the file was expired and its mtime is less than 5 seconds
	*/
	if( !((!$file_expired && $mtime) || ($mtime && $file_expired && (time() - $mtime) < 5)) ) {
		$fr = fopen($cache_path . $cache_filename, 'w');
		if (!$fr)
			$buffer = "Couldn't write to: " . $cache_path . $cache_filename . "\n";

		if (preg_match('/<!--mclude|<!--mfunc/', $buffer)) { //Dynamic content
			$store = preg_replace('|<!--mclude (.*?)-->(.*?)<!--/mclude-->|is', 
					"<!--mclude-->\n<?php include_once('" . ABSPATH . "$1'); ?>\n<!--/mclude-->", $buffer);
			$store = preg_replace('|<!--mfunc (.*?)-->(.*?)<!--/mfunc-->|is', 
					"<!--mfunc-->\n<?php $1 ;?>\n<!--/mfunc-->", $store);
			$meta_object->dynamic = true;
			/* Clean function calls in tag */
			$buffer = preg_replace('|<!--mclude (.*?)-->|is', '<!--mclude-->', $buffer);
			$buffer = preg_replace('|<!--mfunc (.*?)-->|is', '<!--mfunc-->', $buffer);
			fputs($fr, $store);
		} else {
			fputs($fr, $buffer);
		}
		$new_cache = true;
		fclose($fr);
	}
	wp_cache_writers_exit();
	return $buffer;
}

function wp_cache_phase2_clean_cache($file_prefix) {
	global $cache_path;

	wp_cache_writers_entry();
	if ( ($handle = opendir( $cache_path )) ) { 
		while ( false !== ($file = readdir($handle))) {
			if ( preg_match("/^$file_prefix/", $file) ) {
				unlink($cache_path . $file);
			}
		}
		closedir($handle);
	}
	wp_cache_writers_exit();
}

function wp_cache_phase2_clean_expired($file_prefix) {
	global $cache_path, $cache_max_time;

	clearstatcache();
	wp_cache_writers_entry();
	$now = time();
	if ( ($handle = opendir( $cache_path )) ) { 
		while ( false !== ($file = readdir($handle))) {
			if ( preg_match("/^$file_prefix/", $file) && 
				(filemtime($cache_path . $file) + $cache_max_time) <= $now  ) {
				unlink($cache_path . $file);
			}
		}
		closedir($handle);
	}
	wp_cache_writers_exit();
}

function wp_cache_ob_end() {
	global $cache_path, $cache_max_time, $file_expired, $file_prefix, $meta_file, $new_cache;
	global $meta_object, $known_headers;

	/* Preparing... with PHP5 is straightforward, use headers_list() */
	if(function_exists('apache_response_headers') ) {
		$response = apache_response_headers();
		$meta_object->headers = array();
		foreach ($known_headers as $key) {
			if(isset($response{$key})) {
				array_push($meta_object->headers, "$key: " . $response{$key});
			}
		}
		/* Not use because it gives problems with some
		 * PHP installations
		if (!$response{'Content-Length'}) {
		// WP does not set content size
			$content_size = ob_get_length();
			@header("Content-Length: $content_size");
			array_push($meta_object->headers, "Content-Length: $content_size");
		}
		*/
		if (!$response{'Last-Modified'}) {
			$value = gmdate('D, d M Y H:i:s') . ' GMT';
			/* Dont send this the first time */
			/* @header('Last-Modified: ' . $value); */
			array_push($meta_object->headers, "Last-Modified: $value");
		}
		if (!$response{'Content-Type'}) {
			$value =  "text/html; charset=\"" . get_settings('blog_charset')  . "\"";
			@header("Content-Type: $value");
			array_push($meta_object->headers, "Content-Type: $value");
		}
	}
	ob_end_clean();
	if ($new_cache) {
		$serial = serialize($meta_object);
		wp_cache_writers_entry();
		$fr = fopen($cache_path . $meta_file, 'w');
		fputs($fr, $serial);
		fclose($fr);
		wp_cache_writers_exit();
	}

	if ($file_expired == false) {
		return;
	}

	// we delete expired files
	flush(); //Ensure we send data to the client
	wp_cache_phase2_clean_expired($file_prefix);
}

function wp_cache_no_postid($id) {
	return wp_cache_post_change(wp_cache_post_id());
}

function wp_cache_get_postid_from_comment($comment_id) {
	$comment = get_commentdata($comment_id, 1, true);
	$postid = $comment['comment_post_ID'];
	// We must check it up again due to WP bugs calling two different actions
	// for delete, for example both wp_set_comment_status and delete_comment 
	// are called whene deleting a comment
	if ($postid > 0) 
		return wp_cache_post_change($postid);
	else 
		return wp_cache_post_change(wp_cache_post_id());
}

function wp_cache_post_change($post_id) {
	global $file_prefix;
	global $cache_path;
	static $last_processed = -1;

	// Avoid cleaning twice the same pages
	if ($post_id == $last_processed) return $post_id;
	$last_processed = $post_id;

	$meta = new CacheMeta;
	$matches = array();
	wp_cache_writers_entry();
	if ( ($handle = opendir( $cache_path )) ) { 
		while ( false !== ($file = readdir($handle))) {
			if ( preg_match("/^($file_prefix.*)\.meta/", $file, $matches) ) {
				$meta_pathname = $cache_path . $file;
				$content_pathname = $cache_path . $matches[1] . ".html";
				if ($post_id > 0 && ($meta = unserialize(@file_get_contents($meta_pathname))) ) {
					if ( !$meta->post || $meta->post == $post_id ) {
						unlink($meta_pathname);
						unlink($content_pathname);
					}
				} else {
					unlink($meta_pathname);
					unlink($content_pathname);
				}

			}
		}
		closedir($handle);
	}
	wp_cache_writers_exit();
	return $post_id;
}

function wp_cache_microtime_diff($a, $b) {
	list($a_dec, $a_sec) = explode(' ', $a);
	list($b_dec, $b_sec) = explode(' ', $b);
	return $b_sec - $a_sec + $b_dec - $a_dec;
}

function wp_cache_post_id() {
	global $posts, $comment_post_ID, $post_ID;
	// We try hard all options. More frequent first.
	if ($post_ID > 0 ) return $post_ID;
	if ($comment_post_ID > 0 )  return $comment_post_ID;
	if (is_single() || is_page()) return $posts[0]->ID;
	if ($_GET['p'] > 0) return $_GET['p'];
	if ($_POST['p'] > 0) return $_POST['p'];
	return 0;
}
?>

<?php
/*
Plugin Name: Subscribe2
Plugin URI: http://www.skippy.net/blog/plugins
Description: Notifies an email list when new entries are posted.
Version: 2.2.0
Author: Scott Merrill
Author URI: http://www.skippy.net/
*/

/*
Copyright (C) 2005 Scott Merrill (skippy@skippy.net)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

http://www.gnu.org/licenses/gpl.html
*/

// use Owen's excellent ButtonSnap library
include('buttonsnap.php');

// change this to TRUE if you're on Dreamhost
// (or any other host that limits the number of recipients
// permitted on each outgoing email message)
define('DREAMHOST', FALSE);

define('S2VERSION', '2.2.0');

// start our class
class subscribe2 {
// variables and constructor are declared at the end
	
	/**
	Load all our strings
	*/
	function load_strings() {
		// adjust the output of Subscribe2 here
		
		$this->please_log_in = __('Please log in to manage your subscription options.', 'subscribe2') . "<br /><a href='" . get_settings('home') . "/wp-login.php'>" . get_settings('home') . "/wp-login.php</a>";
		
		$this->use_profile =  __('You may manage your subscription options from your profile.', 'subscribe2') . "<br /><a href='" . get_settings('home') . "/wp-admin/profile.php?page=subscribe2/subscribe2.php'>" . get_settings('home') . "/wp-admin/profile.php?page=subscribe2/subscribe2.php</a>.";
		
		$this->confirmation_sent = __('A confirmation message is on its way!', 'subscribe2');
		
		$this->already_subscribed = __('That email address is already subscribed.', 'subscribe2');
		
		$this->not_subscribed = __('That email address is not subscribed.', 'subscrieb2');

		$this->not_an_email = __('Sorry, but that does not look like an email address to me.', 'subscribe2');
		
		$this->mail_sent = __('Message sent!', 'subscribe2');
		
		$this->form = "<form method='post' action=''><p>" . __('Your email:', 'subscribe2') . "&#160;<input type='text' name='email' value='' size='20' />&#160;<br /><input type='radio' name='s2_action' value='subscribe' checked='checked' /> " . __('subscribe', 'subscribe2') . " <input type='radio' name='s2_action' value='unsubscribe' /> " . __('unsubscribe', 'subscribe2') . " &#160;<input type='submit' value='" . __('Send', 'subscribe2') . "' /></p></form>\r\n";

		// confirmation messages
		$this->no_such_email = __('No such email address is registered.', 'subscribe2');
		
		$this->added = __('You have successfully subscribed!', 'subscribe2');
		
		$this->deleted = __('You have successfully unsubscribed.', 'subscribe2');

		$this->confirm_subject = '[' . get_settings('blogname') . '] ' . __('Please confirm your request', 'subscribe2');

		// menu strings
		$this->options_saved = __('Options saved!', 'subscribe2');
		$this->options_reset = __('Options reset!', 'subscribe2');
	} // end load_strings()

/* ===== WordPress menu registration ===== */
	/**
	Hook the menu
	*/
	function admin_menu() {
		add_management_page(__('Subscribers', 'subscribe2'), __('Subscribers', 'subscribe2'), "manage_options", basename(__FILE__), array(&$this, 'manage_menu'));
		add_options_page(__('Subscribe2 Options', 'subscribe2'), 'Subscribe2', "manage_options", basename(__FILE__), array(&$this, 'options_menu'));
		add_submenu_page('profile.php', __('Subscriptions', 'subscribe2'), __('Subscriptions', 'subscribe2'), "read", __FILE__, array(&$this, 'user_menu'));
		add_submenu_page('post.php', __('Mail subscribers', 'subscribe2'), __('Mail subscribers', 'subscribe2'), "manage_options", __FILE__, array(&$this, 'write_menu'));
	}

/* ===== ButtonSnap configuration ===== */
	/**
	Register our button in the QuickTags bar
	*/
	function s2_button_init() {
		$url = get_settings('siteurl') . '/wp-content/plugins/subscribe2/s2_button.png';
		buttonsnap_textbutton($url, 'Subscribe2', '<!--subscribe2-->');
		buttonsnap_register_marker('subscribe2', 's2_marker');
	}

	/**
	Style a marker in the Rich Text Editor for our tag

	By default, the RTE suppresses output of HTML comments, so this places a CSS style on our token in order to make it display
	*/
	function subscribe2_css() {
		$marker_url =  get_settings('siteurl') . '/wp-content/plugins/subscribe2/s2_marker.png';
		echo "
			.s2_marker {
				display: block;
				height: 45px;
				margin-top: 5px;
				background-image: url({$marker_url});
				background-repeat: no-repeat;
				background-position: center;
			}
		";
	}

/* ===== Install, upgrade, reset ===== */
	/**
	Install our table
	*/
	function install() {
		// include upgrade-functions for maybe_create_table;
		if (! function_exists('maybe_create_table')) {
			require_once(ABSPATH . '/wp-admin/upgrade-functions.php');
		}
		$date = date('Y-m-d');
		$sql = "CREATE TABLE $this->public (
			id int(11) NOT NULL auto_increment, 
			email varchar(64) NOT NULL default '', 
			active tinyint(1) default 0, 
			date DATE default '$date' NOT NULL,
			PRIMARY KEY (id) )";

		// create the table, as needed
		maybe_create_table($this->public, $sql);
		$this->reset();
	} // end install()

	/**
	Upgrade the database
	*/
	function upgrade() {
		global $wpdb;
		
		// include upgrade-functions for maybe_create_table;
		if (! function_exists('maybe_create_table')) {
			require_once(ABSPATH . '/wp-admin/upgrade-functions.php');
		}
		$date = date('Y-m-d');
		maybe_add_column($this->public, 'date', "ALTER TABLE `$this->public` ADD `date` DATE DEFAULT '$date' NOT NULL AFTER `active`;");
		update_option('s2_version', S2VERSION);
		
		// let's take this time to check whether any public subscribers
		// are also registered users of the blog, and convert them
		$check = $wpdb->get_results("SELECT $wpdb->users.ID FROM $wpdb->users INNER JOIN $this->public ON $wpdb->users.user_email = $this->public.email");
		if (! empty($check)) {
			foreach ($check as $user) {
				$this->register($user);
			}
		}
	} // end upgrade()
	
	/**
	Reset our options
	*/
	function reset() {
		update_option('s2_sender', 'author');
		update_option('s2_mailtext', "BLOGNAME has posted a new item, 'TITLE'\r\nPOST\r\nYou may view the latest post at\r\nPERMALINK\r\nYou received this e-mail because you asked to be notified when new updates are posted.\r\nBest regards,\r\nMYNAME\r\nEMAIL");
		update_option('s2_confirm_email', "In order to confirm your request for BLOGNAME, please click on the link below:\n\nLINK\n\nIf you did not request this, please feel free to disregard this notice!\n\nThank you,\nMYNAME.");
		update_option('s2_exclude', '');
		update_option('s2_reg_override', '1');
	 } // end reset()
	
/* ===== mail handling ===== */
	/**
	Performs string substitutions for subscribe2 mail texts
	*/
	function substitute($string = '') {
		if ('' == $string) {
			return;
		}
		$string = str_replace('BLOGNAME', get_settings('blogname'), $string);
		$string = str_replace('BLOGLINK', get_bloginfo('url'), $string);
		$string = str_replace('TITLE', stripslashes($this->post_title), $string);
		$string = str_replace('PERMALINK', $this->permalink, $string);
		$string = str_replace('MYNAME', stripslashes($this->myname), $string);
		$string = str_replace('EMAIL', $this->myemail, $string);
		return $string;
	} // end sustitute()

	/**
	Delivers email to recipients in HTML or plaintext
	*/
	function mail ($recipients = array(), $subject = '', $message = '', $type='text') {
		if (empty($recipients)) { return; }
		if ('' == $message) { return; }

		// Set sender details
		if ('' == $this->myname) {
			$admin = get_userdata('1');
			$this->myname = $admin->display_name;
			$this->myemail = $admin->user_email;
		}
		$headers = "From: $this->myname <$this->myemail>\n";

		if ('html' == $type) {
 		       // To send HTML mail, the Content-type header must be set
		        $headers .= "MIME-Version: 1.0\n";
		        $headers .= "Content-type: " . get_bloginfo('html_type') . "; charset=\"". get_bloginfo('charset') . "\"\n";
		        $mailtext = "<html><head><title>$subject</title></head><body>" . $message . "</body></html>";
		} else {
		         $headers .= "MIME-Version: 1.0\n";
		         $headers .= "Content-type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
		        $mailtext = wordwrap(strip_tags($message), 80, "\n");
		}

		// BCC all recipients
		if ( (defined(DREAMHOST) && TRUE === DREAMHOST) && 
			($count($recipients) > 30) ) {
			// we're on Dreamhost, and have more than 30 susbcribers
		        $count = 1;
		        $bcc = '';
		        $batch = array();
		        foreach ($recipients as $recipient) {
				// advance the array pointer by one, for use down below
				// the array pointer _is not_ advanced by the foreach() loop itself
				next($recipients);
		                $recipient = trim($recipient);
		                if (! empty($recipient)) {
		                        $bcc .= "Bcc: " . $recipient . "\n";
		                }
		                if (30 == $count) {
		                        $count = 1;
		                        $batch[] = $bcc;
		                        $bcc = '';
		                } else {
					if (false === current($recipients)) {
						// we've reached the end of the subscriber list
						// add what we have to the batch, and move on
						$batch[] = $bcc;
						break;
					} else {
			                        $count++;
					}
		                }
		        }
			// rewind the array, just to be safe
			reset($recipients);
		} else {
		        // we're not on dreamhost, or have less than 30
			// subscribers, so do it normal
		        foreach ($recipients as $recipient) {
		                $recipient = trim($recipient);
		                if (! empty($recipient)) {
		                        $headers .= "Bcc: $recipient\n";
		                }
		        }
		}
		// actually send mail
	        if ( (defined(DREAMHOST) && TRUE === DREAMHOST) && (isset($batch)) ) {
        	        foreach ($batch as $bcc) {
                	        $newheaders = $headers . $bcc;
                        	@wp_mail($this->myemail, $subject, $mailtext, $newheaders);                }
	        } else {
        	        @wp_mail($this->myemail, $subject, $mailtext, $headers);
	        }
	} // end mail()
	
	/**
	Sends an email notification of a new post
	*/
	function publish($id = 0) {
		if (! $id) { return $id; }
		// if this is an edit, we don't need to do anything
		if ('post' !== $_POST['action']) { return $id; }
		
		$post_cats = wp_get_post_cats('1', $id);
		$post_cats_string = implode(',', $post_cats);
		$check = false;
		// is the current post assigned to any categories
		// which should not generate a notification email?
		foreach (explode(',', $this->get_excluded_cats()) as $cat) {
			if (in_array($cat, $post_cats)) {
				$check = true;
			}
		}
		// if so, bail out
		if ($check) { 
			// hang on -- can registered users subscribe to 
			// excluded categories?
			if ('0' == get_option('s2_reg_override')) {
				// nope?  okay, let's leave
				return $id;
			}
		}
		
		global $wpdb;
		$post = & get_post($id);
		// is this post set in the future?
		if ($post->post_date > current_time('mysql')) {
			// if so, bail out
			// we really should make a cron action here
			return $id;
		}

		// lets collect our public subscribers
		// and all our registered subscribers for these categories
		$public = array();
		$registered = array();
		if (! $check) {
			// if this post is assigned to an excluded
			// category, then this test will prevent
			// the public from receiving notification
			$public = $this->get_public();
		}
		$registered = $this->get_registered("cats=$post_cats_string");
		
		// do we have subscribers?
		if ( empty($public) && empty($registered) ) {
			// if not, no sense doing anything else
			return $id;
		}
		// we set these class variables so that we can avoid 
		// passing them in function calls a little later
		$this->post_title = $post->post_title;
		$this->permalink = get_permalink($id);

		// do we send as admin, or post author?
		if ('author' == get_option('s2_sender')) {
		// get author details
			$user = get_userdata($post->post_author);
		} else {
			// get admin detailts
			$user = get_userdata(1);
		}
		$this->myemail = $user->user_email;
		$this->myname = $user->display_name;
		// Get email subject
		$subject = $this->substitute($this->s2_subject);
		// Get the message template
		$mailtext = $this->substitute(stripslashes(get_option('s2_mailtext')));
		
		$plaintext = $post->post_content;
		$content = apply_filters('the_content', $post->post_content);
		$content = str_replace(']]>', ']]&gt', $content);
		$excerpt = $post->post_excerpt;
		if ('' == $excerpt) {
			// no excerpt, is there a <!--more--> ?
			if (false !== strpos($content, '<!--more-->')) {
				list($excerpt, $more) = explode('<!--more-->', $plaintext, 2);
				// strip leading and trailing whitespace
				$excerpt = trim($excerpt);
			} else {
               			$excerpt = strip_tags($plaintext);
		                $excerpt_length = 55;
		                $words = explode(' ', $plaintext, $excerpt_length + 1);
		                if (count($words) > $excerpt_length) {
		                        array_pop($words);
		                        array_push($words, '[...]');
		                        $excerpt = implode(' ', $words);
		                }
			}

		}
		// first we send plaintext summary emails
		$body = str_replace('POST', $excerpt, $mailtext);
		$this->mail(array_merge($this->get_public(), $this->get_registered("cats=$post_cats&format=text&amount=excerpt")), $subject, $body);
		// next we send plaintext full content emails
		$body = str_replace('POST', $content, $mailtext);
		$this->mail($this->get_registered("cats=$post_cats_string&format=text&amount=post"), $subject, $body);
		// finally we send html full content emails
		$body = str_replace("\r\n", "<br />\r\n", $mailtext);
		$body = str_replace('POST', $content, $body);
		$this->mail($this->get_registered("cats=$post_cats_string&format=html"), $subject, $body, 'html');
	
		return $id;
	} // end publish()

	/**
	Send confirmation email to the user
	*/
	function send_confirm($what = '') {
		if ( (! $this->email) || (! $what) ) {
			return false;
		}
		$id = $this->get_id($this->email);
		if (! $id) {
			return false;
		}

		// generate the URL "?s2=ACTION+HASH+ID"
		//     ACTION = 1 to subscribe, 0 to unsubscribe
		//     HASH = md5 hash of email address
		//     ID = user's ID in the subscribe2 table
		$link = get_settings('siteurl') . "?s2=";
		if ('add' == $what) {
			$link .= '1';
		} elseif ('del' == $what) {
			$link .= '0';
		}
		$link .= md5($this->email);
		$link .= $id;
		
		$admin = get_userdata(1);
		$this->myname = $admin->display_name;

		$body = $this->substitute(get_option('s2_confirm_email'));
		$body = str_replace("LINK", $link, $body);

		$subject = $this->substitute($this->confirm_subject);
		$mailheaders .= "MIME-Version: 1.0\n";
		$mailheaders .= "Content-type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
		$mailheaders = "From: $admin->display_name <$admin->user_email>";

		@wp_mail ($this->email, $subject, $body, $mailheaders);
	} // end send_confirm()

/* ===== Category functions ===== */
	/**
	Returns a comma-separated list of category IDs which should not generate notifications
	*/
	function get_excluded_cats() {
		if ('' != $this->excluded_cats) {
			return $this->excluded_cats;
		} else {
			global $wpdb;
			$this->excluded_cats = get_option('s2_exclude');
			return $this->excluded_cats;
		}
	} // end get_excluded_cats()

	/**
	Return either a comma-separated list of all the category IDs in the blog or an array of cat_ID => cat_name 
	*/
	function get_all_categories($select = 'id') {
		global $wpdb;
		if ('id' == $select) {
			return implode(',', $wpdb->get_col("SELECT cat_ID FROM $wpdb->categories"));
		} else {
			$cats = array();
			$result = $wpdb->get_results("SELECT cat_ID, cat_name FROM $wpdb->categories", ARRAY_N);
			foreach ($result as $result) {
				$cats[$result[0]] = $result[1];
			}
			return $cats;
		}
	} // end get_all_categories()


/* ===== Subscriber functions ===== */
	/**
	Given a public subscriber ID, returns the email address
	*/
	function get_email ($id = 0) {
		global $wpdb;

		if (! $id) {
			return false;
		}
		return $wpdb->get_var("SELECT email FROM $this->public WHERE id=$id");
	} // end get_email
	
	/**
	Given a public subscriber email, returns the subscriber ID
	*/
	function get_id ($email = '') {
		global $wpdb;

		if (! $email) {
			return false;
		}
		return $wpdb->get_var("SELECT id FROM $this->public WHERE email='$email'");
	} // end get_id()
	
	/**
	Activate an email address

	If the address is not already present, it will be added
	*/
	function activate ($email = '') {
		global $wpdb;

		if ('' == $email) {
			if ('' != $this->email) {
				$email = $this->email;
			} else {
				return false;
			}
		}

		if (false !== $this->is_public($email)) {
			$check = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email='$this->email'");
			if ($check) { return; }
			$wpdb->get_results("UPDATE $this->public SET active='1' WHERE email='$email'");
		} else {
			$wpdb->get_results("INSERT INTO $this->public (email, active, date) VALUES ('$email', '1', NOW())");
		}
	} // end activate()

	/**
	Add an unconfirmed email address to the subscriber list
	*/
	function add ($email = '') {
		global $wpdb;

		if ('' == $email) {
			if ('' != $this->email) {
				$email = $this->email;
			} else {
				return false;
			}
		}

		if (! is_email($email)) { return false; }

		if (false !== $this->is_public($email)) {
			$wpdb->get_results("UPDATE $this->public SET date=NOW() WHERE email='$email'");
		} else {
			$wpdb->get_results("INSERT INTO $this->public (email, active, date) VALUES ('$email', '0', NOW())");
		}
	} // end add()
	
	/**
	Remove a user from the subscription table
	*/
	function delete($email = '') {
		global $wpdb;

		if ('' == $email) {
			if ('' != $this->email) {
				$email = $this->email;
			} else {
				return false;
			}
		}

		if (! is_email($email)) { return false; }
		$wpdb->get_results("DELETE FROM $this->public WHERE email='$email'");
	} // end delete()

	/**
	Toggle a public subscriber's status
	*/
	function toggle($email = '') {
		global $wpdb;
		
		if ( ('' == $email) || (! is_email($email)) ) { return false; }
		
		// let's see if this is a public user
		$status = $this->is_public($email);
		if (false === $status) { return false; }

		if ('0' == $status) {
			$wpdb->get_results("UPDATE $this->public SET active='1' WHERE email='$email'");
		} else {
			$wpdb->get_results("UPDATE $this->public SET active='0' WHERE email='$email'");
		}
	} // end toggle()
	
	/**
	Confirm request from the link emailed to the user and email the admin
	*/
	function confirm($content = '') {
		global $wpdb;

		$code = $_GET['s2'];
		$action = intval(substr($code, 0, 1));
		$hash = substr($code, 1, 32);
		$code = str_replace($hash, '', $code);
		$id = intval(substr($code, 1));
		if ($id) {
			$this->email = $this->get_email($id);
			if (! $this->email) {
				return $this->no_such_email;
			}
		} else {
			return $this->no_such_email;
		}

		if ('1' == $action) {
			// make this subscription active
			$this->activate();
			$this->message = $this->added;
			$subject = '[' . get_settings('blogname') . '] ' . __('New subscriber', 'subscribe2');
			$message = "$this->email " . __('subscribed to email notifications!', 'subscribe2');
			$recipients = $wpdb->get_col("SELECT DISTINCT(user_email) FROM $wpdb->users INNER JOIN $wpdb->usermeta ON $wpdb->users.ID = $wpdb->usermeta.user_id WHERE $wpdb->usermeta.meta_key='wp_user_level' AND $wpdb->usermeta.meta_value='10'");
			$this->mail($recipients, $subject, $message);
		} elseif ('0' == $action) {
			// remove this subscriber
			$this->delete();
			$this->message = $this->deleted;
		}

		if ('' != $this->message) {
			return $this->message;
		}
	} // end confirm
			
	/**
	Is the supplied email address a public subscriber?
	*/
	function is_public($email = '') {
		global $wpdb;

		if ('' == $email) { return false; }
		
		$check = $wpdb->get_var("SELECT active FROM $this->public WHERE email='$email'");
		if ( ('0' === $check) || ('1' == $check) ) {
			return $check;
		} else {
			return false;
		}
	} // end is_public

	/**
	Is the supplied email address a registered user of the blog?
	*/
	function is_registered($email = '') {
		global $wpdb;

		if ('' == $email) { return false; }
		
		$check = $wpdb->get_var("SELECT email FROM $wpdb->users WHERE user_email='$email'");
		if ($check) {
			return true;
		} else {
			return false;
		}
	}

	/**
	Return an array of all the public subscribers
	*/
	function get_public ($confirmed = 1) {
		global $wpdb;
		if (1 == $confirmed) {
			if ('' == $this->all_public) {
				$this->all_public = $wpdb->get_col("SELECT email FROM $this->public WHERE active='1'");
			}
			return $this->all_public;
		} else {
			if ('' == $this->all_unconfirmed) {
				$this->all_unconfirmed = $wpdb->get_col("SELECT email FROM $this->public WHERE active='0'");
			}
			return $this->all_unconfirmed;
		}
	} // end get_public()

	/**
	Return an array of registered subscribers

	Collect all the registered users of the blog who are subscribed to the specified categories
	*/
	function get_registered ($args = '') {
		global $wpdb;

		$format = '';
		$amount = '';
		$cats = '';
		$subscribers = array();

		parse_str($args, $r);
		if (! isset($r['cats']))
			$r['cats'] = '';
		if (! isset($r['format']))
			$r['format'] = 'all';
		if (! isset($r['amount']))
			$r['amount'] = 'all';

		$JOIN = ''; $AND = '';
		// text or HTML subscribers
		if ('all' != $r['format']) {
			$JOIN .= "INNER JOIN $wpdb->usermeta AS b ON a.user_id = b.user_id ";
			$AND .= " AND b.meta_key='s2_format' AND b.meta_value=";
			if ('html' == $r['format']) {
				$AND .= "'html'";
			} elseif ('text' == $r['format']) {
				$AND .= "'text'";
			}
		}

		// full post or excerpt subscribers
		if ('all' != $r['amount']) {
			$JOIN .= "INNER JOIN $wpdb->usermeta AS c ON a.user_id = c.user_id ";
			$AND .= " AND c.meta_key='s2_excerpt' AND c.meta_value=";
			if ('excerpt' == $r['amount']) {
				$AND .= "'excerpt'";
			} elseif ('post' == $r['amount']) {
				$AND.= "'post'";
			}
		}
		
		// specific category subscribers
		if ('' != $r['cats']) {
			$JOIN .= "INNER JOIN $wpdb->usermeta AS d ON a.user_id = d.user_id ";
			foreach (explode(',', $r['cats']) as $cat) {
				('' == $and) ? $and = "d.meta_key='s2_cat$cat'" : $and .= " OR d.meta_key='s2_cat$cat'";
			}
			$AND .= "AND ($and)";
		}
		
		$sql = "SELECT a.user_id FROM $wpdb->usermeta AS a " . $JOIN . " WHERE a.meta_key='s2_subscribed'" . $AND;
		$result = $wpdb->get_col($sql);
		if ($result) {
			$ids = implode(',', $result);
			return $wpdb->get_col("SELECT user_email FROM $wpdb->users WHERE ID IN ($ids)");
		}
	} // end get_registered()

	/**
	Collects the signup date for all public subscribers
	*/
	function signup_date($email = '') {
		if ('' == $email) { return false; }

		global $wpdb;
		if (! empty($this->signup_dates)) {
			return $this->signup_dates[$email];
		} else {
			$results = $wpdb->get_results("SELECT email, date FROM $this->public", ARRAY_N);
			foreach ($results as $result) {
				$this->signup_dates[$result[0]] = $result[1];
			}
			return $this->signup_dates[$email];
		}
	} // end signup_date()

	/**
	Create the appropriate usermeta values when a user registers

	If the registering user had previously subscribed to notifications, this function will delete them from the public subscriber list first
	*/
	function register ($user_id = 0) {
		global $wpdb;

		if (0 == $user_id) { return $user_id; }
		$user = get_userdata($user_id);

		// has this user previously signed up for email notification?
		if (false !== $this->is_public($user->user_email)) {
			// delete this user from the public table, and subscribe them to all the categories
			$this->delete($user->user_email);
			update_usermeta($user_id, 'subscribe2', $this->get_all_categories());
		} else {
			// add the usermeta, but don't subscribe them
			update_usermeta($user_id, 'subscribe2', 'none');
		}
		update_usermeta($user_id, 's2_format', 'text');
		update_usermeta($user_id, 's2_excerpt', 'full');
		return $user_id;
} // end register()

/* ===== Menus ===== */
	/**
	Our management page
	*/
	function manage_menu() {
		global $wpdb;

		$what = '';
		
		// was anything POSTed ?
		if (isset($_POST['s2_admin'])) {
			if ('subscribe' == $_POST['s2_admin']) {
				foreach (preg_split ("/[\s,]+/", $_POST['addresses']) as $email) {
				        if (is_email($email)) {
						$this->activate($email);
					}
				}
				$_POST['what'] = 'confirmed';
				echo "<div class='updated'>" . __('Address(es) subscribed!', 'subscribe2') . '</div>';
			} elseif ('delete' == $_POST['s2_admin']) {
				$this->delete($_POST['email']);
				echo "<div class='updated'>" . $_POST['email'] . ' ' . __('deleted!', 'subscribe2') . '</div>';
			} elseif ('toggle' == $_POST['s2_admin']) {
				$this->toggle($_POST['email']);
				echo "<div class='updated'>" . $_POST['email'] . ' ' . __('status changed!', 'subscribe2') . '</div>';
			}
		}
		
		if (isset($_POST['what'])) {
			if ('all' == $_POST['what']) { 
				$what = 'all'; 
				$confirmed = $this->get_public();
				$unconfirmed = $this->get_public(0);
				$registered = $this->get_registered();
				$subscribers = array_merge($confirmed, $unconfirmed, $registered);
			} elseif ('public' == $_POST['what']) {
				$what = 'public';
				$confirmed = $this->get_public();
				$unconfirmed = $this->get_public(0);
				$subscribers = array_merge($confirmed, $unconfirmed);
			} elseif ('confirmed' == $_POST['what']) { 
				$what = 'confirmed'; 
				$confirmed = $this->get_public();
				$subscribers = $confirmed;
			} elseif ('unconfirmed' == $_POST['what']) {
				$what = 'unconfirmed';
				$unconfirmed = $this->get_public('0');
				$subscribers = $unconfirmed;
			} elseif (is_numeric($_POST['what'])) { 
				$what = intval($_POST['what']);
				$subscribers = $this->get_registered("cats=$what");
			} elseif ('registered' == $_POST['what']) {
				$what = 'registered';
				$subscribers = $this->get_registered();
			}
		} elseif ('' == $what) {
			$subscribers = $this->get_registered();
			$what = 'registered';
			if (empty($subscribers)) {
				$confirmed = $this->get_public();
				$subscribers = $confirmed;
				$what = 'confirmed';
				if (empty ($subscribers)) {
					$unconfirmed = $this->get_public(0);
					$subscribers = $unconfirmed;
					$what = 'unconfirmed';
					if (empty($subscribers)) {
						$what = 'all';
					}
				}
			}
		}
		if (! empty($subscribers)) {
			sort($subscribers);
		}
		// safety check for our arrays
		if ('' == $confirmed) { $confirmed = array(); }
		if ('' == $unconfirmed) { $unconfirmed = array(); }
		if ('' == $registered) { $registered = array(); }

		// show our form
		echo '<div class="wrap">';
		echo '<h2>' . __('Subscribe Addresses', 'subscribe2') . "</h2>\r\n";
		echo "<form method='POST'>\r\n";
		echo "<span style='align:left'>" . __('Enter addresses, one per line or comma-seperated', 'subscribe2') . "</span><br />\r\n";
		echo '<textarea rows="2" cols="80" name="addresses"></textarea>';
		echo ' <span class="submit"><input type="submit" name="submit" value="' . __('Subscribe', 'subscribe2') . '" />';
		echo '<input type="hidden" name="s2_admin" value="subscribe" /></span>';
		echo '</form></div>';
		
		// subscriber lists
		echo "<div class='wrap'><h2>Subscribers</h2>\r\n";
		 
		$this->display_subscriber_dropdown($what, __('Filter', 'subscribe2'));
		// show the selected subscribers
		$alternate = 'alternate';
		if (! empty($subscribers)) {
			echo '<p align="center"><b>' . __('Registered on the left, confirmed in the middle, unconfirmed on the right', 'subscribe2') . '</b></p>';
		}
		echo "<table cellpadding='2' cellspacing'2'>";
		if (! empty($subscribers)) {
			foreach ($subscribers as $subscriber) {
				echo "<tr class='$alternate'>";
				echo "<td width='75%'";
				if (in_array($subscriber, $unconfirmed)) {
					echo " align='right'>";
				} elseif (in_array($subscriber, $confirmed)) {
					echo "align='center'>";
				} else {
					echo "align='left' colspan='3'>";
				}
				echo "<a href='mailto:$subscriber'>$subscriber</a>\r\n";
				if ( in_array($subscriber, $unconfirmed) || in_array($subscriber, $confirmed) ) {
					echo '(' . $this->signup_date($subscriber) . ')</td>';
					echo "<td width='5%' align='center'><form method='POST'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='toggle' /><input type='hidden' name='what' value='$what' /><input type='submit' name='submit' value='";
					(in_array($subscriber, $unconfirmed)) ? $foo = '&lt;-' : $foo= '-&gt;';
					echo "$foo' /></form></td>";
					echo "<td width='2%' align='center'><form method='POST'><span class='delete'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='delete' /><input type='hidden' name='what' value='$what' /><input type='submit' name='submit' value='X' /></span></form>";
				}
				echo "</td></tr>\r\n";
				('alternate' == $alternate) ? $alternate = '' : $alternate = 'alternate';
			}
		} else {
			echo '<tr><td align="center"><b>' . __('NONE', 'subscribe2') . "</b></td></tr>\r\n";
		}
		echo "</table></div>\r\n";

		echo '<div style="clear: both;"><p>&nbsp;</p></div>';

		include(ABSPATH . '/wp-admin/admin-footer.php');
		// just to be sure
		die;
	} // end manage_menu()

	/**
	Our options page
	*/
	function options_menu() {
		// was anything POSTed?
		if (isset($_POST['s2_admin'])) {
			if ('RESET' == $_POST['s2_admin']) {
				$this->reset();
				echo "<div class='updated'>$this->options_reset</div>";
			} elseif ('options' == $_POST['s2_admin']) {
				// excluded categories
				if (! empty($_POST['category'])) {
					$exclude_cats = implode(',', $_POST['category']);
				} else {
					$exclude_cats = '';
				}
				update_option('s2_exclude', $exclude_cats);
				// allow override?
				(isset($_POST['override'])) ? $override = '1' : $override = '0';
				update_option('s2_reg_override', $override);
				
				// send as author or admin?
				$sender = 'author';
				if ('admin' == $_POST['s2_sender']) {
					$sender = 'admin';
				}
				update_option('s2_sender', $sender);

				// email templates
				$mailtext = $_POST['s2_mailtext'];
				update_option('s2_mailtext', $mailtext);
				$confirm_email = $_POST['s2_confirm_email'];
				update_option('s2_confirm_email', $confirm_email);
				echo "<div class='updated'>$this->options_saved</div>";
			}
		}
		// show our form
		$this->sender = get_option('s2_sender');
		$this->mailtext = get_option('s2_mailtext');
		$this->confirm_email = get_option('s2_confirm_email');
		$this->override = get_option('s2_reg_override');
		
		echo '<div class="wrap">';
		echo '<form method="POST">';
		echo '<input type="hidden" name="s2_admin" value="options" />';
		echo '<h2>' . __('Delivery Options', 'subscribe2') . ':</h2>';
		echo __('Send Email From', 'subscribe2') . ': ';
		echo '<input type="radio" name="s2_sender" value="author" ';
		if ('author' == $this->sender) {
			echo 'checked="checked" ';
		}
		echo ' /> ' . __('Author of the post', 'subscribe2') . ' &nbsp;&nbsp;';
		echo '<input type="radio" name="s2_sender" value="admin" ';
		if ('admin' == $this->sender) {
		        echo 'checked="checked" ';
		}
		echo ' /> ' . __('Blog Admin', 'subscribe2') . "<br />\r\n";
		echo '<h2>' . __('Email Templates', 'subscribe2') . "</h2>\r\n";
		echo '<table width="100%" cellspacing="2" cellpadding="1" class="editform">';
		echo '<tr><td>';
		echo __('New Post email (must not be empty)', 'subscribe2') . ':';
		echo "<br />\r\n";
		echo '<textarea rows="9" cols="60" name="s2_mailtext">' . stripslashes($this->mailtext) . "</textarea>\r\n";
		echo '</td><td rowspan="2">';
		echo '<h3>' . __('Message substitions', 'subscribe2') . "</h3>\r\n";
		echo '<dl>';
		echo '<dt><b>BLOGNAME</b></dt><dd>' . get_bloginfo('name') . "</dd>\r\n";
		echo '<dt><b>BLOGLINK</b></dt><dd>' . get_bloginfo('url') . "</dd>\r\n";
		echo '<dt><b>TITLE</b></dt><dd>' . __("the post's title", 'subscribe2') . "</d>\r\n";
		echo '<dt><b>POST</b></dt><dd>' . __("the excerpt or the entire post<br />(<i>based on the subscriber's preferences</i>)", 'subscribe2') . "</dd>\r\n";
		echo '<dt><b>PERMALINK</b></dt><dd>' . __("the post's permalink", 'subscribe2') . "</dd>\r\n";
		echo '<dt><b>MYNAME</b></dt><dd>' . __("the admin or post author's name", 'subscribe2') . "</dd>\r\n";
		echo '<dt><b>EMAIL</b></dt><dd>' . __("the admin or post author's email", 'subscribe2') . "</dd>\r\n";
		echo '<dt><b>LINK</b></dt><dd>' . __('the generated link to confirm a request<br />(<i>only used in the confirmation email template</i>)', 'subscribe2') . "</dd>\r\n";
		echo '</dl></td></tr><tr><td>';
		echo __('Subscribe / Unsubscribe confirmation email', 'subscribe2') . ":<br />\r\n";
		echo '<textarea rows="9" cols="60" name="s2_confirm_email">' . stripslashes($this->confirm_email) . '</textarea>';
		echo "</td></tr></table>\r\n";

		// excluded categories
		echo '<h2>' . __('Excluded Categories', 'subscribe2') . "</h2>\r\n";
		$this->display_category_form(explode(',', $this->get_excluded_cats()));

		echo "<p align='center'><input type='checkbox' name='override' ";
		if ('1' == $this->override) {
			echo "checked='checked'";
		}
		echo "/> " . __('Allow registered users to subscribe to excluded categories?', 'subscribe2') . '</p>';
		// submit
		echo '<p align="center"><span class="submit"><input type="submit" id="save" name="submit" value=' . __('Submit', 'subscribe2') . ' /></span></p>';
		echo "</form>\r\n";

		echo '</div><div class="wrap">';
		// reset
		echo '<h2>' . __('Reset Default', 'subscribe2') . "</h2>\r\n";
		echo '<p>' . __('Use this to reset all options to their defaults. This <strong><em>will not</em></strong> modify your list of subscribers.', 'subscribe2') . "</p>\r\n";
		echo '<form method="POST">';
		echo '<p align="center"><span class="submit">';
		echo '<input type="hidden" name="s2_admin" value="RESET" />';
		echo '<input type="submit" id="deletepost" name="submit" value="' . __('RESET', 'subscribe2') .
		'" />';
		echo "</span></p></form></div>\r\n";

		include(ABSPATH . '/wp-admin/admin-footer.php');
		// just to be sure
		die;
	} // end options_menu()

	/**
	Our profile menu
	*/
	function user_menu() {
		global $user_ID;

		get_currentuserinfo();

		// was anything POSTed?
		if ( (isset($_POST['s2_admin'])) && ('user' == $_POST['s2_admin']) ) {
			$format = 'text';
			$post = 'post';
			if ('html' == $_POST['s2_format']) {
				$format = 'html';
			}
			if ('excerpt' == $_POST['s2_excerpt']) {
				$post = 'excerpt';
			}
			update_usermeta($user_ID, 's2_excerpt', $post);
			update_usermeta($user_ID, 's2_format', $format);

			$cats = $_POST['category'];
			if (empty($cats)) {
				delete_usermeta($user_ID, 's2_subscribed');
			} else {
				$old_cats = explode(',', get_usermeta($user_ID, 's2_subscribed'));
				$remove = array_diff($old_cats, $cats);
				$new = array_diff($cats, $old_cats);
				if (! empty($remove)) {
					// remove subscription to these cat IDs
					foreach ($remove as $id) {
						delete_usermeta($user_ID, "s2_cat$id");
					}
				}
				if (! empty($new)) {
					// add subscription to these cat IDs
					foreach ($new as $id) {
						update_usermeta($user_ID, 's2_cat' . $id, "$id");
					}
				}
				update_usermeta($user_ID, 's2_subscribed', implode(',', $cats));
			}
		}

		// show our form
		echo '<div class="wrap">';
		echo '<h2>' . __('Notification Settings', 'subscribe2') . "</h2>\r\n";
		echo '<form method="POST">';
		echo '<input type="hidden" name="s2_admin" value="user" />';
		echo '<fieldset class="options"><legend>' . __('Delivery Options', 'subscribe2') . ':</legend>';
		echo __('Receive email as', 'subscribe2') . ': &nbsp;&nbsp;';
		echo '<input type="radio" name="s2_format" value="html"';
		if ('html' == get_usermeta($user_ID, 's2_format')) {
			echo 'checked="checked" ';
		}
		echo '/> ' . __('HTML', 'subscribe2') .' &nbsp;&nbsp;';
		echo '<input type="radio" name="s2_format" value="text" ';
		if ('text' == get_usermeta($user_ID, 's2_format')) {
			echo 'checked="checked" ';
		}
		echo '/> ' . __('Plain Text', 'subscribe2') . "<br /><br />\r\n";

		echo __('Email contains', 'subscribe2') . ': &nbsp;&nbsp;';
		$amount = array ('excerpt' => __('Excerpt Only', 'subscribe2'), 'post' => __('Full Post', 'subscribe2'));
		foreach ($amount as $key => $value) {
			echo '<input type="radio" name="s2_excerpt" value="' . $key . '"';
			if ($key == get_usermeta($user_ID, 's2_excerpt')) {
				echo ' checked="checked"';
			}
			echo " /> $value ";
		}
		_e('<p>Note: HTML format will always deliver the full post.</p>', 'subscribe2');
		echo '</fieldset>';

		// subscribed categories
		echo '<h2>' . __('Subscribed Categories', 'subscribe2') . "</h2>\r\n";
		$this->display_category_form(explode(',', get_usermeta($user_ID, 's2_subscribed')), get_option('s2_reg_override'));
		
		// submit
		echo '<p align="center"><input type="submit" name="submit" value="' . __("Update preferences", 'subscribe2') . '" /></p>';
		echo "</form>\r\n";
		
		include(ABSPATH . '/wp-admin/admin-footer.php');
		// just to be sure
		die;
	} // end user_menu()

	/**
	Display the Write sub-menu
	*/
	function write_menu() {
		// was anything POSTed?
		if (isset($_POST['s2_admin']) && ('mail' == $_POST['s2_admin']) ) {
			if ('confirmed' == $_POST['what']) {
				$recipients = $this->get_public();
			} elseif (is_numeric($_POST['what'])) {
				$cat = intval($_POST['what']);
				$recipients = $this->get_registered("cats=$cat");
			} else {
				$recipients = $this->get_registered();
			}
			global $user_identity, $user_email;
			get_currentuserinfo();
			$this->myname = $user_identity;
			$this->myemail = $user_email;
			$subject = strip_tags($_POST['subject']);
			$message = $_POST['message'];
			$this->mail($recipients, $subject, $message, 'text');
			$message = $this->mail_sent;	
		}
		
		if ('' != $message) {
			echo '<div class="updated">' . $message . $user_identity . "</div>\r\n";
		}
		// show our form
		echo '<div class="wrap"><h2>' . __('Send email to all subscribers', 'subscribe2') . "</h2>\r\n";
		echo "<form method='POST'>\r\n";
		echo __('Subject', 'subscribe2') . ': <input type="text" size="69" name="subject" value="' . __('A message from ', 'subscribe2') . get_settings('blogname') . '" /> <br />';
		echo '<textarea rows="12" cols="75" name="message"></textarea>';
		echo "<br />\r\n";
		echo "Recipients: ";
		$this->display_subscriber_dropdown('registered', false, array('all', 'public', 'unconfirmed'));
		echo '<input type="hidden" name="s2_admin" value="mail" />';
		echo '&nbsp;&nbsp;<span class="submit"<input type="submit" name="submit" value="' . __('Send', 'subscribe2') . '" /></span>&nbsp;';
		echo "</form></div>\r\n";
		echo '<div style="clear: both;"><p>&nbsp;</p></div>';


		include(ABSPATH . '/wp-admin/admin-footer.php');
		// just to be sure
		die;
	} // end write_menu()

/* ===== helper functions: forms and stuff ===== */
	/**
	Display a table of categories with checkboxes

	Optionally pre-select those categories specified
	*/
	function display_category_form($selected = array(), $override = 1) {
		global $wpdb;

		$all_cats = $this->get_all_categories('array');
		if (0 == $override) {
			// registered users are not allowed to subscribe to
			// excluded categories
			foreach (explode(',', $this->get_excluded_cats()) as $cat) {
				$category = get_category($cat);
				$excluded[$cat] = $category->cat_name;
			}
			$all_cats = array_diff($all_cats, $excluded);
		}

		$half = (count($all_cats) / 2);
		$i = 0;
		$j = 0;
		echo '<table width="100% cellspacing="2" cellpadding="5" class="editform">';
		echo '<tr valign="top"><td width="50%" align="right">';
		foreach ($all_cats as $cat_ID => $cat_name) {
			 if ( ($i >= $half) && (0 == $j) ){
                		echo '</td><td width="50%" align="left">';
                		$j++;
        		}
		        if (0 == $j) {
		                echo $cat_name . ' <input type="checkbox" name="category[]" value="' . $cat_ID . '"';
		                if (in_array($cat_ID, $selected)) {
		                        echo ' checked="checked" ';
		                }
		                echo " /><br />\r\n";
		        } else {
		                echo '<input type="checkbox" name="category[]"  value="' . $cat_ID . '"';
		                if (in_array($cat_ID, $selected)) {
		                        echo ' checked="checked" ';
		                }
		                echo " /> $cat_name<br />\r\n";
		        }
		        $i++;
		}
		echo "</td></tr></table>\r\n";
	} // end display_category_form()

	/**
	Display a drop-down form to select subscribers

	$selected is the option to select
	$submit is the text to use on the Submit button
	*/
	function display_subscriber_dropdown ($selected = 'registered', $submit = '', $exclude = array()) {
		global $wpdb;

		$who = array('all' => 'All Subscribers', 
			'public' => 'Public Subscribers', 
			'confirmed' => ' &nbsp;&nbsp;Confirmed', 
			'unconfirmed' => ' &nbsp;&nbsp;Unconfirmed', 
			'registered' => 'Registered Subscribers');

		// count the number of subscribers
		$count['confirmed'] = $wpdb->get_var("SELECT COUNT(id) FROM $this->public WHERE active='1'");
		$count['unconfirmed'] = $wpdb->get_var("SELECT COUNT(id) FROM $this->public WHERE active='0'");
		if (in_array('unconfirmed', $exclude)) {
			$count['public'] = $count['confirmed'];
		} elseif (in_array('confirmed', $exclude)) {
			$count['public'] = $count['unconfirmed'];
		} else {
			$count['public'] = ($count['confirmed'] + $count['unconfirmed']);
		}
		$count['registered'] = $wpdb->get_var("SELECT COUNT(meta_key) FROM $wpdb->usermeta WHERE meta_key='s2_subscribed'");
		$count['all'] = ($count['confirmed'] + $count['unconfirmed'] + $count['registered']);
		foreach ($this->get_all_categories('array') as $cat_ID => $cat_name) {
			$count[$cat_name] = $wpdb->get_var("SELECT COUNT(meta_value) FROM $wpdb->usermeta WHERE meta_key='s2_cat$cat_ID'");
		}
		
		// do have actually have some subscribers?
		if ( (0 == $count['confirmed']) && (0 == $count['unconfirmed']) && (0 == $count['registered']) ) {
			// no?  bail out
			return;
		}
		
		if (false !== $submit) {
			echo "<form method='POST'>";
		}
		echo "<select name='what'>\r\n";
		foreach ($who as $whom => $display) {
			if (in_array($whom, $exclude)) { continue; }
			if (0 == $count[$whom]) { continue; }

			echo "<option value='$whom'";
			if ($whom == $selected) { echo " selected "; }
			echo ">$display (" . ($count[$whom]) . ")</option>\r\n";
		}
		
		if ($count['registered'] > 0) {
			foreach ($this->get_all_categories('array') as $cat_ID => $cat_name) {
				if (in_array($cat_ID, $exclude)) { continue; }
				if (0 == $count[$cat_name]) { continue; }
				echo "<option value='$cat_ID'";
				if ($cat_ID == $selected) { echo " selected "; }
				echo "> &nbsp;&nbsp;$cat_name (" . $count[$cat_name] . ") </option>\r\n";
			}
		}
		echo '</select>';
		if (false !== $submit) {
			echo "<span class='submit'><input type='submit' value='$submit' /></span></form>\r\n";
		}
	} // end display_subscriber_dropdown()

/* ===== template and filter functions ===== */
	/**
	Display our form; also handles (un)subscribe requests
	*/
	function filter($content = '') {
		if ('' == $content) { return $content; }

		global $user_ID;
		get_currentuserinfo();
		if ($user_ID) {
			$this->form = '<p>' . $this->use_profile . '</p>';
		}
		if (isset($_POST['s2_action'])) {
			global $wpdb, $user_email;
			if (! is_email($_POST['email'])) {
				$this->form .= '<p>' . $this->not_an_email . '</p>';
			} else {
				$this->email = $_POST['email'];
				// does the supplied email belong to a registered user?
				$check = $wpdb->get_var("SELECT user_email FROM $wpdb->users WHERE user_email = '$this->email'");
				if ('' != $check) {
					// this is a registered email
					$this->form = '<p>' . $this->please_log_in . '</p>';
				} else {
					// this is not a registered email
					// what should we do?
					if ('subscribe' == $_POST['s2_action']) {
						// someone is trying to subscribe
						// lets see if they've tried to subscribe previously
						if (! $this->is_public($this->email)) {
							// the user is unknown or inactive
							$this->add();
							$this->send_confirm('add');
							$this->form = '<p>' . $this->confirmation_sent . '</p>';
						} else {
							// they're already subscribed
							$this->form = '<p>' . $this->already_subscribed . '</p>';
						}
						$this->action = 'subscribe';
					} elseif ('unsubscribe' == $_POST['s2_action']) {
						// is this email a subscriber?
						if (false === $this->is_public($this->email)) {
							$this->form .= '<p>' . $this->not_subscribed . '</p>';
						} else {
							$this->send_confirm('del');
							$this->form = '<p>' . $this->confirmation_sent . '</p>';
						}
						$this->action='unsubscribe';
					}
				}
			}
		}
		return preg_replace('|<!--subscribe2-->|', $this->form, $content);
	} // end filter()

	/**
	Overrides the default query when handling a (un)subscription confirmation

	this is basically a trick: if the s2 variable is in the query string, just grab the first static page
	and override it's contents later with title_filter() and template_filter()
	*/
	function query_filter() {
		global $wpdb;
		
		$id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_status='static' LIMIT 1");
		return "page_id=$id";
	} // end query_filter()

	/**
	Overrides the page title
	*/
	function title_filter() {
		return "Subscription Confirmation";
	} // end title_filter()

	/**
	Override the template filter to make sure a special template is not used
	*/
	function template_filter() {
		return;
	} // end template_filter()

/* ===== Our constructor ===== */
	/**
	Subscribe2 constructor
	*/
	function subscribe2() {
		global $table_prefix;

		load_plugin_textdomain('subscribe2');
		
		$this->public = $table_prefix . "subscribe2";
		// do we need to install anything?
		$this->version = get_option('s2_version');
		if (! $this->version) { $this->install(); }
		if ($this->version !== S2VERSION) { $this->upgrade(); }

		if (isset($_GET['s2'])) {
			// someone is confirming a request
			add_filter('query_string', array(&$this, 'query_filter'));
			add_filter('single_post_title', array(&$this, 'title_filter'));
			add_filter('the_title', array(&$this, 'title_filter'));
			add_filter('the_content', array(&$this, 'confirm'));
		}

		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('publish_post', array(&$this, 'publish'));
		add_action('user_register', array(&$this, 'register'));
		add_filter('the_content', array(&$this, 'filter'));
		// add our button
		add_action('init', array(&$this, 's2_button_init'));
		add_action('marker_css', array(&$this, 'subscribe2_css'));
		// load our strings
		$this->load_strings();
	} // end subscribe2()

/* ===== our variables ===== */
	// cache variables
	var $version = '';
	var $all_public = '';
	var $all_unconfirmed = '';
	var $excluded_cats = '';
	var $post_title = '';
	var $permalink = '';
	var $mayname = '';
	var $myemail = '';
	var $s2_subject = '[BLOGNAME] TITLE';
	var $signup_dates = array();

	// state variables used to affect processing
	var $action = '';
	var $email = '';
	var $message = '';
	var $error = '';
	
	// some messages
	var $use_profile = '';
	var $please_log_in = '';
	var $already_subscribed = '';
	var $not_an_email = '';
	var $mail_sent = '';
	var $form = '';
	var $no_such_email = '';
	var $added = '';
	var $deleted = '';
	var $confirm_subject = '';
	var $options_saved = '';
	var $options_reset = '';

} // end class subscribe2
$mysubscribe2 = new subscribe2();
?>

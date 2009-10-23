<?php
// ** MySQL settings ** //
define('WP_CACHE', true); //Added by WP-Cache Manager
define('DB_NAME', 'blog');     // The name of the database
define('DB_USER', 'yscr_bb2kA7');     // Your MySQL username
define('DB_PASSWORD', '_LSu9ryQdLp1'); // ...and password
define('DB_HOST', 'mysql');     // 99% chance you won't need to change this value

// Change the prefix if you want to have multiple blogs in a single database.
$table_prefix  = 'wp_';   // example: 'wp_' or 'b2' or 'mylogin_'

// Change this to localize WordPress.  A corresponding MO file for the
// chosen language must be installed to wp-includes/languages.
// For example, install de.mo to wp-includes/languages and set WPLANG to 'de'
// to enable German language support.
define ('WPLANG', '');

/* Stop editing */

define('ABSPATH', dirname(__FILE__).'/');
require_once(ABSPATH.'wp-settings.php');
?>

<?php
/**
 * The following snippets uses `PLUGIN` to prefix
 * the constants and class names. You should replace
 * it with something that matches your plugin name.
 */
$plugin_main_dir = dirname(__DIR__, 2);
$plugin_rel_dir = 'wp-content/plugins/PostMapTableView';

// define test environment
define( 'PLUGIN_PHPUNIT', true );
define( 'WEEK_IN_SECONDS', 1 );

if (!defined('SETTINGS_FILE')) {
    define('SETTINGS_FILE', 'category_mapping.json');
}

// define fake ABSPATH
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() );
}
// define fake PLUGIN_ABSPATH
if ( ! defined( 'PLUGIN_ABSPATH' ) ) {
	define( 'PLUGIN_ABSPATH', $plugin_main_dir );
}

// define fake WP_PLUGIN_DIR
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', dirname(__DIR__, 3));
}

if ( ! defined( 'WP_SITEURL' ) ) {
	define( 'WP_SITEURL', 'http://localhost/wordpress');
}

if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_SITEURL . '/' . $plugin_rel_dir);
}

//$comp_path = "C:/Users/Martin von Berg/AppData/Roaming/Composer"; // TODO: get the global path

require_once 'C:\Bitnami\wordpress-6.0.1-0\apps\wordpress\htdocs\wp-content\plugins\PostMapTableView\vendor\autoload.php';

// Mocks for global functions
function maybe_serialize($data) {
    return serialize($data);
}

function maybe_unserialize($data) {
    return unserialize($data);
}

function strip_shortcodes($content) {
	// Entferne alle Shortcodes im Format [shortcode] oder [shortcode attr="value"]...[/shortcode]
	return preg_replace('/\[(\w+)(?:[^\]]*)?\](?:.*?\[\/\1\])?/s', '', $content);
}
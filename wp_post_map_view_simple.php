<?php

/**
 *
 * @link              https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * @since             5.3.0
 * @package           PostMapTableView
 *
 * @wordpress-plugin
 * Plugin Name:       Post Map Table View
 * Plugin URI:        https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * Description:       Shows a map with posts that have geotags. Or shows a tour map with posts that have geotags and a tour date.
 * Version:           1.3.3
 * Author:            Martin von Berg
 * Author URI:        https://www.berg-reise-foto.de/info/ueber-mich/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postmapviewsimple
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Tested up to:      6.9
 */

namespace mvbplugins\postmapviewsimple;

defined('ABSPATH') or die('Are you ok?');

//include_once __DIR__ . '/inc/post_map_table.php';
include_once __DIR__ . '/inc/PostMapViewSimpleClass.php';

add_shortcode('mapview', 'mvbplugins\postmapviewsimple\register_mapview_shortcode');
function register_mapview_shortcode($atts) {
    // Die Klasse nur instanziieren, wenn der Shortcode genutzt wird.
    if ( \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes == 0) 
        {return (new \mvbplugins\postmapviewsimple\PostMapViewSimple($atts))->show_post_map();}
    \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes++;
}

add_shortcode('tourmap', 'mvbplugins\postmapviewsimple\register_tourmap_shortcode');
function register_tourmap_shortcode($atts) {
    // Die Klasse nur instanziieren, wenn der Shortcode genutzt wird.
    // TODO: ist die beschränkung auf einen Shortcode notwendig?
    if ( \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes == 0) 
        {return (new \mvbplugins\postmapviewsimple\PostMapViewSimple($atts))->show_tourmap();}
    \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes++;
}

function plugin_load_textdomain() {
    $path = dirname(plugin_basename(__FILE__)) . '/languages/';
    $result = load_plugin_textdomain('postmapviewsimple', false, $path);
    if ($result === false) {
        error_log('Plugin PostMapTableView: Fehler beim Laden der Sprachdatei');
    }
}
add_action('init', '\mvbplugins\postmapviewsimple\plugin_load_textdomain');

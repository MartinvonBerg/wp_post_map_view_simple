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
 * Description:       Anzeige aller Posts (max 100!) mit GPS-Daten (lat, lon) und Kategorie in einer Karte
 * Version:           0.10.5
 * Author:            Martin von Berg
 * Author URI:        https://www.berg-reise-foto.de/info/ueber-mich/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace mvbplugins\postmapviewsimple;

defined('ABSPATH') or die('Are you ok?');

//include_once __DIR__ . '/inc/post_map_table.php';
include_once __DIR__ . '/inc/PostMapViewSimpleClass.php';

function register_mapview_shortcode($atts) {
    // Die Klasse nur instanziieren, wenn der Shortcode genutzt wird.
    if ( \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes == 0) 
        {return (new \mvbplugins\postmapviewsimple\PostMapViewSimple($atts))->show_post_map();}
    \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes++;
}

add_shortcode('mapview', 'mvbplugins\postmapviewsimple\register_mapview_shortcode');
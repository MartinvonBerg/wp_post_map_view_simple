<?php

/**
 *
 * @link              https://github.com/MartinvonBerg/Post-Map-Table-View
 * @since             5.3.0
 * @package           PostMapTableView
 *
 * @wordpress-plugin
 * Plugin Name:       Post Map Table View
 * Plugin URI:        https://github.com/MartinvonBerg/Post-Map-Table-View
 * Description:       Shows a map with posts that have geotags. Or shows a tour map with posts that have geotags and a tour date.
 * Version:           1.5.1
 * Author:            Martin von Berg
 * Author URI:        https://www.berg-reise-foto.de/info/ueber-mich/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postmapviewsimple
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Tested up to:      7.0
 */

// TODOs / Ideas für das Plugin in der Tabelle
// in der Kopfzeile steht bei Distanz auch "m", das müsste eigentlich "km" heißen
//          Anpassung in der js/tabulator/tabulatorClass.js -> getLangs() -> 'Distance':'Distanz / km',
//          Dann aber auch in der js/tabulator/tabulatorClass.js -> getLangs() die en-en Übersetzung ergänzen.
//          TBD : Array als JSON auslagern, damit der Anwender ändern kann? Dann aber sanitizen, absichern, fallback im Code etc.
//          oder Array im Code direkt anpassen und mit einem shortcode Parameter anpassen "useUnitsinTable" ??
//          oder alles mit Shortcodes-Parametern vorgeben, damit es flexibel ist? Diese müssen auch bereinigt werden.
//          Der Headerfilter bleibt dann aber "m", da die Änderung zuviel Aufwand wäre. 
// eine Spalte mit dem Datum der Reise ? -> Aus GPX auslesen oder einem Custom-Field?
// eine Spalte mit dem Kategorie-Icon ? -> Ergänzung in der Kategorie-Spalte?
// eine Spalte mit der jeweiligen gpx-Datei (Überschrift "Download") -> GPX-Download, sonst unklar.

namespace mvbplugins\postmapviewsimple;

\defined('ABSPATH') or die('Are you ok?');

//include_once __DIR__ . '/inc/post_map_table.php';
include_once __DIR__ . '/inc/PostMapViewSimpleClass.php';

add_shortcode('mapview', 'mvbplugins\postmapviewsimple\register_mapview_shortcode');
/**
 * @param array<string, mixed> $atts
 */
function register_mapview_shortcode( array $atts ) :string {
    // Die Klasse nur instanziieren, wenn der Shortcode einmalig genutzt wird.
    if ( \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes === 0) 
    {
        // generate the html for the page and increase the counter for the number of shortcodes afterwards
        $html = (new \mvbplugins\postmapviewsimple\PostMapViewSimple($atts))->show_post_map();    
        \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes++;
        return $html;
    }
    return '';
}

add_shortcode('tourmap', 'mvbplugins\postmapviewsimple\register_tourmap_shortcode');
/**
 * @param array<string, mixed> $atts
 */
function register_tourmap_shortcode(array $atts ) : string {
    // Die Klasse nur instanziieren, wenn der Shortcode einmalig genutzt wird.
    if ( \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes === 0) 
    {
        // generate the html for the page and increase the counter for the number of shortcodes afterwards
        $html = (new \mvbplugins\postmapviewsimple\PostMapViewSimple($atts))->show_tourmap();
        \mvbplugins\postmapviewsimple\PostMapViewSimple::$numberShortcodes++;
        return $html;
    }
    return '';
}

function plugin_load_textdomain() : void {
    $path = dirname(plugin_basename(__FILE__)) . '/languages/';
    $result = load_plugin_textdomain('postmapviewsimple', false, $path);
    if ($result === false) {
        error_log('Plugin PostMapTableView: Fehler beim Laden der Sprachdatei');
    }
}
add_action('init', '\mvbplugins\postmapviewsimple\plugin_load_textdomain');

<?php

/**
 *
 * @link              www.mvb1.de
 * @since             1.0.0
 * @package           Wp_post_map_view_simple
 *
 * @wordpress-plugin
 * Plugin Name:       wp_post_map_view_simple
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige aller Posts (max 100!) mit GPS-Daten (lat, lon) und Kategorie in einer Karte
 * Version:           0.3.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') or die('Are you ok?');

add_shortcode('mapview', 'show_post_map');

// Wortlänge für excerpt vorgeben. Gilt für alle Excerpts auf der ganzen Seite!!!
function wp_example_excerpt_length( $length ) {
    return 50; // Anzahl Worte!
}
add_filter( 'excerpt_length', 'wp_example_excerpt_length');

function show_post_map($attr)
{
	global $wpdb;
	
	// Pfade und Verzeichnisse definieren
	$plugin_path = $plugin_url = plugins_url('/', __FILE__);
	$wp_postmap_path = $plugin_path . 'images/';
	
	$args = array('numberposts' => 100); 
	$custom_posts = get_posts($args);
	$i = 0;
	$string = '';
	$string .= '<div class="box1">';
	$string .= '<div id="map"></div>'; // hier wird die Karte erzeugt!
	$string .= '<div id="map10_img">';
	
	// Post auslesen und <a>-Tags mit allen Informationen schreiben
	
	foreach ($custom_posts as $post) { 
		$lat = get_post_meta($post->ID, 'lat', true);
		$lon = get_post_meta($post->ID, 'lon', true);
		
		if (!((is_null($lat) || (0 == $lat)) && (is_null($lon) || (0 == $lon)))) { // Achte auf das Not!
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
			$tags= get_the_tags($post->ID);
			$tagnames = array();
			
			if ($tags !== false) {
				foreach ($tags as $tag) {
					$tagnames[] = $tag->name;
				}
			} 
						
			$tag2 = implode(" ",$tagnames);
			$icon = wp_postmap_get_icon($tag2);
			$excerpt = get_the_excerpt($post->ID);
			
			if (strpos($excerpt, 'urzbeschr') !== false) { 
				$excerpt = ltrim(strstr($excerpt," "));
			}

			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$postlink = get_permalink($post->ID);
			$i++;
			$string  .= '<a alt="' . $title . '" href="' . $featimage . '" data-title="'.$title.'" data-icon="'. $icon. '" data-geo="lat:' . $lat . ',lon:' . $lon . '" data-link="'. $postlink .'">' . $excerpt. '</a>';
		}
	}
	
	$string  .= '</div></div>';
	$string  .= '<script>var g_wp_postmap_path = "' . $wp_postmap_path . '";'; 
	$string  .= '</script>';
	return $string;
}

require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

function wp_postmap_get_icon($arraytagnames)
{
	$icon = "";
	switch (true){
		case stristr($arraytagnames,'Trekk') !== false:
		   $icon = "hiking";
		   break;
		case ((stristr($arraytagnames,'bike') !== false) && (stristr($arraytagnames,'hike') !== false)):
		   $icon = "bike-hike";
		   break;
		case stristr($arraytagnames,'Radfahren') !== false:
			$icon = "cycling";
			break;   
		case stristr($arraytagnames,'MTB') !== false:
			$icon = "MTB";
			break;	
		case stristr($arraytagnames,'Wander') !== false:
			$icon = "hiking";
			break;
		case stristr($arraytagnames,'Bergtour') !== false:
			$icon = "mountain";
			break;	
		case stristr($arraytagnames,'skitour') !== false:
			$icon = "skiing"	;
			break;
		case stristr($arraytagnames,'reisebericht') !== false:
			$icon = "travel";
			break;	
		default:
			$icon = "travel";
		break;	
	 }
	 
	 return $icon;
}
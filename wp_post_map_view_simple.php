<?php

/**
 *
 * @link              www.mvb1.de
 * @since             0.0.1
 * @package           Wp_post_map_view_simple
 *
 * @wordpress-plugin
 * Plugin Name:       wp_post_map_view_simple
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige aller Posts (max 100!) mit GPS-Daten (lat, lon) und Kategorie in einer Karte
 * Version:           0.5.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
namespace mvbplugins\postmapviewsimple;

defined('ABSPATH') or die('Are you ok?');

add_shortcode('mapview', '\mvbplugins\postmapviewsimple\show_post_map');

function show_post_map($attr)
{
	// Pfade und Verzeichnisse definieren
	$plugin_path = plugins_url('/', __FILE__);
	$wp_postmap_path = $plugin_path . 'images/';
	$lenexcerpt = 150;
	
	$args = array('numberposts' => 100, 'post_type' => 'post'); 
	$custom_posts = get_posts($args);

	$i = 0;
	$string = '';
	$string .= '<div class="box1">';
	$string .= '<div id="map"></div>'; // hier wird die Karte erzeugt!
	$string .= '<div id="map10_img">';

	foreach ($custom_posts as $post) { 
		$lat = $post->lat;
		$lon = $post->lon;
		
		if (!((is_null($lat) || (0 == $lat)) && (is_null($lon) || (0 == $lon)))) { // Achte auf das Not!
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
		
			$tag3 =  implode( ', ', array_map( function( $tags ){
				return $tags->name;
			 }, get_the_tags($post->ID) ) );

			$icon = wp_postmap_get_icon($tag3);
		
			// Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
			// Absätze mit [shortcodes] werden ignoriert.
			// der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
			$content = $post->post_content;
			$p = '';
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
				$sub = substr($line,0,3); // html-tag aus der zeile ausschneiden
				$isshortcode = strpos($line,'['); 
				if (($sub == '<p>') and ($isshortcode == false)) {
					$p .= substr($line,3);
				}
				$p = str_replace('</p>','',$p);
			} 
			$p = str_replace('Kurzbeschreibung:','',$p);
			$p = str_replace('Tourenbeschreibung:','',$p);
			$p = strip_tags($p); // html-Tags entfernen
			$p = substr($p,0, $lenexcerpt); // erst jetzt auf die richtige länge kürszen
			$excerpt = $p . '...';

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
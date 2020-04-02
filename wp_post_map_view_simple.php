<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.mvb1.de
 * @since             1.0.0
 * @package           Wp_post_map_view
 *
 * @wordpress-plugin
 * Plugin Name:       wp_post_map_view_simple
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige aller Posts mit GPS-Daten (lat, lon) in einer Karte
 * Version:           1.0.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp_post_map_view
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('Are you ok?');

add_shortcode('mapview', 'show_post_map');

// Wortlänge für excerpt vorgeben
function wp_example_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'wp_example_excerpt_length');


function show_post_map($attr)
{
	global $wpdb;
	$mapheight = 600;
	$string = 'GEO-Daten werden angezeigt.';
	
	$args = array('numberposts' => 100); // exclude category 9
	$custom_posts = get_posts($args);
	$i = 0;
	// echo '<table>';
	// echo  '<th>Nr.</th>';
	// echo  '<th>ID</th>';
	// echo  '<th>Lat.</th>';
	// echo  '<th>Lon.</th>';
	// echo  '<th>Titel</th>';
	// echo  '<th>Kategorie</th>';
	// // echo  '<th>Feat_Img</th>';
	// // echo  '<th>Link</th>';
	// echo  '<th>Excerpt</th>';

	$string  .= '<div id="map10" class="gpxview::OPENTOPO" style="width:100%;height:' . $mapheight . 'px"></div>';
	$string  .= '<div id="map10_img">';
	
	// Bildinfo ausgeben, auch für SEO! 
	
	foreach ($custom_posts as $post) { 
		//setup_postdata($post);
		$lat = get_post_meta($post->ID, 'lat', true);
		$lon = get_post_meta($post->ID, 'lon', true);
		if (!((is_null($lat) || (0 == $lat)) && (is_null($lon) || (0 == $lon)))) { // Achte auf das Not!
			// echo '<tr>';
			// echo "<td> $i </td>";
			// echo "<td> $post->ID </td>";
			// echo "<td> $lat </td>";
			// echo "<td> $lon </td>";
			$title = substr($post->post_title,0,50);
			// echo "<td> $title </td>";
			$tags= get_the_tags($post->ID);
			$tagnames = array();
			if ($tags !== false) {
				foreach ($tags as $tag) {
					$tagnames[] = $tag->name;
				}
			}
			
			$tag2 = implode(" ",$tagnames);
			$icon = get_icon($tag2);
			// echo "<td> $icon </td>";
			//$string .= "<br>Nr. $i lat= $lat lon= $lon  Titel: $title";
			$excerpt = get_the_excerpt($post->ID);
			if (strpos($excerpt, 'urzbeschr') !== false) { 
				$excerpt = ltrim(strstr($excerpt," "));
			}
			//$string .= $excerpt;
			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			//$string .= $featimage;
			// echo "<td> $featimage </td>";
			
			$postlink = get_permalink($post->ID);
			// echo "<td> $postlink </td>";
			// echo "<td> $excerpt </td>";
			//$string .= $postlink;
			$alles = get_post_custom($post->ID);
			$i++;
			// echo '</tr>';
			$string  .= '<a alt="' . $title . '" href="' . $featimage . '" data-title="'.$title.'" data-icon="'. $icon. '" data-geo="lat:' . $lat . ',lon:' . $lon . '" data-link="'. $postlink .'">' . $excerpt. '</a>';
			//$string  .= '<div data-geo="lat:' . $lat . ',lon:' . $lon .'" data-icon='.$icon.' data-name="'. $title . '" data-link="'. $postlink .'">'.$excerpt.'</div>';
			//$string .= '<img src="'.$featimage.'" data-geo="lat:' . $lat . ',lon:' . $lon .'" data-link="'. $postlink .'" alt="'.$title.'" />';
			
		}
	}
	// echo '</table>';
	$string  .= '</div>';
	return $string;
}

require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

function get_icon($arraytagnames)
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
		case stristr($arraytagnames,'Bergwander') !== false:
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
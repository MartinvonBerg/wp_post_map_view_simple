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
	$string = 'GEO-Daten werden angezeigt.';
	
	$args = array('numberposts' => 100); // exclude category 9
	$custom_posts = get_posts($args);
	$i = 0;
	echo '<table>';
	echo  '<th>Nr.</th>';
	echo  '<th>ID</th>';
	echo  '<th>Lat.</th>';
	echo  '<th>Lon.</th>';
	echo  '<th>Titel</th>';
	echo  '<th>Feat_Img</th>';
	echo  '<th>Link</th>';
	echo  '<th>Excerpt</th>';
	foreach ($custom_posts as $post) { 
		//setup_postdata($post);
		echo '<tr>';
		echo "<td> $i </td>";
		echo "<td> $post->ID </td>";
		$lat = get_post_meta($post->ID, 'lat', true);
		$lon = get_post_meta($post->ID, 'lon', true);
		echo "<td> $lat </td>";
		echo "<td> $lon </td>";
		$title = $post->post_title;
		echo "<td> $title </td>";
		//$string .= "<br>Nr. $i lat= $lat lon= $lon  Titel: $title";
		$excerpt = get_the_excerpt($post->ID);
		if (strpos($excerpt, 'urzbeschr') !== false) { 
			$excerpt = ltrim(strstr($excerpt," "));
		}
		//$string .= $excerpt;
		$featimage = get_the_post_thumbnail_url($post->ID, $size='post-thumbnail'); 
		//$string .= $featimage;
		echo "<td> $featimage </td>";
		
		$postlink = get_permalink($post->ID);
		echo "<td> $postlink </td>";
		echo "<td> $excerpt </td>";
		//$string .= $postlink;
		$alles = get_post_custom($post->ID);
		$i++;
		echo '</tr>';
	}
	echo '</table>';
	return $string;
}

//require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

function strToFloat($str)
{
	if (is_int($str) || is_float($str)) {
		return floatval($str);
	}
	if (!is_string($str) || empty($str)) {
		//throw new Exception('String expected but received ' . gettype($str) . '.');
		$str = null;
		return $str;
	}
	$str = trim($str);
	if (!preg_match('/^(\-|\+)?[0-9][0-9\,\.]*/', $str)) {
		throw new Exception("Could not convert string to float. Given string does not match expected number format.");
	}

	$last = max(strrpos($str, ','), strrpos($str, '.'));
	if ($last !== false) {
		$str = strtr($str, ',.', 'XX');
		$str[$last] = '.';
		$str = str_replace('X', '', $str); // strtr funktioniert nicht mit $to=''
	}
	return (float) $str;
}

function CheckGps($gpsvalue)
{
	$wert = strToFloat($gpsvalue);
	if (($wert <= 180.0) && ($wert >= -180.0) && is_numeric($wert)) {
		return $wert;
	} else {
		return null;
	}
}
function get_excerpt(){
	$excerpt = get_the_content();
	$excerpt = preg_replace(" ([.*?])",'',$excerpt);
	$excerpt = strip_shortcodes($excerpt);
	$excerpt = strip_tags($excerpt);
	$excerpt = substr($excerpt, 0, 50);
	$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
	$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
	$excerpt = $excerpt.'... <a href="'.get_the_permalink().'">more</a>';
	return $excerpt;
	}

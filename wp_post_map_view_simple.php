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


function show_post_map($attr)
{
	global $wpdb;
	$string = 'GEO-Daten werden kopiert.';
	
	$args = array('numberposts' => 100); // exclude category 9
	$custom_posts = get_posts($args);
	foreach ($custom_posts as $post) { 
		//setup_postdata($post);
		$result = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."novomap_marker WHERE post_id = ".$post->ID);
		if (sizeof($result)>0) {
			$success = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."novomap_marker WHERE post_id = ".$post->ID);
			$infos = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."novomap_marker WHERE post_id = ".$post->ID);
			$oldlat = $infos->latitude;
			$oldlon = $infos->longitude;
			$success = update_post_meta($post->ID,'lat',$oldlat,'');
			$success = update_post_meta($post->ID,'lon',$oldlon,'');
		} else {
			$success = update_post_meta($post->ID,'lat','0','');
			$success = update_post_meta($post->ID,'lon','0','');
		}
		$lat = get_post_meta($post->ID, 'lat', true);
		$lon = get_post_meta($post->ID, 'lon', true);
		$title = $post->post_title;
		$string .= "<br> lat= $lat lon= $lon  Titel: $title";
		
		$alles = get_post_custom($post->ID);
		
		
	}

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

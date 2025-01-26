<?php

namespace mvbplugins\postmapviewsimple;

/**
 * Main function or Class of Post-Map-View-Simple
 *
 * Description: This file contains the main function or Class of Post-Map-View-Simple
 *
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Author: Martin von Berg
 * Author URI: https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * License: GPL-2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Post-Map-View-Simple
 */

// Prevent direct access
defined('ABSPATH') or die('Are you ok?');

require_once __DIR__ . '/geoaddress.php';
use function mvbplugins\helpers\get_geoaddress as get_geoaddress;

require_once __DIR__ . '/get_icon_cat.php';
use function mvbplugins\helpers\wp_postmap_get_icon as wp_postmap_get_icon;
use function mvbplugins\helpers\wp_postmap_get_cat as wp_postmap_get_cat;
use function mvbplugins\helpers\get_icon_mapping_array as get_icon_mapping_array;

add_shortcode('mapview', 'mvbplugins\postmapviewsimple\show_post_map');

/**
 * main shortcode function to generate the html
 *
 * @param array $attr parameters after the shortcode
 * @return string html-code to show on the page
 */
function show_post_map( $attr )
{
	// --------------- constructor start
	// Pfade, Verzeichnisse und Variablen definieren
	$plugin_path = plugin_dir_url(__DIR__);
	$wp_postmap_path = $plugin_path . 'images/';
	$gpxpath = get_option( 'fotorama_elevation_option_name' )['path_to_gpx_files_2'] ?? 'gpx'; // option?
	$up_dir = wp_get_upload_dir()['basedir'];     // upload_dir
	$gpx_dir = $up_dir . '/' . $gpxpath . '/';    // gpx_dir
	$postArray = [];
	$data2 = [];
	$lenexcerpt = 150; // option?

	// extract and handle shortcode parameters
	extract ( shortcode_atts ( array ( 
		'numberposts' => 100, 
		'post_type'   => 'post',
		'showmap'     => 'true',
		'showtable'   => 'true',
		'category'    => 'all',
		'headerhtml'  => '',
	), $attr));
	
	$args = array(
		'numberposts' => $numberposts, 
		'post_type'   => $post_type,
	); 	
	
	$showmap = $showmap === 'true';
	$showtable = $showtable === 'true';
	$category = \strtolower( $category );
	// --------------- constructor end

	// start processing of posts and prepara the data
	[$postArray, $data2] = prepare_data( $args, $gpx_dir, $lenexcerpt );

	// generate html for map with post data 
	$string = '';
	if ( $showmap ) {
		require_once __DIR__ . '/enqueue_map.php';
		$string = generate_map_html($postArray);
	}

	// generate html for table with post data
	if ( $showtable ){
		require_once __DIR__ . '/enqueue_tabulator.php';
		$table_out = generate_table_html( $headerhtml, $data2, $category );
		$string .= $table_out;
	}

	// ----------------
	// load icons for the categories
	$allIcons = get_icon_mapping_array();

	//enqueue scripts
	require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

	wp_localize_script('wp_post_map_view_simple_js', 'php_touren' , $postArray );
	wp_localize_script('wp_post_map_view_simple_js', 'g_wp_postmap_path' , array( 'g_wp_postmap_path'  => $wp_postmap_path, ));
	wp_localize_script('wp_post_map_view_simple_js', 'php_allIcons', $allIcons );
	// ----------------

	return $string;
}

function generate_table_html( $headerhtml, $data2, $category ) {
	// generate table with post data: generate the header
	if ( $headerhtml == '') {
		$table_out  = '<h4>Tourenübersicht</h4>';
		$table_out  .= '<p>Tabellarische Übersicht aller Touren- und Reiseberichte mit Filter- und Sortierfunktion<br></p>';
		$table_out  .= '<p>Die Kopfzeile ermöglicht die Suche in der Tabelle nach beliebigen Inhalten:</p>';
	} else {
		$headerhtml = str_replace(array("\r", "\n"), '', $headerhtml);
		$table_out  = $headerhtml;
	}
	$table_out  .= '<button id="tablereset" type="button">Reset Filter</button>';
	$table_out  .= '<table id="post_table"><thead><tr>';
						
	$table_out  .= '<th>Nr</th>';
	$table_out  .= '<th>Titel</th>';
	$table_out  .= '<th>Kategorie</th>';
	$table_out  .= '<th>Distanz</th>';
	$table_out  .= '<th>Aufstieg</th>';
	$table_out  .= '<th>Abstieg</th>';
	$table_out  .= '<th>Land</th>';
	$table_out  .= '<th>Region</th>';
	$table_out  .= '<th>Stadt</th>';
	$table_out  .= '</tr></thead><tbody>';
	
	// generate table with post data 
	$catcounter = 0;
	foreach ($data2 as $data) {
		$datacat = preg_replace("/[^a-zA-Z]+/", "", $data['wpcategory']);
		//$table_out  .= '<tr>';
		//$table_out  .= '<td>' . $datacat . ' : ' . $category . '</td>';
		//$table_out  .= '</tr>'; 

		if ( ($category === 'all') || ( $datacat == $category)) {
			// get geo statistics
			$geostatarr= \explode(' ', $data['geostat'] );
			isset($geostatarr[0]) ? '' :  $geostatarr[0] = '';
			isset($geostatarr[1]) ? '' :  $geostatarr[1] = '';
			isset($geostatarr[4]) ? '' :  $geostatarr[4] = '';
			isset($geostatarr[7]) ? '' :  $geostatarr[7] = '';
			
			// define google url
			$googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
			
			// define the table row 
			$table_out  .= '<tr>';
			$table_out  .= '<td>' . $data['id'] . '</td>';
			$table_out  .= '<td><a href="' . $data['link']. '" target="_blank">' . $data['title'] . '</a></td>';
			$table_out  .= '<td>' . $data['category'] . '</td>'; // category gehört hier rein!
			$geostatarr[1] = \str_replace(',', '.', $geostatarr[1]);
			$table_out  .= '<td>' . floatval($geostatarr[1]) ?? 0 . '</td>';
			$table_out  .= '<td>' . floatval($geostatarr[4]) ?? 0 . '</td>';
			$table_out  .= '<td>' . floatval($geostatarr[7]) ?? 0 . '</td>';
			$table_out  .= '<td>' . $data['country'] . '</td>';
			$table_out  .= '<td>' . $data['state'] . '</td>';
			$table_out  .= '<td><a href="' . $googleurl . '" target="_blank" rel="noopener noreferrer">'. $data['address'] .'</a></td>';
			$table_out  .= '</tr>';
			$catcounter += 1;
		}		
	}

	// finally close table
	$table_out  .= '</tbody></table>';
	if ( $catcounter == 0 ) $table_out = '';
	return $table_out;
}

function generate_map_html( $allposts ) {
	$string = '';
	$string .= '<div class="box1">';
	$string .= '<div id="map"></div>'; // hier wird die Karte erzeugt!
	$string .= '<div id="map10_img">';

	// loop through all posts and fetch data for the output
	foreach ($allposts as $post) {
		$string  .= '<a href="' . $post['featimage'] . '" data-title="'.$post['title'].'" data-icon="'. $post['icon']. '" data-geo="lat:' . $post['lat'] . ',lon:' . $post['lon'] . '" data-link="'. $post['link'] .'">' . $post['excerpt']. '</a>';
	}
	// close divs for the map
	$string  .= '</div></div>';

	return $string;
}

function generate_the_excerpt( $post, $length ) {
	$excerpt = $post->post_excerpt;
	$useWPExcerpt = false; // option?

	if ( ! empty( $excerpt ) ) {
		return $excerpt . '...';
	}

	if ( $useWPExcerpt ) {
		$excerpt = $post->post_content;
		$excerpt = apply_filters( 'the_content', $excerpt );
		$excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
		$excerpt = strip_shortcodes( $excerpt );
		$excerpt = strip_tags( $excerpt );
		$excerpt = substr( $excerpt, 0, $length );
		$excerpt = substr( $excerpt, 0, strrpos( $excerpt, ' ' ) );
		$excerpt .= '...';
	} else {
		$content = $post->post_content;
		$p = '';
		foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
			$sub = substr($line,0,3); // html-tag aus der zeile ausschneiden
			$isshortcode = strpos($line,'['); 
			if (($sub == '<p>') and (false == $isshortcode)) {
				$p .= substr($line, 3) . ' ';
			}	
			$p = str_replace('</p>','',$p);
		}
		// sanitize Excerpt
		$p = str_replace('Kurzbeschreibung:','',$p);
		$p = str_replace('Tourenbeschreibung:','',$p);
		$p = strip_tags($p); // html-Tags entfernen
		$p = substr($p,0, $length); // erst jetzt auf die richtige länge kürszen
		$excerpt = $p . '...';
		} 
	
	return $excerpt;
}

function get_statistics_from_gpxfile( $path_to_gpxfile ) {
	$geostat = '--';

	if ( \is_file( $path_to_gpxfile) ) {		
		$gpxdata = \simplexml_load_file($path_to_gpxfile);
		$geostat = (string) $gpxdata->metadata->desc;
		// geostat prüfen
		$geostatarr= \explode(' ', $geostat);

		if ( 'Dist:' !== $geostatarr[0] ) {
			$geostat = 'file valid but no statistics';
		} 
	}

	return $geostat;
}

function sanitize_geoaddress( $geoaddress ) {
    $address = $geoaddress['village'] ?? 
               $geoaddress['city'] ?? 
               $geoaddress['town'] ?? 
               $geoaddress['municipality'] ?? 
               $geoaddress['county'] ?? 
               $geoaddress['state'] ?? 
               'none';

    $state = $geoaddress['state'] ?? 
             $geoaddress['county'] ?? 
             $geoaddress['state_district'] ?? 
             'none';

    $country = $geoaddress['country'] ?? 'none';

    return compact('address', 'state', 'country');
}

function prepare_data( $args, $gpx_dir, $lenexcerpt ) {
	$custom_posts = get_posts($args);
	$i = 0;
	
	// loop through all posts and fetch data for the output
	foreach ($custom_posts as $post) { 
		
		$lat = get_post_meta($post->ID,'lat', true) ?? '';
		$lon = get_post_meta($post->ID,'lon', true) ?? '';
		$gpxfilearr = [];

		if ( ! ( (is_null($lat) || (0 == $lat) ) && (is_null($lon) || (0 == $lon)) ) ) { // Achte auf das Not!
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
		
			// tags des posts holen und in string umwandeln
			$tag3 =  implode( ', ', wp_get_post_tags($post->ID, array('fields' => 'names')));
			$icon = wp_postmap_get_icon($tag3);
			$cat = wp_postmap_get_cat($tag3);
			$wpcat = \get_the_category( $post->ID );
			$wpcat = count($wpcat) === 1 ? strtolower($wpcat[0]->name) : 'multiple';
		
			// Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
			// Absätze mit [shortcodes] werden ignoriert.
			// der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
			$content = $post->post_content;
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
				
				// extract the gpxfile from the shortcode
				$isshortcode = strpos($line,'[gpxview');	

				if ( is_numeric($isshortcode) ) {
					$line = str_replace(' ', '', $line);
					$isgpxfile = strpos($line,'gpxfile'); // suche nicht nach dem shortcode, sondern ob ein gpxfile definiert wirde

					if ( $isgpxfile) {
						$line = substr($line, $isgpxfile+9);
						$gpx = substr($line, 0, strpos($line, '"'));
						$morethanone = strpos($gpx, ',');
						if ($morethanone) {
							$gpxfilearr = explode(',', $gpx);
						} else {
							$gpxfilearr[] = $gpx;
						}
					}
				}
			}

			$excerpt = generate_the_excerpt($post, $lenexcerpt);
			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$postlink = get_permalink($post->ID);
			$i++;
			
			$postArray[] = array(
				'img' => $featimage,
				'title' 	=> $title,
				'category'  	=> $icon,
				'coord'   	=> array( floatval($lat), floatval($lon) ),
				'link' 	=> $postlink,
				'excerpt' 	=> $excerpt,
				'featimage' => $featimage,
				'icon' => $icon,
				'lat' => $lat,
				'lon' => $lon,
			);

			// get the address corresponding to posts lat and lon customfield
			$geoaddresstest =  get_post_meta($post->ID,'geoadress', false);
			$type = '';	
			if ( ! empty($geoaddresstest[0]) ) {
				$test = $geoaddresstest[0]; // we need only the first index
				$geoaddress = maybe_unserialize($test);	// type conversion to string
				$type = gettype( $geoaddress ); 
			} 

			$lat = number_format( floatval($lat), 6);
			$lon = number_format( floatval($lon), 6);

			if ( empty($geoaddresstest[0]) || 'string' == $type ) {
				$geoaddress = [];
				if (\current_user_can('edit_posts')) {
					$geoaddress = get_geoaddress($post->ID, $lat, $lon);
				} else {
					$geoaddress = 'was_string_but_not_set';
				}
			}

			$gpxcount = 1;
			if ( empty($gpxfilearr) ) $gpxfilearr = [''];

			foreach ($gpxfilearr as $gpxfile) {

				$geostat = get_statistics_from_gpxfile( $gpx_dir . $gpxfile );
				// sanitize geoaddress
				['address' => $address, 'state' => $state, 'country' => $country] = sanitize_geoaddress($geoaddress);

				$data2[] = array(
					'id' => count($gpxfilearr) == 1 ? $i : $i . '.' . $gpxcount,
					'lat' => $lat,
					'lon' => $lon,
					'title' => count($gpxfilearr) == 1 ? $title : $title . ' - ' . \str_replace('.gpx', '', $gpxfile),
					'category' => $cat,
					'link' => $postlink,
					'address' => $address,
					'country' => $country,
					'state' => $state,
					'gpxfile' => $gpxfile,
					'geostat' => $geostat,
					'wpcategory' => $wpcat,
				);
				$gpxcount++;
			}
		}
	}

	return [$postArray, $data2];
}

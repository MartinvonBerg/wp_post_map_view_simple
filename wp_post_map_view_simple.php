<?php

/**
 *
 * @link              www.mvb1.de
 * @since             5.3.0
 * @package           wp_post_map_view_simple
 *
 * @wordpress-plugin
 * Plugin Name:       wp_post_map_view_simple
 * Plugin URI:        www.mvb1.de
 * Description:       Anzeige aller Posts (max 100!) mit GPS-Daten (lat, lon) und Kategorie in einer Karte
 * Version:           0.7.0
 * Author:            Martin von Berg
 * Author URI:        www.mvb1.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
// TODO: force_update einführen?
namespace mvbplugins\postmapviewsimple;

defined('ABSPATH') or die('Are you ok?');

add_shortcode('mapview', '\mvbplugins\postmapviewsimple\show_post_map');

/**
 * main shortcode function to generate the html
 *
 * @param array $attr parameters after the shortcode
 * @return string html-code to show on the page
 */
function show_post_map($attr)
{
	// Pfade und Verzeichnisse definieren
	$plugin_path = plugins_url('/', __FILE__);
	$wp_postmap_path = $plugin_path . 'images/';
	$lenexcerpt = 150;
    $gpxpath = get_option( 'fotorama_elevation_option_name' )['path_to_gpx_files_2'] ?? 'gpx';
	//$up_url = gpxview_get_upload_dir('baseurl');  // upload_url
	$up_dir = wp_get_upload_dir()['basedir'];     // upload_dir
	$gpx_dir = $up_dir . '/' . $gpxpath . '/';    // gpx_dir
	//$gpx_url = $up_url . '/' . $gpxpath . '/';    // gpx_url

	// Report simple running errors
	//error_reporting(E_ERROR | E_PARSE);
	
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
		
			// tages des posts holen und in string umwandeln
			$tag3 =  implode( ', ', wp_get_post_tags($post->ID, array('fields' => 'names')));

          	$icon = \mvbplugins\postmapviewsimple\wp_postmap_get_icon($tag3);
			$cat = \mvbplugins\postmapviewsimple\wp_postmap_get_cat($tag3);
		
			// Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
			// Absätze mit [shortcodes] werden ignoriert.
			// der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
			$content = $post->post_content;
			$p = '';
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
				$sub = substr($line,0,3); // html-tag aus der zeile ausschneiden
				$isshortcode = strpos($line,'['); 
                if (($sub == '<p>') and (false == $isshortcode)) {
                    $p .= substr($line, 3) . ' ';
                }	
				$p = str_replace('</p>','',$p);

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
                     					
					} else { //$gpxfilearr = 'none';
					 }
				}	
			} 

			// sanitize Excerpt
			$p = str_replace('Kurzbeschreibung:','',$p);
			$p = str_replace('Tourenbeschreibung:','',$p);
			$p = strip_tags($p); // html-Tags entfernen
			$p = substr($p,0, $lenexcerpt); // erst jetzt auf die richtige länge kürszen
			$excerpt = $p . '...';

			$featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
			$postlink = get_permalink($post->ID);
			$i++;
			$string  .= '<a alt="' . $title . '" href="' . $featimage . '" data-title="'.$title.'" data-icon="'. $icon. '" data-geo="lat:' . $lat . ',lon:' . $lon . '" data-link="'. $postlink .'">' . $excerpt. '</a>';

			// get the address corresponding to posts lat and lon customfield
			$geoaddresstest =  get_post_meta($post->ID,'geoadress');	
			if ( ! empty($geoaddresstest[0]) ) {
				$geoaddress['country'] = '';
				$geoaddress['state'] = '';
				$geoaddress['village'] = '';
                $geoaddress['municipality'] = '';
                $geoaddress['town'] = '';
                $geoaddress['city'] = '';
                $geoaddress['county'] = '';
				$test = $geoaddresstest[0]; // we need only the first index
				$geoaddress = maybe_unserialize($test);	// type conversion to array
				$geoaddress = sanitize_geoaddress($geoaddress);
				/*
				foreach ($geoaddress as $key => $value) {
					if ($key != 'country') {
						$v .= $value . ', ';
					} else {
						$v .= $value;
						break;
					}
				}
				*/
			} else {
                $geoaddress = [];
                if (\current_user_can('edit_posts')) {
                    $geoaddress = get_geoaddress($post->ID, $lat, $lon);
                } else {
                    $geoaddress = sanitize_geoaddress($geoaddress);
				}
			}

			// get the statistics of the gpx-track, TODO: für alle Tracks
			/*
			$geostattest =  get_post_meta($post->ID,'geostat');
			if ( ! empty($geostattest[0]) ) {
				$test = $geostattest[0]; // we need only the first index
				$geostat = maybe_unserialize($test);	// type conversion to array
			
			} else {
                $path_to_gpxfile = '';
				$path_to_gpxfile = $gpx_dir . $gpxfile;

				if ( \is_file( $path_to_gpxfile) ) {		
					$gpxdata = \simplexml_load_file($path_to_gpxfile);
					$geostat = (string) $gpxdata->metadata->desc;
					// geostat prüfen
                    $geostatarr= \explode(' ', $geostat);

					if ('Dist:' == $geostatarr[0] && \current_user_can('edit_posts')) {
						//$geostatfield = maybe_serialize($geostat);
						//delete_post_meta($post->ID,'geostat');
						//update_post_meta($post->ID,'geostat', $geostatfield,'');
					} elseif ( 'Dist:' != $geostatarr[0] ) {
                        $geostat = 'file valid but no statistics';
					} 
                } else {
                    $geostat = '--';
				}
			}
			*/

			$lat = number_format( floatval($lat), 6);
			$lon = number_format( floatval($lon), 6);
            $gpxcount = 1;

			if ($gpxfilearr == null) {
                $gpxfilearr[0] = '';
			}

            foreach ($gpxfilearr as $gpxfile) {

				$path_to_gpxfile = '';
				$path_to_gpxfile = $gpx_dir . $gpxfile;

				if ( \is_file( $path_to_gpxfile) ) {		
					$gpxdata = \simplexml_load_file($path_to_gpxfile);
					$geostat = (string) $gpxdata->metadata->desc;
					// geostat prüfen
                    $geostatarr= \explode(' ', $geostat);

					if ('Dist:' == $geostatarr[0] && \current_user_can('edit_posts')) {
						//$geostatfield = maybe_serialize($geostat);
						//delete_post_meta($post->ID,'geostat');
						//update_post_meta($post->ID,'geostat', $geostatfield,'');
					} elseif ( 'Dist:' != $geostatarr[0] ) {
                        $geostat = 'file valid but no statistics';
					} 
                } else {
                    $geostat = '--';
				}

                $data2[] = array(
                    'id' => count($gpxfilearr) == 1 ? $i : $i . '.' . $gpxcount,
                    'lat' => $lat,
                    'lon' => $lon,
                    'title' => $title,
                    'category' => $cat,
                    'link' => $postlink,
                    'address' => (((($geoaddress['village'] ?? $geoaddress['city']) ?? $geoaddress['town']) ?? $geoaddress['municipality']) ?? $geoaddress['county']) ?? $geoaddress['state'],
                    'country' => $geoaddress['country'],
                    'state' => ($geoaddress['state'] ?? $geoaddress['county']) ?? $geoaddress['state_district'],
                    'gpxfile' => $gpxfile,
                    'geostat' => $geostat,
                );
                $gpxcount++;
            }
            $gpxfilearr = null;
		}
	}
	
	$string  .= '</div></div>';
	$string  .= '<script>var g_wp_postmap_path = "' . $wp_postmap_path . '";'; 
	$string  .= '</script>';

	// generate table with post data: generate the header
	$string  .= '<h3>Tourenübersicht</h3>';
	$string  .= '<p>Tabellarische Übersicht aller Touren- und Reiseberichte mit Filter- und Sortierfunktion</br></p>';
	$string  .= '<p>Suche in der Tabelle nach beliebigen Inhalten:</p>';
	
	//$string  .= '<div><table data-toggle="table" data-search="true" data-show-columns="true" data-show-search-clear-button="true" data-pagination="true"><thead><tr>';
    $string  .= '<div><table data-toggle="table" data-search="true" data-show-search-clear-button="true"><thead><tr>';
    $string  .= '<th data-sortable="true" data-field="id">Nr</th>';
    $string  .= '<th data-field="Titel">Titel</th>';
    $string  .= '<th data-sortable="true" data-field="Kategorie">Kategorie</th>';
	$string  .= '<th data-sortable="true" data-field="Distanz">Distanz</br>  km</th>';
	$string  .= '<th data-sortable="true" data-field="Aufstieg">Aufstieg</br>  Hm</th>';
	$string  .= '<th data-sortable="true" data-field="Abstieg">Abstieg</br> Hm</th>';
	$string  .= '<th data-sortable="true" data-field="Land">Land</th>';
	$string  .= '<th data-sortable="true" data-field="Region">Region</th>';
    $string  .= '<th data-sortable="true" data-field="Stadt">Stadt</th>';
	//$string  .= '<th>Breite</th><th>Länge</th>';
	$string  .= '</tr></thead><tbody>';

	// generate table with post data 
	foreach ($data2 as $data) {
        $gpxfile = $gpx_dir . $data['gpxfile'];
		$geostatarr= \explode(' ', $data['geostat'] );
        isset($geostatarr[0]) ? '' :  $geostatarr[0] = 0;
		isset($geostatarr[1]) ? '' :  $geostatarr[1] = 0;
		isset($geostatarr[4]) ? '' :  $geostatarr[4] = 0;
		isset($geostatarr[7]) ? '' :  $geostatarr[7] = 0;
       
		$googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
        $string  .= '<tr>';
		$string  .= '<td>' . $data['id'] . '</td>';
        $string  .= '<td><a href="' . $data['link']. '" target="_blank">' . $data['gpxfile'] . '</a></td>';
		$string  .= '<td>' . $data['category'] . '</td>'; // category gehört hier rein!
        $geostatarr[1] = \str_replace(',', '.', $geostatarr[1]);
		$string  .= '<td>' . $geostatarr[1] ?? 0 . '</td>';
		$string  .= '<td>' . $geostatarr[4] ?? 0 . '</td>';
		$string  .= '<td>' . $geostatarr[7] ?? 0 . '</td>';
		$string  .= '<td>' . $data['country'] . '</td>';
		$string  .= '<td>' . $data['state'] . '</td>';
		$string  .= '<td><a href="' . $googleurl . '" target="_blank">'. $data['address'] .'</a></td>';
		//$string  .= '<td>' . $data['lat'] . '</td>';
		//$string  .= '<td>' . $data['lon'] . '</td>';
		$string  .= '</tr>';
	}

    $string  .= '</tbody></table></div>';
	
	return $string;
}

require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

/**
 * Zuordnung eines sprechenden icon-namens für javascript zu den Tags eines Posts 
 * (MTB, Trekk, Wander, ...). Nur in deutsch. Vorsicht bei Übersetzung
 *
 * @param string $arraytagnames die Tags des Posts als String
 * @return string $icon die Kategorie des Posts als String
 */
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

/**
 * Zuordnung eines sprechenden Kategorienamens zu den Tags eines Posts 
 * (MTB, Trekk, Wander, ...). Nur in deutsch. Vorsicht bei Übersetzung
 *
 * @param string $arraytagnames die Tags des Posts als String
 * @return string $icon die Kategorie des Posts als String
 */
function wp_postmap_get_cat($arraytagnames)
{
	$icon = "";
	switch (true){
		case stristr($arraytagnames,'Trekk') !== false:
		   $icon = "Trekking";
		   break;
		case ((stristr($arraytagnames,'bike') !== false) && (stristr($arraytagnames,'hike') !== false)):
		   $icon = "Bike-Hike";
		   break;
		case stristr($arraytagnames,'Radfahren') !== false:
			$icon = "Radfahren";
			break;   
		case stristr($arraytagnames,'MTB') !== false:
			$icon = "MTB";
			break;	
		case stristr($arraytagnames,'Wander') !== false:
			$icon = "Wanderung";
			break;
		case stristr($arraytagnames,'Bergtour') !== false:
			$icon = "Bergtour";
			break;	
		case stristr($arraytagnames,'skitour') !== false:
			$icon = "Skitour"	;
			break;
		case stristr($arraytagnames,'reisebericht') !== false:
			$icon = "Reisebericht";
			break;	
		default:
			$icon = "Reisebericht";
		break;	
	 }
	 
	 return $icon;
}

/**
 * get the geoaddress for coordinates as json from momitatim
 *
 * @param integer $postid the id of the current post
 * @param string $lat latitude of the current post as stored in custom field lat
 * @param string $lon longitude of the current post as stored in custom field lon
 * @return string $geoadressfield the serialized geoaddress from the json-array 
 */
function get_geoaddress($postid, $lat, $lon) {
	$url = 'https://nominatim.openstreetmap.org/reverse?lat=' . $lat . '&lon='. $lon . '&format=json&zoom=10&accept-language=de';
	$opts = array(
		'http'=>array(
		'method'=>"GET",
		'header'=>'User-Agent: PostmanRuntime/7.26.8' // just any user-agent to fake a human access
		)
	);
	$context = stream_context_create($opts);
	$geojson = json_decode(file_get_contents( $url , false, $context ));
	$geoadress = (array) $geojson->address;
	$geoadressfield = maybe_serialize($geoadress);
	delete_post_meta($postid,'geoadress');
	update_post_meta($postid,'geoadress', $geoadressfield,'');
    return $geoadressfield;
}

/**
 * sanitize the geoaddress: set undefined array-keys to an empty string ''
 *
 * @param array $geoaddress geoaddress to sanitize
 * @return array $geoaddress sanitized geoaddress
 */
function sanitize_geoaddress($geoaddress) {
	isset($geoaddress['village']) ? '' : $geoaddress['village'] = '';
	isset($geoaddress['city']) ? '' : $geoaddress['city'] = '';
	isset($geoaddress['town']) ? '' : $geoaddress['town'] = '';
	isset($geoaddress['municipality']) ? '' : $geoaddress['municipality'] = '';
	isset($geoaddress['country']) ? '' : $geoaddress['country'] = '';
	isset($geoaddress['state']) ? '' : $geoaddress['state'] = '';
	isset($geoaddress['county']) ? '' : $geoaddress['county'] = '';
	isset($geoaddress['state_district']) ? '' : $geoaddress['state_district'] = '';
    return $geoaddress;
}

/**
 * Get the upload URL/path in right way (works with SSL).
 *
 * @param string $param  "basedir" or "baseurl"
 *
 * @param string $subfolder  subfolder to append to basedir or baseurl
 * 
 * @return string the base appended with subfolder
 */
function gpxview_get_upload_dir($param, $subfolder = '')
{
	
	$upload_dir = wp_get_upload_dir();
	$url = $upload_dir[$param];

	if ($param === 'baseurl' && is_ssl()) {
		$url = str_replace('http://', 'https://', $url);
	}

	return $url . $subfolder;
}
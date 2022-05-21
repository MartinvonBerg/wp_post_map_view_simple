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

add_shortcode('mapview', '\mvbplugins\postmapviewsimple\show_post_map');

/**
 * main shortcode function to generate the html
 *
 * @param array $attr parameters after the shortcode
 * @return string html-code to show on the page
 */
function show_post_map( $attr )
{
	// icons für javascript und php definieren als array of arrays
	// struktur eines sub-arrays: < Dateiname-Icon-im-Ordner-Images:string, zu-suchendes-Schlagwort-im-Post:string, Tour-Name-in-der-Karte:string >
	
	$allIcons = array(
		array ('hiking2.png', 'hiking', 'Wandern'),
		array ('mountainbiking-3.png', 'bike-hike', 'Bike-Hike'),
		array ('cycling.png', 'cycling', 'Radfahren'),
		array ('MTB.png', 'MTB', 'MTB'),
		array ('peak2.png', 'mountain', 'Bergtour'),
		array ('skiing.png', 'skiing', 'Skitour'),
		array ('kayaking2.png','kayaking','Paddeln'),
		array ('campingcar.png', 'travel', 'Reisebericht'),
	);
	
	// Pfade, Verzeichnisse und Variablen definieren
	$plugin_path = plugins_url('/', __FILE__);
	$wp_postmap_path = $plugin_path . 'images/';
	$gpxpath = get_option( 'fotorama_elevation_option_name' )['path_to_gpx_files_2'] ?? 'gpx';
	$up_dir = wp_get_upload_dir()['basedir'];     // upload_dir
	$gpx_dir = $up_dir . '/' . $gpxpath . '/';    // gpx_dir
	$postArray = [];
	$data2 = [];
	$lenexcerpt = 150;

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

	//enqueue scripts
	if ( $showmap )  require_once __DIR__ . '/enqueue_map.php';
	if ( $showtable )  require_once __DIR__ . '/enqueue_tabulator.php';
	require_once __DIR__ . '/wp_post_map_view_simple_enq.php';

	// start html-output generation
	$custom_posts = get_posts($args);
	$i = 0;
	$string = '';
	$string .= '<div class="box1">';
	$string .= '<div id="map"></div>'; // hier wird die Karte erzeugt!
	$string .= '<div id="map10_img">';
	// loop through all posts and fetch data for the output
	foreach ($custom_posts as $post) { 
		
		$lat = get_post_meta($post->ID,'lat', true) ?? '';
		$lon = get_post_meta($post->ID,'lon', true) ?? '';
		$gpxfilearr = null;

		if ( ! ( (is_null($lat) || (0 == $lat) ) && (is_null($lon) || (0 == $lon)) ) ) { // Achte auf das Not!
			$title = substr($post->post_title,0,80); // Länge des Titels beschränken, Anzahl Zeichen
		
			// tages des posts holen und in string umwandeln
			$tag3 =  implode( ', ', wp_get_post_tags($post->ID, array('fields' => 'names')));

			$icon = \mvbplugins\postmapviewsimple\wp_postmap_get_icon($tag3);
			$cat = \mvbplugins\postmapviewsimple\wp_postmap_get_cat($tag3);
			$wpcat = \get_the_category( $post->ID );
			if (count( $wpcat ) === 1) $wpcat = strtolower( $wpcat[0]->name );
			else $wpcat = 'multiple';
		
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
			$string  .= '<a href="' . $featimage . '" data-title="'.$title.'" data-icon="'. $icon. '" data-geo="lat:' . $lat . ',lon:' . $lon . '" data-link="'. $postlink .'">' . $excerpt. '</a>';

			$postArray[] = array(
				'img' => $featimage,
				'title' 	=> $title,
				'category'  	=> $icon,
				'coord'   	=> array( floatval($lat), floatval($lon) ),
				'link' 	=> $postlink,
				'excerpt' 	=> $excerpt,
			);

			// get the address corresponding to posts lat and lon customfield
			$geoaddresstest =  get_post_meta($post->ID,'geoadress');
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

				if       ( isset($geoaddress['village'])){
					$address = $geoaddress['village'];
				} elseif ( isset($geoaddress['city'])) {
					$address = $geoaddress['city'];	
				} elseif ( isset($geoaddress['town'])) {
					$address = $geoaddress['town'];
				} elseif ( isset($geoaddress['municipality'])) {
					$address = $geoaddress['municipality'];
				} elseif ( isset($geoaddress['county'])) {
					$address = $geoaddress['county'];
				} elseif ( isset($geoaddress['state'])) {
					$address = $geoaddress['state'];
				} else {
					$address = 'none';
				}

				if       ( isset($geoaddress['state']) ) {
						$state = $geoaddress['state'];
				} elseif ( isset($geoaddress['county'])) {
						$state = $geoaddress['county'];
				} elseif ( isset($geoaddress['state_district']))  {
						$state = $geoaddress['state_district'];
				} else {
					$state = 'none';
				}

				if (isset($geoaddress['country'])) {
					$country = $geoaddress['country'];
				} else {
					$country = 'none';
				}


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
			//$gpxfilearr = null;
		}
	}
	// close divs for the map
	$string  .= '</div></div>'; 

	// reset $string-output if showmap == false 
	if ( ! $showmap ) $string = '';

	//wp_localize_script('wp_post_map_view_simple_js', 'php_touren' , $postArray );

	if ( $showtable ){
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
				$gpxfile = $gpx_dir . $data['gpxfile'];
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
		$string .= $table_out;
	}

	wp_localize_script('wp_post_map_view_simple_js', 'php_touren' , $postArray );
	wp_localize_script('wp_post_map_view_simple_js', 'g_wp_postmap_path' , array( 'g_wp_postmap_path'  => $wp_postmap_path, ));
	wp_localize_script('wp_post_map_view_simple_js', 'php_allIcons', $allIcons );
		
	return $string;
}

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
			$icon = "skiing";
			break;
		case stristr($arraytagnames,'Paddeln') !== false:
			$icon = "kayaking";
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
		case stristr($arraytagnames,'Paddeln') !== false:
			$icon = "Paddeln";
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
 * get the geoaddress for coordinates as json from nominatim
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
	//delete_post_meta($postid,'geoadress');
	//update_post_meta($postid,'geoadress', $geoadressfield,'');
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
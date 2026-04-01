<?php

namespace mvbplugins\postmapviewsimple;

use DOMDocument;
use WP_Post;

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
 * Version: 1.5.0
 *
 * @package Post-Map-View-Simple
 */

// Prevent direct access
defined('ABSPATH') or die('Are you ok?');

if (!\defined('SETTINGS_FILE')) {
    define('SETTINGS_FILE', 'category_mapping.json');
}

require_once __DIR__ . '/geoaddress.php';
use function mvbplugins\helpers\get_geoaddress as get_geoaddress;

require_once __DIR__ . '/get_icon_cat.php';
use function mvbplugins\helpers\find_best_category_match as find_best_category_match;

require_once dirname(__DIR__) . '/leaflet_map_tiles/tileserver.php';
use function mvbplugins\fotoramamulti\checkHtaccess as checkHtaccess;
use function mvbplugins\fotoramamulti\generateHtaccess as generateHtaccess;

interface PostMapViewSimpleInterface {
	public function show_post_map(): string;
    public function show_tourmap(): string;
}

/**
 * main shortcode function to generate the html
 *  
 * @return string
 * 
 */
final class PostMapViewSimple implements PostMapViewSimpleInterface {

	public static int $numberShortcodes = 0;
    // ---------- shortcode parameters ----------
    private int $numberposts = 100; // is shortcode parameter. Max 1000 wg. PHP Memory Limit und max_allowed_packet bei MySQL.
    /** @var array<int, string>|string */
    private string|array $post_type = 'post'; // is shortcode parameter 
	private bool $showmap = true; // is shortcode parameter
	private bool $showtable = true; // is shortcode parameter
    private bool $tablefirst = false; // is shortcode parameter
    /** @var array<int, string>|string */
    private string|array $category = 'all'; // is shortcode parameter
	private string $headerhtml = ''; // is shortcode parameter
    private string $gpxfolder = 'gpx'; // to retrieve the gpx files added to posts with fotorama
    private int $lenexcerpt = 150;
    private bool $useWPExcerptExtraction = false;
    private int $titlelength = 80;
    private bool $useTileServer = true;
    private bool $convertTilesToWebp = true;
    /** @var array<int, string> */
    private array $contentFilter = ['Kurzbeschreibung:', 'Tourenbeschreibung:'];
    private string $tabulatorTheme = '';
    private int $tablePageSize = 20;
    private int $tableHeight = 0;
    private string $mapHeight = ''; // shortcode parameter as string px or %
    private string $mapWidth = ''; // shortcode parameter as string px or %
    private string $mapAspectRatio = ''; // shortcode parameter as number (int or float)
    private string $tourfolder  = '';
    private string $trackwidth	 = '3';
    private string $trackcolour = '#ff0000';
    private string $mapselector = 'OpenStreeMap';
    private bool $myMarkerIcons = false;
    /** @var array<int, string>|string */
    private string|array $categoryFilter = [];

    // new parametes to hide columns in the table by javascript so needed for pageVarsForJs (pageVars in JS) 
    private bool $hidetitle = false;
    private bool $hidecategory = false;
    private bool $hidedistance = false;
    private bool $hideascent = false;
    private bool $hidedescent = false;
    private bool $hidecountry = false;
    private bool $hidestate = false;
    private bool $hidecity = false;
    private bool $hidemap = false; // is very close to showmap!
        
    // ---------- end of shortcode parameters ----------

    private string $plugin_url;
    private string $wp_postmap_url;
	private string $up_dir;
	private string $gpx_dir;
    /** @var array<int, array<string, mixed>> */
	private array $postArray = [];
    /** @var array<int, array<string, mixed>> */
	private array $geoDataArray = [];
    private bool $htaccessTileServerIsOK = false;
    /** @var array<int|string, array<string, mixed>> */
    private array $pageVarsForJs = [];
    private ?int $m = null;
    /** @var positive-int */
    private int $chunksize = 20;
    private string $tableMapMoveSelector = '';

    private string $cf_distance = '';
    private string $cf_ascent = '';
    private string $cf_descent = '';
    private bool $hasGpxStatsCustomFields = false;
	
    /**
     * @param array<string, mixed> $attr
     */
	public function __construct( array $attr ) {
		
		// extract and handle shortcode parameters
        //'post_status' => 'publish' ist voreingestellt.
		$attr = shortcode_atts ([ 
			'numberposts' => 100, 
			'post_type'   => 'post', // laut doku geht das so : array( 'post', 'page', 'movie', 'book' ) post_types können mit array abgefragt werden
			'showmap'     => 'true',
			'showtable'   => 'true', 
            'tablefirst' => 'false', // new in V1.4.0
			'category'    => 'all', // mehrere Kategorien können über den slug zur Kategorie abgefragt werden. Case Sensitiv. Childs werden mit abgefragt! Geht nur einzeln nicht mit category_name=cat1+cat2!
			'headerhtml'  => '',
            'gpxfolder'   => 'gpx',
            'lenexcerpt'  => 150,
            'usewpexcerpt' => 'false',
            'titlelength' => 80,
            'usetileserver' => 'true',
            'converttiles'  => 'true',
            'contentfilter' => 'Kurzbeschreibung:,Tourenbeschreibung:',
            'tabulatortheme' => '',
            'tablepagesize' => 20,
            'tableheight' => 0,
            'mapheight' => '',
            'mapwidth' => '',
            'mapaspectratio' => '',
            'tourfolder'    => '',
            'trackwidth'		=> '3',
		    'trackcolour'		=> '#ff0000',
            'mapselector'       => 'OpenStreeMap',
            'mymarkericons'     => 'false',
            'categoryfilter'    => 'Reisebericht,Tourenbericht', // string
            // new parametes to hide columns in the table by javascript so needed for pageVarsForJs (pageVars in JS) 
            'hidetitle' => 'false',
            'hidecategory' => 'false',
            'hidedistance' => 'false',
            'hideascent' => 'false',
            'hidedescent' => 'false',
            'hidecountry' => 'false',
            'hidestate' => 'false',
            'hidecity' => 'false',
            'hidemap' => 'false',
            // new in V1.5.0
            'cf_distance' => '',
            'cf_ascent' => '',
            'cf_descent' => '',
        ], $attr);

        $this->plugin_url = plugin_dir_url(__DIR__);
		$this->wp_postmap_url = $this->plugin_url . 'images/';
		$this->up_dir = wp_get_upload_dir()['basedir'];     // upload_dir
		$this->gpxfolder = (string) $attr['gpxfolder'];
		$this->gpx_dir = $this->up_dir . \DIRECTORY_SEPARATOR . $this->gpxfolder . \DIRECTORY_SEPARATOR;    // gpx_dir
		
		$this->numberposts = (int) $attr['numberposts'];
        // fallback for great values
        if ( $this->numberposts > 1000 ) $this->numberposts = 1000;
        if ( $this->numberposts < 1 ) $this->numberposts = 1;

		$this->post_type = $this->parseParameterToArray((string) $attr['post_type']);
		$this->showmap = (string) $attr['showmap'] === 'true';
		$this->showtable = (string) $attr['showtable'] === 'true';
        $this->tablefirst = (string) $attr['tablefirst'] === 'true';
		$this->category = $this->parseParameterToArray(strtolower((string) $attr['category']));
		$this->headerhtml = (string) $attr['headerhtml'];
        $this->lenexcerpt = (int) $attr['lenexcerpt'];
        $this->useWPExcerptExtraction = (string) $attr['usewpexcerpt'] === 'true';
        $this->titlelength = (int) $attr['titlelength'];
        $this->useTileServer = (string) $attr['usetileserver'] === 'true';
        $this->convertTilesToWebp = (string) $attr['converttiles'] === 'true';
        $this->contentFilter = $this->parseParameterToList((string) $attr['contentfilter']);

        $this->tabulatorTheme = (string) $attr['tabulatortheme'];
        $this->tablePageSize = max(1, (int) $attr['tablepagesize']);
        $this->tableHeight = max(0, (int) $attr['tableheight']);
        $this->mapHeight = (string) $attr['mapheight'];
        $this->mapWidth = (string) $attr['mapwidth'];
        $this->mapAspectRatio = (string) $attr['mapaspectratio'];

        $this->m = self::$numberShortcodes;
        $this->pageVarsForJs[$this->m] = [];

        // CSS loading for tabulator does only work here and not in 'show_post_map()'.
        if ( $this->showtable ){
            $this->enqueue_tabulator_Theme($this->tabulatorTheme);
        }

        // extensions for tourmap
        $this->tourfolder = (string) $attr['tourfolder'];
        $this->trackwidth = (string) $attr['trackwidth'];
        $this->trackcolour = (string) $attr['trackcolour'];
        $this->mapselector = (string) $attr['mapselector'];
        $this->myMarkerIcons = (string) $attr['mymarkericons'] === 'true';
        $this->categoryFilter = $this->parseParameterToArray((string) $attr['categoryfilter']);

        // new parametes to hide columns in the table by javascript so needed for pageVarsForJs (pageVars in JS)
        $this->hidetitle = (string) $attr['hidetitle'] === 'true';
        $this->hidecategory = (string) $attr['hidecategory'] === 'true';
        $this->hidedistance = (string) $attr['hidedistance'] === 'true';
        $this->hideascent = (string) $attr['hideascent'] === 'true';
        $this->hidedescent = (string) $attr['hidedescent'] === 'true';
        $this->hidecountry = (string) $attr['hidecountry'] === 'true';
        $this->hidestate = (string) $attr['hidestate'] === 'true';
        $this->hidecity = (string) $attr['hidecity'] === 'true';
        $this->hidemap = (string) $attr['hidemap'] === 'true';

        // new parameters for custom fields for track statistics
        $this->cf_distance = (string) $attr['cf_distance'];
        $this->cf_ascent = (string) $attr['cf_ascent'];
        $this->cf_descent = (string) $attr['cf_descent'];

        // check if shortcode parameters for custom fields for track statistics are set. Could be different for every post.
        if ( !empty($this->cf_distance) && !empty($this->cf_ascent) && !empty($this->cf_descent) ) {
            $this->hasGpxStatsCustomFields = true;
        }
    }
	
	public function show_post_map(): string {

        $this->tableMapMoveSelector = 'City'; // No I18n required!
        
        // check the transient set time and delete transient if post was published during that time
        $wpid = get_the_ID();
        $wpid = $wpid ? strval($wpid) : '';
        $transient_duration = \WEEK_IN_SECONDS;
        $last_post_date = \get_lastpostmodified('server', 'any'); // "2020-12-24 13:16:03.000000"
        $last_post_date = \strtotime( $last_post_date ); // now in seconds from 01.01.1970 00:00:00.000
        $expires = (int) get_option( '_transient_timeout_post_map_html_output_' . $wpid, 0 ); // int value 0 if not set
        $transient_set_time = $expires - $transient_duration;

        if ( ($last_post_date > $transient_set_time || $this->is_user_editing_overview_map() ) ) {
            delete_transient( 'post_map_html_output_' . $wpid );
            delete_transient( 'post_map_js_pageVars_output_' . $wpid );
            $chunk_keys = get_option('post_map_array_chunk_keys_' . $wpid, []);
            if (!is_array($chunk_keys)) {
                $chunk_keys = [];
            }
            foreach ($chunk_keys as $chunk_key) {
                delete_option($chunk_key);
            }
            delete_option('post_map_array_chunk_keys_' . $wpid );
        }

        // generate the output if not set in transient
        $html = get_transient( 'post_map_html_output_' . $wpid );
        $temp = get_transient( 'post_map_js_pageVars_output_' . $wpid );
        $this->pageVarsForJs = is_array($temp) ? $temp : [];

        $chunk_keys = get_option('post_map_array_chunk_keys_' . $wpid, []);
        if (!is_array($chunk_keys)) {
            $chunk_keys = [];
        }
        foreach ($chunk_keys as $chunk_key) {
            $chunk = json_decode((string) get_option($chunk_key, '[]'), true);
            if (is_array($chunk)) {
                $this->postArray = array_merge($this->postArray, $chunk);
            }
        }
        
        if ( !$html || !$this->postArray || !$this->pageVarsForJs || $this->is_user_editing_overview_map() ) {

            // check htaccess for tileserver only here 
            if ( $this->useTileServer) {
                $this->htaccessTileServerIsOK = checkHtaccess();
                // if htaccess is not OK, write the file and set the flag to true
                if ( !$this->htaccessTileServerIsOK ) {
                    $this->htaccessTileServerIsOK = generateHtaccess();
                    !$this->htaccessTileServerIsOK ? $this->useTileServer=false : null;
                }
            }
            
            $this->pageVarsForJs = [];
            $this->pageVarsForJs[$this->m] = [];
            $this->pageVarsForJs[$this->m]['useTileServer'] = $this->useTileServer ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['convertTilesToWebp'] = $this->convertTilesToWebp ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['htaccessTileServerIsOK'] = $this->htaccessTileServerIsOK ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['imagepath'] = $this->wp_postmap_url;

            $this->pageVarsForJs[$this->m]['mapHeight'] = $this->mapHeight; //
            $this->pageVarsForJs[$this->m]['mapWidth'] = $this->mapWidth; //
            $this->pageVarsForJs[$this->m]['mapAspectRatio'] = $this->mapAspectRatio; //
            
            //$this->pageVarsForJs[$this->m]['tabulatorTheme'] = $this->tabulatorTheme; // TODO: why is it not used?
            $this->pageVarsForJs[$this->m]['tablePageSize'] = $this->tablePageSize;
            $this->pageVarsForJs[$this->m]['tableHeight'] = strval($this->tableHeight) . 'px';

            // set the hidecolumns parameters for JS
            $this->pageVarsForJs[$this->m]['hidetitle'] = $this->hidetitle ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidecategory'] = $this->hidecategory ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidedistance'] = $this->hidedistance ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hideascent'] = $this->hideascent ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidedescent'] = $this->hidedescent ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidecountry'] = $this->hidecountry ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidestate'] = $this->hidestate ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidecity'] = $this->hidecity ? 'true' : 'false';
            $this->pageVarsForJs[$this->m]['hidemap'] = $this->hidemap ? 'true' : 'false';

            $args = array(
                'numberposts' => $this->numberposts, 
                'post_type'   => $this->post_type,
                'post_status' => 'publish',
                'category_name'    => $this->category
                // Cusom field 'lat' and 'lon' must be set in the post. Add to the query if not set! geoaddress is optional!
            );
            
            // start processing of posts and prepara the data
            [$this->postArray, $this->geoDataArray] = $this->prepare_data( $args, $this->gpx_dir, $this->lenexcerpt );
            
            // generate html for map with post data 
            $html = '';

            // generate html for table with post data
            if ( $this->showtable && $this->tablefirst ){
                $html .= $this->generate_table_html( $this->headerhtml, $this->geoDataArray );
            }

            if ( $this->showmap ) {
                $html .= $this->generate_map_html($this->postArray);
            }
            
            // generate html for table with post data
            if ( $this->showtable && !$this->tablefirst ){
                $html .= $this->generate_table_html( $this->headerhtml, $this->geoDataArray );
            }

            // end generation of html output: write the html-output in $string now as set_transient
		    \set_transient('post_map_html_output_' . $wpid, $html, $transient_duration);
            \set_transient('post_map_js_pageVars_output_' . $wpid, $this->pageVarsForJs, $transient_duration);

		    $chunks = array_chunk($this->postArray, $this->chunksize);
            $chunk_keys = [];
            foreach ($chunks as $index => $chunk) {
                $chunk_key = "post_map_array_chunk_{$wpid}_{$index}";
                $saved = update_option($chunk_key, wp_json_encode($chunk), false); // true = autoload, aber hier nicht nötig
                $chunk_keys[] = $chunk_key;
            }
            update_option('post_map_array_chunk_keys_' . $wpid, $chunk_keys, false);
        }
            
        // --- enqueue scripts
        $plugin_url = plugin_dir_url(__DIR__);
        wp_enqueue_style('wp_pmtv_main_css', $plugin_url . 'css/wp_post_map_view_simple.css', [], '1.5.0', 'all');
        wp_enqueue_script('wp_pmtv_main_js', $plugin_url . 'build/pmtv_main.js', [], '1.5.0', true);
		
		wp_localize_script('wp_pmtv_main_js', 'php_touren' , $this->postArray );
		wp_localize_script('wp_pmtv_main_js', 'g_wp_postmap_path' , array( 
            'path'  => $this->wp_postmap_url, 
            'number' => self::$numberShortcodes+1, 
            'hasTable' => $this->showtable, 
            'hasMap' => $this->showmap,
            'type' => 'standard',
            'tableMapMoveSelector' => $this->tableMapMoveSelector ));
        wp_localize_script('wp_pmtv_main_js', 'pageVarsForJs', $this->pageVarsForJs);
		
		return $html;
	}

    public function show_tourmap(): string {
        if ( $this->tourfolder === '') {
            return '';
        }
        $this->tableMapMoveSelector = 'Map'; // No I18n required!
        $tourDir = $this->up_dir . '/' . $this->tourfolder;
        $tourUrl = wp_get_upload_dir()['baseurl'] . '/' . $this->tourfolder;
        $pathSettingsFile = null;

        if ( !is_dir( $tourDir ) ) {
            return '';
        }

        // get the geojson and gpx-file in the tourfolder and pass it to js.
        $jsonFiles = glob($tourDir . '/*.{json,geojson}', GLOB_BRACE) ?: [];
        // check if settings file is in $tourDir, if so exclude it from $jsonFiles and return the full url to the settings file
        if ( file_exists($tourDir . DIRECTORY_SEPARATOR . SETTINGS_FILE) ) {
            // exclude settings file from jsonFiles
            $jsonFiles = array_filter($jsonFiles, fn($item) => !str_contains($item, SETTINGS_FILE));
            $pathSettingsFile = $tourDir . DIRECTORY_SEPARATOR . SETTINGS_FILE;
        }
        $gpxFiles = glob($tourDir . '/*.gpx') ?: [];
        if ( !$jsonFiles && !$gpxFiles ) {
            return '';
        }

        // ------ parse geojseon files to php_touren array
        $idCounter = 0;
        foreach ( $jsonFiles as $jsonfile ) {
            
            $json0 = file_get_contents($jsonfile);
            if ( $json0 === false) { break; }
            $json0 = mb_convert_encoding($json0, 'UTF-8', 'auto');
            if (!is_string($json0)) { break; }
            $geojson = json_decode($json0, true);
            if (!is_array($geojson) || !isset($geojson['type'], $geojson['features']) || !is_array($geojson['features'])) {
                break;
            }
               
            if ($geojson['type'] !== 'FeatureCollection' || count($geojson['features']) === 0) { break; }
            
            foreach ( $geojson['features'] as $feature) {
                
                // parse the points to php_touren
                if ( strtolower($feature['geometry']['type']) !== 'point' || count($feature['properties']) === 0) { break; }

                // check whether from kml, maps-marker-pro or other source like manually generated
                $hasPopupInHTML = false;
                if (key_exists('popup', $feature['properties'] ) ) {
                    $test = $feature['properties']['popup'];
                    $hasPopupInHTML = $test !== strip_tags($test);
                }
                // Die 'category' wird in PHP für den Klarnamen der Kategorie in der Tabelle benutzt. 
                // Im JS zur Auswahl des Icons UND der Klarnamens im ControlLayer. 
                [$catname, $iconpng] = find_best_category_match($feature['properties']['category'], $this->categoryFilter ); 

                if ($hasPopupInHTML) { 
                    // extract from maps marker pro geojson
                    $parsed = $this->extractHTMLFromGeoJson($feature['properties']['popup'] );
                    
                    $this->postArray[] = array(
                        'id'        => $idCounter, 
                        'img'       => $feature['properties']['image'] ?? $parsed['image_link'] ?? '', //
                        'title' 	=> $parsed['title'] ?? $feature['properties']['name'] ?? $feature['properties']['title'] ?? '', //
                        'category'  => $catname,
                        //'iconpng'  	=> $iconpng,
                        'coord'   	=> [ $feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0] ],
                        'lat' => $feature['geometry']['coordinates'][1],
                        'lon' => $feature['geometry']['coordinates'][0],
                        'link' 	    => $parsed['link'] ?? $feature['properties']['link'] ?? '', //
                        'excerpt' 	=> $parsed['text'] ?? $feature['properties']['popop'] ?? $feature['properties']['text'] ?? $feature['properties']['description'] ?? '', // 
                    );
                } else {
                    // extract data from other sources
                    
                    $this->postArray[] = array(
                        'id'        => $idCounter, //
                        'img'       => $feature['properties']['image'] ?? '', //
                        'title' 	=> $feature['properties']['name'] ?? $feature['properties']['Name'] ?? $feature['properties']['title'] ?? '', //
                        'category'  => $catname,
                        //'iconpng'  	=> $iconpng,
                        'coord'   	=> [ $feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0] ],
                        'lat'       => $feature['geometry']['coordinates'][1], //
                        'lon'       => $feature['geometry']['coordinates'][0], //
                        'link' 	    => $feature['properties']['link'] ?? '', //
                        'excerpt' 	=> $feature['properties']['text'] ?? $feature['properties']['popop'] ?? $feature['properties']['description'] ?? '',
                    );
                }
                
                $idCounter++;
            }
        }
        // ---------- end loop array generation

        // replace the path to the directory by the url in array_shift
        $jsonFiles = array_map(function ($file) use ($tourUrl, $tourDir) {
            return str_replace($tourDir, $tourUrl, $file);
        }, $jsonFiles);

        $tracks = [];
        foreach( $gpxFiles as $index => $file){
            $url = str_replace($tourDir, $tourUrl, $file);
            $tracks['track_' . $index]['url'] = $url;
            $tracks['track_' . $index]['info'] = '';
        }

        if ( $pathSettingsFile !== null ) { $pathSettingsFile = str_replace($tourDir, $tourUrl, $pathSettingsFile); }
        else { $pathSettingsFile = ''; }

        // check htaccess for tileserver only here 
        if ( $this->useTileServer) {
            $this->htaccessTileServerIsOK = checkHtaccess();
            // if htaccess is not OK, write the file and set the flag to true
            if ( !$this->htaccessTileServerIsOK ) {
                $this->htaccessTileServerIsOK = generateHtaccess();
                !$this->htaccessTileServerIsOK ? $this->useTileServer=false : null;
            }
        }
        
        $this->pageVarsForJs = [];
        $this->pageVarsForJs[$this->m] = [];
        $this->pageVarsForJs[$this->m]['useTileServer'] = $this->useTileServer ? 'true' : 'false';
        $this->pageVarsForJs[$this->m]['convertTilesToWebp'] = $this->convertTilesToWebp ? 'true' : 'false';
        $this->pageVarsForJs[$this->m]['htaccessTileServerIsOK'] = $this->htaccessTileServerIsOK ? 'true' : 'false';
        $this->pageVarsForJs[$this->m]['imagepath'] = $this->wp_postmap_url;

        $this->pageVarsForJs[$this->m]['mapHeight'] = $this->mapHeight; //
        $this->pageVarsForJs[$this->m]['mapWidth'] = $this->mapWidth; //
        $this->pageVarsForJs[$this->m]['mapAspectRatio'] = $this->mapAspectRatio; //
        
        $this->pageVarsForJs[$this->m]['tablePageSize'] = $this->tablePageSize;
        $this->pageVarsForJs[$this->m]['tableHeight'] = strval($this->tableHeight) . 'px';
        $this->pageVarsForJs[$this->m]['geoJsonFile'] = $jsonFiles;
        $this->pageVarsForJs[$this->m]['settingsFile'] = $pathSettingsFile;
        $this->pageVarsForJs[$this->m]['ngpxfiles'] = count($gpxFiles);
        $this->pageVarsForJs[$this->m]['tracks'] = $tracks;
        $this->pageVarsForJs[$this->m]['trackcolour'] = $this->trackcolour;
        $this->pageVarsForJs[$this->m]['trackwidth'] = $this->trackwidth;
        $this->pageVarsForJs[$this->m]['mapselector'] = $this->mapselector;
        $this->pageVarsForJs[$this->m]['myMarkerIcons'] = $this->myMarkerIcons;
       

        // generate html for map with post data 
        $html = '';

        if ( $this->showmap && !$this->tablefirst ) {
            $html .= $this->generate_map_html([1,1,1]);
        }

        // generate html for table with post data
        if ( $this->showtable ){
            $html .= '<div id="post_table_wrapper"><div>'; // hier 2x div, da sonst mit Headerhtml eine flex-Tabelle angezeigt wird.
            $html .= $this->generate_table_html($this->headerhtml, $this->postArray, 'tourmap');
            $html .= '</div></div>';
        }

        if ( $this->showmap && $this->tablefirst ) {
            $html .= $this->generate_map_html([1,1,1]);
        }

        // --- enqueue scripts and styles ---
        $plugin_url = plugin_dir_url(__DIR__);
        wp_enqueue_style('wp_pmtv_main_css', $plugin_url . 'css/wp_post_map_view_simple.css', [], '1.5.0', 'all');
        wp_enqueue_script('wp_pmtv_main_js', $plugin_url . 'build/pmtv_main.js', [], '1.5.0', true);
        wp_localize_script('wp_pmtv_main_js', 'php_touren' , $this->postArray );
		wp_localize_script('wp_pmtv_main_js', 'g_wp_postmap_path' , array( 
            'path'  => $this->wp_postmap_url, 
            'number' => self::$numberShortcodes+1, 
            'hasTable' => $this->showtable, 
            'hasMap' => $this->showmap,
            'type' => 'tourmap',
            'tableMapMoveSelector' => $this->tableMapMoveSelector ));
        wp_localize_script('wp_pmtv_main_js', 'pageVarsForJs', $this->pageVarsForJs);

        return $html;
    }
    // ---------------- pivate functions ----------------
	/**
	 * @return array<int, string>|string
	 */
    private function parseParameterToArray(string $input): string|array {
        // Entferne unnötige Anführungszeichen
        $input = trim($input, '"');
        
        // Ersetze mögliche Trennzeichen durch Komma
        $input = str_replace([';', ' '], ',', $input);
        
        // Zerlege den String in ein Array
        $items = array_map('trim', explode(',', $input));
        
        // Entferne leere Einträge
        $filteredItems = array_filter($items, fn($item) => $item !== '');
        
        // Wenn nur ein Element übrig bleibt, gib es als String zurück
        if (count($filteredItems) === 1) {
            return reset($filteredItems);
        }
        
        // Ansonsten gib das Array zurück
        return $filteredItems;
    }

    /**
     * @return array<int, string>
     */
    private function parseParameterToList(string $input): array {
        $result = $this->parseParameterToArray($input);

        return is_array($result) ? array_values($result) : [$result];
    }
    
    private function enqueue_tabulator_Theme(string $theme): void {
        $plugin_url = plugin_dir_url(__DIR__);

        $themes = [
            'bootstrap3' => 'tabulator_bootstrap3.min.css',
            'bootstrap4' => 'tabulator_bootstrap4.min.css',
            'bootstrap5' => 'tabulator_bootstrap5.min.css',
            'bulma' => 'tabulator_bulma.min.css',
            'materialize' => 'tabulator_materialize.min.css',
            'midnight'  => 'tabulator_midnight.min.css',
            'modern' => 'tabulator_modern.min.css',
            'semanticui' => 'tabulator_semanticui.min.css',
            'simple' => 'tabulator_simple.min.css',
            'site_dark' => 'tabulator_site_dark.min.css',
            'site' => 'tabulator_site.min.css',
            'default' => 'tabulator.min.css',
            'standard ' => 'tabulator.min.css',
            'custom' => 'tabulator_custom.min.css'
        ];

        // fallback to default theme if argument $theme is not in array $themes
        if ( !array_key_exists($theme, $themes) ) {
            $theme = '';
        }

        // Load default Styles
        if ( $theme == '' ) {
            wp_enqueue_style('tabulator_css', $plugin_url . 'css/tabulator.min.css', [], '1.5.0', 'all');
        } else {
            wp_enqueue_style('tabulator_css', $plugin_url . 'css/' . $themes[$theme], [], '1.5.0', 'all');
        }
    }

	/**
	 * @param array<string, mixed> $data
	 * @return array{id: string, title: string, category: string, link: string, lat: string, lon: string, country: string, state: string, address: string, geostat: string}
	 */
    private function sanitize_table_row(array $data): array {
        return [
            'id'       => isset($data['id'])       ? esc_html((string) $data['id'])  : '',
            'title'    => isset($data['title'])    ? esc_html($data['title'])         : '',
            'category' => isset($data['category']) ? esc_html($data['category'])      : '',
            'link'     => isset($data['link'])     ? esc_url($data['link'])           : '',
            'lat'      => isset($data['lat'])      ? number_format((float) $data['lat'], 6) : '0.000000',
            'lon'      => isset($data['lon'])      ? number_format((float) $data['lon'], 6) : '0.000000',
            'country'  => isset($data['country'])  ? esc_html($data['country'])       : '',
            'state'    => isset($data['state'])    ? esc_html($data['state'])         : '',
            'address'  => isset($data['address'])  ? esc_html($data['address'])       : '',
            'geostat'  => isset($data['geostat'])  ? esc_html($data['geostat'])       : '0 0 0 0 0 0 0 0',
        ];
    }

	/**
	 * @param array<int, array<string, mixed>> $data2
	 */
    private function generate_table_html(string $headerhtml, array $data2, string $caller = ''): string {
        if ( count($data2) === 0) return '';

        // generate table with post data: generate the header
        if ( $headerhtml == '') {
            $table_out  = '<h4>Tourenübersicht</h4>';
            $table_out  .= '<p>Tabellarische Übersicht aller Touren- und Reiseberichte mit Filter- und Sortierfunktion<br></p>';
            $table_out  .= '<p>Die Kopfzeile ermöglicht die Suche in der Tabelle nach beliebigen Inhalten:</p>';
        } else if ( $headerhtml == 'none' ) {
            $table_out  = '';
        } 
        else {
            $headerhtml = str_replace(array("\r", "\n"), '', $headerhtml);
            $table_out  = $headerhtml;
        } 

        $table_out  .= '<table id="post_table"><thead><tr>';

        /* translators: This strings are translated in javascript, so no translation needed! The info is kept for later usage */
        /* translators: Table Row 1: Number Nr */
        /* flag: format: text */  
        $table_out  .= '<th data-filter="false">Nr</th>'; // I18n
        /* translators: Table Row 2: Title */
        /* flag: format: html */
        $table_out  .= '<th data-type="html">Title</th>'; // I18n
        /* translators: Table Row 3: Category */
        /* flag: format: text */
        $table_out  .= '<th>Category</th>'; // I18n
        
        if ( $caller !== 'tourmap' ) {
            /* translators: Table Row 4: Distance */
            /* flag: format: text */
            $table_out  .= '<th data-filter="number">Distance</th>'; // I18n
            /* translators: Table Row 5: Ascent */
            /* flag: format: text */
            $table_out  .= '<th data-filter="number">Ascent</th>'; // I18n
            /* translators: Table Row 6: Descent */
            /* flag: format: text */
            $table_out  .= '<th data-filter="number">Descent</th>'; // I18n
            /* translators: Table Row 7: Country */
            /* flag: format: text */
            $table_out  .= '<th>Country</th>'; // I18n
            /* translators: Table Row 8: State */
            /* flag: format: text */
            $table_out  .= '<th>State</th>'; // I18n
            /* translators: Table Row 9: City */
            /* flag: format: html */
            $table_out  .= '<th data-type="html" data-selector="true">City</th>'; // I18n
            //$table_out  .= '<th>'.$this->tableMapMoveSelector.'</th>'; // tableMapMoveSelector
        } else {
            /* translators: Table Row 4 or 10: Map Link*/
            /* flag: format: html */
            $table_out  .= '<th data-type="html" data-filter="false" data-selector="true">Map</th>'; // I18n
            //$table_out  .= '<th>'.$this->tableMapMoveSelector.'</th>'; // tableMapMoveSelector
        }
        $table_out  .= '</tr></thead><tbody>';
        
        // generate table with post data 
        if ( $caller !== 'tourmap' ) {
            foreach ($data2 as $data) {
                $data = $this->sanitize_table_row($data);
                // get geo statistics
                $geostatarr= \explode(' ', $data['geostat'] ); // gives strings of the values
                
                // define google url
                //$googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
                //https://wego.here.com/l/39.93171,16.1693?map=39.93306,16.16222,9
                $googleurl = 'https://wego.here.com/l/' . $data['lat'] . ',' . $data['lon'] . '?map=' . $data['lat'] . ',' . $data['lon'] . '&z=9';
                // OSM : https://www.openstreetmap.org/?mlat=39.93171&mlon=16.16933#map=9/39.93171/16.16933
                
                // define the table row 
                $table_out  .= '<tr>';
                $table_out  .= '<td>' . $data['id'] . '</td>';
                $table_out  .= '<td><a href="' . $data['link']. '" target="_blank">' . $data['title'] . '</a></td>';
                $table_out  .= '<td>' . $data['category'] . '</td>'; // category gehört hier rein!
                $table_out  .= '<td>' . $geostatarr[1] . '</td>';
                $table_out  .= '<td>' . $geostatarr[4] . '</td>';
                $table_out  .= '<td>' . $geostatarr[7] . '</td>';
                $table_out  .= '<td>' . $data['country'] . '</td>';
                $table_out  .= '<td>' . $data['state'] . '</td>';
                $table_out  .= '<td><a href="' . $googleurl . '" target="_blank" rel="noopener noreferrer">'. $data['address'] .'</a></td>';
                $table_out  .= '</tr>';
            }
        } else {
            foreach ($data2 as $data) {
                $data = $this->sanitize_table_row($data);
                // define google url
                //$googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
                $googleurl = 'https://wego.here.com/l/' . $data['lat'] . ',' . $data['lon'] . '?map=' . $data['lat'] . ',' . $data['lon'] . '&z=9';
                // OSM : https://www.openstreetmap.org/?mlat=39.93171&mlon=16.16933#map=9/39.93171/16.16933
                
                // define the table row 
                $table_out  .= '<tr>';
                $table_out  .= '<td>' . $data['id'] . '</td>';
                if ($data['link'] == '') {
                    $table_out  .= '<td>' . $data['title'] . '</td>';
                } else {
                    $table_out  .= '<td><a href="' . $data['link']. '" target="_blank">' . $data['title'] . '</a></td>';
                }
                $table_out  .= '<td>' . $data['category'] . '</td>'; // category gehört hier rein!
                $table_out  .= '<td><a href="' . $googleurl . '" target="_blank" rel="noopener noreferrer">&#8811;&#8811;&#8811;</a></td>';
                $table_out  .= '</tr>';
            }
        };
        // finally close table
        $table_out  .= '</tbody></table>';
        
        return $table_out;
    }
    
	/**
	 * @param array<int, mixed> $allposts
	 */
    private function generate_map_html( array $allposts ) : string {
        if ( count( $allposts) === 0) return '';
        
        $string = '<div class="box1">';
        $string .= '<div id="map0"></div>'; // hier wird die Karte erzeugt!
        $string .= '</div>';
    
        return $string;
    }
    
    /**
     * @param WP_Post|object{post_excerpt: string, post_content: string} $post
     */
    private function generate_the_excerpt( object $post, int $length ) : string {
        $excerpt = $post->post_excerpt;
    
        if ( ! empty( $excerpt ) ) {
            return $excerpt;
        }
    
        if ( $this->useWPExcerptExtraction ) {
            $excerpt = apply_filters('the_content', $post->post_content);
            if (!is_string($excerpt)) {
                $excerpt = '';
            }

            // Entferne alle HTML-Headings (h1 bis h6) inklusive ihrer Inhalte
            $excerpt = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/si', '', $excerpt);
            if (!is_string($excerpt)) {
                $excerpt = '';
            }

            // Entferne doppelte oder überflüssige Leerzeilen und konvertiere nicht-HTML Leerzeichen
            $excerpt = preg_replace(["/[\r\n]{2,}/", '/&nbsp;/'], ["\n", ' '], $excerpt);
            if (!is_string($excerpt)) {
                $excerpt = '';
            }

            // Entferne Shortcodes, Tags und trimme den Text
            $excerpt = trim(strip_tags(strip_shortcodes($excerpt)));

            // Kürze den Text auf die gewünschte Länge
            $excerpt = substr($excerpt, 0, $length) . '...';
        } else {
            $content = $post->post_content;
            $p = '';
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) ?: [] as $line) { 
                $line = trim($line);
                $sub = substr($line,0,3); // html-tag aus der zeile ausschneiden
                $isshortcode = strpos($line,'['); 
                if (($sub == '<p>') and (false == $isshortcode)) {
                    $p .= substr($line, 3) . ' ';
                }	
                $p = str_replace('</p>','',$p);
            }
            // sanitize Excerpt
            $p = str_replace($this->contentFilter, '', $p);
            $p = trim(strip_tags($p)); // html-Tags entfernen
            $excerpt = substr($p,0, $length) . '...'; // erst jetzt auf die richtige länge kürszen
            } 
        
        return $excerpt;
    }
    
    /**
     * This function reads a gpx file and returns the statistics as a string in the format 'distance ascent descent points time movingtime'
     * 
     * @param string $path_to_gpxfile the path to the gpx file which is expected to have the statistics in the desc field of the metadata like so
     * <metadata>
     *   <name>Skitour-Güntlespitze.gpx</name>
     *   <desc>Dist: 7.5 km, Gain: 866 Hm, Loss: 860 Hm</desc>
     *   <time>2026-01-31T07:21:33.000Z</time>
     *   <bounds minlat="47.3009692132473" maxlat="47.30993600562215" minlon="10.078713102266192" maxlon="10.121628362685442" />
     * </metadata>
     * 
     * @return string the statistics as a string in the format 'distance ascent descent points time movingtime'
     */
    private function get_statistics_from_gpxfile(string $path_to_gpxfile): string {
        $default = 'Dist: 0.0 km, Gain: 0 m, Loss: 0 m';

        // 1. Basic file checks
        if (!\is_file($path_to_gpxfile) || !\is_readable($path_to_gpxfile)) {
            return $default;
        }

        // 2. Ensure SimpleXML is available (avoid fatal error)
        if (!\function_exists('simplexml_load_file')) {
            return $default;
        }

        // 3. Prevent URL usage (avoid allow_url_fopen issues)
        if (\preg_match('#^(https?:)?//#i', $path_to_gpxfile)) {
            return $default;
        }

        // 4. Load XML safely
        \libxml_use_internal_errors(true);
        $gpxdata = \simplexml_load_file($path_to_gpxfile);

        if ($gpxdata === false) {
            \libxml_clear_errors();
            return $default;
        }

        // 5. Check required structure
        if (!isset($gpxdata->metadata->desc)) {
            return $default;
        }

        $geostat = (string) $gpxdata->metadata->desc;

        if ($geostat === '') {
            return $default;
        }

        // 6. Robust parsing using regex (order-independent, whitespace-safe)
        $distance = $ascent = $descent = null;

        if (\preg_match('/Dist:?\s*([\d.,]+)/i', $geostat, $m)) {
            $distance = $this->normalizeNumber($m[1], 1);
        }

        if (\preg_match('/Gain:?\s*([\d.,]+)/i', $geostat, $m)) {
            $ascent = $this->normalizeNumber($m[1], 0);
        }

        if (\preg_match('/Loss:?\s*([\d.,]+)/i', $geostat, $m)) {
            $descent = $this->normalizeNumber($m[1], 0);
        }

        // 7. Validate extracted values
        if ($distance === null || $ascent === null || $descent === null) {
            return $default;
        }

        // 8. Rebuild string in original expected format (positions preserved)
        return "Dist: $distance km, Gain: $ascent m, Loss: $descent m";
    }

    private function get_statistics_from_postmeta( int $postID): string {
        $default = 'Dist: 0.0 km, Gain: 0 m, Loss: 0 m';

        // guard the function against missing post meta keys or empty values which was checked in the constructor.
        if ( !$this->hasGpxStatsCustomFields) {
            return $default;
        }

        $distance =  get_post_meta($postID, $this->cf_distance, true) ?? '';
        $ascent = get_post_meta($postID, $this->cf_ascent, true) ?? '';
        $descent = get_post_meta($postID, $this->cf_descent, true) ?? '';

        // normalize the values
        $distance = $this->normalizeNumber($distance, 1);
        $ascent = $this->normalizeNumber($ascent, 0);
        $descent = $this->normalizeNumber($descent, 0);

        // Validate extracted values
        if ($distance === "0.0" || $ascent === "0" || $descent === "0") {
            return $default;
        }

        // Rebuild string in original expected format (positions preserved)
        return "Dist: $distance km, Gain: $ascent m, Loss: $descent m";
    }

    private function normalizeNumber( string $numberString, int $decimals = 1) : string {
        // Ersetze Komma durch Punkt
        $numberString = str_replace(',', '.', $numberString);
        
        // In eine Gleitkommazahl umwandeln
        $number = (float) $numberString;
        
        // Auf eine Nachkommastelle runden und als String formatieren
        return number_format($number, $decimals, '.', '');
    }
    
	/**
	 * @param array<string, string>|string $geoaddress
	 * @return array{address: string, state: string, country: string}
	 */
    private function sanitize_geoaddress( string|array $geoaddress ) : array {
        if ( \is_string( $geoaddress ) ) {
            return [
                'address' => 'none',
                'state' => 'none',
                'country' => 'none'
            ];
        }
    
        // sanitize geoaddress values
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
    
    	/**
    	 * @param array<string, mixed> $queryArgs
    	 * @return array{0: array<int, array<string, mixed>>, 1: array<int, array<string, mixed>>}
    	 */
    private function prepare_data( array $queryArgs, string $gpx_dir, int $lenexcerpt ) : array {
        $postArray = [];
        $data2 = [];
        $custom_posts = [];
        
        // add lat and lon to queryArgs
        $queryArgs['meta_query'] = [
            'relation' => 'AND', // Beide Bedingungen müssen erfüllt sein
            [
                'key'     => 'lat',
                'compare' => 'BETWEEN', // Stellt sicher, dass das Feld existiert
                'value'   => array(-90, 90),
                'type'    => 'NUMERIC'
            ],
            [
                'key'     => 'lon',
                'compare' => 'BETWEEN', // Stellt sicher, dass das Feld existiert
                'value'   => array(-180, 180),
                'type'    => 'NUMERIC'
            ],
        ];
        
        // remove category_name from queryArgs if it is 'all'
        if ( array_key_exists('category_name', $queryArgs) && $queryArgs['category_name'] === 'all' ) {
            unset( $queryArgs['category_name'] );
        }
        // convert category_name array to comma separated string
        if ( array_key_exists('category_name', $queryArgs) && is_array($queryArgs['category_name'])) {
            $queryArgs['category_name'] = implode(',', $queryArgs['category_name']);
        }
        
        // query all custom posts
        $custom_posts = get_posts($queryArgs);
        $i = 0;
        
        // loop through all posts and fetch data (Tags, Categorie, gpxfiles) for the output. breaking change : posts without lat lon are no longer shown!
        foreach ($custom_posts as $post) { 
        
            // tags des posts holen.
            $tag_names = wp_get_post_tags($post->ID, array('fields' => 'names'));
            if (is_wp_error($tag_names)) {
                $tag_names = [];
            }
            // categories des posts holen. Kategorie "Uncategorized" wird ignoriert, da sie keinen Mehrwert bietet.
            $wpcat = \get_the_category( $post->ID );
            $wpcat_names = [];
            foreach ($wpcat as $onecat) {
                // skip if category is uncategorized
                if ( $onecat->name === 'Uncategorized' ) continue;
                $wpcat_names[] = $onecat->name;
            }
            // combine tags and categories to find the best match for the category filter
            $all_names = implode(',', array_merge($tag_names, $wpcat_names) );
            [$cat, $iconPng] = find_best_category_match($all_names, $this->categoryFilter ); 
            
            // extract the gpxfile from the shortcode of fotorama if any
            $content = $post->post_content;
            $gpxfilearr = $this->extractGpxFiles($content);

            // Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
            // Absätze mit [shortcodes] werden ignoriert.
            // der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
            $lat = floatval( get_post_meta($post->ID,'lat', true) );
            $lon = floatval( get_post_meta($post->ID,'lon', true) );
            $title = substr($post->post_title,0,$this->titlelength); // Länge des Titels beschränken, Anzahl Zeichen
            $excerpt = $this->generate_the_excerpt($post, $lenexcerpt);
            $featimage = get_the_post_thumbnail_url($post->ID, $size='thumbnail'); 
            $postlink = get_permalink($post->ID);
            $i++;
                
            $postArray[] = array(
                'id' => $i,   // post with several gpx-files are 2.1 , 2.2 instead of 1, 2, 3....
                'img' => $featimage,
                'title' 	=> $title,
                'category'  	=> $cat,
                //'iconpng' => $iconPng,
                'coord'   	=> [ $lat, $lon ],
                'link' 	=> $postlink,
                'excerpt' 	=> $excerpt,
                
            );

            // get the address corresponding to posts lat and lon customfield
            $lat = number_format( $lat, 6);
            $lon = number_format( $lon, 6);
            
            $geoaddresstest =  get_post_meta($post->ID,'geoadress', true) ?? '';
             if ( !empty($geoaddresstest) ) {
                $geoaddress = maybe_unserialize($geoaddresstest);	// type conversion to string 
            } else {
                // lat and lon have to be set always
                $geoaddress = get_geoaddress($post->ID, $lat, $lon); // breaking change : this Plugin does not longer set the metadata!
            }
            // sanitize geoaddress
            ['address' => $address, 'state' => $state, 'country' => $country] = $this->sanitize_geoaddress($geoaddress);

            // get geostatistics from post meta.
            $geostat = '';
            if ( $this->hasGpxStatsCustomFields ) {
                $geostat = $this->get_statistics_from_postmeta($post->ID);
            }

            $gpxcount = 1;
            if ( empty($gpxfilearr) ) $gpxfilearr = ['']; // we need this here to loop at least once, even if there is no gpx file, to show the post in the table with empty gpxfile and geostatistics
            
            foreach ($gpxfilearr as $gpxfile) {

                if ( $geostat === '' || $geostat === 'Dist: 0.0 km, Gain: 0 m, Loss: 0 m' ) {
                    $geostat = $this->get_statistics_from_gpxfile( $gpx_dir . $gpxfile ); // Keine Plausibilisierung dass Datei und custom fields zusammenpassen durchführen.
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
                );
                $gpxcount++;
                $geostat = '';
            }
            
        }
    
        return [$postArray, $data2];
    }

    /**
     * Extract the gpx-files from the content which are expected to be in the shortcode 
     * [gpxview gpxfile="file1.gpx, file2.gpx"] or [gpxview gpxfile="file1.gpx"] or [gpxview gpxfile=file1.gpx].
     * This is the "fotorama" shortcode.
     * 
     * @param string $content the page content retrieved from the database.
     * @return array<int, string>
     */
    private function extractGpxFiles(string $content): array
    {
        $result = [];

        preg_match_all('/' . get_shortcode_regex(['gpxview']) . '/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $shortcode) {
            $atts = shortcode_parse_atts($shortcode[3] ?? ''); // $shortcode[3] ist per Definition der Attribut-String.

            if (!empty($atts['gpxfile'])) {
                $files = array_map('trim', explode(',', $atts['gpxfile']));
                $result = array_merge($result, array_filter($files));
            }
        }

        return $result;
    }

    /**
     * @return array{title?: string, text?: string, link?: string, image_link?: string}
     */
    private function extractHTMLFromGeoJson( string $html ) : array {
        $result = [];

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $html = '<?xml encoding="UTF-8">' . $html;

        if (@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD) === false) {
            libxml_clear_errors();
            return $result;
        };
        
        $title = $dom->getElementsByTagName('strong')->item(0);
        if (!$title) return $result;
        $result['title'] = trim($title->textContent);
        
        if ($dom->documentElement && $dom->documentElement->nodeValue) {
            $text = trim($dom->documentElement->nodeValue);
            $text = trim(str_replace($result['title'],'' ,$text));
            $result['text'] = $text;
        } else {
            $result['text'] = '';
        }   

        $aTag = $dom->getElementsByTagName('a')->item(0);
        $result['link'] = $aTag ? $aTag->getAttribute('href') : '';

        $imgTag = $dom->getElementsByTagName('img')->item(0);
        $result['image_link'] = $imgTag ? $imgTag->getAttribute('src') : '';

        return $result;
    }

    private function is_user_editing_overview_map() : bool {
        if ( is_user_logged_in() && is_admin() ) {
            $screen = get_current_screen();

            if ( $screen && 'page' === $screen->post_type && isset($_GET['post'])) {
                $post_id = absint($_GET['post']);
                $post = get_post( $post_id);

                if ($post instanceof WP_Post && has_shortcode( $post->post_content, 'mapview' )) return true;
            }
        }
        return false;
    }

}
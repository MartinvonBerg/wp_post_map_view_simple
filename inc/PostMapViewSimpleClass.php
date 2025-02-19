<?php

namespace mvbplugins\postmapviewsimple;

use DOMDocument;
use DOMElement;

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
 * Version: 1.0.1
 *
 * @package Post-Map-View-Simple
 */

// Prevent direct access
defined('ABSPATH') or die('Are you ok?');

if (!defined('SETTINGS_FILE')) {
    define('SETTINGS_FILE', 'category_mapping.json');
}

require_once __DIR__ . '/geoaddress.php';
use function mvbplugins\helpers\get_geoaddress as get_geoaddress;

require_once __DIR__ . '/get_icon_cat.php';
use function mvbplugins\helpers\wp_postmap_get_icon_cat as wp_postmap_get_icon_cat;

interface PostMapViewSimpleInterface {
	public function show_post_map(): string;
    public function show_tourmap(): string;
}

/**
 * main shortcode function to generate the html
 * 
 * TODO: update PHPunit-Tests for PostMapViewSimple....php
 * TODO: the category mapping is inconsistent. Currently the tags are decisive. and not the WP categories )
 * 
 * @return string
 * 
 */
final class PostMapViewSimple implements PostMapViewSimpleInterface {

	public static $numberShortcodes = 0;
    // ---------- shortcode parameters ----------
    private $numberposts = 100; // is shortcode parameter. Max 1000 wg. PHP Memory Limit und max_allowed_packet bei MySQL.
    private $post_type = 'post'; // is shortcode parameter 
	private $showmap = true; // is shortcode parameter
	private $showtable = true; // is shortcode parameter
	private $category = 'all'; // is shortcode parameter
	private $headerhtml = ''; // is shortcode parameter
    private $gpxfolder = 'gpx'; // to retrieve the gpx files added to posts with fotorama
    private $lenexcerpt = 150;
    private $useWPExcerptExtraction = false;
    private $titlelength = 80;
    private $useTileServer = true;
    private $convertTilesToWebp = true;
    private $contentFilter = ['Kurzbeschreibung:', 'Tourenbeschreibung:'];
    private $tabulatorTheme = '';
    private $tablePageSize = 20;
    private $tableHeight = 0;
    private $mapHeight = ''; // shortcode parameter as string px or %
    private $mapWidth = ''; // shortcode parameter as string px or %
    private $mapAspectRatio = ''; // shortcode parameter as number (int or float)
    private $tourfolder  = '';
    private $trackwidth	 = '3';
    private $trackcolour = '#ff0000';
    private $mapselector = 'OpenStreeMap';
    private $myMarkerIcons = false;
    // ---------- end of shortcode parameters ----------

    private $plugin_url;
    private $wp_postmap_url;
	private $up_dir;
	private $gpx_dir;
	private $postArray = [];
	private $geoDataArray = [];
    private $htaccessTileServerIsOK = false;
    private $pageVarsForJs = [];
    private $m = null;
    private $chunksize = 20;
    private $tableMapMoveSelector = '';
	
	public function __construct( $attr ) {
		
		// extract and handle shortcode parameters
        //'post_status' => 'publish' ist voreingestellt.
		$attr = shortcode_atts ( array ( 
			'numberposts' => 100, 
			'post_type'   => 'post', // laut doku geht das so : array( 'post', 'page', 'movie', 'book' ) post_types können mit array abgefragt werden
			'showmap'     => 'true',
			'showtable'   => 'true',
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
            'mymarkericons'     => 'false'
		), $attr);

        $this->plugin_url = plugin_dir_url(__DIR__);
		$this->wp_postmap_url = $this->plugin_url . 'images/';
		$this->up_dir = wp_get_upload_dir()['basedir'];     // upload_dir
        $this->gpxfolder = $attr['gpxfolder'];
		$this->gpx_dir = $this->up_dir . '/' . $this->gpxfolder . '/';    // gpx_dir
		
		$this->numberposts = $attr['numberposts'];
        // fallback for great values, see above.
        if ( $this->numberposts > 1000 ) $this->numberposts = 1000;

		$this->post_type = $this->parseParameterToArray($attr['post_type']) ?? '';
		$this->showmap = $attr['showmap'] === 'true';
		$this->showtable = $attr['showtable'] === 'true';
		$this->category = $this->parseParameterToArray(strtolower( $attr['category'] ) );
		$this->headerhtml = $attr['headerhtml'];
        $this->lenexcerpt = $attr['lenexcerpt'];
        $this->useWPExcerptExtraction = $attr['usewpexcerpt'] === 'true';
        $this->titlelength = $attr['titlelength'];
        $this->useTileServer = $attr['usetileserver'] === 'true';
        $this->convertTilesToWebp = $attr['converttiles'] === 'true';
        $this->contentFilter = $this->parseParameterToArray($attr['contentfilter']);

        $this->tabulatorTheme = $attr['tabulatortheme'];
        $this->tablePageSize = $attr['tablepagesize'];
        $this->tableHeight = $attr['tableheight'];
        $this->mapHeight = $attr['mapheight'];
        $this->mapWidth = $attr['mapwidth'];
        $this->mapAspectRatio = $attr['mapaspectratio'];

        $this->m = self::$numberShortcodes;
        $this->pageVarsForJs[$this->m] = [];

        // CSS loading for tabulator does only work here and not in 'show_post_map()'.
        if ( $this->showtable ){
            $this->enqueue_tabulator_Theme($this->tabulatorTheme);
        }

        // extensions for tourmap
        $this->tourfolder = $attr['tourfolder'];
        $this->trackwidth = $attr['trackwidth'];
        $this->trackcolour = $attr['trackcolour'];
        $this->mapselector = $attr['mapselector'];
        $this->myMarkerIcons = $attr['mymarkericons'] === 'true';
    }
    
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
	
	public function show_post_map() :string {

        $this->tableMapMoveSelector = 'Stadt';
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
            foreach ($chunk_keys as $chunk_key) {
                delete_option($chunk_key);
            }
            delete_option('post_map_array_chunk_keys_' . $wpid );
        }

        // generate the output if not set in transient
        $html = get_transient( 'post_map_html_output_' . $wpid );
        $this->pageVarsForJs = get_transient( 'post_map_js_pageVars_output_' . $wpid );

        $chunk_keys = get_option('post_map_array_chunk_keys_' . $wpid, []);
        foreach ($chunk_keys as $chunk_key) {
            $this->postArray = array_merge($this->postArray, json_decode(get_option($chunk_key, []), true ) );
        }
        
        if ( !$html || !$this->postArray || !$this->pageVarsForJs || $this->is_user_editing_overview_map() ) {

            // check htaccess for tileserver only here 
            if ( $this->useTileServer) {
                $this->htaccessTileServerIsOK = $this->checkHtaccess();
                !$this->htaccessTileServerIsOK ? $this->useTileServer=false : null;
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
            
            //$this->pageVarsForJs[$this->m]['tabulatorTheme'] = $this->tabulatorTheme;
            $this->pageVarsForJs[$this->m]['tablePageSize'] = $this->tablePageSize;
            $this->pageVarsForJs[$this->m]['tableHeight'] = strval($this->tableHeight) . 'px';

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
            if ( $this->showmap ) {
                $html = $this->generate_map_html($this->postArray);
            }
            
            // generate html for table with post data
            if ( $this->showtable ){
                //$html .= '<div id="post_table_wrapper">';
                $html .= $this->generate_table_html( $this->headerhtml, $this->geoDataArray );
                //$html .= '</div>';
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
        wp_enqueue_style('wp_pmtv_main_css', $plugin_url . 'css/wp_post_map_view_simple.css', [], '1.0.1', 'all');
        wp_enqueue_script('wp_pmtv_main_js', $plugin_url . 'build/pmtv_main.js', [], '1.0.1', true);
		
		wp_localize_script('wp_pmtv_main_js', 'php_touren' , $this->postArray );
		wp_localize_script('wp_pmtv_main_js', 'g_wp_postmap_path' , array( 
            'path'  => $this->wp_postmap_url, 
            'number' => self::$numberShortcodes+1, 
            'hasTable' => $this->showtable, 
            'hasMap' => $this->showmap,
            'type' => 'standard',
            'tableMapMoveSelector' => $this->tableMapMoveSelector ));
        wp_localize_script('wp_pmtv_main_js', 'pageVarsForJs', $this->pageVarsForJs);

		// ----------------
		
		return $html;
	}

    public function show_tourmap(): string {
        if ( $this->tourfolder === '') {
            return '';
        }
        $this->tableMapMoveSelector = 'Google';
        $tourDir = $this->up_dir . '/' . $this->tourfolder;
        $tourUrl = wp_get_upload_dir()['baseurl'] . '/' . $this->tourfolder;
        $pathSettingsFile = null;

        if ( !is_dir( $tourDir ) ) {
            return '';
        }

        // get the geojson and gpx-file in the tourfolder and pass it to js.
        $jsonFiles = glob($tourDir . '/*.{json,geojson}', GLOB_BRACE);
        // check if settings file is in $tourDir, if so exclude it from $jsonFiles and return the full url to the settings file
        if ( file_exists($tourDir . DIRECTORY_SEPARATOR . SETTINGS_FILE) ) {
            //$jsonFiles = array_diff($jsonFiles, [$tourDir . DIRECTORY_SEPARATOR . SETTINGS_FILE]);
            $jsonFiles = array_filter($jsonFiles, function($item) {
                if ( !str_contains($item, SETTINGS_FILE)) {
                    return $item;
                }
            });
            $pathSettingsFile = $tourDir . DIRECTORY_SEPARATOR . SETTINGS_FILE;
        }
        $gpxFiles = glob($tourDir . '/*.gpx');
        if ( !$jsonFiles && !$gpxFiles ) {
            return '';
        }

        // ------ parse geojseon files to php_touren array
        $idCounter = 0;
        foreach ( $jsonFiles as $jsonfile ) {
            
            $json0 = file_get_contents($jsonfile);
            $json0 = mb_convert_encoding($json0, 'UTF-8', 'auto');
            if ( $json0 === false) { break; }
            $geojson = json_decode($json0, true);
                //array_walk_recursive($geojson, function (&$value) {
                //    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                //});
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
                if ($hasPopupInHTML) { 
                    // extract from maps marker pro geojson
                    $parsed = $this->extractHTMLFromGeoJson($feature['properties']['popup'] );
                    $catname = wp_postmap_get_icon_cat($feature['properties']['category'], 'category', $pathSettingsFile) ?? 'Keiner';
                    
                    $this->postArray[] = array(
                        'img' => $feature['properties']['image'] ?? $parsed['image_link'], //
                        'title' 	=> $parsed['title'] ?? $feature['properties']['name'] ?? $feature['properties']['title'] ?? '', //
                        'icon'  	=> wp_postmap_get_icon_cat($feature['properties']['category'], 'icon', $pathSettingsFile),
                        'coord'   	=> [ $feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0] ],
                        'lat' => $feature['geometry']['coordinates'][1],
                        'lon' => $feature['geometry']['coordinates'][0],
                        'link' 	=> $parsed['link'] ?? $feature['properties']['link'], //
                        'excerpt' 	=> $parsed['text'] ?? $feature['properties']['popop'] ?? $feature['properties']['text'] ?? $feature['properties']['description'] ?? '', //
                        'id' => $idCounter, //$feature['properties']['id'],
                        //'category' => $feature['properties']['category'] ?? wp_postmap_get_icon_cat('none', 'category', $pathSettingsFile),
                        'category'   => $catname,
                    );
                } else {
                    // extract data from other sources
                    $catname = wp_postmap_get_icon_cat($feature['properties']['category'], 'category', $pathSettingsFile);
                    
                    $this->postArray[] = array(
                        'img'       => $feature['properties']['image'] ?? '', //
                        'title' 	=> $feature['properties']['name'] ?? $feature['properties']['Name'] ?? $feature['properties']['title'] ?? '', //
                        'icon'  	=> wp_postmap_get_icon_cat($feature['properties']['category'], 'icon', $pathSettingsFile),
                        'coord'   	=> [ $feature['geometry']['coordinates'][1], $feature['geometry']['coordinates'][0] ],
                        'lat'       => $feature['geometry']['coordinates'][1], //
                        'lon'       => $feature['geometry']['coordinates'][0], //
                        'link' 	    => $feature['properties']['link'] ?? '', //
                        'excerpt' 	=> $feature['properties']['text'] ?? $feature['properties']['popop'] ?? $feature['properties']['description'] ?? '',
                        'id'        => $idCounter, //
                        //'category'  =>  $feature['properties']['category'],
                        'category'   => $catname, // Die 'category' wird in PHP für den Klarnamen der Kategorie in der Tabelle benutzt. 
                        //Im JS jedoch zur Auswahl des Icons UND der Klarnamens. Das ist nicht konsistent und nicht nachvollziehbar.
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

        /*
        $gpxFiles = array_map(function ($file) use ($tourUrl, $tourDir) {
            return str_replace($tourDir, $tourUrl, $file);
        }, $gpxFiles);
        */
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
            $this->htaccessTileServerIsOK = $this->checkHtaccess();
            !$this->htaccessTileServerIsOK ? $this->useTileServer=false : null;
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
        
        //$this->pageVarsForJs[$this->m]['tabulatorTheme'] = $this->tabulatorTheme;
        $this->pageVarsForJs[$this->m]['tablePageSize'] = $this->tablePageSize;
        $this->pageVarsForJs[$this->m]['tableHeight'] = strval($this->tableHeight) . 'px';
        $this->pageVarsForJs[$this->m]['geoJsonFile'] = $jsonFiles;
        //$this->pageVarsForJs[$this->m]['gpxFile'] = $gpxFiles;
        $this->pageVarsForJs[$this->m]['settingsFile'] = $pathSettingsFile;
        $this->pageVarsForJs[$this->m]['ngpxfiles'] = count($gpxFiles);
        $this->pageVarsForJs[$this->m]['tracks'] = $tracks;
        $this->pageVarsForJs[$this->m]['trackcolour'] = $this->trackcolour;
        $this->pageVarsForJs[$this->m]['trackwidth'] = $this->trackwidth;
        $this->pageVarsForJs[$this->m]['mapselector'] = $this->mapselector;
        $this->pageVarsForJs[$this->m]['myMarkerIcons'] = $this->myMarkerIcons;
       

        // generate html for map with post data 
        //$html = '<div class="pmtv_grid">';
        $html = '';

        if ( $this->showmap ) {
            $html .= $this->generate_map_html([1,1,1]);
        }

        // generate html for table with post data
        if ( $this->showtable ){
            //$html .= '<table id="post_table"></table>';
            $html .= '<div id="post_table_wrapper"><div>'; // hier 2x div, da sonst mit Headerhtml eine flex-Tabelle angezeigt wird.
            $html .= $this->generate_table_html($this->headerhtml, $this->postArray, 'tourmap');
            $html .= '</div></div>';
        }
        //$html .= '</div>';

        // --- enqueue scripts and styles ---
        $plugin_url = plugin_dir_url(__DIR__);
        wp_enqueue_style('wp_pmtv_main_css', $plugin_url . 'css/wp_post_map_view_simple.css', [], '1.0.1', 'all');
        wp_enqueue_script('wp_pmtv_main_js', $plugin_url . 'build/pmtv_main.js', [], '1.0.1', true);
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
    private function enqueue_tabulator_Theme($theme) {
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
            wp_enqueue_style('tabulator_css', $plugin_url . 'css/tabulator.min.css', [], '1.0.1', 'all');
        } else {
            wp_enqueue_style('tabulator_css', $plugin_url . 'css/' . $themes[$theme], [], '1.0.1', 'all');
        }
    }

    private function generate_table_html( $headerhtml, $data2, $caller = '') {
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

        //$table_out  .= '<button id="tablereset" type="button">Reset Filter</button>';
        $table_out  .= '<table id="post_table"><thead><tr>';
                            
        $table_out  .= '<th>Nr</th>';
        $table_out  .= '<th>Titel</th>';
        $table_out  .= '<th>Kategorie</th>';
        if ( $caller !== 'tourmap' ) {
            $table_out  .= '<th>Distanz</th>';
            $table_out  .= '<th>Aufstieg</th>';
            $table_out  .= '<th>Abstieg</th>';
            $table_out  .= '<th>Land</th>';
            $table_out  .= '<th>Region</th>';
            $table_out  .= '<th>'.$this->tableMapMoveSelector.'</th>'; // tableMapMoveSelector
        } else {
            $table_out  .= '<th>'.$this->tableMapMoveSelector.'</th>'; // tableMapMoveSelector
        }
        $table_out  .= '</tr></thead><tbody>';
        
        // generate table with post data 
        if ( $caller !== 'tourmap' ) {
            foreach ($data2 as $data) {
                
                // get geo statistics
                $geostatarr= \explode(' ', $data['geostat'] ); // gives strings of the values
                
                // define google url
                $googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
                
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
                
                // define google url
                $googleurl = 'https://www.google.com/maps/place/' . $data['lat'] . ',' . $data['lon'] . '/@' . $data['lat'] . ',' . $data['lon'] . ',9z';
                
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
    
    private function generate_map_html( $allposts ) {
        if ( count( $allposts) === 0) return '';
        
        $string = '<div class="box1">';
        $string .= '<div id="map0"></div>'; // hier wird die Karte erzeugt!
        //$string .= '<div id="map10_img">';
    
        // loop through all posts and fetch data for the output
        //foreach ($allposts as $post) {
        //    $string  .= '<a href="' . $post['featimage'] . '" data-title="'.$post['title'].'" data-icon="'. $post['icon']. '" data-geo="lat:' . $post['lat'] . ',lon:' . $post['lon'] . '" data-link="'. $post['link'] .'">' . $post['excerpt']. '</a>';
        //}
        // close divs for the map
        //$string  .= '</div>';
        $string .= '</div>';
    
        return $string;
    }
    
    private function generate_the_excerpt( $post, $length ) {
        $excerpt = $post->post_excerpt;
    
        if ( ! empty( $excerpt ) ) {
            return $excerpt;
        }
    
        if ( $this->useWPExcerptExtraction ) {
            $excerpt = apply_filters('the_content', $post->post_content);

            // Entferne alle HTML-Headings (h1 bis h6) inklusive ihrer Inhalte
            $excerpt = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/si', '', $excerpt);

            // Entferne doppelte oder überflüssige Leerzeilen und konvertiere nicht-HTML Leerzeichen
            $excerpt = preg_replace(["/[\r\n]{2,}/", '/&nbsp;/'], ["\n", ' '], $excerpt);

            // Entferne Shortcodes, Tags und trimme den Text
            $excerpt = trim(strip_tags(strip_shortcodes($excerpt)));

            // Kürze den Text auf die gewünschte Länge
            $excerpt = substr($excerpt, 0, $length) . '...';
        } else {
            $content = $post->post_content;
            $p = '';
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $content) as $line){ 
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
    
    private function get_statistics_from_gpxfile( $path_to_gpxfile ) {
        $default = '0 0 0 0 0 0 0 0';
    
        if ( \is_file( $path_to_gpxfile) ) {		
            $gpxdata = \simplexml_load_file($path_to_gpxfile);
            $geostat = (string) $gpxdata->metadata->desc;
            // geostat prüfen
            $geostatarr= \explode(' ', $geostat);
    
            if ( 'Dist:' !== $geostatarr[0] && !isset($geostatarr[1]) && !isset($geostatarr[4]) && !isset($geostatarr[7]) ) {
                //file with desc in meta but no statistics
                return $default;
                
            //sanitize the geostatistics value and array. The i18n causes problems with tabulator, so dist should be 25.3 instead of 25,3
            } else {
                //$geostatarr[1] = number_format_i18n(floatval( $geostatarr[1]), 1);
                //$geostatarr[4] = number_format_i18n(floatval( $geostatarr[4]), 1);
                //$geostatarr[7] = number_format_i18n(floatval( $geostatarr[7]), 1);
                $geostatarr[1] = $this->normalizeNumber( $geostatarr[1], 1);
                $geostatarr[4] = $this->normalizeNumber( $geostatarr[4], 0);
                $geostatarr[7] = $this->normalizeNumber( $geostatarr[7], 0);
                $geostat = implode(' ', $geostatarr);
                
                return $geostat;
            }
        }
    
        return $default;
    }

    private function normalizeNumber($numberString, $decimals = 1) {
        // Ersetze Komma durch Punkt
        $numberString = str_replace(',', '.', $numberString);
        
        // In eine Gleitkommazahl umwandeln
        $number = (float) $numberString;
        
        // Auf eine Nachkommastelle runden und als String formatieren
        return number_format($number, $decimals, '.', '');
    }
    
    private function sanitize_geoaddress( $geoaddress ) {
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
    
    private function prepare_data( $queryArgs, $gpx_dir, $lenexcerpt ) {
        $postArray = [];
        $data2 = [];
        $custom_posts = [];

        // add query for the custom fields 'lat' and 'lon' which should be numeric to the argument $queryAargs like WP_Query.
        // breaking change : posts without lat lon are no longer shown!
        
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
        
        if ( array_key_exists('category_name', $queryArgs) && $queryArgs['category_name'] === 'all' ) {
            // remove category_name from queryAargs
            unset( $queryArgs['category_name'] );
        }
        if ( array_key_exists('category_name', $queryArgs) && is_array($queryArgs['category_name'])) {
            // convert array to comma separated string
            $queryArgs['category_name'] = implode(',', $queryArgs['category_name']);
        }
        // get the different get_categories if $queryAargs['category'] is an array. Loop through the array and get the posts for each category and append them to $custom_posts
        // Voragabe Verwendung: $args['category_name'] = 'cat1,cat2'; // Komma als "OR"
        /*
        if ( array_key_exists('category_name', $queryArgs) && is_array($queryArgs['category_name'])) {
            foreach ($queryArgs['category_name'] as $category) {
                $queryArgs['category_name'] = $category;
                $custom_posts = array_merge($custom_posts, get_posts($queryArgs));
            }
        } else {
            $custom_posts = get_posts($queryArgs);
        }
        */
        $custom_posts = get_posts($queryArgs);
        $i = 0;
        
        // loop through all posts and fetch data for the output
        foreach ($custom_posts as $post) { 
            
            $lat = get_post_meta($post->ID,'lat', true);
            $lon = get_post_meta($post->ID,'lon', true);
            $gpxfilearr = [];
    
            //if ( is_numeric($lat) && is_numeric($lon) ) // breaking change : posts without lat lon are no longer shown!
            //{
            $title = substr($post->post_title,0,$this->titlelength); // Länge des Titels beschränken, Anzahl Zeichen
        
            // tags des posts holen und in string umwandeln
            $tag3 =  implode( ', ', wp_get_post_tags($post->ID, array('fields' => 'names')));
            $icon = wp_postmap_get_icon_cat($tag3, 'icon'); 
            $cat = wp_postmap_get_icon_cat($tag3, 'category'); 
            $wpcat = \get_the_category( $post->ID );
            $wpcat = count($wpcat) === 1 ? strtolower($wpcat[0]->name) : 'multiple';
            $content = $post->post_content;

            // extract the gpxfile from the shortcode fo fotoramay if any
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

            // Excerpt nur aus den Absätzen <p> herstellen! Schlüsselwörter entfernen, dürfen dann im Text nicht vorkommen
            // Absätze mit [shortcodes] werden ignoriert.
            // der html-code muss mit zeilenumbrüchen formatiert sein, sonst geht das nicht!
            $excerpt = $this->generate_the_excerpt($post, $lenexcerpt);
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
                'id' => $i,   // post with several gpx-files are 2.1 , 2.2 instead of 1, 2, 3....
                //'featimage' => $featimage,
                //'icon' => $icon,
                //'lat' => $lat,
                //'lon' => $lon,
            );

            // get the address corresponding to posts lat and lon customfield
            $lat = number_format( floatval($lat), 6);
            $lon = number_format( floatval($lon), 6);
            
            $geoaddresstest =  get_post_meta($post->ID,'geoadress', true) ?? '';
            if ( !empty($geoaddresstest) ) {
                $geoaddress = maybe_unserialize($geoaddresstest);	// type conversion to string 
            } else {
                // lat and lon have to be set always
                $geoaddress = get_geoaddress($post->ID, $lat, $lon); // breaking change : this Plugin does not longer set the metadata!
            }
            // sanitize geoaddress
            ['address' => $address, 'state' => $state, 'country' => $country] = $this->sanitize_geoaddress($geoaddress);

            $gpxcount = 1;
            if ( empty($gpxfilearr) ) $gpxfilearr = [''];

            foreach ($gpxfilearr as $gpxfile) {

                $geostat = $this->get_statistics_from_gpxfile( $gpx_dir . $gpxfile );

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
            //}
        }
    
        return [$postArray, $data2];
    }
    private function getTagStructure(\DOMNode $node, $depth =0) {
        $structure = [];
        $innerText = '';

        $tagName = $node->nodeName;
        $innerText .= $node->nodeValue;
        //$structure[] = str_repeat(' ', $depth ) . $tagName;
        $structure[] = $tagName;
        

        foreach ($node->childNodes as $childNode) {
            if ( $childNode instanceof DOMElement) {
                [$structure, $innerText] = array_merge( $structure, $this->getTagStructure($childNode, $depth+1 ));
            } 
        }
        return [$structure, $innerText];
    }

    private function getTagValues(\DOMNode $node, $depth =0) {
        $structure = [];
        $innerText = '';

        $tagName = $node->nodeName;
        $innerText .= $node->nodeValue;
        //$structure[] = str_repeat(' ', $depth ) . $tagName;
        $structure[] = $tagName;
        

        foreach ($node->childNodes as $childNode) {
            if ( $childNode instanceof DOMElement) {
                $innerText .= $this->getTagValues($childNode);
            } 
        }
        return $innerText;
    }

    private function extractTextFromHTMLwithoutStrong(\DOMNode $node) {
        $texts = [];

        if ($node instanceof DOMElement || $node instanceof DOMDocument ) {
            if ($node->nodeName === 'strong') {
                return $texts;
            }
            if(trim($node->textContent)) {
                $texts[] = trim($node->textContent);
            }
            foreach ($node->childNodes as $childNode) {
                $texts = array_merge($texts, $this->extractTextFromHTMLwithoutStrong( $childNode));
            }
        }
        return $texts;
    }

    private function extractHTMLFromGeoJson( $html ) {
        $result = [];

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $html = '<?xml encoding="UTF-8">' . $html;

        if (@$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD) === false) {
            libxml_clear_errors();
            return $result;
        };
        
        $title = $dom->getElementsByTagName('strong')->item(0);
        $result['title'] = trim($title->textContent);

        $text = trim($dom->documentElement->nodeValue);
        $text = trim(str_replace($result['title'],'' ,$text));
        $result['text'] = $text;

        $aTag = $dom->getElementsByTagName('a')->item(0);
        $result['link'] = $aTag ? $aTag->getAttribute('href') : '';

        $imgTag = $dom->getElementsByTagName('img')->item(0);
        $result['image_link'] = $imgTag ? $imgTag->getAttribute('src') : '';

        return $result;
    }

    private function decodeUnicodeEscapes( $string) {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function($matches) {
            return mb_convert_encoding('$#' . hexdec($matches[1]) .';', 'UTF-8' , 'HTML-ENTITIES' );
        }, $string);
    }

    // ---- htaccess helper -----------------
	/**
	 * Check if file .htaccess is available in the sub-folder 'leaflet_map_tiles' and try to fetch the 
	 * testfile, which will be responded with status code 302 file by the script 'tileserver.php'.
	 * 
	 * @return boolean the result of the htaccess check
	 */
	private function checkHtaccess()
	{
		// try to access testfile.webp which will be redirected to testfile.php if .htaccess is working. 
		// The file 'testfile.webp' shall not be existent!
		$path = \str_replace('inc/', '', plugins_url('/', __FILE__)) . 'leaflet_map_tiles/';

		if (\ini_get('allow_url_fopen') === '1') {
			$url = $path . 'testfile.webp';
            $context = stream_context_create( array(
                'http'=>array(
                    'timeout' => 5.0 // Das erzeugt bei Fehlern einen Verzögerung um 5 Sekunden!
                )
            ), );

			// switch off PHP error reporting and get the url.
			$ere = \error_reporting();
			\error_reporting(0);
			$test = fopen($url, 'r', false, $context);
			\error_reporting($ere);

			// check if header contains status code 302
			if ($test !== false) {
				$code = $http_response_header[0];
				$found = \strpos($code, '302');
				fclose($test);
				if ($found  !== false) return true;
			}
		}
		return false;
	}

    private function is_user_editing_overview_map() {
        if ( is_user_logged_in() && is_admin() ) {
            $screen = get_current_screen();

            if ( 'page' === $screen->post_type && isset($_GET['post'])) {
                $post_id = $_GET['post'];
                $post = get_post( $post_id);

                if ($post && has_shortcode( $post->post_content, 'mapview' )) return true;
            }
        }
        return false;
    }

}
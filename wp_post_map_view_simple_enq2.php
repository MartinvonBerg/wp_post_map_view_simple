<?php
namespace mvbplugins\postmapviewsimple;

add_action('wp_enqueue_scripts', '\mvbplugins\postmapviewsimple\wp_post_map_scripts');

function wp_post_map_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);

  //If page is using slider portfolio template then load our slider script
  // Load Styles
  //wp_enqueue_style('wp_post_map_style5', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css');
  wp_enqueue_style('leaflet_css',                 $plugin_url . 'css/leaflet.min.css');
  wp_enqueue_style('markercluster_css',           $plugin_url . 'css/MarkerCluster.min.css');
  wp_enqueue_style('markercluster_default_css',   $plugin_url . 'css/MarkerCluster.Default.min.css');
  wp_enqueue_style('bootstrap_table_css',         $plugin_url . 'css/bootstrap-table.min.css', '', '1.18.2');
  wp_enqueue_style('wp_post_map_view_simple_css', $plugin_url . 'css/wp_post_map_view_simple.min.css', '', '0.9.1');
  
 
  // Load Scripts
  wp_register_script('leaflet_js', $plugin_url . 'js/leaflet.min.js', array('jquery'), '1.7.1', true);
  wp_enqueue_script('leaflet_js');
  
  wp_enqueue_script('leaflet_markercluster_js',              $plugin_url . 'js/leaflet.markercluster.min.js', array('jquery'), '1.4.1', true);
  wp_enqueue_script('leaflet_markercluster_layersupport_js', $plugin_url . 'js/leaflet.markercluster.layersupport.min.js', array('jquery'), '2.0.1', true);
  wp_enqueue_script('bootstrap_table_js',                    $plugin_url . 'js/bootstrap-table.min.js', array('jquery'), '1.18.2', true);
  wp_enqueue_script('bootstrap_table_locale_all_js',         $plugin_url . 'js/bootstrap-table-locale-all.min.js', array('jquery'), '1.18.2', true);
  wp_enqueue_script('wp_post_map_view_simple_js',            $plugin_url . 'js/wp_post_map_view_simple.min.js', array('jquery'), '0.9.1', true);
}
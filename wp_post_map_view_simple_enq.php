<?php
namespace mvbplugins\postmapviewsimple;

add_action('wp_enqueue_scripts', '\mvbplugins\postmapviewsimple\wp_post_map_scripts');

function wp_post_map_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);

  //If page is using slider portfolio template then load our slider script
  // Load Styles
  wp_enqueue_style('wp_post_map_style1', $plugin_url . 'css/leaflet.min.css');
  wp_enqueue_style('wp_post_map_style2', $plugin_url . 'css/MarkerCluster.min.css');
  wp_enqueue_style('wp_post_map_style3', $plugin_url . 'css/MarkerCluster.Default.min.css');
  wp_enqueue_style('wp_post_map_style4', $plugin_url . 'css/wp_post_map_view_simple.min.css');
  //wp_enqueue_style('wp_post_map_style5', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css');
  wp_enqueue_style('wp_post_map_style6', 'https://unpkg.com/bootstrap-table@1.18.2/dist/bootstrap-table.min.css');
 
  // Load Scripts
  wp_enqueue_script('wp_post_map_script1', $plugin_url . 'js/leaflet.min.js', array('jquery'), '1.7.1', true);
  wp_enqueue_script('wp_post_map_script2', $plugin_url . 'js/leaflet.markercluster.min.js', array('jquery'), '1.4.1', true);
  wp_enqueue_script('wp_post_map_script3', $plugin_url . 'js/leaflet.markercluster.layersupport.min.js', array('jquery'), '2.0.1', true);
  wp_enqueue_script('wp_post_map_script5', 'https://unpkg.com/bootstrap-table@1.18.2/dist/bootstrap-table.min.js', array('jquery'), '1.18.2', true);
  wp_enqueue_script('wp_post_map_script4', $plugin_url . 'js/wp_post_map_view_simple.min.js', array('jquery'), '', true);
}

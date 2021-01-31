<?php
namespace mvbplugins\postmapviewsimple;

add_action('wp_enqueue_scripts', '\mvbplugins\postmapviewsimple\wp_post_map_scripts');

function wp_post_map_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);
  $id = get_the_ID();

  if ($id == 1820 || $id==391 || is_admin()) {
    //If page is using slider portfolio template then load our slider script
    // Load Styles
    wp_enqueue_style('wp_post_map_style1', $plugin_url . 'css/leaflet.min.css');
    wp_enqueue_style('wp_post_map_style2', $plugin_url . 'css/MarkerCluster.min.css');
    wp_enqueue_style('wp_post_map_style3', $plugin_url . 'css/MarkerCluster.Default.min.css');
    wp_enqueue_style('wp_post_map_style4', $plugin_url . 'css/wp_post_map_view_simple.min.css');

    // Load Scripts
    wp_enqueue_script('wp_post_map_script1', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.min.js', array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_post_map_script2', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/leaflet.markercluster.js', array('jquery'), '', true);
    wp_enqueue_script('wp_post_map_script3', $plugin_url . 'js/leaflet.markercluster.layersupport.min.js', array('jquery'), '', true);
    wp_enqueue_script('wp_post_map_script4', $plugin_url . 'js/wp_post_map_view_simple.min.js', array('jquery'), '', true);
  }
}
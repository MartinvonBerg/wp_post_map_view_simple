<?php
add_action('wp_enqueue_scripts', 'wp_post_map_scripts');
function wp_post_map_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);
  $id = get_the_ID();

  if ($id == 1820 || $id==391 || is_admin()) {
    //If page is using slider portfolio template then load our slider script
    // Load Styles
    wp_enqueue_style('wp_post_map_style1', $plugin_url . 'css/leaflet.css');
    wp_enqueue_style('wp_post_map_style2', $plugin_url . 'css/MarkerCluster.css');
    wp_enqueue_style('wp_post_map_style3', $plugin_url . 'css/MarkerCluster.Default.css');
    wp_enqueue_style('wp_post_map_style4', $plugin_url . 'css/wp_post_map_view_simple.css');

    // Load Scripts
    wp_enqueue_script('wp_post_map_script1', $plugin_url . 'js/leaflet.js', array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_post_map_script2', $plugin_url . 'js/leaflet.markercluster.js', array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_post_map_script3', $plugin_url . 'js/leaflet.markercluster.layersupport.js', array('jquery'), '1.10.2', true);
    wp_enqueue_script('wp_post_map_script4', $plugin_url . 'js/wp_post_map_view_simple.js', array('jquery'), '1.10.2', true);
  }
}
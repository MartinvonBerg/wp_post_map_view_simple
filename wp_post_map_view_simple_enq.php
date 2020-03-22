<?php
add_action('wp_enqueue_scripts', 'wp_post_map_scripts');
function wp_post_map_scripts()
{
  wp_reset_query();
  $plugin_url = plugins_url('/', __FILE__);

  if (!is_front_page() || !is_home()) {
    //If page is using slider portfolio template then load our slider script
    // Load Styles
    wp_enqueue_style('wp_post_map_style1', $plugin_url . 'css/wp_post_map_simple.css');
    
    // Load Scripts
    wp_enqueue_script('wp_post_map_script1', $plugin_url . 'GM_Utils/GPX2GM.js', array('jquery'), '1.10.2', true);
    
  }
}

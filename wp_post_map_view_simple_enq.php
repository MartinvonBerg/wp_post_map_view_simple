<?php

$plugin_url = plugins_url('/', __FILE__);

//If page is using slider portfolio template then load our slider script
// Load Styles
wp_enqueue_style('wp_post_map_view_simple_css', $plugin_url . 'css/wp_post_map_view_simple.min.css', '', '0.10.3');

// Load Scripts
wp_enqueue_script('wp_post_map_view_simple_js', $plugin_url . 'js/wp_post_map_view_simple.min.js', array('jquery'), '0.10.3', true);

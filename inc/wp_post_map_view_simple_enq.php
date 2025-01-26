<?php

$plugin_url = plugin_dir_url(__DIR__);

//If page is using slider portfolio template then load our slider script
// Load Styles
wp_enqueue_style('wp_post_map_view_simple_css', $plugin_url . 'css/wp_post_map_view_simple.min.css', [], '0.10.5');

// Load Scripts
wp_enqueue_script('wp_post_map_view_simple_js', $plugin_url . 'js/wp_post_map_view_simple.min.js', array('jquery'), '0.10.5', true);

<?php

$plugin_url = plugins_url('/', __FILE__);

//If page is using slider portfolio template then load our slider script
// Load Styles
wp_enqueue_style('tabulator_css', $plugin_url . 'tabulator-master/dist/css/tabulator.min.css', '', '0.5.0');

// Load Scripts
wp_enqueue_script('tabulator_js', $plugin_url . 'tabulator-master/dist/js/tabulator.min.js', array('jquery'), '0.5.0', true);
wp_enqueue_script('wp_post_map_tabulator_js', $plugin_url . 'js/wp_post_map_tabulator.min.js', array('jquery'), '0.10.3', true);

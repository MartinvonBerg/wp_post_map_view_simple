<?php

$plugin_url = plugin_dir_url(__DIR__);

//If page is using slider portfolio template then load our slider script
// Load Styles
wp_enqueue_style('leaflet_css',                 $plugin_url . 'css/leaflet.min.css');
wp_enqueue_style('markercluster_css',           $plugin_url . 'css/MarkerCluster.min.css');
wp_enqueue_style('markercluster_default_css',   $plugin_url . 'css/MarkerCluster.Default.min.css');
wp_enqueue_style('leaflet_gesture_handling_css', $plugin_url . 'css/leaflet-gesture-handling.min.css', [], '1.2.1');

// Load Scripts
wp_register_script('leaflet_js', $plugin_url . 'js/leaflet.min.js', array('jquery'), '1.7.1', true);
wp_enqueue_script( 'leaflet_js');

wp_enqueue_script('leaflet_markercluster_js',              $plugin_url . 'js/leaflet.markercluster.min.js', array('jquery'), '1.4.1', true);
wp_enqueue_script('leaflet_markercluster_layersupport_js', $plugin_url . 'js/leaflet.markercluster.layersupport.min.js', array('jquery'), '2.0.1', true);
wp_enqueue_script('leaflet_gesture_handling_js',           $plugin_url . 'js/leaflet-gesture-handling.min.js', array('jquery'), '1.2.1', true);
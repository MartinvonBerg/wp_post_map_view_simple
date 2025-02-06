<?php

/**
 *
 * Description:       uninstall script - PostMapTableView -automatically run by WP
 * Author:            Martin von Berg
 * Author URI:        https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */
// delete options in wp_options in the database at uninstall of the plugin

// if uninstall.php is not called by WordPress, die
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    die;
}

$chunk_keys = get_option('post_map_array_chunk_keys', []);

foreach ($chunk_keys as $chunk_key) {
    delete_option($chunk_key);
}
delete_option('post_map_array_chunk_keys');





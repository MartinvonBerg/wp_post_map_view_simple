<?php

namespace mvbplugins\helpers;

/**
 * Helper functions to get icons and categories from a json file and order to a requested icon or category.
 *
 * Description: This file contains the functions to get icons and categories from a json file and order to a requested icon or category .
 *
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Author: Martin von Berg
 * Author URI: https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * License: GPL-2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package GetIconCat
 */

// Prevent direct access
defined('ABSPATH') or die('Are you ok?');

/**
 * Zuordnung eines Icons für die Tags eines Posts.
 *
 * @param string $arraytagnames Die Tags des Posts als String.
 * @return string Der Icon-Name als String.
 */
function wp_postmap_get_icon($arraytagnames) {
    $data = wp_postmap_load_category_mapping();
    $mapping = $data['mapping'];
    $default = $data['default']['icon'];

    foreach ($mapping as $key => $details) {
        if (str_contains($key, '&&')) {
            [$part1, $part2] = explode('&&', $key);
            if (stristr($arraytagnames, $part1) !== false && stristr($arraytagnames, $part2) !== false) {
                return $details['icon'];
            }
        } elseif (stristr($arraytagnames, $key) !== false) {
            return $details['icon'];
        }
    }

    return $default;
}

/**
 * Zuordnung eines sprechenden Kategorienamens zu den Tags eines Posts.
 *
 * @param string $arraytagnames Die Tags des Posts als String.
 * @return string Die Kategorie des Posts als String.
 */
function wp_postmap_get_cat($arraytagnames) {
    $data = wp_postmap_load_category_mapping();
    $mapping = $data['mapping'];
    $default = $data['default']['category'];

    foreach ($mapping as $key => $details) {
        if (str_contains($key, '&&')) {
            [$part1, $part2] = explode('&&', $key);
            if (stristr($arraytagnames, $part1) !== false && stristr($arraytagnames, $part2) !== false) {
                return $details['category'];
            }
        } elseif (stristr($arraytagnames, $key) !== false) {
            return $details['category'];
        }
    }

    return $default;
}

/**
 * Lädt die category_mapping.json und gibt sie als Array zurück.
 *
 * @return array Das Mapping aus der JSON-Datei.
 */
function wp_postmap_load_category_mapping( $file = null ) {
    $default = [
        'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'campingcar.png'],
        'mapping' => []
    ];

    if ( $file === null ) {
        $mapping_file = plugin_dir_path(__DIR__) . 'settings/category_mapping.json';
    } else {
        $mapping_file = $file;
    }
    
    if (!file_exists($mapping_file)) {
        return $default;
    }

    $data = json_decode(file_get_contents($mapping_file), true);
    if (!is_array($data) || !isset($data['mapping']) || !isset($data['default'])) {
        return $default;
    }

    return $data;
}

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

/**
 * Zuordnung eines Icons oder Kategorienamens für die Tags eines Posts.
 *
 * @param string $arraytagnames Die Tags des Posts als String.
 * @param string $returnKey Der Schlüssel des zurückzugebenden Wertes.
 * @return string Der Icon-Name als String.
 */
function wp_postmap_get_icon_cat($arraytagnames, $returnKey) {
    $data = wp_postmap_load_category_mapping();
    $mapping = $data['mapping'];
    $default = $data['default'][$returnKey];

    // Schlagwörter bereinigen und normalisieren
    $searchWords = array_map('\mvbplugins\helpers\normalize_string', explode(',', $arraytagnames));

    foreach ($mapping as $details) {
        $normalizedCategory = normalize_string($details['category']);

        // Prüfen, ob mindestens ein Suchwort als Teilstring in einer normalisierten Kategorie vorkommt
        foreach ($searchWords as $word) {
            if (!empty($word) && str_contains($normalizedCategory, $word)) {
                return $details[$returnKey]; // Erste Übereinstimmung zurückgeben
            }
        }
    }

    return $default;
}

// Funktion zur Normalisierung: Entfernt alle Nicht-Buchstaben und wandelt in Kleinbuchstaben um
function normalize_string($string) {
    return strtolower(preg_replace('/[^\p{L}]+/u', '', $string));
}


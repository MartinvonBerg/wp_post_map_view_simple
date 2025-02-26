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
    // set the default value according to the settings file
    $default = [
        'default' => ['category' => 'Reisebericht', 'icon' => 'travel', 'icon-png' => 'travel.png'],
        'mapping' => []
    ];

    if ( $file === null ) {
        $mapping_file = plugin_dir_path(__DIR__) . 'settings/' . SETTINGS_FILE;
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

function find_best_category_match($keywords, $stopwords, $json_file=null) {
    // JSON-Datei mit settings einlesen
    $json_data = \mvbplugins\helpers\wp_postmap_load_category_mapping($json_file);

    if (!$json_data || !isset($json_data['mapping'])) {
        return [$json_data['default']['category'], $json_data['default']['icon'], $json_data['default']['icon-png'] ] ?? ['','',''];
    }

    // Kategorien aus JSON extrahieren
    $categories = array_column($json_data['mapping'], 'category');
    
    // stopwords strtolower
    $stopwords = array_map('strtolower', $stopwords);
    // Keywords verarbeiten: Kleinbuchstaben, Stopwords entfernen, Bindestriche ersetzen, trimmen, in ein Array splitten
    $keyword_list = array_unique(array_filter(array_map(function ($word) use ($stopwords) {
        return trim(str_replace('-', ' ', str_replace($stopwords, '', strtolower($word))));
    }, explode(',', $keywords))));
    
    $best_match = null;
    $best_score = 0;
    $match_count = 0;

    // Fuzzy Matching Schwellenwert
    $levenshtein_threshold = 3;

    foreach ($categories as $category) {
        // Kategorie ebenfalls bereinigen
        $clean_category = strtolower(str_replace('-', ' ', $category));

        // Prüfe, wie viele Keywords mit der Kategorie übereinstimmen oder ähnlich sind
        $score = 0;

        foreach ($keyword_list as $keyword) {
            if ($keyword === $clean_category) {
                $score += 3; // Perfekter Treffer
            } elseif (levenshtein($keyword, $clean_category) <= $levenshtein_threshold) {
                $score += 1; // Fuzzy-Match Treffer
            }
        }

        // Besten Treffer speichern
        if ($score > $best_score) {
            $best_score = $score;
            $best_match = $category;
            $match_count = 1;
        } elseif ($score === $best_score && $score > 0) {
            $match_count++;
        }
    }

    // Falls mehr als eine beste Übereinstimmung → Default zurückgeben
    if ($match_count > 1 || $match_count === 0) {
        return [$json_data['default']['category'], $json_data['default']['icon'], $json_data['default']['icon-png'] ];
    } else {
        // Wenn genau eine beste Übereinstimmung gefunden wurde, zurückgeben
        $key = array_search($best_match, $categories);
        return [$best_match, $json_data['mapping'][$key]['icon'], $json_data['mapping'][$key]['icon-png']];
    }
}

/**
 * Zuordnung eines Icons oder Kategorienamens für die Tags eines Posts.
 *
 * @param string $arraytagnames Die Tags des Posts als String.
 * @param string $returnKey Der Schlüssel des zurückzugebenden Wertes.
 * @return string Der Icon-Name als String.
 */
function wp_postmap_get_icon_cat($arraytagnames, $returnKey, $file = null) {
    $data = wp_postmap_load_category_mapping($file);
    $mapping = $data['mapping'];
    $default = $data['default'][$returnKey];

    // Schlagwörter bereinigen und normalisieren
    $searchWords = array_map('\mvbplugins\helpers\normalize_string', explode(',', $arraytagnames));

    foreach ($mapping as $singleEntry) {
        $normalizedCategory = normalize_string($singleEntry['category']);

        // Prüfen, ob mindestens ein Suchwort als Teilstring in einer normalisierten Kategorie vorkommt
        foreach ($searchWords as $word) {
            if (!empty($word) && str_contains($normalizedCategory, $word)) {
                return $singleEntry[$returnKey]; // Erste Übereinstimmung zurückgeben
            }
        }
    }

    return $default;
}

// Funktion zur Normalisierung: Entfernt alle Nicht-Buchstaben und wandelt in Kleinbuchstaben um
function normalize_string($string) {
    return strtolower(preg_replace('/[^\p{L}]+/u', '', $string));
}


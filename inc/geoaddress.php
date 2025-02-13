<?php

namespace mvbplugins\helpers;

/**
 * Helper functions to get the geoaddress from the nominatim API.
 *
 * Description: This file contains the functions to get the geoaddress from the nominatim API.
 *
 * Requires PHP: 8.0
 * Requires at least: 6.0
 * Author: Martin von Berg
 * Author URI: https://www.berg-reise-foto.de/software-wordpress-lightroom-plugins/wordpress-plugins-fotos-und-gpx/
 * License: GPL-2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package Geoaddress
 */

// Prevent direct access
defined('ABSPATH') or die('Are you ok?');

/**
 * Holt die Geo-Adresse für Koordinaten von Nominatim (OpenStreetMap) und bereitet Ausgaben für Übersetzungen vor.
 *
 * @param int $postid Die ID des aktuellen Posts.
 * @param string $lat Die Breite (Latitude), wie im benutzerdefinierten Feld gespeichert.
 * @param string $lon Die Länge (Longitude), wie im benutzerdefinierten Feld gespeichert.
 * @return string Die serialisierte Geo-Adresse oder ein Fallback-Wert.
 */
function get_geoaddress($postid, $lat, $lon) {
    // API-URL mit Parametern
    $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
        'lat' => $lat,
        'lon' => $lon,
        'format' => 'json',
        'zoom' => 10,
        'accept-language' => 'de',
    ]);

    // Dynamische Einstellungen aus WordPress
    $admin_email = get_option('admin_email'); // Administrator-E-Mail
    $site_url = home_url(); // URL der Website

    // HTTP-Kontext-Optionen mit dynamischem Header
    $opts = [
        'http' => [
            'method'  => "GET",
            'header'  => [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36",
                "Referer: $site_url",
                "Contact: $admin_email",
            ],
            'timeout' => 10, // Timeout in Sekunden
        ]
    ];

    $context = stream_context_create($opts);

    try {
        // API-Abfrage
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new \Exception(__('Nominatim API nicht erreichbar.', 'postmapviewsimple'));
        }

        $geojson = json_decode($response, true);

        // Validierung der API-Antwort
        if (!isset($geojson['address']) || !is_array($geojson['address'])) {
            throw new \Exception(__('Ungültige API-Antwort.', 'postmapviewsimple'));
        }

        // Adresse serialisieren und speichern
        $geoadressfield = maybe_serialize($geojson['address']);
        update_post_meta($postid, 'geoadress', $geoadressfield); // Post-Meta aktualisieren

        return $geoadressfield;
    } catch (\Exception $e) {
        // Fehlerprotokollierung
        error_log(__('Fehler bei der Nominatim-Abfrage: ', 'postmapviewsimple') . $e->getMessage());

        // Fehlermeldung an den Administrator senden
        wp_mail(
            $admin_email,
            __('Fehler bei der Geo-API-Abfrage', 'postmapviewsimple'),
            __('Es gab ein Problem bei der Abfrage der Geo-API für Post-ID: ', 'postmapviewsimple') . $postid . "\n\n" . $e->getMessage()
        );

        // Fallback: Standard-Adresse
        $fallback_address = [
            'country' => __('Unbekannt', 'postmapviewsimple'),
            'state'   => __('Unbekannt', 'postmapviewsimple'),
            'city'    => __('Unbekannt', 'postmapviewsimple'),
            'village' => __('Unbekannt', 'postmapviewsimple'),
        ];

        $geoadressfield = maybe_serialize($fallback_address);
        //update_post_meta($postid, 'geoadress', $geoadressfield); // Fallback in Post-Meta speichern

        return $geoadressfield;
    }
}

/**
 * sanitize the geoaddress: set undefined array-keys to an empty string ''
 *
 * @param array $geoaddress geoaddress to sanitize
 * @return array $geoaddress sanitized geoaddress
 */
function sanitize_geoaddress($geoaddress) {
    $keys = ['village', 'city', 'town', 'municipality', 'country', 'state', 'county', 'state_district'];

    foreach ($keys as $key) {
        if (!isset($geoaddress[$key])) {
            $geoaddress[$key] = '';
        }
    }

    return $geoaddress;
}
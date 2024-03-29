=== wp-post-map-view-simple ===
Contributors: Martin von Berg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CQA6XZ7LUMBJQ
Tags: GPX, leaflet, Track, map, thumbnail, image, responsive, overview, marker, cluster
Requires at least: 5.0
Tested up to: 6.0
Requires PHP: 7.4
License: GPLv2
Stable Tag: 0.10.5
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Here is a short description of the plugin. This should be no more. The following text is mainly in German.

== Description ==

Anzeige aller Wordpress-Posts mit GPX-Daten (lat,lon) in den Custom-Fields auf einer OpenStreetMap-Karte. Die Posts werden nach Kategorien in den Tags 
(Schlagwörtern) eingeteilt (Auswahl in der Karte und spezifische Icons). Zusätzlich wird eine Tabelle mit allen Posts angezeigt.
Shortcode: [mapview] Fertig. Keine Optionen!
Hinweis: Geo Mashup funktioniert sehr ähnlich und bietet mehr Optionen!


== Screenshots ==

There are no screenshots yet, see : https://www.berg-reise-foto.de/uebersichtskarte/ for an example of the plugin.


1.Post-Vorbereitung
    1.1. Vorbereitung
    - Custom-Fields: 
        Lat : Lattitude eintragen (Punkt als Dezimaltrenner verwenden!)
        Lon : Longitude eintragen
        Posts mit (0,0) oder ungültigen oder keinen Daten werden ignoriert
        Übersicht, ob alle Custom-Fields gesetzt sind: am besten mit WP-Plugin Admin Columns und spezifischer Ansicht.
    
    - Kategorien in Posts: Untesrchieden wird:
    Wort im Schlagwort | Kategorie | Icon
        Trekk ->        hiking        hiking2.png
        bike hike ->    bike-hike     mountainbiking-3.png
        Radfahren ->    cycling       cycling.png
        MTB ->          MTB           MTB.png
        Wander ->       hiking        hiking2.png
        Bergtour ->     mountain      peak2.png
        skitour ->      skiing        skiing.png
        reisebericht -> travel        campingcar.png
        Paddeln    ->   Paddeln       kayaking2.png
        default ->      travel        campingcar.png

    Definiert wird das in einem PHP-Array:
    $allIcons = array(
		array ('hiking2.png', 'hiking', 'Wandern'),
		array ('mountainbiking-3.png', 'bike-hike', 'Bike-Hike'),
		array ('cycling.png', 'cycling', 'Radfahren'),
		array ('MTB.png', 'MTB', 'MTB'),
		array ('peak2.png', 'mountain', 'Bergtour'),
		array ('skiing.png', 'skiing', 'Skitour'),
		array ('kayaking2.png','kayaking','Paddeln'),
		array ('campingcar.png', 'travel', 'Reisebericht'),
	);

    1.2. Karte erzeugen
    - Shortcode [mapview] auf Seite einbinden. Fertig.
    - Folgende Parameter stehen zur Verfügung
        <Parameter>      <Default-Wert>
        'numberposts' => 100,  // Anzahl der anzuzeigenden Posts
        'post_type'   => 'post',  // Verwende Posts zur Anzeige, keine Alternative möglich. Am besten auf post lassen.
        'showmap'     => 'true',   // Zeige die Karte
        'showtable'   => 'true',    // Zeige die Tabelle
        'category'    => 'all',   // Auswahl der Kategorien, am besten auf 'all' lassen
        'headerhtml'  => '',       // Alternativer html-Code für den Header der Tabelle 
    

    1.3. Randbedingungen und Einstellungen
    - Titel wird auf 80 Zeichen gekürzt (Zeile 53 im ...php)
    - Excerpt wird auf 50 Worte gekürzt (Funktion 'wp_example_excerpt_length' im ...php) 
    - Icons müssen in <plugin-Pfad>./images liegen. Der Pfad wird als globale Variable an JS übergeben.
    - Anzahl der Posts ist auf 100 begrenzt
    - Featured Image muss vorhanden sein. Keine Fehlerbehandlung, falls nicht vorhanden!
    - Bezeichnungen der Kategorien sind fix und in den verschiedenen Plugin-Dateien aufeinander abgestimmt. Änderungen müssen in allen Dateien durchgeführt werden.
    - Icongröße ist fix im *.js mit icnh und icnw eingestellt (32px)
    - Für die Karte sind vier Base-Layer (Karten) auswählbar.
    - Die Variablen für MarkerCluster sind direkt in leaflet.markercluster.js eingestellt
    - Das responsive Verhalten ist mit den jQuery-Funktionen am Ende im JS eingestellt (ab Zeile 334)
    - Das Design wird direkt im CSS eingestellt. Kartenhöhe nicht vergessen!

    1.5 TODO
    Allgemeine Optimierung, mehr als Wunsch. 

== Installation ==

1. Verzeichnis mit Plugin zippen -> *.zip
2. Plugin installieren mit den Standard WP-Methoden (Upload zip im Admin-Backend). 
   Falls bereits installiert, Vorversion löschen! Es werden keine anderen Verzeichnisse gelöscht. Einträge in der WP-SQL-Datenbank werden nicht verändert.
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Fertig. Keine weiteren Settings nötig

== Frequently Asked Questions ==

There are no FAQs just yet.

== Changelog ==

= 0.3.0 =
*   First release: 1.04.2020

= 0.4.0 =
*   14.12.2020
* namespace introduced

= 0.5.0 =
*   28.12.2020
* speed improvement: the excerpt function was definitely too slow. New excerpt based on abstracts <p> only!
* function for tags to string replaced with anonymous function, maybe to improve speed

= 0.6.0 =
*   09.01.2021
* mobile improvement: Don't show zoom-control for mobile versions

= 0.7.0 =
*   17.02.2021
* PHP 8 compatibility check with phpcs. OK. No change necessary.
* replace 'jQuery' with $
* run js on page only if div 'map10_img' is on page

= 0.8.0 =
*   30.03.2021
* added a table to show all posts under the map. It is not configurable. Used bootstrap-table for that.
* Full functionality is not given with theme Photo perfect pro.
* handle for leaflet fixed to load it only once.
* loaded all files locally. pass variable "g_wp_postmap_path" by localize_scripts to javascript
* introduced a transient variable to store the generated html output. is re-generated if a new post is published

= 0.8.1 =
*   08.11.2021
*   Added 'tab: false' for Safari to open the pop-ups of single tours.

= 0.9.0 =
*   16.11.2021
*   Rework for flexible icons and groups provided by php script. tours are now passed to JS as variable.

= 0.9.1 =
*   18.11.2021
*   introduced and tested transients for PHP -> JS variable $postArray

= 0.10.0 =
*   30.11.2021
*   switched to tabulator.info

= 0.10.1 =
*   13.01.2022
*   CSS changes for new theme. Readme update

= 0.10.2 =
*   16.01.2022
*   Minor PHP bugfix.

= 0.10.3 =
*   30.01.2022
*   Replacement of bike-hike-map by CycleOSM

= 0.10.4 =
*   02.02.2022
*   Minor PHP Warning correction to satisfy PHPStan (level 5) and QueryMonitor

= 0.10.5=
*   14.05.0222
* Test with WordPress 6.0.

== Upgrade Notice ==

There is no need to upgrade just yet.

== Credits ==
This plugin uses the great work from:

- leaflet, see: https://leafletjs.com/
- leaflet markercluster: https://github.com/Leaflet/Leaflet.markercluster
- wordpress for coding hints: https://de.wordpress.org/
- map-icons generated with: https://mapicons.mapsmarker.com/

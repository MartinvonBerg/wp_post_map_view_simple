=== wp-post-map-view-simple ===
Contributors: Martin von Berg
Donate link: http://www.mvb1.de
Tags: GPX, leaflet, Track, map, thumbnail, image, responsive, overview, marker, cluster
Requires at least: 5.0
Tested up to: 5.4.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Here is a short description of the plugin. This should be no more

== Description ==

Anzeige aller Posts mit GPX-Daten (lat,lon) in den Custom-Fields auf einer Karte. Die Posts werden nach Kategorien in den Tags 
(Schlagwörtern) eingeteilt (Auswahl in der Karte und spezifische Icons)
Shortcode: [mapview] Fertig. Keine Optionen!


1.Post-Vorbereitung
    1.1. Vorbereitung
    - Custom-Fields: 
        Lat : Lattitude eintragen
        Lon : Longitude eintragen
        Posts mit (0,0) oder ungültig oder keinen Daten werden ignoriert
        Übersicht, ob alle Daten gesetzt: am besten mit WP-Plugin Admin Columns und spezifischer Ansicht
    
    - Kategorien in Posts: Unteschieden wird:
    Wort im Schlagwort | Kategorie | Icon
        Trekk ->        hiking        hiking2.png
        bike hike ->    bike-hike     mountainbiking-3.png
        Radfahren ->    cycling       cycling.png
        MTB ->          MTB           MTB.png
        Wander ->       hiking        hiking2.png
        Bergtour ->     mountain      peak2.png
        skitour ->      skiing        skiing.png
        reisebericht -> travel        campingcar.png
        default ->      travel        campingcar.png

    1.2. Karte erzeugen
    - Shortcode [mapview] auf Seite einbinden. Fertig.

    1.3. Randbedingungen und Einstellungen
    - Titel wird auf 80 Zeichen gekürzt (Zeile 53 im ...php)
    - Excerpt wird auf 50 Worte gekürzt (Funktion 'wp_example_excerpt_length' im ...php) 
    - Icons müssen in <plugin-Pfad>./images liegen. Der Pfad wird als globale Variable an JS übergeben.
    - Anzahl der Posts ist auf 100 begrenzt
    - Featured Image muss vorhanden sein. Keine Fehlerbehandlung!
    - Kategorien sind fix und in den verschiedenen Dateien aufeinander abgestimmt. Änderungen müssen in allen Dateien durchgeführt werden.
    - Icongröße ist fix im *.js mit icnh und icnw eingestellt (32px)
    - Für die Karte sind vier Base-Layer (Karten) auswählbar.
    - Die Variablen für MarkerCluster sind direkt in leaflet.markercluster.js eingestellt
    - Das responsive Verhalten ist mit den jQuery-Funktionen am Ende im JS eingestellt (ab Zeile 334)
    - Das Design wird direkt im CSS eingestellt. Kartenhöhe nicht vergessen!

    1.5 TODO
    JS zumindest so flexibel gestalten, dass sich die MarkerCluster an den Kategorien ausrichten. Die Icons müssen dann wie die Kategorie heissen.
    Dann wird das JS durch PHP und den HTML-Code gesteuert. 

== Installation ==

1. Verzeichnis mit Plugin zippen -> *.zip
2. Plugin installieren mit den Standard WP-Methoden (Upload zip im Admin-Backend). 
   Falls bereits installiert, Vorversion löschen! Es werden keine anderen Verzeichnisse gelöscht.
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Fertig. Keine weiteren Settings nötig

== Frequently Asked Questions ==

There are no FAQ just yet.

== Changelog ==

= 0.3.0 =
*   First release: 1.04.2020

== Upgrade Notice ==

There is no need to upgrade just yet.

== Screenshots ==

There are no screenshots yet, or see : https://www.mvb1.de/uebersichtskarte/
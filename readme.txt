=== wp-fotorama-gpxviewer ===
Contributors: Martin von Berg
Donate link: http://www.mvb1.de
Tags: Slider, Gallery, GPX, leaflet, Track, chart, map, thumbnail, image, fullscreen, responsive
Requires at least: 5.0
Tested up to: 5.2.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Here is a short description of the plugin. This should be no more

== Description ==

Anzeige Bildergallerie als Fotorama-Image-Slider kombiniert mit
Anzeige eines oder mehrerer GPX-Tracks auf einer Leaflet-Karte unter dem Slider.

1.Slider-Bedienung (Fotorama)
    1.1. Vorbereitung
    - OPTIONAL: EXIF-Daten in den Bildern anpassen
        Mit dem Skript "copy_exif_data.ps1" die Objektiv-Informationen (EXIF: Lens ID) in die Make-Info (EXIF: Make) kopieren.
        Wenn der Schritt nicht durchgeführt wird, wird nur das Kameramodell angegeben (EXIF: Model)
    - OPTIONAL: Thubnails generieren und Bilder verkleinern
        Zu den Bildern mit dem Tool "ImageResizer für Windows" die Bilder verkleinern und thumbnails generieren. Thumbs sollten eine 
        minimale Seitenlänge von 64 px haben.
        Alternativ: Thumbs im Ordner ./thumbs ablegen oder im Stammordner der Bilder
        Wenn keine Thumbs vorhanden, werden die großen Bilder genommen und skaliert, dann dauert das Laden aber länger.

    1.2. Bilder hochladen
    - Bilder und Thubnails in einem beliebigen Ordner unter  ./wp-content/uploads/ hochladen   
        z.B. unter:  /wp-content/uploads/Alben_Website/Friaul-Cacciatore -> Fertig!
    - Die Bilder werden in der WP-Medien-Anzeige nicht angezeigt.

    1.3. Slider bzw. Bildergallerie aktivieren
    Im post den Shortcode [gpxview imgpath="Alben_Website/Friaul-Cacciatore"] angeben -> Fertig
    Dabei keine (Back)-Slashes ('/' oder '\') am Anfang oder Ende angeben!
    Es wird nur die Bildergallerie angezeigt!   
    Achtung: Bilder OHNE GPS-Daten im EXIF werden NICHT angezeigt. Nicht gesetzte EXIF-Daten werden durch "--" ersetzt!  

    1.4 Fotorama Optionen
    Für SEO kann für das erste Bild ein 'alt"-Tag definiert werden, das im HTML-Quellcode angzeigt wird.
        Option: alttext="...." Default : 'Fotorama Bildergallerie als Javascript-Slider'    
    Die Fotorama Optionen sind fix eingestellt in dieser Zeile:
  		$string  .= '<div id="fotorama" class="fotorama" data-auto="false" data-width="100%" data-fit="contain" data-ratio="1.5" data-nav="thumbs" data-allowfullscreen="native" data-keyboard="true" data-hash="true">';
    und teilweise auch in der Datei wp_gpxviewer_style.css für das Styling! Änderungen direkt im *.css vornehmen oder in der o.g. Codezeile
    Weitere Optionen zu Fotorama finden sich unter: https://fotorama.io/docs/4/options/ (oder im abgelegten Fotorama....mthml) 
    oder in fotorama.dev.js ab Zeile 880 unter "OPTIONS = {..."
    
    1.5 TODO
    Umschaltung "data-fit" zwischen Inline-Anzeige und fullscreen-Anzeige: Keine Kontaktdaten vom Entwickler verfügbar und Debugging mit Chrome geht nicht. 

2. GPXVIEWER-Bedienung:    
    1.1. Vorbereitung
    - OPTIONAL: GPX-Tracks verkleinern: mit Batch-Datei: FOR %%i In (*.gpx) do GPSBabel -i gpx -f %%~i -x simplify,count=100 -o GPX -F %%~ni.gpx (Datei: GPS_Babel_GPX_Reducet.bat)
      Anzahl der Punkte unter count
      TODO: Alle Daten, außer lat, long, ele aus dem GPX entfernen

    1.2. GPX-Tracks hochladen
    - Tracks im Ordner  ./wp-content/uploads/gpx hochladen.
        Der Ordner "gpx" kann relativ zu "./wp-content/uploads/" geändert werden mit [gpxview ...gpxpath="<Pfad>"]. 
        Dabei keine (Back)-Slashes ('/' oder '\') am Anfang oder Ende angeben!

    1.3. Karte mit Track aktivieren
    - Shortcode im Post einfügen: [gpxview gpxfile="<Trackname>.gpx"]   Default: "test.gpx" 
        Ohne Angabe des Ordners wird der Standard-Ordner ./wp-content/uploads/gpx/ verwendet.
        Angaben mehrer Dateien ist möglich. Verwendung einer Kommma-getrennten Liste z.B.: gpxfile="Malle.gpx, Malle2.gpx, Malle3.gpx"
        Die Angabe der Erweiterung *.gpx ist immer nötig.
        
    1.4 GPX-Viewer-Optionen: Ohne Angabe werden die Default-Werte verwendet
        Höhe der Karte: mapheight=300. Default : 450. Keine Anführungszeichen!
        HÖhe des Charts: chartheight=100. Default : 150. Keine Anführungszeichen!
        Download der GPX-Datei anzeigen: dload='no'. Default : 'yes', Anzeige nur, wenn genau EINE GPX-Datei angegeben wird, sonst nicht!

        Weitere Optionen:
        Im Quellcode: OPENTOPO, style="...
        	$string  .= '<div id=map0 class="map gpxview:' . $gpxfile . ':OPENTOPO" style="width:100%;height:' . $mapheight . 'px"></div>';
		    $string  .= '<div id="map0_profiles" style="width:100%;height:' . $chartheight . 'px"><div id="map0_hp" class="map" style="width:100%;height:' . $chartheight . 'px"></div></div>';
		CSS:
            Styling teilweise auch im wp_gpxviewer_style.css! Änderungen direkt im *.css vornehmen
        Weitere:
            siehe unter: https://www.j-berkemeier.de/GPXViewer/#Zus%C3%A4tzliche
        Javascript-Dateien:
            Einstellungen direkt im JS-Code sollten alle mit // Martin markiert sein! Wenn nicht dann Vergleich mit BeyondCompare oder VSC im Vergleich zum Original.
            Die meisten Einstellungen finden sich im GPX2GM_Defs.js, ab Zeile 111 unter "JB.GPX2GM.setparameters = function() { ..."   
        Achtung:
            Die Dateien unter GPX2GM dürfen nicht minimiert werden (minify)! Sonst geht das Tool nicht mehr. Daher kann in WP das Plugin 
            "Autoptimize" (https://wordpress.org/plugins/autoptimize/) NICHT benutzt werden.
            Im Plugin "Asset Clean up" (https://wordpress.org/plugins/wp-asset-clean-up/ ) muss das Plugin explizit ausgenommen werden.
                /smrtzl/plugins/wp-fotorama-gpxviewer/(.*?).css
                /smrtzl/plugins/wp-fotorama-gpxviewer/(.*?).js
            eintragen in der Liste bei den Ausnahmen.
            Andere Plugins wurden nicht getestet.   

    1.5. TODO & Bugs
        - Anzeige Infofenster mit Trackdaten von Anfang an, nicht erst bei MousOver-Track.
        - Zentrieren der Karte auf den aktiven Bilder-Marker.
        - Angabe Info im Impressum. 
        - BUG: wenn die Wegpunkte einmal de- / re-aktiviert werden, folgt der Kreis nicht mehr dem Bild! Die Seite muss dann neu geladen werden! 

    3. Komination Fotorama + GPXViewer:
        3.1. Bedienung wie oben mit gemeinsamer Verwendung der Optionen 
            Das erzeugt den Slider oben und die Karte unten. Der Marker auf der Karte folgt dem im Slider angezeigten Bild.
            Achtung: Bilder OHNE GPS-Daten im EXIF werden NICHT angezeigt.       
		
		
		
== Installation ==

1. Verzeichnis mit Plugin zippen -> *.zip
2. Plugin installieren mit den Standard WP-Methoden (Upload zip im Admin-Backend). 
   Falls bereits installiert, Vorversion löschen! Es werden keine anderen Verzeichnisse gelöscht.
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Fertig. Keine weiteren Settings nötig

== Frequently Asked Questions ==

There are no FAQ just yet.

== Changelog ==

= 0.4.0 =
*   First release: 18.03.2020

== Upgrade Notice ==

There is no need to upgrade just yet.

== Screenshots ==

There are no screenshots yet, or see : www.mvb1.de
=== Post-Map-Table-View ===
Contributors: Martin von Berg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CQA6XZ7LUMBJQ
Tags: leaflet, map, markercluster, table, post-map, travel-map
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
License: GPLv2
Stable Tag: 1.3.2
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==

This plugin displays all WordPress posts or pages containing GPX data (lat, lon) stored in custom fields on an OpenStreetMap map. Posts are categorized using tags, allowing filtering and custom icons. Additionally, a table with all posts is displayed which may be used to filter the shown Posts.
Alternatively a descriptive JSON-File in a separate folder may be used to show a map with all destinations you travelled or any other POI you defined in the JSON. 
There is no Admin-Panel to control the plugin. Everything is defined by shortcoder parameters oder settings in JSON-Files.

== Shortcodes: == 
[mapview] — Ready to use. Multiple options available, see table below! Use only once per Page or Post! The generated HTML is stored in transients for improved performance.

[tourmap tourfolder=<folder relative to upload directory with json-file>] Will show a nice map and table with all markers you defined in the JSON. This shortocdes requires work with JSON-Files and uploading these with FileZilla. If you don't feel comfortable with that this Plugins is not for you.

The Map Tiles for Leaflet may be stored locally on your server!
NOTE: Both shortcodes might be used once only per page or post!

== Screenshots ==

There are no screenshots yet, see : https://www.berg-reise-foto.de/uebersichtskarte/ for an example of the plugin.


== Usage ==
See, the github repository for detailed description: https://github.com/MartinvonBerg/wp_post_map_view_simple
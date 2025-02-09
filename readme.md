# WP-Post-Map-View-Simple

## Description

This plugin displays all WordPress posts or pages containing GPX data (lat, lon) stored in custom fields on an OpenStreetMap map. Posts are categorized using tags, allowing filtering and custom icons. Additionally, a table with all posts is displayed.

**Shortcode:** `[mapview]` — Ready to use. Multiple options available, see table below! Use only once per Page or Post!

The generated HTML is stored in transients for improved performance.

The Map Tiles for Leaflet may be stored locally on your server!

## Screenshots

There are no screenshots yet. See an example of the plugin in action: [Demo](https://www.berg-reise-foto.de/uebersichtskarte/)

## Usage

### 1. Preparing Posts
#### Add Custom Fields:
- **Lat:** Latitude (use a decimal point as separator)
- **Lon:** Longitude
- Posts with `(0,0)`, invalid, or missing data are ignored.
- To check if all custom fields are set, use the "Admin Columns" WP plugin for better overview.
- Optionally : add **geoadress** taken from nominatim as serialized JSON. Example:
    ```json 
    a:7:{s:7:"village";s:6:"Marktl";s:12:"municipality";s:13:"Marktl (VGem)";s:6:"county";s:20:"Landkreis Altötting";s:5:"state";s:6:"Bayern";s:14:"ISO3166-2-lvl4";s:5:"DE-BY";s:7:"country";s:11:"Deutschland";s:12:"country_code";s:2:"de";}
    ```  

#### Check Category and Icon Mapping:
Categories are now defined in a JSON file. Below is the mapping:

| Tag in Post | Category | Icon |
|------------|----------|---------------|
| Trekk | Trekking | hiking.png |
| Bike hike | Bike-Hike | mountainbiking-3.png |
| Radfahren | Cycling | cycling.png |
| MTB | MTB | MTB.png |
| Wander | Hiking | hiking2.png |
| Bergtour | Mountain | peak2.png |
| Skitour | Skiing | skiing.png |
| Paddeln | Kayaking | kayaking2.png |
| (Default) | Travel | campingcar.png |

It is case sensitive!
Simple translate or change according to your site, e.g. translate Tag 'Radfahren' to 'Cycling'. Or Add 'Painting' / 'painting' / 'painting.png'

### 2. Displaying the Map
Insert the `[mapview]` shortcode on any page. Use only once per Page or Post!

### 3. Set Shortcode Parameters
The following parameters are available.
| Parameter | Default Value | Description | Example |
|-----------|---------------|-------------|---------|
| `numberposts` | 100 | Number of posts to display |
| `post_type` | post | Post types (can be an array) | post,page
| `showmap` | true | Show the map |
| `showtable` | true | Show the table |
| `category` | all | Filter by category-slug NOT Name! (case-sensitive, includes child categories) | travel,biking
| `headerhtml` | "" | Custom HTML for table header. Mind restrictions of passing html in shortcode parameters | <h3>Tour Table</h3>
| `gpxfolder` | gpx | Folder containing GPX files | gpx
| `lenexcerpt` | 150 | Length of the excerpt |
| `usewpexcerpt` | false | Use WordPress excerpt |
| `titlelength` | 80 | Max title length |
| `usetileserver` | true | Use tile server |
| `converttiles` | true | Convert tiles to *.webp |
| `contentfilter` | "Kurzbeschreibung:,Tourenbeschreibung:" | Content filter keywords which are removed from the excerpt |
| `tabulatortheme` | tabulator.min.css | Tabulator.js theme from folder ./css. Available are: bootstrap3, bootstrap4, bootstrap5, bulma, materialize, midnight, modern, semanticui, simple, site_dark, site, custom in tabulator_custom.min.css (change this according to your needs) |
| `tablepagesize` | 20 | Number of rows per table page |
| `tableheight` | 0 | Height of the table. 0 is ignored. Used to improve Table load time |
| `mapheight` | "" | Map height |
| `mapwidth` | "" | Map width |
| `mapaspectratio` | "" | Map aspect ratio |

### 4. Check Tile Server Settings in .htaccess
#### Tile Server for Leaflet Map Tiles
Since version 1.0.0, it is also possible to cache the leaflet tiles locally on your own server. This procedure conforms to the guidelines of the osmfoundation (https://operations.osmfoundation.org/policies/tiles/). There is no bulk download and the maps are stored locally. The Http referrer of the current request is used as the Http referrer. 
In addition the visitor's IP is NOT forwarded to the map server. This ensures that the use of maps from OpenStreeMap complies with the General Data Protection Regulation EC 2016/679. Therefore, no notice is required in the privacy policy of the website. This option can be set via shortcode parameter. Furthermore, the conversion of the tiles into webp file format can be chosen in order to meet Google Pagespeed requirements.
Note: The file ./the-plugin-folder/leaflet_map_tiles/.htacces has to be changed for the correct path and the admin panel will show if the Redirection by the .htaccess is successful:
```PHP
    ... content of .htaccess in ../the-plugin-folder/leaflet_map_tiles
    # Change only the next according to your server 
    RewriteBase /wordpress/wp-content/plugins/the-plugin-folder/leaflet_map_tiles/
    # Do not change after this line
```
Drawback: No fileage clean-up implemented. So, once stored, the tiles are used forever. Currently, only a manual deletion works which forces a new download of the tiles.

## Upgrade Notice

Upgrade is recommended. Preparation of Release is still missing.

## Installation

1. Zip the plugin directory (`*.zip`).
2. Install via the standard WP method (Upload zip in Admin Dashboard).
   - If already installed, remove the old version first! No additional directories or database entries will be deleted.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Done! No further settings required.

## Deinstallation
- Use the standard way of WordPress.

## Changelog

### 1.0.0 (9.03.2025)
- TBD, see github changelog.

### 0.10.5 (14.05.2022)
- Tested with WordPress 6.0

### 0.10.4 (02.02.2022)
- Minor PHP warning correction to satisfy PHPStan (level 5) and QueryMonitor

### 0.10.3 (30.01.2022)
- Replaced Bike-Hike-Map with CycleOSM

### 0.10.2 (16.01.2022)
- Minor PHP bugfix

### 0.10.1 (13.01.2022)
- CSS changes for new theme
- Readme update

### 0.10.0 (30.11.2021)
- Switched to tabulator.info

### 0.9.1 (18.11.2021)
- Introduced and tested transients for PHP -> JS variable `$postArray`

### 0.9.0 (16.11.2021)
- Reworked flexible icons and groups provided by PHP script
- Tours are now passed to JS as a variable

### 0.8.1 (08.11.2021)
- Added 'tab: false' for Safari to open pop-ups correctly

### 0.8.0 (30.03.2021)
- Added a table to show all posts under the map
- Bootstrap-table used for rendering
- Full functionality not guaranteed with the "Photo Perfect Pro" theme
- Fixed leaflet loading issue (ensuring it loads only once)
- All files now loaded locally
- Passed variable `g_wp_postmap_path` to JS using `localize_scripts`
- Introduced transients to store the generated HTML output (re-generated if a new post is published)

### 0.7.0 (17.02.2021)
- PHP 8 compatibility check (OK, no changes needed)
- Replaced `jQuery` with `$`
- Run JS only if `div 'map10_img'` is present

### 0.6.0 (09.01.2021)
- Mobile improvement: Hide zoom controls on mobile devices

### 0.5.0 (28.12.2020)
- Speed improvement: Optimized excerpt function (uses `<p>` abstracts only)
- Replaced function for converting tags to strings with an anonymous function

### 0.4.0 (14.12.2020)
- Introduced namespace

### 0.3.0 (01.04.2020)
- Initial release

## Credits

This plugin utilizes the following libraries and resources:
- [Leaflet](https://leafletjs.com/)
- [Leaflet MarkerCluster](https://github.com/Leaflet/Leaflet.markercluster)
- [Map Icons](https://mapicons.mapsmarker.com/)
- [Tabulator](https://tabulator.info/)


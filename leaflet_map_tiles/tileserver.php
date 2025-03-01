<?php 
namespace mvbplugins\fotoramamulti;

/**
 * Summary: Tileserver to provide leaflet map tiles from a locals server.
 * 
 * Assuming that map tiles and other files are in <plugin>/leaflet_map_tiles:
 * Provide a .htaccess file that points to above folder. This path has to be complete after your wordpress installation path.
 * If the WP-option is set to use local files (see Admin Page) the javascript code for leaflet map tiles is generated with url pointing to 
 * that (above) folder on your server.
 * The access to that folder is handled by Apache-servers and .htaccess: If the map tile as file is available it will be sent to the client.
 * If the file is not found this PHP script will be loaded. The new file will be donwloaded from OpenStreetMap servers and stored locally on your server.
 * Optionally it will be converted to webp which is best for performance and SEO.
 *
 * @author     Martin von Berg <mail@mvb1.de>
 * @copyright  Martin von Berg, 2023
 * @license    https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @link       https://github.com/MartinvonBerg/Fotorama-Leaflet-Elevation
 */

// TODO: cache fileage solution. What to do if files are too old?
// Note: Do not convert to avif because performance will be worse than webp.

// get the directory ot this file which is the cachedir and define other variables
$cacheDir = __DIR__;
$cacheHeaderTime = 60*60*24*365; // Browser Header (sec)
$cacheFileTime = 60*60*24*365 / 4; // max File Age (sec)
$preUrl = 'https://';
$ds = \DIRECTORY_SEPARATOR;
$allowed = \ini_get('allow_url_fopen') === '1';
$useWebp = true;
$error = false;

// return silently if the request is not a tile request
if ( ! isset($_GET["tile"])) {
	return;
}

// partition the request code
if ($_GET["tile"] === 'testfile.webp') {
	http_response_code(302);
	echo('local htaccess is working');
	return;
}

$req = preg_split('/(\/|\.)/', $_GET["tile"]);
if ( \count($req) !== 5 ) {
	return;
}
$req[4] = \strtolower($req[4]);

$tileServers = array(
	"osm" => array(
		"searchfor" => "osmde",
		"localdir" 	=> "osm{$ds}{$req[1]}{$ds}{$req[2]}",
		"server" 	=> "tile.openstreetmap.org/", //"tile.openstreetmap.org/", https://tile.openstreetmap.org/{z}/{x}/{y}.png
		"tile" 		=> "{$req[1]}/{$req[2]}/{$req[3]}",
		"file"		=> "{$req[3]}",
		"ext" 		=> "png"
	),
	"otm" => array(
		"searchfor" => "opentopomap",
		"localdir" 	=> "otm{$ds}{$req[1]}{$ds}{$req[2]}",
		"server" 	=> "a.tile.opentopomap.org/",
		"tile" 		=> "{$req[1]}/{$req[2]}/{$req[3]}",
		"file"		=> "{$req[3]}",
		"ext" 		=> "png"
	),
	"cycle" => array(
		"searchfor" => "cyclosm",
		"localdir" 	=> "cycle{$ds}{$req[1]}{$ds}{$req[2]}",
		"server" 	=> "a.tile-cyclosm.openstreetmap.fr/cyclosm/",
		"tile" 		=> "{$req[1]}/{$req[2]}/{$req[3]}",
		"file"		=> "{$req[3]}",
		"ext" 		=> "png"
	),
	"sat" => array(
		"searchfor" => "arcgisonline",
		"localdir" 	=> "sat{$ds}{$req[1]}{$ds}{$req[2]}",
		"server" 	=> "server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/",
		"tile" 		=> "{$req[1]}/{$req[2]}/{$req[3]}",
		"file"		=> "{$req[3]}",
		"ext" 		=> "jpeg"
	),
);

// check if localdir is in request and get the requested tiletype.
$tile ='';
foreach ($tileServers as $key => $entry) {
	if ( $req[0] === $key ) {
		$tile = $key;
		break;
	}
}

if ( $tile==='' || ! \is_numeric( $req[1]) || ! \is_numeric( $req[2]) || ! \is_numeric( $req[3]) || !( ($req[4] === 'webp') || ($req[4] === 'png') || ($req[4] === 'jpg') || ($req[4] === 'jpeg') )) {
	http_response_code(404);
	echo('request denied');
	return;
}

// create the directory name.
$localDir = $cacheDir . $ds . $tileServers[$tile]["localdir"];

if ( $req[4] !== 'webp') $useWebp = false;

// create the file name 
$localFile = $localDir . $ds . $tileServers[$tile]["file"];
if ( $useWebp ) {
	$localFile = $localFile . ".webp";
	$headerMime = 'image/webp';
} else {
	$localFile = $localFile . '.' . $req[4];
	$headerMime = 'image/' . $req[4];
}

// check if file is available on server. // combine with filemtime to check if file is too old
if ( \file_exists($localFile)) { 
	$httpResCode = 200;
} 
elseif ( $allowed ) 
{ // fallback if the file is still not available

	// check if dir exists if not create it
	if ( !\file_exists($localDir) && !\is_dir($localDir) ) \mkdir($localDir, 0777, true);
	// set original Filename 
	// note: file operations are not checked for errors. The assumption is that this operations work as expected.
	$tileFile = $localDir . $ds . $tileServers[$tile]["file"] . '.' . $tileServers[$tile]['ext'];

	// fetch the new file
	$url = $preUrl . $tileServers[$tile]['server'] . $tileServers[$tile]['tile'] . '.' . $tileServers[$tile]['ext'];
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
	$imgData = file_get_contents( $url , false, $context );

	if ( $imgData === false) {
		$error = true;
	} else {
		// save the original file
		$result = file_put_contents($tileFile, $imgData); 

		// convert the original and save it
		if ( $useWebp ) { 
			$result = webpImage($tileFile, 75, true);
		} elseif ( $req[4] !== $tileServers[$tile]['ext']) {
			$headerMime = 'image/' . $tileServers[$tile]['ext'];
			$localFile = $tileFile;
		}

		if ( $result === false) {
			$error = true;
		} else {
			$httpResCode = 201;
		}
	}
}

if ( $error || ! $allowed) {
	$localFile = $cacheDir . $ds . 'tile-error.webp';
	$headerMime = 'image/webp';	
	$httpResCode = 404;
}

// pass the image content to client
http_response_code( $httpResCode );
header("Cache-Control: public, max-age=".$cacheHeaderTime.", s-maxage=".$cacheHeaderTime."");
header('Content-type: ' . $headerMime); 
readfile( $localFile);

// ----------------------------------------------------------------
/**
 * Convert image file to webp and keep or remove the original file.
 *
 * @param  string  $source the file path to the original file.
 * @param  integer $quality for webp conversion.
 * @param  boolean $removeOld remove the original file or not.
 * @return string|false the file path to the converted file.
 */
function webpImage($source, $quality = 80, $removeOld = false) {
	if( ! extension_loaded('gd') || ! \function_exists('imagewebp')) return false;

	$dir = pathinfo($source, PATHINFO_DIRNAME);
	$name = pathinfo($source, PATHINFO_FILENAME);
	$destination = $dir . DIRECTORY_SEPARATOR . $name . '.webp';
	$info = getimagesize($source);
	$isAlpha = false;

	if ($info['mime'] == 'image/jpeg')
		$image = imagecreatefromjpeg($source);
	elseif ($isAlpha = $info['mime'] == 'image/gif') {
		$image = imagecreatefromgif($source);
	} elseif ($isAlpha = $info['mime'] == 'image/png') {
		$image = imagecreatefrompng($source);
	} else {
		return $source;
	}
	if ($isAlpha) {
		imagepalettetotruecolor($image);
		imagealphablending($image, true);
		imagesavealpha($image, true);
	}
	imagewebp($image, $destination, $quality);

	if ($removeOld)
		unlink($source);

	return $destination;
}

function generateHtaccess() {
	$url = 'http://' . $_SERVER['HTTP_HOST'];
	if ( is_ssl() ) {
		$url = str_replace('http://', 'https://', $url);
	}
	$plugin_main_dir = dirname( __DIR__, 1 );
	$slug = \WP_PLUGIN_URL . '/' . \basename( $plugin_main_dir ) . '/leaflet_map_tiles/';
	$relative_url =  \str_replace($url, '', $slug);

	// create the file name 
	$localFile = __DIR__ . \DIRECTORY_SEPARATOR . '.htaccess';

	// create the .htaccess file
	$htaccess = <<<EOT
<LimitExcept GET HEAD>
    Order Allow,Deny
    Deny from all
</LimitExcept>

<IfModule mod_rewrite.c>
    RewriteEngine On
    # Change only the next line according to your server 
    RewriteBase {$relative_url}
    # Do not change after this line
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} \.(jpeg|jpg|png|webp)$
    RewriteRule ^(.+)$ tileserver.php/?tile=$1 [L]
</IfModule>
EOT;
	// save the original file
	$result = (bool) file_put_contents($localFile, $htaccess);
	return $result;
}

/**
 * Check if file .htaccess is available in the sub-folder 'leaflet_map_tiles' and try to fetch the 
 * testfile, which will be responded with status code 302 file by the script 'tileserver.php'.
 * 
 * @return boolean the result of the htaccess check
 */
function checkHtaccess()
{
	// try to access testfile.webp which will be redirected to testfile.php if .htaccess is working. 
	// The file 'testfile.webp' shall not be existent!
	$path = plugins_url('/', __FILE__);

	if (\ini_get('allow_url_fopen') === '1') {
		$url = $path . 'testfile.webp';
		$context = stream_context_create( array(
			'http'=>array(
				'timeout' => 5.0 // Das erzeugt bei Fehlern einen Verzögerung um 5 Sekunden!
			)
		), );

		// switch off PHP error reporting and get the url.
		$ere = \error_reporting();
		\error_reporting(0);
		$test = fopen($url, 'r', false, $context);
		\error_reporting($ere);

		// check if header contains status code 302
		if ($test !== false) {
			$code = $http_response_header[0];
			$found = \strpos($code, '302');
			fclose($test);
			if ($found  !== false) return true;
		}
	}
	return false;
}


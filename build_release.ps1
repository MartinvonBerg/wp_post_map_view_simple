# build a release for github
$OutputEncoding = [console]::InputEncoding = [console]::OutputEncoding = New-Object System.Text.UTF8Encoding

# Clean release and build folder completely
Remove-Item ".\release" -Recurse -Force
Remove-Item ".\build"   -Recurse -Force

# create release directory if not existing
$path = ".\release"
New-Item "$($path)" -Force -itemType Directory

# copy php files from main folder to the same structure
Copy-Item ".\wp_post_map_view_simple.php" -Destination "$($path)\wp_post_map_view_simple.php" 
Copy-Item ".\uninstall.php" -Destination "$($path)\uninstall.php" 
Copy-Item ".\readme.md" -Destination "$($path)\readme.md" 
Copy-Item ".\readme.txt" -Destination "$($path)\readme.txt" 

# first build the new gutenberg block build files
npm run build # this uses the file ./js/webpack.config.js.

# copy gutenberg, swiper, fotorama, leaflet-map build files
$path = ".\release"
$path = "$($path)\build"
New-Item "$($path)" -Force -itemType Directory
robocopy .\build $path *.* /s /NFL /NDL /NJH /NJS

# ./css 
## ./css copy all images
$path = ".\release"
$path = "$($path)\css"
robocopy .\css $path *.png /s /NFL /NDL /NJH /NJS

## ./css copy only all minified css files
$path = ".\release"
$path = "$($path)\js"
robocopy .\js $path *.min.css /xf Control* /xf leaflet* /NFL /NDL /NJH /NJS

# ./images
$path = ".\release"
$path = "$($path)\images"
robocopy .\images $path *.* /s /NFL /NDL /NJH /NJS

# ./inc
$path = ".\release"
$path = "$($path)\inc"
robocopy .\inc $path *.* /s /xf shortCodeTester.php /NFL /NDL /NJH /NJS

# ./languages
$path = ".\release"
$path = "$($path)\languages"
robocopy .\languages $path *.* /s /NFL /NDL /NJH /NJS

# ./leaflet_map_tiles - without the subdirectories
$path = ".\release"
$path = "$($path)\leaflet_map_tiles"
robocopy .\leaflet_map_tiles $path *.* /NFL /NDL /NJH /NJS

# ./leaflet_map_tiles - without the subdirectories
$path = ".\release"
$path = "$($path)\settings"
robocopy .\leaflet_map_tiles $path *.* /NFL /NDL /NJH /NJS

# Finally write a warning that CSS-Files should have been minified before
Write-Warning "War webpack auf production gesetzt?"
Write-Warning "Nun den Inhalt vom realease-Ordner zippen als postmapviewsimple.zip. Fertig"
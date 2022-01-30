/**
 * JS-File for past-map-view-simple
 */

(function (window, document, $, undefined) {
    "use strict";
    let numberOfboxes = document.querySelectorAll('[id=map10_img]').length;
    g_wp_postmap_path = g_wp_postmap_path.g_wp_postmap_path;
    
    if (numberOfboxes == 1) {
        let mobile = (/iphone|ipod|android|webos|ipad|iemobile|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));

        // get the data from the php variable with all tours or posts
        let touren = Object.values(php_touren); // Daten aus dem <a>-Tag laden
    
        // Icons definieren aus der php variable übernehmen
        let allIcons = php_allIcons;
        let myIcon = new Array();

        allIcons.forEach( function(icon, iconindex) {
            myIcon[iconindex] = L.icon({
              iconUrl: g_wp_postmap_path + icon[0],
              iconSize: [32, 32],
              iconAnchor: [0, 0],
              popupAnchor: [0, 0],
              //shadowUrl: 'icon-shadow.png',
              //shadowSize: [100, 95],
              //shadowAnchor: [22, 94]
            });            
          });  
 
        // Kartenlayer definieren
        var layer1 = new L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: 'MapData &copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors, <a href="https://viewfinderpanoramas.org">SRTM</a> | MapStyle:&copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
            });

        var layer2 = new L.tileLayer('https://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/ {y}.png', {
                maxZoom: 19,
                attribution: 'MapData &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            });

        var layer3 = new L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: 'MapData &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                });

        var layer4 = new L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri User Community'
        });

        if ( mobile ) {
            layer1.options.attribution = layer2.options.attribution;
            layer3.options.attribution = layer2.options.attribution;
            layer4.options.attribution = layer2.options.attribution;
        };

        var mapOptions = {
            center: [48.0, 12],
            zoom: 10,
            zoomControl: false,
            layers: [layer1],
            tap : false,
            gestureHandling: true,            
        }

        var baseMaps = {
            "OpenStreetMap": layer2,
            "OpenTopoMap": layer1,
            "CycleOSM": layer3,
            "Satellit": layer4
        }

        var map = new L.map('map', mapOptions);

        // Creating scale control
        var scale = L.control.scale();
        scale.addTo(map);

        // create scale control top left // mobile: zoom deactivate. use fingers!
        if ( ! mobile ) { 
            var controlZoom = new L.Control.Zoom( {position: 'topleft'}); 
            controlZoom.addTo(map); 
        }

        // Creating markergroups ----------------------- 
        let LayerSupportGroup = L.markerClusterGroup.layerSupport();
        LayerSupportGroup.addTo(map);   
                    
        // Creating markers -----------------------
        let group = new Array();
        allIcons.forEach( function(sIcon, index) {
            group[index] = L.layerGroup();
        }); 
     
        var marker = new Array();
        var nposts = Array( allIcons.length ).fill(0);
        var j = 0;
        var icn, grp;
        touren.forEach(tour => { 
            //allIcons.forEach( function(icon, index) {
            let found = false;

            for (let index = 0; index < allIcons.length; index++) {    
                if (tour["category"] == allIcons[index][1]) {
                    icn = myIcon[index];
                    grp = group[index];
                    nposts[index]++;
                    found = true;
                    break;
                };
            };

            if ( ! found ) {
                icn = myIcon[ allIcons.length-1 ];
                grp = group[ allIcons.length-1];
                nposts[ allIcons.length-1]++;
            }

            marker.push(new L.Marker(tour["coord"], { title: tour["title"], icon: icn })); 
            marker[j].bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br><img src="' + tour["img"] + '">' + tour["excerpt"] + '</a>');
            marker[j].addTo(grp);
            j++;
        });
        
        group.forEach( function(sgrp, index) {
            LayerSupportGroup.checkIn(group[index]);
        }); 
        
        let control = L.control.layers(baseMaps, null, { collapsed: true });

        allIcons.forEach( function(icon, index) {
            control.addOverlay(group[index], '<img class="layerIcon" src="' + g_wp_postmap_path + icon[0] + '"/> '+icon[2]+' (' + nposts[index] + ')');  
        });
      
        control.addTo(map);

        group.forEach( function(sgrp, index) {
            group[index].addTo(map);
        }); 
        // ---------------------------------------------------

        var bounds = L.latLngBounds();
        marker.forEach(m => {
            let lat_lng = m._latlng;
            bounds.extend(lat_lng);
        });
        map.fitBounds(bounds, { padding: [50, 50] });

        L.Control.Watermark = L.Control.extend({
            onAdd: function (map) {
                var img = L.DomUtil.create('img');
                img.src = g_wp_postmap_path + 'lupe_p_32.png';
                img.style.background = 'white';
                img.style.width = '32px';
                img.style.height = '32px';
                img.style.cursor = 'pointer';
                img.title = 'Alles anzeigen';
                img.onclick = function () {
                    map.fitBounds(bounds, { padding: [50, 50] }); 
                    group.forEach( function(sgrp, index) {
                        map.removeLayer(group[index]);
                        map.addLayer(group[index]);
                    });
                };
                return img;
            },
            onRemove: function () {
                // Nothing to do here
            }
        });

        L.control.watermark = function (opts) {
            return new L.Control.Watermark(opts);
        }

        L.control.watermark({ position: 'topleft' }).addTo(map);

        LayerSupportGroup.on('clustermouseover', function (a) {
            var children = a.layer.getAllChildMarkers();
            var names = [];
            var string = '';
            var max = children.length;
            if (max > 10) { max = 10 };
            for (let i = 0; i < max; i++) {
                names.push(children[i].options.title);
                string = string + '- ' + children[i].options.title + '<br>';

            }
            if (children.length > max) { string = string + '...u.v.m....' }
            a.propagatedFrom.bindTooltip(string).openTooltip();
        });

        LayerSupportGroup.on('clustermouseout', function (a) {
            a.propagatedFrom.bindTooltip('').closeTooltip();
        });

        L.Control.Layers.include({
            getOverlays: function() {
            // create hash to hold all layers
            var control, layers, activemaplayer;
            layers = {};
            control = this;
                    
            // loop thru all layers in control
            control._layers.forEach(function(obj) {
                var layerName;
        
                // check if layer is not an overlay 
                if (!obj.overlay) {
                // get name of overlay
                layerName = obj.name;
                // store whether it's present on the map or not
                layers[layerName] = control._map.hasLayer(obj.layer);
                if (layers[layerName]) {activemaplayer = layerName};
                }
            });
            return activemaplayer;
            }
        });

        $(window).on("resize", function() {
            var or = window.orientation;
            var h = window.screen.availHeight;
            var w = window.screen.availWidth;

            if (or == undefined) { // Desktop Version
                var divwidth = $('.box1').width();
                var divhgt = 0.8 * divwidth;
                if (divhgt<400) {divhgt=400};
                $("#map").height(divhgt);
                //console.log('Desktop');
                //map.fitBounds(bounds, { padding: [50, 50] });
            } else { // Mobile Version
                if ((or == 0) || (or == 180)) { // Hochformat
                    $("#map").height(0.75*w);
                } else { // Querformat
                    $("#map").height(0.5*h);
                }

            }
            //map.fitBounds(bounds, { padding: [50, 50] });
            map.invalidateSize();
        }).trigger('resize');

        map.on('baselayerchange', function() {
            var activeLayer =  control.getOverlays();
            switch (activeLayer) {
                case 'OpenStreetMap':
                    $('.leaflet-container').css("background","#b9d3dc");
                    break;
                case 'OpenTopoMap':
                    $('.leaflet-container').css("background","#97d2e3");
                    break;
                case 'Bike-Hike-Map':
                    $('.leaflet-container').css("background","#c8e4fa");
                    break;
                case 'Satellit':
                    $('.leaflet-container').css("background","#071e40");
                    break;
                default:
                    $('.leaflet-container').css("background","lightgrey");
                    break;
            }
        });
        
        //Note: This API has been removed in jQuery 3.0; please use .on( "load", handler ) 
        //instead of .load( handler ) and .trigger( "load" ) instead of .load().
        //jQuery(window).load(function(){
        $(window).on("load", function() {    
            var activeLayer =  control.getOverlays();
            var or = window.orientation;
            var h = window.screen.availHeight;
            var w = window.screen.availWidth;

            if (or == undefined) { // Desktop Version
                var divwidth = $('.box1').width();
                var divhgt = 0.8 * divwidth;
                if (divhgt<400) {divhgt=400};
                $("#map").height(divhgt);
            } else { // Mobile Version
                if ((or == 0) || (or == 180)) { // Hochformat
                    $("#map").height(0.75*w);
                } else { // Querformat
                    $("#map").height(0.5*h);
                }

            }
        
            map.fitBounds(bounds, { padding: [50, 50] });
            map.invalidateSize();

            switch (activeLayer) {
                case 'OpenStreetMap':
                    $('.leaflet-container').css("background","#b9d3dc");
                    break;
                case 'OpenTopoMap':
                    $('.leaflet-container').css("background","#97d2e3");
                    break;
                case 'Bike-Hike-Map':
                    $('.leaflet-container').css("background","#c8e4fa");
                    break;
                case 'Satellit':
                    $('.leaflet-container').css("background","#071e40");
                    break;
                default:
                    $('.leaflet-container').css("background","lightgrey");
                    break;
            }
        });

    }
    
})(window, document, jQuery);
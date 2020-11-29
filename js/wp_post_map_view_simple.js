"use strict";

if (typeof g_wp_postmap_path === 'undefined') { var g_wp_postmap_path = ''; }

(function (window, document, undefined) {
  
    var touren = new Array(); // Daten aus dem <a>-Tag laden
    jQuery('a').each(function (index, value) { // Achtung: geht nur, wenn die Marker als <a> auf der Seite erzeugt werden und sonst keine a-Elemente mit geo-daten vorhanden sind
        var geodata = this.dataset.geo; // Index for das touren-array evtl. als eigene Lauf-Variable, da index aus beliebigen Werten besteht
        if (geodata) {
            geodata = geodata.split(",");
            if (geodata.length == 2) {
                for (var j = 0; j < 2; j++) {
                    var par = geodata[j].split(":");
                    geodata[j] = parseFloat(par[1]);
                }
                if (isNaN(geodata[0]) || isNaN(geodata[1])){
                    // do nothing
                } else {
                    touren[index] = new Object;
                    touren[index]["excerpt"] = this.text.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ");
                    touren[index]["link"] = this.dataset.link;
                    touren[index]["img"] = this.href;
                    touren[index]["title"] = this.dataset.title.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ");
                    touren[index]["category"] = this.dataset.icon;
                    touren[index]["coord"] = geodata;
                }
            }
        }

    });
   
    // Icons definieren, TODO: besser als Klasse und als LOOP, abhängig von der Anzahl der Kategorien!
    var icnh = 32;
    var icnw = 32;

    var myIcon1 = L.icon({ // hiking     $icon = "hiking";
        iconUrl: g_wp_postmap_path + 'hiking2.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });
    
    var myIcon2 = L.icon({ // bike-hike  $icon = "bike-hike";
        iconUrl: g_wp_postmap_path + 'mountainbiking-3.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });

    var myIcon3 = L.icon({ // cycling    $icon = "cycling";
        iconUrl: g_wp_postmap_path + 'cycling.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });

    var myIcon4 = L.icon({ // MTB        $icon = "MTB";
        iconUrl: g_wp_postmap_path + 'MTB.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });

    var myIcon5 = L.icon({ // mountain   $icon = "mountain";
        iconUrl: g_wp_postmap_path + 'peak2.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });

    var myIcon6 = L.icon({ // skiing     $icon = "skiing"
        iconUrl: g_wp_postmap_path + 'skiing.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });

    var myIcon7 = L.icon({ // travel     $icon = "travel";
        iconUrl: g_wp_postmap_path + 'campingcar.png',
        iconSize: [icnh, icnw],
        iconAnchor: [0, 0],
        popupAnchor: [0, 0],
        //shadowUrl: 'icon-shadow.png',
        //shadowSize: [100, 95],
        //shadowAnchor: [22, 94]
    });
    
    // Karenlayer definieren
    var layer1 = new L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: 'MapData:&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | MapStyle:&copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
        });

    var layer2 = new L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/ {y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

    var layer3 = new L.tileLayer('https://tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });

    var layer4 = new L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri User Community'
    });

    var mapOptions = {
        center: [48.0, 12],
        zoom: 10,
        layers: [layer1]
    }

    var baseMaps = {
        "OpenStreetMap": layer2,
        "OpenTopoMap": layer1,
        "Bike-Hike-Map": layer3,
        "Satellit": layer4
    }

    var map = new L.map('map', mapOptions);

    // Creating scale control
    var scale = L.control.scale();
    // Adding scale control to the map
    scale.addTo(map);

    // Creating markergroups ----------------------- TODO: LOOP, abhängig von der Anzahl der Kategorien!
    var LayerSupportGroup = L.markerClusterGroup.layerSupport(), 
        group1 = L.layerGroup(), // hiking     $icon = "hiking";
        group2 = L.layerGroup(), // bike-hike  $icon = "bike-hike";
        group3 = L.layerGroup(), // cycling    $icon = "cycling";
        group4 = L.layerGroup(), // MTB        $icon = "MTB";
        group5 = L.layerGroup(), // mountain   $icon = "mountain";
        group6 = L.layerGroup(), // skiing     $icon = "skiing"
        group7 = L.layerGroup(), // travel     $icon = "travel";
                   
        control = L.control.layers(baseMaps, null, { collapsed: true }),
        i, a, title;

    LayerSupportGroup.addTo(map);

    // Creating markers -----------------------
    var marker = new Array();
    var nposts = [0,0,0,0,0,0,0]; // TODO: Loop
    var j = 0;
    var icn, grp;
    touren.forEach(tour => { // TODO: Loop
        switch (tour["category"]) {
            case 'hiking':
            icn = myIcon1;
            grp = group1;
            nposts[0] = nposts[0] +1;
                break;
            
            case 'bike-hike':
            icn = myIcon2;
            grp = group2;
            nposts[1] = nposts[1] +1;
                break;

            case 'cycling':
            icn = myIcon3;
            grp = group3;
            nposts[2] = nposts[2] +1;
                break;

            case 'MTB':
            icn = myIcon4;
            grp = group4;
            nposts[3] = nposts[3] +1;
                break;

            case 'mountain':
            icn = myIcon5;
            grp = group5;
            nposts[4] = nposts[4] +1;
                break;

            case 'skiing':
            icn = myIcon6;
            grp = group6;
            nposts[5] = nposts[5] +1;
                break;

            case 'travel':
            icn = myIcon7;
            grp = group7;
            nposts[6] = nposts[6] +1;
                break;    

            default:
            icn = myIcon7;
            grp = group7;
            nposts[6] = nposts[6] +1;
                break;
        }
        marker.push(new L.Marker(tour["coord"], { title: tour["title"], icon: icn })); 
        marker[j].bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br><img src="' + tour["img"] + '">' + tour["excerpt"] + '</a>');
        marker[j].addTo(grp);
        j++;
    });
    // ---------------------------------------- TODO: LOOP
    LayerSupportGroup.checkIn([group1, group2, group3, group4, group5, group6, group7]); 

    control.addOverlay(group1, '<img class="layerIcon" src="'+g_wp_postmap_path+'hiking2.png"/> Wandern (' + nposts[0] + ')');               // hiking     $icon = "hiking";; 
    control.addOverlay(group2, '<img class="layerIcon" src="'+g_wp_postmap_path+'mountainbiking-3.png"/> Bike-Hike (' + nposts[1] + ')');    // bike-hike  $icon = 'bike-hike';
    control.addOverlay(group3, '<img class="layerIcon" src="'+g_wp_postmap_path+'cycling.png"/> Radfahren (' + nposts[2] + ')');             // cycling    $icon = 'cycling';
    control.addOverlay(group4, '<img class="layerIcon" src="'+g_wp_postmap_path+'MTB.png"/> MTB (' + nposts[3] + ')');                       // MTB        $icon = 'MTB';
    control.addOverlay(group5, '<img class="layerIcon" src="'+g_wp_postmap_path+'peak2.png"/> Bergtour (' + nposts[4] + ')');                // mountain   $icon = 'mountain';
    control.addOverlay(group6, '<img class="layerIcon" src="'+g_wp_postmap_path+'skiing.png"/> Skitour (' + nposts[5] + ')');                // skiing     $icon = 'skiing'
    control.addOverlay(group7, '<img class="layerIcon" src="'+g_wp_postmap_path+'campingcar.png"/> Reisebericht (' + nposts[6] + ')');       // travel     $icon = 'travel';
    control.addTo(map);

    group1.addTo(map); // Adding to map or to AutoMCG are now equivalent.
    group2.addTo(map);
    group3.addTo(map);
    group4.addTo(map);
    group5.addTo(map);
    group6.addTo(map);
    group7.addTo(map);
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
                map.fitBounds(bounds, { padding: [50, 50] }); // TODO: Loop
                map.removeLayer(group1);
                map.addLayer(group1);
                map.removeLayer(group2);
                map.addLayer(group2);
                map.removeLayer(group3);
                map.addLayer(group3);
                map.removeLayer(group4);
                map.addLayer(group4);
                map.removeLayer(group5);
                map.addLayer(group5);
                map.removeLayer(group6);
                map.addLayer(group6);
                map.removeLayer(group7);
                map.addLayer(group7);
            };
            return img;
        },
        onRemove: function (map) {
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
        for (i = 0; i < max; i++) {
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

    jQuery(window).on("resize", function() {
        var or = window.orientation;
        var h = window.screen.availHeight;
        var w = window.screen.availWidth;

        if (or == undefined) { // Desktop Version
            var divwidth = jQuery('.box1').width();
            var divhgt = 0.8 * divwidth;
            if (divhgt<400) {divhgt=400};
            jQuery("#map").height(divhgt);
            console.log('Desktop');
            //map.fitBounds(bounds, { padding: [50, 50] });
        } else { // Mobile Version
            if ((or == 0) || (or == 180)) { // Hochformat
                jQuery("#map").height(0.75*w);
            } else { // Querformat
                jQuery("#map").height(0.5*h);
            }

        }
        //map.fitBounds(bounds, { padding: [50, 50] });
        map.invalidateSize();
    }).trigger('resize');

     map.on('baselayerchange', function() {
        var activeLayer =  control.getOverlays();
        switch (activeLayer) {
            case 'OpenStreetMap':
                jQuery('.leaflet-container').css("background","#b9d3dc");
                break;
            case 'OpenTopoMap':
                jQuery('.leaflet-container').css("background","#97d2e3");
                break;
            case 'Bike-Hike-Map':
                jQuery('.leaflet-container').css("background","#c8e4fa");
                break;
            case 'Satellit':
                jQuery('.leaflet-container').css("background","#071e40");
                break;
            default:
                jQuery('.leaflet-container').css("background","lightgrey");
                break;
        }
    });
    
    jQuery(window).load(function(){
        var activeLayer =  control.getOverlays();
        var or = window.orientation;
        var h = window.screen.availHeight;
        var w = window.screen.availWidth;

        if (or == undefined) { // Desktop Version
            var divwidth = jQuery('.box1').width();
            var divhgt = 0.8 * divwidth;
            if (divhgt<400) {divhgt=400};
            jQuery("#map").height(divhgt);
        } else { // Mobile Version
            if ((or == 0) || (or == 180)) { // Hochformat
                jQuery("#map").height(0.75*w);
            } else { // Querformat
                jQuery("#map").height(0.5*h);
            }

        }
      
        map.fitBounds(bounds, { padding: [50, 50] });
        map.invalidateSize();

        switch (activeLayer) {
            case 'OpenStreetMap':
                jQuery('.leaflet-container').css("background","#b9d3dc");
                break;
            case 'OpenTopoMap':
                jQuery('.leaflet-container').css("background","#97d2e3");
                break;
            case 'Bike-Hike-Map':
                jQuery('.leaflet-container').css("background","#c8e4fa");
                break;
            case 'Satellit':
                jQuery('.leaflet-container').css("background","#071e40");
                break;
            default:
                jQuery('.leaflet-container').css("background","lightgrey");
                break;
        }
    });
    
})(window, document);
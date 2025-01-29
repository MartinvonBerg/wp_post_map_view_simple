function mainLogic (window, document, undefined) {
    "use strict";
    
    if (window.g_wp_postmap_path.number === "1" && php_touren.length > 0) {
      //let mobile = (/iphone|ipod|android|webos|ipad|iemobile|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
      let postmap_url = window.g_wp_postmap_path.path;
      let hasMap = true;
      let hasTable = false;
      let numberOfBoxes = 1;
      let allMaps = [ numberOfBoxes-1 ];
      let m = 0;
        
      Promise.all([
        import('../settings/category_mapping.json'),
        import('./leafletMapClass.js')
      ]).then(([category_mapping, LeafletMap]) => {

        allMaps[m] = new LeafletMap.LeafletMap(m, 'map10_img' );
                        
        // Icons definieren aus der Json variable übernehmen
        let allIcons = category_mapping['mapping']; // allIcons ist ident zu dem was aus php kommt
        
        let myIcon = new Array();
        allIcons.forEach( function(icon, iconindex) {
          myIcon[iconindex] = allMaps[m].setIcon(postmap_url,icon['icon-png'],'marker-shadow.png')
        });
        /*
        allIcons.forEach( function(icon, iconindex) {
            myIcon[iconindex] = L.icon({
              iconUrl: postmap_url + icon['icon-png'],
              iconSize: [32, 32],
              iconAnchor: [0, 0],
              popupAnchor: [0, 0],
              //shadowUrl: 'icon-shadow.png',
              //shadowSize: [100, 95],
              //shadowAnchor: [22, 94]
            });            
          });
        */  
        
        // Creating markergroups ----------------------- 
        let LayerSupportGroup = L.markerClusterGroup.layerSupport();
        LayerSupportGroup.addTo(allMaps[m].map);
        
        // Creating markers -----------------------
        let group = new Array();
        allIcons.forEach( function(sIcon, index) {
            group[index] = L.layerGroup();
        });

        let marker = new Array();
        let nposts = Array( allIcons.length ).fill(0); 
        let j = 0;
        let icn, grp;

        php_touren.forEach(tour => { 
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

        allIcons.forEach( function(icon, index) {
          allMaps[m].controlLayer.addOverlay(group[index], '<img class="layerIcon" src="' + postmap_url + icon['icon-png'] + '"/> '+icon['category']+' (' + nposts[index] + ')');  
        });

        group.forEach( function(sgrp, index) {
            group[index].addTo(allMaps[m].map);
        });

        let bounds = L.latLngBounds();
        marker.forEach(m => {
            let lat_lng = m._latlng;
            bounds.extend(lat_lng);
        });
        allMaps[m].map.fitBounds(bounds, { padding: [50, 50] });

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
        
        allMaps[m].map.on('baselayerchange', function() {
          let activeLayer =  allMaps[0].controlLayer.getOverlays();
          switch (activeLayer) {
              case 'OpenStreetMap':
                document.querySelector('.leaflet-container').style.background = "#b9d3dc";
                break;
              case 'OpenTopoMap':
                document.querySelector('.leaflet-container').style.background = "#97d2e3";
                break;
              case 'Bike-Hike-Map':
                document.querySelector('.leaflet-container').style.background = "#c8e4fa";
                break;
              case 'Satellit':
                document.querySelector('.leaflet-container').style.background = "#071e40";
                break;
              default:
                document.querySelector('.leaflet-container').style.background = "lightgrey";
                break;
          }
        });

        window.addEventListener("load", function() {
          let newBounds = allMaps[m].map.getBounds();
          allMaps[m].setBounds(newBounds);
        })

      }) // end Promise - then
      
    } // end if numberOfBoxes
};

// Export oder Nutzung im Backend
export { mainLogic };
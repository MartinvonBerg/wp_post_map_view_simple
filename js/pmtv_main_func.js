function mainLogic (window, document, undefined) {
    "use strict";

    function isValidCssSize(value) {
      if (typeof value !== 'string') return false;
  
      // Regulärer Ausdruck für gültige CSS-Größenangaben mit Zahlen > 0
      const cssSizePattern = /^(?!0(?:px|em|rem|vw|vh|vmin|vmax|%|))(\d*\.?\d+)(px|em|rem|vw|vh|vmin|vmax|%)|auto|inherit|initial|unset$/;
      
      const match = value.trim().match(cssSizePattern);
      if (!match) return false;
      
      // Falls der Wert numerisch ist, prüfen, ob er größer als 0 ist
      if (match[1] && parseFloat(match[1]) <= 0) return false;
      
      return true;
    }
  
    function isValidAspectRatio(value) {
        if (typeof value !== 'string') return false;
        
        // Regulärer Ausdruck für aspect-ratio: entweder ein Float oder zwei Integer mit Schrägstrich
        const aspectRatioPattern = /^(\d+(\.\d+)?|\d+\/\d+)$/;
        
        return aspectRatioPattern.test(value.trim());
    }  

    /**
    * update CSS rules that are used according to the options and client
    */
    function updateCSS(pageVars) {
      // analyze pageVars
      if (!pageVars || ( !isValidCssSize(pageVars.mapHeight) && !isValidCssSize(pageVars.mapWidth) && !isValidAspectRatio(pageVars.mapAspectRatio) )) {
        return;
      }

      // ignore Aspect Ratio if Height and Width are set
      if (isValidCssSize(pageVars.mapHeight) && isValidCssSize(pageVars.mapWidth)) {
        pageVars.mapAspectRatio = 0;
      }

      // build CSS rules
      const heightString = isValidCssSize(pageVars.mapHeight) ? `height: ${pageVars.mapHeight};` : '';
      const widthString = isValidCssSize(pageVars.mapWidth) > 0 ? `width: ${pageVars.mapWidth};` : '';
      const aspectString = isValidAspectRatio(pageVars.mapAspectRatio) ? `aspect-ratio: ${pageVars.mapAspectRatio};` : '';

      // set width and height to the current window size
      const style = document.createElement('style');
      style.innerHTML = `
          #map0 {
              ${ heightString };
              ${ widthString };
              ${ aspectString };
          }`;
      document.head.appendChild(style);
    }

    // Funktion zur Steuerung der Checkboxen
    function toggleAllLayers(selectAll, table) {
      document.querySelectorAll('.leaflet-control-layers-overlays input[type="checkbox"]').forEach(checkbox => {
          if (checkbox.checked !== selectAll) {
              checkbox.click(); // Simuliert ein Nutzer-Klick-Event
          }
      });

      if (selectAll) {
        table.clearFilter(true);
      }
    }

    function createMarkers(php_touren, allIcons, myIcon, group, nposts) {
          
      let marker = new Array();
      let j = 0;
      let icn, grp;

      php_touren.forEach(tour => { 
        
        let found = false;

        for (let index = 0; index < allIcons.length; index++) {    
            if (tour["category"] == allIcons[index]['icon']) {
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
        if (tour["img"] == false || tour["img"] == null || tour["img"] == '') {
          marker[j].bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br>' + tour["excerpt"] + '</a>');
        } else {
          marker[j].bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br><img src="' + tour["img"] + '">' + tour["excerpt"] + '</a>');
        }
        marker[j].addTo(grp);
        j++;
      });

      let bounds = L.latLngBounds();
      marker.forEach(m => {
          let lat_lng = m._latlng;
          bounds.extend(lat_lng);
      });

      return bounds;
    }
    
    if (window.g_wp_postmap_path.number === "1" && php_touren.length > 0) {
      //let mobile = (/iphone|ipod|android|webos|ipad|iemobile|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
      let postmap_url = window.g_wp_postmap_path.path;
      let hasTable = window.g_wp_postmap_path.hasTable == '1' ? true : false;
      let numberOfBoxes = 1;
      let allMaps = [ numberOfBoxes-1 ];
      let m = 0;
      let pageVars = window.pageVarsForJs[m];
      let table = {};
      let tableMapMoveSelector = 'Stadt'; // Mind: This might be i18n or other values
      let LayerSupportGroup = {};
      let allIcons = {};
      let nposts = [];
      let myIcon = new Array();
      let group = new Array();
      let bounds = {};

      updateCSS(pageVars);
        
      Promise.all([
        import('../settings/category_mapping.json'),
        import('./leafletMapClass.js')
      ]).then(([category_mapping, LeafletMap]) => {

        allMaps[m] = new LeafletMap.LeafletMap(m, 'map10_img' );
                        
        // Icons definieren aus der Json variable übernehmen
        allIcons = category_mapping['mapping']; // allIcons ist ident zu dem was aus json kommt
        nposts = Array( allIcons.length ).fill(0);
        //let myIcon = new Array();
        //let group = new Array();
        // Creating markergroups ----------------------- 
        LayerSupportGroup = L.markerClusterGroup.layerSupport();
        LayerSupportGroup.addTo(allMaps[m].map);

        allIcons.forEach( function(icon, index) {
          myIcon[index] = allMaps[m].setIcon(postmap_url,icon['icon-png'],'marker-shadow.png');
          group[index] = L.layerGroup();
        }); 
        
        // Creating markers -----------------------
        bounds = createMarkers(php_touren, allIcons, myIcon, group, nposts);
        allMaps[m].map.fitBounds(bounds, { padding: [50, 50] });

        group.forEach( function(sgrp, index) {
            LayerSupportGroup.checkIn(group[index]);
            group[index].addTo(allMaps[m].map);
        }); 

        allIcons.forEach( function(icon, index) {
          allMaps[m].controlLayer.addOverlay(group[index], '<img class="layerIcon" src="' + postmap_url + icon['icon-png'] + '"/> '+icon['category']+' (' + nposts[index] + ')');  
        });

        // Eigenes Element mit "Alles" und "Nichts" hinzufügen
        // Warten, bis Leaflet das Control gerendert hat
        setTimeout(() => {
          const layersContainer = allMaps[m].controlLayer._container;
          const overlaysList = layersContainer.querySelector('.leaflet-control-layers-overlays');

          if (overlaysList) {
              // Buttons für "Alles" & "Nichts" direkt über die Kategorien einfügen
              const selectButtons = document.createElement('div');
              selectButtons.className = "layer-select-buttons";
              selectButtons.innerHTML = `
                  <button type="button" id="selectAllBtn">` + allMaps[m].i18n('All') + `</button>
                  <button type="button" id="deselectAllBtn">` + allMaps[m].i18n('None') + `</button>
              `;
              
              overlaysList.parentNode.insertBefore(selectButtons, overlaysList);

              // Event-Listener für die Buttons
              document.getElementById('selectAllBtn').addEventListener('click', () => toggleAllLayers(true, table));
              document.getElementById('deselectAllBtn').addEventListener('click', () => toggleAllLayers(false, table));

              // CSS anpassen, damit die Buttons nur bei Hover sichtbar sind
              const style = document.createElement('style');
              style.innerHTML = `
                  .leaflet-control-layers {
                      transition: opacity 0.2s ease-in-out;
                  }
                  .leaflet-control-layers:not(:hover) .layer-select-buttons {
                      display: inherit;
                  }
                  .layer-select-buttons {
                      padding-bottom: 3px;
                      text-align: center;
                      background: rgba(255, 255, 255, 0.8);
                      border-bottom: 1px solid #ccc;
                  }
                  .layer-select-buttons button {
                      cursor: pointer;
                      font-size: inherit;
                  }
              `;
              document.head.appendChild(style);
          }
        }, 100);

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

      if (hasTable) {
        import('./tabulatorClass.js').then((MyTabulatorClass) => {
          let tabulator = new MyTabulatorClass.MyTabulatorClass({});
          table = tabulator.createTable("#post_table", pageVars );

          table.on("dataFiltered", function(filters, rows, event){
            // TODO : filter markers in map, 
            if (filters.length == 0) {
              // show all markers again. TBD: What has to be done if this event appears on filtered data.
              //LayerSupportGroup.removeLayers();
              //LayerSupportGroup._layers = LayerSupportGroup.getLayers()
              //bounds = createMarkers(php_touren, allIcons, myIcon, group, nposts);
              //allMaps[m].map.fitBounds(bounds, { padding: [50, 50] });
              //toggleAllLayers(true, table);
              return;
            }
            //LayerSupportGroup.clearLayers();
            // clearLayer : beachte dabei die möglichen wechselwirkungen
            // filter php_touren to another Array
            // show this array with createMarkers
            // set bounds to the filtered subset of Markers
            
          });

          // click auf die Reihe zentriert die Karte auf den Marker, zoom bleibt gleich
          table.on("rowClick", function(e, row, data){
            let url = row._row.data[tableMapMoveSelector];
            // get lat lon from google url which is like so "https://www.google.com/maps/place/47.607203,12.887333/@47.607203,12.887333,9z"
            if (!url) return;
            let match = url.match(/@?(-?\d+\.\d+),\s*(-?\d+\.\d+)/);
            let lat = parseFloat(match[1]);
            let lng = parseFloat(match[2]);
            if (lat === 0 || lng === 0) return;
            allMaps[m].mapFlyTo([lat, lng]);
          })

        });
      } // end if hasTable
      
    } // end if numberOfBoxes
};

// Export oder Nutzung im Backend
export { mainLogic };
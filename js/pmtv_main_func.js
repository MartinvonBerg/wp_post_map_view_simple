import { isValidAspectRatio, isValidCssSize } from './libs/cssCheckLib.js';
import { loadSettings } from './libs/loadJSON.js';

/**
 * Main logic function for the PostMapTableView plugin.
 * 
 * @param {Window} window - The global window object.
 * @param {Document} document - The global document object.
 * @param {undefined} undefined - An undefined value.
 */
function mainLogic (window, document, undefined) {
    "use strict";
  
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

    /**
     * Toggles all layers in the layer control on or off. If all layers are
     * supposed to be shown, the header filter of the table is reset.
     * @param {boolean} selectAll - Show all layers if true, hide all layers if false.
     * @param {Object} table - The table object (not used yet).
     */
    function toggleAllLayers(selectAll, table) {
      document.querySelectorAll('.leaflet-control-layers-overlays input[type="checkbox"]').forEach(checkbox => {
          if (checkbox.checked !== selectAll) {
              checkbox.click(); // Simuliert ein Nutzer-Klick-Event
          }
      });

      //if (selectAll) {
        //table.clearHeaderFilter(true);
      //}
    }

    /**
     * Function to create markers from php_touren, allIcons, myIcon and nposts.
     * @param {array} php_touren - the array of all posts with their gps data
     * @param {array} allIcons - the array of all icons with their corresponding categories
     * @param {array} myIcon - the array of all icons
     * @param {array} nposts - the array to store the number of posts for each icon
     * @returns {array} markersInGroups - an array of arrays with all markers grouped by their icon
     */
    function createMarkers(php_touren, allIcons, myIcon, nposts) {
      let markersInGroups = [];
      /*
      php_touren.forEach(tour => {
          let grpIndex = allIcons.findIndex(icon => icon.icon === tour.category);
          grpIndex = grpIndex !== -1 ? grpIndex : allIcons.length - 1;
          
          nposts[grpIndex]++;
          let icon = myIcon[grpIndex];
          let marker = new L.Marker(tour.coord, { title: tour.title, icon });
  
          let popupContent = `<a href="${tour.link}"><b>${tour.title}</b><br>`;
          if (tour.img) popupContent += `<img src="${tour.img}">`;
          popupContent += `${tour.excerpt}</a>`;
          marker.bindPopup(popupContent);
  
          (markersInGroups[grpIndex] ||= []).push(marker);
      });
      */
      php_touren.forEach(tour => {
        let singleMarker;
        let found = false;
        let grpIndex = 0;
        let icn;
        let allIconsLength = allIcons.length;

        for (let index = 0; index < allIconsLength; index++) {    
            if (tour["category"] == allIcons[index]['icon']) {
                icn = myIcon[index];
                nposts[index]++;
                found = true;
                grpIndex = index;
                break;
            };
        };

        if ( ! found ) { // sollte eigentlich der Default sein
            icn = myIcon[ allIconsLength-1 ];
            nposts[ allIconsLength-1]++;
        }
        singleMarker = new L.Marker(tour["coord"], { title: tour["title"], icon: icn });

        if (tour["img"] == false || tour["img"] == null || tour["img"] == '') {
          singleMarker.bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br>' + tour["excerpt"] + '</a>');
        } else {
          singleMarker.bindPopup('<a href="' + tour["link"] + '"><b>' + tour["title"] + '</b><br><img src="' + tour["img"] + '">' + tour["excerpt"] + '</a>');
        }
    
        if (markersInGroups[grpIndex] == undefined) {
          markersInGroups[grpIndex] = new Array();
        }
        markersInGroups[grpIndex].push(singleMarker);
      });
      return markersInGroups;
    }

    /**
     * Set the map view to the bounds of all markers in the given array of marker groups.
     * @param {L.Map} map - the map object
     * @param {array} markersArray - array of arrays with all markers grouped by their icon.
     * @param {array} [padding=[50, 50]] - padding between the map view and the markers.
     */
    function fitMaptoMarkers(map, markersArray, padding = [50, 50], checkZoom = false) {
      let bounds = L.latLngBounds();
      let marker = new Array();
      marker = markersArray.flat();
      marker.forEach(m => {
          let lat_lng = m._latlng;
          bounds.extend(lat_lng);
      });
      let zoomBefore = map.getZoom();
      map.fitBounds(bounds, { padding: padding });
      let zoomAfter = map.getZoom();
      let zoomDiff = zoomAfter - zoomBefore;  
      if (checkZoom && zoomDiff > 1) {
        map.setZoom(zoomBefore+3);
      }
    }

    /**
     * Create a marker cluster group for the given map and array of markers grouped by their icon.
     * @param {object} mapClass - the map class object
     * @param {array} markersInGroups - array of arrays with all markers grouped by their icon.
     * @param {L.LayerSupport.Group} LayerSupportGroup - the layer support group to add the marker cluster group to.
     * @param {array} allIcons - array of all icons.
     * @param {string} iconUrl - the url of the icon folder.
     * @param {array} nposts - array with the number of posts for each icon.
     * @return {array} - array of layer groups with all markers grouped by their icon.
     */
    function createMarkerClusterGroup( mapClass, markersInGroups, LayerSupportGroup, allIcons, iconUrl, nposts ) {
      // ---add the marker cluster group to map --------------
      let group = new Array();
      
      allIcons.forEach( function(icon, index) {
        if (markersInGroups[index] != undefined) {
          group[index] = L.layerGroup( markersInGroups[index]); // arrayOfMarkers fehlt hier
        }	else {
          group[index] = L.layerGroup();
        }
        if (nposts[index] > 0) {
          mapClass.controlLayer.addOverlay(group[index], '<img class="layerIcon" src="' + iconUrl + icon['icon-png'] + '"/> '+icon['category']+' (' + nposts[index] + ')');
        }

      });
      LayerSupportGroup.addTo(mapClass.map);

      group.forEach( function(sgrp, index) {
        LayerSupportGroup.checkIn(group[index]);
        group[index].addTo(mapClass.map);
      });

      return group;
    }
    
    // main logic
    if (window.g_wp_postmap_path.number === "1" && php_touren.length > 0) {
      let postmap_url = window.g_wp_postmap_path.path;
      let hasTable = window.g_wp_postmap_path.hasTable == '1' ? true : false;
      let hasMap = window.g_wp_postmap_path.hasMap == '1' ? true : false;
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

      pageVars.type = window.g_wp_postmap_path.type;

      updateCSS(pageVars);

      /**
       * Removes all Marker Layers and the control layers 
       * @param {Object} event - contains the operation op to perform
       * @param {Array} groups - the groups to remove
       * @global {Array} allMaps
       * @global {Object} LayerSupportGroup
       */
      function toggleGroup(event, groups = null) {
        let op = event.op;
        if (groups == null || op != 'removeLayer') {
          return;
        }
        // removes all Marker Layers and the control layers 
        //if (op == 'removeLayer') { 
        groups.forEach( function(group, index) {
          //console.log(op + " " + index);
          LayerSupportGroup[op](group);
          allMaps[m].map.removeLayer(groups[index]); // Entfernt den Layer von der Karte
          allMaps[m].controlLayer.removeLayer(groups[index]); // Entfernt den Overlay-Eintrag
        })
        //}
      }
      
      // --- Generate Map with Markers
      if (hasMap) {
        Promise.all([
          //import('../settings/category_mapping.json'),
          import('./leafletMapClass.js')
        ]).then( async ([LeafletMap]) => {

          allMaps[m] = new LeafletMap.LeafletMap(m, 'map10_img' );
                          
          // Define Icons from imported json file
          let settingsUrl = '';
          if (pageVars.settingsFile) {
            settingsUrl = pageVars.settingsFile;
          } else {
            settingsUrl = postmap_url.replace('images/','') + 'settings/category_mapping.json';
          }
          let category_mapping = await loadSettings(settingsUrl);
          allIcons = category_mapping['mapping']; // allIcons ist ident zu dem was aus json kommt
          // append the default from the json to allIcons array
          allIcons.push(category_mapping['default']);
          
          nposts = Array( allIcons.length ).fill(0);
          allIcons.forEach( function(icon, index) {
            myIcon[index] = allMaps[m].setIcon(postmap_url,icon['icon-png'],'marker-shadow.png'); 
          }); 
          
          // Creating markers as an array of arrays -----------------
          let markersInGroups = new Array(); // possible return value
          markersInGroups = createMarkers(php_touren, allIcons, myIcon, nposts);
          
          // ---add the marker cluster group to map --------------
          LayerSupportGroup = L.markerClusterGroup.layerSupport();
          group = createMarkerClusterGroup( allMaps[m], markersInGroups, LayerSupportGroup, allIcons, postmap_url, nposts)
          
          // get the bounds and Fit map to it
          fitMaptoMarkers(allMaps[m].map, markersInGroups, [50, 50]);

          // --- show the title of the included markers in the tooltip ---
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
          // --- remove the tooltip ---
          LayerSupportGroup.on('clustermouseout', function (a) {
              a.propagatedFrom.bindTooltip('').closeTooltip();
          });

          // add a function to the control to get the active base layer
          L.Control.Layers.include({
            /**
             * Retrieves the currently active base layer from the map.
             * Iterates over all layers managed by the control to determine which base layer
             * is currently present on the map, and returns its name.
             * 
             * @returns {string} The name of the active base layer.
             */
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
          
          // set the background color of the map according to the base layer
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

          // set the initial bounds and add the buttons "All" & "None" to the control. The Button Text is defined in the json file
          window.addEventListener("load", function() {
            let newBounds = allMaps[m].map.getBounds();
            allMaps[m].setBounds(newBounds);

            // Eigenes Element mit "Alles" und "Nichts" hinzufügen
            // Warten, bis Leaflet das Control gerendert hat
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
          })

        }) // end Promise - then
      }
      
      // --- Generate the Table with Tabulator and Filter markers on the Map ---
      if (hasTable) {
        import('./tabulatorClass.js').then((MyTabulatorClass) => {
          let tabulator = new MyTabulatorClass.MyTabulatorClass({});
          table = tabulator.createTable("#post_table", pageVars );

          if (hasMap) {
            table.on("dataFiltered", function(filters, rows){
              
              if (filters.length == 0 && (document.activeElement.id === 'selectAllBtn')) { // || document.activeElement.id === ''
                return;
              } 

              if (document.activeElement.type === 'search') {
                toggleGroup({ op: 'removeLayer' }, group); // Vorab alle Layer entfernen
            
                let markersInGroups = [];
                nposts = Array(allIcons.length).fill(0);
            
                if (filters.length > 0 && rows.length > 0) {
                    // Gefilterte Touren ermitteln
                    let filtered_touren = php_touren.filter(tour =>
                        rows.some(row => parseInt(row._row.data.Nr) === tour.id)
                    );
            
                    // Marker für gefilterte Touren erstellen
                    markersInGroups = createMarkers(filtered_touren, allIcons, myIcon, nposts);

                } else if (filters.length === 0 && rows.length > 0) {
                    // Alle Marker erneut hinzufügen
                    markersInGroups = createMarkers(php_touren, allIcons, myIcon, nposts);

                } else {
                    return; // Keine Marker hinzuzufügen -> Abbrechen
                }
            
                // Clustergruppe neu erzeugen und hinzufügen
                LayerSupportGroup = L.markerClusterGroup.layerSupport();
                group = createMarkerClusterGroup(allMaps[m], markersInGroups, LayerSupportGroup, allIcons, postmap_url, nposts);
            
                // Karte an neue Marker anpassen
                fitMaptoMarkers(allMaps[m].map, markersInGroups, [50, 50] , true );
              }

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
          }

        });
      } // end if hasTable
      
    } // end if numberOfBoxes abd main Logic
};

// Export oder Nutzung im Backend
export { mainLogic };
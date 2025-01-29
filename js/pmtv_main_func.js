function mainLogic (window, document, $, undefined) {
    "use strict";
    
    if (window.g_wp_postmap_path.number === "1" && php_touren.length > 0) {
      let mobile = (/iphone|ipod|android|webos|ipad|iemobile|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
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
        let nposts = Array( allIcons.length ).fill(0); // TODO: change var name
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
      
      //let control = L.control.layers(allMaps[m].baseLayers, null, { collapsed: true });

      allIcons.forEach( function(icon, index) {
        allMaps[m].controlLayer.addOverlay(group[index], '<img class="layerIcon" src="' + postmap_url + icon['icon-png'] + '"/> '+icon['category']+' (' + nposts[index] + ')');  
      });
    
      //control.addTo(allMaps[m].map);

      group.forEach( function(sgrp, index) {
          group[index].addTo(allMaps[m].map);
      }); 
      })
        
    
        
    }

};

// Export oder Nutzung im Backend
export { mainLogic };
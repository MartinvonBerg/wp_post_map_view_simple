//import category_mapping from '../settings/category_mapping.json'; // setting sind in der globalen variable category_mapping

function mainLogic (window, document, $, undefined) {
    "use strict";
    
    let g_wp_postmap_path = window.g_wp_postmap_path.path;
    let mobile = (/iphone|ipod|android|webos|ipad|iemobile|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
    // get the data from the php variable with all tours or posts
    let touren = Object.values(php_touren);
    
    if (window.g_wp_postmap_path.number === "1" && touren.length > 0) {
        
      Promise.all([
        import('../settings/category_mapping.json'),
        import('leaflet') // TODO: hier muss die LeafletMapClass importiert werden
        //import('module-b')
      ]).then(([category_mapping, L]) => {
        
        // Icons definieren aus der Json variable übernehmen
        let allIcons = category_mapping['mapping']; // allIcons ist ident zu dem was aus php kommt
        
        let myIcon = new Array();

        allIcons.forEach( function(icon, iconindex) {
            myIcon[iconindex] = L.icon({
              iconUrl: g_wp_postmap_path + icon['icon-png'],
              iconSize: [32, 32],
              iconAnchor: [0, 0],
              popupAnchor: [0, 0],
              //shadowUrl: 'icon-shadow.png',
              //shadowSize: [100, 95],
              //shadowAnchor: [22, 94]
            });            
          });
      })
        
    
        
    }

};

// Exporte oder Nutzung im Backend
export { mainLogic };
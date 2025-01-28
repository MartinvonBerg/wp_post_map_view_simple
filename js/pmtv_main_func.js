import category_mapping from '../settings/category_mapping.json'; // setting sind in der globalen variable category_mapping
import { getIconMappingArray } from './libs/get_all_Icon_Mappging.js'; // getIconMappingArray ist in der globalen variable getIconMappingArray

function mainLogic (window, document, $, undefined) {
    "use strict";
    // Hier kommt der Code hin
    let numberOfboxes = document.querySelectorAll('[id=map10_img]').length;
    let wp_plugin_path = g_wp_postmap_path.g_wp_postmap_path;
    let allIcons = getIconMappingArray(category_mapping); // allIcons ist ident zu dem was aus php kommt
    
    if (numberOfboxes == 1) {
        
    }

};

// Exporte oder Nutzung im Backend
export { mainLogic };
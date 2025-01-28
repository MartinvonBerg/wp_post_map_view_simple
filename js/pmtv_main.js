import { mainLogic } from './pmtv_main_func.js';

// IIFE für das Frontend
(function (window, document, jQuery, undefined) {
    mainLogic(window, document, jQuery, undefined); // Standardaufruf im Frontend
})(window, document, jQuery); // IIFE für das Frontend
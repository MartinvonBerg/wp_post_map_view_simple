import { mainLogic } from './pmtv_main_func.js';

// IIFE für das Frontend
(function (window, document, undefined) {
    mainLogic(window, document, undefined); // Standardaufruf im Frontend
})(window, document); // IIFE für das Frontend
import { mainLogic } from './pmtv_main_func.js';

// IIFE für das Frontend
(function (window, document) {
    mainLogic(window, document); // Standardaufruf im Frontend
})(window, document); // IIFE für das Frontend
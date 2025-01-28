/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/libs/get_all_Icon_Mappging.js":
/*!******************************************!*\
  !*** ./js/libs/get_all_Icon_Mappging.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   getIconMappingArray: () => (/* binding */ getIconMappingArray)\n/* harmony export */ });\nfunction getIconMappingArray(data) {\r\n    // Überprüfen, ob data ein Array ist und 'mapping' enthält\r\n    if (typeof data !== 'object' || !data.mapping || typeof data.mapping !== 'object') {\r\n        return []; // Leeres Array zurückgeben, wenn die Eingabe ungültig ist\r\n    }\r\n\r\n    const allIcons = [];\r\n\r\n    // Mapping-Daten verarbeiten\r\n    for (const key in data.mapping) {\r\n        if (data.mapping.hasOwnProperty(key)) {\r\n            const mapping = data.mapping[key];\r\n            const iconPng = mapping['icon-png'] || data.default['icon-png'];\r\n            const icon = mapping['icon'] || data.default['icon'];\r\n            const category = mapping['category'] || data.default['category'];\r\n\r\n            allIcons.push([iconPng, icon, category]);\r\n        }\r\n    }\r\n\r\n    return allIcons;\r\n}\r\n\r\n\n\n//# sourceURL=webpack://post-map-table-view/./js/libs/get_all_Icon_Mappging.js?");

/***/ }),

/***/ "./js/pmtv_main.js":
/*!*************************!*\
  !*** ./js/pmtv_main.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _pmtv_main_func_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pmtv_main_func.js */ \"./js/pmtv_main_func.js\");\n\r\n\r\n// IIFE für das Frontend\r\n(function (window, document, jQuery, undefined) {\r\n    (0,_pmtv_main_func_js__WEBPACK_IMPORTED_MODULE_0__.mainLogic)(window, document, jQuery, undefined); // Standardaufruf im Frontend\r\n})(window, document, jQuery); // IIFE für das Frontend\n\n//# sourceURL=webpack://post-map-table-view/./js/pmtv_main.js?");

/***/ }),

/***/ "./js/pmtv_main_func.js":
/*!******************************!*\
  !*** ./js/pmtv_main_func.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   mainLogic: () => (/* binding */ mainLogic)\n/* harmony export */ });\n/* harmony import */ var _settings_category_mapping_json__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../settings/category_mapping.json */ \"./settings/category_mapping.json\");\n/* harmony import */ var _libs_get_all_Icon_Mappging_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./libs/get_all_Icon_Mappging.js */ \"./js/libs/get_all_Icon_Mappging.js\");\n // setting sind in der globalen variable category_mapping\r\n // getIconMappingArray ist in der globalen variable getIconMappingArray\r\n\r\nfunction mainLogic (window, document, $, undefined) {\r\n    \"use strict\";\r\n    // Hier kommt der Code hin\r\n    let numberOfboxes = document.querySelectorAll('[id=map10_img]').length;\r\n    let wp_plugin_path = g_wp_postmap_path.g_wp_postmap_path;\r\n    let allIcons = (0,_libs_get_all_Icon_Mappging_js__WEBPACK_IMPORTED_MODULE_1__.getIconMappingArray)(_settings_category_mapping_json__WEBPACK_IMPORTED_MODULE_0__);\r\n    \r\n    if (numberOfboxes == 1) {\r\n        \r\n    }\r\n\r\n};\r\n\r\n// Exporte oder Nutzung im Backend\r\n\n\n//# sourceURL=webpack://post-map-table-view/./js/pmtv_main_func.js?");

/***/ }),

/***/ "./settings/category_mapping.json":
/*!****************************************!*\
  !*** ./settings/category_mapping.json ***!
  \****************************************/
/***/ ((module) => {

eval("module.exports = /*#__PURE__*/JSON.parse('{\"default\":{\"category\":\"Reisebericht\",\"icon\":\"travel\",\"icon-png\":\"campingcar.png\"},\"mapping\":{\"Trekk\":{\"category\":\"Trekking\",\"icon\":\"hiking\",\"icon-png\":\"hiking.png\"},\"bike&&hike\":{\"category\":\"Bike-Hike\",\"icon\":\"bike-hike\",\"icon-png\":\"mountainbiking-3.png\"},\"Radfahren\":{\"category\":\"Radfahren\",\"icon\":\"cycling\",\"icon-png\":\"cycling.png\"},\"MTB\":{\"category\":\"MTB\",\"icon\":\"MTB\",\"icon-png\":\"MTB.png\"},\"Wander\":{\"category\":\"Wandern\",\"icon\":\"hiking\",\"icon-png\":\"hiking2.png\"},\"Bergtour\":{\"category\":\"Bergtour\",\"icon\":\"mountain\",\"icon-png\":\"peak2.png\"},\"skitour\":{\"category\":\"Skitour\",\"icon\":\"skiing\",\"icon-png\":\"skiing.png\"},\"Paddeln\":{\"category\":\"Paddeln\",\"icon\":\"kayaking\",\"icon-png\":\"kayaking2.png\"},\"reisebericht\":{\"category\":\"Reisebericht\",\"icon\":\"travel\",\"icon-png\":\"campingcar.png\"}}}');\n\n//# sourceURL=webpack://post-map-table-view/./settings/category_mapping.json?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./js/pmtv_main.js");
/******/ 	
/******/ })()
;
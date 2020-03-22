"use strict";

(function (window, document, undefined) {
  
     // 1. Initialize fotorama manually.
    var $fotoramaDiv = jQuery('#fotorama').fotorama();

    // 2. Get the API object.
    var fotorama = $fotoramaDiv.data('fotorama');

    // 3. Inspect it in console.
    //console.log(fotorama);
	
	jQuery('#move').click(function() {
		var index = fotorama.activeIndex;
		var length = fotorama.size-1;
		if (index == length) {
			index = -1;
			}
		index++;
		fotorama.show(index);
		//console.log('Bild verschoben');
	});
	
	jQuery('#turn').click(function() {
		fotorama.reverse();
		//console.log('umgedreht');
	});
  
  var myIcon = L.icon({
	   iconUrl: '../wp-content/plugins/leaflet/icon.png',
	   iconSize: [64, 39],
	   iconAnchor: [0, 0],
	   popupAnchor: [0, 0],
	   //shadowUrl: 'icon-shadow.png',
	   //shadowSize: [100, 95],
	   //shadowAnchor: [22, 94]
			   });       

  var planes = [
       ["Bild1",39.790748, 2.700928],
	   ["Bild2",39.79, 2.8]
	   //["7C6CA1",-41.49413,173.5421],
	   //["7C6CA2",-40.98585,174.50659],
	   //["C81D9D",-40.93163,173.81726],
	   //["C82009",-41.5183,174.78081],
	   //["C82081",-41.42079,173.5783],
	   //["C820AB",-42.08414,173.96632]
	   //["C820B6",-41.51285,173.53274]
	   ];

  function showinfo() {
	    var wert = this.options.alt;
		//console.log("Id = ",Idmarker);
		//console.log(this);
		//alert("Marker: " + wert);
		jQuery("#marker").text("Marker " + wert + " geklickt");
		//fotorama.show(3);
		}
		
  // Full list options at "leaflet-elevation.js"
  var elevation_options = {

    // Default chart colors: theme lime-theme, magenta-theme, ...
    theme: "steelblue-theme",
	downloadLink: false,
	url: "waypoints.gpx",
	

    // Chart container outside/inside map container
    detached: true,
	
    // if (detached), the elevation chart container
    elevationDiv: "#elevation-div",

    // if (!detached) autohide chart profile on chart mouseleave
    autohide: false,

    // if (!detached) initial state of chart profile control
    collapsed: false,

    // if (!detached) control position of chart on one of map corners
    //position: "topright",
	position: "bottomleft",

    // Autoupdate map center on chart mouseover.
    followMarker: false,

    // Chart distance/elevation units.
    imperial: false,

    // [Lat, Long] vs [Long, Lat] points. (leaflet default: [Lat, Long])
    reverseCoords: false,

    // Summary track info style: "line" || "multiline" || false,
    summary: 'line',

  };

  // Instantiate map (leaflet-ui).
  var map = new L.Map('map', { mapTypeId: 'topo', center: [41.4583, 12.7059], zoom: 10, locateControl: false, editInOSMControl: false, searchControl: false, pegmanControl:false, printControl:false});

  // Instantiate elevation control.
  var controlElevation = L.control.elevation(elevation_options);

  // Load track from url (allowed data types: "*.geojson", "*.gpx")
  controlElevation.load("../wp-content/plugins/leaflet/Malle.gpx");
  //controlElevation.load(elevation_options.url);
  
  controlElevation.addTo(map);
  
  for (var i = 0; i < planes.length; i++) {
	   var marker = new L.marker([planes[i][1],planes[i][2]], {icon: myIcon, riseOffset: 100, riseOnHover: true, alt: planes[i][0], Idmarker: i}).addTo(map).on("click",showinfo) ;
		};
   /*		 
  jquery("p").click(function(){
     map.flyTo(new L.LatLng(41.4583, 12.7059));
	 console.log("geklickt");
    });
	*/
	
})(window, document);
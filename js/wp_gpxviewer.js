"use strict";

(function (window, document, undefined) {
    var marker = null, map;
    var mapdiv = document.getElementById("map0");
    var img = document.querySelector("#Bilder img");
    var figcaption = document.querySelector("#Bilder figcaption");
    var images = [], nr = 0, ct = 0;
    window.JB = window.JB || {};
    window.JB.GPX2GM = window.JB.GPX2GM || {};
    
    function setImage(nr) {
        if(nr < 0) nr = images.length - 1;
        if(nr >= images.length) nr = 0;
        //console.log(nr);
        img.src = images[images[nr].marker.nr].src;
        figcaption.innerHTML = images[nr].text;
        if(marker) JB.RemoveElement(marker);
        marker = map.Marker({lat:images[nr].coord.lat,lon:images[nr].coord.lon},JB.icons.Kreis)[0];
        fotorama.show(nr);
        return nr;
        }
        
    JB.GPX2GM.callback = function(pars) {
    //console.log(pars.type);
    if(pars.type == "Map_n") {
        map = mapdiv.makeMap.GetMap();
        }
    if(pars.type == "created_Marker_Bild") {
        images[ct] =  {src: pars.src, text: pars.text, marker: pars.marker, coord: pars.coord};
        pars.marker.nr = ct;
        if(ct==0) {
        setImage(ct);
        }
        ct++;
        return;
    }
    if(pars.type == "click_Marker_Bild") {
        nr = pars.marker.nr;
        nr = setImage(nr);
        return false;
    }
    return true;
    }

    // 1. Initialize fotorama manually.
    var $fotoramaDiv = jQuery('#fotorama').fotorama();
    // 2. Get the API object.
    var fotorama = $fotoramaDiv.data('fotorama');
  
    jQuery('.fotorama').on('fotorama:showend ',
        function (e, fotorama, extra) {
            var index = fotorama.activeIndex;
            if(marker) JB.RemoveElement(marker);
            if (g_numb_gpxfiles>0) {
                marker = map.Marker({lat:images[index].coord.lat,lon:images[index].coord.lon},JB.icons.Kreis)[0];
            }
        });

    jQuery(window).load(function ()
    {
    var i = setInterval(function ()
    {
        clearInterval(i);
        // safe to execute your code here
        jQuery("#map_headmap0 > button").css("background","lightgray");
        jQuery("#map_headmap0 > button").append('Alles anzeigen ');
        jQuery(".JBinfofenster").css("top",'');
        jQuery(".JBinfofenster").css("left",'');
        jQuery(".JBinfofenster").css("bottom",'20px');
        jQuery(".JBinfofenster").css("right",'10px');
        }, 100); });	
        
})(window, document);
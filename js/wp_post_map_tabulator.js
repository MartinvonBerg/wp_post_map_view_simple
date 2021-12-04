/**
 * JS for the table with tabulator.info
 */

(function (window, document, $, undefined) {
    "use strict";

    function HeaderFilter(headerValue, rowValue, rowData, filterParams){
        //headerValue - the value of the header filter element
        //rowValue - the value of the column in this row
        //rowData - the data for the row being filtered
        //filterParams - params object passed to the headerFilterFuncParams property
        let rowInt = parseFloat(rowValue);
        let headerInt = parseFloat(headerValue);
        return rowInt >= headerInt; //must return a boolean, true if it passes the filter.
    }

    //document.getElementById("tablereset").onclick = resetHeaderFilter();
    var el = document.getElementById("tablereset");

    if ( el != null ) {
        el.onclick = function(){
            table.clearFilter(true);
        }
        
        // Erzeugung der Tabelle aus dem HTML-Code
        var table = new Tabulator("#post_table", {
            layout:"fitData",
            locale:true,
            langs:{
                "de-de":{
                    "pagination":{
                        "page_size":"Anzahl Touren", //label for the page size select element
                        "page_title":"Zeige",//tooltip text for the numeric page button, appears in front of the page number (eg. "Show Page" will result in a tool tip of "Show Page 1" on the page 1 button)
                        "first":"Erste", //text for the first page button
                        "first_title":"Erste", //tooltip text for the first page button
                        "last":"Letzte",
                        "last_title":"Letzte",
                        "prev":"Nächste",
                        "prev_title":"Nächste",
                        "next":"Nächste",
                        "next_title":"Nächste Seite",
                        "all":"Alle",
                    },
                }
            },
            pagination:"local",
            paginationSize:20,
            paginationSizeSelector:[5, 10, 20, 50, true],
            movableColumns:true,
            columns:[
                {title:"Nr", field:"Nr", },
                {title:"Titel", field:"Titel", formatter:"html", headerFilter:"input"},
                {title:"Kategorie", field:"Kategorie", headerFilter:"input"},
                {title:"Distanz", field:"Distanz",  hozAlign:"right", headerFilterPlaceholder:">...km", headerFilter:"input", headerFilterFunc:HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                {title:"Aufstieg", field:"Aufstieg", hozAlign:"right",headerFilterPlaceholder:">...Hm", headerFilter:"input", headerFilterFunc:HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                {title:"Abstieg", field:"Abstieg", hozAlign:"right",headerFilterPlaceholder:">...Hm", headerFilter:"input", headerFilterFunc:HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                {title:"Land", field:"Land", headerFilter:"input"},
                {title:"Region", field:"Region", headerFilter:"input"},
                {title:"Stadt", field:"Stadt", formatter:"html", headerFilter:"input"},
            ],
        });
    } else {
        document.getElementsByClassName( 'mvb-post-table')[0].style.display = 'none';
    }

})(window, document, jQuery);
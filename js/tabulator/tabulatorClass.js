//tabulatorClass.js
/*!
	tabulatorClass V 1.0.1
	license: GPL 2.0
	Martin von Berg
*/

// imports
//import { Tabulator, EditModule, FilterModule, FormatModule, HtmlTableImportModule, InteractionModule, MenuModule, PageModule, SortModule } from 'tabulator-tables';
import { TabulatorFull as Tabulator } from 'tabulator-tables';
//import 'tabulator-tables/dist/css/tabulator.min.css';

// AccessorModule, AjaxModule, CalcComponent, CellComponent, ClipboardModule, ColumnCalcsModule, ColumnComponent, DataTreeModule, DownloadModule, EditModule, ExportModule, FilterModule, FormatModule, FrozenColumnsModule, FrozenRowsModule, GroupComponent, GroupRowsModule, HistoryModule, HtmlTableImportModule, ImportModule, InteractionModule, KeybindingsModule, MenuModule, Module, MoveColumnsModule, MoveRowsModule, MutatorModule, PageModule, PersistenceModule, PopupModule, PrintModule, PseudoRow, RangeComponent, ReactiveDataModule, Renderer, ResizeColumnsModule, ResizeRowsModule, ResizeTableModule, ResponsiveLayoutModule, RowComponent, SelectRangeModule, SelectRowModule, SheetComponent, SortModule, SpreadsheetModule, Tabulator, TabulatorFull, TooltipModule, ValidateModule
// exports
export {MyTabulatorClass};

// class TabulatorClass
class MyTabulatorClass {
    
    options = {};

    constructor(options={}) {
        this.options = options;
        this.locale = navigator.language.toLowerCase();
    }

    HeaderFilter(headerValue, rowValue){
        //headerValue - the value of the header filter element
        //rowValue - the value of the column in this row
        //rowData - the data for the row being filtered
        //filterParams - params object passed to the headerFilterFuncParams property
        let rowInt = parseFloat(rowValue, this.locale);
        let headerInt = parseFloat(headerValue, this.locale);
        return rowInt >= headerInt; //must return a boolean, true if it passes the filter.
    }

    createTable(tableId, options={}){
        let page_size = options.tablePageSize ? options.tablePageSize : 20;
        let tableHeight = options.tableHeight ? options.tableHeight : '0px';
        let tabulatorOptions = {};
        let tableMapMoveSelector = window.g_wp_postmap_path.tableMapMoveSelector;

        if (options.type === 'tourmap') {
            tabulatorOptions = {
                layout: "fitDataStretch",
                locale: true,
                height: tableHeight,
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
                pagination: "local",
                paginationSize: page_size,
                paginationSizeSelector:[5, 10, page_size, 50, true],
                movableColumns:false,
                columns:[
                    {title:"Nr", field:"Nr", },
                    {title:"Titel", field:"Titel", formatter:"html", headerFilter:"input"},
                    {title:"Kategorie", field:"Kategorie", headerFilter:"input"},
                    {title:tableMapMoveSelector, field:tableMapMoveSelector, formatter:"html"},
                ],
            };
        } else {
            tabulatorOptions = {
                layout: "fitData",
                locale: true,
                height: tableHeight,
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
                pagination: "local",
                paginationSize: page_size,
                paginationSizeSelector:[5, 10, page_size, 50, true],
                movableColumns:false,
                columns:[
                    {title:"Nr", field:"Nr", },
                    {title:"Titel", field:"Titel", formatter:"html", headerFilter:"input"},
                    {title:"Kategorie", field:"Kategorie", headerFilter:"input"},
                    {title:"Distanz", field:"Distanz",  hozAlign:"right", headerFilterPlaceholder:">...km", headerFilter:"input", headerFilterFunc:this.HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                    {title:"Aufstieg", field:"Aufstieg", hozAlign:"right",headerFilterPlaceholder:">...Hm", headerFilter:"input", headerFilterFunc:this.HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                    {title:"Abstieg", field:"Abstieg", hozAlign:"right",headerFilterPlaceholder:">...Hm", headerFilter:"input", headerFilterFunc:this.HeaderFilter, headerFilterFuncParams:{Distanz:0}},
                    {title:"Land", field:"Land", headerFilter:"input"},
                    {title:"Region", field:"Region", headerFilter:"input"},
                    {title:tableMapMoveSelector, field:tableMapMoveSelector, formatter:"html", headerFilter:"input"},
                ],
            };
        }

        if ( tableHeight == '0px' ) {
            delete tabulatorOptions.height;
        }
        return new Tabulator(tableId, tabulatorOptions);
    }

}
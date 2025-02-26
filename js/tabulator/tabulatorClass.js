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

    getTableColumns() {
        const table = document.getElementById("post_table");
        if (!table) return [];
        
        const headers = table.querySelectorAll("thead th");
        const columns = [];
        
        headers.forEach(th => {
            let column = {
                title: th.textContent.trim(),
                field: th.textContent.trim()
            };
            
            if (th.dataset.type === "html") {
                column.formatter = "html";
            }
            
            if (th.dataset.filter !== "false") {
                column.headerFilter = "input";
            }
            
            if (th.dataset.filter === "number") {
                column.hozAlign = "right";
                column.headerFilterPlaceholder = ">...m";
                column.headerFilterFunc = this.HeaderFilter;
                column.headerFilterFuncParams = { Distanz: 0 };
            }
            
            columns.push(column);
        });
        
        return columns;
    }

    getUserLocale() {
        const availableLangs = Object.keys(this.getLangs());
        let userLang = navigator.language.toLowerCase(); // z. B. "de", "de-de", "fr-fr"
    
        // Wenn exakte Sprache existiert, verwenden
        if (availableLangs.includes(userLang)) return userLang;
    
        // Falls "de", "fr", "it" statt "de-de" kommt, kürzen und prüfen
        let baseLang = userLang.split('-')[0]; // "de", "fr", "it"
        if (availableLangs.includes(baseLang + '-' + baseLang)) {
            return baseLang + '-' + baseLang;
        }
    
        // Fallback auf Englisch
        return "en-en";
    }

    getLangs() {
        return {
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
            },
            "it-it": {
                "pagination": {
                    "page_size": "Numero di percorsi",
                    "page_title": "Mostra",
                    "first": "Primo",
                    "first_title": "Primo",
                    "last": "Ultimo",
                    "last_title": "Ultimo",
                    "prev": "Precedente",
                    "prev_title": "Precedente",
                    "next": "Successivo",
                    "next_title": "Pagina successiva",
                    "all": "Tutti"
                }
            },
            "fr-fr": {
                "pagination": {
                    "page_size": "Nombre de parcours",
                    "page_title": "Afficher",
                    "first": "Premier",
                    "first_title": "Premier",
                    "last": "Dernier",
                    "last_title": "Dernier",
                    "prev": "Précédent",
                    "prev_title": "Précédent",
                    "next": "Suivant",
                    "next_title": "Page suivante",
                    "all": "Tous"
                }
            },
            "es-es": {
                "pagination": {
                    "page_size": "Número de rutas",
                    "page_title": "Mostrar",
                    "first": "Primero",
                    "first_title": "Primero",
                    "last": "Último",
                    "last_title": "Último",
                    "prev": "Anterior",
                    "prev_title": "Anterior",
                    "next": "Siguiente",
                    "next_title": "Página siguiente",
                    "all": "Todos"
                }
            }
        };
    }

    createTable(tableId, options={}){
        let page_size = options.tablePageSize ? options.tablePageSize : 20;
        let tableHeight = options.tableHeight ? options.tableHeight : '0px';
        let tabulatorOptions = {};
        let columns = this.getTableColumns();        

        if (options.type === 'tourmap') {
            tabulatorOptions = {
                layout: "fitDataTable", //
                locale: this.getUserLocale(),
                height: tableHeight,
                columnDefaults: { //
                    widthShrink: 1, // Passt Spalten flexibel an, aber nicht größer als nötig
                },
                langs:this.getLangs(),
                pagination: "local",
                paginationSize: page_size,
                paginationSizeSelector:[5, 10, page_size, 50, true],
                movableColumns:false,
                columns: columns,
            };
        } else {
            tabulatorOptions = {
                layout: "fitData",
                locale: this.getUserLocale(),
                height: tableHeight,
                langs:this.getLangs(),
                pagination: "local",
                paginationSize: page_size,
                paginationSizeSelector:[5, 10, page_size, 50, true],
                movableColumns:false,
                columns: columns,
            };
        }

        if ( tableHeight == '0px' ) {
            delete tabulatorOptions.height;
        }
        return new Tabulator(tableId, tabulatorOptions);
    }

}
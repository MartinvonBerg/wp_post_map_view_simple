/*!
	leafletChartJsClass 0.17.0
	license: GPL 2.0
	Martin von Berg
*/
// imports
import {LeafletMap} from './leafletMapClass.js';
// import class for track on map for one or multiple tracks
import {gpxTrackClass} from './gpxTrackClass.js';
// import class for chart for one or multiple tracks
import {calculateEquallyDistributedColors} from '../libs/colorLib.js'

// local Styles  
import './leafletGpxJsClass.css';

export {LeafletGpxJs};

class LeafletGpxJs extends LeafletMap {

    coords = [];
    theMarker = {};
    elev_data = [];
    leafletTrackID = 0;
    chart = {};
    track = [];
    trackStartColour = '#ff0000';
    trackColours = [];
    allBounds =[];
    currentTrack = 0;
    preload = true;

    constructor(number, elementOnPage, preload=null, center=null, zoom=null ) {
        super(number, elementOnPage, center=null, zoom=null);

        if (preload !== null) {
          this.preload = preload;
        }

        this.createTrackOnMap();
    };

    /**
     * generate the tracks on one 
     * @global pageVarsForJs[number].sw_options.trackcolour
     * @global this.pageVariables.ngpxfiles
     * @global this.pageVariables.tracks, 
     *            this.pageVariables.tracks[track_x].url
     * @global {object} pageVarsForJs[number]
     *            pageVariables.sw_options.gpx_distsmooth
     *            pageVariables.sw_options.gpx_elesmooth
     *            this.pageVariables.imagepath
     *            this.pageVariables.tracks['track_<number>'].info
     *            this.pageVariables.sw_options.trackwidth
     * @global {object} pageVarsForJs[number].tracks_polyline_options
     */
    async createTrackOnMap() {
        // generate the track colors
        let number = this.number;
        this.trackStartColour = pageVarsForJs[number].sw_options.trackcolour ?? '#ff0000';
        // calculate track colours for the different tracks.
        this.trackColours = calculateEquallyDistributedColors(this.trackStartColour, this.pageVariables.ngpxfiles);

        // generate all tracks on the map 
        for (const [key, track] of Object.entries( this.pageVariables.tracks )) {
            let trackNumber = parseInt(key.replace(/\D/g,''));
            this.track[trackNumber] = await this.createTrack(number, trackNumber).then(results => { 
              return results ;
            });
            // get all bounds from all tracks. These bounds are not available if asyncLoading is true.
            if ( this.track[trackNumber].bounds !== null && this.track[trackNumber].bounds.isValid() ) {
              this.allBounds[trackNumber] = this.track[trackNumber].bounds;
            } else if ( this.bounds ) {
              this.allBounds[trackNumber] = this.bounds;
            } 
        };

        // set the bounds for the map. handling of parameter showalltracks is ignored here.
        let maxBounds = this.findMaxBounds(this.allBounds);
        if (maxBounds !== null && maxBounds.isValid()) {
          super.setBounds(maxBounds); // bounds might not correctly set leaflet-overlay-pane
          this.map.fitBounds(maxBounds);
        }
        this.map.currentTrack = this.currentTrack; 
    };

    async createTrack(number, trackNumber) {
      if ( this.preload ) {
        let track_x = `track_${trackNumber}`;  // where x is 0, 1, 2, etc.
        let path = this.pageVariables.tracks[track_x].url;
        let newFile = await fetch(path).then(response => response.text());
        this.pageVariables.tracks[track_x].url = newFile;
      }

      return new gpxTrackClass(number, this, this.pageVariables.tracks, trackNumber, this.trackColours[trackNumber]);
    };
    
    /**
     * Finds the maximum bounds from an array of map bounds.
     *
     * @param {Array<L.LatLngBounds>} mapBoundsArray - An array of map bounds.
     * @return {L.LatLngBounds|null} The maximum bounds from the array, or null if the array is empty or invalid.
     */
    findMaxBounds(mapBoundsArray) {
      if ( mapBoundsArray[0] === null || !Array.isArray(mapBoundsArray) || mapBoundsArray.length === 0) {
        return null; // Return null for an empty or invalid array
      }
    
      let maxBounds = mapBoundsArray[0]; // Initialize with the first bounds in the array
    
      for (let i = 1; i < mapBoundsArray.length; i++) { // performance
        const currentBounds = mapBoundsArray[i];
    
        // Compare the latitude and longitude values to find the maximum bounds
        maxBounds._southWest.lat = Math.min(maxBounds._southWest.lat, currentBounds._southWest.lat);
        maxBounds._southWest.lng = Math.min(maxBounds._southWest.lng, currentBounds._southWest.lng);
        maxBounds._northEast.lat = Math.max(maxBounds._northEast.lat, currentBounds._northEast.lat);
        maxBounds._northEast.lng = Math.max(maxBounds._northEast.lng, currentBounds._northEast.lng);
      }
    
      return maxBounds;
    }
}
function Tiles() {
    var tiles = false;
    if (cubewp_map_params.map_option == 'google') {
        tiles = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 18,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            noWrap: true,
            attribution: '&copy; Map data ©2022 <a href="https://www.google.com">Google</a>'
        });
    } else if (cubewp_map_params.map_option == 'openstreet') {
        tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        });
    } else if (cubewp_map_params.map_option == 'mapbox') {
        tiles = L.tileLayer('https://api.mapbox.com/styles/v1/' + cubewp_map_params.map_style + '/tiles/256/{z}/{x}/{y}?access_token=' + cubewp_map_params.mapbox_token, {
            maxZoom: 18,
            attribution: 'Map data ©<a href="http://openstreetmap.org">OpenStreetMap</a>' + 'Imagery © <a href="http://mapbox.com">Mapbox</a>',
        });
    }

    return tiles;
}

function cwp_rand_id(length, preFix = '', postFix = '') {
    var char = 'abcdefghijklmnopqrstuvwxyz',
        ID = '';
    if (typeof length !== "number") length = 6;
    for (var i = length; i > 0; i--) {
        ID += char[Math.floor(Math.random() * char.length)];
    }
    return preFix + ID + postFix;
}

function CWP_Single_Map() {
    var cptSingleMap = jQuery('.cpt-single-map');
    if (cptSingleMap.length > 0) {
        cptSingleMap.each(function () {
            var thisObj = jQuery(this),
                latitude = parseFloat(thisObj.attr('data-latitude')),
                longitude = parseFloat(thisObj.attr('data-longitude')),
                pin = thisObj.attr('data-pinicon'),
                uniqueID = cwp_rand_id(6, 'cwp-map-'),
                tiles = Tiles(),
                markerOptions = {
                    icon: createCustomIcon(pin)
                };
            thisObj.empty();
            thisObj.html('<div id="' + uniqueID + '"></div>');

            if (typeof latitude == "undefined" || latitude === '') latitude = cubewp_map_params.map_latitude;
            if (typeof longitude == "undefined" || longitude === '') longitude = cubewp_map_params.map_longitude;

            if (typeof latitude == "undefined" || latitude === '') latitude = 51.5072;
            if (typeof longitude == "undefined" || longitude === '') longitude = -0.128;

            if (checkIfValidlatitudeAndlongitude(latitude + ',' + longitude)) {
                var map = latlng = marker = null;
                latlng = new L.latLng(latitude, longitude);
                jQuery('#' + uniqueID).css('height', '100%');
                map = new L.map(uniqueID, {
                    center: latlng,
                    zoom: cubewp_map_params.map_zoom,
                    layers: [tiles]
                });
                marker = new L.marker(new L.LatLng(latitude, longitude), markerOptions);
                map.addLayer(marker);
            }
        });
    }
}

CWP_Single_Map();

function checkIfValidlatitudeAndlongitude(str) {
    // Regular expression to check if string is a latitude and longitude
    const regexExp = /^((\-?|\+?)?\d+(\.\d+)?),\s*((\-?|\+?)?\d+(\.\d+)?)$/i;

    return regexExp.test(str);
}


var CWP_MAP_SETTINGS = {
    pin: {
        useHtml: true, // true = HTML pin | false = image pin
        defaultIcon: '', // fallback image icon
        size: [30, 42],
        anchor: [15, 42],
        popupAnchor: [0, -42]
    },
    cluster: {
        enabled: true
    },
    map: {
        scrollWheelZoom: true,
        fitPadding: [50, 50]
    }
};

// Keep last coordinates so map can be rebuilt when hidden container becomes visible.
var CWP_MAP_LAST_ARGS = [];

function CWP_Cluster_Map(args = '') {

    var cwpArchiveMap = jQuery('.cwp-archive-content-map');
    if (!cwpArchiveMap.length) return;

    var tiles = Tiles();
    if (!tiles) return;

    var latlng = L.latLng(
        cubewp_map_params.map_latitude,
        cubewp_map_params.map_longitude
    );

    var MapID = 'archive-map';
    cwpArchiveMap.empty().html('<div id="' + MapID + '"></div>');

    var map = L.map(MapID, {
        center: latlng,
        zoom: cubewp_map_params.map_zoom,
        layers: [tiles],
        fullscreenControl: false
    });

    map.addControl(new L.Control.Fullscreen());

    if (!args || !args.length) {
        return;
    }

    CWP_MAP_LAST_ARGS = args;

    var markers = CWP_MAP_SETTINGS.cluster.enabled ?
        L.markerClusterGroup() :
        L.layerGroup();

    var showmap = false;

    for (var i = 0; i < args.length; i++) {

        if (!checkIfValidlatitudeAndlongitude(args[i][0] + ',' + args[i][1])) {
            continue;
        }

        showmap = true;

        var a = args[i];
        var lat = a[0];
        var lng = a[1];
        var title = a[2];
        var url = a[3];
        var thumbnail = a[4];
        var pinIconUrl = a[5] || CWP_MAP_SETTINGS.pin.defaultIcon;

        var markerOptions = {
            title: title
        };

        // ✅ HTML PIN FROM DATA ATTRIBUTE
        var pinHtml = cwpArchiveMap.data('mappin-html');

        if (CWP_MAP_SETTINGS.pin.useHtml && pinHtml) {
            markerOptions.icon = createCustomIcons(pinHtml);
        }
        // ✅ IMAGE PIN FALLBACK
        else if (pinIconUrl) {
            markerOptions.icon = createCustomIcon(pinIconUrl);
        }

        var marker = L.marker([lat, lng], markerOptions);

        var popover =
            '<div class="cwp-map-popover">' +
            '<a href="' + url + '" target="_blank">' +
            '<img src="' + thumbnail + '" alt="' + title + '">' +
            '<h3>' + title + '</h3>' +
            '</a>' +
            '</div>';

        marker.bindPopup(popover);
        markers.addLayer(marker);
    }

    if (showmap) {
        map.addLayer(markers);
        map.fitBounds(markers.getBounds(), {
            padding: CWP_MAP_SETTINGS.map.fitPadding
        });

        if (CWP_MAP_SETTINGS.map.scrollWheelZoom) {
            map.scrollWheelZoom.enable();
        } else {
            map.scrollWheelZoom.disable();
        }

        map.invalidateSize();
        map.dragging.enable();
    }
}

function CWP_Refresh_Cluster_Map() {
    if (typeof CWP_Cluster_Map !== 'function') {
        return;
    }
    if (Array.isArray(CWP_MAP_LAST_ARGS) && CWP_MAP_LAST_ARGS.length) {
        CWP_Cluster_Map(CWP_MAP_LAST_ARGS);
    }
}

if (typeof window !== 'undefined') {
    window.CWP_Refresh_Cluster_Map = CWP_Refresh_Cluster_Map;
}

function createCustomIcon(iconUrl) {
    return L.icon({
        iconUrl: iconUrl,
        iconSize: CWP_MAP_SETTINGS.pin.size,
        iconAnchor: CWP_MAP_SETTINGS.pin.anchor,
        popupAnchor: CWP_MAP_SETTINGS.pin.popupAnchor
    });
}

function createCustomIcons(html) {
    return L.divIcon({
        className: 'cwp-svg-pin',
        html: `<div class="cwp-pin">${html}</div>`,
        iconSize: CWP_MAP_SETTINGS.pin.size,
        iconAnchor: CWP_MAP_SETTINGS.pin.anchor,
        popupAnchor: CWP_MAP_SETTINGS.pin.popupAnchor
    });
}
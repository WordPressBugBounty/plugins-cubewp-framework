function getDisplayAddressFromPlaceAdmin(place, useCityOnly) {
    if (!place) return '';
    if (!useCityOnly) return place.formatted_address || place.name || '';
    if (place.address_components) {
        for (var i = 0; i < place.address_components.length; i++) {
            var c = place.address_components[i];
            if (c.types.indexOf('locality') !== -1) return c.long_name;
        }
        for (var j = 0; j < place.address_components.length; j++) {
            var ac = place.address_components[j];
            if (ac.types.indexOf('administrative_area_level_2') !== -1) return ac.long_name;
        }
        if (place.name) return place.name;
    }
    return place.formatted_address || place.name || '';
}

function getGoogleAddressAdminAutocompleteOptions() {
    var opts = {};
    if (typeof cubewp_google_address_field_params !== 'undefined') {
        if (cubewp_google_address_field_params.google_address_city_location) {
            opts.types = ['(cities)'];
        }
        if (cubewp_google_address_field_params.google_address_country_code && cubewp_google_address_field_params.google_address_country_code.trim() !== '') {
            var countries = cubewp_google_address_field_params.google_address_country_code.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
            opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
        }
    }
    return opts;
}

function getGoogleAddressAdminAutocompleteOptionsForInput(inputEl) {
    var opts = {};
    if (!inputEl) return getGoogleAddressAdminAutocompleteOptions();
    var useCityLocation = inputEl.getAttribute && inputEl.getAttribute('data-city-location') === '1';
    var countryCode = inputEl.getAttribute && inputEl.getAttribute('data-country-code');
    if (useCityLocation) {
        opts.types = ['(cities)'];
    }
    if (countryCode && countryCode.trim() !== '') {
        var countries = countryCode.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
        opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
    } else if (typeof cubewp_google_address_field_params !== 'undefined' && cubewp_google_address_field_params.google_address_country_code && cubewp_google_address_field_params.google_address_country_code.trim() !== '') {
        var countries = cubewp_google_address_field_params.google_address_country_code.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
        opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
    }
    return opts;
}

function isCityLocationFieldAdmin(inputEl) {
    return inputEl && inputEl.getAttribute && inputEl.getAttribute('data-city-location') === '1';
}

if(jQuery('.cwp-google-address').length > 0){
    function initialize_google_address() {
        jQuery('.cwp-google-address').each(function(){
            var thisObj = jQuery(this);
            var addressInput = thisObj.find('.address')[0];
            var autocompleteOpts = getGoogleAddressAdminAutocompleteOptionsForInput(addressInput);
        
            var input_id  = thisObj.find('.address').attr('id');
            var latitude  = thisObj.find('.latitude').val();
            var longitude = thisObj.find('.longitude').val();

            
            var loadmap = loadMap(input_id,latitude,longitude);
            var marker  = loadmap[1];
            var map     = loadmap[0];

            var autocomplete = new google.maps.places.Autocomplete(document.getElementById(input_id), autocompleteOpts);
            var useCityOnly = isCityLocationFieldAdmin(addressInput);
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                
                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
                
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();
                thisObj.find('.latitude').val(latitude);
                thisObj.find('.longitude').val(longitude);
                thisObj.find('.address').val(getDisplayAddressFromPlaceAdmin(place, useCityOnly));
            });
            
            var geocoder = new google.maps.Geocoder();
            google.maps.event.addListener(marker, 'dragend', function(evt){
                var useCityOnlyDrag = isCityLocationFieldAdmin(addressInput);
                geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            thisObj.find('.address').val(getDisplayAddressFromPlaceAdmin(results[0], useCityOnlyDrag));
                            thisObj.find('.latitude').val(marker.getPosition().lat());
                            thisObj.find('.longitude').val(marker.getPosition().lng());

                        }
                    }
                });
            });
            
        });
    }
    jQuery( window ).on( 'load', initialize_google_address );
    jQuery( '.cwp-add-row-btn' ).on( 'click', function() {
        setTimeout(function() {initialize_google_address();}, 1000);
    });
    jQuery(document).on( "click", ".cwp-get-current-location", function() {
        var thismap = jQuery(this);
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showCurrentPosition);
        }else{
            alert('Geolocation is not supported by your browser');
        }
        function showCurrentPosition(position){
            
            var input_id  = thismap.parents().children('.address').attr('id');
            var latitude  = thismap.parents().children('.latitude').val();
            var longitude = thismap.parents().children('.longitude').val();
            var loadmap = loadMap(input_id,latitude,longitude);
            var marker  = loadmap[1];
            var map     = loadmap[0];
            
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            thismap.parents().children('.latitude').val(position.coords.latitude);
            thismap.parents().children('.longitude').val(position.coords.longitude);
            var pos = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            map.setCenter(pos);
            marker.setPosition(pos);
            marker.setVisible(true);

            var geocoder = new google.maps.Geocoder();
            var addressInput = thismap.parents().children('.address')[0];
            var useCityOnly = isCityLocationFieldAdmin(addressInput);
            geocoder.geocode({ "latLng": latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        thismap.parents().children('.address').val(getDisplayAddressFromPlaceAdmin(results[1], useCityOnly));
                    }
                }
            });
        }
    });
    
    function loadMap(input_id,latitude,longitude){

        if( typeof latitude == "undefined" || latitude == '' ){
            latitude = 51.5072;
        }
        if( typeof longitude == "undefined" || longitude == '' ){
            longitude = -0.128;
        }

        var latLng   = new google.maps.LatLng(latitude, longitude);
        var map = new google.maps.Map(document.getElementById("map-"+ input_id), {
            center: latLng,
            zoom: 14,
            minZoom: 0,
            maxZoom: 30,
            draggable: true,
            scrollwheel: false,
            navigationControl: !0,
            mapTypeControl: !1,
            streetViewControl: !1,
        });

        var marker = new google.maps.Marker({
            position: latLng,
            map: map,
            draggable: true
        });
        var maparray = new Array(map,marker);
         return maparray;
    }
}
function getGoogleAddressAutocompleteOptions() {
    var opts = {};
    if (typeof cwp_google_address_field_params !== 'undefined') {
        if (cwp_google_address_field_params.google_address_city_location) {
            opts.types = ['(cities)'];
        }
        if (cwp_google_address_field_params.google_address_country_code && cwp_google_address_field_params.google_address_country_code.trim() !== '') {
            var countries = cwp_google_address_field_params.google_address_country_code.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
            opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
        }
    }
    return opts;
}

function getGoogleAddressAutocompleteOptionsForInput(inputEl) {
    var opts = {};
    if (!inputEl) return getGoogleAddressAutocompleteOptions();
    var useCityLocation = inputEl.getAttribute && inputEl.getAttribute('data-city-location') === '1';
    var countryCode = inputEl.getAttribute && inputEl.getAttribute('data-country-code');
    if (useCityLocation) {
        opts.types = ['(cities)'];
    }
    if (countryCode && countryCode.trim() !== '') {
        var countries = countryCode.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
        opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
    } else if (typeof cwp_google_address_field_params !== 'undefined' && cwp_google_address_field_params.google_address_country_code && cwp_google_address_field_params.google_address_country_code.trim() !== '') {
        var countries = cwp_google_address_field_params.google_address_country_code.trim().toLowerCase().split(',').map(function(c) { return c.trim(); }).filter(function(c) { return c; });
        opts.componentRestrictions = { country: countries.length === 1 ? countries[0] : countries };
    }
    return opts;
}

function isCityLocationField(inputEl) {
    return inputEl && inputEl.getAttribute && inputEl.getAttribute('data-city-location') === '1';
}

function getDisplayAddressFromPlace(place, useCityOnly) {
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

if(jQuery('.cwp-field-google_address').length > 0){
    function initialize_google_address() {
        jQuery('.cwp-field-google_address').each(function(){
            var thisObj = jQuery(this);
            var addressInput = thisObj.find('.address')[0];
            var autocompleteOpts = getGoogleAddressAutocompleteOptionsForInput(addressInput);
            
            var input_id  = thisObj.find('.address').attr('id');
            var latitude  = thisObj.find('.latitude').val();
            var longitude = thisObj.find('.longitude').val();
                        
            var loadmap = loadMap(input_id,latitude,longitude);
            var marker  = loadmap[1];
            var map     = loadmap[0];
            var autocomplete = new google.maps.places.Autocomplete(document.getElementById(input_id), autocompleteOpts);
            var useCityOnly = isCityLocationField(addressInput);
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                
                map.setCenter(place.geometry.location);
                marker.setPosition(place.geometry.location);
                marker.setVisible(true);
                
                var latitude = place.geometry.location.lat();
                var longitude = place.geometry.location.lng();
                thisObj.find('.latitude').val(latitude);
                thisObj.find('.longitude').val(longitude);
                thisObj.find('.address').val(getDisplayAddressFromPlace(place, useCityOnly));
            });
            
            google.maps.event.addListener(marker, 'dragend', function(evt){
                var geocoder = new google.maps.Geocoder();
                var useCityOnly = isCityLocationField(addressInput);
                geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results[0]) {
                            thisObj.find('.address').val(getDisplayAddressFromPlace(results[0], useCityOnly));
                            thisObj.find('.latitude').val(marker.getPosition().lat());
                            thisObj.find('.longitude').val(marker.getPosition().lng());

                        }
                    }
                });
            });
            
            
        });
    }
    jQuery( window ).on( 'load', initialize_google_address );
    jQuery( '.cwp-add-new-repeating-field' ).on( 'click', function() {
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
            var useCityOnly = isCityLocationField(addressInput);
            geocoder.geocode({ "latLng": latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        thismap.parents().children('.address').val(getDisplayAddressFromPlace(results[1], useCityOnly));
                    }
                }
            });
        }
    });
    
    // Drop Pin Modal Functionality
    var dropPinModalMaps = {}; // Store maps per field ID
    
    jQuery(document).on( "click", ".cwp-drop-pin", function() {
        var dropPinBtn = jQuery(this);
        var fieldContainer = dropPinBtn.closest('.cwp-field-google_address');
        var input_id = fieldContainer.find('.address').attr('id');
        var modalId = 'cwp-drop-pin-modal-' + input_id;
        var modal = jQuery('#' + modalId);
        var mapContainerId = 'cwp-drop-pin-map-' + input_id;
        var mapContainer = document.getElementById(mapContainerId);
        
        // Get current coordinates or use defaults
        var currentLat = fieldContainer.find('.latitude').val();
        var currentLng = fieldContainer.find('.longitude').val();
        
        if (!currentLat || currentLat === '') {
            currentLat = 51.5072;
        }
        if (!currentLng || currentLng === '') {
            currentLng = -0.128;
        }
        
        // Show modal
        modal.fadeIn(300);
        
        // Initialize map in modal if not already initialized for this field
        if (!dropPinModalMaps[input_id] || !dropPinModalMaps[input_id].map) {
            var latLng = new google.maps.LatLng(currentLat, currentLng);
            var modalMap = new google.maps.Map(mapContainer, {
                center: latLng,
                zoom: 14,
                minZoom: 0,
                maxZoom: 30,
                draggable: true,
                scrollwheel: true,
                navigationControl: true,
                mapTypeControl: true,
                streetViewControl: true,
            });
            
            // Create marker
            var modalMarker = new google.maps.Marker({
                position: latLng,
                map: modalMap,
                draggable: true,
                animation: google.maps.Animation.DROP
            });
            
            // Add click listener to map to drop pin
            google.maps.event.addListener(modalMap, 'click', function(event) {
                var clickedLocation = event.latLng;
                modalMarker.setPosition(clickedLocation);
                modalMarker.setVisible(true);
            });
            
            // Allow marker dragging
            google.maps.event.addListener(modalMarker, 'dragend', function(event) {
                modalMarker.setPosition(event.latLng);
            });
            
            // Store map and marker for this field
            dropPinModalMaps[input_id] = {
                map: modalMap,
                marker: modalMarker,
                fieldId: input_id
            };
        } else {
            // Update map center if coordinates exist
            var latLng = new google.maps.LatLng(currentLat, currentLng);
            dropPinModalMaps[input_id].map.setCenter(latLng);
            dropPinModalMaps[input_id].marker.setPosition(latLng);
            dropPinModalMaps[input_id].marker.setVisible(true);
        }
    });
    
    // Confirm location button
    jQuery(document).on( "click", ".cwp-drop-pin-confirm", function() {
        var confirmBtn = jQuery(this);
        var modal = confirmBtn.closest('.cwp-drop-pin-modal');
        var modalId = modal.attr('id');
        var input_id = modalId.replace('cwp-drop-pin-modal-', '');
        
        if (dropPinModalMaps[input_id] && dropPinModalMaps[input_id].marker && dropPinModalMaps[input_id].marker.getVisible()) {
            var fieldContainer = jQuery('.cwp-field-google_address').has('#' + input_id);
            var position = dropPinModalMaps[input_id].marker.getPosition();
            var latitude = position.lat();
            var longitude = position.lng();
            
            // Update hidden fields
            fieldContainer.find('.latitude').val(latitude);
            fieldContainer.find('.longitude').val(longitude);
            
            // Geocode to get address
            var geocoder = new google.maps.Geocoder();
            var addressInput = fieldContainer.find('.address')[0];
            var useCityOnly = isCityLocationField(addressInput);
            geocoder.geocode({'latLng': position}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        fieldContainer.find('.address').val(getDisplayAddressFromPlace(results[0], useCityOnly));
                    }
                }
            });
            
            // Update main map if it exists
            var mainMapId = 'map-' + input_id;
            var mainMapElement = document.getElementById(mainMapId);
            if (mainMapElement && typeof google !== 'undefined' && google.maps) {
                // Try to get existing map instance or reload
                setTimeout(function() {
                    var mainLat = fieldContainer.find('.latitude').val();
                    var mainLng = fieldContainer.find('.longitude').val();
                    if (mainLat && mainLng) {
                        var loadmap = loadMap(input_id, mainLat, mainLng);
                        if (loadmap && loadmap[1]) {
                            loadmap[1].setPosition(new google.maps.LatLng(mainLat, mainLng));
                            loadmap[1].setVisible(true);
                            loadmap[0].setCenter(new google.maps.LatLng(mainLat, mainLng));
                        }
                    }
                }, 100);
            }
            
            // Close modal
            modal.fadeOut(300);
        }
    });
    
    // Close modal handlers
    jQuery(document).on( "click", ".cwp-drop-pin-modal-close, .cwp-drop-pin-cancel, .cwp-drop-pin-modal-overlay", function() {
        jQuery(this).closest('.cwp-drop-pin-modal').fadeOut(300);
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



if(jQuery('.cwp-search-field-google_address').length > 0){
    function initialize_search_google_address() {
        jQuery('.cwp-search-field-google_address').each(function(){
            var thisObj   = jQuery(this);
            var addressInput = thisObj.find('.address')[0];
            var autocompleteOpts = getGoogleAddressAutocompleteOptionsForInput(addressInput);
            var input_id  = thisObj.find('.address').attr('id');
            
            var autocomplete = new google.maps.places.Autocomplete(document.getElementById(input_id), autocompleteOpts);
            var useCityOnly = isCityLocationField(addressInput);
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                var place = autocomplete.getPlace();
                if (place.geometry) {
                    var latitude = place.geometry.location.lat();
                    var longitude = place.geometry.location.lng();
                    thisObj.find('.latitude').val(latitude);
                    thisObj.find('.longitude').val(longitude);
                    thisObj.find('.address').val(getDisplayAddressFromPlace(place, useCityOnly));
                }else {
                    thisObj.find('.latitude').val('');
                    thisObj.find('.longitude').val('');
                }
                jQuery("#" + input_id).trigger("cwp-address-change");
            });
            jQuery(document).on("input", "#" + input_id, function() {
                if (jQuery(this).val() === '') {
                    thisObj.find('.latitude').val('');
                    thisObj.find('.longitude').val('');
                    jQuery(this).trigger("cwp-address-change");
                }
            });
            jQuery(document).on( "click", ".cwp-get-current-location", function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showCurrentPosition);
                }else{
                    alert('Geolocation is not supported by your browser');
                }
                function showCurrentPosition(position){
                    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    thisObj.find('.latitude').val(position.coords.latitude);
                    thisObj.find('.longitude').val(position.coords.longitude);
                    
                    var geocoder = new google.maps.Geocoder();
                    var useCityOnly = isCityLocationField(addressInput);
                    geocoder.geocode({ "latLng": latlng }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                thisObj.find('.address').val(getDisplayAddressFromPlace(results[1], useCityOnly));
                                jQuery("#" + input_id).trigger("cwp-address-change");
                            }
                        }
                    });
                }
            });
        });
    }
    jQuery( window ).on( 'load', initialize_search_google_address );
}
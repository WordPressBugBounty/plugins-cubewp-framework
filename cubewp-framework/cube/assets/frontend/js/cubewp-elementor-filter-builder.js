/**
 * CubeWP Filter Builder Module
 *
 * Separate module for filter builder widget that works independently
 * Uses the same AJAX PHP function as the existing filter system
 *
 * @package cubewp-framework
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    function triggerFilterAjax() {
        if (typeof cwp_search_filters_ajax_content === 'function') {
            cwp_search_filters_ajax_content();
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function updateURLParam(name, value) {
        const url = new URL(window.location);
        if (value === '' || value === null) {
            url.searchParams.delete(name);
        } else {
            url.searchParams.set(name, value);
        }
        window.history.replaceState({}, '', url);
    }


    // Debounced handler for input/select changes
    const debouncedFilterChange = debounce(function () {
        const name = $(this).attr('name');
        if (!name) return;

        const value = $(this).val();
        if (value === '' || value === null) {
            updateURLParam(name, '');
        } else {
            updateURLParam(name, value);
        }
        triggerFilterAjax();
    }, 300);

    $(document).on('input change', '.cubewp-filter-builder-field-wrapper input:not([type="hidden"]):not([type="range"]):not([type="checkbox"]):not([type="radio"]):not([type="google_address"]),.cubewp-filter-builder-field-wrapper select', function () {
        debouncedFilterChange.call(this);
    });

    function updateGoogleAddress($wrapper) {
        // Address text
        const $addressInput = $wrapper.find('input[type="google_address"]');
        const addressName = $addressInput.attr('name');
        const addressValue = $addressInput.val();
        const $lat = $wrapper.find('input.latitude');
        const $lng = $wrapper.find('input.longitude');
        const $range = $wrapper.find('input.range');
        // Send params only if name exists
        if (addressName) {
            updateURLParam(addressName, addressValue);
        }
        if ($lat.length && $lat.val()) {
            updateURLParam(addressName + '_lat', $lat.val());
        }
        if ($lng.length && $lng.val()) {
            updateURLParam(addressName + '_lng', $lng.val());
        }
        if ($range.length && $range.val()) {
            updateURLParam(addressName + '_range', $range.val());
        }
    }

    $(document).on('change', '.cubewp-filter-builder-field-wrapper input[type="google_address"]', function () {
        var $wrapper = $(this).closest('.cubewp-filter-builder-field-container');
        setTimeout(function () {
            $wrapper.addClass('loading');
            updateGoogleAddress($wrapper);
            triggerFilterAjax();
        }, 500);
    });


    $(document).on('change', '.cubewp-filter-builder-field-container select', function () {
        debouncedFilterChange.call(this);

    });

    // Handle range slider changes
    $(document).on('change', '.cubewp-filter-builder-field-wrapper input[type="range"]', function () {
        // Only proceed if not inside .cwp-address-range parent
        if ($(this).closest('.cwp-address-range').length) return;
        const name = $(this).attr('name');
        if (!name) return;

        const value = $(this).val();
        if (value === '' || value === null) {
            updateURLParam(name, '');
        } else {
            updateURLParam(name, value);
        }
        triggerFilterAjax();
    });


     // Handle range slider changes
     $(document).on('change', '.cubewp-filter-builder-field-wrapper .cwp-address-range input[type="range"]', function () {
        const $input = $(this);
        const $container = $input.closest('.cubewp-filter-builder-field-container');
        const $googleAddressInput = $container.find('input[type="google_address"]');
        let baseName = '';
        if ($googleAddressInput.length && $googleAddressInput.attr('name')) {
            baseName = $googleAddressInput.attr('name');
        } else if ($input.attr('name')) {
            // fallback to using input name without _range if it is already present
            baseName = $input.attr('name').replace(/_range$/, '');
        }
        if (!baseName) return;
        const paramName = baseName + '_range';
        const value = $input.val();
        if (value === '' || value === null) {
            updateURLParam(paramName, '');
        } else {
            updateURLParam(paramName, value);
        }
        triggerFilterAjax();
    });

    $(document).on("click", ".cubewp-filter-popup-button", function () {
        var target = $(this).data("target");
        $("#" + target).addClass("active");
        $("#" + target + "-overlay").addClass("active");
    });

    // Popup close functionality
    $(document).on("click", ".cubewp-filter-popup-close", function () {
        var target = $(this).data("target");
        $("#" + target).removeClass("active");
        $("#" + target + "-overlay").removeClass("active");
    });

    // Close popup when clicking overlay
    $(document).on("click", ".cubewp-filter-popup-overlay", function () {
        $(this).removeClass("active");
        $(".cubewp-filter-popup-content").removeClass("active");
    });

    // Apply filters in popup
    $(document).on("click", ".cubewp-filter-popup-apply", function () {
        var popup = $(this).closest(".cubewp-filter-popup-content");
        var popupId = popup.attr("id");
        popup.removeClass("active");
        $("#" + popupId + "-overlay").removeClass("active");
        $("body").css("overflow", "");
        if (typeof cubewpCollectFilterBuilderData === "function") {
            cubewpCollectFilterBuilderData();
        }
        if (typeof cwp_search_filters_ajax_content === "function") {
            cwp_search_filters_ajax_content();
        }
    });


    $(document).on('change', '.cubewp-filter-builder-field-wrapper .cwp-switch-field', function () {
        const $wrapper = $(this).closest('.cubewp-filter-builder-field-wrapper');
        if (!$wrapper.length) return;

        const $hiddenInput = $wrapper.find('input[type="hidden"]');
        if (!$hiddenInput.length || !$hiddenInput.attr('name')) return;

        const value = $(this).is(':checked') ? 'YES' : '';

        updateURLParam($hiddenInput.attr('name'), value);
        triggerFilterAjax();
    });

    $(document).on('click', '.cubewp-business-hours-btn:not(.google-address-btn)', function () {
        const $this = $(this);
        const value = $this.data('filter-type');
        const name = $this.data('field-name');

        if ($this.hasClass('active')) {
            $this.removeClass('active');
            updateURLParam(name + '_status', '');
        } else {
            $this.addClass('active');
            updateURLParam(name + '_status', value);
        }
        triggerFilterAjax();
    });


    $(document).on('click', '.cubewp-sorting-dropdown-toggle', function (e) {
        e.stopPropagation();
        var $dropdown = $(this).closest('.cubewp-sorting-dropdown');
        var $menu = $dropdown.find('.cubewp-sorting-dropdown-menu');

        // Close other dropdowns
        $('.cubewp-sorting-dropdown-menu').not($menu).removeClass('open');

        // Toggle current dropdown
        $menu.toggleClass('open');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.cubewp-sorting-dropdown').length) {
            $('.cubewp-sorting-dropdown-menu').removeClass('open');
        }
    });

    function updateDateRange($wrapper) {
        const from = $wrapper.find('.cubewp-date-range-picker-from').val();
        const to = $wrapper.find('.cubewp-date-range-picker-to').val();
        const name = $wrapper.find('.cubewp-date-range-picker-input').attr('name');

        if (!name) return;

        if (from || to) {
            const value = (from || '') + '-' + (to || '');
            updateURLParam(name, value);
        } else {
            updateURLParam(name, '');
        }
    }

    // Listen dynamically (important for AJAX / dynamic fields)
    $(document).on('change', '.cubewp-filter-builder-search-field .cubewp-date-range-picker-from, ' + '.cubewp-filter-builder-search-field .cubewp-date-range-picker-to', function () {
        const $wrapper = $(this).closest('.cubewp-filter-builder-search-field');
        updateDateRange($wrapper);
    });


    function updateRadio($wrapper) {
        const $hidden = $wrapper.find('input[type="hidden"]');
        const name = $hidden.attr('name');
        if (!name) return;
        const value = $wrapper.find('input[type="radio"]:checked').val() || '';
        $hidden.val(value);
        updateURLParam(name, value);
    }

    $(document).on('change', '.cubewp-filter-builder-search-field input[type="radio"]', function () {
        const $wrapper = $(this).closest('.cubewp-filter-builder-search-field');
        $wrapper.find('input[type="radio"]').not(this).prop('checked', false);

        updateRadio($wrapper);
        triggerFilterAjax();
    });


    function updateCheckbox($wrapper) {
        const $hidden = $wrapper.find('input[type="hidden"]');
        const name = $hidden.attr('name');
        if (!name) return;
        const values = [];
        $wrapper.find('input[type="checkbox"]:checked').each(function () {
            values.push($(this).val());
        });
        const value = values.join(',');
        $hidden.val(value);
        updateURLParam(name, value);
    }

    $(document).on('change', '.cubewp-filter-builder-search-field input[type="checkbox"]', function () {
        const $wrapper = $(this).closest('.cubewp-filter-builder-search-field');
        updateCheckbox($wrapper);
        triggerFilterAjax();
    });



    /**
     * Apply relevance: set URL params (orderby=relevance + open_now status) and add active/selected to the 4 relevance sub-buttons. Single AJAX call.
     */
    function applyRelevanceParamsAndClasses() {
        updateURLParam('orderby', 'relevance');
        updateURLParam('field_type', 'cubewp_post_views');
        updateURLParam('sortings', 'ASC');
        $('[data-filter-type="open_now"]').each(function () {
            var name = $(this).data('field-name');
            if (name) {
                updateURLParam(name + '_status', 'open_now');
            }
        });
        $('[data-orderby="cubewp_review_count"]').addClass('active selected');
        $('[data-filter-type="open_now"]').addClass('active').closest('.cubewp-business-hours-filter, .cubewp-google-address-filter-container').addClass('active');
    }

    /**
     * Remove relevance: clear orderby and open_now status params, remove active/selected from the 4 relevance sub-buttons. Single AJAX call.
     */
    function removeRelevanceParamsAndClasses() {
        updateURLParam('orderby', '');
        updateURLParam('field_type', '');
        updateURLParam('operation', '');
        updateURLParam('value', '');
        updateURLParam('sortings', '');
        $('[data-filter-type="open_now"]').each(function () {
            var name = $(this).data('field-name');
            if (name) {
                updateURLParam(name + '_status', '');
            }
        });
        $('[data-orderby="cubewp_review_count"]').removeClass('active selected');
        $('[data-filter-type="open_now"]').removeClass('active').closest('.cubewp-business-hours-filter, .cubewp-google-address-filter-container').removeClass('active');
    }

    function clearCustomSortingParams() {
        updateURLParam('field_type', '');
        updateURLParam('operation', '');
        updateURLParam('value', '');
        updateURLParam('sortings', '');
    }

    $(document).on('click', '.cubewp-sorting-btn', function () {
        var $btn = $(this);
        var orderby = $btn.data('orderby');
        var operation = $btn.data('operation');
        var value = $btn.data('value');
        var sortings = $btn.data('sortings');
        var isActive = $btn.hasClass('active selected');

        $('.cubewp-sorting-dropdown-menu').removeClass('open');
        var isActives = $btn.hasClass('selected active');

        if (isActive || isActives) {
            $btn.removeClass('active');
            $btn.removeClass('selected');
            if (orderby == 'relevance') {
                removeRelevanceParamsAndClasses();
            } else {
                if ($btn.hasClass('cubewp-sorting-btn-custom')) {
                    updateSortingParameter_custom_filter('', '', '', '');
                } else {
                    updateURLParam('orderby', '');
                    clearCustomSortingParams();
                }
            }
        } else {
            // Keep only one selected sorting button globally.
            $('.cubewp-sorting-btn').removeClass('active selected');
            $btn.addClass('active selected');
            if (orderby == 'relevance') {
                applyRelevanceParamsAndClasses();
            } else {
                if ($btn.hasClass('cubewp-sorting-btn-custom')) {
                    if (sortings && sortings !== '') {
                        sortings = sortings;
                    } else {
                        sortings = '';
                    }
                    updateURLParam('orderby', '');
                    updateSortingParameter_custom_filter(orderby, operation, value, sortings);
                } else {
                    updateURLParam('orderby', orderby);
                    clearCustomSortingParams();
                }
            }
        }
        triggerFilterAjax();
        return;
    });


    function updateSortingParameter_custom_filter(orderby, operation, value, sortings) {
        // Update URL parameter
        var url = new URL(window.location.href);
        // Custom sorting uses field_type/sortings, not orderby.
        url.searchParams.delete('orderby');
        if (operation && operation !== '') {
            url.searchParams.set('operation', operation);
            url.searchParams.set('sortings', sortings);
        } else {
            url.searchParams.delete('operation');
            url.searchParams.delete('sortings');
        }

        if (sortings && sortings !== '') {
            url.searchParams.set('sortings', sortings);
        } else {
            url.searchParams.delete('sortings');
        }

        if (orderby && orderby !== '') {
            url.searchParams.set('field_type', orderby);
        } else {
            url.searchParams.delete('field_type');
        }
        if (value && value !== '') {
            url.searchParams.set('value', value);
        } else {
            url.searchParams.delete('value');
        }
        window.history.pushState({}, '', url);
    }


    function resetURLAndApplyFormParams() {



        const $containers = $('.cubewp-filter-builder-field-container');

        $containers.find('input[type!="checkbox"][type!="radio"]').val('');
        $containers.find('select').val('');
        $containers.find('input[type="checkbox"]').prop('checked', false);
        $containers.find('input[type="radio"]').prop('checked', false);
        $containers.find('input[type="hidden"]').val('');
        resetConditionalTaxonomyFields();
        jQuery('.cubewp-sorting-dropdown-item ').removeClass('selected active');
        jQuery('.cubewp-sorting-btn').removeClass('active selected');
        jQuery('.cubewp-business-hours-btn').removeClass('active selected');
        $containers.filter('.cwp-select2').each(function () {
            var $select = $(this).find('select');
            if (!$select.length) {
                return;
            }
            var placeholder = $select.attr('placeholder');
            $select.select2({
                placeholder: placeholder,
                allowClear: true
            });
        });
        const $form = $('form[name="cwp-search-filters"]');
        if (!$form.length) return;

        const url = new URL(window.location.href);

        url.search = '';

        $form.find('input[type="hidden"]').each(function () {
            const name = $(this).attr('name');
            const value = $(this).val();
            if (name) {
                url.searchParams.set(name, value);
            }
        });
        url.searchParams.set('s', '');

        window.history.replaceState(null, null, url.toString());

        setTimeout(function () {
            cwp_search_filters_ajax_content('');
        }, 300);
    }

    // Attach to your reset button (or call directly)
    $(document).on('click', '.cubewp-filter-builder-reset-button-button', function () {
        resetURLAndApplyFormParams();
        $(document).trigger('cubewp_reset_filters');
    });

    function resetConditionalTaxonomyFields() {
        $('.cubewp-filter-builder-field[data-conditional-taxonomy]').each(function () {
            var $conditionalField = $(this);
            var $select = $conditionalField.find('select');
            var $checkboxes = $conditionalField.find('input[type="checkbox"]');
            var $radios = $conditionalField.find('input[type="radio"]');

            clearConditionalField($conditionalField);

            if ($select.length > 0) {
                $select.find('option').prop('disabled', false).show();
                if ($select.hasClass('select2-hidden-accessible') || $select.data('select2')) {
                    $select.trigger('change.select2');
                } else {
                    $select.trigger('change');
                }
            }

            if ($checkboxes.length > 0) {
                $checkboxes.each(function () {
                    var $checkbox = $(this);
                    var $container = $checkbox.closest('li');
                    if ($container.length === 0) {
                        $container = $checkbox.closest('.cwp-field-checkbox');
                    }
                    if ($container.length === 0) {
                        $container = $checkbox.closest('.nextwp-term-container');
                    }
                    if ($container.length === 0) {
                        $container = $checkbox.parent();
                    }
                    $container.show();
                    $checkbox.prop('disabled', false);
                });
            }

            if ($radios.length > 0) {
                $radios.each(function () {
                    var $radio = $(this);
                    var $container = $radio.closest('li');
                    if ($container.length === 0) {
                        $container = $radio.closest('.cwp-field-radio');
                    }
                    if ($container.length === 0) {
                        $container = $radio.parent();
                    }
                    $container.show();
                    $radio.prop('disabled', false);
                });
            }

            $conditionalField.hide();
        });
    }


    $(document).on("click", ".google-address-btn", function () {
        const $btn = $(this);
        const $container = $btn.closest(".cubewp-filter-builder-container");
        const $wrapper = $container.find(".cubewp-filter-builder-field-container");
        const $addressInput = $wrapper.find('input[type="google_address"]');
        const $lat = $wrapper.find('input.latitude');
        const $lng = $wrapper.find('input.longitude');
        const $range = $wrapper.find('input.range');
        // TOGGLE OFF
        if ($btn.hasClass("active")) {
            $addressInput.val("");
            $lat.val("");
            $lng.val("");
            updateURLParam($addressInput.attr('name'), "");
            updateURLParam($addressInput.attr('name') + '_lat', "");
            updateURLParam($addressInput.attr('name') + '_lng', "");
            updateURLParam($addressInput.attr('name') + '_range', "");
            $range.attr('type', 'hidden');
            $btn.removeClass("active");
            $btn.closest(".cubewp-google-address-filter-container").removeClass("active");
            triggerFilterAjax();
            console.log("Near Me disabled – URL updated");
            return;
        }
        if (location.protocol !== "https:") {
            alert("Geolocation requires HTTPS.");
            return;
        }
        if (!navigator.geolocation) {
            alert("Geolocation is not supported by this browser.");
            return;
        }
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({
                    location: {
                        lat: lat,
                        lng: lng
                    }
                },
                    function (results, status) {
                        if (status === "OK" && results[0]) {
                            const address = results[0].formatted_address;
                            $lat.val(lat);
                            $lng.val(lng);
                            // Address text
                            const $addressInput = $wrapper.find('input[type="google_address"]');
                            const addressName = $addressInput.attr('id');
                            $addressInput.val(address);
                            // Send params only if name exists
                            if (addressName) {
                                updateURLParam($addressInput.attr('name'), address);
                            }
                            updateURLParam($addressInput.attr('name') + '_lat', lat);
                            updateURLParam($addressInput.attr('name') + '_lng', lng);
                            $range.attr('type', 'range');
                            updateURLParam($addressInput.attr('name') + '_range', $wrapper.find('input[type="range"]').val());
                            // Activate button
                            $btn.addClass("active");
                            $btn.closest(".cubewp-google-address-filter-container").addClass("active");
                            triggerFilterAjax();
                        } else {
                            alert("Unable to fetch address from Google.");
                        }
                    }
                );
            },
            function (error) {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        alert("Location permission denied. Please enable location.");
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert("Location unavailable.");
                        break;
                    case error.TIMEOUT:
                        alert("Location request timed out.");
                        break;
                    default:
                }
            }
        );
    });


    // Run only in accordion mode
    $(document).on('click', '.cubewp-filter-accordion-mode .cubewp-filter-builder-field label', function () {

        var $field = $(this).closest('.cubewp-filter-builder-field');
        var $container = $field.find('.cubewp-filter-builder-field-container');

        if ($container.is(':visible')) {
            // $container.slideUp(200);
            $field.removeClass('cwp-accordion-open');
        } else {
            // $container.slideDown(200);
            $field.addClass('cwp-accordion-open');
        }
    });


    function getURLParams() {
        return new URLSearchParams(window.location.search);
    }

    function restoreInputsFromURL() {
        const params = getURLParams();

        params.forEach(function (value, name) {
            const $fields = $('[name="' + name + '"]');

            if (!$fields.length) return;

            // Restore select values directly.
            const $select = $fields.filter('select');
            if ($select.length) {
                $select.val(value);
                return;
            }

            // Restore only text-like inputs. Do NOT set hidden values when a checkbox/radio
            // with same name exists, otherwise checkbox value attributes get overwritten.
            const $textLikeInputs = $fields.filter('input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"])');
            if ($textLikeInputs.length) {
                $textLikeInputs.val(value);
                return;
            }

            const hasChoiceFields = $fields.filter('input[type="checkbox"], input[type="radio"]').length > 0;
            if (!hasChoiceFields) {
                $fields.filter('input[type="hidden"]').val(value);
            }
        });
    }

    function restoreCheckboxesFromURL() {
        const params = getURLParams();

        params.forEach(function (value, name) {
            const $checkboxes = $('input[type="checkbox"][name="' + name + '"], input[type="checkbox"][name="' + name + '[]"]');
            if (!$checkboxes.length) return;

            const values = (value || '').split(',').map(function (val) {
                return $.trim(val);
            }).filter(function (val) {
                return val !== '';
            });

            $checkboxes.prop('checked', false);

            values.forEach(function (val) {
                $checkboxes.filter(function () {
                    return $(this).val() === val;
                }).prop('checked', true);
            });

            const $hidden = $('input[type="hidden"][name="' + name + '"], input[type="hidden"][name="' + name + '[]"]');
            if ($hidden.length) {
                $hidden.val(values.join(','));
            }
        });
    }

    function restoreRadiosFromURL() {
        const params = getURLParams();

        params.forEach(function (value, name) {
            $('input[type="radio"][name="' + name + '"][value="' + value + '"]')
                .prop('checked', true);
        });
    }

    function restoreGoogleAddressFromURL() {
        const params = getURLParams();
        $('.cubewp-google-address-filter-container').each(function () {
            const $container = $(this);
            const $address = $container.find('input[type="google_address"]');
            const $lat = $container.find('input.latitude');
            const $lng = $container.find('input.longitude');
            const $range = $container.find('input.range');
            const $btn = $container.find('.google-address-btn');
            if (!$address.length) return;
            const addressVal = params.get($address.attr('name'));
            const latVal = params.get($address.attr('name') + '_lat');
            const lngVal = params.get($address.attr('name') + '_lng');
            const rangeVal = params.get($address.attr('name') + '_range');
            if (addressVal && latVal && lngVal) {
                $address.val(addressVal);
                $lat.val(latVal);
                $lng.val(lngVal);
                if ($range.length) {
                    $range.attr('type', 'range');
                }
                $btn.addClass('active');
                $container.addClass('active');
            }
        });
    }

    function restoreSortingFromURL() {
        const params = getURLParams();
        const orderby = params.get('orderby');
        const fieldType = params.get('field_type');
        const sortings = params.get('sortings');

        $('.cubewp-sorting-btn').removeClass('active selected');

        if (fieldType) {
            let $custom = $('.cubewp-sorting-btn-custom[data-orderby="' + fieldType + '"]');
            if (sortings) {
                const $withSortings = $custom.filter('[data-sortings="' + sortings + '"]');
                if ($withSortings.length) {
                    $custom = $withSortings;
                }
            }
            if ($custom.length) {
                $custom.first().addClass('active selected');
                return;
            }
        }

        if (!orderby) return;

        $('.cubewp-sorting-btn').each(function () {
            const $btn = $(this);
            if ($btn.data('orderby') === orderby && !$btn.hasClass('cubewp-sorting-btn-custom')) {
                $btn.addClass('active selected');
                if (orderby === 'relevance') {
                    applyRelevanceParamsAndClasses();
                }
            }
        });
    }

    $(document).ready(function () {
        restoreInputsFromURL();
        restoreCheckboxesFromURL();
        restoreRadiosFromURL();
        restoreGoogleAddressFromURL();
        restoreSortingFromURL();
    });


    /* Conditional Taxonomy */

    /**
     * Initialize conditional taxonomy functionality
     */
    function initConditionalTaxonomy() {
        $('.cubewp-filter-builder-field[data-conditional-taxonomy]').hide();
        // Find all conditional taxonomy fields
        $('.cubewp-filter-builder-field[data-conditional-taxonomy]').each(function () {
            var $conditionalField = $(this);
            var conditionalTaxonomy = $conditionalField.attr('data-conditional-taxonomy') || $conditionalField.data('conditional-taxonomy');
            var conditionalTermMetaKey = $conditionalField.attr('data-conditional-term-meta-key') || $conditionalField.data('conditional-term-meta-key');
            var conditionalTaxonomyField = $conditionalField.attr('data-conditional-taxonomy-field') || $conditionalField.data('conditional-taxonomy-field');

            if (!conditionalTaxonomy || !conditionalTermMetaKey) {
                console.warn('Conditional taxonomy: Missing required attributes', {
                    conditionalTaxonomy: conditionalTaxonomy,
                    conditionalTermMetaKey: conditionalTermMetaKey,
                    conditionalTaxonomyField: conditionalTaxonomyField
                });
                return;
            }

            // Find the parent taxonomy field using the conditional taxonomy name directly
            // The conditionalTaxonomy value is the actual taxonomy name (e.g., "listing-category")
            var $parentField = findParentTaxonomyField(conditionalTaxonomy);

            if ($parentField.length === 0) {
                console.warn('Conditional taxonomy: Parent field not found', {
                    conditionalTaxonomy: conditionalTaxonomy,
                    conditionalTaxonomyField: conditionalTaxonomyField,
                    conditionalField: $conditionalField.attr('data-field-name')
                });
                return;
            }

            var displayType = '';
            if ($parentField.is('select')) {
                displayType = 'select';
            } else if ($parentField.is(':checkbox')) {
                displayType = 'checkbox';
            } else if ($parentField.is(':radio')) {
                displayType = 'radio';
            }

            // Set up change handler for parent field
            setupParentFieldHandler($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);

            // Check initial value on page load
            checkInitialValue($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
        });
    }

    // Initialize Conditional Taxonomy on document ready
    $(document).ready(function () {
        initConditionalTaxonomy();
    });

    //Initialize on search results 
    // $(document).on('cubewp_search_results_loaded', function () {
    //     initConditionalTaxonomy();
    // });

    /**
     * Find parent taxonomy field by field name
     */
    function findParentTaxonomyField(fieldName) {
        // Extract taxonomy name from fieldName (format: taxonomy_taxonomy_name or just taxonomy_name)
        // The actual field name in HTML uses _ST_ prefix: _ST_taxonomy_name
        var taxonomyName = '';
        if (fieldName.indexOf('taxonomy_') === 0) {
            taxonomyName = fieldName.replace('taxonomy_', '');
        } else {
            taxonomyName = fieldName;
        }

        // Construct the actual field name as it appears in the form (CubeWP uses _ST_ prefix)
        var actualFieldName = '_ST_' + taxonomyName;
        var actualFieldNameArray = '_ST_' + taxonomyName + '[]';

        // Try different selectors based on field type
        var selectors = [
            // Exact name match for select dropdown (CubeWP filter builder format)
            'select[name="' + actualFieldName + '"]',
            'select[name="' + actualFieldNameArray + '"]',
            // ID selector (fields often have ID matching taxonomy name)
            '#' + taxonomyName,
            'select#' + taxonomyName,
            // Name contains for flexibility
            'select[name*="' + taxonomyName + '"]',
            'select[name*="_ST_' + taxonomyName + '"]',
            // Checkbox and radio inputs
            'input[type="checkbox"][name="' + actualFieldNameArray + '"]',
            'input[type="checkbox"][name*="' + taxonomyName + '"]',
            'input[type="checkbox"][name*="_ST_' + taxonomyName + '"]',
            'input[type="radio"][name="' + actualFieldName + '"]',
            'input[type="radio"][name*="' + taxonomyName + '"]',
            // Class selector (taxonomy fields have cwp-taxonomy-field class)
            '.cwp-taxonomy-field[name="' + actualFieldName + '"]',
            '.cwp-taxonomy-field[name*="' + taxonomyName + '"]',
            '.cwp-taxonomy-field[id="' + taxonomyName + '"]',
            '.cwp-taxonomy-field[id*="' + taxonomyName + '"]',
            // Find by data-field-name attribute (used in filter builder)
            '.cubewp-filter-builder-field[data-field-name="' + taxonomyName + '"] select',
            '.cubewp-filter-builder-field[data-field-name="' + taxonomyName + '"] input',
            // Generic name contains (last resort)
            '[name*="' + taxonomyName + '"]'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var $field = $(selectors[i]);
            if ($field.length > 0) {
                // For checkboxes/radios, return the first one or the container
                if ($field.is(':checkbox') || $field.is(':radio')) {
                    // Return the first input, we'll handle multiple in the change handler
                    return $field.first();
                }
                return $field.first();
            }
        }

        // If still not found, try to find within widget containers
        // Look for a field container with data-field-name matching the taxonomy
        var $fieldContainer = $('.cubewp-filter-builder-field[data-field-name="' + taxonomyName + '"]');
        if ($fieldContainer.length > 0) {
            // Find select or input within this container
            var $field = $fieldContainer.find('select, input[type="checkbox"], input[type="radio"]').first();
            if ($field.length > 0) {
                return $field;
            }
        }

        // Last resort: search all filter builder containers
        var $foundField = $();
        $('.cubewp-filter-builder-container').each(function () {
            var $container = $(this);
            var $field = $container.find('select[name*="' + taxonomyName + '"], input[name*="' + taxonomyName + '"]').first();
            if ($field.length > 0) {
                $foundField = $field;
                return false; // Break the each loop
            }
        });

        return $foundField;
    }

    /**
     * Set up change handler for parent taxonomy field
     */
    function setupParentFieldHandler($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType) {
        // Handle select dropdown
        if ($parentField.is('select')) {
            displayType = displayType || 'select';
            // Remove existing handlers to avoid duplicates
            $parentField.off('change.conditionalTaxonomy select2:select.conditionalTaxonomy');

            // Handle regular select change
            $parentField.on('change.conditionalTaxonomy', function () {
                handleParentFieldChange($(this), $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
            });

            // Handle Select2 if present
            if ($parentField.hasClass('select2-hidden-accessible') || $parentField.data('select2')) {
                $parentField.on('select2:select.conditionalTaxonomy', function () {
                    handleParentFieldChange($(this), $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
                });
            }
        }
        // Handle checkbox - need to bind to all inputs with same name
        else if ($parentField.is(':checkbox')) {
            displayType = displayType || 'checkbox';
            // Get the name attribute to find all related inputs
            var fieldName = $parentField.attr('name');
            if (fieldName) {
                // Remove [] from name for selector
                var selectorName = fieldName.replace(/\[\]$/, '');
                var escapedSelector = selectorName.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&');

                // Remove existing handlers
                $(document).off('change.conditionalTaxonomy', 'input[name="' + escapedSelector + '"], input[name="' + escapedSelector + '[]"]');

                // Bind to all checkboxes with the same name
                $(document).on('change.conditionalTaxonomy', 'input[name="' + escapedSelector + '"], input[name="' + escapedSelector + '[]"]', function () {
                    var $allFields = $('input[name="' + escapedSelector + '"], input[name="' + escapedSelector + '[]"]');
                    handleParentFieldChange($allFields, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
                });
            } else {
                // Fallback: bind directly to the field
                $parentField.off('change.conditionalTaxonomy').on('change.conditionalTaxonomy', function () {
                    var $allFields = $('input[name="' + $parentField.attr('name') + '"]');
                    handleParentFieldChange($allFields.filter(':checked'), $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
                });
            }
        }
        // Handle radio buttons
        else if ($parentField.is(':radio')) {
            displayType = displayType || 'radio';
            var fieldName = $parentField.attr('name');
            if (fieldName) {
                var escapedSelector = fieldName.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&');
                $(document).off('change.conditionalTaxonomy', 'input[type="radio"][name="' + escapedSelector + '"]');
                $(document).on('change.conditionalTaxonomy', 'input[type="radio"][name="' + escapedSelector + '"]', function () {
                    var $allFields = $('input[type="radio"][name="' + escapedSelector + '"]');
                    handleParentFieldChange($allFields.filter(':checked'), $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
                });
            }
        }
    }

    /**
     * Handle parent field change
     */
    function handleParentFieldChange($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType) {
        var selectedTerms = getSelectedTerms($parentField);

        if (selectedTerms.length === 0) {
            // No terms selected, hide conditional field
            $conditionalField.hide();
            clearConditionalField($conditionalField);
            return;
        }

        // Get associated terms from term meta (async)
        getAssociatedTerms(selectedTerms, conditionalTermMetaKey, conditionalTaxonomy, displayType, function (associatedTerms) {
            if (associatedTerms.length === 0) {
                // No associated terms found, hide conditional field
                $conditionalField.hide();
                clearConditionalField($conditionalField);
                return;
            }

            // Show conditional field and filter terms
            $conditionalField.show();
            filterConditionalFieldTerms($conditionalField, associatedTerms);
        });
    }

    /**
     * Get selected terms from parent field
     */
    function getSelectedTerms($parentField) {
        var selectedTerms = [];

        // Handle jQuery collection (multiple fields)
        if ($parentField.length > 1 || ($parentField.length === 1 && ($parentField.is(':checkbox') || $parentField.is(':radio')))) {
            // For checkboxes/radios, get all checked values
            $parentField.filter(':checked').each(function () {
                var val = $(this).val();
                if (val && val !== '') {
                    selectedTerms.push(val);
                }
            });
        } else if ($parentField.is('select')) {
            if ($parentField.prop('multiple')) {
                selectedTerms = $parentField.val() || [];
            } else {
                var val = $parentField.val();
                if (val && val !== '' && val !== null) {
                    selectedTerms = [val];
                }
            }
        } else if ($parentField.is(':checkbox')) {
            $parentField.filter(':checked').each(function () {
                selectedTerms.push($(this).val());
            });
        } else if ($parentField.is(':radio')) {
            var val = $parentField.filter(':checked').val();
            if (val && val !== '') {
                selectedTerms = [val];
            }
        }

        // Convert to integers and filter out invalid values
        return selectedTerms.map(function (term) {
            return parseInt(term, 10);
        }).filter(function (term) {
            return !isNaN(term) && term > 0;
        });
    }

    /**
     * Get associated terms from term meta (async)
     */
    function getAssociatedTerms(selectedTermIds, termMetaKey, taxonomyName, displayType, callback) {
        if (selectedTermIds.length === 0 || !termMetaKey) {
            if (typeof callback === 'function') {
                callback([]);
            }
            return;
        }

        // Get AJAX URL and nonce (with fallback)
        var ajaxUrl = (typeof cwp_filter_builder_params !== 'undefined' && cwp_filter_builder_params.ajax_url)
            ? cwp_filter_builder_params.ajax_url
            : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
        var nonce = (typeof cwp_filter_builder_params !== 'undefined' && cwp_filter_builder_params.nonce)
            ? cwp_filter_builder_params.nonce
            : '';

        // Make AJAX call to get associated terms
        $.ajax({
            url: cwp_filter_builder_params.ajax_url,
            type: 'POST',
            data: {
                action: 'cubewp_get_filters_conditional_taxonomy_terms',
                term_ids: selectedTermIds,
                term_meta_key: termMetaKey,
                taxonomy_name: taxonomyName,
                display_type: displayType,
                nonce: cwp_filter_builder_params.nonce
            },
            success: function (response) {
                var associatedTerms = [];
                if (response && response.success && response.data && Array.isArray(response.data)) {
                    associatedTerms = response.data;
                } else if (response && !response.success) {
                    console.warn('Conditional taxonomy AJAX error:', response.data || response.message || 'Unknown error');
                }
                if (typeof callback === 'function') {
                    callback(associatedTerms);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error fetching conditional taxonomy terms:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                if (typeof callback === 'function') {
                    callback([]);
                }
            }
        });
    }

    /**
     * Filter conditional field terms
     */
    function filterConditionalFieldTerms($conditionalField, allowedTermIds) {
        var $select = $conditionalField.find('select');
        var $checkboxes = $conditionalField.find('input[type="checkbox"]');
        var $radios = $conditionalField.find('input[type="radio"]');

        // Handle select dropdown
        if ($select.length > 0) {
            $select.find('option').each(function () {
                var $option = $(this);
                var termId = parseInt($option.val(), 10);

                if (termId === 0 || termId === '' || $option.is(':disabled')) {
                    // Keep default/empty options
                    return;
                }

                if (allowedTermIds.indexOf(termId) === -1) {
                    $option.hide().prop('disabled', true);
                } else {
                    $option.show().prop('disabled', false);
                }
            });

            // If current selection is not in allowed terms, clear it
            var currentVal = $select.val();
            if (currentVal && allowedTermIds.indexOf(parseInt(currentVal, 10)) === -1) {
                $select.val('').trigger('change');
            }
        }

        // Handle checkboxes
        if ($checkboxes.length > 0) {
            $checkboxes.each(function () {
                var $checkbox = $(this);
                var termId = parseInt($checkbox.val(), 10);

                // Find the parent container (could be .cwp-field-checkbox, li, or .nextwp-term-container)
                var $container = $checkbox.closest('li');
                if ($container.length === 0) {
                    $container = $checkbox.closest('.cwp-field-checkbox');
                }
                if ($container.length === 0) {
                    $container = $checkbox.closest('.nextwp-term-container');
                }
                if ($container.length === 0) {
                    $container = $checkbox.parent();
                }

                if (allowedTermIds.indexOf(termId) === -1) {
                    // Hide the container (li or parent element)
                    $container.hide();
                    $checkbox.prop('disabled', true);
                    if ($checkbox.is(':checked')) {
                        $checkbox.prop('checked', false).trigger('change');
                    }
                } else {
                    // Show the container
                    $container.show();
                    $checkbox.prop('disabled', false);
                }
            });

            // Update "Show More" button visibility if present
            var $showMoreBtn = $conditionalField.find('.nextwp-see-more-category');
            if ($showMoreBtn.length > 0) {
                var visibleCheckboxes = $checkboxes.closest('li:visible').length;
                if (visibleCheckboxes === 0) {
                    $showMoreBtn.hide();
                } else {
                    $showMoreBtn.show();
                }
            }
        }
    }

    /**
     * Clear conditional field
     */
    function clearConditionalField($conditionalField) {
        var $select = $conditionalField.find('select');
        var $checkboxes = $conditionalField.find('input[type="checkbox"]');
        var $radios = $conditionalField.find('input[type="radio"]');

        if ($select.length > 0) {
            $select.val('').trigger('change');
        }

        if ($checkboxes.length > 0) {
            $checkboxes.prop('checked', false);
        }

        if ($radios.length > 0) {
            $radios.prop('checked', false);
        }
    }

    /**
     * Check initial value on page load
     */
    function checkInitialValue($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType) {
        var selectedTerms = getSelectedTerms($parentField);

        if (selectedTerms.length > 0) {
            setTimeout(function () {
                handleParentFieldChange($parentField, $conditionalField, conditionalTaxonomy, conditionalTermMetaKey, displayType);
            }, 100);
        }
    }

})(jQuery);
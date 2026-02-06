/**
 * CubeWP Filter Builder Module
 * 
 * Separate module for filter builder widget that works independently
 * Uses the same AJAX PHP function as the existing filter system
 * 
 * @package cubewp-framework
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Prefix for all filter builder classes and names
    var PREFIX = 'cubewp-filter-builder-';
    var FORM_CLASS = 'cubewp-filter-builder-form';
    var CONTAINER_CLASS = 'cubewp-filter-builder-container';
    
    var typingTimer;
    var doneTypingInterval = 200;
    var ajaxTimer;
    var ajaxDelay = 300; // Delay for AJAX calls to prevent multiple rapid calls

    /**
     * Initialize filter builder module
     */
    function initFilterBuilder() { 
        getOrCreateForm();

        // Check if filter builder widgets exist on the page
        if ($('.' + CONTAINER_CLASS).length > 0) {
            // Populate fields from URL parameters on page load
            populateFieldsFromURL();
            
            // Collect data from all widget instances
            collectAllFilterData();
            
            // If there are URL parameters, trigger AJAX to show results
            if (hasURLParameters()) {
                // Small delay to ensure all fields are populated and DOM is ready
                setTimeout(function() {
                    // Re-collect data after populating fields
                    collectAllFilterData();
                    
                    // Check if archive container exists (where results should be displayed)
                    if ($('.cwp-archive-container').length > 0 || $('.cwp-search-result-output').length > 0) {
                        if (typeof cwp_search_filters_ajax_content === 'function') {
                            cwp_search_filters_ajax_content();
                        }
                    } else {
                        // If no archive container, try to find it or create a placeholder
                        // This prevents 404 by ensuring we have a place to show results
                        console.log('CubeWP Filter Builder: Archive container not found. Results will be loaded via AJAX when container is available.');
                    }
                }, 500);
            }
        }

        // Set up event listeners
        setupEventListeners();
    }

    /**
     * Check if URL has filter parameters
     */
    function hasURLParameters() {
        var urlParams = new URLSearchParams(window.location.search);
        var hasParams = false;
        
        // Check for common filter parameters
        urlParams.forEach(function(value, key) {
            // Skip non-filter parameters
            if (key !== 'page' && key !== 'paged' && key !== 's' && 
                key !== 'action' && !key.startsWith('cubewp_') && 
                !key.startsWith('_wp_') && key !== 'order' && key !== 'orderby') {
                hasParams = true;
                return false; // break
            }
        });
        
        return hasParams;
    }

    /**
     * Populate filter builder fields from URL parameters
     */
    function populateFieldsFromURL() {
        var urlParams = new URLSearchParams(window.location.search);
        
        // Iterate through all filter builder containers
        $('.' + CONTAINER_CLASS).each(function() {
            var $container = $(this);
            
            // Populate text inputs
            $container.find('input[type="text"], input[type="number"]').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                if (name && urlParams.has(name)) {
                    var value = urlParams.get(name);
                    $input.val(decodeURIComponent(value));
                }
            });
            
            // Populate selects
            $container.find('select').each(function() {
                var $select = $(this);
                var name = $select.attr('name');
                if (name && urlParams.has(name)) {
                    var value = urlParams.get(name);
                    $select.val(decodeURIComponent(value)).trigger('change');
                }
            });
            
            // Populate textareas
            $container.find('textarea').each(function() {
                var $textarea = $(this);
                var name = $textarea.attr('name');
                if (name && urlParams.has(name)) {
                    var value = urlParams.get(name);
                    $textarea.val(decodeURIComponent(value));
                }
            });
            
            // Populate checkboxes (taxonomy fields with _ST_ prefix)
            $container.find('.cwp-search-field-checkbox, .cwp-field-checkbox-container').each(function() {
                var $checkboxContainer = $(this);
                var $hiddenField = $checkboxContainer.find('input[type="hidden"]');
                
                if ($hiddenField.length > 0) {
                    var name = $hiddenField.attr('name');
                    if (name && urlParams.has(name)) {
                        var value = urlParams.get(name);
                        var values = decodeURIComponent(value).split(',');
                        
                        // Set hidden field value
                        $hiddenField.val(value);
                        
                        // Check the corresponding checkboxes
                        $checkboxContainer.find('input[type="checkbox"]').each(function() {
                            var $checkbox = $(this);
                            var checkboxValue = $checkbox.val();
                            if ($.inArray(checkboxValue, values) !== -1) {
                                $checkbox.prop('checked', true);
                            }
                        });
                    }
                }
            });
            
            // Populate radio buttons
            $container.find('input[type="radio"]').each(function() {
                var $radio = $(this);
                var name = $radio.attr('name');
                var value = $radio.val();
                if (name && urlParams.has(name)) {
                    var urlValue = decodeURIComponent(urlParams.get(name));
                    if (value === urlValue) {
                        $radio.prop('checked', true);
                    }
                }
            });
            
            // Handle taxonomy fields (they use _ST_ prefix in URL)
            urlParams.forEach(function(value, key) {
                // Check if this is a taxonomy parameter (starts with _ST_)
                if (key.startsWith('_ST_')) {
                    var taxonomyValue = decodeURIComponent(value);
                    
                    // Find taxonomy fields in this container that match this taxonomy
                    // The hidden field name should match the URL parameter (with _ST_ prefix)
                    $container.find('.cwp-search-field-checkbox, .cwp-field-checkbox-container').each(function() {
                        var $taxContainer = $(this);
                        var $hiddenField = $taxContainer.find('input[type="hidden"][name="' + key + '"]');
                        
                        if ($hiddenField.length > 0) {
                            // Set hidden field value
                            $hiddenField.val(taxonomyValue);
                            
                            // Check the corresponding checkboxes
                            var values = taxonomyValue.split(',');
                            $taxContainer.find('input[type="checkbox"]').each(function() {
                                var $checkbox = $(this);
                                var checkboxValue = $checkbox.val();
                                if ($.inArray(checkboxValue, values) !== -1) {
                                    $checkbox.prop('checked', true);
                                }
                            });
                            
                            // Trigger change event to update the hidden field properly
                            $taxContainer.find('input[type="checkbox"]:checked').first().trigger('change');
                        }
                    });
                }
            });
            
            // Populate business hours filter buttons from URL
            $container.find('.cubewp-business-hours-filter').each(function() {
                var $bhContainer = $(this);
                var fieldName = $bhContainer.data('field-name');
                var $statusInput = $bhContainer.find('.cubewp-business-hours-status').first();
                
                if (!$statusInput.length) {
                    return; // Skip if no status input found
                }
                
                // Get the actual field name from the status input's data attribute
                var actualFieldName = $statusInput.data('field-name') || fieldName;
                // Try to find status parameter - check standard format (field_name_status)
                var statusParam = actualFieldName + '_status';
                var statusValue = null;
                
                // First try the standard format
                if (urlParams.has(statusParam)) {
                    statusValue = decodeURIComponent(urlParams.get(statusParam));
                } else {
                    // Fallback: try old format for backward compatibility
                    var oldStatusParam = fieldName + '_business_hours_status';
                    if (urlParams.has(oldStatusParam)) {
                        statusValue = decodeURIComponent(urlParams.get(oldStatusParam));
                    }
                }
                
                if (statusValue) {
                    var $button = $bhContainer.find('.cubewp-business-hours-btn[data-filter-type="' + statusValue + '"]');
                    
                    if ($button.length > 0) {
                        $statusInput.val(statusValue);
                        $button.addClass('active');
                    }
                }
            });
            
            // Populate select fields from URL parameters
            $container.find('select').each(function() {
                var $select = $(this);
                var name = $select.attr('name');
                
                if (name && urlParams.has(name)) {
                    var value = decodeURIComponent(urlParams.get(name));
                    // Handle comma-separated values for multi-select
                    if ($select.prop('multiple')) {
                        var values = value.split(',');
                        $select.val(values);
                    } else {
                        $select.val(value);
                    }
                    $select.trigger('change');
                }
            });
            
            // Populate select fields from URL parameters
            $container.find('select').each(function() {
                var $select = $(this);
                var name = $select.attr('name');
                
                if (name && urlParams.has(name)) {
                    var value = decodeURIComponent(urlParams.get(name));
                    $select.val(value);
                    $select.trigger('change');
                }
            });
        });
    }

    /**
     * Get or create form for filter data
     * First checks if existing cwp-search-filters form exists, otherwise creates our own
     */
    function getOrCreateForm() {
        // First, check if existing cwp-search-filters form exists (but not our own filter builder form)
        var $existingForm = $('.cwp-search-filters').not('.' + FORM_CLASS);
        
        if ($existingForm.length > 0) {
            // Use existing form - ensure it has required hidden fields
            var $form = $existingForm.first();
            
            // Check if required hidden fields exist, if not add them
            if ($form.find('input[name="page_num"]').length === 0) {
                $form.append('<input type="hidden" name="page_num" value="1">');
            }
            if ($form.find('input[name="page"]').length === 0) {
                $form.append('<input type="hidden" name="page" value="page">');
            }
            
            // Ensure form has cwp-search-filters-fields container
            if ($form.find('.cwp-search-filters-fields').length === 0) {
                $form.append('<div class="cwp-search-filters-fields"></div>');
            }
            
            return $form;
        }
        
        // Check if our own filter builder form already exists
        if ($('.' + FORM_CLASS).length > 0) {
            return $('.' + FORM_CLASS).first();
        }

        // Create new hidden form
        var $form = $('<form>', {
            'class': FORM_CLASS + ' cwp-search-filters',
            'name': 'cubewp-filter-builder-form',
            'method': 'post',
            'style': 'display: none;'
        });

        // Add hidden fields that CubeWP filters expect
        $form.append('<input type="hidden" name="page_num" value="1">');
        $form.append('<input type="hidden" name="page" value="page">');
        $form.append('<div class="cwp-search-filters-fields"></div>');

        // Append to body
        $('body').append($form);
        
        return $form;
    }

    /**
     * Collect all filter data from all widget instances on the page
     */
    function collectAllFilterData() {
        // Get or create form (checks for existing cwp-search-filters first)
        var $form = getOrCreateForm();
        
        // Get the fields container (use existing or create)
        var $fieldsContainer = $form.find('.cwp-search-filters-fields');
        if ($fieldsContainer.length === 0) {
            $fieldsContainer = $('<div>', { 'class': 'cwp-search-filters-fields' });
            $form.append($fieldsContainer);
        }

        // Track which fields we've collected in this pass
        var collectedFields = {};

        var postType = '';
        var hasData = false;

        // Check if we have any filter builder containers
        if ($('.' + CONTAINER_CLASS).length === 0) {
            return; // No filter builder widgets on page
        }

        // Collect data from all filter builder containers
        $('.' + CONTAINER_CLASS).each(function() {
            var $container = $(this);
            var containerPostType = $container.data('post-type');
            
            if (!postType && containerPostType) {
                postType = containerPostType;
            }
            

            // First, collect all hidden fields (these store checkbox/radio values)
            // This is important for taxonomy fields and checkbox fields
            // Look in checkbox containers first, then general hidden fields
            $container.find('.cwp-search-field-checkbox input[type="hidden"], .cwp-field-checkbox-container input[type="hidden"], input[type="hidden"]').each(function() {
                var $hidden = $(this);
                var name = $hidden.attr('name');
                var value = $hidden.val() || '';
                value = value.trim();
 
                // Skip if no name or if it's a system field
                if (!name || name === 'page_num' || name === 'page' || name === 'post_type') {
                    return;
                }
                
                // Skip business hours status fields - they will be collected separately
                if ($hidden.hasClass('cubewp-business-hours-status')) {
                    return;
                }

                // Always collect fields from filter builder containers
                // Only skip if the field is already in the form AND it's NOT from a filter builder container
                var isFromFilterBuilder = $hidden.closest('.cubewp-filter-builder-container').length > 0;
                
                if (!isFromFilterBuilder && $hidden.closest('.cwp-search-filters').length > 0) {
                    // It's already in the form (from existing form, not filter builder), update it if needed
                    var existingInput = $form.find('input[name="' + name + '"]').not('.' + PREFIX + 'field');
                    if (existingInput.length > 0) {
                        // Update existing field value
                        if (value && value !== '') {
                            existingInput.val(value);
                            collectedFields[name] = true;
                        } else {
                            existingInput.val('');
                        }
                        return;
                    }
                }
                
                // If it's from filter builder, always collect it (don't return early)
                
                // Collect this hidden field (including taxonomy fields with _ST_ prefix)
                var existingInput = $form.find('input[name="' + name + '"]');
                
                // Track that we've collected this field
                collectedFields[name] = true;
                
                if (value && value !== '') {
                    // Has value - add or update
                    if (existingInput.length > 0) {
                        existingInput.val(value);
                    } else {
                        $fieldsContainer.append($('<input>', {
                            type: 'hidden',
                            name: name,
                            value: value,
                            'class': PREFIX + 'field'
                        }));
                        hasData = true;
                    }
                } else {
                    // Empty value - clear the value but don't remove yet
                    // We'll clean up empty fields at the end
                    if (existingInput.length > 0) {
                        if (existingInput.hasClass(PREFIX + 'field')) {
                            // Only clear our own fields, don't remove yet
                            existingInput.val('');
                        } else {
                            // If it's an existing form field, just clear the value
                            existingInput.val('');
                        }
                    }
                }
            });

            // Collect business hours status fields (special handling)
            // Only collect from THIS container if it has a value (meaning it was clicked)
            $container.find('.cubewp-business-hours-status').each(function() {
                var $statusInput = $(this);
                var value = $statusInput.val() || '';
                value = value.trim();
                
                // Get the actual field name from data attribute for form submission
                var fieldName = $statusInput.data('field-name');
                if (!fieldName) {
                    return; // Skip if no field name
                }
                
                // Use standard format for form submission (field_name_status)
                // This ensures all widgets with the same field name use the same parameter
                var formFieldName = fieldName + '_status';
                
                // Only collect if THIS widget has a value (was clicked)
                if (value) {
                    collectedFields[formFieldName] = true;
                    var existingInput = $form.find('input[name="' + formFieldName + '"]');
                    if (existingInput.length > 0) {
                        existingInput.val(value);
                    } else {
                        $fieldsContainer.append($('<input>', {
                            type: 'hidden',
                            name: formFieldName,
                            value: value,
                            'class': PREFIX + 'field'
                        }));
                        hasData = true;
                    }
                } else {
                    // This widget doesn't have a value (unchecked)
                    // Check if any other widget with this field name has a value
                    var otherWidgetHasValue = false;
                    $('.' + CONTAINER_CLASS).find('.cubewp-business-hours-status[data-field-name="' + fieldName + '"]').each(function() {
                        var otherValue = $(this).val() || '';
                        if (otherValue.trim() && $(this)[0] !== $statusInput[0]) {
                            otherWidgetHasValue = true;
                            return false; // Break the loop
                        }
                    });
                    
                    // If no other widget has a value, keep field but set to empty
                    if (!otherWidgetHasValue) {
                        var existingInput = $form.find('input[name="' + formFieldName + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val('');
                        } else {
                            // Create hidden field with empty value
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: formFieldName,
                                value: '',
                                'class': PREFIX + 'field'
                            }));
                        }
                    }
                }
            });

            // Collect all other inputs, selects, textareas from this container
            $container.find('input:not([type="hidden"]):not(.cubewp-business-hours-status), select, textarea').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var type = $input.attr('type');
                var tagName = $input.prop('tagName') ? $input.prop('tagName').toLowerCase() : '';
                var value = '';

                // Skip if no name
                if (!name) {
                    return;
                }

                // Always collect fields from filter builder containers
                // Only skip if the field is already in the form AND it's NOT from a filter builder container
                var isFromFilterBuilder = $input.closest('.cubewp-filter-builder-container').length > 0;
                var isInForm = $input.closest('.cwp-search-filters').length > 0;
                
                if (!isFromFilterBuilder && isInForm) {
                    // It's already in the form (from existing form, not filter builder), update it if needed
                    var existingInput = $form.find('[name="' + name + '"]');
                    if (existingInput.length > 0) {
                        if (tagName === 'select') {
                            existingInput.val($input.val());
                        } else {
                            existingInput.val(value);
                        }
                        collectedFields[name] = true;
                    }
                    return;
                }
                
                // If it's from filter builder, always collect it (don't return early)

                // Skip checkboxes - they're handled by hidden fields above
                if (type === 'checkbox') {
                    return;
                }

                // Handle select fields (including taxonomy selects)
                if (tagName === 'select') {
                    value = $input.val() || '';
                    
                    // For multi-select, get all selected values as comma-separated
                    if ($input.prop('multiple')) {
                        var selectedValues = $input.val();
                        value = selectedValues && selectedValues.length > 0 ? selectedValues.join(',') : '';
                    }
                    
                    value = value.toString().trim();
                    
                    if (value) {
                        collectedFields[name] = true;
                        var existingInput = $form.find('input[name="' + name + '"], select[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            if (existingInput.is('select')) {
                                existingInput.val($input.val());
                            } else {
                                existingInput.val(value);
                            }
                        } else {
                            // Add as hidden field for consistency with other fields
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: value,
                                'class': PREFIX + 'field'
                            }));
                            hasData = true;
                        }
                    } else {
                        // Empty value (placeholder selected) - keep field but set value to empty
                        var existingInput = $form.find('[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val('');
                        } else {
                            // Create hidden field with empty value
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: '',
                                'class': PREFIX + 'field'
                            }));
                        }
                    }
                } else if (type === 'radio') {
                    // Handle radio buttons
                    // First, check if any radio in this group is checked
                    var $radioGroup = $container.find('input[type="radio"][name="' + name + '"]');
                    var $checkedRadio = $radioGroup.filter(':checked');
                    
                    if ($checkedRadio.length > 0) {
                        // A radio is checked - collect its value
                        value = $checkedRadio.val();
                        collectedFields[name] = true;
                        var existingInput = $form.find('input[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val(value);
                        } else {
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: value,
                                'class': PREFIX + 'field'
                            }));
                            hasData = true;
                        }
                    } else {
                        // No radio is checked - keep field but set value to empty
                        var existingInput = $form.find('input[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val('');
                        } else {
                            // Create hidden field with empty value
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: '',
                                'class': PREFIX + 'field'
                            }));
                        }
                    }
                } else {
                    // For text, number, textarea, etc.
                    value = $input.val() || '';
                    value = value.toString().trim();
                    
                    if (value) {
                        collectedFields[name] = true;
                        var existingInput = $form.find('input[name="' + name + '"], select[name="' + name + '"], textarea[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val(value);
                        } else {
                            // Add as hidden field
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: value,
                                'class': PREFIX + 'field'
                            }));
                            hasData = true;
                        }
                    } else {
                        // Empty value - keep field but set value to empty
                        var existingInput = $form.find('[name="' + name + '"]');
                        if (existingInput.length > 0) {
                            existingInput.val('');
                        } else {
                            // Create hidden field with empty value
                            $fieldsContainer.append($('<input>', {
                                type: 'hidden',
                                name: name,
                                value: '',
                                'class': PREFIX + 'field'
                            }));
                        }
                    }
                }
            });
        });

        // Final cleanup: set business hours fields to empty if they don't have a value in any widget
        // This is a comprehensive check to ensure empty fields have empty values
        var allBusinessHoursFields = {};
        $('.' + CONTAINER_CLASS).find('.cubewp-business-hours-status').each(function() {
            var $statusInput = $(this);
            var fieldName = $statusInput.data('field-name');
            if (!fieldName) {
                return;
            }
            
            var formFieldName = fieldName + '_status'
            var value = $statusInput.val() || '';
            value = value.trim();
            
            // Track if this field name has any value across all widgets
            if (!allBusinessHoursFields.hasOwnProperty(formFieldName)) {
                allBusinessHoursFields[formFieldName] = false;
            }
            
            if (value) {
                allBusinessHoursFields[formFieldName] = true;
            }
        });
        
        // Set business hours fields to empty if they have no value in any widget
        for (var formFieldName in allBusinessHoursFields) {
            if (allBusinessHoursFields.hasOwnProperty(formFieldName) && !allBusinessHoursFields[formFieldName]) {
                var $bhField = $form.find('input[name="' + formFieldName + '"]');
                if ($bhField.length > 0) {
                    $bhField.val('');
                } else {
                    // Create hidden field with empty value
                    $fieldsContainer.append($('<input>', {
                        type: 'hidden',
                        name: formFieldName,
                        value: '',
                        'class': PREFIX + 'field'
                    }));
                }
            }
        }
        
        // Also set business hours fields to empty if they weren't collected
        $form.find('input[class*="' + PREFIX + 'field"]').each(function() {
            var $field = $(this);
            var fieldName = $field.attr('name');
            
            // Check if it's a business hours field (ends with _status)
            if (fieldName && fieldName.indexOf('_status') !== -1) {
                // Check if it was collected (meaning it has a value)
                if (!collectedFields[fieldName]) {
                    // Not collected, so set to empty
                    $field.val('');
                }
            }
        });

        // Set post type if we have one
        if (postType) {
            var $postTypeInput = $form.find('input[name="post_type"]');
            if ($postTypeInput.length === 0) {
                $fieldsContainer.append($('<input>', {
                    type: 'hidden',
                    name: 'post_type',
                    value: postType,
                    'class': PREFIX + 'field'
                }));
            } else {
                $postTypeInput.val(postType);
            }
        }
        
        // Clean up: set empty values for filter builder fields (except system fields)
        // Keep all fields but ensure empty ones have empty string values
        $form.find('.' + PREFIX + 'field').each(function() {
            var $field = $(this);
            var fieldValue = $field.val();
            var fieldName = $field.attr('name');
            
            // Skip system fields - never modify these
            if (fieldName === 'post_type' || fieldName === 'page_num' || fieldName === 'page') {
                return;
            }
            
            // Check if field is empty
            var isEmpty = !fieldValue || fieldValue === '' || fieldValue.trim() === '';
            
            // If field is empty, ensure it has an empty string value
            if (isEmpty) {
                $field.val('');
            }
        });

        return hasData;
    }

    /**
     * Setup event listeners for filter changes
     */
    function setupEventListeners() {
        // Debounced function to trigger AJAX (prevents multiple calls)
        function triggerFilterAjax() {
            clearTimeout(ajaxTimer);
            ajaxTimer = setTimeout(function() {
                collectAllFilterData();
                if (typeof cwp_search_filters_ajax_content === 'function') {
                    cwp_search_filters_ajax_content();
                }
            }, ajaxDelay);
        }
        
        // Listen to text/number inputs and textareas (not checkboxes, radios, or selects - they have specific handlers)
        $(document).on('input keyup', '.' + CONTAINER_CLASS + ' input[type="text"], .' + CONTAINER_CLASS + ' input[type="number"], .' + CONTAINER_CLASS + ' input[type="date"], .' + CONTAINER_CLASS + ' textarea', function() {
            var $input = $(this);
            var value = $input.val() || '';
            value = value.trim();
            
            // If input is cleared, keep field but set value to empty
            if (!value || value === '') {
                var name = $input.attr('name');
                if (name) {
                    var $form = getOrCreateForm();
                    var existingInput = $form.find('[name="' + name + '"]');
                    if (existingInput.length > 0) {
                        existingInput.val('');
                    } else {
                        // Create hidden field with empty value
                        var $fieldsContainer = $form.find('.cwp-search-filters-fields');
                        if ($fieldsContainer.length === 0) {
                            $fieldsContainer = $form;
                        }
                        $fieldsContainer.append($('<input>', {
                            type: 'hidden',
                            name: name,
                            value: '',
                            'class': PREFIX + 'field'
                        }));
                    }
                }
            }
            
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                // Re-collect data to ensure fields are updated
                collectAllFilterData();
                triggerFilterAjax();
            }, doneTypingInterval);
        });

        // Handle checkbox changes
        // The existing search-filters.js handles checkbox changes for .cwp-search-field-checkbox
        // We need to also handle checkboxes in our filter builder containers
        // Stop propagation to prevent multiple handlers from firing
        $(document).on('change', '.' + CONTAINER_CLASS + ' input[type="checkbox"]', function(e) {

            // Stop event from bubbling to prevent other handlers
            e.stopPropagation();
            var $checkbox = $(this);
            
            // Check if this is inside a checkbox container that the existing script handles
            var $checkboxContainer = $checkbox.closest('.cwp-search-field-checkbox, .cwp-field-checkbox-container');
            var $hiddenField = $checkboxContainer.find('input[type="hidden"]');
            
            // If there's a hidden field, make sure it gets updated
            // The existing search-filters.js should handle this, but we'll also handle it as fallback
            if ($hiddenField.length > 0) {
                var name = $hiddenField.attr('name');
                var currentValue = $hiddenField.val() || '';
                var checkboxValue = $checkbox.val();
                
                // Only update if the existing script hasn't already (check after a tiny delay)
                setTimeout(function() {
                    var updatedValue = $hiddenField.val() || '';
                    
                    // If value wasn't updated by existing script, update it ourselves
                    if (updatedValue === currentValue) {
                        if ($checkbox.is(':checked')) {
                            // Add value
                            if (currentValue === '') {
                                currentValue = checkboxValue;
                            } else {
                                var values = currentValue.split(',');
                                if ($.inArray(checkboxValue, values) === -1) {
                                    currentValue = currentValue + ',' + checkboxValue;
                                } else {
                                    currentValue = updatedValue; // Already there
                                }
                            }
                        } else {
                            // Remove value
                            if (typeof cwp_remove_string_value === 'function') {
                                currentValue = cwp_remove_string_value(currentValue, checkboxValue);
                            } else {
                                // Fallback: manual removal
                                var values = currentValue.split(',');
                                var index = $.inArray(checkboxValue, values);
                                if (index !== -1) {
                                    values.splice(index, 1);
                                    currentValue = values.join(',');
                                }
                            }
                        }
                        
                        // Update hidden field if needed
                        if (currentValue !== updatedValue) {
                            $hiddenField.val(currentValue);
                        }
                    }
                    
                    // Now collect all data and trigger AJAX
                    triggerFilterAjax();
                }, 100);
            } else {
                // No hidden field found, trigger AJAX
                triggerFilterAjax();
            }
        });

        // Handle radio changes - catch all radio buttons including those in cwp-radio-container
        $(document).on('change', '.' + CONTAINER_CLASS + ' input[type="radio"], .cwp-radio-container input[type="radio"]', function() {
            // Re-collect data to ensure radio values are properly set
            collectAllFilterData();
            triggerFilterAjax();
        });
        
        // Also handle radio clicks as fallback (in case change event doesn't fire)
        $(document).on('click', '.' + CONTAINER_CLASS + ' input[type="radio"], .cwp-radio-container input[type="radio"]', function() {
            var $radio = $(this);
            // Use setTimeout to ensure the checked state is updated before collecting data
            setTimeout(function() {
                collectAllFilterData();
                triggerFilterAjax();
            }, 10);
        });

        // Handle select changes
        $(document).on('change', '.' + CONTAINER_CLASS + ' select', function() {
            var $select = $(this);
            var value = $select.val(); 
            // If empty value selected, keep field but set value to empty
            if (!value || value === '' || value === '0' || (Array.isArray(value) && value.length === 0)) {
                // Re-collect data to ensure fields are updated
                collectAllFilterData();
            }
            
            // Small delay to ensure value is set
            setTimeout(function() {
                triggerFilterAjax();
            }, 50);
        });

        // Handle Google address changes
        $(document).on('cwp-address-change', '.' + CONTAINER_CLASS + ' .address', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                triggerFilterAjax();
            }, doneTypingInterval);
        });

        // Handle range slider changes
        $(document).on('change', '.' + CONTAINER_CLASS + ' input[type="range"]', function() {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                triggerFilterAjax();
            }, doneTypingInterval);
        });

        // Handle switch/toggle changes
        $(document).on('change', '.' + CONTAINER_CLASS + ' input[type="checkbox"].cwp-switch', function() {
            triggerFilterAjax();
        });

        // Handle sorting button clicks with toggle functionality
        $(document).on('click', '.cubewp-sorting-btn', function() {
            var $btn = $(this);
            var $container = $btn.closest('.cubewp-sorting-container');
            var orderby = $btn.data('orderby');
            var displayField = $btn.data('display-field');
            
            // Check if button is already active - toggle functionality
            var isAlreadyActive = $btn.hasClass('active');
            
            // Remove active class from all buttons with the same display field (across all containers)
            // This ensures only one button per field name is active
            if (displayField) {
                $('.cubewp-sorting-btn[data-display-field="' + displayField + '"]').removeClass('active');
            } else {
                // If no display field, just remove from same container
                $btn.closest('.cubewp-sorting-buttons').find('.cubewp-sorting-btn').removeClass('active');
            }
            
            if (isAlreadyActive) {
                // Deselect: button is already active, so deselect it
                $btn.removeClass('active');
                
                // Clear values
                var $form = getOrCreateForm();
                
                // Clear rating filter if it was a rating field
                if (displayField && displayField.indexOf('rating_') === 0) {
                    var $ratingInput = $form.find('input[name="' + displayField + '"]');
                    if ($ratingInput.length > 0) {
                        $ratingInput.val('');
                    }
                }
                
                // Clear most_viewed
                if (displayField === 'most_viewed') {
                    var $mvInput = $form.find('input[name="most_viewed"]');
                    if ($mvInput.length > 0) {
                        $mvInput.val('');
                    }
                }
                
                // Clear high_rated
                if (displayField === 'high_rated') {
                    var $hrInput = $form.find('input[name="high_rated"]');
                    if ($hrInput.length > 0) {
                        $hrInput.val('');
                    }
                }
                
                // Clear orderby
                updateSortingParameter('');
                triggerFilterAjax();
                return;
            }
            
            // Select: add active class
            $btn.addClass('active');
            
            // Handle best match
            if (orderby === 'best_match') {
                orderby = 'relevance';
            }
            
            var $form = getOrCreateForm();
            
            // For rating filters, send as separate parameter (don't send orderby) - FILTERS posts
            if (displayField && displayField.indexOf('rating_') === 0) {
                var ratingValue = displayField.replace('rating_', '');
                var $ratingInput = $form.find('input[name="' + displayField + '"]');
                
                if ($ratingInput.length === 0) {
                    var $fieldsContainer = $form.find('.cwp-search-filters-fields');
                    if ($fieldsContainer.length === 0) {
                        $fieldsContainer = $form;
                    }
                    $fieldsContainer.append($('<input>', {
                        type: 'hidden',
                        name: displayField,
                        value: ratingValue,
                        'class': PREFIX + 'field'
                    }));
                } else {
                    $ratingInput.val(ratingValue);
                }
                
                // Clear orderby for rating fields - don't send orderby (these are filters, not sorting)
                updateSortingParameter('');
                triggerFilterAjax();
                return;
            }
            
            // For most_viewed and high_rated - use orderby for SORTING (not filtering)
            if (displayField === 'most_viewed') {
                // Set orderby to post_views for sorting
                orderby = 'post_views';
                // Remove any filter inputs
                var $mvInput = $form.find('input[name="most_viewed"]');
                if ($mvInput.length > 0) {
                    $mvInput.remove();
                }
            }
            
            if (displayField === 'high_rated') {
                // Set orderby to average_rating for sorting
                orderby = 'average_rating';
                // Remove any filter inputs
                var $hrInput = $form.find('input[name="high_rated"]');
                if ($hrInput.length > 0) {
                    $hrInput.remove();
                }
            }
            
            // Update URL and trigger AJAX
            updateSortingParameter(orderby);
            triggerFilterAjax();
        });

        // Handle sorting dropdown toggle
        $(document).on('click', '.cubewp-sorting-dropdown-toggle', function(e) {
            e.stopPropagation();
            var $dropdown = $(this).closest('.cubewp-sorting-dropdown');
            var $menu = $dropdown.find('.cubewp-sorting-dropdown-menu');
            
            // Close other dropdowns
            $('.cubewp-sorting-dropdown-menu').not($menu).removeClass('open');
            
            // Toggle current dropdown
            $menu.toggleClass('open');
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.cubewp-sorting-dropdown').length) {
                $('.cubewp-sorting-dropdown-menu').removeClass('open');
            }
        });
        
        // Initialize dropdowns - hide by default
        $(document).ready(function() {
            $('.cubewp-sorting-dropdown-menu').removeClass('open');
            $('.cubewp-filter-dropdown-menu').removeClass('open');
        });
        
        // Handle filter dropdown toggle (for filters, not sorting)
        $(document).on('click', '.cubewp-filter-dropdown-toggle', function(e) {
            e.stopPropagation();
            var $dropdown = $(this).closest('.cubewp-filter-dropdown');
            var $menu = $dropdown.find('.cubewp-filter-dropdown-menu');
            
            // Close other filter dropdowns
            $('.cubewp-filter-dropdown-menu').not($menu).removeClass('open');
            
            // Toggle current dropdown
            $menu.toggleClass('open');
        });
        
        // Close filter dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.cubewp-filter-dropdown').length) {
                $('.cubewp-filter-dropdown-menu').removeClass('open');
            }
        });
        
        // Handle filter dropdown field changes - only one field active at a time
        $(document).on('change', '.cubewp-filter-dropdown-menu input, .cubewp-filter-dropdown-menu select, .cubewp-filter-dropdown-menu textarea', function() {
            var $changedField = $(this);
            var $dropdown = $changedField.closest('.cubewp-filter-dropdown');
            var $menu = $dropdown.find('.cubewp-filter-dropdown-menu');
            
            // Get the field name
            var fieldName = $changedField.attr('name');
            if (!fieldName) {
                return;
            }
            
            // Clear all other fields in this dropdown menu
            $menu.find('input, select, textarea').not($changedField).each(function() {
                var $otherField = $(this);
                var otherFieldName = $otherField.attr('name');
                
                // Skip if it's the same field or a hidden field for checkboxes
                if (otherFieldName === fieldName || $otherField.attr('type') === 'hidden') {
                    return;
                }
                
                // Clear the field
                if ($otherField.is('select')) {
                    $otherField.val('').trigger('change');
                } else if ($otherField.is('input[type="checkbox"]')) {
                    $otherField.prop('checked', false).trigger('change');
                } else if ($otherField.is('input[type="radio"]')) {
                    $otherField.prop('checked', false).trigger('change');
                } else {
                    $otherField.val('').trigger('change');
                }
            });
            
            // Trigger filter update
            triggerFilterAjax();
        });
        
        // Handle business hours button clicks in filter dropdown
        $(document).on('click', '.cubewp-filter-dropdown-menu .cubewp-business-hours-btn', function() {
            var $button = $(this);
            var $dropdown = $button.closest('.cubewp-filter-dropdown');
            var $menu = $dropdown.find('.cubewp-filter-dropdown-menu');
            
            // Clear all other fields in this dropdown menu
            $menu.find('input:not(.cubewp-business-hours-status), select, textarea').each(function() {
                var $otherField = $(this);
                if ($otherField.is('select')) {
                    $otherField.val('').trigger('change');
                } else if ($otherField.is('input[type="checkbox"]')) {
                    $otherField.prop('checked', false).trigger('change');
                } else if ($otherField.is('input[type="radio"]')) {
                    $otherField.prop('checked', false).trigger('change');
                } else {
                    $otherField.val('').trigger('change');
                }
            });
        });

        // Handle sorting dropdown item selection with toggle functionality
        $(document).on('click', '.cubewp-sorting-dropdown-item', function() {
            var $item = $(this);
            var $container = $item.closest('.cubewp-sorting-container');
            var $dropdown = $item.closest('.cubewp-sorting-dropdown');
            var orderby = $item.data('orderby');
            var displayField = $item.data('display-field');
            var label = $item.find('.cubewp-sorting-dropdown-item-text').text() || $item.find('.cubewp-rating-stars').parent().text().trim();
            
            // Check if item is already selected - toggle functionality
            var isAlreadySelected = $item.hasClass('selected');
            
            // Remove selected class from all items with the same display field (across all containers)
            // This ensures only one item per field name is selected
            if (displayField) {
                $('.cubewp-sorting-dropdown-item[data-display-field="' + displayField + '"]').removeClass('selected');
            } else {
                // If no display field, just remove from same dropdown
                $dropdown.find('.cubewp-sorting-dropdown-item').removeClass('selected');
            }
            
            if (isAlreadySelected) {
                // Deselect: item was already selected, so deselect it (already removed class above)
                // Reset dropdown text to default
                $dropdown.find('.cubewp-sorting-dropdown-text').text('Sort By');
                
                // Close dropdown
                $dropdown.find('.cubewp-sorting-dropdown-menu').removeClass('open');
                
                var $form = getOrCreateForm();
                
                // Clear rating filter if it was a rating field
                if (displayField && displayField.indexOf('rating_') === 0) {
                    var $ratingInput = $form.find('input[name="' + displayField + '"]');
                    if ($ratingInput.length > 0) {
                        $ratingInput.val('');
                    }
                }
                
                // Clear most_viewed
                if (displayField === 'most_viewed') {
                    var $mvInput = $form.find('input[name="most_viewed"]');
                    if ($mvInput.length > 0) {
                        $mvInput.val('');
                    }
                }
                
                // Clear high_rated
                if (displayField === 'high_rated') {
                    var $hrInput = $form.find('input[name="high_rated"]');
                    if ($hrInput.length > 0) {
                        $hrInput.val('');
                    }
                }
                
                // Clear orderby
                updateSortingParameter('');
                triggerFilterAjax();
                return;
            }
            
            // Select: add selected class
            $item.addClass('selected');
            
            // Update dropdown text
            if (label) {
                $dropdown.find('.cubewp-sorting-dropdown-text').text(label);
            }
            
            // Close dropdown
            $dropdown.find('.cubewp-sorting-dropdown-menu').removeClass('open');
            
            // Handle best match
            if (orderby === 'best_match') {
                orderby = 'relevance';
            }
            
            var $form = getOrCreateForm();
            
            // For rating filters, send as separate parameter (don't send orderby)
            if (displayField && displayField.indexOf('rating_') === 0) {
                var ratingValue = displayField.replace('rating_', '');
                var $ratingInput = $form.find('input[name="' + displayField + '"]');
                
                if ($ratingInput.length === 0) {
                    var $fieldsContainer = $form.find('.cwp-search-filters-fields');
                    if ($fieldsContainer.length === 0) {
                        $fieldsContainer = $form;
                    }
                    $fieldsContainer.append($('<input>', {
                        type: 'hidden',
                        name: displayField,
                        value: ratingValue,
                        'class': PREFIX + 'field'
                    }));
                } else {
                    $ratingInput.val(ratingValue);
                }
                
                // Clear orderby for rating fields - don't send orderby
                updateSortingParameter('');
                triggerFilterAjax();
                return;
            }
            
            // For most_viewed and high_rated - use orderby for SORTING (not filtering)
            if (displayField === 'most_viewed') {
                // Set orderby to post_views for sorting
                orderby = 'post_views';
                // Remove any filter inputs
                var $mvInput = $form.find('input[name="most_viewed"]');
                if ($mvInput.length > 0) {
                    $mvInput.remove();
                }
            }
            
            if (displayField === 'high_rated') {
                // Set orderby to average_rating for sorting
                orderby = 'average_rating';
                // Remove any filter inputs
                var $hrInput = $form.find('input[name="high_rated"]');
                if ($hrInput.length > 0) {
                    $hrInput.remove();
                }
            }
            
            // Update URL and trigger AJAX
            updateSortingParameter(orderby);
            triggerFilterAjax();
        });
    }

    /**
     * Update sorting parameter in URL and form
     */
    function updateSortingParameter(orderby) {
        // Update URL parameter
        var url = new URL(window.location.href);
        if (orderby && orderby !== '') {
            url.searchParams.set('orderby', orderby);
        } else {
            url.searchParams.delete('orderby');
        }
        
        // Update browser URL without reload
        window.history.pushState({}, '', url);
        
        // Update form if it exists
        var $form = getOrCreateForm();
        var $orderbyInput = $form.find('input[name="orderby"]');
        if ($orderbyInput.length > 0) {
            if (orderby && orderby !== '') {
                $orderbyInput.val(orderby);
            } else {
                $orderbyInput.remove();
            }
        } else if (orderby && orderby !== '') {
            // Add orderby input to form
            var $fieldsContainer = $form.find('.cubewp-filter-builder-fields-container');
            if ($fieldsContainer.length === 0) {
                $fieldsContainer = $form;
            }
         
        }
    }

    /**
     * Override the existing cwp_search_filters_ajax_content to also check filter builder form
     * Since we now use existing forms when available, we just need to ensure data is collected
     * Added flag to prevent multiple calls
     */
    var ajaxInProgress = false;
    var originalAjaxFunction = window.cwp_search_filters_ajax_content;
    
    if (typeof originalAjaxFunction === 'function') {
        // Wrap the original function
        window.cwp_search_filters_ajax_content = function(page_num) {
            // Prevent multiple simultaneous AJAX calls
            if (ajaxInProgress) {
                return;
            }
            
            // Collect filter builder data first (this will use existing form if available)
            collectAllFilterData();
            
            // Set flag to prevent duplicate calls
            ajaxInProgress = true;
            
            // The form is already set up correctly (either existing or our own)
            // Just call the original function - it will use the form with class .cwp-search-filters
            var result = originalAjaxFunction.call(this, page_num);
            
            // Reset flag after AJAX completes (use longer delay to ensure AJAX finishes)
            setTimeout(function() {
                ajaxInProgress = false;
            }, 2000);
            
            return result;
        };
    } else {
        // If function doesn't exist, create a basic one
        window.cwp_search_filters_ajax_content = function(page_num) {
            // Prevent multiple simultaneous AJAX calls
            if (ajaxInProgress) {
                return;
            }
            
            collectAllFilterData();
            
            var $form = $('.' + FORM_CLASS);
            if ($form.length === 0 || $form.find('.' + PREFIX + 'field').length === 0) {
                return;
            }

            page_num = page_num || 1;
            $form.find('input[name="page_num"]').val(page_num);
            
            // Set flag to prevent duplicate calls
            ajaxInProgress = true;

            var action = '&action=cwp_search_filters_ajax_content';
            var filterFields = $form.serialize();

            if ($('#cwp-order-filter').length > 0) {
                filterFields += '&order=' + $('#cwp-order-filter').val();
            }
            if ($('#cwp-sorting-filter').length > 0) {
                filterFields += '&orderby=' + $('#cwp-sorting-filter').val();
            }

            var data_vals = filterFields;

            // Use existing helper functions if available
            if (typeof urlCombine === 'function') {
                data_vals = urlCombine(data_vals, window.location.search);
            }
            if (typeof stripUrlParams === 'function') {
                data_vals = stripUrlParams(data_vals);
            }
            if (typeof stripPrefixFromParams === 'function') {
                data_vals = stripPrefixFromParams(data_vals, '_ST_');
            }

            // Clean up empty values
            data_vals = data_vals.replace(/(?!s=)[^&]+=\.?(?:&|$)/g, function (match) {
                return match.endsWith('&') ? '' : '&';
            }).replace(/&$/, '');
            data_vals = data_vals.replace('undefined', '');

            // Show loading skeleton
            if ($('.cwp-archive-container .cwp-grids-container').length > 0) {
                $('.cwp-archive-container .cwp-grids-container div').html(
                    '<div class="cwp-processing-post-grid">' +
                    '<div class="cwp-processing-post-thumbnail"></div>' +
                    '<div class="cwp-processing-post-content"><p></p><p></p><p></p></div>' +
                    '</div>'
                );
            } else {
                var processingGrid = '';
                for (var i = 0; i < 6; i++) {
                    processingGrid +=
                        '<div class="cwp-col-md-4">' +
                        '<div class="cwp-processing-post-grid">' +
                        '<div class="cwp-processing-post-thumbnail"></div>' +
                        '<div class="cwp-processing-post-content"><p></p><p></p><p></p></div>' +
                        '</div></div>';
                }
                $('.cwp-archive-container .cwp-search-result-output').html(
                    '<div class="cwp-grids-container cwp-row">' + processingGrid + '</div>'
                );
            }

            $.ajax({
                url: (typeof cwp_search_filters_params !== 'undefined' && cwp_search_filters_params.ajax_url) 
                    ? cwp_search_filters_params.ajax_url 
                    : cubewp_params.ajax_url,
                type: 'POST',
                data: data_vals + action,
                dataType: "json",
                success: function (response) {
                    if ($(".cwp-archive-container").length > 0) {
                        $('html, body').animate({
                            scrollTop: $(".cwp-archive-container").offset().top - 100
                        }, 200);
                    }
                    $('.cwp-search-result-output').html(response.grid_view_html);
                    $('.cwp-total-results').html(response.post_data_details);

                    // Listing update on Map
                    if (typeof CWP_Cluster_Map === 'function') {
                        CWP_Cluster_Map(response.map_cordinates);
                    }

                    $('.cwp-archive-container').removeClass('cwp-active-ajax');
                    $(document.body).trigger('cubewp_search_results_loaded');
                }
            });
        };
    }

    /**
     * Public function to manually collect filter data
     */
    window.cubewpCollectFilterBuilderData = function() {
        return collectAllFilterData();
    };

    /**
     * Public function to get collected filter data
     */
    window.cubewpGetFilterBuilderData = function() {
        var $form = $('.' + FORM_CLASS);
        if ($form.length === 0) {
            return {};
        }
        return $form.serialize();
    };

    /**
     * Initialize sorting buttons active state from URL parameters
     */
    function initSortingButtons() {
        var urlParams = new URLSearchParams(window.location.search);
        var orderby = urlParams.get('orderby') || '';
        
        // Check for rating fields, most_viewed, high_rated
        var ratingFields = ['rating_1', 'rating_2', 'rating_3', 'rating_4', 'rating_5'];
        var foundRating = false;
        
        for (var i = 0; i < ratingFields.length; i++) {
            var ratingField = ratingFields[i];
            if (urlParams.has(ratingField)) {
                // Find button with matching display field
                $('.cubewp-sorting-btn[data-display-field="' + ratingField + '"]').addClass('active');
                foundRating = true;
                break;
            }
        }
        
        if (!foundRating) {
            if (urlParams.has('most_viewed')) {
                $('.cubewp-sorting-btn[data-display-field="most_viewed"]').addClass('active');
            } else if (urlParams.has('high_rated')) {
                $('.cubewp-sorting-btn[data-display-field="high_rated"]').addClass('active');
            } else if (orderby) {
                // Find button with matching orderby or display field
                $('.cubewp-sorting-btn').each(function() {
                    var $btn = $(this);
                    var btnOrderby = $btn.data('orderby');
                    var btnDisplayField = $btn.data('display-field');
                    
                    if (btnOrderby === orderby || btnDisplayField === orderby || 
                        (orderby === 'relevance' && btnOrderby === 'best_match')) {
                        $btn.addClass('active');
                        return false; // Break loop
                    }
                });
            }
        }
    }

    /**
     * Initialize sorting buttons active state from URL parameters
     */
    function initSortingButtons() {
        var urlParams = new URLSearchParams(window.location.search);
        var orderby = urlParams.get('orderby') || '';
        
        // First, remove all active classes
        $('.cubewp-sorting-btn').removeClass('active');
        
        // Check for rating fields first (they don't use orderby)
        var ratingFields = ['rating_1', 'rating_2', 'rating_3', 'rating_4', 'rating_5'];
        var foundRating = false;
        
        for (var i = 0; i < ratingFields.length; i++) {
            var ratingField = ratingFields[i];
            if (urlParams.has(ratingField)) {
                // Find button with matching display field
                $('.cubewp-sorting-btn[data-display-field="' + ratingField + '"]').addClass('active');
                foundRating = true;
                break;
            }
        }
        
        if (!foundRating) {
            // Check for most_viewed
            if (urlParams.has('most_viewed')) {
                $('.cubewp-sorting-btn[data-display-field="most_viewed"]').addClass('active');
            } 
            // Check for high_rated
            else if (urlParams.has('high_rated')) {
                $('.cubewp-sorting-btn[data-display-field="high_rated"]').addClass('active');
            } 
            // Check for orderby
            else if (orderby) {
                // Find button with matching orderby or display field
                $('.cubewp-sorting-btn').each(function() {
                    var $btn = $(this);
                    var btnOrderby = $btn.data('orderby');
                    var btnDisplayField = $btn.data('display-field');
                    
                    // Match orderby
                    if (btnOrderby === orderby || btnDisplayField === orderby) {
                        $btn.addClass('active');
                        return false; // Break loop
                    }
                    // Match best match / relevance
                    if ((orderby === 'relevance' || orderby === 'best_match') && 
                        (btnOrderby === 'best_match' || btnOrderby === 'relevance')) {
                        $btn.addClass('active');
                        return false; // Break loop
                    }
                });
            }
        }
    }

    // Initialize on document ready
    $(document).ready(function() {
        initFilterBuilder();
        initSortingButtons();
    });

    // Re-initialize when new widgets are added (for dynamic content)
    $(document).on('cubewp_filter_builder_widget_added', function() {
        collectAllFilterData();
    });

    /**
     * Handle radio button selection with selected class
     * For .cwp-field-radio-container radio buttons
     */
    $(document).on('click', '.cwp-field-radio-container input[type="radio"]', function () {
        var $radio = $(this);
        var $container = $radio.closest('.cwp-field-radio-container');
        var $radioItem = $radio.closest('.cwp-field-radio');
        var name = $radio.attr('name');
        var value = $radio.val();
    
        // 🔹 Uncheck ALL radios in this container
        $container.find('input[type="radio"]').prop('checked', false);
    
        // 🔹 Check ONLY the clicked radio
        $radio.prop('checked', true);
    
        // 🔹 Remove selected class from all items
        $container.find('.cwp-field-radio').removeClass('selected');
    
        // 🔹 Add selected class to clicked item
        $radioItem.addClass('selected');
    
        // 🔹 Update hidden input value
        var $hiddenInput = $container.find('input[type="hidden"][name="' + name + '"]');
        if ($hiddenInput.length) {
            $hiddenInput.val(value);
        } else {
            $container.find('input[type="hidden"]').val(value);
        }
    
        // 🔹 Trigger change event
        $radio.trigger('change');
    
        // 🔹 Trigger filter update
        collectAllFilterData();
        triggerFilterAjax();
    });
    

    /**
     * Initialize radio button selected state from URL or existing values
     */
    function initRadioButtons() {
        $('.cwp-field-radio-container').each(function() {
            var $container = $(this);
            var $hiddenInput = $container.find('input[type="hidden"]');
            
            if ($hiddenInput.length > 0) {
                var name = $hiddenInput.attr('name');
                var value = $hiddenInput.val();
                
                if (value) {
                    // Find radio with matching value and select it
                    var $radio = $container.find('input[type="radio"][name="' + name + '"][value="' + value + '"]');
                    if ($radio.length > 0) {
                        $radio.prop('checked', true);
                        $radio.closest('.cwp-field-radio').addClass('selected');
                    }
                }
            } else {
                // Check URL parameters
                var urlParams = new URLSearchParams(window.location.search);
                $container.find('input[type="radio"]').each(function() {
                    var $radio = $(this);
                    var radioName = $radio.attr('name');
                    var radioValue = $radio.val();
                    
                    if (radioName && urlParams.has(radioName)) {
                        var urlValue = decodeURIComponent(urlParams.get(radioName));
                        if (urlValue === radioValue) {
                            $radio.prop('checked', true);
                            $radio.closest('.cwp-field-radio').addClass('selected');
                        }
                    }
                });
            }
        });
    }

    // Initialize radio buttons on page load
    $(document).ready(function() {
        initRadioButtons();
    });


    function clearFilters() {
        $('.cubewp-filter-builder-field').each(function() {
              $(this).val(''); 
        });
        $('.cubewp-filter-builder-container').find('input[type="radio"]').prop('checked', false);
        $('.cubewp-filter-builder-container').find('input[type="checkbox"]').prop('checked', false);
        $('.cubewp-filter-builder-container').find('input[type="hidden"]').val('');
        $('.cubewp-filter-builder-container').find('input').val('');
        $('.cubewp-filter-builder-container').find('select').val('');
        $('.cubewp-sorting-btn').removeClass('active');
        $('.cubewp-sorting-dropdown-item').removeClass('selected');
         // Update URL parameter
         var url = new URL(window.location.href);
         url.searchParams.delete('orderby');
         window.history.pushState(null, null, url.toString());
        setTimeout(function() {
            cwp_search_filters_ajax_content('');
        }, 300);
       
    }

    $(document).on('click', '.cubewp-filter-builder-reset-button-button', function() {
        clearFilters(); 
    });

})(jQuery);
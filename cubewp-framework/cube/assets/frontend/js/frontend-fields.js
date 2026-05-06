jQuery(document).ready(function () {

    cubewp_init_resources();
    
    var repeating_fields = jQuery('.cwp-repeating-field-container');

    // Repeating Field
    if (repeating_fields.length > 0) {
        repeating_fields.each(function () {
            var $this = jQuery(this);
            $this.repeatable_fields({
                wrapper: '.cwp-repeating-field-wrapper',
                container: '.cwp-repeating-single-field-container',
                template: '.cwp-repeating-single-field-template',
                row: '.cwp-repeating-single-field-row',
                add: '.cwp-add-new-repeating-field',
                remove: '.cwp-repeating-single-field-remove',
                move: '.cwp-repeating-single-field-move',
                move_up: null,
                move_down: null,
                move_steps: null,
                is_sortable: true,
                before_add: function () {
                    jQuery('.cwp-repeating-single-field-content').slideUp(300);
                    jQuery('.cwp-repeating-single-field-actions').removeClass("cubewp-collapse");
                },
                after_add: function (container, new_row) {
                    var row_count = jQuery(container).attr('data-rf-row-count');
                    row_count++;
                    jQuery('*', new_row).each(function () {
                        jQuery.each(this.attributes, function () {
                            this.value = this.value.replace("{{row-count-placeholder}}", row_count - 1);
                        });
                    });
                    jQuery(container).attr('data-rf-row-count', row_count);
                    cubewp_init_select2(new_row.find('.cwp-select2 select'));
                    cubewp_init_date_pickers(new_row.find('.cwp-field-date_picker'));
                    cubewp_init_time_pickers(new_row.find('.cwp-field-time_picker'));
                    cubewp_init_range_pickers(new_row.find('.cwp-field-range'));
                    new_row.find(".cwp-repeating-single-field-content").show();
                    new_row.find(".cwp-repeating-single-field-actions").addClass("cubewp-collapse");
                    jQuery(document).trigger("cubewp-repeating-field-added", [container, new_row]);
                },
                before_remove: null,
                after_remove: null,
                sortable_options: null,
                row_count_placeholder: null
            });
            var loaded_fields = $this.find('.cwp-repeating-single-field-row:not(.cwp-repeating-single-field-template)');
            if (loaded_fields.length > 0) {
                var row_count = 0;
                loaded_fields.each(function () {
                    jQuery(this).find('*').each(function () {
                        jQuery.each(this.attributes, function () {
                            this.value = this.value.replace("{{row-count-placeholder}}", row_count);
                        });
                    });
                    row_count++;
                });

                $this.find('.cwp-repeating-single-field-container').attr('data-rf-row-count', row_count);
            }
        });
    }

    jQuery(document).on('change', '.cwp-field-switch-container input[type="checkbox"]', function () {
        if (jQuery(this).is(":checked")) {
            jQuery(this).closest('label').find('input[type="hidden"]').val('Yes').trigger('input');
        } else {
            jQuery(this).closest('label').find('input[type="hidden"]').val('').trigger('input');
        }
    });
    jQuery(document).on('input', '.cwp-field-number input[maxlength]', function () {
        var maxDigits = jQuery(this).attr('maxlength');
        var inputValue = jQuery(this).val().trim();
    
        if (inputValue.length > maxDigits) {
            jQuery(this).val(inputValue.slice(0, maxDigits)).trigger('input');
        }
    });

    // File Field
    jQuery(document).on('click', '.cwp-file-field-trigger', function (e) {
        e.preventDefault();
        var $this = jQuery(this), parent = $this.closest('.cwp-file-field');

        parent.find('input[type=file]').trigger('click');
    });
    jQuery(document).on('change', '.cwp-file-field input[type=file]', function () {
        var $this = jQuery(this), parent = $this.closest('.cwp-field-container'),
            preview = parent.find('.cwp-file-field-preview'), file_type = 'file';
        if (parent.hasClass("cubewp-have-image-field")) {
            file_type = 'image';
        }
        cubewp_process_input_type_file($this, file_type, function (fileName, fileSize, preview_) {
            if (file_type === 'image') {
                preview.show().find('img').attr('src', preview_.target.result);
                preview.find('p').text(fileName + ' - ' + fileSize);
            } else if (file_type === 'file') {
                preview.show().find('img').attr('src', preview.attr('data-default-file'));
                preview.find('p').text(fileName + ' - ' + fileSize);
            }
        }, function (fileName, fileType, fileSize) {
            preview.find('img').attr('src', '');
            parent.find('.cwp-file-field input[type="file"]').val('');
            parent.find('.cwp-file-field input[type="hidden"]').val('');
            preview.find('p').text('');
            preview.hide();
            preview.find('input').remove();
            alert(fileType + " " + $this.attr("data-error-msg"));
        });
    });
    jQuery(document).on('click', '.cwp-file-field-preview span', function () {
        var $this = jQuery(this),
            parent = $this.closest('.cwp-file-field-preview'),
            preview_img = parent.find('img');
        preview_img.attr('src', '');
        parent.find('.cwp-image-preview').val('');
        parent.find('p').text('');
        parent.hide();
    });
    

    // Gallery Field
    jQuery(document).on('click', '.cwp-gallery-field-trigger', function (e) {
        e.preventDefault();
        var $this = jQuery(this), parent = $this.closest('.cwp-gallery-field');

        parent.find('.cwp-gallery-field-inputs:not(.old-inputs) input[type=file]').trigger('click');
    });
    jQuery(document).on('change', '.cwp-field-gallery input[type=file]', function (x) {
        var $this = jQuery(this), file_name = $this.attr('name'), file_id = $this.attr('id'),
            accept = $this.attr('accept'), multiple = $this.attr('multiple'),
            parent = $this.closest('.cwp-gallery-field-container'), field_parent = $this.closest('.cwp-gallery-field'),
            input_parent = $this.closest('.cwp-gallery-field-inputs'),
            hidden_file_name = field_parent.find('input[type=hidden]').attr('name'),
            preview = parent.find('.cwp-gallery-field-preview:not(.cloned)'),
            rand_id = Math.round(new Date().getTime() + (Math.random() * 100)),
            new_file_name = file_name.replace("[" + file_id + "]", "[" + rand_id + "]");
        input_parent.addClass('old-inputs').attr('id', 'batch-' + rand_id);
        field_parent.append('<div class="cwp-gallery-field-inputs">' + '<input type="hidden" name="' + hidden_file_name + '" value="' + rand_id + '">' + '<input type="file" id="' + rand_id + '" name="' + new_file_name + '" accept="' + accept + '" multiple="' + multiple + '">' + '</div>');
        cubewp_process_input_type_file($this, "image", function (fileName, fileSize, preview_) {
            var previewNew = preview.clone();
            parent.find('.cwp-gallery-field-preview-container').append(previewNew);
            previewNew.find('p').text(fileName + ' - ' + fileSize);
            previewNew.find('img').attr('src', preview_.target.result);
            previewNew.addClass('cloned batch-' + rand_id).css('display', 'block').attr('data-batch-id', 'batch-' + rand_id);
        }, function (fileName, fileType, fileSize) {
            alert(fileType + " " + parent.closest(".cwp-field-container").attr("data-error-msg"));
        });
    });
    
    //Password Field
    jQuery(document).on('click', '.cwp-field-password .show-password', function () {
        var $this = jQuery(this);
        var password_field = $this.closest('.cwp-field-password').find('input');
        if($this.hasClass('dashicons-visibility'))
        {
          $this.removeClass('dashicons-visibility');
          $this.addClass('dashicons-hidden');
        }else{
          $this.removeClass('dashicons-hidden');
          $this.addClass('dashicons-visibility');
        }
        if(password_field.attr('type')==='password')
        {
          password_field.attr('type','text');
        }else{
          password_field.attr('type','password');
        }
    });

    jQuery(document).on('click', '.cwp-gallery-field-preview span', function () {
        var $this = jQuery(this), batch_id = $this.closest('.cwp-gallery-field-preview').attr('data-batch-id');
        jQuery('.' + batch_id).remove();
        jQuery('#' + batch_id).remove();
    });

    if (jQuery('.cwp-search-field-google_address').length > 0 || jQuery('.cwp-search-filters-fields').length > 0) {
        jQuery(document).on("cwp-address-change", ".cwp-search-field-google_address .address, .cwp-search-filters-fields .address", function (x) {
            var $this = jQuery(this), parent = $this.closest(".cwp-search-field-google_address"),
                lat = parent.find(".latitude").val(), long = parent.find(".longitude").val(),
                range = parent.find(".cwp-address-range"), range_input = range.find('.range');
            if (lat === '' || long === '' || lat === 'undefined' || long === 'undefined') {
                range.addClass("cwp-hide");
                range_input.attr('type', 'hidden').removeAttr("value min max");
            } else {
                range.removeClass("cwp-hide");
                range_input.attr({
                    'type': 'range',
                    'min': range_input.attr('data-min'),
                    'max': range_input.attr('data-max'),
                    'value': range_input.attr('data-value'),
                });
            }
        });
    }

    if (jQuery('.cubewp-address-manually').length > 0) {
        jQuery(document).on('click', '.cubewp-address-manually', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            var $this = jQuery(this), parent = $this.closest(".cwp-field-google_address"),
                lat = parent.find(".latitude"), long = parent.find(".longitude"), address = parent.find(".address");
            if (address.hasClass('gm-err-autocomplete')) {
                address.removeClass("gm-err-autocomplete").removeAttr("style disabled").prop("placeholder", address.attr("data-placeholder"));
                parent.find(".cwp-get-current-location").remove();
            }
            if ($this.hasClass('cubewp-active')) {
                $this.removeClass('cubewp-active');
                lat.attr("type", "hidden");
                long.attr("type", "hidden");
            } else {
                $this.addClass('cubewp-active');
                lat.attr("type", "text");
                long.attr("type", "text");
            }
        });
    }

    if (jQuery(".cwp-address-range").length > 0) {
        jQuery(document).on("input", '.cwp-address-range .range', function () {
            var $this = jQuery(this), parent = $this.closest(".cwp-address-range");
            parent.find("p span").text($this.val());
        });
    }
    if (jQuery('.cwp-repeating-single-field-actions').length > 0) {
        jQuery(document).on('click', '.cwp-repeating-single-field-actions', function (e) {
            if (!jQuery(this).hasClass("cubewp-collapse")) {
                jQuery('.cwp-repeating-single-field-content').slideUp(300);
                jQuery('.cwp-repeating-single-field-actions').removeClass("cubewp-collapse");
                jQuery(this).next(".cwp-repeating-single-field-content").slideDown(300);
                jQuery(this).addClass("cubewp-collapse");
            }
        });
        jQuery('.cwp-repeating-single-field-row:not(.cwp-repeating-single-field-template)').first().find('.cwp-repeating-single-field-actions').trigger("click");
    }

});


function cubewp_init_resources() {
    var cwp_select2 = jQuery(".cwp-select2 select"),
        date_picker = jQuery(".cwp-field-date_picker"),
        time_picker = jQuery(".cwp-field-time_picker"), 
        date_time_picker = jQuery(".cwp-field-date_time_picker"),
        range_picker = jQuery(".cwp-field-range"),
        range_number_slider = jQuery(".cwp-range-number-slider-ui");

    // Initializing Date Picker
    if (date_picker.length > 0) {
        cubewp_init_date_pickers(date_picker);
    }

    // Initializing Time Picker
    if (time_picker.length > 0) {
        cubewp_init_time_pickers(time_picker);
    }

    // Initializing Range Picker
    if (range_picker.length > 0) {
        cubewp_init_range_pickers(range_picker);
    }

    // Initializing Number Range Slider (min/max)
    if (range_number_slider.length > 0) {
        cubewp_init_range_number_sliders(jQuery(document));
    }

    // Initializing Date & Time Picker
    if (date_time_picker.length > 0) {
        cubewp_init_date_time_pickers(date_time_picker);
    }

    var date_picker_div, date_picker_div_interval = setInterval(function () {
        date_picker_div = jQuery('#ui-datepicker-div');
        if (date_picker_div.length > 0) {
            if (date_picker_div.parent('.cwp-ui-datepicker').length) {
                clearInterval(date_picker_div_interval);
            }
            date_picker_div.wrap('<div class="cwp-ui-datepicker"></div>');
            date_picker_div.hide();
        }
    }, 500);

    // Initializing Select2 On Select2 UI Dropdowns
    if (cwp_select2.length > 0 ) {
        cubewp_init_select2(cwp_select2);
    }
}

function cubewp_init_range_number_sliders($scope) {
    if (typeof jQuery.ui === 'undefined' || typeof jQuery.ui.slider === 'undefined') {
        return;
    }

    var $sliders = $scope.find('.cwp-range-number-slider-ui');
    if (!$sliders.length) {
        return;
    }

    $sliders.each(function () {
        var $ui = jQuery(this);
        if ($ui.hasClass('cwp-range-slider-initialized')) {
            return;
        }

        var $wrap = $ui.closest('.cwp-range-number-slider');
        var min = parseFloat($wrap.attr('data-min'));
        var max = parseFloat($wrap.attr('data-max'));
        var step = parseFloat($wrap.attr('data-step'));

        if (isNaN(min)) min = 0;
        if (isNaN(max)) max = 100;
        if (isNaN(step) || step <= 0) step = 1;

        var $minInput = $wrap.find('input.cwp-range-number-min');
        var $maxInput = $wrap.find('input.cwp-range-number-max');

        var currentMin = parseFloat($minInput.val());
        var currentMax = parseFloat($maxInput.val());

        if (isNaN(currentMin)) currentMin = min;
        if (isNaN(currentMax)) currentMax = max;

        // Clamp
        currentMin = Math.max(min, Math.min(currentMin, max));
        currentMax = Math.max(min, Math.min(currentMax, max));
        if (currentMin > currentMax) {
            var tmp = currentMin;
            currentMin = currentMax;
            currentMax = tmp;
        }

        // Prevent recursion between slider <-> input syncing
        var isUpdating = false;
        var lastMin = currentMin;
        var lastMax = currentMax;

        /**
         * Apply values to inputs and trigger events like real typing.
         * We intentionally trigger `keyup` because Search Filters uses keyup (debounced) for number inputs.
         *
         * @param {number} v1 min value
         * @param {number} v2 max value
         * @param {boolean} commit true on slider "change", false on "slide"
         */
        var setInputs = function (v1, v2, commit) {
            isUpdating = true;

            // Set values first
            $minInput.val(v1);
            $maxInput.val(v2);

            // Trigger per-side like your working noUiSlider approach
            if (v1 !== lastMin) {
                $minInput.trigger('input');
                $minInput.trigger('keyup');
                if (commit) {
                    $minInput.trigger('change');
                }
            }

            if (v2 !== lastMax) {
                $maxInput.trigger('input');
                $maxInput.trigger('keyup');
                if (commit) {
                    $maxInput.trigger('change');
                }
            }

            // On commit, also trigger change once more in a stable order (max then min)
            // to satisfy any code that serializes/builds URL on change.
            if (commit) {
                $maxInput.trigger('change');
                $minInput.trigger('change');
            }

            lastMin = v1;
            lastMax = v2;
            isUpdating = false;
        };

        $ui.slider({
            range: true,
            min: min,
            max: max,
            step: step,
            values: [currentMin, currentMax],
            slide: function (event, ui) {
                // While dragging, keep inputs in sync and trigger events so filters can react immediately.
                setInputs(ui.values[0], ui.values[1], false);
            },
            change: function (event, ui) {
                // On commit, ensure `change` is fired (in case some browsers skip during drag).
                setInputs(ui.values[0], ui.values[1], true);
            }
        });

        $ui.addClass('cwp-range-slider-initialized');

        var syncFromInputs = function () {
            if (isUpdating) {
                return;
            }
            var v1 = parseFloat($minInput.val());
            var v2 = parseFloat($maxInput.val());
            if (isNaN(v1)) v1 = min;
            if (isNaN(v2)) v2 = max;
            v1 = Math.max(min, Math.min(v1, max));
            v2 = Math.max(min, Math.min(v2, max));
            if (v1 > v2) {
                var t = v1;
                v1 = v2;
                v2 = t;
            }
            $ui.slider('values', [v1, v2]);
            updateTexts(v1, v2);
        };

        $minInput.on('change input', syncFromInputs);
        $maxInput.on('change input', syncFromInputs);
    });
}

function cubewp_init_select2(selects) {
    selects.each(function () {
        var $this = jQuery(this), placeholder = $this.attr('placeholder'),
            dropdown_type = $this.attr('data-dropdown-type'), dropdown_values = $this.attr('data-dropdown-values');

        if (!$this.parents('.cwp-repeating-single-field-template').length) {
            if (!$this.hasClass('cubewp-remote-options')) {
                jQuery(this).select2({
                    width: '100%', placeholder: placeholder, minimumResultsForSearch: 10
                });
            } else {
                jQuery(this).select2({
                    width: '100%', placeholder: placeholder, minimumInputLength: 3, ajax: {
                        url: cwp_frontend_fields_params.ajax_url,
                        dataType: "json",
                        type: "POST",
                        data: function (params) {
                            return {
                                action: 'cubewp_dynamic_options',
                                dropdown_type: dropdown_type,
                                dropdown_values: dropdown_values,
                                keyword: params.term,
                                security_nonce: cwp_frontend_fields_params.security_nonce
                            };
                        },
                        processResults: function (response) {
                            if (response.success) {
                                return {
                                    results: jQuery.map(response.data, function (item) {
                                        return {
                                            text: item.label, id: item.value
                                        }
                                    })
                                };
                            }
                        }
                    }
                });
            }
        }
    })
}

function cubewp_init_date_pickers(date_picker) {
    date_picker.each(function () {
        var thisObj = jQuery(this);
        if (!thisObj.parents('.cwp-repeating-single-field-template').length) {
            var args = {
                dateFormat: 'd/m/yy',
                altField: thisObj.find('input[type="hidden"]'),
                altFormat: 'yy-mm-dd',
                changeYear: true,
                yearRange: "-100:+100",
                changeMonth: true,
                showButtonPanel: true,
                firstDay: '0',
                beforeShow: function (input, datepicker) {
                    thisObj.removeClass('cwp-hide');
                    setTimeout(function () {
                        datepicker.dpDiv.find('.ui-datepicker-current')
                            .click(function () {
                                jQuery(input).datepicker('setDate', new Date());
                            });
                    }, 1);
                    return {};
                }
            };
            thisObj.find('input[type="text"]').datepicker(args);
            thisObj.find('input[type="text"]').removeAttr("name");
        }
    });
}

function cubewp_init_time_pickers(time_picker) {
    time_picker.each(function () {
        var thisObj = jQuery(this);
        if (!thisObj.parents('.cwp-repeating-single-field-template').length) {
            var args = {
                timeFormat: 'hh:mm:ss TT',
                altField: thisObj.find('input[type="hidden"]'),
                altFieldTimeOnly: false,
                altTimeFormat: 'HH:mm:ss',
                showButtonPanel: true,
                controlType: 'select',
                oneLine: true,
                timeOnly: true,
                beforeShow: function (input, datepicker) {
                    setTimeout(function () {
                        datepicker.dpDiv.find('.ui-datepicker-current')
                            .click(function () {
                                jQuery(input).datepicker('setDate', new Date());
                            });
                    }, 1);
                    return {};
                }
            };
            thisObj.find('input[type="text"]').timepicker(args);
            thisObj.find('input[type="text"]').removeAttr("name");
        }
    });
}

function cubewp_init_date_time_pickers(date_time_picker) {
    date_time_picker.each(function () {
        var thisObj = jQuery(this);
        if (!thisObj.parents('.cwp-repeating-single-field-template').length) {
            var args = {
                dateFormat: 'd/m/yy',
                timeFormat: 'HH:mm:ss',
                altField: thisObj.find('input[type="hidden"]'),
                altFieldTimeOnly: false,
                altFormat: 'yy-mm-dd',
                altTimeFormat: 'HH:mm:ss',
                changeYear: true,
                yearRange: "-100:+100",
                changeMonth: true,
                showButtonPanel: true,
                firstDay: '0',
                controlType: 'select',
                oneLine: true,
                beforeShow: function (input, datepicker) {
                    setTimeout(function () {
                        datepicker.dpDiv.find('.ui-datepicker-current')
                            .click(function () {
                                jQuery(input).datepicker('setDate', new Date());
                            });
                    }, 1);
                    return {};
                }
            };
            thisObj.find('input[type="text"]').datetimepicker(args);
            thisObj.find('input[type="text"]').removeAttr("name");
        }
    });
}

function cubewp_process_input_type_file($this, handle_as, $callbackFunction = () => {
}, $typeErrorFunction = () => {
}) {
    var files = $this.prop('files'), sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'], type_compare = Array(),
        allowed_image_types = Array('image/png', 'image/jpg', 'image/gif', 'image/jpeg', 'image/webp'),
        allowed_file_types = Array('application/gzip', 'text/calendar', 'application/pdf', 'text/plain', 'application/zip', 'application/x-7z-compressed', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed'),
        max_allowed_images = $this.closest('.cwp-gallery-field').attr('data-max-files'),
        files_count = files.length;
    if (handle_as === 'image') {
        type_compare = allowed_image_types;
    } else if (handle_as === 'file') {
        type_compare = allowed_file_types;
    }
    files_count += $this.closest('.cwp-gallery-field-container').find('.cwp-gallery-field-preview.cloned').length;
    if ( files_count > max_allowed_images ) {
        alert(cwp_frontend_fields_params.max_upload_files);
        return false;
    }
    for (var files_counter = 0; files_counter < files.length; files_counter++) {
        (function (file_index) {
            var file = files[file_index], fileType = file["type"], fileName = file["name"], fileSize = file["size"],
                i = parseInt(Math.floor(Math.log(fileSize) / Math.log(1024))), previewTempUrl = null,
                file_size = (fileSize / 1024 / 1024).toFixed(2),
                allowed_file_size = $this.closest('.cwp-gallery-field').attr('data-max-upload');
            fileSize = Math.round(fileSize / Math.pow(1024, i), 2) + ' ' + sizes[i];
            if ( file_size > allowed_file_size ) {
                alert(cwp_frontend_fields_params.max_upload_size);
                return false;
            }
            if (jQuery.inArray(fileType, type_compare) !== -1) {
                previewTempUrl = new FileReader();
                previewTempUrl.onload = function (preview_) {
                    $callbackFunction(fileName, fileSize, preview_);
                };
                previewTempUrl.readAsDataURL(file);
            } else {
                $typeErrorFunction(fileName, fileType, fileSize);
            }
        })(files_counter);
    }
}


function cubewp_init_range_pickers(range_picker) {
    range_picker.each(function () {
        var thisObj = jQuery(this),
            current_value_input = thisObj.find("input[type=range]");
        if (thisObj.parents('.cwp-repeating-single-field-template').length == 0) {
            if (!thisObj.find('.cubewp-current-value').length) {
                current_value_input.before("<span class='cubewp-current-value'>" + current_value_input.val() + "</span>");
                current_value_input.on("input", function () {
                    jQuery(this).closest(".cwp-field-range").find(".cubewp-current-value").text(jQuery(this).val());
                });
            }
        }
    });
}

function cwp_condition_logic(selectedVal, fieldVal, Compare, Target) {
    Target = '.' + Target;
    var selectedValues = Array.isArray(selectedVal) ? selectedVal : [selectedVal];
    if (Compare === '!empty') {
        if (selectedValues.length > 0 && selectedValues.join('') !== '' && typeof selectedVal != 'undefined') {
            jQuery(Target).show();
            return true;
        } else {
            jQuery(Target).hide();
            return true;
        }
    } else if (Compare === 'empty') {
        if (selectedValues.length === 0 || selectedValues.join('') === '' || typeof selectedVal == 'undefined') {
            jQuery(Target).show();
            return true;
        } else {
            jQuery(Target).hide();
            return true;
        }
    } else if (Compare === '==') {
        if (selectedValues.indexOf(fieldVal) !== -1) {
            jQuery(Target+fieldVal).show();
            return true;
        } else {
            jQuery(Target+fieldVal).hide();
            return true;
        }
    } else if (Compare === '!=') {
        if (selectedValues.indexOf(fieldVal) === -1 && selectedValues.join('') !== '' && typeof selectedVal != 'undefined') {
            jQuery(Target+fieldVal).show();
            return true;
        } else {
            jQuery(Target+fieldVal).hide();
            return true;
        }
    }
    return false;
}


function cwp_conditional_fields(form_name_method) {
    if (cwp_conditional_fields._hasRun === true) {
        return false;
    }
    if(form_name_method == ''){
        return false;
    }
    cwp_conditional_fields._hasRun = true;
    var cwp_conditional_logic = jQuery('.conditional-logic');
    if (cwp_conditional_logic.length > 0) {
        var get_conditional_field_value = function ($form, field, visibleOnly) {
            var visibilitySelector = visibleOnly ? ':visible' : '';
            var selector = '*' + form_name_method + '[' + field + ']"]' + visibilitySelector;
            var multiSelector = '*' + form_name_method + '[' + field + '][]"]' + visibilitySelector;
            var $fields = $form.find(selector);
            var $multiFields = $form.find(multiSelector);
            var $checkedRadio = $form.find(selector + ':checked');
            var $checkedMulti = $form.find(multiSelector + ':checked');
            var selectedVal = '';

            if ($checkedRadio.length) {
                return $checkedRadio.val();
            }
            if ($checkedMulti.length) {
                return $checkedMulti.map(function () {
                    return jQuery(this).val();
                }).get();
            }

            if (!$fields.length && !$multiFields.length) {
                return selectedVal;
            }

            var $firstField = $fields.length ? $fields.first() : $multiFields.first();
            var tagName = ($firstField.prop('tagName') || '').toUpperCase();
            var inputType = ($firstField.prop('type') || '').toLowerCase();

            if (tagName === 'INPUT' && (inputType === 'checkbox' || inputType === 'radio')) {
                return $firstField.is(':checked') ? $firstField.val() : '';
            }

            selectedVal = $firstField.val();
            return typeof selectedVal === 'undefined' || selectedVal === null ? '' : selectedVal;
        };

        cwp_conditional_logic.each(function () { 
            var $this = jQuery(this),
            field = $this.attr('data-field'),
            value = $this.attr('data-value'),
            operator = $this.attr('data-operator');
         
            var parent = $this.closest('form').find('*' +form_name_method+ '[' + field + ']"]');
            var parentRadio = $this.closest('form').find('*' +form_name_method+ '[' + field + ']"]:checked');
            var parentCheckbox = $this.closest('form').find(form_name_method+ '[' + field + '][]"]');
            var selectedVal = get_conditional_field_value($this.closest('form'), field, false);
            var tagName = parent.prop('tagName');
            if ( tagName === 'INPUT' ) {
                var inputType = parentRadio.prop('type');
                if ( inputType === 'radio' ) {
                        selectedVal = $this.closest('form').find('*' +form_name_method+ '[' + field + ']"]').val();
                }
            }
            if (parent.is(':checked') || (inputType !== 'radio' && (selectedVal != '' || selectedVal == ''))){
                if(inputType === 'radio'){
                    selectedVal = parentRadio.val();
                }
                cwp_condition_logic(selectedVal, value, operator, field, $this.closest('form'));
            }else if(parentCheckbox.is(':checked')){
                var selectedVal = parentCheckbox.val();
                cwp_condition_logic(selectedVal, value, operator, field, $this.closest('form'));
            } else {
                cwp_condition_logic(selectedVal, value, operator, field, $this.closest('form'));
            }

            
            $this.closest('form').on('change input', '*' +form_name_method+ '[' + field + ']"]', function (event) {
                event.preventDefault();
                var selectedVal = jQuery(this).val();
                cwp_condition_logic(selectedVal, value, operator, field, $this.closest('form'));
                
                jQuery('div[class*="'+field+'"]').each(function() {
                    if (jQuery(this).hasClass('conditional-logic')) {
                        if (!jQuery(this).hasClass( field+selectedVal )) {
                            jQuery('div[class*="'+jQuery(this).data('name')+'"]').each(function() {
                                jQuery(this).hide();
                            });
                        }else{
                            selected = jQuery(this).closest('form').find('*' +form_name_method+ '[' + jQuery(this).data('name') + ']"]:visible:checked').val();
                            jQuery('div[class*="'+jQuery(this).data('name')+selected+'"]').each(function() {
                                var $thisx = jQuery(this),
                                fieldx = $thisx.attr('data-field'),
                                valuex = $thisx.attr('data-value'),
                                operatorx = $thisx.attr('data-operator');
                            
                                var parent = $thisx.closest('form').find('*' +form_name_method+ '[' + fieldx + ']"]:visible');
                                var parentCheckbox = $thisx.closest('form').find(form_name_method+ '[' + fieldx + '][]"]');
                                var selectedVal = get_conditional_field_value($thisx.closest('form'), fieldx, true);
                                var tagName = parent.prop('tagName');
                                if ( tagName === 'INPUT' ) {
                                    var inputType = parent.prop('type');
                                    if ( inputType === 'radio' ) {
                                            selectedVal = $thisx.closest('form').find('*' +form_name_method+ '[' + fieldx + ']"]:visible:checked').val();
                                    }
                                }
                                if (parent.is(':checked') || selectedVal != '' || selectedVal == ''){
                                    cwp_condition_logic(selectedVal, valuex, operatorx, fieldx, $thisx.closest('form'));
                                }else if(parentCheckbox.is(':checked')){
                                    var selectedVal = parentCheckbox.val();
                                    cwp_condition_logic(selectedVal, valuex, operatorx, fieldx, $thisx.closest('form'));
                                } else {
                                    cwp_condition_logic(selectedVal, valuex, operatorx, fieldx, $thisx.closest('form'));
                                }
                            });
                        }
                    }
                });
                
            });

            var value_condition = '[value="' + value + '"]';
            if (operator === '!empty' || operator === 'empty' || operator === '!=') value_condition = '';
            $this.closest('form').on('input', '*' +form_name_method+ '[' + field + '][]"]' + value_condition, function (event) {
                event.preventDefault();
                var $this = jQuery(this),
                    selectedVal = '';
                
                if ($this.is(':checked')) selectedVal = $this.val();
                if (operator === '!empty' || operator === 'empty' || operator === '!=') {
                    jQuery('*' +form_name_method+ '[' + field + '][]"]:checked').each(function () {
                        selectedVal = jQuery(this).val();
                    });
                }
                if (operator === '!=') {
                    var target_field = jQuery('*' +form_name_method+ '[' + field + '][]"][value="' + value + '"]');
                    if (target_field.is(':checked')) {
                        selectedVal = target_field.val();
                    }
                }
                cwp_condition_logic(selectedVal, value, operator, field, $this.closest('form'));
            });
        });
    }
}
function cwp_array_diff( array_1, array_2 ) {
    var diffItems = [];
    jQuery.grep(array_1, function(i) {
        if (jQuery.inArray(i, array_2) === -1){
            diffItems.push(i);
        }
    });
    return diffItems;
}
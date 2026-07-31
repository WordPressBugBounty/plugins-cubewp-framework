jQuery(document).ready(function () {
    
    if (jQuery(".cubwp-welcome").length > 0) {
        jQuery('.Section-Faqs').click(function(e) {
            var currentAttrValue = jQuery(this).attr('href');

            if (jQuery(e.target).is('.active')) {
                close_accordion_section();
            } else {
                close_accordion_section();

                jQuery(this).addClass('active');
                jQuery('.Faqs ' + currentAttrValue).slideDown(300).addClass('open');
            }

            e.preventDefault();
        });
    }

    if (jQuery(".cwpform-shortcode").length > 0) {
        jQuery(document).on('click', '.cwpform-shortcode', function (e) {
                var $this = jQuery(this),
                    temp_text = document.createElement("input");
                if ($this.find('.inner').hasClass('copy-to-clipboard')) {
                    temp_text.value = $this.find('.inner').clone().children().remove().end().text();
                    document.body.appendChild(temp_text);
                    temp_text.select();
                    document.execCommand("copy");
                    document.body.removeChild(temp_text);
                }
        });
    }

    if (jQuery(".cubewp_page_cubewp-post-types").length > 0) {
        disable_rewrite_slug();
        jQuery(document).on('change', 'select#rewrite', function (event) {
            disable_rewrite_slug();
        });
    }

    function close_accordion_section() {
        jQuery('.Faqs .Section-Faqs').removeClass('active');
        jQuery('.Faqs .Faqs-section-content').slideUp(300).removeClass('open');
    };

    function disable_rewrite_slug() {
        jQuery('input#rewrite_slug').parents('tr').hide();
        var $this = jQuery('select#rewrite'),
            select = $this.val();

        if ("1" === select) {
            $this.parents('tr').next('tr').show();
        }
    };

    if (jQuery(".cwp-post-type-wrape").length > 0) {
        jQuery(document).on('submit', '.cwp-post-type-wrape form', function (event) {
            var $this = jQuery(this),
                select = $this.find('select[name="action"]').val();

            if ("delete" === select) {
                if ( ! confirm(cwp_vars_params.confirm_text.multiple)){
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    return false;
                }
            }
        });
    }

    jQuery(document).on('click', '.cwp-post-type-wrape .delete a', function (event) {
        if ( ! confirm(cwp_vars_params.confirm_text.single)){
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            return false;
        }
    });
    
    var posttype_menu_icon = jQuery(".cwp-selectMenuIcons > span");
    if (posttype_menu_icon.length > 0) {
        posttype_menu_icon.on("click", function (event) {
            event.preventDefault();
            jQuery(this)
                .closest("td")
                .find("#icon")
                .val(jQuery(this)
                    .attr("data-class"));
        });
    }
    
    if (jQuery(".cwp_import").length > 0) {
        jQuery(document).on('click', '.cwp_import', function(e) {
            e.preventDefault();
            if ( confirm( 'Are You Sure?' ) ) {
                var formData = new FormData(document.getElementById('import_form'));
                jQuery.ajax({
                    type: 'POST',
                    url: cwp_vars_params.ajax_url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function (response) {
                        if( response.success === 'false' ){
                            alert(response.msg);
                        }else{
                            window.location.href = response.redirectURL;
                        }
                    }
                });
            }
        });
        jQuery(document).on('click', '.cwp_import_demo', function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to proceed?\n\nNote: If you are importing the demo on an existing website, you may need to reset the WordPress Installation or remove existing pages to avoid conflicts.")) {
                jQuery(this).append('<div class="loader"></div>');
                jQuery(this).addClass('processing');
                jQuery(this).prop( "disabled", 1 );
                jQuery.ajax({
                    type: 'POST',
                    url: cwp_vars_params.ajax_url,
                    data:'action=cwp_import_dummy_data&data_type=dummy&nonce='+cwp_vars_params.nonce,
                    dataType: 'json',
                    success: function (response) {
                        if( response.success === 'true' ){
                            if( response.content === 'true' ){
                                jQuery.ajax({
                                    type: 'POST',
                                    url: cwp_vars_params.ajax_url,
                                    data:'action=cwp_import_dummy_data&data_type=dummy&content=true&nonce='+cwp_vars_params.nonce,
                                    dataType: 'json',
                                    success: function (response) {
                                        if( response.success === 'false' ){
                                            alert(response.msg);
                                            jQuery(this).prop( "disabled", 0 );
                                        }else{
                                            if(response.redirectURL != null && response.redirectURL != ''){
                                                window.location.href = response.redirectURL;
                                            }else if(response.success_message != null && response.success_message != ''){
                                                jQuery(response.success_message.selecter).text(response.success_message.message);
                                                jQuery(response.success_message.selecter).addClass('done');
                                            }
                                        }
                                    }
                                });
                            }else{
                                if(response.redirectURL != null && response.redirectURL != ''){
                                    window.location.href = response.redirectURL;
                                }else if(response.success_message != null && response.success_message != ''){
                                    jQuery(response.success_message.selecter).text(response.success_message.message);
                                    jQuery(response.success_message.selecter).addClass('done');
                                }
                            }
                        }else if( response.success === 'false' ){
                            alert(response.msg);
                            jQuery(this).prop( "disabled", 0 );
                        }
                    }
                });
            }
        });
    }
    
    if (jQuery(".cwp_export").length > 0) {
        jQuery(document).on('click', '.cwp_export', function (e) {
            e.preventDefault();
            if ( confirm( 'Are You Sure?' ) ) {
                var thisObj = jQuery(this);
                jQuery.ajax({
                    type: 'POST',
                    url: cwp_vars_params.ajax_url,
                    data: jQuery('.export-form').serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if( response.success === 'false' ){
                            alert(response.msg);
                        }else{
                            var export_post_cards = false;
                            if ( jQuery('.export-form').find('#cwp_post_cards').length > 0 ) {
                                if ( jQuery('.export-form').find('#cwp_post_cards').is(':checked') ) {
                                    export_post_cards = true;
                                }
                            }
                            var export_custom_forms = false;
                            if ( jQuery('.export-form').find('#custom-forms-fields').length > 0 ) {
                                if ( jQuery('.export-form').find('#custom-forms-fields').is(':checked') ) {
                                    export_custom_forms = true;
                                }
                            }
                            var _ajax_data = 'action=cwp_user_data&export=success&nonce='+cwp_vars_params.nonce;
                            if (export_post_cards) {
                                _ajax_data += '&export_post_cards=true';
                            }
                            if (export_custom_forms) {
                                _ajax_data += '&download_now=false';
                            }
                            jQuery.ajax({
                                type: 'POST',
                                url: cwp_vars_params.ajax_url,
                                data: _ajax_data,
                                dataType: 'json',
                                success: function (response) {
                                    if( response.success === 'false' ){
                                        alert(response.msg);
                                    }else{
                                        if (export_custom_forms) {
                                            var custom_form_data = 'action=cwp_custom_forms&export=success&nonce='+cwp_vars_params.nonce;
                                            if (export_post_cards) {
                                                custom_form_data += '&export_post_cards=true';
                                            }
                                            jQuery.ajax({
                                                type: 'POST',
                                                url: cwp_vars_params.ajax_url,
                                                data: custom_form_data,
                                                dataType: 'json',
                                                success: function (response) {
                                                    if( response.success === 'false' ){
                                                        alert(response.msg);
                                                    }else{
                                                        alert(response.msg);
                                                        thisObj.hide();
                                                        thisObj.closest('.export-form').find('.cwp_download_content').attr('href', response.file_url);
                                                        thisObj.closest('.export-form').find('.cwp_download_content').removeClass('hidden');
                                                    }
                                                }
                                            });
                                        }else {
                                            alert(response.msg);
                                            thisObj.hide();
                                            thisObj.closest('.export-form').find('.cwp_download_content').attr('href', response.file_url);
                                            thisObj.closest('.export-form').find('.cwp_download_content').removeClass('hidden');
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
    }
    
    if (jQuery('.cwp-widget-select-posttype').length > 0) {
        jQuery(document).on('change', '.cwp-widget-select-posttype', function () {
            let $this = jQuery(this),
                form = $this.closest('form'),
                termSelect = form.find('.cwp-widget-select-term'),
                data = {
                    action: 'cwp_get_terms_by_post_type',
                    post_type: $this.val(),
                    nonce: cwp_vars.nonce
                };
            $this.attr("disabled", "disabled");
            termSelect.attr("disabled", "disabled");
            jQuery.ajax({
                type: 'POST',
                url: cwp_vars.url,
                dataType: 'json',
                data: data,
                success: function (resp) {
                    if (resp.success === true) {
                        $this.removeAttr("disabled");
                        termSelect.empty();
                        var terms = resp.data;
                        if (terms.length > 0) {
                            terms.forEach(function (term) {
                                var termName = term['0'],
                                    termValue = term['1'],
                                    selected = false;
                                if (term['2'] !== "") selected = true;
                                termSelect.append(new Option(termValue, termName, selected));
                            });
                        }
                        termSelect.removeAttr("disabled");
                    }
                }
            });
        });
    }

    if(jQuery('#ctb-add-template-dialog').length > 0){
        jQuery('#ctb-add-template-dialog').dialog({
            autoOpen: false,
            modal: true,
            width: 990,  // Set your desired width here
        });
        
    }

    // Open the dialog
    jQuery('.ctb-add-new-template').on('click', function() {
        jQuery('#ctb-add-template-dialog').dialog('open');
        var $trigger = jQuery(this),
            editLocation = $trigger.attr('data-tlocation') || '',
            editLocationsRaw = $trigger.attr('data-tlocations') || '',
            edittype = $trigger.attr('data-ttype') || '',
            editname = $trigger.attr('data-tname') || '',
            editid = $trigger.attr('data-tid') || '',
            editMobileView = $trigger.attr('data-tmobileview') || '',
            presetLocations = null;

        if (editLocationsRaw) {
            try {
                presetLocations = JSON.parse(editLocationsRaw);
            } catch (e) {
                presetLocations = null;
            }
        }
        if (!presetLocations || (Array.isArray(presetLocations) && !presetLocations.length)) {
            if (editLocation) {
                presetLocations = [editLocation];
            }
        }
        if (presetLocations && !Array.isArray(presetLocations)) {
            presetLocations = [presetLocations];
        }

        // Empty all values
        jQuery('#template_name').val('');
        jQuery('#template_type').val('');
        cwp_tb_destroy_location_select2();
        jQuery('#template_location').empty().val(null);
        jQuery('#template_mobile_view').prop('checked', false);

        if (jQuery('.ctb-hidden-post-id').length > 0) {
            jQuery('.ctb-hidden-post-id').remove();
        }

        if (editname) {
            jQuery('#template_name').val(editname);
            jQuery('#add-template-form').append('<input type="hidden" class="ctb-hidden-post-id" name="ctb_edit_template_id" value="' + editid + '" />');
        }

        if (edittype) {
            jQuery('#template_type').val(edittype);
            cwp_load_theme_builder_rules(edittype, editname ? editMobileView : undefined, presetLocations);
        } else {
            cwp_tb_toggle_mobile_view_field('');
            cwp_tb_toggle_multi_location_field('');
        }
    });

    jQuery('.cwp-save-template').on('click', function() {
        var $form = jQuery(this).closest('form');
        $form.addClass('loader');
        submitForm(jQuery(this).val());
        $form.removeClass('loader');
    });

    function submitForm(action) {
        var formData = jQuery('#add-template-form').serialize();
        jQuery.ajax({
            type: 'POST',
            url: cwp_vars_params.ajax_url,
            data: {
                action: 'cubewp_theme_builder_template',
                nonce: cwp_vars_params.nonce,
                template_action: action,
                data: formData
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(response.data.message || 'There was an error saving the template.');
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                alert('There was an error processing the request.');
            }
        });
    }

    jQuery('#template_type').on('change', function() {
        var templateType = jQuery(this).val();
        cwp_load_theme_builder_rules(templateType);
    });

    // Trigger change event on page load to set initial state
    //jQuery('#template_type').trigger('change');
    
});

function cwp_tb_toggle_mobile_view_field(templateType) {
    var show = templateType === 'single' || templateType === 'archive' || templateType === 'header' || templateType === 'footer';
    jQuery('#cwp_tb_mobile_view_wrap').toggle(show);
    if (!show) {
        jQuery('#template_mobile_view').prop('checked', false);
    }
}

function cwp_tb_destroy_location_select2() {
    var $select = jQuery('#template_location');
    if (!$select.length) {
        return;
    }
    if ($select.hasClass('select2-hidden-accessible') && typeof $select.select2 === 'function') {
        $select.select2('destroy');
    }
}

function cwp_tb_is_multi_location_type(templateType) {
    return ['archive', 'header', 'footer'].indexOf(templateType) !== -1;
}

function cwp_tb_init_location_select2() {
    var $select = jQuery('#template_location');
    if (!$select.length || typeof $select.select2 !== 'function') {
        return;
    }

    var placeholder = $select.attr('data-placeholder') || 'Select display location(s)';
    var $dialog = jQuery('#ctb-add-template-dialog');
    var dropdownParent = $dialog.length ? $dialog.closest('.ui-dialog') : jQuery('body');
    if (!dropdownParent.length) {
        dropdownParent = jQuery('body');
    }

    $select.select2({
        width: '100%',
        placeholder: placeholder,
        allowClear: true,
        closeOnSelect: false,
        minimumResultsForSearch: 10,
        dropdownParent: dropdownParent
    });
}

function cwp_tb_apply_location_values($select, presetLocations, isMulti) {
    if (!presetLocations || !$select.length) {
        return;
    }

    var values = Array.isArray(presetLocations) ? presetLocations : [presetLocations];
    values = values.filter(function (v) {
        return v !== null && v !== undefined && String(v) !== '';
    });

    if (!values.length) {
        return;
    }

    if (isMulti) {
        $select.val(values).trigger('change');
    } else {
        $select.val(values[0]).trigger('change');
    }
}

function cwp_tb_toggle_multi_location_field(templateType, presetLocations) {
    var isMulti = cwp_tb_is_multi_location_type(templateType);
    var $select = jQuery('#template_location');
    var $wrap = jQuery('#cwp_tb_template_location_wrap');

    cwp_tb_destroy_location_select2();

    if (isMulti) {
        $wrap.addClass('cwp-tb-location-is-multi');
        $select.prop('multiple', true);
        $select.attr('name', 'template_locations[]');
        jQuery('#cwp_tb_multi_location_hint').show();
        cwp_tb_init_location_select2();
        cwp_tb_apply_location_values($select, presetLocations, true);
    } else {
        $wrap.removeClass('cwp-tb-location-is-multi');
        $select.prop('multiple', false);
        $select.attr('name', 'template_location');
        jQuery('#cwp_tb_multi_location_hint').hide();
        cwp_tb_apply_location_values($select, presetLocations, false);
    }
}

function cwp_load_theme_builder_rules(templateType, presetMobileView, presetLocations) {

    var templateLocation = jQuery('#template_location');
    var excludeLocation = jQuery('#exclude_location');

    cwp_tb_destroy_location_select2();
    templateLocation.empty();
    if (excludeLocation.length) {
        excludeLocation.empty();
    }

    jQuery.ajax({
        url: ajaxurl, // URL to WordPress admin-ajax.php
        type: 'POST',
        data: {
            action: 'get_template_options',
            template_type: templateType
        },
        success: function(response) {
            if (response.success) {
                templateLocation.append(response.data.template_options);
                if (response.data.exclude_options) {
                    excludeLocation.append(response.data.exclude_options);
                }

                if (templateType === '404' || templateType === 'mega-menu' || templateType === 'shop') {
                    templateLocation.closest('.form-fileds').hide();
                } else {
                    templateLocation.closest('.form-fileds').show();
                }

                cwp_tb_toggle_mobile_view_field(templateType);
                cwp_tb_toggle_multi_location_field(templateType, presetLocations);

                if (typeof presetMobileView !== 'undefined') {
                    jQuery('#template_mobile_view').prop('checked', presetMobileView == 1 || presetMobileView === '1' || presetMobileView === true);
                }
            } else {
                console.log(response.data.message);
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error: ' + status + error);
        }
    });
}

/* CubeWP hub connect JS Start */
// CubeWP Addons page AJAX + modal management
(function ($) {
    function isAddonsPage() {
        return $('.cubewp-addons-wrap').length > 0 || $('.cubwp-welcome').length > 0;
    }

    function norm(s) {
        return (s || '').toString().toLowerCase().trim();
    }

    function ajaxPost(payload) {
        var cfg = window.cubewpAddonsAjax || {};
        return $.ajax({
            url: cfg.ajaxUrl || (typeof ajaxurl !== 'undefined' ? ajaxurl : ''),
            method: 'POST',
            dataType: 'json',
            data: $.extend({
                action: 'cubewp_addons_ajax',
                nonce: cfg.nonce || ''
            }, payload)
        });
    }

    function showTopNotice(type, message) {
        var cls = 'notice';
        if (type) cls += ' notice-' + type;
        var $wrap = $('.cubewp-addons-wrap');
        if (!$wrap.length) {
            $wrap = $('.cubwp-welcome').first();
        }
        if (!$wrap.length) return;
        $wrap.find('.notice.is-dismissible').remove();
        $wrap.prepend('<div class="' + cls + ' is-dismissible"><p>' + $('<div/>').text(message || '').html() + '</p></div>');
    }

    function openModal() {
        $('#cwp-addon-manage-modal').attr('aria-hidden', 'false');
    }

    function closeModal() {
        var $m = $('#cwp-addon-manage-modal');
        $m.attr('aria-hidden', 'true');
        $m.removeData('addonSlug');
        $m.find('[data-cwp-modal-error]').text('');
        $m.find('.cwp-modal__error').hide();
        $m.find('.cwp-modal__content').hide();
        $m.find('.cwp-modal__loading').show();
        $m.find('[data-cwp-licenses-list]').empty();
    }

    function escHtml(s) {
        return $('<div/>').text(s == null ? '' : String(s)).html();
    }

    function escAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/'/g, '&#39;');
    }

    function renderSitesRows(sites, licenseId) {
        var lid = parseInt(licenseId, 10) || 0;
        var rows = '';
        if (!sites || !sites.length) {
            return '<tr><td colspan="2">No activations recorded for this license key.</td></tr>';
        }
        sites.forEach(function (s) {
            var siteId = s.site_id || 0;
            var name = s.site_name || '';
            var token = lid + ':' + siteId;
            rows += '<tr><td>' + escHtml(name) + '</td>' +
                '<td><button type="button" class="button-link-delete" data-cwp-site-deactivate="' + escAttr(token) + '">Deactivate</button></td></tr>';
        });
        return rows;
    }

    function renderManageLicenses($m, data) {
        var product = (data && data.product) ? data.product : '';
        var list = (data && data.licenses) ? data.licenses : [];
        var addonSlug = $m.data('addonSlug') || '';
        $m.find('[data-cwp-license-product]').text(product);
        var $list = $m.find('[data-cwp-licenses-list]');
        $list.empty();
        if (!list.length) {
            $list.append('<p class="description">No licenses returned.</p>');
            return;
        }
        list.forEach(function (lic) {
            var key = lic.key || '';
            var lid = lic.id || 0;
            var onSite = !!lic.on_this_site;
            var summary = lic.summary || '';
            var sitesHtml = renderSitesRows(lic.sites || [], lid);
            var actions = '';
            if (onSite) {
                actions += '<button type="button" class="button" data-cwp-license-deactivate="1" data-addon-slug="' + escAttr(addonSlug) + '" data-license-key="' + escAttr(key) + '">Deactivate on this site</button> ';
            } else {
                actions += '<button type="button" class="button button-primary" data-cwp-license-activate="1" data-addon-slug="' + escAttr(addonSlug) + '" data-license-key="' + escAttr(key) + '">Activate on this site</button> ';
            }
            actions += '<a class="button button-link" href="https://cubewp.com/checkout/purchase-history/" target="_blank" rel="noopener noreferrer">View on CubeWP.com</a>';
            var block = '<div class="cwp-manage-license-block" data-cwp-license-block="' + String(lid) + '">' +
                '<h4 class="cwp-manage-license-block__title">License <code>' + escHtml(key) + '</code></h4>' +
                '<p class="description" style="margin:4px 0 10px;">' + escHtml(summary) + '</p>' +
                '<p style="margin:0 0 10px;">' + actions + '</p>' +
                '<table class="widefat striped cwp-manage-license-sites"><thead><tr><th>Site</th><th>Actions</th></tr></thead><tbody>' +
                sitesHtml + '</tbody></table></div>';
            $list.append(block);
        });
        $m.find('[data-cwp-modal-note]').text(
            'Activating a license on this site saves it as the default key for installs and updates. You can switch keys anytime from this dialog.'
        );
    }

    function loadManageDetails(addonSlug) {
        var $m = $('#cwp-addon-manage-modal');
        $m.data('addonSlug', addonSlug);
        $m.find('.cwp-modal__error').hide();
        $m.find('.cwp-modal__content').hide();
        $m.find('.cwp-modal__loading').show();

        return ajaxPost({ op: 'manage_details', addon_slug: addonSlug }).done(function (res) {
            if (!res || !res.success) {
                var msg = (res && res.data && res.data.message) ? res.data.message : 'Request failed.';
                $m.find('[data-cwp-modal-error]').text(msg);
                $m.find('.cwp-modal__error').show();
                return;
            }
            renderManageLicenses($m, res.data || {});
            $m.find('.cwp-modal__loading').hide();
            $m.find('.cwp-modal__content').show();
        }).fail(function (xhr) {
            var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Request failed.';
            $m.find('[data-cwp-modal-error]').text(msg);
            $m.find('.cwp-modal__error').show();
        });
    }

    function updateCardAfterOp($card, res, op) {
        if (!res || !res.success) return;
        var data = res.data || {};
        if (data.base) {
            $card.find('input[name="addon_base"]').val(data.base);
        }
        if (data.status) {
            $card.find('.cwp-badge')
                .removeClass('status-missing status-installed status-active')
                .addClass('status-' + data.status)
                .text(data.status.charAt(0).toUpperCase() + data.status.slice(1));
        }

        // If install succeeded, ensure Manage appears immediately (no reload)
        if (data.status && data.status !== 'missing') {
            var hasLicenseId = parseInt($card.attr('data-license-id') || '0', 10) > 0;
            var hasManage = $card.find('.cwp-card-manage').length > 0;
            if (hasLicenseId && !hasManage) {
                var slug = $card.attr('data-addon-slug') || '';
                if (slug) {
                    $card.find('.cubewp-card-footer').append(
                        '<div class="cwp-card-manage">' +
                        '<button type="button" class="button cwp-btn-manage" data-cwp-manage="' + slug + '">Manage</button>' +
                        '</div>'
                    );
                }
            }
        }

        // After plugin install, switch Install -> Activate (no reload)
        if (op === 'plugin_install' && data.status === 'installed') {
            var $installForm = $card.find('form.cwp-ajax-form[data-op="plugin_install"]').first();
            if ($installForm.length) {
                $installForm.attr('data-op', 'plugin_activate');
                $installForm.data('op', 'plugin_activate');
                $installForm.find('input[name="action"]').val('cubewp_addons_activate');
                $installForm.find('button[type="submit"]').text('Activate Plugin').addClass('cwp-btn-primary');
            }
        }

        // After plugin activate, switch Activate -> Deactivate (no reload)
        if (op === 'plugin_activate' && data.status === 'active') {
            var $actForm = $card.find('form.cwp-ajax-form[data-op="plugin_activate"]').first();
            if ($actForm.length) {
                $actForm.attr('data-op', 'plugin_deactivate');
                $actForm.data('op', 'plugin_deactivate');
                $actForm.find('input[name="action"]').val('cubewp_addons_deactivate');
                // Deactivate only needs addon_base; keep addon_slug if present (harmless)
                $actForm.find('button[type="submit"]').text('Deactivate').removeClass('cwp-btn-primary');
            }
        }

        // After plugin deactivate, switch Deactivate -> Activate (no reload)
        if (op === 'plugin_deactivate' && data.status === 'installed') {
            var $deactForm = $card.find('form.cwp-ajax-form[data-op="plugin_deactivate"]').first();
            if ($deactForm.length) {
                $deactForm.attr('data-op', 'plugin_activate');
                $deactForm.data('op', 'plugin_activate');
                $deactForm.find('input[name="action"]').val('cubewp_addons_activate');
                $deactForm.find('button[type="submit"]').text('Activate Plugin').addClass('cwp-btn-primary');
            }
        }
    }

    $(document).ready(function () {
        if (!isAddonsPage()) return;

        $(document).on('input', '.cubewp-search-input', function () {
            var term = norm($(this).val());
            $('.cubewp-card[data-haystack]').each(function () {
                var $c = $(this);
                var hay = norm($c.attr('data-haystack'));
                $c.css('display', (!term || hay.indexOf(term) !== -1) ? 'flex' : 'none');
            });
        });

        $(document).on('submit', 'form.cwp-ajax-form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var op = $form.data('op');
            if (!op) return;

            var payload = { op: op };
            if (op === 'plugin_install' || op === 'plugin_activate') {
                payload.addon_slug = $form.find('input[name="addon_slug"]').val() || '';
                payload.addon_base = $form.find('input[name="addon_base"]').val() || '';
            } else if (op === 'plugin_deactivate') {
                payload.addon_base = $form.find('input[name="addon_base"]').val() || '';
            } else if (op === 'theme_install') {
                payload.theme_slug = $form.find('input[name="theme_slug"]').val() || '';
            } else if (op === 'theme_activate') {
                payload.theme_stylesheet = $form.find('input[name="theme_stylesheet"]').val() || '';
            }

            var $card = $form.closest('.cubewp-card');
            var $btn = $form.find('button[type="submit"]');
            var oldText = $btn.text();
            $btn.prop('disabled', true).addClass('cwp-btn-loading');
            if (!$btn.find('.cwp-inline-spinner').length) {
                $btn.append('<span class="spinner is-active cwp-inline-spinner"></span>');
            }

            ajaxPost(payload).done(function (res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Request failed.';
                    showTopNotice('error', msg);
                    return;
                }
                showTopNotice('success', (res.data && res.data.message) ? res.data.message : 'Done.');
                if ($card.length) updateCardAfterOp($card, res, op);

                // Keep Hub panel in sync without reload
                if (op === 'hub_disconnect') {
                    window.location.reload();
                    return;
                }
            }).fail(function (xhr) {
                var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) ? xhr.responseJSON.data.message : 'Request failed.';
                showTopNotice('error', msg);
            }).always(function () {
                $btn.prop('disabled', false).removeClass('cwp-btn-loading');
                $btn.find('.cwp-inline-spinner').remove();
                // If the form op changed (e.g. Install -> Activate), don't revert the new label.
                if (($form.data('op') || $form.attr('data-op')) === op) {
                    $btn.text(oldText);
                }
            });
        });

        $(document).on('click', '[data-cwp-manage]', function () {
            var slug = $(this).attr('data-cwp-manage') || '';
            if (!slug) return;
            openModal();
            loadManageDetails(slug);
        });

        $(document).on('click', '[data-cwp-modal-close]', function () {
            closeModal();
        });
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        // license action buttons removed from modal per UX requirements

        $(document).on('click', '[data-cwp-site-deactivate]', function () {
            var $m = $('#cwp-addon-manage-modal');
            var slug = $m.data('addonSlug') || '';
            var raw = $(this).attr('data-cwp-site-deactivate') || '';
            var parts = raw.split(':');
            var licenseId = parseInt(parts[0] || '0', 10);
            var siteId = parseInt(parts[1] || '0', 10);
            if (!slug || !licenseId || !siteId) {
                $m.find('[data-cwp-modal-error]').text('Missing license/site information. Please close the popup and open Manage again.');
                $m.find('.cwp-modal__error').show();
                return;
            }
            if (!window.confirm('Deactivate this site activation?')) return;

            var $btn = $(this);
            $btn.prop('disabled', true).text('Deactivating...');
            ajaxPost({ op: 'manage_deactivate_site', license_id: licenseId, site_id: siteId }).done(function (res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Request failed.';
                    $m.find('[data-cwp-modal-error]').text(msg);
                    $m.find('.cwp-modal__error').show();
                    return;
                }
                $m.find('.cwp-modal__error').hide();
                loadManageDetails(slug);
            }).fail(function (xhr) {
                var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
                    ? xhr.responseJSON.data.message
                    : 'Request failed.';
                $m.find('[data-cwp-modal-error]').text(msg);
                $m.find('.cwp-modal__error').show();
            }).always(function () {
                $btn.prop('disabled', false).text('Deactivate');
            });
        });

        $(document).on('click', '[data-cwp-license-activate]', function (e) {
            e.preventDefault();
            var $m = $('#cwp-addon-manage-modal');
            var slug = $(this).attr('data-addon-slug') || $m.data('addonSlug') || '';
            var key = $(this).attr('data-license-key') || '';
            if (!slug || !key) return;
            var msg = 'Activate this license on this site? If your plan is limited to one site, the server will only allow it when a slot is free (deactivate it on another site first if needed).';
            if (!window.confirm(msg)) return;
            var $btn = $(this);
            var prevText = $btn.text();
            $btn.prop('disabled', true).text('Activating…');
            ajaxPost({ op: 'license_activate', addon_slug: slug, license_key: key }).done(function (res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Request failed.';
                    $m.find('[data-cwp-modal-error]').text(msg);
                    $m.find('.cwp-modal__error').show();
                    return;
                }
                $m.find('.cwp-modal__error').hide();
                showTopNotice('success', (res.data && res.data.message) ? res.data.message : 'License activated.');
                loadManageDetails(slug);
            }).fail(function (xhr) {
                var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
                    ? xhr.responseJSON.data.message
                    : 'Request failed.';
                $m.find('[data-cwp-modal-error]').text(msg);
                $m.find('.cwp-modal__error').show();
            }).always(function () {
                $btn.prop('disabled', false).text(prevText);
            });
        });

        $(document).on('click', '[data-cwp-license-deactivate]', function (e) {
            e.preventDefault();
            var $m = $('#cwp-addon-manage-modal');
            var slug = $(this).attr('data-addon-slug') || $m.data('addonSlug') || '';
            var key = $(this).attr('data-license-key') || '';
            if (!slug || !key) return;
            if (!window.confirm('Deactivate this license on this site?')) return;
            var $btn = $(this);
            $btn.prop('disabled', true);
            ajaxPost({ op: 'license_deactivate', addon_slug: slug, license_key: key }).done(function (res) {
                if (!res || !res.success) {
                    var msg = (res && res.data && res.data.message) ? res.data.message : 'Request failed.';
                    $m.find('[data-cwp-modal-error]').text(msg);
                    $m.find('.cwp-modal__error').show();
                    return;
                }
                $m.find('.cwp-modal__error').hide();
                showTopNotice('success', (res.data && res.data.message) ? res.data.message : 'License deactivated.');
                loadManageDetails(slug);
            }).fail(function (xhr) {
                var msg = (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message)
                    ? xhr.responseJSON.data.message
                    : 'Request failed.';
                $m.find('[data-cwp-modal-error]').text(msg);
                $m.find('.cwp-modal__error').show();
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });
    });
})(jQuery);
/* CubeWP hub connect JS End */
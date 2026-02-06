jQuery(document).ready(function () {
    jQuery(document).on("click", ".cwp-alert .cwp-alert-close", function () {
        var $this = jQuery(this),
            $parent = $this.closest('.cwp-alert');
        $parent.slideUp(200, function () {
            if ($parent.hasClass("cwp-js-alert")) {
                $parent.hide();
            } else {
                $parent.remove();
            }
        });
    });

    jQuery(document).on('click', '.cubewp-modal-trigger', function (event) {
        event.preventDefault();
        var $this = jQuery(this),
            target = jQuery($this.attr('data-cubewp-modal'));
        if (target.length > 0) {
            target.addClass('shown').fadeIn();
        }
    });
    jQuery(document).on('click', '.cubewp-modal-close', function (event) {
        event.preventDefault();
        var $this = jQuery(this),
            target = $this.closest('.cubewp-modal');
        target.removeClass('shown').fadeOut();
    });

    var view_all_child_terms = jQuery('.cwp-taxonomy-term-child-terms-see-more');
    if (view_all_child_terms.length > 0) {
        view_all_child_terms.on('click', function (e) {
            e.preventDefault();
            var $this = jQuery(this),
                more = $this.attr('data-more'),
                less = $this.attr('data-less'),
                all_child_terms = $this.closest('.cwp-taxonomy-term-child-terms').find('.cwp-taxonomy-term-child-terms-more');
            if ($this.hasClass('cwp-viewing-less')) {
                $this.text(more);
                $this.removeClass('cwp-viewing-less');
                all_child_terms.slideUp('hide');
            } else {
                $this.text(less);
                $this.addClass('cwp-viewing-less');
                all_child_terms.slideDown('show');
            }
        });
    }

});

function cwp_notification_ui(notification_type, notification_content) {
    var $cwp_alert = jQuery(".cwp-alert.cwp-js-alert"),
        $alert_class = '',
        $cwp_alert_content = $cwp_alert.find('.cwp-alert-content');

    if ($cwp_alert.is(":visible") && $cwp_alert_content.html() === notification_content) {
        return false;
    }
    if ($cwp_alert.is(":visible")) {
        $cwp_alert.find('.cwp-alert-close').trigger("click");
    }
    if (notification_type === 'success') {
        $alert_class = 'cwp-alert-success';
    } else if (notification_type === 'warning') {
        $alert_class = 'cwp-alert-warning';
    } else if (notification_type === 'info') {
        $alert_class = 'cwp-alert-info';
    } else if (notification_type === 'error') {
        $alert_class = 'cwp-alert-danger';
    }
    $cwp_alert.removeClass("cwp-alert-danger cwp-alert-success cwp-alert-warning cwp-alert-info").addClass($alert_class);
    $cwp_alert.find('.cwp-alert-heading').text(notification_type + "!");
    $cwp_alert_content.html(notification_content);
    $cwp_alert.slideDown();
    setTimeout(function () {
        $cwp_alert.find('.cwp-alert-close').trigger("click");
    }, 3000);
}

jQuery(document).on('click', '.cwp-post-confirmation-wrap .cwp-confirmation-bottom-bar', function (e) {
    jQuery('.cwp-post-confirmation').slideToggle(700);
});
jQuery(document).on('click', '.cwp-post-confirmation-wrap .cwp-confirmation-bottom-bar', function (e) {
    jQuery('.cwp-post-confirmation').slideToggle(700);
});
jQuery(document).on('click', '.cwp-save-post', function (e) {
    var thisObj = jQuery(this);
    var pid = thisObj.data('pid');
    thisObj.addClass('cubewp-active-ajax');
    jQuery.ajax({
        url: cwp_alert_ui_params.ajax_url,
        type: 'POST',
        data: 'action=cubewp_save_post&post-id=' + pid + '&nonce=' + cwp_alert_ui_params.nonce,
        dataType: "json",
        success: function (response) {
            cwp_notification_ui(response.type, response.msg);
            if (typeof response.text != 'undefined' && response.text != '') {
                thisObj.addClass('cwp-saved-post');
                thisObj.removeClass('cwp-save-post');
                thisObj.find('.cwp-saved-text').html(response.text);
                thisObj.removeClass('cubewp-active-ajax');
            }
        }
    });
});
jQuery(document).on('click', '.cwp-saved-post', function (e) {
    var thisObj = jQuery(this);
    var pid = thisObj.data('pid');
    var action = thisObj.data('action');
    thisObj.addClass('cubewp-active-ajax');
    jQuery.ajax({
        url: cwp_alert_ui_params.ajax_url,
        type: 'POST',
        data: 'action=cubewp_remove_saved_posts&post-id=' + pid + '&nonce=' + cwp_alert_ui_params.nonce,
        dataType: "json",
        success: function (response) {
            cwp_notification_ui(response.type, response.msg);
            if (typeof response.text != 'undefined' && response.text != '') {
                if (action == 'remove') {
                    thisObj.closest('tr').remove();
                }
                thisObj.addClass('cwp-save-post');
                thisObj.removeClass('cwp-saved-post');
                thisObj.find('.cwp-saved-text').html(response.text);
                thisObj.removeClass('cubewp-active-ajax');
            }
        }
    });
});

/*-------- Mega Menu and Nav Menu --------*/
// CubeWP Menus JS >>
jQuery(document).ready(function ($) {
    jQuery(document).on(
        "click",
        ".elementor-cubewp-menu-toggle__icon--open",
        function () {
            jQuery(this).next("svg").addClass("active");
            jQuery(this).removeClass("active");
            jQuery(this)
                .closest(".elementor-cubewp-menu-toggle")
                .next(".elementor-cubewp-nav-menu--dropdown")
                .addClass("active");
        }
    );
    jQuery(document).on(
        "click",
        ".elementor-cubewp-menu-toggle__icon--close",
        function () {
            jQuery(this).prev("svg").addClass("active");
            jQuery(this).removeClass("active");
            jQuery(this)
                .closest(".elementor-cubewp-menu-toggle")
                .next(".elementor-cubewp-nav-menu--dropdown")
                .removeClass("active");
        }
    );
    jQuery(document).on("click", ".cubwp-menu-desktop.mobile", function () {
        jQuery(this)
            .closest(".elementor-widget-container")
            .find(".cubewp-offcanvas-menus")
            .addClass("active");
    });
    jQuery(document).on("click", ".cubewp-menu-closed", function () {
        jQuery(this)
            .closest(".elementor-widget-container")
            .find(".cubewp-offcanvas-menus")
            .removeClass("active");
    });

    $(document).on("click", ".cubewp-mega-menu-item.hover", function (event) {
        if ($(window).width() <= 1024) {
            if (!$(event.target).closest(".cubewp-mega-menu-item-dropdown").length) {
                $(this).toggleClass("active");
                $(this).closest('.cubewp-mega-menu').toggleClass("active");
            }
        }
    });
    $(document).on("click", ".close-mega-menu-mobile", function (event) {
        if ($(window).width() <= 1024) {
            $('.cubewp-mega-menu-item , .cubewp-mega-menu').removeClass("active");
        }
    });
    jQuery(document).ready(function ($) {
        // Open the next slide when clicking the trigger
        $(document).on("click", ".container-next-triger", function () {
            $(this)
                .closest(".elementor-element")
                .next(".container-next-screen")
                .addClass("active");
        });
        // Close the slide when clicking the back button
        $(document).on("click", ".container-back-slide", function (e) {
            if ($(window).width() <= 1024) {
                e.preventDefault();
                setTimeout(() => {
                    if ($(this).closest(".container-next-screen").length > 0) {
                        $(this).closest(".container-next-screen").removeClass("active");
                    } else {
                        $(this).closest(".cubewp-mega-menu").removeClass("active");
                        $(this).closest(".cubewp-mega-menu-item").removeClass("active");
                    }
                }, 200);
            }
        });
    });

    // CubeWP Mega menus JS >>
    jQuery(document).on(
        "click",
        ".cubewp-mega-menu .cubewp-mega-menu-item.click",
        function () {
            var getID = jQuery(this).data("showid");
            jQuery(this)
                .closest(".cubewp-mega-menu")
                .find(".cubewp-mega-menu-item-dropdown")
                .removeClass("active");
            jQuery(this)
                .closest(".cubewp-mega-menu")
                .find(".cubewp-mega-menu-item-dropdown")
                .removeClass("init");
            jQuery(this)
                .closest(".cubewp-mega-menu")
                .find(".cubewp-mega-menu-item.click")
                .removeClass("active");
            jQuery("#" + getID).addClass("active");
            jQuery(document.body).trigger("cubewp_mega_menu_item_loaded");
            jQuery(this).addClass("active");
            setTimeout(function () {
                jQuery("#" + getID).addClass("init");
            }, 500);
        }
    );

    jQuery(document).on(
        "click",
        ".cubewp-mega-menu-mobile-button",
        function () {
            jQuery(this).next(".cubewp-mega-menu.cubwp-menu-desktop").slideToggle();
            jQuery(this).toggleClass("active");
        }
    );

    function adjustMegaMenuDropdown($this) {
        var dropdown = $this.find(".cubewp-mega-menu-item-dropdown");
        if (dropdown.length) {
            var bodyWidth = jQuery("body").width(); // Get the full width of the body
            var dropdownOffsetLeft = dropdown.offset().left; // Current left position of the dropdown
            var bodyOffsetLeft = jQuery("body").offset().left; // Body's left position (should be 0)
            var difference = dropdownOffsetLeft - bodyOffsetLeft;

            // Adjust the dropdown's width and position to span the entire body width
            dropdown.css({
                "left": "-" + difference + "px",
                "right": "auto",
                "width": bodyWidth + "px"
            });
        }
    }

    jQuery(document).on("mouseenter", ".cubewp-mega-menu-item", function () {
        var $this = jQuery(this);
        adjustMegaMenuDropdown($this);
    });

    jQuery(document).on("mouseleave", ".cubewp-mega-menu-item", function () {
        jQuery(this).find(".cubewp-mega-menu-item-dropdown").css({
            "left": "unset",
        });
    });

    setTimeout(function () {
        if (jQuery(".elementor-cubewp-nav-menu__container").length > 0) {
            var get_iconsInd = jQuery(".elementor-cubewp-nav-menu__container").data(
                "icons"
            );
            if (typeof get_iconsInd === "string") {
                get_iconsInd = get_iconsInd.trim().replace(/1$/, "");
            }
            jQuery(".elementor-cubewp-nav-menu__container")
                .find(".menu-item-has-children>a")
                .append(get_iconsInd);
        }
    }, 200);
});

/*------- CubeWP Post Slider ---------*/
function initPostSlider($scope, clicked) {
    var sliders = $scope.find('.cubewp-post-slider');
    if (!sliders.length) return;
    sliders.each(function () {
        var sliderElement = jQuery(this);

        if (sliderElement.hasClass('slick-initialized')) {
            if (clicked == 'clicked') {
                sliderElement.slick("unslick");
                console.log('have slider');
                sliderElement.addClass('sliderElement');
            } else {
                return;

            }

        }

        var isPrevSvg = sliderElement.data('is-prev-svg');
        var isNextSvg = sliderElement.data('is-next-svg');

        var prevArrowHtml = isPrevSvg ? sliderElement.attr('data-prev-arrow-svg') : sliderElement.data('prev-arrow');

        var nextArrowHtml = isNextSvg ? sliderElement.attr('data-next-arrow-svg') : sliderElement.data('next-arrow');

        var enable_wrapper = sliderElement.data('enable-wrapper');
        var slidesToShow = sliderElement.data('slides-to-show');
        var slidesToScroll = sliderElement.data('slides-to-scroll');
        var slidesToShowTablet = sliderElement.data('slides-to-show-tablet');
        var slidesToShowTabletPortrait = sliderElement.data('slides-show-tablet-portrait');
        var slidesToShowMobile = sliderElement.data('slides-to-show-mobile');
        var slidesToScrollTablet = sliderElement.data('slides-to-scroll-tablet');
        var slidesToScrollTabletPortrait = sliderElement.data('slides-scroll-tablet-portrait');
        var slidesToScrollMobile = sliderElement.data('slides-to-scroll-mobile');
        var autoplay = sliderElement.data('autoplay') === true || sliderElement.data('autoplay') === 'true';
        var autoplaySpeed = sliderElement.data('autoplay-speed');
        var Speed = sliderElement.data('speed');
        var infinite = sliderElement.data('infinite') === true || sliderElement.data('infinite') === 'true';
        var fade_effect = sliderElement.data('fade') === true || sliderElement.data('fade') === 'true';
        var variableWidth = sliderElement.data('variable-width') === true || sliderElement.data('variable-width') === 'true';
        var prevArrowButton, nextArrowButton;

        if (isPrevSvg) {
            prevArrowButton = '<button type="button" class="slick-prev">' + prevArrowHtml + '</button>';
        } else {
            prevArrowButton = '<button type="button" class="slick-prev"><i class="' + prevArrowHtml + '"></i></button>';
        }

        if (isNextSvg) {
            nextArrowButton = '<button type="button" class="slick-next">' + nextArrowHtml + '</button>';
        } else {
            nextArrowButton = '<button type="button" class="slick-next"><i class="' + nextArrowHtml + '"></i></button>';
        }
        var CustomArrows = sliderElement.data('custom-arrows') === true || sliderElement.data('custom-arrows') === 'true';
        var CustomDots = sliderElement.data('custom-dots') === true || sliderElement.data('custom-dots') === 'true';
        var enableProgressBar = sliderElement.data('enable-progress-bar') === true || sliderElement.data('enable-progress-bar') === 'true';
        sliderElement.slick({
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            autoplay: autoplay,
            autoplaySpeed: autoplaySpeed,
            speed: Speed,
            infinite: infinite,
            fade: fade_effect,
            variableWidth: variableWidth,
            prevArrow: prevArrowButton,
            nextArrow: nextArrowButton,
            arrows: CustomArrows,
            dots: CustomDots,
            responsive: [{
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: slidesToShowTablet,
                        slidesToScroll: slidesToScrollTablet
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: slidesToShowTabletPortrait,
                        slidesToScroll: slidesToScrollTabletPortrait
                    }
                },
                {
                    breakpoint: 481,
                    settings: {
                        slidesToShow: slidesToShowMobile,
                        slidesToScroll: slidesToScrollMobile
                    }
                }
            ]
        });

        if (enableProgressBar == true) {
            if (!sliderElement.next('.slick-progress').length) {
                sliderElement.after(
                    '<div class="slick-progress"><div class="slick-progress-bar"></div></div>'
                );
                var totalSlides = sliderElement.slick("getSlick").slideCount;
                sliderElement.on("afterChange", function (event, slick, currentSlide) {
                    var progress = ((currentSlide + 1) / totalSlides) * 100;
                    sliderElement.next('.slick-progress').find('.slick-progress-bar').css("width", progress + "%");
                });
            }
        }
        if (enable_wrapper == true) {
            sliderElement.append('<div class="slick-arrows-wrapper"></div>');
            sliderElement.find(".slick-prev").appendTo(sliderElement.find(".slick-arrows-wrapper"));
            sliderElement.find(".slick-dots").appendTo(sliderElement.find(".slick-arrows-wrapper"));
            sliderElement.find(".slick-next").appendTo(sliderElement.find(".slick-arrows-wrapper"));
        }
    });
    if (clicked == 'clicked') {
        jQuery(document).trigger("post_slider_initialized", [$scope]);
    }
}
(function ($) {
    // Expose initPostSlider to a global object if needed outside Elementor's scope
    if (typeof window.CubeWp === 'undefined') {
        window.CubeWp = {};
    }
    window.CubeWp.initPostSlider = initPostSlider;

    // Hook for Elementor frontend and editor
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/cubewp_posts.default', initPostSlider);
    });
})(jQuery);

var CubeWpShortcodePostsAjax = {
    loadPosts: function (containerSelector) {
        var $container = jQuery(containerSelector);
        var parameters = $container.data('parameters');
        if (parameters) {
            jQuery.ajax({
                url: cwp_alert_ui_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'cubewp_posts_output',
                    ...parameters,
                    load_via_ajax: 'yes'
                },
                success: function (response) {
                    if (response.success) {
                        $container.replaceWith(response.data.content);
                        initPostSlider(jQuery(document.body));
                        jQuery(document.body).trigger('cubewp_posts_loaded');
                    } else {
                        $container.html('<div class="cubewp-error-card">Error loading posts.</div>');
                    }
                },
                error: function () {
                    $container.html('<div class="cubewp-error-card">Failed to load posts.</div>');
                }
            });
        }
    }
};
jQuery(document).on("click", ".vpack-nested-tabs  .e-n-tab-title", function () {
    var $dataid = jQuery(this).attr('aria-controls');
    var $sliderContainer = jQuery(this).closest('.elementor-element').find('#' + $dataid);
    var clicked = 'clicked';
    if (jQuery(this).hasClass('init-clicked')) {
        return false;
    }
    initPostSlider($sliderContainer, clicked);
    jQuery(this).addClass('init-clicked');
});

/* CubeWP Term Slider */
function initTermSlider($scope, clicked) {
    var sliders = $scope.find('.cubewp-term-slider');
    if (!sliders.length) return;
    sliders.each(function () {
        var sliderElement = jQuery(this);

        if (sliderElement.hasClass('slick-initialized')) {
            if (clicked == 'clicked') {
                sliderElement.slick("unslick");
                console.log('have slider');
                sliderElement.addClass('sliderElement');
            } else {
                return;

            }

        }

        var isPrevSvg = sliderElement.data('is-prev-svg');
        var isNextSvg = sliderElement.data('is-next-svg');

        var prevArrowHtml = isPrevSvg ? sliderElement.attr('data-prev-arrow-svg') : sliderElement.data('prev-arrow');

        var nextArrowHtml = isNextSvg ? sliderElement.attr('data-next-arrow-svg') : sliderElement.data('next-arrow');

        var enable_wrapper = sliderElement.data('enable-wrapper');
        var slidesToShow = sliderElement.data('slides-to-show');
        var slidesToScroll = sliderElement.data('slides-to-scroll');
        var slidesToShowTablet = sliderElement.data('slides-to-show-tablet');
        var slidesToShowTabletPortrait = sliderElement.data('slides-show-tablet-portrait');
        var slidesToShowMobile = sliderElement.data('slides-to-show-mobile');
        var slidesToScrollTablet = sliderElement.data('slides-to-scroll-tablet');
        var slidesToScrollTabletPortrait = sliderElement.data('slides-scroll-tablet-portrait');
        var slidesToScrollMobile = sliderElement.data('slides-to-scroll-mobile');
        var autoplay = sliderElement.data('autoplay') === true || sliderElement.data('autoplay') === 'true';
        var autoplaySpeed = sliderElement.data('autoplay-speed');
        var Speed = sliderElement.data('speed');
        var infinite = sliderElement.data('infinite') === true || sliderElement.data('infinite') === 'true';
        var fade_effect = sliderElement.data('fade') === true || sliderElement.data('fade') === 'true';
        var variableWidth = sliderElement.data('variable-width') === true || sliderElement.data('variable-width') === 'true';
        var prevArrowButton, nextArrowButton;

        if (isPrevSvg) {
            prevArrowButton = '<button type="button" class="slick-prev">' + prevArrowHtml + '</button>';
        } else {
            prevArrowButton = '<button type="button" class="slick-prev"><i class="' + prevArrowHtml + '"></i></button>';
        }

        if (isNextSvg) {
            nextArrowButton = '<button type="button" class="slick-next">' + nextArrowHtml + '</button>';
        } else {
            nextArrowButton = '<button type="button" class="slick-next"><i class="' + nextArrowHtml + '"></i></button>';
        }
        var CustomArrows = sliderElement.data('custom-arrows') === true || sliderElement.data('custom-arrows') === 'true';
        var CustomDots = sliderElement.data('custom-dots') === true || sliderElement.data('custom-dots') === 'true';
        var enableProgressBar = sliderElement.data('enable-progress-bar') === true || sliderElement.data('enable-progress-bar') === 'true';
        sliderElement.slick({
            slidesToShow: slidesToShow,
            slidesToScroll: slidesToScroll,
            autoplay: autoplay,
            autoplaySpeed: autoplaySpeed,
            speed: Speed,
            infinite: infinite,
            fade: fade_effect,
            variableWidth: variableWidth,
            prevArrow: prevArrowButton,
            nextArrow: nextArrowButton,
            arrows: CustomArrows,
            dots: CustomDots,
            responsive: [{
                    breakpoint: 1025,
                    settings: {
                        slidesToShow: slidesToShowTablet,
                        slidesToScroll: slidesToScrollTablet
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: slidesToShowTabletPortrait,
                        slidesToScroll: slidesToScrollTabletPortrait
                    }
                },
                {
                    breakpoint: 481,
                    settings: {
                        slidesToShow: slidesToShowMobile,
                        slidesToScroll: slidesToScrollMobile
                    }
                }
            ]
        });

        if (enableProgressBar == true) {
            if (!sliderElement.next('.slick-progress').length) {
                sliderElement.after(
                    '<div class="slick-progress"><div class="slick-progress-bar"></div></div>'
                );
                var totalSlides = sliderElement.slick("getSlick").slideCount;
                sliderElement.on("afterChange", function (event, slick, currentSlide) {
                    var progress = ((currentSlide + 1) / totalSlides) * 100;
                    sliderElement.next('.slick-progress').find('.slick-progress-bar').css("width", progress + "%");
                });
            }
        }
        if (enable_wrapper == true) {
            sliderElement.append('<div class="slick-arrows-wrapper"></div>');
            sliderElement.find(".slick-prev").appendTo(sliderElement.find(".slick-arrows-wrapper"));
            sliderElement.find(".slick-dots").appendTo(sliderElement.find(".slick-arrows-wrapper"));
            sliderElement.find(".slick-next").appendTo(sliderElement.find(".slick-arrows-wrapper"));
        }
    });
    if (clicked == 'clicked') {
        jQuery(document).trigger("term_slider_initialized", [$scope]);
    }
}
(function ($) {
    // Expose initTermSlider to a global object if needed outside Elementor's scope
    if (typeof window.CubeWp === 'undefined') {
        window.CubeWp = {};
    }
    window.CubeWp.initTermSlider = initTermSlider;

    // Hook for Elementor frontend and editor
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/cubewp_taxonomy.default', initTermSlider);
    });

    // Initialize on document ready for frontend (non-Elementor contexts)
    jQuery(document).ready(function () {
        initTermSlider(jQuery(document.body));
    });

    /* CubeWP Post Cards Hover Effects */
    window.addEventListener('elementor/frontend/init', () => {

        class CubewpHoverHandler extends elementorModules.frontend.handlers.Base {

            onInit() {
                this.applyHoverStyles();
            }

            onElementChange(settingName) {
                const hoverSettings = [
                    'cwp_hover_animation_direction',
                    'cwp_hover_translate_distance',
                    'cwp_hover_transition_duration'
                ];
                if (hoverSettings.includes(settingName)) {
                    this.applyHoverStyles();
                }
            }

            applyHoverStyles() {
                const settings = this.getElementSettings();
                const direction = settings.cwp_hover_animation_direction || 'none';
                const visiblity = settings.cwp_hover_visibility || 'default';

                const $el = this.$element;
                const elementId = this.getID();
                const $card = $el.closest('.cwp-elementor-post-card, .cwp-elementor-term-card');

                if (!$card.length) return;

                // Remove previous bindings for this specific element only
                $card.off(`.cubewpHover_${elementId}`);

                if (direction === 'none') {
                    $el.css({
                        'transform': '',
                        'opacity': '',
                        'transition': ''
                    });
                    if (visiblity === 'show') {
                        $card.on(`mouseenter.cubewpHover_${elementId}`, () => {
                            $el.css({
                                'display': 'flex',
                            });
                        });
                        // Hover Out
                        $card.on(`mouseleave.cubewpHover_${elementId}`, () => {
                            $el.css({
                                'display': 'none',
                            });
                        });
                    } else if (visiblity === 'hide') {
                        $card.on(`mouseenter.cubewpHover_${elementId}`, () => {
                            $el.css({
                                'display': 'none',
                            });
                        });
                        // Hover Out
                        $card.on(`mouseleave.cubewpHover_${elementId}`, () => {
                            $el.css({
                                'display': 'flex',
                            });
                        });
                    }
                    return;
                }

                const distance = settings.cwp_hover_translate_distance ?.size || 30;
                const duration = settings.cwp_hover_transition_duration ?.size || 0.3;

                let transform = '';
                let opacity = '1';

                switch (direction) {
                    case 'top':
                        transform = `translateY(-${distance}px)`;
                        break;
                    case 'bottom':
                        transform = `translateY(${distance}px)`;
                        break;
                    case 'left':
                        transform = `translateX(-${distance}px)`;
                        break;
                    case 'right':
                        transform = `translateX(${distance}px)`;
                        break;
                    case 'fade':
                        opacity = '0';
                        break;
                    case 'fadeout':
                        opacity = '1';
                        break;
                }

                // Apply base styles
                $el.css({
                    'transition': `all ${duration}s ease`,
                    'transform': transform,
                    'opacity': opacity
                });

                // Hover In (applies only for this element)
                $card.on(`mouseenter.cubewpHover_${elementId}`, () => {
                    if (direction === 'fadeout') {
                        // Fade OUT on hover
                        $el.css({
                            'opacity': '0',
                            'transform': 'none',
                        });
                    } else {
                        // All other animations fade/slide IN
                        $el.css({
                            'transform': 'none',
                            'opacity': '1'
                        });
                    }
                });

                // Hover Out
                $card.on(`mouseleave.cubewpHover_${elementId}`, () => {
                    if (direction === 'fadeout') {
                        // Restore visibility when mouse leaves
                        $el.css({
                            'opacity': '1',
                            'transform': transform,
                        });
                    } else {
                        // Return to default (hidden or translated)
                        $el.css({
                            'transform': transform,
                            'opacity': opacity
                        });
                    }
                });
            }
        }

        // Apply to container, button, and icon widgets
        ['container', 'button.default', 'icon.default'].forEach(widget => {
            elementorFrontend.hooks.addAction(`frontend/element_ready/${widget}`, ($scope) => {
                elementorFrontend.elementsHandler.addHandler(CubewpHoverHandler, {
                    $element: $scope
                });
            });
        });
    });

    window.addEventListener('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/container', function ($scope) {
            if (elementorFrontend.isEditMode()) {
                class ContainerHandler extends elementorModules.frontend.handlers.Base {
                    onInit() {
                        super.onInit();
                        const settings = this.getElementSettings();
                        const cwp_click_target_remove_css_controls = settings.cwp_click_target_remove_css_controls;
                        const cwp_click_target_remove_css_transform = settings.cwp_click_target_remove_css_transform;
                        const cwp_click_target_remove_css_transform_x = settings.cwp_click_target_remove_css_transform_x;
                        const cwp_click_target_remove_css_transition = settings.cwp_click_target_remove_css_transition;
                        if (cwp_click_target_remove_css_controls === 'hide') {
                            $scope.css('display', 'none');
                        } else if (cwp_click_target_remove_css_controls === 'show') {
                            $scope.css('display', 'block');
                        } else if (cwp_click_target_remove_css_controls === 'transform') {
                            $scope.css('transform', 'translateX(' + cwp_click_target_remove_css_transform + 'px)');
                            $scope.css('transform-x', 'translateY(' + cwp_click_target_remove_css_transform_x + 'px)');
                            $scope.css('transition', 'transform ' + cwp_click_target_remove_css_transition + 'ms');
                        }

                    }
                    onElementChange(settingName) {
                        super.onElementChange ?.apply(this, arguments);
                        const sliderSettings = [
                            'cwp_click_target_remove_css_controls',
                            'cwp_click_target_remove_css_transform', 'cwp_click_target_remove_css_transform_x', 'cwp_click_target_remove_css_transform_x'
                        ];

                        if (sliderSettings.includes(settingName)) {
                            this.onInit();
                        }
                    }
                }
                elementorFrontend.elementsHandler.addHandler(ContainerHandler, {
                    $element: $scope
                });
            }
        });
    });

    jQuery(document).ready(function ($) {
        jQuery(document).on('click', '.cwp-elementor-post-card, .cwp-elementor-term-card', function (event) {
            $(this).find('.cwp-click-element[data-cwp-click-enabled="true"]').each(function () {
                const clickable = $(this);
                const targetMode = clickable.data('cwp-target-mode');
                const applyCss = clickable.data('cwp-apply-css');
                if (targetMode === 'parent' && applyCss) {
                    clickable.attr('style', function (i, oldStyle) {
                        return (oldStyle ? oldStyle + '; ' : '') + applyCss;
                    });
                }
            });
        });

        jQuery(document).on('click', '.cwp-click-element[data-cwp-click-enabled="true"]', function (event) {
            const clickable = $(this);
            const targetMode = clickable.data('cwp-target-mode');
            const applyCss = clickable.data('cwp-apply-css');
            if (targetMode === 'current' && applyCss) {
                clickable.attr('style', function (i, oldStyle) {
                    return (oldStyle ? oldStyle + '; ' : '') + applyCss;
                });
            }
        });

        jQuery(document).on('click', '.cwp-click-element[data-cwp-click-enabled="true"]', function (event) {
            const clickable = $(this);
            const targetMode = clickable.data('cwp-target-class');


            var $this_data = $('.cwp-click-element[data-cwp-apply-class="' + targetMode + '"]');
            var $this_data_remove = $('.cwp-click-element[data-cwp-remove-class="' + targetMode + '"]');
            if ($this_data.length > 0) {
                const targetModes = $this_data.data('cwp-target-mode');
                const dataID = $this_data.data('id');
                const applyClass = $this_data.data('cwp-apply-class');
                const applyCss = $this_data.data('cwp-apply-css');
                if (targetModes === 'custom' && applyClass) {
                    const card = $(this).closest('.cwp-elementor-post-card, .cwp-elementor-term-card');
                    const element = card.find('.cwp-click-element[data-id="' + dataID + '"]');
                    element.removeAttr('style');
                    element.attr('style', applyCss);

                }
            }
            if ($this_data_remove.length > 0) {
                const targetModes = $this_data_remove.data('cwp-target-mode');
                const dataID = $this_data_remove.data('id');
                const removeClass = $this_data_remove.data('cwp-remove-class');
                const removeCss = $this_data_remove.data('cwp-remove-css');
                console.log('.cwp-click-element[data-id="' + dataID + '"]');
                if (targetModes === 'custom' && removeClass && $this_data_remove.length > 0) {
                    const card = $(this).closest('.cwp-elementor-post-card, .cwp-elementor-term-card');
                    const element = card.find('.cwp-click-element[data-id="' + dataID + '"]');
                    element.removeAttr('style');
                    element.attr('style', removeCss);
                }
            }
        })
    });

    //Grid/List Switcher
    jQuery(document).on('click', '.cubewp-view-btn', function (e) {
        e.preventDefault();

        var $btn = jQuery(this);
        var view = $btn.data('view');
        var $wrapper = $btn.closest('.cubewp-view-switcher');

        // Active button
        $wrapper.find('.cubewp-view-btn').removeClass('active');
        $btn.addClass('active');

        // Apply view class to archive container
        var $archive = jQuery('.cwp-search-result-output .cwp-grids-container');
        $archive.removeClass('grid-view list-view')
            .addClass(view + '-view ');
    });

})(jQuery);
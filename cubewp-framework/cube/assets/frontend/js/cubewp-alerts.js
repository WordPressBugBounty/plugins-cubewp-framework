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
        var $this = jQuery(this)
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
})
var cubewpCaptchaLoaded = function() {
    (function ($) {
        'use strict';
        $.cubewp_recaptcha = $.cubewp_recaptcha || {};

        var captcha_init = $(".cubewp-form-recaptcha");
        if (captcha_init.length > 0) {
            captcha_init.each(function () {
                var captcha_init_id = $(this).attr("id");
                grecaptcha.render(captcha_init_id, {
                    'sitekey' : cubewp_recaptcha_params.site_key,
                });
            });
        }

    })(jQuery);
};

(function ($) {
    'use strict';

    if (typeof cubewp_recaptcha_params === 'undefined' || cubewp_recaptcha_params.recaptcha_type !== 'google_v3') {
        return;
    }

    var siteKey = cubewp_recaptcha_params.site_key;

    function getRecaptchaAction(form) {
        var input = form.querySelector('input.cubewp-form-recaptcha-v3');
        if (!input) {
            return 'cubewp_form_submit';
        }
        return input.getAttribute('data-recaptcha-action') || 'cubewp_form_submit';
    }

    function setRecaptchaToken(form, token) {
        var input = form.querySelector('input.cubewp-form-recaptcha-v3[name="g-recaptcha-response"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'g-recaptcha-response';
            input.className = 'cubewp-form-recaptcha-v3';
            input.setAttribute('data-recaptcha-action', 'cubewp_form_submit');
            form.appendChild(input);
        }
        input.value = token;
    }

    function refreshRecaptchaToken(form, callback) {
        if (typeof grecaptcha === 'undefined') {
            if (typeof callback === 'function') {
                callback(false);
            }
            return;
        }

        grecaptcha.ready(function () {
            grecaptcha.execute(siteKey, { action: getRecaptchaAction(form) }).then(function (token) {
                setRecaptchaToken(form, token);
                if (typeof callback === 'function') {
                    callback(true);
                }
            }).catch(function () {
                if (typeof callback === 'function') {
                    callback(false);
                }
            });
        });
    }

    function initV3Forms() {
        $('form').each(function () {
            if (this.querySelector('input.cubewp-form-recaptcha-v3')) {
                refreshRecaptchaToken(this);
            }
        });
    }

    $(document).ready(function () {
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.ready(initV3Forms);
        } else {
            var attempts = 0;
            var waitForRecaptcha = window.setInterval(function () {
                attempts++;
                if (typeof grecaptcha !== 'undefined') {
                    window.clearInterval(waitForRecaptcha);
                    grecaptcha.ready(initV3Forms);
                } else if (attempts >= 50) {
                    window.clearInterval(waitForRecaptcha);
                }
            }, 100);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.tagName !== 'FORM' || !form.querySelector('input.cubewp-form-recaptcha-v3')) {
            return;
        }
        if (form.dataset.cubewpRecaptchaDone === '1') {
            delete form.dataset.cubewpRecaptchaDone;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        refreshRecaptchaToken(form, function (success) {
            if (!success) {
                return;
            }

            form.dataset.cubewpRecaptchaDone = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                $(form).trigger('submit');
            }
        });
    }, true);
})(jQuery);
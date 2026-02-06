jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.cubewp-load-more-button', function () {
        var button = $(this);
        var dataAttributes = button.data('attributes');
        $('.cubewp-load-more-conatiner').remove();
        dataAttributes.sendby = 'load_more';

        // Ensure action is added to the data attributes
        dataAttributes.action = 'cubewp_posts_output';

        jQuery.ajax({
            url: cwp_alert_ui_params.ajax_url,
            type: 'POST',
            dataType: "json",
            data: dataAttributes, // Send data as query string
            success: function (response) {
                jQuery('.cubewp-posts-shortcode').append(response.data.content);

                // Check if there are more posts
                if (response.data.has_more_posts) {
                    if (response.data.newAttributes) {
                        button.data('attributes', response.data.newAttributes);
                    }
                } else {
                    button.hide();
                    jQuery('.cubewp-load-more-conatiner').append('<div class="no-more-posts">No more posts</div>');
                }
            },
            error: function (xhr, status, error) {}
        });
    });
});
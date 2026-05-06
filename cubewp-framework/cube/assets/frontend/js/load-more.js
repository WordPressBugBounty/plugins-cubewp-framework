jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.cubewp-load-more-button', function () {
        var button = $(this);
        var dataAttributes = button.data('attributes');
    
        dataAttributes.sendby = 'load_more';
        $(this).addClass('cubewp-processing-ajax');

        dataAttributes.action = 'cubewp_posts_output';
        if ($('.cwp-option').hasClass('selected')) {
            dataAttributes.cwp_reviews_filters_value = $('.cwp-option.selected').data('value');
            dataAttributes.cwp_reviews_filters_key = 'cwp_reviews_filters';
        }
        jQuery.ajax({
            url: cwp_alert_ui_params.ajax_url,
            type: 'POST',
            dataType: "json",
            data: dataAttributes,
            success: function (response) {
                // Cache DOM references before removing load more container (which contains the button)
                var widget = button.closest('.elementor-widget-cubewp_posts');
                var postsShortcode = widget.find('.cubewp-posts-shortcode'); 
                widget.find('.cubewp-load-more-conatiner').remove();
                postsShortcode.append(response.data.content);
                postsShortcode.attr('data-post-count', response.data.total_posts);
                // Check if there are more posts
                if (response.data.has_more_posts) {
                    if (response.data.newAttributes) {
                        button.data('attributes', response.data.newAttributes);
                    }
                } else {
                    button.hide();
                    widget.find('.cubewp-load-more-conatiner').append('<div class="no-more-posts">No more posts</div>');
                }
            },
            error: function (xhr, status, error) {}
        });
    });
});
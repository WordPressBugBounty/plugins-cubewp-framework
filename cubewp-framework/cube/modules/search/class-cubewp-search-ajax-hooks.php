<?php

/**
 * display fields of custom fields.
 *
 * @version 1.0
 * @package cubewp/cube/modules/search
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * CubeWp_Search_Ajax_Hooks
 */
class CubeWp_Search_Ajax_Hooks
{

    private static $terms = null;

    /**
     * Method cwp_search_filters_ajax_content
     *
     * @return array json to ajax
     * @since  1.0.0
     */
    public static function cwp_search_filters_ajax_content()
    {
        global $cwpOptions;
        $archive_map = isset($cwpOptions['archive_map']) ? $cwpOptions['archive_map'] : 1;
        $archive_filters = isset($cwpOptions['archive_filters']) ? $cwpOptions['archive_filters'] : 1;
        $posts_per_page = isset($cwpOptions['archive_posts_per_page']) ? $cwpOptions['archive_posts_per_page'] : 10;
        $grid_class = 'cwp-col-12 cwp-col-md-6';
        if (! $archive_map || ! $archive_filters) {
            $grid_class = 'cwp-col-12 cwp-col-md-4';
        }
        $latLng = array();
        $post_data = CubeWp_Sanitize_text_Array($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

        $allowed_post_types = CWP_all_post_types();
        $post_type = isset($post_data['post_type']) ? sanitize_text_field($post_data['post_type']) : '';

        $post_data['post_status'] = 'publish'; // Ensure only published posts are queried

        // Validate post_type against allowed list
        if (!in_array($post_type, $allowed_post_types, true)) {
            $post_type = '';
        }

        $post_data['posts_per_page'] = apply_filters('cubewp/search/post_per_page', $posts_per_page, $post_data);

        $_DATA = apply_filters('cubewp/search/query/update', $post_data, sanitize_text_field($post_type));

        $page_num     =  isset($_DATA['page_num']) ? $_DATA['page_num'] : 1;
        $post_type    =  isset($_DATA['post_type']) ? $_DATA['post_type'] : '';
        $post_per_page = isset($_DATA['posts_per_page']) ? $_DATA['posts_per_page'] : 10;
        $style = isset($_DATA['style']) ? $_DATA['style'] : '';

        $query = new CubeWp_Query($_DATA);
        $the_query = $query->cubewp_post_query();



        $grid_view_html = '';
        if ($the_query->have_posts()) {
            ob_start();
            $data_args = array(
                'total_posts'    => $the_query->found_posts,
                'terms' => self::$terms,
                'data' => $_DATA,
            );
            $data = apply_filters('cubewp_frontend_search_data', '', $data_args);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo apply_filters('cubewp/frontend/before/search/loop', '');

            $promotional_cards = [];
            foreach ($_DATA as $key => $value) {
                if (strpos($key, 'cubewp_promotional_card_option-') !== false) {
                    preg_match('/-(\d+)$/', $key, $matches);
                    $index = $matches[1] ?? null;
                    if ($index !== null && isset($_DATA["cubewp_promotional_card_position-$index"])) {
                        $position = $_DATA["cubewp_promotional_card_position-$index"] ?? null;
                        if ($position !== null) {
                            $promotional_cards[$position] = [
                                'option' => $value, // direct value (string now)
                                'width'  => $_DATA["cubewp_promotional_card_width-$index"] ?? '',
                            ];
                        }
                    }
                }
            }

?>
            <div class="cwp-grids-container cwp-row <?php echo esc_attr(cwp_get_post_card_view()); ?>">
                <?php
                $counter = 1;
                while ($the_query->have_posts()): $the_query->the_post();
                    if (get_the_ID()) {
                        if (!empty(self::cwp_map_lat_lng(get_the_ID()))) {
                            $latLng[] = self::cwp_map_lat_lng(get_the_ID());
                        }
                        if (isset($promotional_cards[$counter]) && !empty($promotional_cards[$counter])) {
                            $promotional_cardID =  $promotional_cards[$counter]['option'];
                            $width = $promotional_cards[$counter]['width'];
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo cubewp_promotional_card_output($promotional_cardID, $width);
                        }
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo CubeWp_frontend_grid_HTML(get_the_ID(), $grid_class, $style);
                        $counter++;
                    }
                endwhile;
                ?>
            </div>
<?php
            $pagination_args = array(
                'total_posts'    => $the_query->found_posts,
                'posts_per_page' => $post_per_page,
                'page_num'       => $page_num
            );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo apply_filters('cubewp_frontend_posts_pagination', '', $pagination_args);
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo apply_filters('cubewp/frontend/after/search/loop', '');
            $grid_view_html = ob_get_contents();
            ob_end_clean();
        } else {
            $no_result_template_id = false;

            // On taxonomy archives, post_type may be empty; resolve no-results template by taxonomy first.
            $taxonomy = self::detect_taxonomy_from_request($post_data);
            if (!empty($taxonomy)) {
                $no_result_template_id = CubeWp_Theme_Builder::get_template_post_id_by_location('tax_' . $taxonomy, 'no-results');
            }

            // Fallback to post type no-results template when available.
            if (!$no_result_template_id) {
                // Prefer prefixed location, then fallback to legacy (unprefixed) for backward compatibility.
                if (!empty($post_type)) {
                    $no_result_template_id = CubeWp_Theme_Builder::get_template_post_id_by_location('pt_' . $post_type, 'no-results');
                    if (!$no_result_template_id) {
                        $no_result_template_id = CubeWp_Theme_Builder::get_template_post_id_by_location($post_type, 'no-results');
                    }
                }
            }

            // Final fallback: global no-results template.
            if (!$no_result_template_id) {
                $no_result_template_id = CubeWp_Theme_Builder::get_template_post_id_by_location('all', 'no-results');
            }
            $grid_view_html = self::cwp_no_result_found($no_result_template_id);
        }
        wp_reset_postdata();
        if (empty($latLng)) $latLng = '';
        if (empty($data)) $data = '';

        wp_send_json(array('post_data_details' => $data, 'map_cordinates' =>  $latLng, 'grid_view_html' => $grid_view_html));
    }

    public static function cwp_map_lat_lng($postid = '')
    {
        $Map = array();
        $map_meta_key = self::cwp_map_meta_key(get_post_type($postid));
        if ($map_meta_key && !empty($map_meta_key) && !empty($postid)) {
            $Lat = get_post_meta($postid, $map_meta_key . '_lat', true);
            $Lng = get_post_meta($postid, $map_meta_key . '_lng', true);
            if (!empty($Lat) && !empty($Lng)) {
                $Map[0] = $Lat;
                $Map[1] = $Lng;
                $Map[2] = get_the_title($postid);
                $Map[3] = get_the_permalink($postid);
                $Map[4] = cubewp_get_post_thumbnail_url($postid);
                $Map[5] = apply_filters('cubewp/search_result/map/pin', '', $postid);
                return $Map;
            }
        }
    }

    private static function cwp_map_meta_key($post_type = '')
    {
        if (empty($post_type)) return;
        $options = CWP()->get_custom_fields('post_types');
        $options = $options == '' ? array() : $options;
        if (isset($options['cwp_map_meta'][$post_type]) && !empty($options['cwp_map_meta'][$post_type])) {
            $MapMeta = $options['cwp_map_meta'][$post_type];
            return $MapMeta;
        }
    }

    private static function cwp_no_result_found($template_id)
    {
        if (!empty($template_id)) {
            if (did_action('elementor/loaded')) {
                return '<div class="elementor-cwp-empty-search">' . \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($template_id) . '</div>';
            }
        } else {
            return '<div class="cwp-empty-search"><img class="cwp-empty-search-img" src="' . esc_url(CWP_PLUGIN_URI . 'cube/assets/frontend/images/no-result.png') . '" alt=""><h2>' . esc_html__('No Results Found', 'cubewp-framework') . '</h2><p>' . esc_html__('There are no results matching your search.', 'cubewp-framework') . '</p></div>';
        }
    }

    /**
     * Detect taxonomy context from the AJAX request payload.
     * On taxonomy archives, the search filter form includes a hidden field named as taxonomy with term slug.
     *
     * @param array $post_data Sanitized request payload.
     *
     * @return string Taxonomy name or empty string.
     */
    private static function detect_taxonomy_from_request($post_data) {
        if (!is_array($post_data) || empty($post_data)) {
            return '';
        }

        $taxonomies = get_taxonomies(array('public' => true), 'names');
        if (empty($taxonomies) || !is_array($taxonomies)) {
            return '';
        }

        foreach ($taxonomies as $tax) {
            if (!empty($tax) && isset($post_data[$tax]) && $post_data[$tax] !== '') {
                return sanitize_text_field($tax);
            }
        }

        return '';
    }
}

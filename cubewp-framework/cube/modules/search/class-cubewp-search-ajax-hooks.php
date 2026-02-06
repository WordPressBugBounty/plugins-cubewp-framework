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

        $_DATA = self::filter_business_hours_posts($post_data, $post_type);
        $_DATA = self::filter_ratings_and_views($_DATA, $post_type);

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
            $grid_view_html = self::cwp_no_result_found();
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

    
        /**
     * Filter posts by business hours status
     * 
     * @param array $post_data The post data array from AJAX request
     * @param string $post_type The post type to filter
     * @return array Modified post data with post__in filter applied
     * @since 1.0.0
     */
    private static function filter_business_hours_posts($post_data, $post_type)
    {  
        if (empty($post_data) || !is_array($post_data) || empty($post_type)) {
            return $post_data;
        } 
        // Look for business hours status parameters (format: fieldname_business_hours_status)
        $business_hours_filters = array();
        foreach ($post_data as $key => $value) {
           
            if (strpos($key, '_status') !== false && !empty($value)) {
                $field_name = str_replace('_status', '', $key);
                $status = sanitize_text_field($value);
                
                if (!empty($field_name) && !empty($status)) {
                    $business_hours_filters[] = array(
                        'field_name' => $field_name,
                        'status' => $status
                    );
                } 
                // Remove the business hours status parameter as it's been processed
                unset($post_data[$key]);
            }
        }
         // If no business hours filters, return original data
        
        if (empty($business_hours_filters)) {
            return $post_data;
        } 
        // Get current day and time
        $timezone = wp_timezone_string();
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        $currentDateTime = new \DateTime('now', new \DateTimeZone($timezone));
        $currentDay = strtolower($currentDateTime->format('l'));
        $currentTime = $currentDateTime->format('H:i:s');

        // Get all posts of this post type (respecting existing filters if any)
        $base_args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
        );

        // If there's already a post__in filter, use it as base
        if (isset($post_data['post__in']) && is_array($post_data['post__in']) && !empty($post_data['post__in'])) {
            $base_args['post__in'] = $post_data['post__in'];
        }

        // Get posts to check (using existing query if available)
        $posts_to_check = get_posts($base_args);

        if (empty($posts_to_check)) {
            // No posts to check, return empty result
            $post_data['post__in'] = array(0);
            return $post_data;
        }

        $filtered_post_ids = array();

        // Check each post against all business hours filters
        foreach ($posts_to_check as $post_id) {
            $matches_all_filters = true;

            foreach ($business_hours_filters as $filter) {
                $field_name = $filter['field_name'];
                $status = $filter['status'];

                $business_hours = get_post_meta($post_id, $field_name, true);

                if (empty($business_hours) || !is_array($business_hours)) {
                    // No business hours data - only match if status is 'closed_now' or 'day_off'
                    if ($status === 'open_now' || $status === 'open_24_hours') {
                        $matches_all_filters = false;
                        break;
                    }
                    continue;
                }

                $matches = false;

                if (isset($business_hours[$currentDay])) {
                    $day_schedule = $business_hours[$currentDay];

                    switch ($status) {
                        case 'open_now':
                            // Check if currently open
                            if (!is_array($day_schedule) && $day_schedule === '24-hours-open') {
                                $matches = true;
                            } elseif (is_array($day_schedule) && isset($day_schedule['open']) && isset($day_schedule['close'])) {
                                $open_times = is_array($day_schedule['open']) ? $day_schedule['open'] : array($day_schedule['open']);
                                $close_times = is_array($day_schedule['close']) ? $day_schedule['close'] : array($day_schedule['close']);

                                for ($i = 0; $i < count($open_times); $i++) {
                                    $open_time = isset($open_times[$i]) ? $open_times[$i] : '';
                                    $close_time = isset($close_times[$i]) ? $close_times[$i] : '';

                                    if (!empty($open_time) && !empty($close_time)) {
                                        if ($currentTime >= $open_time && $currentTime <= $close_time) {
                                            $matches = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            break;

                        case 'closed_now':
                            // Check if currently closed
                            if (!is_array($day_schedule) && $day_schedule === '24-hours-open') {
                                $matches = false; // 24 hours open means never closed
                            } elseif (is_array($day_schedule) && isset($day_schedule['open']) && isset($day_schedule['close'])) {
                                $open_times = is_array($day_schedule['open']) ? $day_schedule['open'] : array($day_schedule['open']);
                                $close_times = is_array($day_schedule['close']) ? $day_schedule['close'] : array($day_schedule['close']);

                                $is_open = false;
                                for ($i = 0; $i < count($open_times); $i++) {
                                    $open_time = isset($open_times[$i]) ? $open_times[$i] : '';
                                    $close_time = isset($close_times[$i]) ? $close_times[$i] : '';

                                    if (!empty($open_time) && !empty($close_time)) {
                                        if ($currentTime >= $open_time && $currentTime <= $close_time) {
                                            $is_open = true;
                                            break;
                                        }
                                    }
                                }
                                $matches = !$is_open; // Closed if not open
                            } else {
                                $matches = true; // No schedule means closed
                            }
                            break;

                        case 'open_24_hours':
                            // Check if open 24 hours today
                            if (!is_array($day_schedule) && $day_schedule === '24-hours-open') {
                                $matches = true;
                            }
                            break;

                        case 'day_off':
                            // This case is handled below (day not in schedule)
                            $matches = false;
                            break;
                    }
                } else {
                    // Day not in schedule
                    if ($status === 'day_off') {
                        $matches = true;
                    } elseif ($status === 'closed_now') {
                        $matches = true;
                    } else {
                        $matches = false;
                    }
                }

                // If this filter doesn't match, the post doesn't match all filters
                if (!$matches) {
                    $matches_all_filters = false;
                    break;
                }
            }

            // If post matches all business hours filters, include it
            if ($matches_all_filters) {
                $filtered_post_ids[] = $post_id;
            }
        }

        // Apply filtered post IDs to query
        if (!empty($filtered_post_ids)) {
            if (isset($post_data['post__in']) && is_array($post_data['post__in']) && !empty($post_data['post__in'])) {
                // Intersect with existing post__in (if any)
                $post_data['post__in'] = array_intersect($post_data['post__in'], $filtered_post_ids);
            } else {
                $post_data['post__in'] = $filtered_post_ids;
            }
        } else {
            // No posts match, return empty result
            $post_data['post__in'] = array(0);
        }

        return $post_data;
    }

    /**
     * Filter posts by ratings and views
     * 
     * @param array $post_data The post data array from AJAX request
     * @param string $post_type The post type to filter
     * @return array Modified post data with filters applied
     * @since 1.0.0
     */
    private static function filter_ratings_and_views($post_data, $post_type)
    {
        if (empty($post_data) || !is_array($post_data) || empty($post_type)) {
            return $post_data;
        }

        // Check if reviews plugin is active
        if (!defined('CUBEWP_REVIEWS') && !class_exists('CubeWp_Reviews_Load')) {
            return $post_data;
        }

        // Check for rating filters (rating_1, rating_2, etc.)
        $rating_filter = null;
        foreach ($post_data as $key => $value) {
            if (strpos($key, 'rating_') === 0 && !empty($value)) {
                $rating_filter = (int) str_replace('rating_', '', $key);
                unset($post_data[$key]);
                break;
            }
        }

        // Note: most_viewed and high_rated are now handled via orderby parameter for sorting, not filtering
        // They should not be processed here as filters

        // If no rating filter, return original data
        if ($rating_filter === null) {
            return $post_data;
        }

        // Get base query args
        $base_args = array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
        );

        // If there's already a post__in filter, use it as base
        if (isset($post_data['post__in']) && is_array($post_data['post__in']) && !empty($post_data['post__in'])) {
            $base_args['post__in'] = $post_data['post__in'];
        }

        // Get posts to check
        $posts_to_check = get_posts($base_args);

        if (empty($posts_to_check)) {
            $post_data['post__in'] = array(0);
            return $post_data;
        }

        $filtered_post_ids = array();

        foreach ($posts_to_check as $post_id) {
            $include_post = true;

            // Filter by rating
            if ($rating_filter !== null) {
                // Get average rating for this post from reviews
                $average_rating = '';
                if (class_exists('CubeWp_Reviews_Stats')) {
                    $average_rating = CubeWp_Reviews_Stats::get_current_post_rating('post', $post_id);
                }
                
                if (empty($average_rating)) {
                    $include_post = false;
                } else {
                    $avg_rating = (float) $average_rating;
                    // Check if average rating matches the filter (e.g., rating_1 means >= 1.0 and < 2.0)
                    if ($avg_rating < $rating_filter || $avg_rating >= ($rating_filter + 1)) {
                        $include_post = false;
                    }
                }
            }

            // Note: high_rated and most_viewed are now handled via orderby for sorting, not filtering here

            if ($include_post) {
                $filtered_post_ids[] = $post_id;
            }
        }

        // Apply filtered post IDs to query
        if (!empty($filtered_post_ids)) {
            if (isset($post_data['post__in']) && is_array($post_data['post__in']) && !empty($post_data['post__in'])) {
                $post_data['post__in'] = array_intersect($post_data['post__in'], $filtered_post_ids);
            } else {
                $post_data['post__in'] = $filtered_post_ids;
            }
        } else {
            $post_data['post__in'] = array(0);
        }

        return $post_data;
    }

    private static function cwp_no_result_found()
    {
        return '<div class="cwp-empty-search"><img class="cwp-empty-search-img" src="' . esc_url(CWP_PLUGIN_URI . 'cube/assets/frontend/images/no-result.png') . '" alt=""><h2>' . esc_html__('No Results Found', 'cubewp-framework') . '</h2><p>' . esc_html__('There are no results matching your search.', 'cubewp-framework') . '</p></div>';
    }
}

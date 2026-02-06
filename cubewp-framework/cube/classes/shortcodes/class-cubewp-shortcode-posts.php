<?php

/**
 * CubeWp Posts Shortcode.
 *
 * @version 1.0
 * @package cubewp/cube/classes
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

defined('ABSPATH') || exit;

/**
 * CubeWp Shortcode Posts.
 *
 * @class CubeWp_Shortcode_Posts
 */
class CubeWp_Shortcode_Posts
{

	public function __construct()
	{
		add_shortcode('cubewp_shortcode_posts', array($this, 'cubewp_shortcode_posts_callback'));
		add_filter('cubewp_shortcode_posts_output', array($this, 'cubewp_posts'), 10, 2);
		new CubeWp_Ajax('', 'CubeWp_Shortcode_Posts', 'cubewp_posts_output');
		new CubeWp_Ajax('wp_ajax_nopriv_', 'CubeWp_Shortcode_Posts', 'cubewp_posts_output');
		add_action('wp_enqueue_scripts', [$this, 'cubewp_enqueue_slick_for_elementor'], 999);
		add_action('elementor/editor/after_enqueue_scripts', [$this, 'cubewp_enqueue_slick_for_elementor']);

		// Cache invalidation hooks
		add_action('save_post', array($this, 'clear_posts_cache'), 10, 1);
		add_action('delete_post', array($this, 'clear_posts_cache'), 10, 1);
		add_action('wp_trash_post', array($this, 'clear_posts_cache'), 10, 1);
		add_action('untrash_post', array($this, 'clear_posts_cache'), 10, 1);
		add_action('elementor/document/before_save', array($this, 'clear_elementor_cache'), 10, 2);
		add_action('elementor/document/after_save', array($this, 'clear_elementor_cache'), 10, 2);
	}

	public static function cubewp_posts($output, array $parameters)
	{
		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		if ($cwp_enable_slider) {
			CubeWp_Enqueue::enqueue_style('cubewp-slick');
			CubeWp_Enqueue::enqueue_script('cubewp-slick');
		}

		$load_via_ajax = isset($parameters['load_via_ajax']) ? $parameters['load_via_ajax'] : 'yes';
		if ($load_via_ajax !== 'yes' || cubewp_is_elementor_editing()) {
			return self::cubewp_posts_output($parameters);
		}

		$is_load_more = isset($parameters['load_more']) && $parameters['load_more'] === 'yes';
		if ($is_load_more) {
			CubeWp_Enqueue::enqueue_script('cwp-load-more');
		}

		$slides_to_show = isset($parameters['slides_to_show']) ? intval($parameters['slides_to_show']) : 3;
		$slides_to_show_tablet = isset($parameters['slides_to_show_tablet']) ? intval($parameters['slides_to_show_tablet']) : 2;
		$slides_to_show_mobile = isset($parameters['slides_to_show_mobile']) ? intval($parameters['slides_to_show_mobile']) : 1;
		$processing_grids_per_row = isset($parameters['processing_grids_per_row']) ? intval($parameters['processing_grids_per_row']) : 4;

		$posts_per_row = isset($parameters['posts_per_row']) ? $parameters['posts_per_row'] : 'auto';
		$posts_per_row_tablet = (isset($parameters['posts_per_row_tablet']) && $parameters['posts_per_row_tablet'] !== 'auto') ? $parameters['posts_per_row_tablet'] : 3;
		$posts_per_row_mobile = (isset($parameters['posts_per_row_mobile']) && $parameters['posts_per_row_mobile'] !== 'auto') ? $parameters['posts_per_row_mobile'] : 2;

		if ($cwp_enable_slider) {
			$processing_grids_per_row = $slides_to_show;
			$posts_per_row_tablet = $slides_to_show_tablet;
			$posts_per_row_mobile = $slides_to_show_mobile;
		}

		$processing_grid_count = $processing_grids_per_row;

		if ($posts_per_row !== 'auto' && !$cwp_enable_slider) {
			$processing_grids_per_row = $posts_per_row;
			$processing_grid_count = isset($parameters['number_of_posts']) ? $parameters['number_of_posts'] : 4;
		}

		$unique_id = uniqid('cubewp_posts_');

		// Container start
		$params_with_nonce = $parameters;
		$params_with_nonce['nonce'] = wp_create_nonce('cubewp_posts_output');
		$output .= '<div id="' . esc_attr($unique_id) . '" class="cubewp-ajax-posts-container" data-parameters="' . htmlspecialchars(json_encode($params_with_nonce), ENT_QUOTES, 'UTF-8') . '">
        <div class="cubewp-processing-posts-container" style="display: flex; flex-wrap: wrap; gap: 10px;">';

		for ($i = 0; $i < $processing_grid_count; $i++) {
			$output .=
				'<div class="cwp-processing-post-grid">'
				. '<div class="cwp-processing-post-thumbnail"></div>'
				. '<div class="cwp-processing-post-content"><p></p><p></p><p></p></div>'
				. '</div>';
		}

		$output .= '</div></div>';

		//Dynamic CSS per instance
		$output .= '<style>
        #' . esc_attr($unique_id) . ' .cwp-processing-post-grid {
            flex-basis: calc(100% / ' . esc_attr($processing_grids_per_row) . ' - 10px);
            max-width: calc(100% / ' . esc_attr($processing_grids_per_row) . ' - 10px);
        }';

		if ($posts_per_row_tablet !== 'auto') {
			$output .= '
        @media (max-width: 1024px) {
            #' . esc_attr($unique_id) . ' .cwp-processing-post-grid {
                flex-basis: calc(100% / ' . esc_attr($posts_per_row_tablet) . ' - 10px);
                max-width: calc(100% / ' . esc_attr($posts_per_row_tablet) . ' - 10px);
            }
        }';
		}

		if ($posts_per_row_mobile !== 'auto') {
			$output .= '
        @media (max-width: 767px) {
            #' . esc_attr($unique_id) . ' .cwp-processing-post-grid {
                flex-basis: calc(100% / ' . esc_attr($posts_per_row_mobile) . ' - 10px);
                max-width: calc(100% / ' . esc_attr($posts_per_row_mobile) . ' - 10px);
            }
        }';
		}

		$output .= '</style>';

		// Ajax loader
		$output .= '<script type="text/javascript">
        jQuery(window).on("load", function () {
            setTimeout(function () {
                CubeWpShortcodePostsAjax.loadPosts("#' . esc_attr($unique_id) . '");
            }, 500);
        });
    </script>';

		return $output;
	}


	public static function cubewp_posts_output($parameters)
	{
		if (wp_doing_ajax() && !cubewp_is_elementor_editing()) {
			check_ajax_referer('cubewp_posts_output', 'nonce');
			$parameters = isset($_POST) ? wp_unslash($_POST) : array();
		}

		// Check if cache is enabled
		$cache_enabled = self::is_cache_enabled();

		// Check cache first (skip cache for AJAX requests, load more requests, and Elementor editor)
		$is_ajax_request = wp_doing_ajax();
		$is_load_more = isset($parameters['load_more']) && $parameters['load_more'] === 'yes';
		$skip_cache = $is_ajax_request || $is_load_more || cubewp_is_elementor_editing() || !$cache_enabled;

		$loadbyclick = true;
		if (isset($parameters['sendby']) && $parameters['sendby'] === 'load_more') {
			$loadbyclick = false;
		}

		if (!$skip_cache) {
			$cache_key = self::get_cache_key($parameters);
			$cached_content = self::get_cache($cache_key);

			if ($cached_content !== false) {
				// Return cached content
				if (is_array($cached_content)) {
					return $cached_content['content'] . (isset($cached_content['load_btn']) ? $cached_content['load_btn'] : '');
				}
				return $cached_content;
			}
		}

		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		if ($cwp_enable_slider) {
			$prev_icon = isset($parameters['prev_icon']) ? $parameters['prev_icon'] : 'fas fa-chevron-left';
			$next_icon = isset($parameters['next_icon']) ? $parameters['next_icon'] : 'fas fa-chevron-right';
			$slides_to_show = isset($parameters['slides_to_show']) ? intval($parameters['slides_to_show']) : 3;
			$slides_to_scroll = isset($parameters['slides_to_scroll']) ? intval($parameters['slides_to_scroll']) : 1;
			$slides_to_show_tablet = isset($parameters['slides_to_show_tablet']) ? intval($parameters['slides_to_show_tablet']) : 3;
			$slides_to_show_tablet_portrait = isset($parameters['slides_to_show_tablet_portrait']) ? intval($parameters['slides_to_show_tablet_portrait']) : 2;
			$slides_to_show_mobile = isset($parameters['slides_to_show_mobile']) ? intval($parameters['slides_to_show_mobile']) : 1;
			$slides_to_scroll_tablet = isset($parameters['slides_to_scroll_tablet']) ? intval($parameters['slides_to_scroll_tablet']) : 1;
			$slides_to_scroll_tablet_portrait = isset($parameters['slides_to_scroll_tablet_portrait']) ? intval($parameters['slides_to_scroll_tablet_portrait']) : 1;
			$slides_to_scroll_mobile = isset($parameters['slides_to_scroll_mobile']) ? intval($parameters['slides_to_scroll_mobile']) : 1;
			$autoplay = isset($parameters['autoplay']) ? $parameters['autoplay'] : 'false';
			$autoplay_speed = isset($parameters['autoplay_speed']) ? intval($parameters['autoplay_speed']) : 2000;
			$speed = isset($parameters['speed']) ? intval($parameters['speed']) : 500;
			$infinite = (isset($parameters['infinite']) && $parameters['infinite'] === true) ? 'true' : 'false';
			$fade_effect = (isset($parameters['fade_effect']) && $parameters['fade_effect'] === true) ? 'true' : 'false';
			$variable_width = (isset($parameters['variable_width']) && $parameters['variable_width'] === true) ? 'true' : 'false';
			$custom_arrows = (isset($parameters['custom_arrows']) && $parameters['custom_arrows'] === true) ? 'true' : 'false';
			$enable_progress_bar = (isset($parameters['enable_progress_bar']) && $parameters['enable_progress_bar'] === true) ? 'true' : 'false';
			$custom_dots = (isset($parameters['custom_dots']) && $parameters['custom_dots'] === true) ? 'true' : 'false';
			$enable_wrap_dots_arrows = (isset($parameters['enable_wrap_dots_arrows']) && $parameters['enable_wrap_dots_arrows'] === true) ? 'true' : 'false';
		}
		$promotional_card = $parameters['promotional_card'];
		$promotional_card_list = $parameters['promotional_cards'];

		$args = array(
			'post_type'      => $parameters['post_type'],
			'orderby'        => $parameters['orderby'],
			'order'          => $parameters['order'],
			'page_num'          => 1,
			'meta_query'     => isset($parameters['meta_query']) ? $parameters['meta_query'] : array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		);

		if (isset($parameters['number_of_posts']) && $parameters['number_of_posts'] !== '' && intval($parameters['number_of_posts']) !== -1) {
			$args['posts_per_page'] = intval($parameters['number_of_posts']);
		} elseif (isset($parameters['posts_per_page']) && $parameters['posts_per_page'] !== '') {
			$args['posts_per_page'] = intval($parameters['posts_per_page']);
		}

		if (isset($parameters['page_num'])) {
			$args['page_num'] = $parameters['page_num'];
		}

		$posts_per_row = isset($parameters['posts_per_row']) ? $parameters['posts_per_row'] : 'auto';
		$posts_per_row_tablet = isset($parameters['posts_per_row_tablet']) ? $parameters['posts_per_row_tablet'] : 'auto';
		$posts_per_row_mobile = isset($parameters['posts_per_row_mobile']) ? $parameters['posts_per_row_mobile'] : 'auto';
		if ($cwp_enable_slider) {
			$posts_per_row = $slides_to_show;
			$posts_per_row_tablet = $slides_to_show_tablet;
			$posts_per_row_mobile = $slides_to_show_mobile;
		}

		$posts_row_class = '';
		if ($posts_per_row !== 'auto' && $posts_per_row !== '' && $posts_per_row !== null) {
			$desktop_val = intval($posts_per_row);
			$tablet_val = ($posts_per_row_tablet === 'auto' || $posts_per_row_tablet === '' || !is_numeric($posts_per_row_tablet))
				? $desktop_val
				: intval($posts_per_row_tablet);

			$mobile_val = ($posts_per_row_mobile === 'auto' || $posts_per_row_mobile === '' || !is_numeric($posts_per_row_mobile))
				? $desktop_val
				: intval($posts_per_row_mobile);
			$posts_row_class = sprintf(
				'cubewp-posts-row-%1$s cubewp-posts-row-tablet-%2$s cubewp-posts-row-mobile-%3$s',
				$desktop_val,
				$tablet_val,
				$mobile_val
			);
		}



		$show_boosted_posts = '';
		if (class_exists('CubeWp_Booster_Load')) {
			$show_boosted_posts = $parameters['boosted_only'];
		}
		if (!empty($parameters['post__in'])) {
			$post_ids = is_array($parameters['post__in']) ? $parameters['post__in'] : explode(',', $parameters['post__in']);
			$args['post__in'] = array_filter(array_map('intval', array_map('trim', $post_ids)));
		}

		// Handle related posts query
		$is_related_posts = isset($parameters['posts_by']) && $parameters['posts_by'] === 'related';
		if ($is_related_posts) {
			self::cubewp_handle_related_posts_query($parameters, $args);
		} elseif (isset($parameters['taxonomy']) && ! empty($parameters['taxonomy']) && is_array($parameters['taxonomy'])) {
			// Handle regular taxonomy query (not related posts)
			foreach ($parameters['taxonomy'] as $taxonomy) {
				if (isset($parameters[$taxonomy . '-terms']) && ! empty($parameters[$taxonomy . '-terms'])) {
					$terms_raw = $parameters[$taxonomy . '-terms'];
					$term_ids  = array();
					foreach ($terms_raw as $term_value) {
						if (is_numeric($term_value)) {
							// If it's numeric, treat as term ID
							$term_ids[] = (int) $term_value;
						} else {
							// Otherwise, treat as slug and try to get term ID
							$term = get_term_by('slug', $term_value, $taxonomy);
							if ($term && ! is_wp_error($term)) {
								$term_ids[] = $term->term_id;
							}
						}
					}
					if (! empty($term_ids)) {
						$args[$taxonomy] = implode(',', $term_ids);
					}
				}
			}
		}

		// Handle nearby posts query
		$is_nearby_posts = isset($parameters['posts_by']) && $parameters['posts_by'] === 'nearby';
		if ($is_nearby_posts) {
			self::cubewp_handle_nearby_posts_query($parameters, $args);
		}

		$layout = $parameters['layout'];
		$row_class = 'grid-view';
		if ($layout == 'list') {
			$col_class = 'cwp-col-12';
			$row_class = 'list-view';
		}
		$query = new CubeWp_Query($args);
		$posts = $query->cubewp_post_query();
		$load_btn = $post_markup = '';
		$slider_class = $cwp_enable_slider === 'cubewp-post-slider' ? 'cubewp-post-slider' : '';
		$container_open = '<div class="cubewp-posts-shortcode cwp-row ' . esc_attr($slider_class) . '"';
		if ($cwp_enable_slider) {

			$prev_icon = cubewp_get_svg_content($prev_icon);
			$next_icon = cubewp_get_svg_content($next_icon);

			// Ensure icons are strings (handle any edge cases - should not be needed after fix, but safety check)
			if (is_array($prev_icon)) {
				$prev_icon = isset($prev_icon['value']) ? $prev_icon['value'] : (isset($prev_icon['url']) ? $prev_icon['url'] : '');
			}
			if (is_array($next_icon)) {
				$next_icon = isset($next_icon['value']) ? $next_icon['value'] : (isset($next_icon['url']) ? $next_icon['url'] : '');
			}
			$prev_icon = is_string($prev_icon) ? $prev_icon : '';
			$next_icon = is_string($next_icon) ? $next_icon : '';

			$is_prev_svg = (is_string($prev_icon) && strpos(trim($prev_icon), '<svg') === 0);
			$is_next_svg = (is_string($next_icon) && strpos(trim($next_icon), '<svg') === 0);

			if ($is_prev_svg) {
				$container_open .= " data-prev-arrow-svg='" . esc_attr($prev_icon) . "'";
				$container_open .= ' data-is-prev-svg="true"';
			} else {
				$container_open .= ' data-prev-arrow="' . esc_attr($prev_icon) . '"';
				$container_open .= ' data-is-prev-svg="false"';
			}

			if ($is_next_svg) {
				$container_open .= " data-next-arrow-svg='" . esc_attr($next_icon) . "'";
				$container_open .= ' data-is-next-svg="true"';
			} else {
				$container_open .= ' data-next-arrow="' . esc_attr($next_icon) . '"';
				$container_open .= ' data-is-next-svg="false"';
			}



			$container_open .= ' data-slides-to-show="' . esc_attr($slides_to_show) . '"';
			$container_open .= ' data-slides-to-scroll="' . esc_attr($slides_to_scroll) . '"';
			$container_open .= ' data-slides-to-show-tablet="' . esc_attr($slides_to_show_tablet) . '"';
			$container_open .= ' data-slides-show-tablet-portrait="' . esc_attr($slides_to_show_tablet_portrait) . '"';
			$container_open .= ' data-slides-to-show-mobile="' . esc_attr($slides_to_show_mobile) . '"';
			$container_open .= ' data-slides-to-scroll-tablet="' . esc_attr($slides_to_scroll_tablet) . '"';
			$container_open .= ' data-slides-scroll-tablet-portrait="' . esc_attr($slides_to_scroll_tablet_portrait) . '"';
			$container_open .= ' data-slides-to-scroll-mobile="' . esc_attr($slides_to_scroll_mobile) . '"';
			$container_open .= ' data-autoplay="' . esc_attr($autoplay) . '"';
			$container_open .= ' data-autoplay-speed="' . esc_attr($autoplay_speed) . '"';
			$container_open .= ' data-speed="' . esc_attr($speed) . '"';
			$container_open .= ' data-infinite="' . esc_attr($infinite) . '"';
			$container_open .= ' data-fade="' . esc_attr($fade_effect) . '"';
			$container_open .= ' data-variable-width="' . esc_attr($variable_width) . '"';
			$container_open .= ' data-custom-arrows="' . esc_attr($custom_arrows) . '"';
			$container_open .= ' data-custom-dots="' . esc_attr($custom_dots) . '"';
			$container_open .= ' data-enable-progress-bar="' . esc_attr($enable_progress_bar) . '"';
			$container_open .= ' data-enable-wrapper="' . esc_attr($enable_wrap_dots_arrows) . '"';
		}
		$container_open .= '>';
		$container_close = '</div>';

		$counter        = 1;
		$has_more_posts = false;
		if ($posts->have_posts()) {
			global $post;
			if ($posts_row_class) {
				add_filter('post_class', function ($classes) use ($posts_row_class) {
					$classes[] = $posts_row_class;
					return $classes;
				});
			}
			if ($loadbyclick) {
				$post_markup = $container_open;
			} else {
				$post_markup = '';
			}
			$promotional_cards = [];
			if ($promotional_card && !empty($promotional_card_list) && is_array($promotional_card_list)) {
				foreach ($promotional_card_list as $promotional_card) {
					// Check required keys exist and are valid
					if (isset($promotional_card['cubewp_promotional_card_option'])) {
						$option = $promotional_card['cubewp_promotional_card_option'];
						$width = $promotional_card['cubewp_promotional_card_width']['size'] . $promotional_card['cubewp_promotional_card_width']['unit'];
						$position = $promotional_card['cubewp_promotional_card_position'];

						$promotional_cards[$position] = [
							'option' => $option,
							'width' => $width,
						];
					}
				}
			}
			if ($show_boosted_posts == 'yes') {
				if (class_exists('CubeWp_Booster_Load')) {
					while ($posts->have_posts()): $posts->the_post();
						$post_type = get_post_type(get_the_ID());
						$style = isset($parameters['card_style'][$post_type]) ? $parameters['card_style'][$post_type] : '';
						if (function_exists('is_boosted')) {
							if (is_boosted(get_the_ID())) {
								if ($promotional_card && isset($promotional_cards[$counter]) && !empty($promotional_cards[$counter])) {
									$promotional_cardID =  $promotional_cards[$counter]['option'];
									$width = $promotional_cards[$counter]['width'];
									$post_markup .= cubewp_promotional_card_output($promotional_cardID, $width);
								}
								$counter++;
								$post_markup .= CubeWp_frontend_grid_HTML(get_the_ID(), '', $style);
							}
						}
					endwhile;
				}
			} else {
				while ($posts->have_posts()): $posts->the_post();
					$post_type = get_post_type(get_the_ID());
					$style = isset($parameters['card_style'][$post_type]) ? $parameters['card_style'][$post_type] : '';
					if ($promotional_card && isset($promotional_cards[$counter]) && !empty($promotional_cards[$counter])) {
						$promotional_cardID =  $promotional_cards[$counter]['option'];
						$width = $promotional_cards[$counter]['width'];
						$post_markup .= cubewp_promotional_card_output($promotional_cardID, $width);
					}
					$counter++;
					$post_markup .= CubeWp_frontend_grid_HTML(get_the_ID(), '', $style);
				endwhile;
			}
			if (isset($parameters['load_more']) && $parameters['load_more'] == 'yes') {

				// Get current page number for pagination check
				$current_page = isset($parameters['page_num']) ? intval($parameters['page_num']) : 1;

				// Check if there are more posts available
				$has_more_posts = $current_page < $posts->max_num_pages;

				// Prepare parameters for next page (for load more button) 
				$parameters['page_num'] = $current_page + 1;
				$dataAttributes = json_encode($parameters);

				CubeWp_Enqueue::enqueue_script('cwp-load-more');

				if ($has_more_posts) {
					$load_btn .= '<div class="cubewp-load-more-conatiner">
						<button class="cubewp-load-more-button" data-attributes="' . htmlspecialchars($dataAttributes, ENT_QUOTES, 'UTF-8') . '">
							' . esc_html__('Load More', 'cubewp-framework') . '
						</button>
					</div>';
				} else {
					// No more posts, keep original parameters
					$next_page_params = $parameters;
				}
			} else {
				// Load more not enabled, keep original parameters
				$next_page_params = $parameters;
			}
			if ($loadbyclick) {
				$post_markup .= $container_close;
			}
			if ($posts_row_class) {
				remove_all_filters('post_class'); // or remove using the closure reference if needed
			}
		} else {
			$post_markup = self::cwp_no_result_found();
		}
		wp_reset_postdata();

		$final_output = $post_markup . $load_btn;

		// Cache the output if not AJAX and not Elementor editor and cache is enabled
		if (!$skip_cache && $cache_enabled) {
			$cache_key = self::get_cache_key($parameters);
			$cache_data = array(
				'content' => $post_markup,
				'load_btn' => $load_btn,
				'has_more_posts' => $has_more_posts,
				'newAttributes' => isset($next_page_params) ? $next_page_params : $parameters
			);
			// Get cache TTL from settings (in hours, convert to seconds)
			$cache_ttl_hours = self::get_cache_ttl_hours();
			self::set_cache($cache_key, $cache_data, $cache_ttl_hours * HOUR_IN_SECONDS);
		}



		if (wp_doing_ajax() && !cubewp_is_elementor_editing()) {
			wp_send_json_success(array('content' => $final_output, 'newAttributes' => $parameters, 'has_more_posts' => $has_more_posts));
		} else {
			return $final_output;
		}
	}

	public static function init()
	{
		$CubeWPClass = __CLASS__;
		new $CubeWPClass;
	}

	public function cubewp_shortcode_posts_callback($parameters)
	{
		$title  = isset($parameters['title']) ? $parameters['title'] : '';
		$output = '<div class="cwp-widget-shortcode">';
		if (! empty($title)) {
			$output .= '<h2 class="cwp-widget-shortcode-heading">' . esc_html($title) . '</h2>';
		}
		if (isset($parameters['load_via_ajax']) && $parameters['load_via_ajax'] === 'yes' && !wp_doing_ajax()) {
			$unique_id = uniqid('cubewp_posts_');
			$params_with_nonce = $parameters;
			$params_with_nonce['nonce'] = wp_create_nonce('cubewp_posts_output');
			$output .= '<div id="' . esc_attr($unique_id) . '" class="cubewp-ajax-posts-container" data-parameters="' . wp_json_encode($params_with_nonce) . '">
                            <div class="cubewp-processing-card">
                                <div class="cubewp-processing-card-inner">
                                    <div class="cubewp-processing-card-icon">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <div class="cubewp-processing-card-text">
                                        Processing...
                                    </div>
                                </div>
                            </div>
                        </div>';
			$output .= '<script type="text/javascript">
						jQuery(window).on("load", function () {
							setTimeout(function () {
								CubeWpShortcodePostsAjax.loadPosts("#' . esc_attr($unique_id) . '");
							}, 1000); // 1000ms = 1 second
						});
                        </script>';
		} else {
			$output .= apply_filters('cubewp_shortcode_posts_output', '', $parameters);
		}
		$output .= '</div>';

		return $output;
	}

	private static function cwp_no_result_found()
	{
		return '<div class="cwp-empty-search"><img class="cwp-empty-search-img" src="' . esc_url(CWP_PLUGIN_URI . 'cube/assets/frontend/images/no-result.png') . '" alt=""><h2>' . esc_html__('No Results Found', 'cubewp-framework') . '</h2><p>' . esc_html__('There are no results matching your search.', 'cubewp-framework') . '</p></div>';
	}

	public function cubewp_enqueue_slick_for_elementor()
	{
		$is_elementor_editor = false;

		// Method 1: Check URL parameters
		if (isset($_GET['action']) && $_GET['action'] === 'elementor') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for Elementor editor state.
			$is_elementor_editor = true;
		}

		// Method 2: Check for elementor-preview parameter
		if (isset($_GET['elementor-preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for Elementor editor state.
			$is_elementor_editor = true;
		}

		// Method 3: Check if Elementor editor is in edit mode
		if (
			class_exists('\Elementor\Plugin') &&
			isset(\Elementor\Plugin::$instance) &&
			\Elementor\Plugin::$instance->editor &&
			\Elementor\Plugin::$instance->editor->is_edit_mode()
		) {
			$is_elementor_editor = true;
		}

		// Enqueue only if in Elementor editor
		if ($is_elementor_editor) {
			CubeWp_Enqueue::enqueue_style('cubewp-slick');
			CubeWp_Enqueue::enqueue_script('cubewp-slick');
		}
	}
	/**
	 * Check if posts cache is enabled from settings
	 * 
	 * @return bool True if cache is enabled
	 */
	private static function is_cache_enabled()
	{
		global $cwpOptions;
		if (empty($cwpOptions)) {
			$cwpOptions = get_option('cwpOptions', array());
		}

		// Default to enabled if not set
		$enabled = isset($cwpOptions['cwp_posts_enable_cache']) ? !empty($cwpOptions['cwp_posts_enable_cache']) : true;

		/**
		 * Filter whether posts caching should be enabled.
		 *
		 * @param bool $enabled
		 */
		return (bool) apply_filters('cubewp/posts/cache_enabled', $enabled);
	}

	/**
	 * Get cache TTL in hours from settings
	 * 
	 * @return int Cache TTL in hours (default: 12)
	 */
	private static function get_cache_ttl_hours()
	{
		global $cwpOptions;
		if (empty($cwpOptions)) {
			$cwpOptions = get_option('cwpOptions', array());
		}

		// Default to 12 hours if not set
		$ttl_hours = isset($cwpOptions['cwp_posts_cache_ttl']) ? intval($cwpOptions['cwp_posts_cache_ttl']) : 12;

		// Ensure minimum of 1 hour
		if ($ttl_hours < 1) {
			$ttl_hours = 12;
		}

		/**
		 * Filter the cache TTL for posts output.
		 *
		 * @param int $ttl_hours Cache TTL in hours
		 */
		return (int) apply_filters('cubewp/posts/cache_ttl_hours', $ttl_hours);
	}

	/**
	 * Generate cache key based on query parameters
	 * 
	 * @param array $parameters Query parameters
	 * @return string Cache key
	 */
	private static function get_cache_key($parameters)
	{
		// Create a unique key based on all relevant parameters
		$key_parts = array(
			'cubewp_posts',
			isset($parameters['post_type']) ? $parameters['post_type'] : 'post',
			isset($parameters['orderby']) ? $parameters['orderby'] : 'date',
			isset($parameters['order']) ? $parameters['order'] : 'DESC',
			isset($parameters['posts_per_page']) ? $parameters['posts_per_page'] : (isset($parameters['number_of_posts']) ? $parameters['number_of_posts'] : 10),
			isset($parameters['page_num']) ? $parameters['page_num'] : 1,
			isset($parameters['post__in']) ? md5(serialize($parameters['post__in'])) : '',
			isset($parameters['taxonomy']) ? md5(serialize($parameters['taxonomy'])) : '',
			isset($parameters['meta_query']) ? md5(serialize($parameters['meta_query'])) : '',
			isset($parameters['layout']) ? $parameters['layout'] : 'grid',
			isset($parameters['card_style']) ? md5(serialize($parameters['card_style'])) : '',
			isset($parameters['posts_by']) ? $parameters['posts_by'] : '',
			isset($parameters['nearby_address_field']) ? $parameters['nearby_address_field'] : '',
			isset($parameters['nearby_radius_unit']) ? $parameters['nearby_radius_unit'] : '',
			isset($parameters['nearby_min_radius']) ? $parameters['nearby_min_radius'] : '',
			isset($parameters['nearby_default_radius']) ? $parameters['nearby_default_radius'] : '',
			isset($parameters['nearby_max_radius']) ? $parameters['nearby_max_radius'] : '',
			isset($parameters['nearby_source_post_id']) ? $parameters['nearby_source_post_id'] : '',
			// Include source post ID in cache key for related posts (since results depend on source post)
			(isset($parameters['posts_by']) && $parameters['posts_by'] === 'related') ? self::get_related_source_post_id($parameters) : '',
		);

		// Include site URL to make cache unique per site
		$key_parts[] = get_site_url();

		// Convert all values to strings to avoid array to string conversion errors
		$key_parts = array_map(function ($value) {
			if (is_array($value) || is_object($value)) {
				return md5(serialize($value));
			}
			return (string) $value;
		}, $key_parts);

		return 'cubewp_posts_' . md5(implode('|', $key_parts));
	}

	/**
	 * Get cached content based on cache type (automatically detects wp_using_ext_object_cache)
	 * 
	 * @param string $cache_key Cache key
	 * @return mixed Cached content or false if not found
	 */
	private static function get_cache($cache_key)
	{
		// Automatically detect if external object cache is available
		if (wp_using_ext_object_cache()) {
			// Use object cache
			// Check cache version for compatibility with older WordPress
			$cache_version_key = 'cubewp_posts_version';
			$cache_version = wp_cache_get($cache_version_key, 'cubewp_posts');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_posts', 0);
			}

			// Include version in cache key for invalidation support
			$versioned_cache_key = $cache_key . '_v' . $cache_version;

			return wp_cache_get($versioned_cache_key, 'cubewp_posts');
		} else {
			// Use SQL cache (transients) when object cache is not available
			return get_transient($cache_key);
		}
	}

	/**
	 * Set cached content based on cache type (automatically detects wp_using_ext_object_cache)
	 * 
	 * @param string $cache_key Cache key
	 * @param mixed $cache_data Data to cache
	 * @param int $expiration Expiration time in seconds
	 */
	private static function set_cache($cache_key, $cache_data, $expiration)
	{
		// Automatically detect if external object cache is available
		if (wp_using_ext_object_cache()) {
			// Use object cache
			// Get cache version for compatibility with older WordPress
			$cache_version_key = 'cubewp_posts_version';
			$cache_version = wp_cache_get($cache_version_key, 'cubewp_posts');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_posts', 0);
			}

			// Include version in cache key for invalidation support
			$versioned_cache_key = $cache_key . '_v' . $cache_version;
			wp_cache_set($versioned_cache_key, $cache_data, 'cubewp_posts', $expiration);
		} else {
			// Use SQL cache (transients) when object cache is not available
			set_transient($cache_key, $cache_data, $expiration);
		}
	}

	/**
	 * Clear cache when post is updated/deleted
	 * Automatically detects cache type using wp_using_ext_object_cache()
	 * 
	 * @param int $post_id Post ID
	 */
	public function clear_posts_cache($post_id)
	{
		// Automatically detect if external object cache is available
		if (wp_using_ext_object_cache()) {
			// Clear object cache
			// WordPress 6.1+ supports wp_cache_flush_group, otherwise we need to track keys
			if (function_exists('wp_cache_flush_group')) {
				// Flush entire cache group (most efficient)
				wp_cache_flush_group('cubewp_posts');
			} else {
				// Fallback: Clear cache by pattern (requires tracking or clearing all)
				// For older WordPress versions, we'll use a cache versioning approach
				$cache_version_key = 'cubewp_posts_version';
				$current_version = wp_cache_get($cache_version_key, 'cubewp_posts');
				if ($current_version === false) {
					$current_version = 1;
				}
				// Increment version to invalidate all caches
				wp_cache_set($cache_version_key, $current_version + 1, 'cubewp_posts', 0);
			}
		} else {
			// Clear SQL cache (transients) when object cache is not available
			global $wpdb;

			// Get all transients with our cache prefix
			$cache_keys = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared -- Controlled internal query to enumerate plugin-specific transient keys for cleanup when object cache is unavailable.
				"SELECT option_name 
				FROM {$wpdb->options} 
				WHERE option_name LIKE '_transient_cubewp_posts_%' 
				OR option_name LIKE '_transient_timeout_cubewp_posts_%'"
			);

			foreach ($cache_keys as $cache_key) {
				// Remove _transient_ or _transient_timeout_ prefix
				$transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $cache_key);
				delete_transient($transient_name);
			}
		}
	}

	/**
	 * Clear cache when Elementor template is updated
	 * 
	 * @param object $document Elementor document
	 * @param array $data Document data
	 */
	public function clear_elementor_cache($document, $data = array())
	{
		// Check if it's a post card template
		$post_id = $document->get_main_id();
		$template_type = get_post_meta($post_id, 'template_type', true);

		if ($template_type === 'postcard') {
			// Clear all post caches when post card template is updated
			$this->clear_posts_cache($post_id);
		}
	}

	/**
	 * Handle related posts query - get source post's terms and find related posts
	 * 
	 * @param array $parameters Query parameters
	 * @param array &$args Query arguments (passed by reference)
	 * @return void
	 */
	private static function cubewp_handle_related_posts_query($parameters, &$args)
	{
		// Get source post ID - check for specific post ID first, then fall back to current post
		$post_types = isset($parameters['post_type']) ? (is_array($parameters['post_type']) ? $parameters['post_type'] : array($parameters['post_type'])) : array();
		$source_post_id = null;

		foreach ($post_types as $post_type) {
			$source_post_id_key = 'related_source_post_id_' . $post_type;
			if (isset($parameters[$source_post_id_key]) && intval($parameters[$source_post_id_key]) > 0) {
				$source_post_id = intval($parameters[$source_post_id_key]);
				break; // Use first found specific post ID
			}
		}

		// If no specific post ID, use current post
		if (!$source_post_id) {
			$source_post_id = get_the_ID();
		}

		if (!$source_post_id) {
			// No source post available, return no results
			$args['post__in'] = array(0);
			return;
		}

		// Get source post's post type
		$source_post_type = get_post_type($source_post_id);

		// Get related posts settings from parameters - check all post types in query
		$related_settings = array();
		foreach ($post_types as $post_type) {
			$related_settings_key = 'related_posts_' . $post_type;
			if (isset($parameters[$related_settings_key]) && !empty($parameters[$related_settings_key])) {
				$related_settings = array_merge($related_settings, $parameters[$related_settings_key]);
			}
		}

		if (empty($related_settings)) {
			// No related settings, return no results
			$args['post__in'] = array(0);
			return;
		}

		$taxonomies_to_query = array();
		$matching_term_ids = array();
		$tax_post_ids = array();

		// Collect terms from all selected taxonomies
		foreach ($related_settings as $related_setting) {
			if (isset($related_setting['related_taxonomy'])) {
				$taxonomy = $related_setting['related_taxonomy'];

				// Get source post's terms for this taxonomy
				$source_post_terms = wp_get_post_terms($source_post_id, $taxonomy, array('fields' => 'ids'));
				if (!is_wp_error($source_post_terms) && !empty($source_post_terms)) {
					// Use all source post's terms for this taxonomy
					$matching_terms = $source_post_terms;

					if (!empty($matching_terms)) {
						// Add to matching term IDs array
						$matching_term_ids = array_merge($matching_term_ids, $matching_terms);

						// Store taxonomy for query
						$taxonomies_to_query[] = array(
							'taxonomy' => $taxonomy,
							'field' => 'term_id',
							'terms' => $matching_terms
						);
					}
				}
			}
		}

		// Query for posts with matching terms
		if (empty($taxonomies_to_query) || empty($matching_term_ids)) {
			// No matching terms found
			$args['post__in'] = array(0);
			return;
		}

		// Remove duplicate term IDs
		$matching_term_ids = array_unique($matching_term_ids);

		// Set up tax query
		if (count($taxonomies_to_query) > 1) {
			$taxonomies_to_query['relation'] = 'OR';
		}

		$tax_args = array(
			'post_type' => $parameters['post_type'],
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'post__not_in' => array($source_post_id), // Exclude source post
			'tax_query' => $taxonomies_to_query
		);

		$tax_query = new WP_Query($tax_args);

		if ($tax_query->have_posts()) {
			$tax_post_ids = $tax_query->posts;
		}

		wp_reset_postdata();

		// Set taxonomies for query
		if (!empty($tax_post_ids)) {
			// Exclude the source post from results
			$tax_post_ids = array_diff($tax_post_ids, array($source_post_id));

			if (!empty($tax_post_ids)) {
				$args['post__in'] = $tax_post_ids;
				// Order by relevance (posts with more matching terms first)
				if (count($tax_post_ids) > 1) {
					$args['orderby'] = 'post__in';
				}
			} else {
				$args['post__in'] = array(0);
			}
		} else {
			// No related posts found
			$args['post__in'] = array(0);
		}
	}

	/**
	 * Handle nearby posts query - using CubeWP's proximity SQL approach (same as q_type_google)
	 * 
	 * @param array $parameters Query parameters
	 * @param array &$args Query arguments (passed by reference)
	 * @return void
	 */
	private static function cubewp_handle_nearby_posts_query($parameters, &$args)
	{
		// Get the address field name from parameters (set by widget)
		$nearby_address_field = isset($parameters['nearby_address_field']) ? $parameters['nearby_address_field'] : '';

		if (empty($nearby_address_field)) {
			// If nearby posts is selected but no address field configured, return no results
			$args['post__in'] = array(0);
			return;
		}

		$meta_key = $nearby_address_field;
		$field_indicator = isset($parameters[$meta_key]) ? $parameters[$meta_key] : '';

		if (empty($field_indicator)) {
			// If nearby posts is selected but field indicator not set, return no results
			$args['post__in'] = array(0);
			return;
		}

		// Get lat/lng from parameters (set by widget as $args[$meta_key.'_lat'] and $args[$meta_key.'_lng'])
		$lat = isset($parameters[$meta_key . '_lat']) ? floatval($parameters[$meta_key . '_lat']) : '';
		$lng = isset($parameters[$meta_key . '_lng']) ? floatval($parameters[$meta_key . '_lng']) : '';

		// Get range from parameters, fallback to default radius
		$range = isset($parameters[$meta_key . '_range']) ? floatval($parameters[$meta_key . '_range']) : '';
		if (empty($range)) {
			$range = isset($parameters['nearby_default_radius']) ? floatval($parameters['nearby_default_radius']) : 10;
		}

		// Get radius unit from parameters (set by widget)
		$radius_unit = isset($parameters['nearby_radius_unit']) ? $parameters['nearby_radius_unit'] : 'km';

		if (empty($lat) || empty($lng)) {
			// No valid coordinates, return no results
			$args['post__in'] = array(0);
			return;
		}

		// Use CubeWP's proximity SQL function (same as q_type_google)
		// Get nearby post IDs using proximity SQL
		$nearby_post_ids = cubewp_get_nearby_post_ids($meta_key . '_lat', $meta_key . '_lng', $lat, $lng, $radius_unit, $range, array());
		
		// Ensure it's an array
		if (!is_array($nearby_post_ids)) {
			$nearby_post_ids = array();
		}

		// Filter the main query by nearby post IDs
		if (!empty($nearby_post_ids)) {
			// Merge with existing post__in if any
			if (isset($args['post__in']) && !empty($args['post__in'])) {
				$args['post__in'] = array_intersect($args['post__in'], $nearby_post_ids);
			} else {
				$args['post__in'] = $nearby_post_ids;
			}
			
			// If no posts match after intersection, set to empty array to return no results
			if (empty($args['post__in'])) {
				$args['post__in'] = array(0); // Force no results
			}
		} else {
			// No nearby posts found, return no results
			$args['post__in'] = array(0); // Force no results
		}
	}

	/**
	 * Get source post ID for related posts
	 * 
	 * @param array $parameters Query parameters
	 * @return int Source post ID or 0
	 */
	private static function get_related_source_post_id($parameters)
	{
		$post_types = isset($parameters['post_type']) ? (is_array($parameters['post_type']) ? $parameters['post_type'] : array($parameters['post_type'])) : array();

		foreach ($post_types as $post_type) {
			$source_post_id_key = 'related_source_post_id_' . $post_type;
			if (isset($parameters[$source_post_id_key]) && intval($parameters[$source_post_id_key]) > 0) {
				return intval($parameters[$source_post_id_key]);
			}
		}

		// Fall back to current post ID
		return get_the_ID();
	}
}

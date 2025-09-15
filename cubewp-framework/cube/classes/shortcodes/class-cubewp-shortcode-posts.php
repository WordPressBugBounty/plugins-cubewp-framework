<?php
defined('ABSPATH') || exit;

/**
 * CubeWP Posts Shortcode.
 *
 * @class CubeWp_Frontend_Posts_Shortcode
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
	}

	public static function cubewp_posts($output, array $parameters)
	{
		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		if ($cwp_enable_slider) {
			CubeWp_Enqueue::enqueue_style('cubewp-slick');
			CubeWp_Enqueue::enqueue_script('cubewp-slick');
		}
		if (cubewp_is_elementor_editing()) {
			return self::cubewp_posts_output($parameters);
		}

		$slides_to_show = isset($parameters['slides_to_show']) ? intval($parameters['slides_to_show']) : 3;
		$slides_to_show_tablet = isset($parameters['slides_to_show_tablet']) ? intval($parameters['slides_to_show_tablet']) : 2;
		$slides_to_show_mobile = isset($parameters['slides_to_show_mobile']) ? intval($parameters['slides_to_show_mobile']) : 1;
		$processing_grids_per_row = isset($parameters['processing_grids_per_row']) ? intval($parameters['processing_grids_per_row']) : 4;

		$posts_per_row = isset($parameters['posts_per_row']) ? $parameters['posts_per_row'] : 'auto';
		$posts_per_row_tablet = (isset($parameters['posts_per_row_tablet']) && $parameters['posts_per_row_tablet'] !== 'auto') ? $parameters['posts_per_row_tablet'] : 3;
		$posts_per_row_mobile = (isset($parameters['posts_per_row_mobile']) && $parameters['posts_per_row_mobile'] !== 'auto') ? $parameters['posts_per_row_mobile'] : 2;
		
		if($cwp_enable_slider){
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
		$output .= '<div id="' . esc_attr($unique_id) . '" class="cubewp-ajax-posts-container" data-parameters="' . htmlspecialchars(json_encode($parameters), ENT_QUOTES, 'UTF-8') . '">
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
		if (wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'cubewp_posts_output' && !cubewp_is_elementor_editing()) {
			$parameters = $_POST;
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
			$infinite = (isset($parameters['infinite']) && $parameters['infinite'] === 'true') ? 'true' : 'false';
			$fade_effect = (isset($parameters['fade_effect']) && $parameters['fade_effect'] === 'true') ? 'true' : 'false';
			$variable_width = (isset($parameters['variable_width']) && $parameters['variable_width'] === 'true') ? 'true' : 'false';
			$custom_arrows = (isset($parameters['custom_arrows']) && $parameters['custom_arrows'] === 'true') ? 'true' : 'false';
			$enable_progress_bar = (isset($parameters['enable_progress_bar']) && $parameters['enable_progress_bar'] === 'true') ? 'true' : 'false';
			$custom_dots = (isset($parameters['custom_dots']) && $parameters['custom_dots'] === 'true') ? 'true' : 'false';
			$enable_wrap_dots_arrows = (isset($parameters['enable_wrap_dots_arrows']) && $parameters['enable_wrap_dots_arrows'] === 'true') ? 'true' : 'false';
		}
		$promotional_card = $parameters['promotional_card'];
		$promotional_card_list = $parameters['promotional_cards'];

		$args = array(
			'post_type'      => $parameters['post_type'],
			'orderby'        => $parameters['orderby'],
			'order'          => $parameters['order'],
			'page_num'          => 1,
			'meta_query'     => isset($parameters['meta_query']) ? $parameters['meta_query'] : array(),
		);

		if (isset($parameters['number_of_posts']) && $parameters['number_of_posts'] !== '') {
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

		if (isset($parameters['taxonomy']) && ! empty($parameters['taxonomy']) && is_array($parameters['taxonomy'])) {
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

			$prev_icon = self::cubewp_get_svg_content($prev_icon);
			$next_icon = self::cubewp_get_svg_content($next_icon);

			$is_prev_svg = strpos(trim($prev_icon), '<svg') === 0;
			$is_next_svg = strpos(trim($next_icon), '<svg') === 0;

			if ($is_prev_svg) {

				$container_open .= " data-prev-arrow-svg='" . $prev_icon . "'";
				$container_open .= ' data-is-prev-svg="true"';
			} else {
				$container_open .= ' data-prev-arrow="' . esc_attr($prev_icon) . '"';
				$container_open .= ' data-is-prev-svg="false"';
			}

			if ($is_next_svg) {
				$container_open .= " data-next-arrow-svg='" . $next_icon . "'";
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
			if ($posts_row_class) {
				add_filter('post_class', function ($classes) use ($posts_row_class) {
					$classes[] = $posts_row_class;
					return $classes;
				});
			}
			$post_markup = $container_open;
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
				if (isset($parameters['page_num'])) {
					$parameters['page_num'] = $parameters['page_num'] + 1;
				} else {
					$parameters['page_num'] = 2;
				}
				$has_more_posts = $args['page_num'] < $posts->max_num_pages;
				$dataAttributes = json_encode($parameters);
				CubeWp_Enqueue::enqueue_script('cwp-load-more');


				$load_btn .= '<div class="cubewp-load-more-conatiner">
					<button class="cubewp-load-more-button" data-attributes="' . htmlspecialchars($dataAttributes, ENT_QUOTES, 'UTF-8') . '">
						' . esc_html__('Load More', 'cubewp-framework') . '
					</button>
				</div>';
			}
			$post_markup .= $container_close;
			if ($posts_row_class) {
				remove_all_filters('post_class'); // or remove using the closure reference if needed
			}
		} else {
			$post_markup = self::cwp_no_result_found();
		}
		wp_reset_query();

		if (wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'cubewp_posts_output' && !cubewp_is_elementor_editing()) {
			wp_send_json_success(array('content' => $post_markup, 'newAttributes' => $parameters, 'has_more_posts' => $has_more_posts));
		} else {
			return $post_markup . $load_btn;
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
			$output .= '<div id="' . esc_attr($unique_id) . '" class="cubewp-ajax-posts-container" data-parameters="' . wp_json_encode($parameters) . '">
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

	private static function cubewp_get_svg_content($icon)
	{
		// If icon is array with 'url', fetch the content
		if (is_array($icon) && isset($icon['url'])) {
			$response = wp_safe_remote_get($icon['url']);
			if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
				return wp_remote_retrieve_body($response);
			}
			return ''; // fallback if fetch fails
		}

		// If icon is string, return it
		if (is_string($icon)) {
			return $icon;
		}

		return ''; // fallback
	}

	public function cubewp_enqueue_slick_for_elementor()
	{
		$is_elementor_editor = false;

		// Method 1: Check URL parameters
		if (isset($_GET['action']) && $_GET['action'] === 'elementor') {
			$is_elementor_editor = true;
		}

		// Method 2: Check for elementor-preview parameter
		if (isset($_GET['elementor-preview'])) {
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
}

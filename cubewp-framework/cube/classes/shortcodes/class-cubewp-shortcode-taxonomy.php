<?php
defined('ABSPATH') || exit;

/**
 * CubeWP Taxonomy Terms Shortcode.
 *
 * @class CubeWp_Frontend_Taxonomy_Shortcode
 */
class CubeWp_Shortcode_Taxonomy
{
	public function __construct()
	{
		add_shortcode('cubewp_shortcode_taxonomy', array($this, 'cubewp_shortcode_taxonomy_callback'));
		add_filter('cubewp_shortcode_taxonomy_output', array($this, 'cubewp_taxonomy_output'), 10, 2);

		// Cache invalidation hooks
		add_action('created_term', array($this, 'clear_taxonomy_cache'), 10, 1);
		add_action('edited_term', array($this, 'clear_taxonomy_cache'), 10, 1);
		add_action('delete_term', array($this, 'clear_taxonomy_cache'), 10, 1);
		add_action('elementor/document/before_save', array($this, 'clear_elementor_termcard_cache'), 10, 2);
		add_action('elementor/document/after_save', array($this, 'clear_elementor_termcard_cache'), 10, 2);
		// When cache is re-enabled in settings, clear old cache so next load builds and saves fresh data
		add_action('update_option_cwpOptions', array($this, 'clear_taxonomy_cache_when_reenabled'), 10, 3);
	}

	public static function cubewp_taxonomy_output($output, $parameters = array())
	{
		if (empty($parameters) || count($parameters) == 0) {
			return $output;
		}

		$cache_enabled = self::is_taxonomy_cache_enabled();
		$skip_cache = cubewp_is_elementor_editing() || !is_singular() || !$cache_enabled;
	
		if (!$skip_cache) {
			$cache_key = self::get_taxonomy_cache_key($parameters);
			$cached_content = self::get_taxonomy_cache($cache_key);
			
			if ($cached_content !== false) {
				return $cached_content;
			}
		}

		$output_style = isset($parameters['output_style']) ? $parameters['output_style'] : 'boxed_view';
		$result = '';

		if (strpos($output_style, '_vp_elmentor_term_') !== 0) {
			wp_enqueue_style('cwp-taxonomy-shortcode');
			$taxonomy        = isset($parameters['taxonomy']) ? $parameters['taxonomy'] : '';
			if (empty($taxonomy)) {
				return $output;
			}

			$terms_per_page  = $parameters['terms_per_page'];
			$_child_terms    = $parameters['child_terms'];
			$_hide_empty     = $parameters['hide_empty'];
			$icon_media_name = isset($parameters['icon_media_name']) ? $parameters['icon_media_name'] : '';
			$terms_box_color = isset($parameters['terms_box_color']) ? $parameters['terms_box_color'] : array();
			$child_terms     = false;
			$hide_empty      = false;
			if ($_child_terms == 'yes') {
				$child_terms = true;
			}
			if ($_hide_empty == 'yes') {
				$hide_empty = true;
			}
			
			// Check if we should get terms from associated post
			$get_terms_from_post = isset($parameters['get_terms_from_post']) ? $parameters['get_terms_from_post'] : 'no';
			$post_source = isset($parameters['post_source']) ? $parameters['post_source'] : 'current';
			$specific_post_id = isset($parameters['specific_post_id']) ? intval($parameters['specific_post_id']) : 0;

			// Terms source: all, by IDs, or by slugs
			$terms_source    = isset($parameters['terms_source']) ? $parameters['terms_source'] : 'all';
			$term_ids_input  = isset($parameters['term_ids']) ? trim($parameters['term_ids']) : '';
			$term_slugs_input = isset($parameters['term_slugs']) ? trim($parameters['term_slugs']) : '';
			$orderby = isset($parameters['orderby']) ? $parameters['orderby'] : 'name';
			$order = isset($parameters['order']) && in_array(strtoupper($parameters['order']), array('ASC', 'DESC'), true) ? strtoupper($parameters['order']) : 'ASC';
			
			// Initialize term IDs and term slugs arrays
			$term_ids  = array();
			$term_slugs = array();
			
			if ($terms_source === 'by_ids' && $term_ids_input !== '') {
				// Parse comma-separated term IDs
				$term_ids = array_map('intval', array_filter(array_map('trim', explode(',', $term_ids_input))));
				$term_ids = array_unique(array_filter($term_ids));
			}
			if ($terms_source === 'by_slugs' && $term_slugs_input !== '') {
				// Parse comma-separated term slugs
				$term_slugs = array_map('trim', array_filter(explode(',', $term_slugs_input)));
				$term_slugs = array_unique(array_filter($term_slugs));
			}
			
			if (empty($term_ids) && empty($term_slugs) && $get_terms_from_post === 'yes') {
				// Get post ID based on source
				$post_id = 0;
				
				if ($post_source === 'current') {
					// Get current post ID
					$post_id = get_the_ID();
					if (cubewp_is_elementor_editing()) {
                        $post_id = cubewp_get_elementor_preview_post_id();
                    }
				} elseif ($post_source === 'specific' && $specific_post_id > 0) {
					// Get specific post ID
					$post_id = $specific_post_id;
				}
				
				// If we have a valid post ID, get its terms
				if ($post_id > 0) {
					$post_terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
					if (!is_wp_error($post_terms) && !empty($post_terms)) {
						$term_ids = $post_terms;
					}
				}
			}
			
			// Build query arguments
			$args  = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => $hide_empty,
				'parent'     => 0,
				'orderby'    => $orderby,
				'order'      => $order,
			);
			
			// If showing specific term IDs (by_ids or from post), use include; if by_slugs, use slug
			$fallback_if_no_terms = false;
			if (!empty($term_ids)) {
				$args['include'] = $term_ids;
				unset($args['parent']); // So all included IDs are returned regardless of hierarchy
				if ($terms_source === 'by_ids') {
					$fallback_if_no_terms = true; // When by_ids yields no terms (e.g. demo import), fall back to "all" by other controls
				}
			} elseif (!empty($term_slugs)) {
				$args['slug'] = $term_slugs;
				unset($args['parent']); // So all matching slugs are returned regardless of hierarchy
			} else {
				// Post-associated terms requested but post has no terms → return nothing
				if ($get_terms_from_post === 'yes') {
					$terms = array();
				} else {
					// Original logic - get all terms
					$args['parent'] = $child_terms ? '' : 0;
				}
			}
			
			// Add terms per page limit
			if ($terms_per_page > 0) {
				$args['number'] = $terms_per_page;
			}
			
			if (!isset($terms)) {
				$terms = get_terms($args);
				// When source is by_ids and no terms found (e.g. demo import – IDs don't match), fall back to terms by other controls
				if ($fallback_if_no_terms && (is_wp_error($terms) || empty($terms))) {
					unset($args['include']);
					$args['parent'] = $child_terms ? '' : 0;
					$terms = get_terms($args);
				}
			}

			// Slider functionality
			$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
			if ($cwp_enable_slider == 'yes') {
				CubeWp_Enqueue::enqueue_style('cubewp-slick');
				CubeWp_Enqueue::enqueue_script('cubewp-slick');
			}

			$slider_class = '';
			$container_attrs = '';
			if ($cwp_enable_slider == 'yes' && !empty($terms) && is_array($terms)) {
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
				$autoplay = (isset($parameters['autoplay']) && $parameters['autoplay'] === 'yes') ? 'true' : 'false';
				$autoplay_speed = isset($parameters['autoplay_speed']) ? intval($parameters['autoplay_speed']) : 2000;
				$speed = isset($parameters['speed']) ? intval($parameters['speed']) : 500;
				$infinite = (isset($parameters['infinite']) && $parameters['infinite'] === 'yes') ? 'true' : 'false';
				$fade_effect = (isset($parameters['fade_effect']) && $parameters['fade_effect'] === 'yes') ? 'true' : 'false';
				$variable_width = (isset($parameters['variable_width']) && $parameters['variable_width'] === 'yes') ? 'true' : 'false';
				$custom_arrows = (isset($parameters['custom_arrows']) && $parameters['custom_arrows'] === 'yes') ? 'true' : 'false';
				$enable_progress_bar = (isset($parameters['enable_progress_bar']) && $parameters['enable_progress_bar'] === 'yes') ? 'true' : 'false';
				$custom_dots = (isset($parameters['custom_dots']) && $parameters['custom_dots'] === 'yes') ? 'true' : 'false';
				$enable_wrap_dots_arrows = (isset($parameters['enable_wrap_dots_arrows']) && $parameters['enable_wrap_dots_arrows'] === 'yes') ? 'true' : 'false';
				$slider_device_option = isset($parameters['slider_device_option']) ? $parameters['slider_device_option'] : 'all_devices';

				// Check if slider should be disabled based on term count
				if (wp_is_mobile() && count($terms) <= $slides_to_show_mobile) {
					$cwp_enable_slider = 'no';
				} elseif (!wp_is_mobile() && count($terms) <= $slides_to_show) {
					$cwp_enable_slider = 'no';
				}

				if ($cwp_enable_slider === 'yes') {
					$slider_class = 'cubewp-term-slider';
					$prev_icon = cubewp_get_svg_content($prev_icon);
					$next_icon = cubewp_get_svg_content($next_icon);

					// Ensure icons are strings (handle any edge cases)
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

					$container_attrs = '';
					if ($is_prev_svg) {
						$container_attrs .= " data-prev-arrow-svg='" . esc_attr($prev_icon) . "'";
						$container_attrs .= ' data-is-prev-svg="true"';
					} else {
						$container_attrs .= ' data-prev-arrow="' . esc_attr($prev_icon) . '"';
						$container_attrs .= ' data-is-prev-svg="false"';
					}

					if ($is_next_svg) {
						$container_attrs .= " data-next-arrow-svg='" . esc_attr($next_icon) . "'";
						$container_attrs .= ' data-is-next-svg="true"';
					} else {
						$container_attrs .= ' data-next-arrow="' . esc_attr($next_icon) . '"';
						$container_attrs .= ' data-is-next-svg="false"';
					}

					$container_attrs .= ' data-slides-to-show="' . esc_attr($slides_to_show) . '"';
					$container_attrs .= ' data-slides-to-scroll="' . esc_attr($slides_to_scroll) . '"';
					$container_attrs .= ' data-slides-to-show-tablet="' . esc_attr($slides_to_show_tablet) . '"';
					$container_attrs .= ' data-slides-show-tablet-portrait="' . esc_attr($slides_to_show_tablet_portrait) . '"';
					$container_attrs .= ' data-slides-to-show-mobile="' . esc_attr($slides_to_show_mobile) . '"';
					$container_attrs .= ' data-slides-to-scroll-tablet="' . esc_attr($slides_to_scroll_tablet) . '"';
					$container_attrs .= ' data-slides-scroll-tablet-portrait="' . esc_attr($slides_to_scroll_tablet_portrait) . '"';
					$container_attrs .= ' data-slides-to-scroll-mobile="' . esc_attr($slides_to_scroll_mobile) . '"';
					$container_attrs .= ' data-autoplay="' . esc_attr($autoplay) . '"';
					$container_attrs .= ' data-autoplay-speed="' . esc_attr($autoplay_speed) . '"';
					$container_attrs .= ' data-speed="' . esc_attr($speed) . '"';
					$container_attrs .= ' data-infinite="' . esc_attr($infinite) . '"';
					$container_attrs .= ' data-fade="' . esc_attr($fade_effect) . '"';
					$container_attrs .= ' data-variable-width="' . esc_attr($variable_width) . '"';
					$container_attrs .= ' data-custom-arrows="' . esc_attr($custom_arrows) . '"';
					$container_attrs .= ' data-custom-dots="' . esc_attr($custom_dots) . '"';
					$container_attrs .= ' data-enable-progress-bar="' . esc_attr($enable_progress_bar) . '"';
					$container_attrs .= ' data-enable-wrapper="' . esc_attr($enable_wrap_dots_arrows) . '"';
					$container_attrs .= ' data-slider-device-option="' . esc_attr($slider_device_option) . '"';
				}
			}

			ob_start();
			if (! empty($terms) && is_array($terms)) {
				$counter = 0;
?>
				<div class="cwp-taxonomy-terms <?php echo esc_attr($slider_class); ?>"<?php echo $container_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php foreach ($terms as $term) {
						$term_id   = $term->term_id;
						$term_name = $term->name;
						if ($output_style == 'boxed_view') {
							$color_count = !empty($terms_box_color) && is_array($terms_box_color) ? count($terms_box_color) : 0;
							$icon_media  = !empty($icon_media_name) ? get_term_meta($term_id, $icon_media_name, true) : '';
							$color = (!empty($terms_box_color) && is_array($terms_box_color) && isset($terms_box_color[$counter]['term_box_color']))
								? sanitize_hex_color($terms_box_color[$counter]['term_box_color'])
								: '#000000';
							$counter++;
							if ($color_count > 0 && $counter >= $color_count) {
								$counter = 0;
							}
					?>
							<div class="cwp-taxonomy-term">
								<div class="cwp-taxonomy-term-box">
									<div class="cwp-taxonomy-term-box-heading"
										style="background-color: <?php echo esc_html($color); ?>">
										<?php
										if (! is_array($icon_media)) {
											if ($icon_media != wp_strip_all_tags($icon_media)) {
												// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												echo cubewp_core_data($icon_media);
											} else if (is_numeric($icon_media)) {
												$icon_media = wp_get_attachment_url($icon_media);
												echo '<img src="' . esc_url($icon_media) . '" alt="' . esc_attr($term_name) . '">
																		<div class="cwp-taxonomy-term-box-heading-overlay" style="background-color: ' . esc_attr($color) . ';"></div>';
											} else {
												echo '<i class="' . esc_attr($icon_media) . '" aria-hidden="true"></i>';
											}
										}
										?>
										<a href="<?php echo esc_url(get_term_link($term_id)); ?>"><?php echo esc_html($term_name); ?></a>
									</div>
									<?php
									if ($child_terms) {
										$term_child_args = array(
											'taxonomy'   => $taxonomy,
											'hide_empty' => $hide_empty,
											'parent'     => $term_id,
										);
										$term_childs     = get_terms($term_child_args);
										if (! empty($term_childs) && is_array($term_childs)) {
									?>
											<ul class="cwp-taxonomy-term-child-terms">
												<?php
												$child_terms_count = count($term_childs);
												$term_counter      = 1;
												foreach ($term_childs as $term_child) {
													$child_term_id   = $term_child->term_id;
													$child_term_name = $term_child->name;
													if ($child_terms_count > 5 && 5 == $term_counter) {
												?>
														<li>
															<a href="#"
																class="cwp-taxonomy-term-child-terms-see-more"
																data-more="<?php esc_html_e("View More", "cubewp-framework"); ?>"
																data-less="<?php esc_html_e("View Less", "cubewp-framework"); ?>"><?php esc_html_e("View More", "cubewp-framework"); ?></a>
														</li>
														<ul class="cwp-taxonomy-term-child-terms-more">
														<?php
													}
														?>
														<li>
															<a href="<?php echo esc_url(get_term_link($child_term_id)); ?>"><?php echo esc_html($child_term_name); ?></a>
														</li>
														<?php
														if ($child_terms_count > 5 && $child_terms_count == $term_counter) {
														?>
														</ul>
												<?php
														}
														$term_counter++;
													}
												?>
											</ul>
									<?php
										}
									}
									?>
								</div>
							</div>
						<?php
						} else if ($output_style == 'list_view') {
						?>
							<div class="cwp-taxonomy-term">
								<div class="cwp-taxonomy-term-list">
									<a href="<?php echo esc_url(get_term_link($term_id)); ?>"><?php echo esc_html($term_name); ?></a>
									<?php
									if ($child_terms) {
										$term_child_args = array(
											'taxonomy'   => $taxonomy,
											'hide_empty' => $hide_empty,
											'parent'     => $term_id,
										);
										$term_childs     = get_terms($term_child_args);
										if (! empty($term_childs) && is_array($term_childs)) {
									?>
											<ul><?php
												foreach ($term_childs as $term_child) {
													$child_term_id   = $term_child->term_id;
													$child_term_name = $term_child->name;
												?>
													<li>
														<a href="<?php echo esc_url(get_term_link($child_term_id)); ?>"><?php echo esc_html($child_term_name); ?></a>
													</li>
												<?php
												}
												?>
											</ul><?php
												}
											}
													?>
								</div>
							</div>
					<?php
						}
					} ?>
				</div>
<?php
			}

			$result = ob_get_clean();
		} else {
			$result = self::cubewp_taxonomy_get_term_card_output($parameters, $output);
		}

		if (!$skip_cache && $cache_enabled && $result !== '') {
			$cache_key = self::get_taxonomy_cache_key($parameters);
			$cache_ttl = self::get_taxonomy_cache_ttl_hours() * HOUR_IN_SECONDS;
			self::set_taxonomy_cache($cache_key, $result, $cache_ttl);
		}

		return $result;
	}

	public static function cubewp_taxonomy_get_term_card_output($parameters, $output)
	{
		$output_style = isset($parameters['output_style']) ? $parameters['output_style'] : '';
		$template_id = 0;
		$elementor_key = str_replace('_vp_elmentor_term_', '', $output_style);
		if (ctype_digit((string) $elementor_key)) {
			$template_id = (int) $elementor_key;
		} else {
			// Otherwise treat as slug and resolve to ID
			$maybe_post = get_page_by_path($elementor_key, OBJECT, 'cubewp-tb');
			if ($maybe_post && ! is_wp_error($maybe_post)) {
				$template_id = (int) $maybe_post->ID;
			} else {
				// Fallback resolution by name query
				$by_name = get_posts(array(
					'post_type'      => 'cubewp-tb',
					'name'           => $elementor_key,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				));
				if (! empty($by_name)) {
					$template_id = (int) $by_name[0];
				}
			}
		}
		if ($template_id <= 0) {
			return $output;
		}

		$taxonomy = isset($parameters['taxonomy']) ? $parameters['taxonomy'] : '';
		if (empty($taxonomy)) {
			return $output;
		}

		// Read and normalise parameters
		$terms_per_page = isset($parameters['terms_per_page']) ? $parameters['terms_per_page'] : '0';
		$child_terms    = (isset($parameters['child_terms']) && $parameters['child_terms'] === 'yes');
		$hide_empty     = (isset($parameters['hide_empty']) && $parameters['hide_empty'] === 'yes');

		// Terms source, ordering
		$terms_source     = isset($parameters['terms_source']) ? $parameters['terms_source'] : 'all';
		$term_ids_input   = isset($parameters['term_ids']) ? trim($parameters['term_ids']) : '';
		$term_slugs_input = isset($parameters['term_slugs']) ? trim($parameters['term_slugs']) : '';
		$orderby        = isset($parameters['orderby']) ? $parameters['orderby'] : 'name';
		$order          = isset($parameters['order']) && in_array(strtoupper($parameters['order']), array('ASC', 'DESC'), true) ? strtoupper($parameters['order']) : 'ASC';
		
		// Check if we should get terms from associated post
		$get_terms_from_post = isset($parameters['get_terms_from_post']) ? $parameters['get_terms_from_post'] : 'no';
		$post_source = isset($parameters['post_source']) ? $parameters['post_source'] : 'current';
		$specific_post_id = isset($parameters['specific_post_id']) ? intval($parameters['specific_post_id']) : 0;

		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		if ($cwp_enable_slider) {
			CubeWp_Enqueue::enqueue_style('cubewp-slick');
			CubeWp_Enqueue::enqueue_script('cubewp-slick');
		}
		if ($cwp_enable_slider == 'yes') {
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
			$autoplay = (isset($parameters['autoplay']) && $parameters['autoplay'] === 'yes') ? 'true' : 'false';
			$autoplay_speed = isset($parameters['autoplay_speed']) ? intval($parameters['autoplay_speed']) : 2000;
			$speed = isset($parameters['speed']) ? intval($parameters['speed']) : 500;
			$infinite = (isset($parameters['infinite']) && $parameters['infinite'] === 'yes') ? 'true' : 'false';
			$draggable = (isset($parameters['draggable']) && $parameters['draggable'] === 'yes') ? 'true' : 'false';
			$fade_effect = (isset($parameters['fade_effect']) && $parameters['fade_effect'] === 'yes') ? 'true' : 'false';
			$variable_width = (isset($parameters['variable_width']) && $parameters['variable_width'] === 'yes') ? 'true' : 'false';
			$custom_arrows = (isset($parameters['custom_arrows']) && $parameters['custom_arrows'] === 'yes') ? 'true' : 'false';
			$enable_progress_bar = (isset($parameters['enable_progress_bar']) && $parameters['enable_progress_bar'] === 'yes') ? 'true' : 'false';
			$custom_dots = (isset($parameters['custom_dots']) && $parameters['custom_dots'] === 'yes') ? 'true' : 'false';
			$enable_wrap_dots_arrows = (isset($parameters['enable_wrap_dots_arrows']) && $parameters['enable_wrap_dots_arrows'] === 'yes') ? 'true' : 'false';
			$slider_device_option = isset($parameters['slider_device_option']) ? $parameters['slider_device_option'] : 'all_devices';
		}

		// Build term query args
		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => $hide_empty,
			'orderby'    => $orderby,
			'order'      => $order,
		];
		
		// Initialize term IDs and term slugs arrays (by_ids, by_slugs, or post association)
		$term_ids  = array();
		$term_slugs = array();
		
		if ($terms_source === 'by_ids' && $term_ids_input !== '') {
			$term_ids = array_map('intval', array_filter(array_map('trim', explode(',', $term_ids_input))));
			$term_ids = array_unique(array_filter($term_ids));
		}
		if ($terms_source === 'by_slugs' && $term_slugs_input !== '') {
			$term_slugs = array_map('trim', array_filter(explode(',', $term_slugs_input)));
			$term_slugs = array_unique(array_filter($term_slugs));
		}
		
		if (empty($term_ids) && empty($term_slugs) && $get_terms_from_post === 'yes') {
			// Get post ID based on source
			$post_id = 0;
			
			if ($post_source === 'current') {
				$post_id = get_the_ID();
				if (cubewp_is_elementor_editing() ) {
					$post_id = cubewp_get_elementor_preview_post_id();
				}
			} elseif ($post_source === 'specific' && $specific_post_id > 0) {
				$post_id = $specific_post_id;
			}
			
			if ($post_id > 0) {
				$post_terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
				if (!is_wp_error($post_terms) && !empty($post_terms)) {
					$term_ids = $post_terms;
				}
			}
		}
		
		// If we have specific term IDs (by_ids or from post), use include; if by_slugs, use slug
		$fallback_if_no_terms = false;
		if (!empty($term_ids)) {
			$args['include'] = $term_ids;
			unset($args['parent']); // So all included IDs are returned regardless of hierarchy
			if ($terms_source === 'by_ids') {
				$fallback_if_no_terms = true; // When by_ids yields no terms (e.g. demo import), fall back to "all" by other controls
			}
		} elseif (!empty($term_slugs)) {
			$args['slug'] = $term_slugs;
			unset($args['parent']); // So all matching slugs are returned regardless of hierarchy
		} else {
			// Post-associated terms requested but post has no terms → return nothing
			if ($get_terms_from_post === 'yes') {
				return $output;
			}
			if (! $child_terms) {
				$args['parent'] = 0;
			}
		}

		// number = no limit if '0' (show all)
		if (is_numeric($terms_per_page) && (int) $terms_per_page > 0) {
			$args['number'] = (int) $terms_per_page;
		}

		$terms = get_terms($args);
		if ($fallback_if_no_terms && (is_wp_error($terms) || empty($terms))) {
			unset($args['include']);
			$args['parent'] = $child_terms ? '' : 0;
			$terms = get_terms($args);
		}
		if (empty($terms) || is_wp_error($terms)) {
			return $output;
		}
		if ($cwp_enable_slider === 'yes') {
			if (wp_is_mobile() && count($terms) <= $slides_to_show_mobile) {
				$cwp_enable_slider = 'no';
			} elseif (!wp_is_mobile() && count($terms) <= $slides_to_show) {
				$cwp_enable_slider = 'no';
			}
		}
		$slider_class = $cwp_enable_slider === 'yes' ? 'cubewp-term-slider' : '';
		$container_open = '<div class="cwp-taxonomy-terms cwp-elementor-taxonomy-terms ' . esc_attr($slider_class) . '"';
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
			$container_open .= ' data-draggable="' . esc_attr($draggable) . '"';
			$container_open .= ' data-fade="' . esc_attr($fade_effect) . '"';
			$container_open .= ' data-variable-width="' . esc_attr($variable_width) . '"';
			$container_open .= ' data-custom-arrows="' . esc_attr($custom_arrows) . '"';
			$container_open .= ' data-custom-dots="' . esc_attr($custom_dots) . '"';
			$container_open .= ' data-enable-progress-bar="' . esc_attr($enable_progress_bar) . '"';
			$container_open .= ' data-enable-wrapper="' . esc_attr($enable_wrap_dots_arrows) . '"';
			$container_open .= ' data-slider-device-option="' . esc_attr($slider_device_option) . '"';
		}
		$container_open .= '>';
		$container_close = '</div>';
		$terms_output = '';
		$terms_output .= $container_open;

		static $processed_templates = [];
		$term_settings  = isset($parameters['term_grid_settings']) ? $parameters['term_grid_settings'] : array(); 
		$addClass = '';
		$matchedIndex = null;
		$dynamic_css = "";
		$termCount  = 1;
		foreach ($terms as $term) :
			global $cubewp_term;
			$cubewp_term = $term;
			foreach ($term_settings as $index => $item) {
				if (isset($item['term_position']) && $item['term_position'] == $termCount) {
					$matchedIndex = $index;
					break;
				}
			}
			if ($matchedIndex !== null) {
				$unique_class = 'cwp-term-' . $termCount . '-' . wp_generate_password(4, false, false);
				$margin = isset($term_settings[$matchedIndex]['custom_margin']['top']) ? $term_settings[$matchedIndex]['custom_margin']['top'] . $term_settings[$matchedIndex]['custom_margin']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_margin']['right'] . $term_settings[$matchedIndex]['custom_margin']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_margin']['bottom'] . $term_settings[$matchedIndex]['custom_margin']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_margin']['left'] . $term_settings[$matchedIndex]['custom_margin']['unit'] : '';
				$padding = isset($term_settings[$matchedIndex]['custom_padding']['top']) ? $term_settings[$matchedIndex]['custom_padding']['top'] . $term_settings[$matchedIndex]['custom_padding']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_padding']['right'] . $term_settings[$matchedIndex]['custom_padding']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_padding']['bottom'] . $term_settings[$matchedIndex]['custom_padding']['unit'] . ' ' . $term_settings[$matchedIndex]['custom_padding']['left'] . $term_settings[$matchedIndex]['custom_padding']['unit'] : '';
				$css_rules = "";
				/* COLUMN SPAN */
				if (!empty($term_settings[$matchedIndex]['column_span'])) {
					$col = $term_settings[$matchedIndex]['column_span'];
					$css_rules .= "grid-column: span {$col} / span {$col};";
				}
				/* ROW SPAN */
				if (!empty($term_settings[$matchedIndex]['row_span'])) {
					$row = $term_settings[$matchedIndex]['row_span'];
					$css_rules .= "grid-row: span {$row} / span {$row};";
				}
				/* MARGIN */
				if (!empty($margin)) {
					$css_rules .= "margin: {$margin};";
				}
				/* PADDING */
				if (!empty($padding)) {
					$css_rules .= "padding: {$padding};";
				}
				/* WIDTH */
				if (!empty($term_settings[$matchedIndex]['custom_width']['size'])) {
					$size = $term_settings[$matchedIndex]['custom_width']['size'];
					$unit = $term_settings[$matchedIndex]['custom_width']['unit'] ?? 'px';
					$css_rules .= "width: {$size}{$unit};";
				}
				/* HEIGHT */
				if (!empty($term_settings[$matchedIndex]['custom_height']['size'])) {
					$size = $term_settings[$matchedIndex]['custom_height']['size'];
					$unit = $term_settings[$matchedIndex]['custom_height']['unit'] ?? 'px';
					$css_rules .= "height: {$size}{$unit};";
				}
				/* GRID COLUMN START */
				if (!empty($term_settings[$matchedIndex]['column_span_start'])) {
					$css_rules .= "grid-column-start: {$term_settings[$matchedIndex]['column_span_start']};";
				}
				/* GRID ROW START */
				if (!empty($term_settings[$matchedIndex]['row_span_start'])) {
					$css_rules .= "grid-row-start: {$term_settings[$matchedIndex]['row_span_start']};";
				}
				if (!empty($css_rules)) {
					$dynamic_css .= ".cwp-taxonomy-term-{$unique_class} { {$css_rules} } ";
				}
				$wrapper_attrs = apply_filters('cubewp_term_card_wrapper_attributes', array(), $term);
				$terms_output .= self::build_term_card_wrapper_open('cwp-taxonomy-term cwp-elementor-term-card cwp-taxonomy-term-' . $unique_class, $wrapper_attrs);
			} else {
				$wrapper_attrs = apply_filters('cubewp_term_card_wrapper_attributes', array(), $term);
				$terms_output .= self::build_term_card_wrapper_open('cwp-taxonomy-term cwp-elementor-term-card', $wrapper_attrs);
			}
			if (!in_array($template_id, $processed_templates, true)) {
				ob_start();
				echo CubeWp_Theme_Builder::do_cubewp_theme_builder('termcard', $template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme builder content is safe.
				$terms_output .= ob_get_clean();
				$processed_templates[] = $template_id;
			} else {
				ob_start();
				echo cwp_get_elementor_content_without_styles($template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor content is safe.
				$terms_output .= ob_get_clean();
			}
			$terms_output .= '</div>';
			unset($GLOBALS['cubewp_term']);
			$termCount++;
		endforeach;
		$terms_output .= $container_close;
		if (!empty($dynamic_css)) {
			$terms_output .= "<style>
			@media (min-width: 1024px) {
				" . $dynamic_css . "
			} </style>";
		}
		return $terms_output;
	}

	/**
	 * Build the opening tag for a term card wrapper with optional attributes from filter.
	 *
	 * @param string $class_base Space-separated class names for the wrapper.
	 * @param array  $attrs     Attributes from filter 'cubewp_term_card_wrapper_attributes' (e.g. ['style' => 'background-color: #fff']).
	 * @return string Opening div tag.
	 */
	protected static function build_term_card_wrapper_open($class_base, $attrs = array())
	{
		$attrs = is_array($attrs) ? $attrs : array();
		$class = $class_base;
		if (!empty($attrs['class'])) {
			$class .= ' ' . trim($attrs['class']);
			unset($attrs['class']);
		}
		$html = '<div class="' . esc_attr($class) . '"';
		foreach ($attrs as $key => $value) {
			if ($key === 'style' && is_string($value)) {
				$html .= ' style="' . esc_attr($value) . '"';
			} elseif (is_string($key) && is_string($value)) {
				$html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
			}
		}
		$html .= '>';
		return $html;
	}

	/**
	 * Check if taxonomy cache is enabled from settings.
	 *
	 * @return bool
	 */
	private static function is_taxonomy_cache_enabled()
	{
		$cwpOptions = get_option('cwpOptions', array());
		$enabled = isset($cwpOptions['cwp_taxonomy_enable_cache']) ? !empty($cwpOptions['cwp_taxonomy_enable_cache']) : true;
		return (bool) apply_filters('cubewp/taxonomy/cache_enabled', $enabled);
	}

	/**
	 * Get taxonomy cache TTL in hours from settings.
	 *
	 * @return int
	 */
	private static function get_taxonomy_cache_ttl_hours()
	{
		$cwpOptions = get_option('cwpOptions', array());
		$ttl_hours = isset($cwpOptions['cwp_taxonomy_cache_ttl']) ? intval($cwpOptions['cwp_taxonomy_cache_ttl']) : 12;
		$ttl_hours = max(1, $ttl_hours);
		return (int) apply_filters('cubewp/taxonomy/cache_ttl_hours', $ttl_hours);
	}

	/**
	 * Generate cache key based on taxonomy parameters.
	 *
	 * @param array $parameters
	 * @return string
	 */
	private static function get_taxonomy_cache_key($parameters)
	{
		$key_parts = array(
			'cubewp_taxonomy',
			isset($parameters['taxonomy']) ? $parameters['taxonomy'] : '',
			isset($parameters['output_style']) ? $parameters['output_style'] : 'boxed_view',
			isset($parameters['terms_per_page']) ? $parameters['terms_per_page'] : 0,
			isset($parameters['child_terms']) ? $parameters['child_terms'] : 'no',
			isset($parameters['hide_empty']) ? $parameters['hide_empty'] : 'no',
			isset($parameters['terms_source']) ? $parameters['terms_source'] : 'all',
			isset($parameters['term_ids']) ? $parameters['term_ids'] : '',
			isset($parameters['term_slugs']) ? $parameters['term_slugs'] : '',
			isset($parameters['orderby']) ? $parameters['orderby'] : 'name',
			isset($parameters['order']) ? $parameters['order'] : 'ASC',
			isset($parameters['get_terms_from_post']) ? $parameters['get_terms_from_post'] : 'no',
			isset($parameters['post_source']) ? $parameters['post_source'] : 'current',
			isset($parameters['specific_post_id']) ? intval($parameters['specific_post_id']) : 0,
			isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '',
			get_the_ID(),
			get_site_url(),
		);
		$key_parts = array_map(function ($v) {
			return is_array($v) || is_object($v) ? md5(serialize($v)) : (string) $v;
		}, $key_parts);
		return 'cubewp_taxonomy_' . md5(implode('|', $key_parts));
	}

	/**
	 * Get taxonomy cache.
	 *
	 * @param string $cache_key
	 * @return mixed
	 */
	private static function get_taxonomy_cache($cache_key)
	{
		if (wp_using_ext_object_cache()) {
			$cache_version_key = 'cubewp_taxonomy_version';
			$cache_version = wp_cache_get($cache_version_key, 'cubewp_taxonomy');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_taxonomy', 0);
			}
			return wp_cache_get($cache_key . '_v' . $cache_version, 'cubewp_taxonomy');
		}
		return get_transient($cache_key);
	}

	/**
	 * Set taxonomy cache.
	 *
	 * @param string $cache_key
	 * @param mixed $cache_data
	 * @param int $expiration
	 */
	private static function set_taxonomy_cache($cache_key, $cache_data, $expiration)
	{
		if (wp_using_ext_object_cache()) {
			$cache_version_key = 'cubewp_taxonomy_version';
			$cache_version = wp_cache_get($cache_version_key, 'cubewp_taxonomy');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_taxonomy', 0);
			}
			wp_cache_set($cache_key . '_v' . $cache_version, $cache_data, 'cubewp_taxonomy', $expiration);
		} else {
			set_transient($cache_key, $cache_data, $expiration);
		}
	}

	/**
	 * Clear all taxonomy cache (used on term changes and when cache is re-enabled in settings).
	 */
	public static function flush_taxonomy_cache()
	{
		if (wp_using_ext_object_cache()) {
			if (function_exists('wp_cache_flush_group')) {
				wp_cache_flush_group('cubewp_taxonomy');
			} else {
				$cache_version_key = 'cubewp_taxonomy_version';
				$current = wp_cache_get($cache_version_key, 'cubewp_taxonomy');
				$current = ($current === false) ? 1 : (int) $current;
				wp_cache_set($cache_version_key, $current + 1, 'cubewp_taxonomy', 0);
			}
		} else {
			global $wpdb;
			$cache_keys = $wpdb->get_col(
				"SELECT option_name FROM {$wpdb->options} 
				WHERE option_name LIKE '_transient_cubewp_taxonomy_%' 
				OR option_name LIKE '_transient_timeout_cubewp_taxonomy_%'"
			);
			if (is_array($cache_keys)) {
				foreach ($cache_keys as $cache_key) {
					$transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $cache_key);
					delete_transient($transient_name);
				}
			}
		}
	}

	/**
	 * Clear taxonomy cache on term create/update/delete.
	 *
	 * @param int $term_id
	 */
	public function clear_taxonomy_cache($term_id)
	{
		self::flush_taxonomy_cache();
	}

	/**
	 * When CubeWP options are saved, if taxonomy cache was just turned on, clear old cache
	 * so the next page load builds fresh content and stores it (instead of serving stale cache).
	 *
	 * @param mixed $old_value Previous option value.
	 * @param mixed $value     New option value.
	 * @param string $option   Option name.
	 */
	public function clear_taxonomy_cache_when_reenabled($old_value, $value, $option)
	{
		if (!is_array($value) || empty($value['cwp_taxonomy_enable_cache'])) {
			return;
		}
		$was_enabled = is_array($old_value) && !empty($old_value['cwp_taxonomy_enable_cache']);
		if ($was_enabled) {
			return;
		}
		self::flush_taxonomy_cache();
	}

	/**
	 * Clear taxonomy cache when Elementor term card template is updated.
	 *
	 * @param object $document
	 * @param array $data
	 */
	public function clear_elementor_termcard_cache($document, $data = array())
	{
		$post_id = $document->get_main_id();
		$template_type = get_post_meta($post_id, 'template_type', true);
		if ($template_type === 'termcard') {
			$this->clear_taxonomy_cache($post_id);
		}
	}

	public static function init()
	{
		$CubeWPClass = __CLASS__;
		new $CubeWPClass;
	}

	public function cubewp_shortcode_taxonomy_callback($parameters)
	{
		$title  = isset($parameters['title']) ? sanitize_text_field($parameters['title']) : '';
		$output = '<div class="cwp-widget-shortcode">';

		if (! empty($title)) {
			$output .= '<h2 class="cwp-widget-shortcode-heading">' . esc_html($title) . '</h2>';
		}

		$output .= apply_filters('cubewp_shortcode_taxonomy_output', '', $parameters);
		$output .= '</div>';

		return $output;
	}
}
<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * CubeWP Saved Posts Widget.
 *
 * Elementor widget to display saved posts by post type.
 *
 * @since 1.0.0
 */
class CubeWp_Elementor_Saved_Posts_Widget extends Widget_Base {

	/**
	 * Cached post type options.
	 *
	 * @var array
	 */
	private static $post_types = array();

	public function get_name() {
		return 'cubewp_saved_posts';
	}

	public function get_title() {
		return esc_html__('CubeWP Saved Posts', 'cubewp-framework');
	}

	public function get_icon() {
		return 'eicon-heart';
	}

	public function get_categories() {
		return array('cubewp');
	}

	public function get_keywords() {
		return array('cubewp', 'saved', 'wishlist', 'favorites', 'bookmarks', 'posts');
	}

	protected function register_controls() {
		self::load_post_types();

		$this->start_controls_section(
			'cubewp_saved_posts_settings',
			array(
				'label' => esc_html__('Saved Posts Settings', 'cubewp-framework'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'post_type',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => esc_html__('Post Type', 'cubewp-framework'),
				'options'     => self::$post_types,
				'default'     => 'post',
				'label_block' => true,
			)
		);

		foreach (self::$post_types as $post_type => $label) {
			$card_styles = function_exists('cubewp_post_card_styles') ? cubewp_post_card_styles($post_type) : array();
			if (!empty($card_styles)) {
				$this->add_control(
					$post_type . '_card_style',
					array(
						'type'      => Controls_Manager::SELECT,
						/* translators: %s: post type label */
						'label'     => sprintf(esc_html__('Card Style for %s', 'cubewp-framework'), $label),
						'options'   => $card_styles,
						'default'   => 'default_style',
						'condition' => array(
							'post_type' => $post_type,
						),
					)
				);
			}
		}

		$this->add_control(
			'posts_per_page',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__('Number of Posts to Show', 'cubewp-framework'),
				'options' => array(
					'-1' => esc_html__('All Posts', 'cubewp-framework'),
					'3'  => esc_html__('3 Posts', 'cubewp-framework'),
					'4'  => esc_html__('4 Posts', 'cubewp-framework'),
					'5'  => esc_html__('5 Posts', 'cubewp-framework'),
					'6'  => esc_html__('6 Posts', 'cubewp-framework'),
					'8'  => esc_html__('8 Posts', 'cubewp-framework'),
					'9'  => esc_html__('9 Posts', 'cubewp-framework'),
					'12' => esc_html__('12 Posts', 'cubewp-framework'),
					'15' => esc_html__('15 Posts', 'cubewp-framework'),
					'16' => esc_html__('16 Posts', 'cubewp-framework'),
					'20' => esc_html__('20 Posts', 'cubewp-framework'),
				),
				'default' => '6',
			)
		);

		$this->add_responsive_control(
			'posts_per_row',
			array(
				'label' => esc_html__('Posts Per Row', 'cubewp-framework'),
				'type'  => Controls_Manager::SELECT,
				'device_args' => array(
					\Elementor\Controls_Stack::RESPONSIVE_DESKTOP => array(
						'default' => '3',
						'options' => array(
							'1' => esc_html__('1 Column', 'cubewp-framework'),
							'2' => esc_html__('2 Columns', 'cubewp-framework'),
							'3' => esc_html__('3 Columns', 'cubewp-framework'),
							'4' => esc_html__('4 Columns', 'cubewp-framework'),
							'5' => esc_html__('5 Columns', 'cubewp-framework'),
							'6' => esc_html__('6 Columns', 'cubewp-framework'),
						),
					),
					\Elementor\Controls_Stack::RESPONSIVE_TABLET => array(
						'default' => '2',
						'options' => array(
							'1' => esc_html__('1 Column', 'cubewp-framework'),
							'2' => esc_html__('2 Columns', 'cubewp-framework'),
							'3' => esc_html__('3 Columns', 'cubewp-framework'),
							'4' => esc_html__('4 Columns', 'cubewp-framework'),
							'5' => esc_html__('5 Columns', 'cubewp-framework'),
							'6' => esc_html__('6 Columns', 'cubewp-framework'),
						),
					),
					\Elementor\Controls_Stack::RESPONSIVE_MOBILE => array(
						'default' => '1',
						'options' => array(
							'1' => esc_html__('1 Column', 'cubewp-framework'),
							'2' => esc_html__('2 Columns', 'cubewp-framework'),
							'3' => esc_html__('3 Columns', 'cubewp-framework'),
							'4' => esc_html__('4 Columns', 'cubewp-framework'),
							'5' => esc_html__('5 Columns', 'cubewp-framework'),
							'6' => esc_html__('6 Columns', 'cubewp-framework'),
						),
					),
				),
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Load public post types for control options.
	 */
	private static function load_post_types() {
		$post_types = get_post_types(array('public' => true), 'objects');
		$options    = array();

		foreach ($post_types as $post_type) {
			$options[$post_type->name] = $post_type->label;
		}

		unset($options['elementor_library'], $options['e-landing-page'], $options['attachment']);

		self::$post_types = $options;
	}

	protected function render() {
		$settings            = $this->get_settings_for_display();
		$post_type           = isset($settings['post_type']) ? sanitize_key($settings['post_type']) : 'post';
		$posts_per_page      = isset($settings['posts_per_page']) ? intval($settings['posts_per_page']) : 6;
		$desktop_per_row     = isset($settings['posts_per_row']) ? intval($settings['posts_per_row']) : 3;
		$tablet_per_row      = isset($settings['posts_per_row_tablet']) ? intval($settings['posts_per_row_tablet']) : 2;
		$mobile_per_row      = isset($settings['posts_per_row_mobile']) ? intval($settings['posts_per_row_mobile']) : 1;
		$card_style_key      = $post_type . '_card_style';
		$selected_card_style = isset($settings[$card_style_key]) ? sanitize_text_field($settings[$card_style_key]) : 'default_style';
		$saved_posts         = CubeWp_Saved::cubewp_get_saved_posts();

		if (empty($saved_posts) || !is_array($saved_posts)) {
			$this->render_empty_state();
			return;
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => $posts_per_page,
			'post__in'       => array_map('absint', $saved_posts),
		);

		$query = new WP_Query($args);
		if (!$query->have_posts()) {
			$this->render_empty_state();
			return;
		}

		$row_class = sprintf(
			'cubewp-posts-row-%1$d cubewp-posts-row-tablet-%2$d cubewp-posts-row-mobile-%3$d',
			max(1, $desktop_per_row),
			max(1, $tablet_per_row),
			max(1, $mobile_per_row)
		);

		$post_class_filter = null;
		if (function_exists('CWP')) {
			$post_class_filter = function ($classes) use ($row_class) {
				$classes[] = $row_class;
				return $classes;
			};
			add_filter('post_class', $post_class_filter);
			echo '<div class="cwp-row cubewp-posts-shortcode">';
		} else {
			echo '<ul class="product_list_widget">';
		}

		while ($query->have_posts()) {
			$query->the_post();
			if (function_exists('CWP')) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo CubeWp_frontend_grid_HTML(get_the_ID(), '', $selected_card_style);
			} else {
				printf('<li><a href="%1$s">%2$s</a></li>', esc_url(get_permalink()), esc_html(get_the_title()));
			}
		}

		wp_reset_postdata();

		if (function_exists('CWP')) {
			if ($post_class_filter) {
				remove_filter('post_class', $post_class_filter);
			}
			echo '</div>';
		} else {
			echo '</ul>';
		}
	}

	/**
	 * Render empty state.
	 */
	private function render_empty_state() {
		echo '<div class="cwp-empty-search"><img class="cwp-empty-search-img" src="' . esc_url(CWP_PLUGIN_URI . 'cube/assets/frontend/images/no-result.png') . '" alt=""><h2>' . esc_html__('No Saved Posts Found', 'cubewp-framework') . '</h2><p>' . esc_html__('There are no saved posts found.', 'cubewp-framework') . '</p></div>';
	}
}

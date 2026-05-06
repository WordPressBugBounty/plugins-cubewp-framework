<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor dynamic tag: distance from a source post to the current post (for nearby posts).
 *
 * @package cubewp-framework
 */
class CubeWp_Tag_Nearby_Post_Distance extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'cubewp-nearby-post-distance-tag';
	}

	public function get_title() {
		return esc_html__('Nearby Post Distance', 'cubewp-framework');
	}

	public function get_group() {
		return ['cubewp-single-fields'];
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
		$address_options = array('' => esc_html__('Select address field', 'cubewp-framework'));
		if (function_exists('get_fields_by_type')) {
			$address_options = $address_options + (array) get_fields_by_type(array('google_address'));
		}

		$this->add_control(
			'address_field',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label'       => esc_html__('Address field', 'cubewp-framework'),
				'description' => esc_html__('Google Address field used to measure distance.', 'cubewp-framework'),
				'options'     => $address_options,
				'default'     => '',
			]
		);

		$this->add_control(
			'source_post',
			[
				'type'        => \Elementor\Controls_Manager::SELECT,
				'label'       => esc_html__('Source post', 'cubewp-framework'),
				'description' => esc_html__('Post to measure distance from. "Current post" = post being viewed (e.g. single listing); distance is to the post being displayed in the loop.', 'cubewp-framework'),
				'options'     => [
					'current'  => esc_html__('Current post (e.g. single listing)', 'cubewp-framework'),
					'specific' => esc_html__('Specific post ID', 'cubewp-framework'),
				],
				'default'     => 'current',
			]
		);

		$this->add_control(
			'source_post_id',
			[
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'label'       => esc_html__('Source post ID', 'cubewp-framework'),
				'min'         => 1,
				'condition'   => [
					'source_post' => 'specific',
				],
			]
		);

		$this->add_control(
			'unit',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__('Unit', 'cubewp-framework'),
				'options' => [
					'km'    => esc_html__('Kilometers', 'cubewp-framework'),
					'miles' => esc_html__('Miles', 'cubewp-framework'),
				],
				'default' => 'km',
			]
		);

		$this->add_control(
			'decimal_places',
			[
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'label'   => esc_html__('Decimal places', 'cubewp-framework'),
				'min'     => 0,
				'max'     => 4,
				'default' => 2,
			]
		);

		$this->add_control(
			'suffix',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => esc_html__('Suffix (e.g. " km away")', 'cubewp-framework'),
				'default'     => '',
				'placeholder' => ' km',
			]
		);
	}

	public function render() {
		$address_field = $this->get_settings('address_field');
		if (empty($address_field) || ! function_exists('cubewp_get_distance_between_posts')) {
			return;
		}

		$to_post_id = get_the_ID();
		if (! $to_post_id) {
			return;
		}
		
		$source_post = $this->get_settings('source_post');
		$from_post_id = null;
		if ($source_post === 'specific') {
			$from_post_id = (int) $this->get_settings('source_post_id');
		} else {
			// Current post in context (e.g. single post page, or fallback to global $post)
			$from_post_id = get_queried_object_id();
			if (! $from_post_id || get_post_type($from_post_id) !== get_post_type($to_post_id)) {
				$from_post_id = $to_post_id;
			}
		}

		if (! $from_post_id) {
			return;
		}
		if ($from_post_id === $to_post_id) {
			echo esc_html__('0', 'cubewp-framework');
			return;
		}

		$unit = $this->get_settings('unit');
		$distance = cubewp_get_distance_between_posts($from_post_id, $to_post_id, $address_field, $unit === 'miles' ? 'mi' : 'km');
		if ($distance === null) {
			return;
		}

		$decimals = (int) $this->get_settings('decimal_places');
		$suffix   = $this->get_settings('suffix');
		$output   = number_format_i18n($distance, $decimals) . $suffix;
		echo esc_html($output);
	}
}
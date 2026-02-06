<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Post_Date extends Tag
{

	public function get_name()
	{
		return 'cubewp-post-date-tag';
	}

	public function get_title()
	{
		return esc_html__('Post Date', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-single-fields'];
	}

	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
			Module::POST_META_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return false;
	}

	protected function register_controls()
	{

		$this->add_control(
			'date_type',
			[
				'label'   => esc_html__('Date Type', 'cubewp-framework'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'publish',
				'options' => [
					'publish'  => esc_html__('Publish Date', 'cubewp-framework'),
					'modified' => esc_html__('Modified Date', 'cubewp-framework'),
				],
			]
		);

		$this->add_control(
			'date_format',
			[
				'label'       => esc_html__('Date Format', 'cubewp-framework'),
				'type'        => Controls_Manager::TEXT,
				'default'     => get_option('date_format'),
				'description' => esc_html__('Use PHP date format, e.g. F j, Y or d/m/Y', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'show_relative_time',
			[
				'label' => esc_html__('Show Relative Time (e.g., "2 days ago")', 'cubewp-framework'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'cubewp-framework'),
				'label_off' => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default' => '',
			]
		);
	}

	public function render()
	{
		$settings = $this->get_settings();
		$date_type = ! empty($settings['date_type']) ? $settings['date_type'] : 'publish';
		$format    = ! empty($settings['date_format']) ? $settings['date_format'] : get_option('date_format');
		$relative  = ! empty($settings['show_relative_time']) && $settings['show_relative_time'] === 'yes';

		if (cubewp_is_elementor_editing()) {
			$post_id = cubewp_get_elementor_preview_post_id();
		} else {
			$post_id = get_the_ID();
		}

		if (! $post_id) {
			return;
		}

		// Validate format by checking if date_i18n changes the output
		$test_output = date_i18n($format);
		if ($test_output === $format || empty(trim($test_output))) {
			$format = get_option('date_format');
		}

		// Get the correct timestamp
		if ($date_type === 'modified') {
			$timestamp = get_post_modified_time('U', false, $post_id);
		} else {
			$timestamp = get_post_time('U', false, $post_id);
		}

		if (! $timestamp) {
			return;
		}

		// Handle relative time option
		if ($relative) {
			$time_diff = human_time_diff($timestamp, current_time('timestamp'));
			/* translators: %s: time difference. */
			$date = sprintf(esc_html__('%s ago', 'cubewp-framework'), $time_diff);
		} else {
			$date = date_i18n($format, $timestamp);
		}

		echo esc_html($date);
	}
}

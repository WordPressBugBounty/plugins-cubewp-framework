<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Number extends \Elementor\Core\DynamicTags\Tag
{

	public function get_name()
	{
		return 'cubewp-number-tag';
	}

	public function get_title()
	{
		return esc_html__('Fields type (number)', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-fields'];
	}

	public function get_categories()
	{
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	protected function register_controls()
	{
		$this->add_control(
			'field_type',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Data Source', 'cubewp-framework'),
				'description' => esc_html__(
					'Choose whether to display post data or user data (author, post author, or logged-in user depending on context).',
					'cubewp-framework'
				),
				'options' => [
					'post' => esc_html__('Post Fields', 'cubewp-framework'),
					'user' => esc_html__('User Fields', 'cubewp-framework'),
				],
				'default' => 'post',
			]
		);

		$options = get_fields_by_type(array('number'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select custom field', 'cubewp-framework'),
				'options' => $options,
				'condition' => [
					'field_type' => 'post',
				],
			]
		);

		$user_field_options = get_user_fields_by_type(array('number'));

		$this->add_control(
			'user_selected_user_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select user field', 'cubewp-framework'),
				'options' => $user_field_options,
				'condition' => [
					'field_type' => 'user',
				],
			]
		);
	}

	public function render()
	{

		$field_type = $this->get_settings('field_type');
		$field_type = $field_type ? $field_type : 'post';

		$value = '';

		if ('user' === $field_type) {

			$field = $this->get_settings('user_selected_user_field');
			if (! $field) {
				return;
			}

			$value = get_user_field_value($field);
		} else {

			$field = $this->get_settings('user_selected_field');
			if (! $field) {
				return;
			}

			$value = get_field_value($field);
		}

		// Normalize arrays
		if (is_array($value)) {
			$value = implode(', ', $value);
		}

		/**
		 * Number-only dynamic tag filter
		 */
		if (is_numeric($value)) {
			$value = apply_filters('cubewp/elementor/number/dynamic_tag/value',$value,$this->get_settings(),);
		}

		if ('' === $value || null === $value) {
			return;
		}

		echo esc_html(cubewp_core_data($value));
	}
}

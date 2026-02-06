<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Google_Address extends \Elementor\Core\DynamicTags\Tag
{

	public function get_name()
	{
		return 'cubewp-google_address-tag';
	}

	public function get_title()
	{
		return esc_html__('Fields type (google_address)', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-fields'];
	}

	public function get_categories()
	{
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	protected function register_controls()
	{

		$options = get_fields_by_type(array('google_address'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select custom field', 'cubewp-framework'),
				'options' => $options,
			]
		);
		$this->add_control(
			'google_address_limit_number',
			[
				'type' => \Elementor\Controls_Manager::NUMBER,
				'label' => esc_html__('Google Address Limit Number', 'cubewp-framework'),
				'default' => 1,
				'min' => 1,
				'step' => 1,
				'description' => esc_html__('Enter the number of google addresses to display', 'cubewp-framework'),
			]
		);
	}

	public function render()
	{
		$field = $this->get_settings('user_selected_field');
		$google_address_limit_number = $this->get_settings('google_address_limit_number');

		if (! $field) {
			return;
		}
		$value = get_field_value($field);
		if (is_array($value) && count($value) > 0) {
			$value = $value['address'];
		}
		$title = wp_trim_words($value, $google_address_limit_number, '...');
		echo esc_html(cubewp_core_data($title));
	}
}

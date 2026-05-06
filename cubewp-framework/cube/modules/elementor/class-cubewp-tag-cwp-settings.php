<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Cwp_Settings extends \Elementor\Core\DynamicTags\Data_Tag
{

	public function get_name()
	{
		return 'cubewp-cwp-settings-tag';
	}

	public function get_title()
	{
		return esc_html__('CWP Settings', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-settings-fields'];
	}

	public function get_categories()
	{
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	protected function register_controls()
	{
		$this->add_control(
			'setting_field_type',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Field Type', 'cubewp-framework'),
				'options' => [
					'text' => esc_html__('Text', 'cubewp-framework'),
					'color' => esc_html__('Color', 'cubewp-framework'),
					'select' => esc_html__('Select', 'cubewp-framework'),
					'image' => esc_html__('Image', 'cubewp-framework'),
					'number' => esc_html__('Number', 'cubewp-framework'),
					'pages' => esc_html__('Pages', 'cubewp-framework'),
					'submit_edit_page' => esc_html__('Submit/Edit Post Page', 'cubewp-framework'),
				],
				'default' => 'text',
			]
		);

		$this->add_control(
			'setting_text_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('text'),
				'condition' => [
					'setting_field_type' => 'text',
				],
			]
		);

		$this->add_control(
			'setting_color_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('color'),
				'condition' => [
					'setting_field_type' => 'color',
				],
			]
		);

		$this->add_control(
			'setting_select_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('select'),
				'condition' => [
					'setting_field_type' => 'select',
				],
			]
		);

		$this->add_control(
			'setting_image_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('media'),
				'condition' => [
					'setting_field_type' => 'image',
				],
			]
		);

		$this->add_control(
			'setting_number_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('number'),
				'condition' => [
					'setting_field_type' => 'number',
				],
			]
		);
		$this->add_control(
			'setting_pages_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('pages'),
				'condition' => [
					'setting_field_type' => 'pages',
				],
			]
		);
		$this->add_control(
			'setting_submit_edit_page_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select setting field', 'cubewp-framework'),
				'options' => $this->get_settings_fields_by_type('submit_edit_page'),
				'condition' => [
					'setting_field_type' => 'submit_edit_page',
				],
			]
		);
	}

	public function get_value($options = array())
	{

		$field_type = $this->get_settings('setting_field_type');
		$field_key  = $this->get_selected_setting_field_key($field_type);
		if (empty($field_key)) {
			return '';
		}

		$cwp_options = get_option('cwpOptions', array());
		$value = isset($cwp_options[$field_key]) ? $cwp_options[$field_key] : '';

		if ('image' === $field_type) {
			return $this->normalize_image_value($value);
		}
		if ('pages' === $field_type) {
			return $this->normalize_pages_value($value);
		}
		if ('submit_edit_page' === $field_type) { 
			$value = isset($cwp_options['submit_edit_page'][$field_key]) ? $cwp_options['submit_edit_page'][$field_key] : '';
			return $this->normalize_post_type_page_value($value);
		}
		if (is_array($value)) {
			return implode(', ', array_map('strval', $value));
		}

		return (string) $value;
	}

	private function get_selected_setting_field_key($field_type)
	{
		switch ($field_type) {
			case 'color':
				return $this->get_settings('setting_color_field');
			case 'select':
				return $this->get_settings('setting_select_field');
			case 'image':
				return $this->get_settings('setting_image_field');
			case 'number':
				return $this->get_settings('setting_number_field');
			case 'pages':
				return $this->get_settings('setting_pages_field');
			case 'submit_edit_page':
				return $this->get_settings('setting_submit_edit_page_field');
			case 'text':
			default:
				return $this->get_settings('setting_text_field');
		}
	}

	private function get_settings_fields_by_type($type)
	{
		$options = array();
		$field_type_map = $this->get_default_settings_field_type_map();
		if (empty($field_type_map) || ! is_array($field_type_map)) {
			return $options;
		} 
		if (isset($field_type_map[$type]) && is_array($field_type_map[$type])) {
			$options = array_column($field_type_map[$type], 'title', 'id');
		}

		if ($type === 'submit_edit_page') {
			$cwp_options = get_option('cwpOptions', array());
			$value = isset($cwp_options['submit_edit_page']) ? $cwp_options['submit_edit_page'] : ''; 
			foreach($value as $post_type => $page_id) {
				if(empty($page_id)) {
					continue;
				}
				$options[$post_type] = get_the_title($page_id);
			} 
		}

		return $options;
	}

	private function get_default_settings_field_type_map()
	{
		static $field_map = null;
		if (is_array($field_map)) {
			return $field_map;
		}

		$settings = array();
		if (class_exists('CubeWp_Settings') && method_exists('CubeWp_Settings', 'build_settings_options')) {
			$settings = CubeWp_Settings::build_settings_options();
		}

		if (function_exists('cubewp_settings_get_field_types_and_ids')) {
			$field_map = cubewp_settings_get_field_types_and_ids($settings);
		} else {
			$field_map = array();
		}

		return $field_map;
	}

	private function normalize_image_value($value)
	{
		$image_id = 0;

		if (is_array($value) && isset($value['id']) && is_numeric($value['id'])) {
			$image_id = (int) $value['id'];
		} elseif (is_numeric($value)) {
			$image_id = (int) $value;
		} elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
			$image_id = attachment_url_to_postid($value);
		}

		if (! $image_id || ! wp_attachment_is_image($image_id)) {
			return array();
		}

		$image_src = wp_get_attachment_image_src($image_id, 'full');
		if (! $image_src || empty($image_src[0])) {
			return array();
		}

		return array(
			'id' => $image_id,
			'url' => $image_src[0],
		);
	}

	private function normalize_pages_value($value)
	{
		$page_id = 0;

		// Try to extract an integer page/post ID from possible value types
		if (is_array($value) && isset($value['id']) && is_numeric($value['id'])) {
			$page_id = (int) $value['id'];
		} elseif (is_numeric($value)) {
			$page_id = (int) $value;
		} elseif (is_string($value) && is_numeric($value)) {
			$page_id = (int) $value;
		}

		if ($page_id > 0) {
			$url = get_permalink($page_id);
			if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
				return $url;
			}
		}

		return '';
	}

	private function normalize_post_type_page_value($value)
	{
		$post_type_page_url = '';
		$url = get_permalink($value);
		if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
			return $url;
		}
		return $post_type_page_url;
	}
}

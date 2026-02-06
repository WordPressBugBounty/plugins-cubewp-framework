<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Taxonomy_text extends Tag
{

	public function get_name()
	{
		return 'cubewp-taxonomy-text-tag';
	}

	public function get_title()
	{
		return esc_html__('Taxonomy Text', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-taxonomy-fields'];
	}

	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	protected function register_controls()
	{
		$this->add_control(
			'field_source',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__('Field Source', 'cubewp-framework'),
				'options' => [
					'cubewp' => esc_html__('CubeWP Field', 'cubewp-framework'),
					'custom'  => esc_html__('Custom Term Meta Key', 'cubewp-framework'),
				],
				'default' => 'cubewp',
			]
		);

		$options = array();
		if (function_exists('cubewp_get_taxonomy_fields_by_type')) {
			$options = cubewp_get_taxonomy_fields_by_type(array('text'));
		}

		$this->add_control(
			'user_selected_field',
			[
				'type'      => \Elementor\Controls_Manager::SELECT,
				'label'     => esc_html__('Select custom field', 'cubewp-framework'),
				'options'   => $options,
				'condition' => [
					'field_source' => 'cubewp',
				],
			]
		);

		$this->add_control(
			'custom_field_key',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => esc_html__('Custom Term Meta Key', 'cubewp-framework'),
				'description' => esc_html__('Enter the term meta key/slug', 'cubewp-framework'),
				'condition'   => [
					'field_source' => 'custom',
				],
			]
		);
		$this->add_control(
			'use_for_icon',
			[
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label' => esc_html__('Use For Icon', 'cubewp-framework'),
				'default' => 'no',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'icon_width',
			[
				'type' => \Elementor\Controls_Manager::SLIDER,
				'label' => esc_html__('Icon Width', 'cubewp-framework'),
				'default' => [
					'size' => 14,
				],
				'condition' => [
					'use_for_icon' => 'yes',
				],
			]
		);
	}

	public function render()
	{
		$field_source = $this->get_settings('field_source');
		$use_for_icon = $this->get_settings('use_for_icon');
		$icon_width = $this->get_settings('icon_width');
		$field        = 'cubewp' === $field_source ? $this->get_settings('user_selected_field') : $this->get_settings('custom_field_key');

		if (!$field) {
			return;
		}

		$term_id = null;
		$term = null;

		$preview_term_id = function_exists('cubewp_get_preview_term_id') ? cubewp_get_preview_term_id() : null;
		if ($preview_term_id) {
			$term = get_term((int) $preview_term_id);
			if ($term && ! is_wp_error($term)) {
				$term_id = $term->term_id;
			}
		}

		if (!$term_id) {
			global $cubewp_term;
			if (isset($cubewp_term) && is_object($cubewp_term) && isset($cubewp_term->term_id)) {
				$term_id = $cubewp_term->term_id;
				$term = $cubewp_term;
			}
		}

		if (!$term_id) {
			$queried_object = get_queried_object();
			if ($queried_object && isset($queried_object->term_id)) {
				$term_id = $queried_object->term_id;
				$term = $queried_object;
			}
		}

		if (!$term_id || !$term) {
			return;
		}

		$value = get_term_meta($term_id, $field, true);
		if (!$value) {
			return;
		}

		$icon_width_style = '';
		if ($use_for_icon == 'yes' && isset($icon_width['size'])) {
			$icon_width_size = $icon_width['size'];
			$icon_width_unit = isset($icon_width['unit']) ? $icon_width['unit'] : 'px';
			$icon_width_style = $icon_width_size . $icon_width_unit;
		}

		if ($use_for_icon == 'yes') {
			if (preg_match('/^data:image\/[a-zA-Z]+;base64,/', $value) || preg_match('/^https?:\/\/.+\.(jpg|jpeg|png|gif|svg|webp)$/i', $value)) {
				$width_attr = $icon_width_style ? ' width: ' . esc_attr($icon_width_style) . ';' : '';
				echo '<img src="' . esc_url($value) . '" alt="icon" style="max-width:100%; max-height:100%;' . $width_attr . '" />';
			} else {
				$font_size = $icon_width_style ? 'font-size: ' . esc_attr($icon_width_style) . ';' : '';
				echo '<i class="' . esc_attr($value) . '"' . ($font_size ? ' style="' . $font_size . '"' : '') . '></i>';
			}
		} else {
			echo esc_html(cubewp_core_data($value));
		}
	}
}

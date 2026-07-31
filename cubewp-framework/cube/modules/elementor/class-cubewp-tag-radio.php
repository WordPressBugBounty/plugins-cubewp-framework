<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Radio extends \Elementor\Core\DynamicTags\Tag
{

	public function get_name()
	{
		return 'cubewp-radio-tag';
	}

	public function get_title()
	{
		return esc_html__('Fields type (radio)', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-fields'];
	}

	public function get_categories()
	{
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
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

		$options = get_fields_by_type(array('radio'));

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

		$user_field_options = get_user_fields_by_type(array('radio'));

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

	public function render() {

		$field_type = $this->get_settings( 'field_type' );
		$field_type = $field_type ? $field_type : 'post';
	
		$value = '';
		$raw_value = null;
	
		if ( 'user' === $field_type ) {
	
			$field = $this->get_settings( 'user_selected_user_field' );
			if ( ! $field ) {
				return;
			}
	
			$raw_value = get_user_field_value( $field );
	
		} else {
	
			$field = $this->get_settings( 'user_selected_field' );
			if ( ! $field ) {
				return;
			}
	
			$raw_value = get_field_value( $field );
		}

		// Build classes from raw value(s) before normalization.
		$classes = array( 'cubewp-radio-tag' );
		$class_values = is_array( $raw_value ) ? $raw_value : array( $raw_value );
		$class_values = array_filter( array_map( 'strval', $class_values ) );
		foreach ( $class_values as $class_value ) {
			$classes[] = 'cubewp-radio-value-' . sanitize_html_class( sanitize_title( $class_value ) );
		}
	
		$value = $raw_value;

		// Normalize value
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
	
		$value = apply_filters('cubewp/elementor/radio/dynamic_tag/value',$value,$this->get_settings());
	
		if ( '' === $value || null === $value ) {
			return;
		}

		$classes = apply_filters(
			'cubewp/elementor/radio/dynamic_tag/classes',
			array_values( array_unique( array_filter( $classes ) ) ),
			$value,
			$this->get_settings()
		);

		echo '<span class="' . esc_attr( implode( ' ', $classes ) ) . '">' . esc_html( cubewp_core_data( $value ) ) . '</span>';
	}
	
}

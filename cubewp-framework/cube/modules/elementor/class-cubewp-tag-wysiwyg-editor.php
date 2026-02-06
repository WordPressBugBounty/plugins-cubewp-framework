<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Wysiwyg_Editor extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'cubewp-wysiwyg_editor-tag';
	}

	public function get_title() {
		return esc_html__( 'Fields type (wysiwyg_editor)', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-fields' ];
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
        
		$options = get_fields_by_type(array('wysiwyg_editor'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Select custom field', 'cubewp-framework' ),
				'options' => $options,
			]
		);
	}

	public function render() {
		$field = $this->get_settings( 'user_selected_field' );
        
		if ( ! $field ) {
			return;
		}
		$value = get_field_value( $field );
		// Ensure string, preserve line breaks/paragraphs, then allow safe HTML + SVG.
		$value = is_string( $value ) ? $value : '';
		$value = wpautop( $value );
		echo wp_kses( $value, $this->allowed_svg() );
	}

	public function allowed_svg(): array {
		$allowed = wp_kses_allowed_html('post'); // start from default post context
		$allowed['svg'] = [
			'class'       => true,
			'xmlns'       => true,
			'width'       => true,
			'height'      => true,
			'viewBox'     => true,
			'fill'        => true,
			'stroke'      => true,
			'stroke-width'=> true,
			'role'        => true,
			'aria-hidden' => true,
			'focusable'   => true,
		];
		$allowed['path'] = [
			'd'           => true,
			'fill'        => true,
			'stroke'      => true,
			'stroke-width'=> true,
			'fill-rule'   => true,
			'clip-rule'   => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		];
		$allowed['g']     = [ 'fill' => true, 'stroke' => true, 'clip-path' => true ];
		$allowed['title'] = [];
		$allowed['use']   = [ 'href' => true, 'xlink:href' => true ];
		return $allowed;
	}
}
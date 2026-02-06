<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_File extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'cubewp-file-tag';
	}

	public function get_title() {
		return esc_html__( 'Fields type (file)', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-fields' ];
	}

	public function get_categories() {
		return [ 
                \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
               ];
	}

	public function is_settings_required() {
		return true;
	}
	
	protected function register_controls() {
        
		$options = get_fields_by_type(array('file'));

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
		if( empty( $value ) ) {
			return;
		}
		$fileItemURL = wp_get_attachment_url($value);
		if( empty( $fileItemURL ) ) {
			return;
		}
		echo '<a href="' . esc_url($fileItemURL) . '" download>' . esc_html__('Download File', 'cubewp-framework') . '</a>';
	}
    

}
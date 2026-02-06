<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Gallery extends \Elementor\Core\DynamicTags\Data_Tag {

	public function get_name() {
		return 'cubewp-gallery-tag';
	}

	public function get_title() {
		return esc_html__( 'Fields type (gallery)', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-fields' ];
	}

	public function get_categories() {
		return [ 
                \Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::MEDIA_CATEGORY,
               ];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
        
		$options = get_fields_by_type(array('gallery'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Select custom field', 'cubewp-framework' ),
				'options' => $options,
			]
		);
	}
    
    public function get_value( $options = array() ){
		$field = $this->get_settings( 'user_selected_field' );
        
		if ( ! $field ) {
			return;
		}
        $values = get_field_value( $field );
        $returnArr = array();
        if ( is_array( $values ) && count( $values ) > 0 ) {
            foreach ( $values as $key => $value ) {
				// Normalize ID
				$image_id = is_numeric( $value ) ? (int) $value : 0;
				if ( ! $image_id ) {
					continue;
				}
				// Ensure it's an image attachment
				if ( ! wp_attachment_is_image( $image_id ) ) {
					continue;
				}
				$url = wp_get_attachment_image_url( $image_id, 'full' );
				if ( ! $url ) {
					continue;
				}
				$returnArr[$key] = array(
					'id'  => $image_id,
					'url' => $url,
				);
            }
        } else {
            $image_id = 0;
			// Accept raw URL fallback
			if ( is_string( $values ) && filter_var( $values, FILTER_VALIDATE_URL ) ) {
				$image_id = attachment_url_to_postid( $values );
			} elseif ( is_numeric( $values ) ) {
				$image_id = (int) $values;
			}
			if ( $image_id && wp_attachment_is_image( $image_id ) ) {
				$url = wp_get_attachment_image_url( $image_id, 'full' );
				if ( $url ) {
					$returnArr = array(
						'id'  => $image_id,
						'url' => $url,
					);
				}
			}
        }
		return $returnArr;
	}
    

}
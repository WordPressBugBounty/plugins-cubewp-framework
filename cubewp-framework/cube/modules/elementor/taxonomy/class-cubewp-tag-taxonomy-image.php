<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Taxonomy_image extends Data_Tag {

	public function get_name() {
		return 'cubewp-taxonomy-image-tag';
	}

	public function get_title() {
		return esc_html__( 'Taxonomy Image', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-taxonomy-fields' ];
	}

	public function get_categories() {
		return [
			Module::IMAGE_CATEGORY,
			Module::URL_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
		$this->add_control(
			'field_source',
			[
				'type'    => \Elementor\Controls_Manager::SELECT,
				'label'   => esc_html__( 'Field Source', 'cubewp-framework' ),
				'options' => [
					'cubewp' => esc_html__( 'CubeWP Field', 'cubewp-framework' ),
					'custom'  => esc_html__( 'Custom Term Meta Key', 'cubewp-framework' ),
				],
				'default' => 'cubewp',
			]
		);

		$options = array();
		if ( function_exists( 'cubewp_get_taxonomy_fields_by_type' ) ) {
			$options = cubewp_get_taxonomy_fields_by_type( array( 'image' ) );
		}

		$this->add_control(
			'user_selected_field',
			[
				'type'      => \Elementor\Controls_Manager::SELECT,
				'label'     => esc_html__( 'Select custom field', 'cubewp-framework' ),
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
				'label'       => esc_html__( 'Custom Term Meta Key', 'cubewp-framework' ),
				'description' => esc_html__( 'Enter the term meta key/slug', 'cubewp-framework' ),
				'condition'   => [
					'field_source' => 'custom',
				],
			]
		);
	}

	public function get_value( $options = array() ) {
		$returnArr = array();
		$field_source = $this->get_settings( 'field_source' );
		$field     = 'cubewp' === $field_source ? $this->get_settings( 'user_selected_field' ) : $this->get_settings( 'custom_field_key' );

		if ( ! $field ) {
			return $returnArr;
		}

		$preview_term_id = function_exists( 'cubewp_get_preview_term_id' ) ? cubewp_get_preview_term_id() : null;
		if ( $preview_term_id ) {
			$term = get_term( (int) $preview_term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$value = get_term_meta( $term->term_id, $field, true );
				if ( $value ) {
					$image_id = $this->normalize_image_id( $value );
					if ( $image_id && wp_attachment_is_image( $image_id ) ) {
						$url = wp_get_attachment_image_url( $image_id, 'full' );
						if ( $url ) {
							return array(
								'id'  => (int) $image_id,
								'url' => $url,
							);
						}
					}
				}
			}
			return $returnArr;
		}

		global $cubewp_term;

		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return $returnArr;
		}

		$term_id = isset( $cubewp_term->term_id ) ? $cubewp_term->term_id : 0;
		if ( ! $term_id ) {
			return $returnArr;
		}

		$value = get_term_meta( $term_id, $field, true );
		if ( ! $value ) {
			return $returnArr;
		}

		$image_id = $this->normalize_image_id( $value );
		if ( ! $image_id || ! wp_attachment_is_image( $image_id ) ) {
			return $returnArr;
		}

		$url = wp_get_attachment_image_url( $image_id, 'full' );
		if ( ! $url ) {
			return $returnArr;
		}

		$returnArr = array(
			'id'  => (int) $image_id,
			'url' => $url,
		);

		return $returnArr;
	}

	private function normalize_image_id( $value ) {
		$image_id = 0;
		if ( is_array( $value ) ) {
			if ( isset( $value['id'] ) && is_numeric( $value['id'] ) ) {
				$image_id = (int) $value['id'];
			} elseif ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
				$image_id = attachment_url_to_postid( $value['url'] );
			}
		} elseif ( is_numeric( $value ) ) {
			$image_id = (int) $value;
		} elseif ( is_string( $value ) && filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$image_id = attachment_url_to_postid( $value );
		}

		return $image_id;
	}

}


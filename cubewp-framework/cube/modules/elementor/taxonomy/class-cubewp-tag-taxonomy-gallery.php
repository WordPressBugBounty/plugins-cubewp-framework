<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Taxonomy_gallery extends Data_Tag {

	public function get_name() {
		return 'cubewp-taxonomy-gallery-tag';
	}

	public function get_title() {
		return esc_html__( 'Taxonomy Gallery', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-taxonomy-fields' ];
	}

	public function get_categories() {
		return [
			Module::GALLERY_CATEGORY,
			Module::MEDIA_CATEGORY,
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
			$options = cubewp_get_taxonomy_fields_by_type( array( 'gallery' ) );
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
		$field_source = $this->get_settings( 'field_source' );
		$field = 'cubewp' === $field_source ? $this->get_settings( 'user_selected_field' ) : $this->get_settings( 'custom_field_key' );

		if ( ! $field ) {
			return array();
		}

		$preview_term_id = function_exists( 'cubewp_get_preview_term_id' ) ? cubewp_get_preview_term_id() : null;
		if ( $preview_term_id ) {
			$term = get_term( (int) $preview_term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$values = get_term_meta( $term->term_id, $field, true );
				return $this->process_gallery_values( $values );
			}
			return array();
		}

		global $cubewp_term;

		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return array();
		}

		$term_id = isset( $cubewp_term->term_id ) ? $cubewp_term->term_id : 0;
		if ( ! $term_id ) {
			return array();
		}

		$values = get_term_meta( $term_id, $field, true );
		if ( ! $values ) {
			return array();
		}

		return $this->process_gallery_values( $values );
	}

	private function process_gallery_values( $values ) {
		$returnArr = array();
		if ( is_array( $values ) && count( $values ) > 0 ) {
			foreach ( $values as $key => $value ) {
				$image_id = is_numeric( $value ) ? (int) $value : 0;
				if ( ! $image_id ) {
					continue;
				}
				if ( ! wp_attachment_is_image( $image_id ) ) {
					continue;
				}
				$url = wp_get_attachment_image_url( $image_id, 'full' );
				if ( ! $url ) {
					continue;
				}
				$returnArr[ $key ] = array(
					'id'  => $image_id,
					'url' => $url,
				);
			}
		} else {
			$image_id = 0;
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


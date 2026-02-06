<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Taxonomy_url extends Tag {

	public function get_name() {
		return 'cubewp-taxonomy-url-tag';
	}

	public function get_title() {
		return esc_html__( 'Taxonomy URL', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-taxonomy-fields' ];
	}

	public function get_categories() {
		return [
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
			$options = cubewp_get_taxonomy_fields_by_type( array( 'url' ) );
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

	public function render() {
		$field_source = $this->get_settings( 'field_source' );
		$field        = 'cubewp' === $field_source ? $this->get_settings( 'user_selected_field' ) : $this->get_settings( 'custom_field_key' );

		if ( ! $field ) {
			return;
		}

		$preview_term_id = function_exists( 'cubewp_get_preview_term_id' ) ? cubewp_get_preview_term_id() : null;
		if ( $preview_term_id ) {
			$term = get_term( (int) $preview_term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$value = get_term_meta( $term->term_id, $field, true );
				if ( $value ) {
					echo esc_url( cubewp_core_data( $value ) );
				}
				return;
			}
		}

		global $cubewp_term;

		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return;
		}

		$term_id = isset( $cubewp_term->term_id ) ? $cubewp_term->term_id : 0;
		if ( ! $term_id ) {
			return;
		}

		$value = get_term_meta( $term_id, $field, true );
		if ( ! $value ) {
			return;
		}

		echo esc_url( cubewp_core_data( $value ) );
	}

}


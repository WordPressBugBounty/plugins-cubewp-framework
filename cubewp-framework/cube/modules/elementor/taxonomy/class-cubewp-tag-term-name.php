<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Term_name extends Tag {

	public function get_name() {
		return 'cubewp-term-name-tag';
	}

	public function get_title() {
		return esc_html__( 'Term Name', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-taxonomy-fields' ];
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return false;
	}

	public function render() {
		$preview_term_id = function_exists('cubewp_get_preview_term_id') ? cubewp_get_preview_term_id() : null;
        if ($preview_term_id) {
            $term = get_term((int) $preview_term_id);
            if ($term && ! is_wp_error($term)) {
                echo esc_html( $term->name );
                return;
            }
        }
		global $cubewp_term;
		
		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return;
		}
		
		$term_name = isset( $cubewp_term->name ) ? $cubewp_term->name : '';
		echo esc_html( $term_name );
	}

}


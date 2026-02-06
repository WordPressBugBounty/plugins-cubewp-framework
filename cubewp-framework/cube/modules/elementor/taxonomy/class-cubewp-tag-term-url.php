<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Term_url extends Data_Tag {

	public function get_name() {
		return 'cubewp-term-url-tag';
	}

	public function get_title() {
		return esc_html__( 'Term URL', 'cubewp-framework' );
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
		return false;
	}

	public function get_value( $options = array() ) {
		$preview_term_id = function_exists('cubewp_get_preview_term_id') ? cubewp_get_preview_term_id() : null;
        if ($preview_term_id) {
            $term = get_term((int) $preview_term_id);
            if ($term && ! is_wp_error($term)) {
                $term_link = get_term_link($term);
                if (! is_wp_error($term_link)) {
                    return $term_link;
                }
            }
        }
		global $cubewp_term;
		
		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return '';
		}
		
		$term_id = isset( $cubewp_term->term_id ) ? $cubewp_term->term_id : 0;
		if ( ! $term_id ) {
			return '';
		}
		
		$term_link = get_term_link( $term_id );
		if ( is_wp_error( $term_link ) ) {
			return '';
		}
		
		return $term_link;
	}

	protected function register_controls() {
		// No controls needed for term URL
	}

}


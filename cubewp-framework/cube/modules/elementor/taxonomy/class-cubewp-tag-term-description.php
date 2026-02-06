<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Term_description extends Tag {

	public function get_name() {
		return 'cubewp-term-description-tag';
	}

	public function get_title() {
		return esc_html__( 'Term Description', 'cubewp-framework' );
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
		return true;
	}

	protected function register_controls() {
		$this->add_control(
			'description_length',
			[
				'label' => esc_html__( 'Description Length (words)', 'cubewp-framework' ),
				'type'  => Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 500,
				'description' => esc_html__( 'Set to 0 to show full description. Set a number to limit the word count.', 'cubewp-framework' ),
			]
		);

		$this->add_control(
			'more_text',
			[
				'label' => esc_html__( 'More Text', 'cubewp-framework' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( '...', 'cubewp-framework' ),
				'description' => esc_html__( 'Text to append when description is trimmed (e.g., "...", "Read more")', 'cubewp-framework' ),
				'condition' => [
					'description_length!' => [0, ''],
				],
			]
		);
	}

	public function render() {
		$settings = $this->get_settings();
		$length   = ! empty( $settings['description_length'] ) ? intval( $settings['description_length'] ) : 0;
		$more_text = ! empty( $settings['more_text'] ) ? $settings['more_text'] : '...';

		$preview_term_id = function_exists('cubewp_get_preview_term_id') ? cubewp_get_preview_term_id() : null;
		if ( $preview_term_id ) {
			$term = get_term((int) $preview_term_id);
			if ( $term && ! is_wp_error($term) ) {
				$description = isset( $term->description ) ? $term->description : '';
				if ( ! empty( $description ) ) {
					if ( $length > 0 ) {
						$description = wp_trim_words( wp_strip_all_tags( $description ), $length, $more_text );
					}
					echo wp_kses_post( $description );
				}
				return;
			}
		}

		global $cubewp_term;
		
		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return;
		}
		
		$description = isset( $cubewp_term->description ) ? $cubewp_term->description : '';
		
		if ( empty( $description ) ) {
			return;
		}

		// Apply word limit if set
		if ( $length > 0 ) {
			$description = wp_trim_words( wp_strip_all_tags( $description ), $length, $more_text );
		}

		echo wp_kses_post( $description );
	}

}


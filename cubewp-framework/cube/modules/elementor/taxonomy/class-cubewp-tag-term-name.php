<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
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
		return true;
	}

	protected function register_controls() {
		$this->add_control(
			'line_limit_enable',
			[
				'label'        => esc_html__( 'Enable Line Limit', 'cubewp-framework' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'cubewp-framework' ),
				'label_off'    => esc_html__( 'No', 'cubewp-framework' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'line_limit',
			[
				'label'       => esc_html__( 'Max Lines', 'cubewp-framework' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 1,
				'min'         => 1,
				'max'         => 5,
				'step'        => 1,
				'description' => esc_html__( 'Set how many lines to show before truncating with ellipsis.', 'cubewp-framework' ),
				'condition'   => [
					'line_limit_enable' => 'yes',
				],
			]
		);
	}

	public function render() {
		$term_name = $this->get_term_name();

		if ( '' === $term_name ) {
			return;
		}

		$settings      = $this->get_settings_for_display();
		$enable_limit  = ! empty( $settings['line_limit_enable'] ) && 'yes' === $settings['line_limit_enable'];
		$line_limit    = ! empty( $settings['line_limit'] ) ? intval( $settings['line_limit'] ) : 1;
		$line_limit    = min( 5, max( 1, $line_limit ) );

		if ( $enable_limit ) {
			echo '<span class="cubewp-term-name-tag cubewp-clamp-' . intval( $line_limit ) . '">' . esc_html( $term_name ) . '</span>';
			return;
		}

		echo esc_html( $term_name );
	}

	/**
	 * Resolve the current term name from preview or loop context.
	 *
	 * @return string
	 */
	private function get_term_name() {
		$preview_term_id = function_exists( 'cubewp_get_preview_term_id' ) ? cubewp_get_preview_term_id() : null;

		if ( $preview_term_id ) {
			$term = get_term( (int) $preview_term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				return isset( $term->name ) ? (string) $term->name : '';
			}
		}

		global $cubewp_term;

		if ( ! isset( $cubewp_term ) || ! is_object( $cubewp_term ) ) {
			return '';
		}

		return isset( $cubewp_term->name ) ? (string) $cubewp_term->name : '';
	}

}


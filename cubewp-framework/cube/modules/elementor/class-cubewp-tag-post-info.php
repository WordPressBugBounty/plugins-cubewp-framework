<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Post_Info extends Tag {

	public function get_name() {
		return 'cubewp-post-info-tag';
	}

	public function get_title() {
		return esc_html__( 'Post Info', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-single-fields' ];
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	public function render() {

		$field = $this->get_settings( 'post_info_field' );

		if ( cubewp_is_elementor_editing() ) {
			$post_id = cubewp_get_elementor_preview_post_id();
		} else {
			$post_id = get_the_ID();
		}

		$output = '';

		if ( $field == 'publish_date' ) {

			$output = get_the_date( '', $post_id );

		} else if ( $field == 'publish_time' ) {

			$output = get_the_time( '', $post_id );

		} else if ( $field == 'views' ) {

			$views = get_post_meta( $post_id, 'cubewp_post_views', true );

			$output = ! empty( $views ) ? $views : 0;

		} else if ( $field == 'reading_time' ) {

			$content = get_post_field( 'post_content', $post_id );

			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			$reading_time = ceil( $word_count / 200 );

			$reading_time = $reading_time ? $reading_time : 1;

			$reading_text = $this->get_settings( 'reading_time_text' );

			$output = $reading_time . ' ' . $reading_text;

		} else if ( $field == 'post_id' ) {

			$output = $post_id ? (string) absint( $post_id ) : '';
		}

		echo esc_html( $output );
	}

	protected function register_controls() {

		$this->add_control(
			'post_info_field',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Select Info Field', 'cubewp-framework' ),
				'options' => array(
					'publish_date' => esc_html__( 'Publish Date', 'cubewp-framework' ),
					'publish_time' => esc_html__( 'Publish Time', 'cubewp-framework' ),
					'views'        => esc_html__( 'Post Views', 'cubewp-framework' ),
					'reading_time' => esc_html__( 'Reading Time', 'cubewp-framework' ),
					'post_id'      => esc_html__( 'Post ID', 'cubewp-framework' ),
				),
				'default' => 'publish_date',
			]
		);

		$this->add_control(
			'reading_time_text',
			[
				'type'        => Controls_Manager::TEXT,
				'label'       => esc_html__( 'Reading Time Text', 'cubewp-framework' ),
				'default'     => esc_html__( 'min read', 'cubewp-framework' ),
				'placeholder' => esc_html__( 'min read', 'cubewp-framework' ),
				'condition'   => [
					'post_info_field' => 'reading_time',
				],
			]
		);
	}
}
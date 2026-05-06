<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Post_Count extends Tag {
	public function get_name() {
		return 'cubewp-post-count-tag';
	}

	public function get_title() {
		return esc_html__( 'Post Count', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-single-fields' ];
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
			Module::NUMBER_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	public function render() {
		$post_type   = $this->get_settings( 'post_type' );
		$post_status = $this->get_settings( 'post_status' );
		$limit       = (int) $this->get_settings( 'limit' );

		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}

		$args = [
			'post_type'      => $post_type,
			'posts_per_page' => $limit > 0 ? $limit : -1,
			'fields'         => 'ids',
			'post_status'    => 'all' === $post_status ? 'any' : 'publish',
			'no_found_rows'  => $limit > 0,
		];

		$query = new WP_Query( $args );
		$count = $limit > 0 ? count( $query->posts ) : $query->found_posts;
		wp_reset_postdata();

		echo esc_html( $count );
	}

	protected function register_controls() {
		$this->add_control(
			'post_type',
			[
				'type'    => Controls_Manager::SELECT2,
				'label'   => esc_html__( 'Post Type', 'cubewp-framework' ),
				'options' => $this->get_post_types(),
				'default' => 'post',
			]
		);

		$this->add_control(
			'post_status',
			[
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Post Status', 'cubewp-framework' ),
				'options' => [
					'publish' => esc_html__( 'Published Only', 'cubewp-framework' ),
					'all'     => esc_html__( 'All Posts', 'cubewp-framework' ),
				],
				'default' => 'publish',
			]
		);

		$this->add_control(
			'limit',
			[
				'type'        => Controls_Manager::NUMBER,
				'label'       => esc_html__( 'Limit', 'cubewp-framework' ),
				'default'     => 0,
				'min'         => 0,
				'placeholder' => '0',
				'description' => esc_html__( 'Use 0 for full count. Set a number (e.g. 1000) to cap the count for better performance with large datasets.', 'cubewp-framework' ),
			]
		);
	}

	private function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$options    = [];

		foreach ( $post_types as $post_type ) {
			if ( 'attachment' !== $post_type->name ) {
				$options[ $post_type->name ] = $post_type->label;
			}
		}

		return $options;
	}
}
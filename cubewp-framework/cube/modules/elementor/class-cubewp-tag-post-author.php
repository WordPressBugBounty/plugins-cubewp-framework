<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Post_Author extends Data_Tag {
	public function get_name() {
		return 'cubewp-post-author-tag';
	}

	public function get_title() {
		return esc_html__( 'Post Author', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-single-fields' ];
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
			Module::URL_CATEGORY,
			Module::IMAGE_CATEGORY,
		];
	}

	public function is_settings_required() {
		return true;
	}

	public function get_value( $options = array() ) {
		$field = $this->get_settings( 'post_author_field' );
	
		if ( cubewp_is_elementor_editing() ) {
			$post_id = cubewp_get_elementor_preview_post_id();
		} else {
			$post_id = get_the_ID();
		}
		$post_id = absint( $post_id );
		if ( ! $post_id ) {
			return ( $field === 'post_count' ) ? 0 : '';
		}

		$post_author_id = absint( get_post_field( 'post_author', $post_id ) );

		if ( $field == 'post_count' ) {
			if ( ! $post_author_id ) {
				return 0;
			}

			$post_type = get_post_type( $post_id );
			if ( empty( $post_type ) ) {
				return 0;
			}
			$args = [
				'author'         => $post_author_id,
				'posts_per_page' => -1,
				'post_type'      => $post_type,
				'post_status'    => 'publish',
			]; 
			$query = new \WP_Query( $args );
			return $query->post_count ? $query->post_count : 0;
		}

		$post_author_data = get_userdata( $post_author_id );
		if ( ! $post_author_id || ! $post_author_data ) {
			return '';
		}

		if ( $field == 'display_name' ) {
			return $post_author_data->display_name;
		} else if ( $field == 'profile_url' ) {
			return get_author_posts_url( $post_author_data->ID );
		} else if ( $field == 'avatar' ) {
			return [
				'id'  => $post_author_data->ID,
				'url' => get_avatar_url( $post_author_data->ID ),
			];
		} else if ( $field == 'custom_meta' ) {
			$field = $this->get_settings( 'post_author_custom_field' );
			if ( ! $field ) {
				return '';
			}

			return get_user_meta( $post_author_data->ID, $field, true );
		} else if ( $field == 'registered_date' ) {
			return $this->get_author_registered_date_value( $post_author_data );
		}

		return '';
	}

	/**
	 * Format the post author's registration date.
	 *
	 * @param \WP_User $user Author user object.
	 * @return string
	 */
	private function get_author_registered_date_value( $user ) {
		if ( empty( $user->user_registered ) ) {
			return '';
		}

		$timestamp    = strtotime( $user->user_registered );
		$display_type = $this->get_settings( 'post_author_date_display_type' );

		if ( 'human_readable' === $display_type ) {
			return sprintf(
				/* translators: %s: human-readable time difference. */
				esc_html__( '%s ago', 'cubewp-framework' ),
				human_time_diff( $timestamp, current_time( 'timestamp' ) )
			);
		}

		$format = $this->get_settings( 'post_author_date_format' );

		if ( 'default' === $format ) {
			$format = get_option( 'date_format' );
		} elseif ( 'custom' === $format ) {
			$custom = $this->get_settings( 'post_author_custom_date_format' );
			$format = ! empty( $custom ) ? $custom : get_option( 'date_format' );
		}

		return date_i18n( $format, $timestamp );
	}

	protected function register_controls() {
		$this->add_control( 'post_author_field', [
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Select Author Field', 'cubewp-framework' ),
				'options' => array(
					"display_name"    => esc_html__( "Display Name", "cubewp-framework" ),
					"avatar"          => esc_html__( "Avatar", "cubewp-framework" ),
					"profile_url"     => esc_html__( "Profile URL", "cubewp-framework" ),
					"post_count"      => esc_html__( "Post Count", "cubewp-framework" ),
					"registered_date" => esc_html__( "Registered Date", "cubewp-framework" ),
					"custom_meta"     => esc_html__( "Custom Meta", "cubewp-framework" ),
				),
				'default' => 'display_name'
			] );
		$this->add_control( 'post_author_custom_field', [
				'type'      => Controls_Manager::TEXT,
				'label'     => esc_html__( 'Custom Meta Field ID', 'cubewp-framework' ),
				'condition' => array(
					"post_author_field" => "custom_meta",
				),
			] );

		$this->add_control(
			'post_author_date_display_type',
			array(
				'label'     => esc_html__( 'Date Display', 'cubewp-framework' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'           => esc_html__( 'Date Format', 'cubewp-framework' ),
					'human_readable' => esc_html__( 'Human-Readable ("time ago")', 'cubewp-framework' ),
				),
				'condition' => array(
					'post_author_field' => 'registered_date',
				),
			)
		);

		$this->add_control(
			'post_author_date_format',
			array(
				'label'     => esc_html__( 'Date Format', 'cubewp-framework' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => array(
					'default' => esc_html__( 'WordPress Default', 'cubewp-framework' ),
					'F j, Y'  => date_i18n( 'F j, Y' ),
					'Y-m-d'   => date_i18n( 'Y-m-d' ),
					'd/m/Y'   => date_i18n( 'd/m/Y' ),
					'm/d/Y'   => date_i18n( 'm/d/Y' ),
					'custom'  => esc_html__( 'Custom', 'cubewp-framework' ),
				),
				'condition' => array(
					'post_author_field'        => 'registered_date',
					'post_author_date_display_type' => 'date',
				),
			)
		);

		$this->add_control(
			'post_author_custom_date_format',
			array(
				'label'       => esc_html__( 'Custom Date Format', 'cubewp-framework' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'F j, Y',
				'description' => esc_html__( 'Use PHP date format, e.g. F j, Y or d/m/Y', 'cubewp-framework' ),
				'condition'   => array(
					'post_author_field'             => 'registered_date',
					'post_author_date_display_type' => 'date',
					'post_author_date_format'       => 'custom',
				),
			)
		);
	}

}
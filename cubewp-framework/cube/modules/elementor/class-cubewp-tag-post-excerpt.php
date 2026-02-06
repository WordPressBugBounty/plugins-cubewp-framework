<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Post_Excerpt extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'cubewp-post-excerpt-tag';
	}

	public function get_title() {
		return esc_html__( 'Post Excerpt', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-single-fields' ];
	}

	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
		];
	}

	public function is_settings_required() {
		return false;
	}

	protected function register_controls() {
		$this->add_control(
			'excerpt_length',
			[
				'label' => esc_html__( 'Excerpt Length (words)', 'cubewp-framework' ),
				'type'  => \Elementor\Controls_Manager::NUMBER,
				'default' => 20,
				'min' => 5,
				'max' => 100,
				'description' => esc_html__( 'Trim content if excerpt is empty.', 'cubewp-framework' ),
			]
		);

		$this->add_control(
			'show_read_more',
			[
				'label' => esc_html__( 'Show "Read More" Link', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'cubewp-framework' ),
				'label_off' => esc_html__( 'No', 'cubewp-framework' ),
				'default' => 'no',
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label' => esc_html__( '"Read More" Text', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__( 'Read More', 'cubewp-framework' ),
				'condition' => [
					'show_read_more' => 'yes',
				],
			]
		);

		$this->add_control(
			'read_more_new_tab',
			[
				'label' => esc_html__( 'Open in New Tab', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'cubewp-framework' ),
				'label_off' => esc_html__( 'No', 'cubewp-framework' ),
				'default' => 'no',
				'condition' => [
					'show_read_more' => 'yes',
				],
			]
		);
	}

	public function render() {
		$settings = $this->get_settings();
		$length   = ! empty( $settings['excerpt_length'] ) ? intval( $settings['excerpt_length'] ) : 20;

		$post_id = cubewp_is_elementor_editing() ? cubewp_get_elementor_preview_post_id() : get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$excerpt = get_the_excerpt( $post_id );

		// Fallback: Trim post content if excerpt is missing
		if ( empty( $excerpt ) ) {
			$content = get_post_field( 'post_content', $post_id );
			$excerpt = wp_trim_words( wp_strip_all_tags( $content ), $length );
		}

		// Apply custom length trim if needed
		$excerpt = wp_trim_words( $excerpt, $length );

		// Add Read More link if enabled
		if ( 'yes' === $settings['show_read_more'] ) {
			$read_more_text = ! empty( $settings['read_more_text'] ) ? $settings['read_more_text'] : esc_html__( 'Read More', 'cubewp-framework' );
			$target_attr    = ( 'yes' === $settings['read_more_new_tab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
			$post_url       = get_permalink( $post_id );

			$excerpt .= sprintf(
				' <a href="%1$s"%2$s class="cwp-read-more">%3$s</a>',
				esc_url( $post_url ),
				$target_attr,
				esc_html( $read_more_text )
			);
		}

		echo wp_kses_post( $excerpt );
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_Google_Address extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'cubewp-google_address-tag';
	}

	public function get_title() {
		return esc_html__( 'Fields type (google_address)', 'cubewp-framework' );
	}

	public function get_group() {
		return [ 'cubewp-fields' ];
	}

	public function get_categories() {
		return [ 
                \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
				\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
               ];
	}

	public function is_settings_required() {
		return true;
	}

	protected function register_controls() {
        
		$options = get_fields_by_type(array('google_address'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Select custom field', 'cubewp-framework' ),
				'options' => $options,
			]
		);

		$this->add_control(
			'google_address_content_type',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Content Type', 'cubewp-framework'),
				'options' => [
					'address' => esc_html__('Address', 'cubewp-framework'),
					'formatted_address' => esc_html__('Formatted Address', 'cubewp-framework'),
					'directions_url' => esc_html__('Directions URL', 'cubewp-framework'),
				],
				'default' => 'address',
			]
		);

		$this->add_control(
			'google_address_line_limit_enable',
			[
				'label' => esc_html__( 'Enable Line Limit', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'cubewp-framework' ),
				'label_off' => esc_html__( 'No', 'cubewp-framework' ),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'google_address_content_type' => 'formatted_address',
				],
			]
		);

		$this->add_control(
			'google_address_line_limit',
			[
				'label' => esc_html__( 'Max Lines', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 1,
				'min' => 1,
				'max' => 5,
				'step' => 1,
				'description' => esc_html__( 'Set how many lines to show before truncating with ellipsis (CSS clamp, same as post title tag).', 'cubewp-framework' ),
				'condition' => [
					'google_address_content_type' => 'formatted_address',
					'google_address_line_limit_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'google_address_words_limit_enable',
			[
				'label' => esc_html__( 'Enable Words Limit', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'cubewp-framework' ),
				'label_off' => esc_html__( 'No', 'cubewp-framework' ),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'google_address_content_type' => 'formatted_address',
				],
			]
		);

		$this->add_control(
			'google_address_words_limit',
			[
				'label' => esc_html__( 'Words Limit', 'cubewp-framework' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'description' => esc_html__( 'Set how many words to show for the formatted address.', 'cubewp-framework' ),
				'condition' => [
					'google_address_content_type' => 'formatted_address',
					'google_address_words_limit_enable' => 'yes',
				],
			]
		);

	}

	public function render() {
		$field = $this->get_settings( 'user_selected_field' );
		$content_type = $this->get_settings( 'google_address_content_type' );
		if ( ! $field ) {
			return;
		}
		$settings = $this->get_settings_for_display();
		$value = get_field_value( $field );
		$use_line_clamp = false;
		$line_limit = 1;

		if ( is_array( $value ) && count( $value ) > 0 ) {
			$lat = $value['lat'];
			$lng = $value['lng'];
			$value = $value['address'];
			if ( $content_type == 'formatted_address' ) {
				$enable_line_limit = ! empty( $settings['google_address_line_limit_enable'] ) && $settings['google_address_line_limit_enable'] === 'yes';
				$line_limit = ! empty( $settings['google_address_line_limit'] ) ? intval( $settings['google_address_line_limit'] ) : 1;
				$line_limit = min( 5, max( 1, $line_limit ) );
				$enable_words_limit = ! empty( $settings['google_address_words_limit_enable'] ) && $settings['google_address_words_limit_enable'] === 'yes';
				$words_limit = ! empty( $settings['google_address_words_limit'] ) ? intval( $settings['google_address_words_limit'] ) : 10;

				if ( $enable_words_limit && $words_limit > 0 ) {
					$words = explode( ' ', $value );
					$value = implode( ' ', array_slice( $words, 0, $words_limit ) );
				}

				if ( $enable_line_limit && $line_limit > 0 ) {
					$use_line_clamp = true;
				}
			} else if ( $content_type == 'directions_url' ) {
				if ( $lat && $lng ) {
					$value = 'https://www.google.com/maps?q=' . esc_attr( $lat ) . ',' . esc_attr( $lng );
				}
			}
		}

		$out = cubewp_core_data( $value );
		if ( '' === $out || null === $out ) {
			return;
		}

		if ( $use_line_clamp ) {
			echo '<span class="cubewp-google-address-field-tag cubewp-clamp-' . intval( $line_limit ) . '">' . esc_html( $out ) . '</span>';
		} else {
			echo $out;
		}
	}
}
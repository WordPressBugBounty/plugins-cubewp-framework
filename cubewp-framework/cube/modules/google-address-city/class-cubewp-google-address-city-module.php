<?php
/**
 * CubeWP Google Address City Location Module
 *
 * Adds settings for city-only suggestions and country restriction in Google Address field.
 * Works in submission forms, search filters, and admin.
 *
 * @version 1.0
 * @package cubewp/modules/google-address-city
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Google_Address_City_Module
 */
class CubeWp_Google_Address_City_Module {

	/**
	 * Initialize the module.
	 */
	public static function init() {
		$instance = new self();
		$instance->setup_hooks();
	}

	/**
	 * Setup hooks for localization and filters.
	 */
	private function setup_hooks() {
		add_filter( 'get_frontend_script_data', array( $this, 'localize_google_address_settings' ), 10, 2 );
		add_filter( 'cubewp_get_admin_script', array( $this, 'localize_admin_google_address_settings' ), 10, 2 );
		add_filter( 'cubewp/frontend/field/parametrs', array( $this, 'filter_google_address_placeholder' ), 10, 2 );
	}

	/**
	 * Get Google address autocomplete settings from options.
	 *
	 * @return array
	 */
	private static function get_autocomplete_settings() {
		$options = get_option( 'cwpOptions', array() );
		return array(
			'city_location' => isset( $options['google_address_city_location'] ) && $options['google_address_city_location'] == '1',
			'country_code'  => isset( $options['google_address_country_code'] ) ? sanitize_text_field( $options['google_address_country_code'] ) : '',
		);
	}

	/**
	 * Pass Google address settings to frontend script.
	 * City mode is applied per-field via data-city-location (only when "Use for Archive Map" is enabled).
	 *
	 * @param array|bool $params Script params.
	 * @param string     $handle Script handle.
	 * @return array|bool
	 */
	public function localize_google_address_settings( $params, $handle ) {
		if ( 'cwp-google-address-field' === $handle ) {
			$settings = self::get_autocomplete_settings();
			return array(
				'google_address_city_location' => false,
				'google_address_country_code'  => $settings['country_code'],
			);
		}
		return $params;
	}

	/**
	 * Pass Google address settings to admin script.
	 * City mode is applied per-field via data-city-location (only when "Use for Archive Map" is enabled).
	 *
	 * @param array|bool $params Script params.
	 * @param string     $handle Script handle.
	 * @return array|bool
	 */
	public function localize_admin_google_address_settings( $params, $handle ) {
		if ( 'cubewp-google-address-field' === $handle ) {
			$settings = self::get_autocomplete_settings();
			return array(
				'google_address_city_location' => false,
				'google_address_country_code'  => $settings['country_code'],
			);
		}
		return $params;
	}

	/**
	 * Filter placeholder for city mode in frontend fields.
	 * Only apply city placeholder when this field has "Use for Archive Map" enabled (map_use).
	 *
	 * @param array $args Field arguments.
	 * @param array $context Context data.
	 * @return array
	 */
	public function filter_google_address_placeholder( $args, $context = array() ) {
		if ( isset( $args['type'] ) && 'google_address' === $args['type'] ) {
			$use_for_archive_map = isset( $args['map_use'] ) && $args['map_use'] == '1';
			if ( $use_for_archive_map ) {
				if ( empty( $args['placeholder'] ) || $args['placeholder'] === __( 'Enter a Location', 'cubewp-framework' ) ) {
					$args['placeholder'] = __( 'Enter a City', 'cubewp-framework' );
				}
			}
		}
		return $args;
	}
}
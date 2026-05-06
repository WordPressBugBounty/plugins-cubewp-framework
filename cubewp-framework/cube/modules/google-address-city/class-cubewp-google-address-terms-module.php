<?php
/**
 * CubeWP Google Address Terms Module
 *
 * When "Use Terms for Google Location" is enabled, searches by city name (e.g., Lahore)
 * filter results by taxonomy term instead of geographic proximity.
 *
 * Usage:
 * 1. Enable "Use Terms for Google Location" in CubeWP Settings > Search & Filters
 * 2. For each post type, select a taxonomy (only taxonomies related to that post type are shown)
 * 3. Ensure terms exist in that taxonomy (e.g., term "Lahore")
 * 4. When user searches "Lahore" in Google Address field, results filter by that term for the current post type
 *
 * @version 1.0
 * @package cubewp/modules/google-address-city
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Google_Address_Terms_Module
 */
class CubeWp_Google_Address_Terms_Module {

	/**
	 * Initialize the module.
	 */
	public static function init() {
		$instance = new self();
		$instance->setup_hooks();
	}

	/**
	 * Setup hooks.
	 */
	private function setup_hooks() {
		add_filter( 'cubewp/search/query/update', array( $this, 'inject_term_from_location_search' ), 5, 2 );
		add_filter( 'cubewp_query_use_google_proximity', array( $this, 'maybe_skip_proximity' ), 10, 2 );
	}

	/**
	 * Get taxonomy for the given post type when terms mode is enabled.
	 *
	 * @param string $post_type Post type slug.
	 * @return array{enabled: bool, taxonomy: string}
	 */
	private static function get_terms_settings( $post_type = '' ) {
		$options  = get_option( 'cwpOptions', array() );
		$enabled  = isset( $options['google_address_use_terms'] ) && $options['google_address_use_terms'] == '1';
		$taxonomy = '';
		if ( $enabled && ! empty( $post_type ) ) {
			$key      = 'google_address_taxonomy_' . sanitize_key( $post_type );
			$taxonomy = isset( $options[ $key ] ) ? sanitize_key( $options[ $key ] ) : '';
		}
		return array(
			'enabled'  => $enabled && ! empty( $taxonomy ),
			'taxonomy' => $taxonomy,
		);
	}

	/**
	 * Find term by name in taxonomy.
	 * Tries exact match first, then slug match.
	 *
	 * @param string $location_name City/location name (e.g., "Lahore").
	 * @param string $taxonomy      Taxonomy slug.
	 * @return WP_Term|false
	 */
	private static function get_term_by_location_name( $location_name, $taxonomy ) {
		if ( empty( $location_name ) || empty( $taxonomy ) ) {
			return false;
		}
		$name = trim( sanitize_text_field( $location_name ) );
		if ( empty( $name ) ) {
			return false;
		}
		// Try exact name match.
		$term = get_term_by( 'name', $name, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
		// Try slug match (handles "Lahore" -> "lahore").
		$slug = sanitize_title( $name );
		$term = get_term_by( 'slug', $slug, $taxonomy );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term;
		}
		return false;
	}

	/**
	 * Get keys in post_data that are google_address fields.
	 *
	 * @param array $post_data Search params.
	 * @return array Field names.
	 */
	private static function get_google_address_field_keys( $post_data ) {
		$keys = array();
		foreach ( array_keys( $post_data ) as $key ) {
			if ( substr( $key, -4 ) === '_lat' || substr( $key, -4 ) === '_lng' || substr( $key, -6 ) === '_range' ) {
				continue;
			}
			$field_options = function_exists( 'get_field_options' ) ? get_field_options( $key ) : array();
			if ( ! empty( $field_options['type'] ) && $field_options['type'] === 'google_address' ) {
				$keys[] = $key;
			}
		}
		return $keys;
	}

	/**
	 * Inject taxonomy term into search args when location is searched and terms mode is on.
	 *
	 * @param array  $post_data Search POST data.
	 * @param string $post_type Post type.
	 * @return array
	 */
	public function inject_term_from_location_search( $post_data, $post_type ) {
		$post_type = ! empty( $post_type ) ? $post_type : ( isset( $post_data['post_type'] ) ? sanitize_key( $post_data['post_type'] ) : '' );
		$settings  = self::get_terms_settings( $post_type );
		if ( ! $settings['enabled'] ) {
			return $post_data;
		}

		$taxonomy = $settings['taxonomy'];
		$fields   = self::get_google_address_field_keys( $post_data );

		foreach ( $fields as $field_name ) {
			$location_value = isset( $post_data[ $field_name ] ) ? $post_data[ $field_name ] : '';
			if ( empty( $location_value ) ) {
				continue;
			}

			$term = self::get_term_by_location_name( $location_value, $taxonomy );
			if ( ! $term ) {
				continue;
			}

			// Add taxonomy filter. Merge with existing if taxonomy already in args.
			$existing = isset( $post_data[ $taxonomy ] ) ? $post_data[ $taxonomy ] : '';
			$term_ids = array_filter( array_map( 'absint', explode( ',', $existing ) ) );
			$term_ids[] = $term->term_id;
			$term_ids   = array_unique( $term_ids );
			$post_data[ $taxonomy ] = implode( ',', $term_ids );

			// Flag to skip proximity search in CubeWp_Query.
			$post_data['_google_address_use_terms'] = 1;
			break; // Only first matching location.
		}

		return $post_data;
	}

	/**
	 * Skip proximity search when terms mode is used.
	 *
	 * @param bool  $use_proximity Default value.
	 * @param array $args          Query args.
	 * @return bool
	 */
	public function maybe_skip_proximity( $use_proximity, $args ) {
		if ( isset( $args['_google_address_use_terms'] ) && $args['_google_address_use_terms'] ) {
			return false;
		}
		return $use_proximity;
	}
}

<?php
/**
 * CubeWP Keyword Suggestions
 *
 * Handles keyword suggestions based on taxonomy settings
 *
 * @package cubewp-framework
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

class CubeWp_Keyword_Suggestions {

	/**
	 * Initialize the class
	 */
	public static function init() {
		$instance = new self();
	}

	public function __construct() {
		// Add AJAX handlers
		new CubeWp_Ajax( '', __CLASS__, 'get_keyword_suggestions' );
		new CubeWp_Ajax( 'wp_ajax_nopriv_', __CLASS__, 'get_keyword_suggestions' );
		new CubeWp_Ajax( '', __CLASS__, 'get_default_keyword_suggestions' );
		new CubeWp_Ajax( 'wp_ajax_nopriv_', __CLASS__, 'get_default_keyword_suggestions' );

		// Hook into keyword field rendering
		add_filter( 'cubewp/frontend/search/text/field', array( $this, 'modify_keyword_field' ), 20, 2 );
		add_filter( 'cubewp/frontend/search/s/field', array( $this, 'modify_keyword_field' ), 20, 2 );

		// Enqueue scripts when search forms are rendered
		add_action( 'cubewp_loaded', array( $this, 'hook_search_form_enqueue' ) );
	}

	/**
	 * Hook into search form rendering to enqueue scripts
	 */
	public function hook_search_form_enqueue() {
		// Hook into shortcode search form rendering
		add_filter( 'cubewp_search_shortcode_output', array( $this, 'enqueue_on_search_render' ), 5, 2 );
		
		// Hook into frontend search fields rendering
		add_action( 'cubewp/frontend/search/form', array( $this, 'enqueue_on_search_render' ), 5, 3 );
	}

	/**
	 * Enqueue scripts when search form is rendered
	 */
	public function enqueue_on_search_render( $output = '', $atts = array(), $search_fields = array() ) {
		// Only enqueue once per page
		if ( ! wp_script_is( 'cubewp-keyword-suggestions', 'enqueued' ) ) {
			$this->enqueue_scripts();
		}
		return $output;
	}

	/**
	 * Enqueue scripts and styles for keyword suggestions
	 * Can be called statically or as instance method
	 */
	public function enqueue_scripts() {
		// Prevent multiple enqueues
		if ( wp_script_is( 'cubewp-keyword-suggestions', 'enqueued' ) ) {
			return;
		}
		wp_enqueue_script(
			'cubewp-keyword-suggestions',
			CWP_PLUGIN_URI . 'cube/assets/frontend/js/keyword-suggestions.js',
			array( 'jquery' ),
			'1.0.2',
			true
		);

		// wp_enqueue_style(
		// 	'cubewp-keyword-suggestions',
		// 	CWP_PLUGIN_URI . 'cube/assets/frontend/css/keyword-suggestions.css',
		// 	array(),
		// 	'1.0.0'
		// );

		// Get all post types with their taxonomy settings
		$post_types_settings = array();
		$cwpOptions = get_option( 'cwpOptions' );
		$all_post_types = CWP_all_post_types( 'settings' );

		foreach ( $all_post_types as $post_type_slug => $post_type_label ) {
			$enable_key = 'enable_keywords_suggestions_' . $post_type_slug;
			$taxonomy_key = 'keywords_suggestions_taxonomy_' . $post_type_slug;

			if ( isset( $cwpOptions[ $enable_key ] ) && $cwpOptions[ $enable_key ] ) {
				$taxonomies = isset( $cwpOptions[ $taxonomy_key ] ) ? $cwpOptions[ $taxonomy_key ] : array();
				if ( ! empty( $taxonomies ) ) {
					if ( ! is_array( $taxonomies ) ) {
						$taxonomies = array( $taxonomies );
					}
					$post_types_settings[ $post_type_slug ] = $taxonomies;
				}
			}
		}

		// Localize script with AJAX URL and settings
		wp_localize_script(
			'cubewp-keyword-suggestions',
			'cubewpKeywordSuggestions',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'cubewp_keyword_suggestions_nonce' ),
				'settings'    => $post_types_settings,
				'loadingText' => __( 'Loading suggestions...', 'cubewp-framework' ),
			)
		);
	}

	/**
	 * Modify keyword field to add suggestions container
	 *
	 * @param string $output
	 * @param array  $args
	 *
	 * @return string
	 */
	public function modify_keyword_field( $output = '', $args = array() ) {

		if ( ! isset( $args['name'] ) || $args['name'] !== 's' ) {
			return $output;
		} 
    
        if ( strpos( $output, 'data-enable-suggestions' ) !== false ) {
            return $output; 
        }

		if ( ! wp_script_is( 'cubewp-keyword-suggestions', 'enqueued' ) ) {
			$this->enqueue_scripts();
		}

		$cwpOptions = get_option( 'cwpOptions' );
		$all_post_types = CWP_all_post_types( 'settings' );
		$has_enabled = false;

		foreach ( $all_post_types as $post_type_slug => $post_type_label ) {
			$enable_key = 'enable_keywords_suggestions_' . $post_type_slug;
			if ( isset( $cwpOptions[ $enable_key ] ) && $cwpOptions[ $enable_key ] ) {
				$has_enabled = true;
				break;
			}
		}

		if ( ! $has_enabled ) {
			return $output;
		}

		$data_attrs = 'data-enable-suggestions="1" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"';
		$output = str_replace( '<input', '<input ' . $data_attrs, $output );
		$suggestions_html = '<div class="cubewp-keyword-suggestions-container" style="display: none;"></div>';
		$output = str_replace( '</div>', $suggestions_html . '</div>', $output );
		$output = str_replace( 'name="s"', 'name="select"', $output );
		$hidden_s_field = '<input type="hidden" name="s" value="">';
		$output = str_replace( '<input', $hidden_s_field . '<input', $output );

		return $output;
	}

	/**
	 * Whether term meta holds a safe data:image …;base64, URI for use in img src.
	 * (WordPress esc_url() strips data: URLs, so we validate and use esc_attr() instead.)
	 *
	 * @param string $value Raw meta value.
	 * @return bool
	 */
	private static function is_term_icon_data_uri_image( $value ) {
		if ( ! is_string( $value ) || strlen( $value ) < 30 ) {
			return false;
		}
		return (bool) preg_match( '#^data:image/(png|gif|jpe?g|webp|svg\+xml);base64,#i', $value );
	}

	/**
	 * AJAX handler to get default keyword suggestions (when field is empty)
	 */
	public static function get_default_keyword_suggestions() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cubewp_keyword_suggestions_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'cubewp-framework' ) ) );
		}

		global $cwpOptions;

		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$taxonomies = isset( $_POST['taxonomies'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['taxonomies'] ) ) : array();

		if ( empty( $post_type ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing post type', 'cubewp-framework' ) ) );
		}

		$suggestions = array();

		$default_taxonomy_key = 'keywords_suggestions_default_taxonomy_' . $post_type;
		$default_taxonomy     = isset( $cwpOptions[ $default_taxonomy_key ] ) ? sanitize_text_field( $cwpOptions[ $default_taxonomy_key ] ) : '';

		// Empty keyword behavior: use selected "Default Taxonomy" only (single taxonomy, 10 terms).
		if ( ! empty( $default_taxonomy ) && taxonomy_exists( $default_taxonomy ) ) {
			$taxonomies = array( $default_taxonomy );
		} elseif ( ! empty( $taxonomies ) ) {
			$taxonomies = array_values( array_filter( array_map( 'sanitize_text_field', (array) $taxonomies ) ) );
		} else {
			$taxonomies = array();
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'orderby'    => 'count',
					'order'      => 'DESC',
					'number'     => 10,
				)
			);

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$taxonomy_obj   = get_taxonomy( $taxonomy );
				$taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $taxonomy;
				foreach ( $terms as $term ) {
					$term_icon_key = 'keywords_suggestions_icon_' . $taxonomy;
					if ( isset( $cwpOptions[ $term_icon_key ] ) ) {
						$term_icon = get_term_meta( $term->term_id, $cwpOptions[ $term_icon_key ], true );
					} else {
						$term_icon = '';
					}
					$term_icon = $term_icon ? $term_icon : '';
					// Font Awesome class, attachment ID, or data:image base64 URI.
					if ( strpos( $term_icon, 'fa-' ) !== false ) {
						$term_icon = '<i class="' . esc_attr( $term_icon ) . '"></i>';
					} elseif ( is_numeric( $term_icon ) ) {
						$attachment_url = wp_get_attachment_image_url( (int) $term_icon, 'thumbnail' );
						$term_icon      = $attachment_url
							? '<img src="' . esc_url( $attachment_url ) . '" alt="' . esc_attr( $term->name ) . '" />'
							: '';
					} elseif ( self::is_term_icon_data_uri_image( $term_icon ) ) {
						$term_icon = '<img src="' . esc_attr( $term_icon ) . '" alt="' . esc_attr( $term->name ) . '" />';
					} elseif ( is_string( $term_icon ) && strpos( $term_icon, 'http' ) === 0 ) {
						$term_icon = '<img src="' . esc_attr( $term_icon ) . '" alt="' . esc_attr( $term->name ) . '" />';
					} elseif ( empty( $term_icon ) ) {
						$term_icon = '';
					} else {
						$term_icon = '';
					}

					$suggestions[] = array(
						'id'             => $term->term_id,
						'name'           => $term->name,
						'slug'           => $term->slug,
						'taxonomy'       => $taxonomy,
						'taxonomy_label' => $taxonomy_label,
						'type'           => 'term',
						'thumbnail'      => '',
						'icon'           => $term_icon ? $term_icon : '',
					);
				}
			}

			// With default taxonomy selected we only use one taxonomy source.
			if ( ! empty( $default_taxonomy ) ) {
				break;
			}
		}

		wp_send_json_success( array( 'suggestions' => $suggestions ) );
	}

	/**
	 * AJAX handler to get keyword suggestions
	 */
	public static function get_keyword_suggestions() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cubewp_keyword_suggestions_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'cubewp-framework' ) ) );
		}

		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
		$taxonomies = isset( $_POST['taxonomies'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['taxonomies'] ) ) : array();

		if ( empty( $keyword ) || strlen( $keyword ) < 1 ) {
			wp_send_json_success( array( 'suggestions' => array() ) );
		}

		if ( empty( $post_type ) || empty( $taxonomies ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing parameters', 'cubewp-framework' ) ) );
		}

		$suggestions = array();

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			// Get terms matching the keyword
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'name__like' => $keyword,
					'hide_empty' => false,
					'number'     => 5, // Reduce to make room for posts
				)
			);
			global $cwpOptions;
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$taxonomy_obj   = get_taxonomy( $taxonomy );
				$taxonomy_label = $taxonomy_obj ? $taxonomy_obj->labels->singular_name : $taxonomy;
				foreach ( $terms as $term ) {
					$term_icon_key = 'keywords_suggestions_icon_' . $taxonomy;
					if ( isset( $cwpOptions[ $term_icon_key ] ) ) {
						$term_icon = get_term_meta( $term->term_id, $cwpOptions[ $term_icon_key ], true );
					} else {
						$term_icon = '';
					}
					$term_icon = $term_icon ? $term_icon : '';
					// Font Awesome class, attachment ID, or data:image base64 URI.
					if ( strpos( $term_icon, 'fa-' ) !== false ) {
						$term_icon = '<i class="' . esc_attr( $term_icon ) . '"></i>';
					} elseif ( is_numeric( $term_icon ) ) {
						$attachment_url = wp_get_attachment_image_url( (int) $term_icon, 'thumbnail' );
						$term_icon      = $attachment_url
							? '<img src="' . esc_url( $attachment_url ) . '" alt="' . esc_attr( $term->name ) . '" />'
							: '';
					} elseif ( self::is_term_icon_data_uri_image( $term_icon ) ) {
						$term_icon = '<img src="' . esc_attr( $term_icon ) . '" alt="' . esc_attr( $term->name ) . '" />';
					} elseif ( is_string( $term_icon ) && strpos( $term_icon, 'http' ) === 0 ) {
						$term_icon = '<img src="' . esc_attr( $term_icon ) . '" alt="' . esc_attr( $term->name ) . '" />';
					} elseif ( empty( $term_icon ) ) {
						$term_icon = '';
					} else {
						$term_icon = '';
					}

					$suggestions[] = array(
						'id'             => $term->term_id,
						'name'           => $term->name,
						'slug'           => $term->slug,
						'taxonomy'       => $taxonomy,
						'taxonomy_label' => $taxonomy_label,
						'type'           => 'term',
						'thumbnail'      => '',
						'icon'           => $term_icon ? $term_icon : '',
					);
				}
			}
		}

		// Also search for posts
		$post_args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			's'              => $keyword,
			'posts_per_page' => 5,
			'fields'         => 'ids',
		);

		$post_query = new WP_Query( $post_args );

        $post_type_obj = get_post_type_object( $post_type );
        $post_type_label = $post_type_obj && isset( $post_type_obj->labels->singular_name ) ? $post_type_obj->labels->singular_name : ucfirst( $post_type );


		if ( $post_query->have_posts() ) {
			foreach ( $post_query->posts as $post_id ) { 
				$post = get_post( $post_id );
				$thumbnail = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
				$suggestions[] = array(
					'id'       => $post_id,
					'name'     => get_the_title( $post_id ),
					'slug'     => $post->post_name,
					'taxonomy' => '',
					'taxonomy_label' => $post_type_label,
					'type'     => 'post',
					'url'      => get_permalink( $post_id ),
					'thumbnail' => $thumbnail ? $thumbnail : '',
				);
			}
		}

		// Separate posts and terms, then combine with terms first
		$posts = array();
		$terms = array();

		foreach ( $suggestions as $suggestion ) {
			if ( $suggestion['type'] === 'post' ) {
				$posts[] = $suggestion;
			} else {
				$terms[] = $suggestion;
			}
		}

		// Combine with terms first, limited to 10 total suggestions
		$combined_suggestions = array_merge( $terms, $posts );
		$final_suggestions = array_slice( $combined_suggestions, 0, 10 );

		wp_send_json_success( array( 'suggestions' => $final_suggestions ) );
	}

	/**
	 * Static method to enqueue scripts (for use in widgets/shortcodes)
	 */
	public static function enqueue_if_needed() {
		// Prevent multiple enqueues
		if ( wp_script_is( 'cubewp-keyword-suggestions', 'enqueued' ) ) {
			return;
		}

		// Get all post types with their taxonomy settings
		$post_types_settings = array();
		$cwpOptions = get_option( 'cwpOptions' );
		$all_post_types = CWP_all_post_types( 'settings' );

		foreach ( $all_post_types as $post_type_slug => $post_type_label ) {
			$enable_key = 'enable_keywords_suggestions_' . $post_type_slug;
			$taxonomy_key = 'keywords_suggestions_taxonomy_' . $post_type_slug;

			if ( isset( $cwpOptions[ $enable_key ] ) && $cwpOptions[ $enable_key ] ) {
				$taxonomies = isset( $cwpOptions[ $taxonomy_key ] ) ? $cwpOptions[ $taxonomy_key ] : array();
				if ( ! empty( $taxonomies ) ) {
					if ( ! is_array( $taxonomies ) ) {
						$taxonomies = array( $taxonomies );
					}
					$post_types_settings[ $post_type_slug ] = $taxonomies;
				}
			}
		}

		// Enqueue script
		wp_enqueue_script(
			'cubewp-keyword-suggestions',
			CWP_PLUGIN_URL . 'cube/assets/frontend/js/keyword-suggestions.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);

	 
		// Localize script with AJAX URL and settings
		wp_localize_script(
			'cubewp-keyword-suggestions',
			'cubewpKeywordSuggestions',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'cubewp_keyword_suggestions_nonce' ),
				'settings'    => $post_types_settings,
				'loadingText' => __( 'Loading suggestions...', 'cubewp-framework' ),
			)
		);
	}
}

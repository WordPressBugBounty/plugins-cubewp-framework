<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CubeWp_Addons_Admin {
	/**
	 * Submenu slug (matches `callback` in `CubeWp_Submenu`).
	 */
	const PAGE_SLUG = 'cubewp-addons';
	const NONCE_ACTION = 'cubewp_addons_action';
	/**
	 * Default hub base URL (override via `CUBEWP_HUB_BASE_URL` constant or filter).
	 *
	 * Example (local dev): define( 'CUBEWP_HUB_BASE_URL', 'http://localhost/cubewp-licensing/' );
	 */
	const DEFAULT_HUB_BASE_URL = 'https://cubewp.com/';

	const OPT_HUB_TOKEN   = 'cubewp_hub_token';
	const OPT_HUB_EXPIRES = 'cubewp_hub_token_expires';
	const OPT_HUB_EMAIL   = 'cubewp_hub_email';
	const OPT_HUB_ITEMS   = 'cubewp_hub_items';

	const STATE_PREFIX = 'cubewp_hub_state_';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_handle_hub_callback' ), 5 );
		add_action( 'wp_ajax_cubewp_addons_ajax', array( $this, 'handle_ajax' ) );
		add_action( 'admin_post_cubewp_hub_connect_start', array( $this, 'handle_hub_connect_start' ) );
		add_action( 'admin_post_cubewp_hub_disconnect', array( $this, 'handle_hub_disconnect' ) );
		add_action( 'admin_post_cubewp_hub_refresh', array( $this, 'handle_hub_refresh' ) );
		add_action( 'admin_post_cubewp_addons_install', array( $this, 'handle_install' ) );
		add_action( 'admin_post_cubewp_addons_activate', array( $this, 'handle_activate_plugin' ) );
		add_action( 'admin_post_cubewp_addons_deactivate', array( $this, 'handle_deactivate_plugin' ) );
		add_action( 'admin_post_cubewp_addons_license_activate', array( $this, 'handle_license_activate' ) );
		add_action( 'admin_post_cubewp_addons_license_deactivate', array( $this, 'handle_license_deactivate' ) );
		add_action( 'admin_post_cubewp_addons_license_check', array( $this, 'handle_license_check' ) );
		add_action( 'admin_post_cubewp_addons_install_theme', array( $this, 'handle_install_theme' ) );
		add_action( 'admin_post_cubewp_addons_activate_theme', array( $this, 'handle_activate_theme' ) );
	}

	public static function init() {
		$CubeClass = __CLASS__;
		new $CubeClass();
	}

	private function get_addons() {
		if ( ! function_exists( 'CWP' ) ) {
			return array();
		}

		$addons = CWP()->cubewp_get_modules();
		return is_array( $addons ) ? $addons : array();
	}

	private function get_store_url() {
		if ( class_exists( 'CubeWp_Add_Ons' ) ) {
			$obj = new CubeWp_Add_Ons();
			if ( ! empty( $obj->route ) ) {
				return $obj->route;
			}
		}
		return 'https://cubewp.com';
	}

	/**
	 * Site URL for EDD SL (activate / check / deactivate / get_version). Matches {@see CubeWp_Add_Ons::add_on_management()}.
	 *
	 * @return string
	 */
	private function edd_license_client_site_url() {
		return home_url();
	}

	/**
	 * Product fields for EDD API: same shape as CubeWp_Add_Ons (item_name sent with urlencode when set; else item_id).
	 *
	 * @param array<string, mixed> $addon Row with item_name / item_id.
	 * @return array<string, string>
	 */
	private function edd_sl_addon_product_params( array $addon ) {
		$params    = array();
		$item_name = isset( $addon['item_name'] ) ? trim( (string) $addon['item_name'] ) : '';
		$item_id   = isset( $addon['item_id'] ) ? trim( (string) $addon['item_id'] ) : '';

		if ( '' !== $item_name ) {
			$params['item_name'] = urlencode( $item_name );
		} elseif ( '' !== $item_id ) {
			$params['item_id'] = $item_id;
		}

		return $params;
	}

	/**
	 * Resolve EDD license post ID from the hub catalog for a slug + key (multi-license products).
	 *
	 * @param string $slug         Sanitized product slug.
	 * @param string $license_key License key.
	 * @return int 0 if unknown.
	 */
	private function hub_license_id_for_slug_and_license_key( $slug, $license_key ) {
		$slug        = sanitize_key( (string) $slug );
		$license_key = trim( (string) $license_key );
		if ( '' === $slug || '' === $license_key ) {
			return 0;
		}
		$item = $this->hub_item_for_slug( $slug );
		if ( ! is_array( $item ) ) {
			return 0;
		}
		if ( ! empty( $item['licenses'] ) && is_array( $item['licenses'] ) ) {
			foreach ( $item['licenses'] as $row ) {
				if ( is_array( $row ) && ! empty( $row['license_key'] ) && (string) $row['license_key'] === $license_key && ! empty( $row['license_id'] ) ) {
					return (int) $row['license_id'];
				}
			}
		}
		if ( ! empty( $item['license_key'] ) && (string) $item['license_key'] === $license_key && ! empty( $item['license_id'] ) ) {
			return (int) $item['license_id'];
		}
		return 0;
	}

	private function hub_base_url() {
		$base = self::DEFAULT_HUB_BASE_URL;

		if ( defined( 'CUBEWP_HUB_BASE_URL' ) ) {
			$const = constant( 'CUBEWP_HUB_BASE_URL' );
			if ( is_string( $const ) && ! empty( $const ) ) {
				$base = $const;
			}
		}

		/**
		 * Filter the hub base URL.
		 *
		 * Should be the site root (with or without trailing slash), not `/my-account/`.
		 *
		 * @param string $base Base URL (default https://cubewp.com/).
		 */
		$base = (string) apply_filters( 'cubewp_hub_base_url', $base );
		$base = trim( $base );

		// Ensure scheme/host present; fall back to default if invalid.
		$host = wp_parse_url( $base, PHP_URL_HOST );
		$scheme = wp_parse_url( $base, PHP_URL_SCHEME );
		if ( empty( $host ) || empty( $scheme ) ) {
			$base = self::DEFAULT_HUB_BASE_URL;
		}

		return trailingslashit( $base );
	}

	private function hub_my_account_url() {
		return home_url( '/my-account/' );
	}

	private function hub_api_base() {
		return trailingslashit( $this->hub_base_url() ) . 'wp-json/cubewp-hub/v1';
	}

	private function hub_host() {
		$host = (string) wp_parse_url( $this->hub_base_url(), PHP_URL_HOST );
		return $host;
	}

	/**
	 * OAuth-style redirect URI back to this site (must match what the hub stored for the auth code).
	 *
	 * @return string
	 */
	private function hub_oauth_redirect_uri() {
		return $this->hub_oauth_redirect_uri_for_page( self::PAGE_SLUG );
	}

	private function hub_oauth_redirect_uri_for_page( $page_slug ) {
		$page_slug = sanitize_key( (string) $page_slug );
		if ( empty( $page_slug ) ) {
			$page_slug = self::PAGE_SLUG;
		}
		return add_query_arg(
			array(
				'page'                => $page_slug,
				'cubewp_hub_callback' => '1',
			),
			admin_url( 'admin.php' )
		);
	}

	private function format_hub_api_error_notice( WP_Error $err ) {
		$msg  = $err->get_error_message();
		$data = $err->get_error_data();

		if ( is_array( $data ) ) {
			$code = isset( $data['status_code'] ) ? (int) $data['status_code'] : 0;
			$body = isset( $data['body'] ) ? (string) $data['body'] : '';
			$url  = isset( $data['url'] ) ? (string) $data['url'] : '';

			if ( $code ) {
				$msg .= ' (HTTP ' . $code . ')';
			}
			if ( ! empty( $url ) ) {
				$msg .= ' URL: ' . $url;
			}
			if ( ! empty( $body ) ) {
				$body = wp_strip_all_tags( $body );
				$body = preg_replace( '/\s+/', ' ', $body );
				$msg .= ' Response: ' . substr( $body, 0, 500 );
			}
		}

		return $msg;
	}

	private function hub_is_connected() {
		$token   = get_option( self::OPT_HUB_TOKEN, '' );
		$expires = (int) get_option( self::OPT_HUB_EXPIRES, 0 );
		// 0 = no local expiry (matches hub expires_in 0); still disconnected when token cleared.
		return is_string( $token ) && ! empty( $token ) && ( 0 === $expires || $expires > time() );
	}

	private function hub_get_items() {
		$items = get_option( self::OPT_HUB_ITEMS, array() );
		return is_array( $items ) ? $items : array();
	}

	private function hub_item_for_slug( $slug ) {
		$slug = sanitize_key( (string) $slug );
		foreach ( $this->hub_get_items() as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			if ( isset( $item['slug'] ) && sanitize_key( (string) $item['slug'] ) === $slug ) {
				return $item;
			}
		}
		return null;
	}

	/**
	 * All license keys from the hub snapshot for a slug (flat + `licenses` list).
	 *
	 * @return array<int, string>
	 */
	private function hub_license_keys_for_slug( $slug ) {
		$item = $this->hub_item_for_slug( $slug );
		if ( ! is_array( $item ) ) {
			return array();
		}
		$keys = array();
		if ( ! empty( $item['licenses'] ) && is_array( $item['licenses'] ) ) {
			foreach ( $item['licenses'] as $row ) {
				if ( is_array( $row ) && ! empty( $row['license_key'] ) ) {
					$keys[] = (string) $row['license_key'];
				}
			}
		}
		if ( ! empty( $item['license_key'] ) ) {
			$keys[] = (string) $item['license_key'];
		}
		$keys = array_values( array_unique( array_filter( $keys ) ) );
		return $keys;
	}

	/**
	 * @param int $license_id EDD SL license post ID.
	 */
	private function hub_license_id_is_allowed( $license_id ) {
		$license_id = (int) $license_id;
		if ( $license_id <= 0 ) {
			return false;
		}
		foreach ( $this->hub_get_items() as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			if ( ! empty( $item['licenses'] ) && is_array( $item['licenses'] ) ) {
				foreach ( $item['licenses'] as $row ) {
					if ( is_array( $row ) && (int) ( $row['license_id'] ?? 0 ) === $license_id ) {
						return true;
					}
				}
			}
			if ( (int) ( $item['license_id'] ?? 0 ) === $license_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Posted `license_key` (optional) must match a hub snapshot key for this slug.
	 *
	 * @param string               $slug
	 * @param array<string, mixed> $addon Catalog row.
	 * @return string|WP_Error
	 */
	private function resolve_license_key_for_slug_or_fail( $slug, $addon ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by the calling handler (verify_post()/handle_ajax()) before this helper runs.
		$posted = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['license_key'] ) ) : '';
		if ( $posted !== '' ) {
			$allowed = $this->hub_license_keys_for_slug( $slug );
			if ( ! in_array( $posted, $allowed, true ) ) {
				return new WP_Error(
					'cubewp_invalid_license_choice',
					__( 'That license key is not in your hub purchase list for this product.', 'cubewp-framework' )
				);
			}
			return $posted;
		}
		$key = $this->get_effective_license_key( $slug, $addon );
		if ( $key === '' ) {
			return new WP_Error(
				'cubewp_missing_license',
				__( 'Connect to CubeWP.com to sync a license first.', 'cubewp-framework' )
			);
		}
		return $key;
	}

	private function license_option_key( $slug ) {
		return sanitize_key( (string) $slug ).'_key';
	}

	private function base_option_key( $slug ) {
		return sanitize_key( (string) $slug ).'_base';
	}

	private function get_saved_base( $slug ) {
		$base = get_option( $this->base_option_key( $slug ), '' );
		return is_string( $base ) ? $base : '';
	}

	private function get_license_key( $slug ) {
		$key = get_option( $this->license_option_key( $slug ), '' );
		return is_string( $key ) ? $key : '';
	}

	/**
	 * Save `{slug}_key` only after install/activate — not when syncing the hub catalog.
	 * When connected to the hub, the key must exist in the current snapshot for that slug.
	 *
	 * @param string $slug        Addon/theme slug.
	 * @param string $license_key License key that was used for EDD on this site.
	 */
	private function persist_addon_license_key_for_slug( $slug, $license_key ) {
		$slug        = sanitize_key( (string) $slug );
		$license_key = trim( (string) $license_key );
		if ( '' === $slug || '' === $license_key ) {
			return;
		}
		if ( $this->hub_is_connected() ) {
			$allowed = $this->hub_license_keys_for_slug( $slug );
			if ( ! empty( $allowed ) && ! in_array( $license_key, $allowed, true ) ) {
				return;
			}
		}
		update_option( $this->license_option_key( $slug ), $license_key, false );
	}

	/**
	 * Hub snapshot first, then optional `{slug}_key` saved when the user installs/activates (see persist_addon_license_key_for_slug).
	 * When the hub lists multiple keys for one product, prefer the key already saved locally if it matches.
	 */
	private function get_effective_license_key( $slug, $addon ) {
		$item = $this->hub_item_for_slug( $slug );
		if ( ! is_array( $item ) ) {
			return $this->get_license_key( $slug );
		}
		$saved = $this->get_license_key( $slug );
		if ( ! empty( $item['licenses'] ) && is_array( $item['licenses'] ) ) {
			if ( $saved !== '' ) {
				foreach ( $item['licenses'] as $row ) {
					if ( is_array( $row ) && ! empty( $row['license_key'] ) && (string) $row['license_key'] === $saved ) {
						return (string) $row['license_key'];
					}
				}
			}
			$list = $item['licenses'];
			$last = end( $list );
			if ( is_array( $last ) && ! empty( $last['license_key'] ) ) {
				return (string) $last['license_key'];
			}
		}
		if ( ! empty( $item['license_key'] ) ) {
			return (string) $item['license_key'];
		}
		return $saved;
	}

	/**
	 * Catalog rows for the Addons screen.
	 *
	 * IMPORTANT (per requirements): do NOT use framework/modules.json as a catalog source.
	 * The customer site should show only what the hub returns (same as cubewp.com account page).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function get_catalog_rows() {
		$rows = array();

		foreach ( $this->hub_get_items() as $item ) {
			if ( ! is_array( $item ) || empty( $item['slug'] ) ) {
				continue;
			}
			$slug = sanitize_key( (string) $item['slug'] );
			$type = isset( $item['type'] ) && 'theme' === $item['type'] ? 'theme' : 'plugin';

			// Only show plugins/themes like cubewp.com account page.
			if ( ! in_array( $type, array( 'plugin', 'theme' ), true ) ) {
				continue;
			}

			$rows[ $slug ] = array(
				'item_name'  => isset( $item['item_name'] ) ? (string) $item['item_name'] : $slug,
				'slug'       => $slug,
				'base'       => isset( $item['base'] ) ? (string) $item['base'] : $this->get_saved_base( $slug ),
				'item_id'    => isset( $item['download_id'] ) ? (string) $item['download_id'] : ( isset( $item['item_id'] ) ? (string) $item['item_id'] : '' ),
				'type'       => $type,
				'stylesheet' => isset( $item['stylesheet'] ) ? (string) $item['stylesheet'] : $slug,
				'_source'    => 'hub',
				'_hub'       => $item,
			);
		}

		return $rows;
	}

	/**
	 * Prefer EDD download ID from hub snapshot when modules.json has no item_id.
	 *
	 * @param array<string,mixed> $row Catalog row.
	 * @return array<string,mixed>
	 */
	private function addon_for_edd_api( $row ) {
		if ( ! is_array( $row ) ) {
			return array();
		}
		$a = $row;
		if ( ! empty( $row['_hub']['download_id'] ) ) {
			$a['item_id'] = (string) $row['_hub']['download_id'];
		}
		return $a;
	}

	private function hub_api_request( $method, $path, $body = null ) {
		$method = strtoupper( (string) $method );
		$url    = trailingslashit( $this->hub_api_base() ) . ltrim( (string) $path, '/' );

		$args = array(
			'timeout'   => 30,
			'sslverify' => false,
			'method'    => $method,
			'headers'   => array(
				'Accept' => 'application/json',
			),
		);

		$token = get_option( self::OPT_HUB_TOKEN, '' );
		if ( is_string( $token ) && ! empty( $token ) ) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}

		if ( null !== $body ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                   = wp_json_encode( $body );
		}

		$res = wp_remote_request( $url, $args );
		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$code = (int) wp_remote_retrieve_response_code( $res );
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( $code < 200 || $code >= 300 || ! is_array( $data ) ) {
			return new WP_Error(
				'cubewp_hub_api_error',
				__( 'Hub API request failed.', 'cubewp-framework' ),
				array(
					'status_code' => $code,
					'body'        => wp_remote_retrieve_body( $res ),
					'url'         => $url,
				)
			);
		}

		return $data;
	}

	public function handle_hub_connect_start() {
		$this->ensure_admin_cap();
		check_admin_referer( 'cubewp_hub_connect' );

		$back_url = wp_get_referer();
		$back_url = is_string( $back_url ) ? $back_url : '';
		$back_url = esc_url_raw( $back_url );

		$explicit_back = isset( $_POST['cwp_hub_back'] ) ? sanitize_key( wp_unslash( $_POST['cwp_hub_back'] ) ) : '';
		if ( ! empty( $explicit_back ) ) {
			$back_url = admin_url( 'admin.php?page=' . $explicit_back );
		}

		$target_page = self::PAGE_SLUG;
		if ( ! empty( $explicit_back ) ) {
			$target_page = $explicit_back;
		} elseif ( is_string( $back_url ) && false !== strpos( $back_url, 'page=cube_wp_dashboard' ) ) {
			$target_page = 'cube_wp_dashboard';
		}

		// Allow redirecting to the CubeWP hub domain.
		$hub_host = $this->hub_host();
		add_filter(
			'allowed_redirect_hosts',
			static function ( $hosts ) use ( $hub_host ) {
				if ( ! empty( $hub_host ) ) {
					$hosts[] = $hub_host;
					$hosts[] = 'www.' . $hub_host;
				}
				return array_values( array_unique( $hosts ) );
			}
		);

		$state = wp_generate_password( 32, false, false );
		$redirect_uri = $this->hub_oauth_redirect_uri_for_page( $target_page );
		// Persist the exact redirect_uri we send to hub so token exchange can reuse it verbatim.
		set_transient(
			self::STATE_PREFIX . md5( $state ),
			array(
				'user_id'      => get_current_user_id(),
				'redirect_uri' => $redirect_uri,
				'back_url'     => $back_url,
			),
			600
		);

		$url          = add_query_arg(
			array(
				'cubewp_connect' => '1',
				// Don't pre-encode; `add_query_arg()` will encode.
				'redirect_uri'   => $redirect_uri,
				'state'          => $state,
				'site_url'       => home_url(),
				'site_name'      => get_bloginfo( 'name' ),
			),
			trailingslashit( $this->hub_base_url() ) . 'my-account/'
		);

		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	public function handle_hub_disconnect() {
		$this->ensure_admin_cap();
		check_admin_referer( 'cubewp_hub_disconnect' );

		delete_option( self::OPT_HUB_TOKEN );
		delete_option( self::OPT_HUB_EXPIRES );
		delete_option( self::OPT_HUB_EMAIL );
		delete_option( self::OPT_HUB_ITEMS );

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => rawurlencode( __( 'Disconnected from CubeWP.com.', 'cubewp-framework' ) ) ) ) );
		exit;
	}

	public function handle_hub_refresh() {
		$this->ensure_admin_cap();
		check_admin_referer( 'cubewp_hub_refresh' );
		$ref = $this->hub_refresh_items();
		if ( is_wp_error( $ref ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => rawurlencode( $this->format_hub_api_error_notice( $ref ) ) ) ) );
			exit;
		}
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => rawurlencode( __( 'Refreshed products from hub.', 'cubewp-framework' ) ) ) ) );
		exit;
	}

	private function hub_refresh_items() {
		if ( ! $this->hub_is_connected() ) {
			return false;
		}
		$data = $this->hub_api_request( 'GET', '/products' );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
		update_option( self::OPT_HUB_ITEMS, $items, false );

		return true;
	}

	public function maybe_handle_hub_callback() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from CubeWP.com; CSRF is enforced by the user-bound state transient validated below.
		if ( empty( $_GET['cubewp_hub_callback'] ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from CubeWP.com; CSRF is enforced by the user-bound state transient validated below.
		if ( empty( $_GET['code'] ) || empty( $_GET['state'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from CubeWP.com; CSRF is enforced by the user-bound state transient validated below.
		$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth callback from CubeWP.com; CSRF is enforced by the user-bound state transient validated below.
		$state = sanitize_text_field( wp_unslash( $_GET['state'] ) );

		$t = get_transient( self::STATE_PREFIX . md5( $state ) );
		if ( ! is_array( $t ) || empty( $t['user_id'] ) || (int) $t['user_id'] !== (int) get_current_user_id() ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => rawurlencode( __( 'Connect session expired. Please try again.', 'cubewp-framework' ) ) ) ) );
			exit;
		}
		delete_transient( self::STATE_PREFIX . md5( $state ) );

		// Canonical callback URL (must match hub-side loose validation: host + path + page + cubewp_hub_callback).
		$redirect_uri = ! empty( $t['redirect_uri'] ) ? (string) $t['redirect_uri'] : $this->hub_oauth_redirect_uri();

		$token_data   = $this->hub_api_request(
			'POST',
			'/token',
			array(
				'code'         => $code,
				'redirect_uri' => $redirect_uri,
			)
		);

		if ( is_wp_error( $token_data ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => rawurlencode( $this->format_hub_api_error_notice( $token_data ) ) ) ) );
			exit;
		}

		update_option( self::OPT_HUB_TOKEN, (string) ( $token_data['access_token'] ?? '' ), false );
		$expires_in = (int) ( $token_data['expires_in'] ?? 0 );
		$expires_at = ( $expires_in > 0 ) ? ( time() + $expires_in ) : 0;
		update_option( self::OPT_HUB_EXPIRES, $expires_at, false );
		update_option( self::OPT_HUB_EMAIL, (string) ( $token_data['email'] ?? '' ), false );

		$ref = $this->hub_refresh_items();
		if ( is_wp_error( $ref ) ) {
			$to = ! empty( $t['back_url'] ) ? (string) $t['back_url'] : $this->back_url();
			$to = add_query_arg(
				array(
					'cwp_notice_type' => 'error',
					'cwp_notice'      => rawurlencode( $this->format_hub_api_error_notice( $ref ) ),
				),
				$to
			);
			wp_safe_redirect( $to );
			exit;
		}

		$to = ! empty( $t['back_url'] ) ? (string) $t['back_url'] : $this->back_url();
		$to = add_query_arg(
			array(
				'cwp_notice_type' => 'success',
				'cwp_notice'      => rawurlencode( __( 'Connected to CubeWP.com.', 'cubewp-framework' ) ),
			),
			$to
		);
		wp_safe_redirect( $to );
		exit;
	}

	private function get_theme_status( $stylesheet ) {
		$stylesheet = sanitize_key( (string) $stylesheet );
		if ( empty( $stylesheet ) ) {
			return 'unknown';
		}
		$current = get_option( 'stylesheet' );
		if ( $stylesheet === $current ) {
			return 'active';
		}
		if ( wp_get_theme( $stylesheet )->exists() ) {
			return 'installed';
		}
		return 'missing';
	}

	private function get_plugin_status( $base ) {
		// If the hub didn't provide a plugin base, treat as not installed so the UI can still show "Install".
		if ( empty( $base ) ) {
			return 'missing';
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( isset( $all_plugins[ $base ] ) ) {
			return is_plugin_active( $base ) ? 'active' : 'installed';
		}

		return 'missing';
	}

	private function guess_plugin_base_from_slug( $slug ) {
		$slug = sanitize_key( (string) $slug );
		if ( empty( $slug ) ) {
			return '';
		}
		// Common pattern for CubeWP addons: <dir>/<dir>.php
		return $slug . '/' . $slug . '.php';
	}

	private function detect_installed_plugin_base_for_slug( $slug ) {
		$slug = sanitize_key( (string) $slug );
		if ( empty( $slug ) ) {
			return '';
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		foreach ( get_plugins() as $file => $data ) {
			if ( sanitize_key( dirname( $file ) ) === $slug ) {
				return (string) $file;
			}
		}
		return '';
	}

	private function maybe_write_config_txt( $addon, $slug, $base ) {
		// CubeWP add-ons expect: <plugin>/cube/config.txt containing the license key.
		if ( empty( $base ) ) {
			return;
		}

		$license_key = $this->get_effective_license_key( $slug, $addon );
		if ( empty( $license_key ) ) {
			// For free add-ons, your modules.json contains a pre-shared key.
			if ( isset( $addon['license_type'], $addon['key'] ) && 'free' === $addon['license_type'] ) {
				$license_key = (string) $addon['key'];
			}
		}

		if ( empty( $license_key ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			return;
		}

		$plugin_dir = trailingslashit( WP_PLUGIN_DIR ) . trailingslashit( dirname( $base ) );
		$cube_dir   = trailingslashit( $plugin_dir ) . 'cube/';
		$file       = $cube_dir . 'config.txt';

		if ( ! $wp_filesystem->is_dir( $cube_dir ) ) {
			$wp_filesystem->mkdir( $cube_dir );
		}

		// Write/overwrite config.txt to match manual downloaded package behavior.
		$wp_filesystem->put_contents( $file, $license_key, FS_CHMOD_FILE );
	}

	private function ensure_admin_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to perform this action.', 'cubewp-framework' ) );
		}
	}

	private function verify_post() {
		$this->ensure_admin_cap();
		check_admin_referer( self::NONCE_ACTION );
	}

	private function back_url( $extra = array() ) {
		$url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		if ( ! empty( $extra ) ) {
			$url = add_query_arg( $extra, $url );
		}
		return $url;
	}

	public function handle_ajax() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, you are not allowed to perform this action.', 'cubewp-framework' ) ), 403 );
		}

		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$op = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
		if ( empty( $op ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing operation.', 'cubewp-framework' ) ), 400 );
		}

		$catalog = $this->get_catalog_rows();

		try {
			switch ( $op ) {
				case 'hub_refresh':
					$ref = $this->hub_refresh_items();
					if ( is_wp_error( $ref ) ) {
						wp_send_json_error(
							array(
								'message' => $ref->get_error_message(),
							),
							400
						);
					}
					wp_send_json_success( array( 'message' => __( 'Synced from hub.', 'cubewp-framework' ) ) );

				case 'hub_disconnect':
					delete_option( self::OPT_HUB_TOKEN );
					delete_option( self::OPT_HUB_EXPIRES );
					delete_option( self::OPT_HUB_EMAIL );
					delete_option( self::OPT_HUB_ITEMS );
					wp_send_json_success( array( 'message' => __( 'Signed out from hub.', 'cubewp-framework' ) ) );

				case 'plugin_install':
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
					$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $addon ) ) {
						wp_send_json_error( array( 'message' => __( 'Addon not found.', 'cubewp-framework' ) ), 404 );
					}

					$license_key = $this->get_effective_license_key( $slug, $addon );
					if ( empty( $license_key ) && isset( $addon['license_type'] ) && 'free' !== $addon['license_type'] ) {
						wp_send_json_error( array( 'message' => __( 'Connect to CubeWP.com to sync a license for this product.', 'cubewp-framework' ) ), 400 );
					}

					if ( ! empty( $license_key ) && ( empty( $addon['license_type'] ) || 'free' !== $addon['license_type'] ) ) {
						$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $addon ), $license_key );
						if ( is_wp_error( $ensured ) ) {
							wp_send_json_error( array( 'message' => $ensured->get_error_message() ), 400 );
						}
					}

					$package = $this->get_download_package_url( $this->addon_for_edd_api( $addon ), $license_key );
					if ( is_wp_error( $package ) ) {
						wp_send_json_error( array( 'message' => $package->get_error_message() ), 400 );
					}

					$pre = $this->preflight_package_url( $package );
					if ( is_wp_error( $pre ) ) {
						$data = $pre->get_error_data();
						$msg  = $pre->get_error_message();
						if ( is_array( $data ) && ! empty( $data['status_code'] ) ) {
							$msg .= ' (HTTP ' . (int) $data['status_code'] . ')';
						}
						if ( is_array( $data ) && ! empty( $data['body'] ) ) {
							$msg .= ' Response: ' . (string) $data['body'];
						}
						$msg .= ' Package: ' . $package;
						wp_send_json_error( array( 'message' => $msg ), 400 );
					}

					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
					require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

					WP_Filesystem();
					$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
					$result   = $upgrader->install( $package );
					if ( is_wp_error( $result ) || empty( $result ) ) {
						$msg = __( 'Installation failed.', 'cubewp-framework' );
						if ( is_wp_error( $result ) ) {
							$msg .= ' ' . $result->get_error_message();
						} elseif ( isset( $upgrader->skin ) && is_object( $upgrader->skin ) && ! empty( $upgrader->skin->result ) && is_wp_error( $upgrader->skin->result ) ) {
							$msg .= ' ' . $upgrader->skin->result->get_error_message();
						}
						$msg .= ' Package: ' . $package;
						wp_send_json_error( array( 'message' => $msg ), 400 );
					}

					$installed_base = '';
					if ( method_exists( $upgrader, 'plugin_info' ) ) {
						$installed_base = (string) $upgrader->plugin_info();
					}
					if ( empty( $installed_base ) && ! empty( $slug ) ) {
						$installed_base = $this->detect_installed_plugin_base_for_slug( $slug );
					}
					if ( ! empty( $installed_base ) ) {
						update_option( $this->base_option_key( $slug ), $installed_base, false );
						$base = $installed_base;
					}

					$this->maybe_write_config_txt( $addon, $slug, $base );
					if ( ! empty( $license_key ) ) {
						$this->persist_addon_license_key_for_slug( $slug, $license_key );
					}

					wp_send_json_success(
						array(
							'message' => __( 'Addon installed.', 'cubewp-framework' ),
							'base'    => (string) $base,
							'status'  => 'installed',
						)
					);

				case 'plugin_activate':
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
					if ( empty( $base ) ) {
						wp_send_json_error( array( 'message' => __( 'Missing plugin base.', 'cubewp-framework' ) ), 400 );
					}
					if ( ! empty( $slug ) ) {
						$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
						if ( is_array( $addon ) ) {
							$this->maybe_write_config_txt( $addon, $slug, $base );
							$lk = $this->get_effective_license_key( $slug, $addon );
							if ( ! empty( $lk ) && $this->catalog_addon_requires_edd_license( $addon ) ) {
								$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $addon ), $lk );
								if ( is_wp_error( $ensured ) ) {
									wp_send_json_error( array( 'message' => $ensured->get_error_message() ), 400 );
								}
							}
						}
					}
					$result = activate_plugin( $base );
					if ( is_wp_error( $result ) ) {
						wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
					}
					if ( ! empty( $slug ) ) {
						$addon_row = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
						if ( is_array( $addon_row ) ) {
							$lk = $this->get_effective_license_key( $slug, $addon_row );
							if ( ! empty( $lk ) ) {
								$this->persist_addon_license_key_for_slug( $slug, $lk );
							}
						}
					}
					wp_send_json_success( array( 'message' => __( 'Plugin activated.', 'cubewp-framework' ), 'status' => 'active' ) );

				case 'plugin_deactivate':
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
					$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
					if ( empty( $base ) ) {
						wp_send_json_error( array( 'message' => __( 'Missing plugin base.', 'cubewp-framework' ) ), 400 );
					}
					deactivate_plugins( $base, false, false );
					wp_send_json_success( array( 'message' => __( 'Plugin deactivated.', 'cubewp-framework' ), 'status' => 'installed' ) );

				case 'theme_install':
					$slug = isset( $_POST['theme_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_slug'] ) ) : '';
					$row  = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $row ) || empty( $row['type'] ) || 'theme' !== $row['type'] ) {
						wp_send_json_error( array( 'message' => __( 'Theme not found.', 'cubewp-framework' ) ), 404 );
					}
					$license_key = $this->get_effective_license_key( $slug, $row );
					if ( empty( $license_key ) ) {
						wp_send_json_error( array( 'message' => __( 'Connect to CubeWP.com to sync a license for this theme.', 'cubewp-framework' ) ), 400 );
					}

					$addon_for_api = $row;
					if ( ! empty( $row['_hub']['download_id'] ) ) {
						$addon_for_api['item_id'] = (string) $row['_hub']['download_id'];
					}

					$act = $this->ensure_edd_license_for_package_download( $addon_for_api, $license_key );
					if ( is_wp_error( $act ) ) {
						wp_send_json_error( array( 'message' => $act->get_error_message() ), 400 );
					}

					$package = $this->get_download_package_url( $addon_for_api, $license_key );
					if ( is_wp_error( $package ) ) {
						wp_send_json_error( array( 'message' => $package->get_error_message() ), 400 );
					}
					$pre = $this->preflight_package_url( $package );
					if ( is_wp_error( $pre ) ) {
						$data = $pre->get_error_data();
						$msg  = $pre->get_error_message();
						if ( is_array( $data ) && ! empty( $data['status_code'] ) ) {
							$msg .= ' (HTTP ' . (int) $data['status_code'] . ')';
						}
						if ( is_array( $data ) && ! empty( $data['body'] ) ) {
							$msg .= ' Response: ' . (string) $data['body'];
						}
						$msg .= ' Package: ' . $package;
						wp_send_json_error( array( 'message' => $msg ), 400 );
					}

					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
					require_once ABSPATH . 'wp-admin/includes/theme-install.php';

					WP_Filesystem();
					$upgrader = new Theme_Upgrader( new Automatic_Upgrader_Skin() );
					$result   = $upgrader->install( $package );
					if ( is_wp_error( $result ) || empty( $result ) ) {
						$msg = __( 'Theme installation failed.', 'cubewp-framework' );
						if ( is_wp_error( $result ) ) {
							$msg .= ' ' . $result->get_error_message();
						} elseif ( isset( $upgrader->skin ) && is_object( $upgrader->skin ) && ! empty( $upgrader->skin->result ) && is_wp_error( $upgrader->skin->result ) ) {
							$msg .= ' ' . $upgrader->skin->result->get_error_message();
						}
						$msg .= ' Package: ' . $package;
						wp_send_json_error( array( 'message' => $msg ), 400 );
					}
					$this->persist_addon_license_key_for_slug( $slug, $license_key );
					wp_send_json_success( array( 'message' => __( 'Theme installed.', 'cubewp-framework' ), 'status' => 'installed' ) );

				case 'theme_activate':
					$stylesheet = isset( $_POST['theme_stylesheet'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_stylesheet'] ) ) : '';
					if ( empty( $stylesheet ) ) {
						wp_send_json_error( array( 'message' => __( 'Missing theme.', 'cubewp-framework' ) ), 400 );
					}
					if ( ! current_user_can( 'switch_themes' ) ) {
						wp_send_json_error( array( 'message' => __( 'You cannot switch themes.', 'cubewp-framework' ) ), 403 );
					}
					$theme = wp_get_theme( $stylesheet );
					if ( ! $theme->exists() ) {
						wp_send_json_error( array( 'message' => __( 'Theme is not installed.', 'cubewp-framework' ) ), 400 );
					}
					$matched_slug = '';
					$matched_row    = null;
					foreach ( $catalog as $th_slug => $th_row ) {
						if ( ! is_array( $th_row ) || empty( $th_row['type'] ) || 'theme' !== $th_row['type'] ) {
							continue;
						}
						$ts = isset( $th_row['stylesheet'] ) ? (string) $th_row['stylesheet'] : '';
						if ( $ts !== '' && $ts === $stylesheet ) {
							$matched_slug = sanitize_key( (string) $th_slug );
							$matched_row  = $th_row;
							break;
						}
					}
					if ( null !== $matched_row ) {
						$lk = $this->get_effective_license_key( $matched_slug, $matched_row );
						if ( ! empty( $lk ) && $this->catalog_addon_requires_edd_license( $matched_row ) ) {
							$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $matched_row ), $lk );
							if ( is_wp_error( $ensured ) ) {
								wp_send_json_error( array( 'message' => $ensured->get_error_message() ), 400 );
							}
						}
					}
					switch_theme( $stylesheet );
					if ( null !== $matched_row ) {
						$lk = $this->get_effective_license_key( $matched_slug, $matched_row );
						if ( ! empty( $lk ) ) {
							$this->persist_addon_license_key_for_slug( $matched_slug, $lk );
						}
					}
					wp_send_json_success( array( 'message' => __( 'Theme activated.', 'cubewp-framework' ), 'status' => 'active' ) );

				case 'license_check':
				case 'license_activate':
				case 'license_deactivate':
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $addon ) ) {
						wp_send_json_error( array( 'message' => __( 'Addon not found.', 'cubewp-framework' ) ), 404 );
					}
					$license_key = $this->resolve_license_key_for_slug_or_fail( $slug, $addon );
					if ( is_wp_error( $license_key ) ) {
						wp_send_json_error( array( 'message' => $license_key->get_error_message() ), 400 );
					}
					$license_key = (string) $license_key;

					if ( 'license_check' === $op ) {
						$data = $this->check_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					} elseif ( 'license_deactivate' === $op ) {
						$data = $this->deactivate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					} else {
						// license_activate (Manage modal): same rules as package install — limits, check then activate, post-activate verify.
						if ( '' !== $license_key && $this->catalog_addon_requires_edd_license( $addon ) ) {
							$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $addon ), $license_key );
							if ( is_wp_error( $ensured ) ) {
								wp_send_json_error( array( 'message' => $ensured->get_error_message() ), 400 );
							}
						}
						$data = $this->check_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					}

					if ( is_wp_error( $data ) ) {
						wp_send_json_error( array( 'message' => $data->get_error_message() ), 400 );
					}

					$api_ok = $this->assert_edd_license_api_success( $data );
					if ( is_wp_error( $api_ok ) ) {
						wp_send_json_error( array( 'message' => $api_ok->get_error_message() ), 400 );
					}

					if ( 'license_activate' === $op ) {
						$act_ok = $this->assert_edd_license_allowed_for_site_response( $data );
						if ( is_wp_error( $act_ok ) ) {
							wp_send_json_error( array( 'message' => $act_ok->get_error_message() ), 400 );
						}
					}

					$lic_status = isset( $data['license'] ) ? (string) $data['license'] : '';
					$out        = array(
						'message' => __( 'License request completed.', 'cubewp-framework' ),
						'license' => $lic_status,
					);
					if ( 'license_activate' === $op && in_array( $lic_status, array( 'valid', 'active' ), true ) ) {
						$this->persist_addon_license_key_for_slug( $slug, $license_key );
						$this->maybe_write_config_txt( $addon, $slug, isset( $addon['base'] ) ? (string) $addon['base'] : '' );
					}
					// license_deactivate: EDD removes this URL from the license only. Keep `{slug}_key` (and any `{slug}_status`) so the product still knows which key to prefer for re-activation / installs.
					if ( 'license_deactivate' === $op && '' !== $lic_status ) {
						$status_key = sanitize_key( (string) $slug ) . '_status';
						if ( false !== get_option( $status_key, false ) ) {
							update_option( $status_key, $lic_status, false );
						}
					}
					wp_send_json_success( $out );

				case 'manage_details':
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $addon ) ) {
						wp_send_json_error( array( 'message' => __( 'Addon not found.', 'cubewp-framework' ) ), 404 );
					}
					$hub = $this->hub_item_for_slug( sanitize_key( $slug ) );
					if ( ! is_array( $hub ) ) {
						wp_send_json_error( array( 'message' => __( 'No license found for this product.', 'cubewp-framework' ) ), 400 );
					}

					$license_rows = array();
					if ( ! empty( $hub['licenses'] ) && is_array( $hub['licenses'] ) ) {
						$license_rows = $hub['licenses'];
					} elseif ( ! empty( $hub['license_key'] ) && ! empty( $hub['license_id'] ) ) {
						$license_rows = array(
							array(
								'license_key' => (string) $hub['license_key'],
								'license_id'  => (int) $hub['license_id'],
								'payment_id'  => isset( $hub['payment_id'] ) ? (int) $hub['payment_id'] : 0,
							),
						);
					}

					if ( empty( $license_rows ) ) {
						wp_send_json_error( array( 'message' => __( 'No license found for this product.', 'cubewp-framework' ) ), 400 );
					}

					$download_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : (string) $slug;
					$edd_addon     = $this->addon_for_edd_api( $addon );
					$licenses_out  = array();

					foreach ( $license_rows as $row ) {
						if ( ! is_array( $row ) ) {
							continue;
						}
						$lid = isset( $row['license_id'] ) ? (int) $row['license_id'] : 0;
						$lk  = isset( $row['license_key'] ) ? (string) $row['license_key'] : '';
						if ( $lid <= 0 || $lk === '' ) {
							continue;
						}

						$sites = $this->hub_get_license_sites( $lid );
						if ( is_wp_error( $sites ) ) {
							wp_send_json_error( array( 'message' => $sites->get_error_message() ), 400 );
						}
						$sites = is_array( $sites ) ? $sites : array();

						$check   = $this->check_license_for_site( $edd_addon, $lk );
						$summary = '';
						if ( ! is_wp_error( $check ) && is_array( $check ) ) {
							$parts = array();
							if ( isset( $check['license'] ) ) {
								$parts[] = 'Status: ' . (string) $check['license'];
							}
							if ( isset( $check['expires'] ) ) {
								$parts[] = 'Expires: ' . (string) $check['expires'];
							}
							if ( isset( $check['site_count'] ) ) {
								$parts[] = 'Sites: ' . (string) $check['site_count'];
							}
							if ( isset( $check['license_limit'] ) ) {
								$parts[] = 'Limit: ' . (string) $check['license_limit'];
							}
							$summary = ! empty( $parts ) ? implode( ' ', $parts ) : '';
						}

						$lic_status = ( ! is_wp_error( $check ) && is_array( $check ) && isset( $check['license'] ) )
							? (string) $check['license']
							: '';
						$on_site    = in_array( $lic_status, array( 'valid', 'active' ), true );

						$licenses_out[] = array(
							'id'           => $lid,
							'key'          => $lk,
							'payment_id'   => isset( $row['payment_id'] ) ? (int) $row['payment_id'] : 0,
							'summary'      => $summary,
							'sites'        => $sites,
							'on_this_site' => $on_site,
						);
					}

					if ( empty( $licenses_out ) ) {
						wp_send_json_error( array( 'message' => __( 'No license found for this product.', 'cubewp-framework' ) ), 400 );
					}

					$n       = count( $licenses_out );
					$primary = $n > 0 ? $licenses_out[ $n - 1 ] : array();

					wp_send_json_success(
						array(
							'product'   => (string) $download_name,
							'licenses'  => $licenses_out,
							'license'   => array(
								'key'     => (string) ( $primary['key'] ?? '' ),
								'product' => (string) $download_name,
								'id'      => (int) ( $primary['id'] ?? 0 ),
								'sites'   => isset( $primary['sites'] ) && is_array( $primary['sites'] ) ? $primary['sites'] : array(),
								'summary' => (string) ( $primary['summary'] ?? '' ),
							),
						)
					);

				case 'manage_deactivate_site':
					$license_id = isset( $_POST['license_id'] ) ? absint( wp_unslash( $_POST['license_id'] ) ) : 0;
					$site_id    = isset( $_POST['site_id'] ) ? absint( wp_unslash( $_POST['site_id'] ) ) : 0;
					if ( empty( $license_id ) || empty( $site_id ) ) {
						wp_send_json_error( array( 'message' => __( 'Missing license or site.', 'cubewp-framework' ) ), 400 );
					}
					if ( ! $this->hub_license_id_is_allowed( $license_id ) ) {
						wp_send_json_error( array( 'message' => __( 'That license is not in your hub list.', 'cubewp-framework' ) ), 403 );
					}
					$res = $this->hub_deactivate_license_site( $license_id, $site_id );
					if ( is_wp_error( $res ) ) {
						wp_send_json_error( array( 'message' => $res->get_error_message() ), 400 );
					}
					wp_send_json_success( array( 'message' => __( 'Site deactivated.', 'cubewp-framework' ) ) );

				default:
					wp_send_json_error( array( 'message' => __( 'Unknown operation.', 'cubewp-framework' ) ), 400 );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ), 500 );
		}
	}

	private function hub_get_license_sites( $license_id ) {
		$license_id = absint( $license_id );
		if ( empty( $license_id ) ) {
			return new WP_Error( 'missing_license', __( 'Missing license.', 'cubewp-framework' ) );
		}
		return $this->hub_api_request( 'GET', '/licenses/' . $license_id . '/sites' );
	}

	private function hub_deactivate_license_site( $license_id, $site_id ) {
		$license_id = absint( $license_id );
		$site_id    = absint( $site_id );
		if ( empty( $license_id ) || empty( $site_id ) ) {
			return new WP_Error( 'missing_args', __( 'Missing license or site.', 'cubewp-framework' ) );
		}
		return $this->hub_api_request(
			'POST',
			'/licenses/' . $license_id . '/deactivate-site',
			array(
				'site_id' => $site_id,
			)
		);
	}

	// `pill()` kept for backwards compatibility (unused by the card UI).
	private function pill( $label, $variant = 'muted' ) {
		$cls = 'cubewp-pill';
		if ( 'success' === $variant ) {
			$cls .= ' is-success';
		} elseif ( 'warn' === $variant ) {
			$cls .= ' is-warn';
		} elseif ( 'info' === $variant ) {
			$cls .= ' is-info';
		} else {
			$cls .= ' is-muted';
		}
		return '<span class="' . esc_attr( $cls ) . '">' . esc_html( $label ) . '</span>';
	}

	private function edd_api_request( array $params ) {
		$response = wp_remote_post(
			$this->get_store_url(),
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'body'      => $params,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( 200 !== (int) $code || ! is_array( $data ) ) {
			return new WP_Error( 'cubewp_addons_api_error', __( 'Unexpected response from licensing server.', 'cubewp-framework' ), array( 'status_code' => $code, 'body' => $body ) );
		}

		return $data;
	}

	/**
	 * Preflight check: ensure the package URL is downloadable (EDD SL can return a URL that later 403s).
	 *
	 * @param string $package_url
	 * @return true|WP_Error
	 */
	private function preflight_package_url( $package_url ) {
		$package_url = esc_url_raw( (string) $package_url );
		if ( empty( $package_url ) ) {
			return new WP_Error( 'cubewp_addons_bad_package', __( 'Invalid package URL.', 'cubewp-framework' ) );
		}

		$head = wp_remote_head(
			$package_url,
			array(
				'timeout'     => 30,
				'sslverify'   => false,
				'redirection' => 5,
				'headers'     => array(
					'Accept' => '*/*',
				),
			)
		);

		if ( is_wp_error( $head ) ) {
			return $head;
		}

		$code = (int) wp_remote_retrieve_response_code( $head );
		if ( $code >= 200 && $code < 300 ) {
			return true;
		}

		// Fetch a small body snippet to surface the real error (403/404 often returns HTML/JSON).
		$get = wp_remote_get(
			$package_url,
			array(
				'timeout'     => 30,
				'sslverify'   => false,
				'redirection' => 5,
				'headers'     => array(
					'Range'  => 'bytes=0-1023',
					'Accept' => '*/*',
				),
			)
		);

		$body = '';
		if ( ! is_wp_error( $get ) ) {
			$body = (string) wp_remote_retrieve_body( $get );
		}
		$body = wp_strip_all_tags( $body );
		$body = preg_replace( '/\s+/', ' ', $body );

		return new WP_Error(
			'cubewp_addons_package_http_error',
			__( 'Package download is not accessible.', 'cubewp-framework' ),
			array(
				'status_code' => $code,
				'body'        => substr( $body, 0, 500 ),
				'package'     => $package_url,
			)
		);
	}

	private function get_download_package_url( $addon, $license_key ) {
		$license_key = (string) $license_key;

		$params = array_merge(
			array(
				'edd_action' => 'get_version',
				'license'    => $license_key,
				'url'        => $this->edd_license_client_site_url(),
			),
			$this->edd_sl_addon_product_params( is_array( $addon ) ? $addon : array() )
		);

		$data = $this->edd_api_request( $params );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$pkg_ok = $this->assert_edd_get_version_allows_package( $data, trim( $license_key ) !== '' );
		if ( is_wp_error( $pkg_ok ) ) {
			return $pkg_ok;
		}

		if ( isset( $data['package'] ) && is_string( $data['package'] ) && ! empty( $data['package'] ) ) {
			return $data['package'];
		}

		return new WP_Error( 'cubewp_addons_no_package', __( 'No download package returned. Please verify the license is valid for this addon.', 'cubewp-framework' ), $data );
	}

	private function activate_license_for_site( $addon, $license_key ) {
		$license_key = (string) $license_key;
		$site_url    = $this->edd_license_client_site_url();
		$slug        = isset( $addon['slug'] ) ? sanitize_key( (string) $addon['slug'] ) : '';

		if ( $this->hub_is_connected() && '' !== $slug && '' !== $license_key ) {
			$lid = $this->hub_license_id_for_slug_and_license_key( $slug, $license_key );
			if ( $lid > 0 ) {
				$hub = $this->hub_api_request(
					'POST',
					'/licenses/' . $lid . '/activate-site',
					array( 'site_url' => $site_url )
				);
				if ( ! is_wp_error( $hub ) && is_array( $hub ) ) {
					return $hub;
				}
			}
		}

		$params = array_merge(
			array(
				'edd_action' => 'activate_license',
				'license'    => $license_key,
				'url'        => $site_url,
			),
			$this->edd_sl_addon_product_params( is_array( $addon ) ? $addon : array() )
		);

		return $this->edd_api_request( $params );
	}

	private function deactivate_license_for_site( $addon, $license_key ) {
		$params = array_merge(
			array(
				'edd_action' => 'deactivate_license',
				'license'    => (string) $license_key,
				'url'        => $this->edd_license_client_site_url(),
			),
			$this->edd_sl_addon_product_params( is_array( $addon ) ? $addon : array() )
		);

		$data = $this->edd_api_request( $params );
		return $data;
	}

	private function check_license_for_site( $addon, $license_key ) {
		$params = array_merge(
			array(
				'edd_action' => 'check_license',
				'license'    => (string) $license_key,
				'url'        => $this->edd_license_client_site_url(),
			),
			$this->edd_sl_addon_product_params( is_array( $addon ) ? $addon : array() )
		);

		return $this->edd_api_request( $params );
	}

	/**
	 * EDD SL uses "valid" / "active" when this site URL is licensed.
	 *
	 * @param string $status Raw status from API.
	 */
	private static function edd_license_status_allows_this_site( $status ) {
		$s = strtolower( trim( (string) $status ) );
		return in_array( $s, array( 'valid', 'active' ), true );
	}

	/**
	 * @param array<string, mixed> $data Decoded EDD SL JSON.
	 * @return true|WP_Error
	 */
	private function assert_edd_license_api_success( array $data ) {
		if ( isset( $data['success'] ) ) {
			$s = $data['success'];
			if ( false === $s || 0 === $s || '0' === $s || 0.0 === $s ) {
				$msg = ! empty( $data['error'] ) ? (string) $data['error'] : __( 'The licensing server rejected this request.', 'cubewp-framework' );
				return new WP_Error( 'cubewp_edd_license_rejected', $msg, $data );
			}
			if ( is_string( $s ) && 'false' === strtolower( $s ) ) {
				$msg = ! empty( $data['error'] ) ? (string) $data['error'] : __( 'The licensing server rejected this request.', 'cubewp-framework' );
				return new WP_Error( 'cubewp_edd_license_rejected', $msg, $data );
			}
		}
		return true;
	}

	/**
	 * After activate_license or check_license: require success + valid/active when a status is returned.
	 *
	 * @param array<string, mixed> $data
	 * @return true|WP_Error
	 */
	private function assert_edd_license_allowed_for_site_response( array $data ) {
		$r = $this->assert_edd_license_api_success( $data );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		if ( isset( $data['license'] ) ) {
			$lic = strtolower( trim( (string) $data['license'] ) );
			if ( '' !== $lic && ! self::edd_license_status_allows_this_site( $lic ) ) {
				$msg = ! empty( $data['error'] ) ? (string) $data['error'] : sprintf(
					/* translators: %s: license state from EDD Software Licensing */
					__( 'This license is not active for this site (%s). If your plan allows one site, deactivate it elsewhere or use a different license key.', 'cubewp-framework' ),
					$lic
				);
				return new WP_Error( 'cubewp_edd_license_state', $msg, $data );
			}
		}
		return true;
	}

	/**
	 * get_version responses must not report failure or a non-licensed state when a package is returned.
	 *
	 * @param array<string, mixed> $data              Decoded EDD SL JSON.
	 * @param bool                 $license_was_sent  When true, reject explicit non-active `license` values; if `license` is absent, allow when a non-empty `package` is returned (many stores omit `license` on get_version).
	 * @return true|WP_Error
	 */
	private function assert_edd_get_version_allows_package( array $data, $license_was_sent = true ) {
		$r = $this->assert_edd_license_api_success( $data );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		if ( $license_was_sent ) {
			if ( isset( $data['license'] ) && trim( (string) $data['license'] ) !== '' ) {
				$lic = strtolower( trim( (string) $data['license'] ) );
				if ( ! self::edd_license_status_allows_this_site( $lic ) ) {
					$msg = ! empty( $data['error'] ) ? (string) $data['error'] : __( 'Download is not permitted for this license on this site (activation limit or invalid license).', 'cubewp-framework' );
					return new WP_Error( 'cubewp_edd_package_license', $msg, $data );
				}
			} elseif ( ! isset( $data['package'] ) || ! is_string( $data['package'] ) || trim( $data['package'] ) === '' ) {
				// Many EDD SL builds omit `license` on get_version even when the download is allowed; only fail if there is no package either.
				return new WP_Error(
					'cubewp_edd_package_license_missing',
					__( 'The licensing server did not confirm this site for downloads (missing license state and no package URL). If this license is limited to one site, deactivate it elsewhere first.', 'cubewp-framework' ),
					$data
				);
			}
		} elseif ( isset( $data['license'] ) ) {
			$lic = strtolower( trim( (string) $data['license'] ) );
			if ( '' !== $lic && ! self::edd_license_status_allows_this_site( $lic ) ) {
				$msg = ! empty( $data['error'] ) ? (string) $data['error'] : __( 'Download is not permitted for this license on this site (activation limit or invalid license).', 'cubewp-framework' );
				return new WP_Error( 'cubewp_edd_package_license', $msg, $data );
			}
		}
		return true;
	}

	/**
	 * When EDD reports this URL as licensed, sanity-check site_count vs license_limit (fail closed on inconsistent data).
	 *
	 * @param array<string, mixed> $check check_license JSON.
	 * @return true|WP_Error
	 */
	private function assert_edd_check_license_site_cap_coherent( array $check ) {
		$st = isset( $check['license'] ) ? strtolower( trim( (string) $check['license'] ) ) : '';
		if ( ! self::edd_license_status_allows_this_site( $st ) ) {
			return true;
		}
		$limit = isset( $check['license_limit'] ) ? (int) $check['license_limit'] : 0;
		$count = isset( $check['site_count'] ) ? (int) $check['site_count'] : -1;
		if ( $limit > 0 && $count >= 0 && $count > $limit ) {
			return new WP_Error(
				'cubewp_edd_site_cap',
				__( 'This license reports more active sites than allowed. Deactivate it on another installation or contact support.', 'cubewp-framework' ),
				$check
			);
		}
		return true;
	}

	/**
	 * Re-check license state on the server after activate_license so we never treat a failed activation as success.
	 *
	 * @param array<string, mixed> $edd         Row for EDD API.
	 * @param string               $license_key License key.
	 * @return true|WP_Error
	 */
	private function assert_fresh_check_license_allows_download( array $edd, $license_key ) {
		$recheck = $this->check_license_for_site( $edd, $license_key );
		if ( is_wp_error( $recheck ) ) {
			return $recheck;
		}
		$r = $this->assert_edd_license_api_success( $recheck );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$r = $this->assert_edd_license_allowed_for_site_response( $recheck );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		return $this->assert_edd_check_license_site_cap_coherent( $recheck );
	}

	/**
	 * @param array<string, mixed> $addon Catalog row.
	 */
	private function catalog_addon_requires_edd_license( array $addon ) {
		return empty( $addon['license_type'] ) || 'free' !== $addon['license_type'];
	}

	/**
	 * Check current URL first; activate only when needed so EDD limits apply consistently (hub + legacy).
	 *
	 * @param array<string, mixed> $addon Row with item_id / item_name for API.
	 * @param string               $license_key
	 * @return true|WP_Error
	 */
	private function ensure_edd_license_for_package_download( array $addon, $license_key ) {
		$license_key = (string) $license_key;
		if ( '' === $license_key ) {
			return true;
		}
		$edd = $this->addon_for_edd_api( $addon );

		$check = $this->check_license_for_site( $edd, $license_key );
		if ( is_wp_error( $check ) ) {
			return $check;
		}
		$check_success = $this->assert_edd_license_api_success( $check );
		if ( is_wp_error( $check_success ) ) {
			return $check_success;
		}

		$st = isset( $check['license'] ) ? strtolower( trim( (string) $check['license'] ) ) : '';
		if ( self::edd_license_status_allows_this_site( $st ) ) {
			$r = $this->assert_edd_license_allowed_for_site_response( $check );
			if ( is_wp_error( $r ) ) {
				return $r;
			}
			$r = $this->assert_edd_check_license_site_cap_coherent( $check );
			if ( is_wp_error( $r ) ) {
				return $r;
			}
			return true;
		}

		$blocked = array( 'expired', 'disabled', 'invalid', 'revoked', 'missing' );
		if ( in_array( $st, $blocked, true ) ) {
			return new WP_Error(
				'cubewp_edd_license_terminal',
				sprintf(
					/* translators: %s: license state */
					__( 'License cannot be used on this site (%s).', 'cubewp-framework' ),
					$st
				),
				$check
			);
		}

		$act = $this->activate_license_for_site( $edd, $license_key );
		if ( is_wp_error( $act ) ) {
			return $act;
		}
		$r = $this->assert_edd_license_allowed_for_site_response( $act );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		return $this->assert_fresh_check_license_allows_download( $edd, $license_key );
	}

	private function notice_from_query() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice text from our own redirect; no state change.
		$type = isset( $_GET['cwp_notice_type'] ) ? sanitize_key( wp_unslash( $_GET['cwp_notice_type'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin notice text from our own redirect; no state change.
		$msg  = isset( $_GET['cwp_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['cwp_notice'] ) ) : '';
		if ( empty( $type ) || empty( $msg ) ) {
			return;
		}
		$cls = 'notice';
		if ( in_array( $type, array( 'success', 'warning', 'error', 'info' ), true ) ) {
			$cls .= ' notice-' . $type;
		}
		echo '<div class="' . esc_attr( $cls ) . '"><p>' . esc_html( $msg ) . '</p></div>';
	}

	public function handle_install() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';

		$catalog = $this->get_catalog_rows();
		$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
		if ( empty( $slug ) || ! is_array( $addon ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Addon not found in modules.json.' ) ) );
			exit;
		}

		$license_key = $this->get_effective_license_key( $slug, $addon );
		if ( empty( $license_key ) && isset( $addon['license_type'] ) && 'free' !== $addon['license_type'] ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'warning', 'cwp_notice' => __( 'Connect to CubeWP.com to sync a license for this product.', 'cubewp-framework' ) ) ) );
			exit;
		}

		// Ensure the license is activated for this site before requesting a package URL.
		if ( ! empty( $license_key ) && ( empty( $addon['license_type'] ) || 'free' !== $addon['license_type'] ) ) {
			$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $addon ), $license_key );
			if ( is_wp_error( $ensured ) ) {
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $ensured->get_error_message() ) ) );
				exit;
			}
		}

		$package = $this->get_download_package_url( $this->addon_for_edd_api( $addon ), $license_key );
		if ( is_wp_error( $package ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $package->get_error_message() ) ) );
			exit;
		}

		$pre = $this->preflight_package_url( $package );
		if ( is_wp_error( $pre ) ) {
			$data = $pre->get_error_data();
			$msg  = $pre->get_error_message();
			if ( is_array( $data ) && ! empty( $data['status_code'] ) ) {
				$msg .= ' (HTTP ' . (int) $data['status_code'] . ')';
			}
			if ( is_array( $data ) && ! empty( $data['body'] ) ) {
				$msg .= ' Response: ' . (string) $data['body'];
			}
			$msg .= ' Package: ' . $package;
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
			exit;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		WP_Filesystem();
		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $package );

		if ( is_wp_error( $result ) || empty( $result ) ) {
			$msg = __( 'Installation failed.', 'cubewp-framework' );
			if ( is_wp_error( $result ) ) {
				$msg .= ' ' . $result->get_error_message();
			} elseif ( isset( $upgrader->skin ) && is_object( $upgrader->skin ) && ! empty( $upgrader->skin->result ) && is_wp_error( $upgrader->skin->result ) ) {
				$msg .= ' ' . $upgrader->skin->result->get_error_message();
			}
			$msg .= ' Package: ' . $package;
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
			exit;
		}

		// Persist detected plugin base so UI can show Activate/Deactivate reliably.
		$installed_base = '';
		if ( method_exists( $upgrader, 'plugin_info' ) ) {
			$installed_base = (string) $upgrader->plugin_info();
		}
		if ( empty( $installed_base ) && ! empty( $slug ) ) {
			$installed_base = $this->detect_installed_plugin_base_for_slug( $slug );
		}
		if ( ! empty( $installed_base ) ) {
			update_option( $this->base_option_key( $slug ), $installed_base, false );
			$base = $installed_base;
		}

		// Ensure add-on activation passes CubeWP's own config.txt check.
		$this->maybe_write_config_txt( $addon, $slug, $base );
		if ( ! empty( $license_key ) ) {
			$this->persist_addon_license_key_for_slug( $slug, $license_key );
		}

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => 'Addon installed.' ) ) );
		exit;
	}

	public function handle_install_theme() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug = isset( $_POST['theme_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_slug'] ) ) : '';
		$catalog = $this->get_catalog_rows();
		$row     = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;

		if ( empty( $slug ) || ! is_array( $row ) || empty( $row['type'] ) || 'theme' !== $row['type'] ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Theme not found.' ) ) );
			exit;
		}

		$license_key = $this->get_effective_license_key( $slug, $row );
		if ( empty( $license_key ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'warning', 'cwp_notice' => __( 'Connect to CubeWP.com to sync a license for this theme.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$addon_for_api = $row;
		if ( ! empty( $row['_hub']['download_id'] ) ) {
			$addon_for_api['item_id'] = (string) $row['_hub']['download_id'];
		}

		// Ensure the license is activated for this site before requesting a package URL.
		if ( ! empty( $license_key ) ) {
			$ensured = $this->ensure_edd_license_for_package_download( $addon_for_api, $license_key );
			if ( is_wp_error( $ensured ) ) {
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $ensured->get_error_message() ) ) );
				exit;
			}
		}

		$package = $this->get_download_package_url( $addon_for_api, $license_key );
		if ( is_wp_error( $package ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $package->get_error_message() ) ) );
			exit;
		}

		$pre = $this->preflight_package_url( $package );
		if ( is_wp_error( $pre ) ) {
			$data = $pre->get_error_data();
			$msg  = $pre->get_error_message();
			if ( is_array( $data ) && ! empty( $data['status_code'] ) ) {
				$msg .= ' (HTTP ' . (int) $data['status_code'] . ')';
			}
			if ( is_array( $data ) && ! empty( $data['body'] ) ) {
				$msg .= ' Response: ' . (string) $data['body'];
			}
			$msg .= ' Package: ' . $package;
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
			exit;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/theme-install.php';

		WP_Filesystem();
		$upgrader = new Theme_Upgrader( new Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $package );

		if ( is_wp_error( $result ) || empty( $result ) ) {
			$msg = __( 'Theme installation failed.', 'cubewp-framework' );
			if ( is_wp_error( $result ) ) {
				$msg .= ' ' . $result->get_error_message();
			} elseif ( isset( $upgrader->skin ) && is_object( $upgrader->skin ) && ! empty( $upgrader->skin->result ) && is_wp_error( $upgrader->skin->result ) ) {
				$msg .= ' ' . $upgrader->skin->result->get_error_message();
			}
			$msg .= ' Package: ' . $package;
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
			exit;
		}

		$this->persist_addon_license_key_for_slug( $slug, $license_key );
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => __( 'Theme installed.', 'cubewp-framework' ) ) ) );
		exit;
	}

	public function handle_activate_theme() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$stylesheet = isset( $_POST['theme_stylesheet'] ) ? sanitize_text_field( wp_unslash( $_POST['theme_stylesheet'] ) ) : '';
		if ( empty( $stylesheet ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Missing theme.' ) ) );
			exit;
		}

		if ( ! current_user_can( 'switch_themes' ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => __( 'You cannot switch themes.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$theme = wp_get_theme( $stylesheet );
		if ( ! $theme->exists() ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => __( 'Theme is not installed.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$catalog      = $this->get_catalog_rows();
		$matched_slug = '';
		$matched_row  = null;
		foreach ( $catalog as $th_slug => $th_row ) {
			if ( ! is_array( $th_row ) || empty( $th_row['type'] ) || 'theme' !== $th_row['type'] ) {
				continue;
			}
			$ts = isset( $th_row['stylesheet'] ) ? (string) $th_row['stylesheet'] : '';
			if ( $ts !== '' && $ts === $stylesheet ) {
				$matched_slug = sanitize_key( (string) $th_slug );
				$matched_row  = $th_row;
				break;
			}
		}
		if ( null !== $matched_row ) {
			$lk = $this->get_effective_license_key( $matched_slug, $matched_row );
			if ( ! empty( $lk ) && $this->catalog_addon_requires_edd_license( $matched_row ) ) {
				$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $matched_row ), $lk );
				if ( is_wp_error( $ensured ) ) {
					wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $ensured->get_error_message() ) ) );
					exit;
				}
			}
		}

		switch_theme( $stylesheet );
		if ( null !== $matched_row ) {
			$lk = $this->get_effective_license_key( $matched_slug, $matched_row );
			if ( ! empty( $lk ) ) {
				$this->persist_addon_license_key_for_slug( $matched_slug, $lk );
			}
		}
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => __( 'Theme activated.', 'cubewp-framework' ) ) ) );
		exit;
	}

	public function handle_activate_plugin() {
		$this->verify_post();

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
		if ( empty( $base ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Missing plugin base.' ) ) );
			exit;
		}

		$addon = null;
		if ( ! empty( $slug ) ) {
			$catalog = $this->get_catalog_rows();
			$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
			if ( is_array( $addon ) ) {
				$this->maybe_write_config_txt( $addon, $slug, $base );
				$lk = $this->get_effective_license_key( $slug, $addon );
				if ( ! empty( $lk ) && $this->catalog_addon_requires_edd_license( $addon ) ) {
					$ensured = $this->ensure_edd_license_for_package_download( $this->addon_for_edd_api( $addon ), $lk );
					if ( is_wp_error( $ensured ) ) {
						wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $ensured->get_error_message() ) ) );
						exit;
					}
				}
			}
		}

		$result = activate_plugin( $base );
		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $result->get_error_message() ) ) );
			exit;
		}

		if ( ! empty( $slug ) && is_array( $addon ) ) {
			$lk = $this->get_effective_license_key( $slug, $addon );
			if ( ! empty( $lk ) ) {
				$this->persist_addon_license_key_for_slug( $slug, $lk );
			}
		}

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => 'Plugin activated.' ) ) );
		exit;
	}

	public function handle_deactivate_plugin() {
		$this->verify_post();

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
		if ( empty( $base ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Missing plugin base.' ) ) );
			exit;
		}

		deactivate_plugins( $base, false, false );
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => 'Plugin deactivated.' ) ) );
		exit;
	}

	public function handle_license_activate() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug    = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		$catalog = $this->get_catalog_rows();
		$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;

		if ( empty( $slug ) || ! is_array( $addon ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Addon not found.' ) ) );
			exit;
		}

		$license_key = $this->get_effective_license_key( $slug, $addon );
		if ( empty( $license_key ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'warning', 'cwp_notice' => __( 'Connect to CubeWP.com to sync a license first.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$data = $this->activate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
		if ( is_wp_error( $data ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $data->get_error_message() ) ) );
			exit;
		}
		$ok = $this->assert_edd_license_allowed_for_site_response( $data );
		if ( is_wp_error( $ok ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $ok->get_error_message() ) ) );
			exit;
		}

		$msg = isset( $data['license'] ) ? 'License: ' . $data['license'] : 'License activation requested.';
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => $msg ) ) );
		exit;
	}

	public function handle_license_deactivate() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug    = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		$catalog = $this->get_catalog_rows();
		$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;

		if ( empty( $slug ) || ! is_array( $addon ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Addon not found.' ) ) );
			exit;
		}

		$license_key = $this->get_effective_license_key( $slug, $addon );
		if ( empty( $license_key ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'warning', 'cwp_notice' => __( 'Connect to CubeWP.com to sync a license first.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$data = $this->deactivate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
		if ( is_wp_error( $data ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $data->get_error_message() ) ) );
			exit;
		}
		$deact_ok = $this->assert_edd_license_api_success( $data );
		if ( is_wp_error( $deact_ok ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $deact_ok->get_error_message() ) ) );
			exit;
		}

		$msg = isset( $data['license'] ) ? 'License: ' . $data['license'] : 'License deactivation requested.';
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => $msg ) ) );
		exit;
	}

	public function handle_license_check() {
		$this->verify_post();

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce and capability are verified by verify_post() at the top of this handler.
		$slug    = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		$catalog = $this->get_catalog_rows();
		$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;

		if ( empty( $slug ) || ! is_array( $addon ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Addon not found.' ) ) );
			exit;
		}

		$license_key = $this->get_effective_license_key( $slug, $addon );
		if ( empty( $license_key ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'warning', 'cwp_notice' => __( 'No synced license key found for this product.', 'cubewp-framework' ) ) ) );
			exit;
		}

		$data = $this->check_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
		if ( is_wp_error( $data ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $data->get_error_message() ) ) );
			exit;
		}
		$chk_ok = $this->assert_edd_license_api_success( $data );
		if ( is_wp_error( $chk_ok ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $chk_ok->get_error_message() ) ) );
			exit;
		}

		// Build a compact status message.
		$parts = array();
		if ( ! empty( $data['license'] ) ) {
			$parts[] = 'Status: ' . (string) $data['license'];
		}
		if ( isset( $data['expires'] ) && '' !== (string) $data['expires'] ) {
			$parts[] = 'Expires: ' . (string) $data['expires'];
		}
		if ( isset( $data['site_count'] ) ) {
			$parts[] = 'Sites: ' . (string) $data['site_count'];
		}
		if ( isset( $data['license_limit'] ) ) {
			$parts[] = 'Limit: ' . (string) $data['license_limit'];
		}
		$msg = ! empty( $parts ) ? implode( ' | ', $parts ) : __( 'License status checked.', 'cubewp-framework' );

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => $msg ) ) );
		exit;
	}
}
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
	 * Hub items first, then option populated by hub sync (no manual UI).
	 */
	private function get_effective_license_key( $slug, $addon ) {
		$item = $this->hub_item_for_slug( $slug );
		if ( is_array( $item ) && ! empty( $item['license_key'] ) ) {
			return (string) $item['license_key'];
		}
		return $this->get_license_key( $slug );
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

		wp_redirect( esc_url_raw( $url ) );
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

		// Also populate per-addon license options for existing installer logic/config.txt writer.
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) || empty( $item['slug'] ) || empty( $item['license_key'] ) ) {
				continue;
			}
			update_option( $this->license_option_key( $item['slug'] ), (string) $item['license_key'], false );
		}

		return true;
	}

	public function maybe_handle_hub_callback() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( empty( $_GET['cubewp_hub_callback'] ) ) {
			return;
		}
		if ( empty( $_GET['code'] ) || empty( $_GET['state'] ) ) {
			return;
		}

		$code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );
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
						$act = $this->activate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
						if ( is_wp_error( $act ) ) {
							wp_send_json_error( array( 'message' => $act->get_error_message() ), 400 );
						}
						$status = isset( $act['license'] ) ? (string) $act['license'] : '';
						if ( '' !== $status && ! in_array( $status, array( 'valid', 'active' ), true ) ) {
							$msg = __( 'License activation failed for this site.', 'cubewp-framework' ) . ' License: ' . $status;
							if ( ! empty( $act['error'] ) ) {
								$msg .= ' Error: ' . (string) $act['error'];
							}
							wp_send_json_error( array( 'message' => $msg ), 400 );
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
						}
					}
					$result = activate_plugin( $base );
					if ( is_wp_error( $result ) ) {
						wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
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

					$act = $this->activate_license_for_site( $addon_for_api, $license_key );
					if ( is_wp_error( $act ) ) {
						wp_send_json_error( array( 'message' => $act->get_error_message() ), 400 );
					}
					$status = isset( $act['license'] ) ? (string) $act['license'] : '';
					if ( '' !== $status && ! in_array( $status, array( 'valid', 'active' ), true ) ) {
						$msg = __( 'License activation failed for this site.', 'cubewp-framework' ) . ' License: ' . $status;
						if ( ! empty( $act['error'] ) ) {
							$msg .= ' Error: ' . (string) $act['error'];
						}
						wp_send_json_error( array( 'message' => $msg ), 400 );
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
					switch_theme( $stylesheet );
					wp_send_json_success( array( 'message' => __( 'Theme activated.', 'cubewp-framework' ), 'status' => 'active' ) );

				case 'license_check':
				case 'license_activate':
				case 'license_deactivate':
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $addon ) ) {
						wp_send_json_error( array( 'message' => __( 'Addon not found.', 'cubewp-framework' ) ), 404 );
					}
					$license_key = $this->get_effective_license_key( $slug, $addon );
					if ( empty( $license_key ) ) {
						wp_send_json_error( array( 'message' => __( 'Connect to CubeWP.com to sync a license first.', 'cubewp-framework' ) ), 400 );
					}

					if ( 'license_check' === $op ) {
						$data = $this->check_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					} elseif ( 'license_deactivate' === $op ) {
						$data = $this->deactivate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					} else {
						$data = $this->activate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					}

					if ( is_wp_error( $data ) ) {
						wp_send_json_error( array( 'message' => $data->get_error_message() ), 400 );
					}
					$lic_status = isset( $data['license'] ) ? (string) $data['license'] : '';
					wp_send_json_success(
						array(
							'message' => __( 'License request completed.', 'cubewp-framework' ),
							'license' => $lic_status,
						)
					);

				case 'manage_details':
					$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
					$addon = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
					if ( empty( $slug ) || ! is_array( $addon ) ) {
						wp_send_json_error( array( 'message' => __( 'Addon not found.', 'cubewp-framework' ) ), 404 );
					}
					$hub = $this->hub_item_for_slug( sanitize_key( $slug ) );
					$license_id = ( is_array( $hub ) && ! empty( $hub['license_id'] ) ) ? (int) $hub['license_id'] : 0;
					$license_key = $this->get_effective_license_key( $slug, $addon );

					if ( empty( $license_key ) || empty( $license_id ) ) {
						wp_send_json_error( array( 'message' => __( 'No license found for this product.', 'cubewp-framework' ) ), 400 );
					}

					$sites = $this->hub_get_license_sites( $license_id );
					if ( is_wp_error( $sites ) ) {
						wp_send_json_error( array( 'message' => $sites->get_error_message() ), 400 );
					}

					$download_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : (string) $slug;
					$check         = $this->check_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
					$summary       = '';
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
						$summary = ! empty( $parts ) ? implode( ' ' , $parts ) : '';
					}

					wp_send_json_success(
						array(
							'license' => array(
								'key'     => (string) $license_key,
								'product' => (string) $download_name,
								'id'      => (int) $license_id,
								'sites'   => is_array( $sites ) ? $sites : array(),
								'summary' => (string) $summary,
							),
						)
					);

				case 'manage_deactivate_site':
					$license_id = isset( $_POST['license_id'] ) ? absint( wp_unslash( $_POST['license_id'] ) ) : 0;
					$site_id    = isset( $_POST['site_id'] ) ? absint( wp_unslash( $_POST['site_id'] ) ) : 0;
					if ( empty( $license_id ) || empty( $site_id ) ) {
						wp_send_json_error( array( 'message' => __( 'Missing license or site.', 'cubewp-framework' ) ), 400 );
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
		$item_id   = isset( $addon['item_id'] ) ? (string) $addon['item_id'] : '';
		$item_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : '';

		$params = array(
			'edd_action' => 'get_version',
			'license'    => (string) $license_key,
			'url'        => home_url(),
		);

		// Prefer item_id if present (less ambiguous than item_name).
		if ( ! empty( $item_id ) ) {
			$params['item_id'] = $item_id;
		} else {
			$params['item_name'] = urlencode( $item_name );
		}

		$data = $this->edd_api_request( $params );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( isset( $data['package'] ) && is_string( $data['package'] ) && ! empty( $data['package'] ) ) {
			return $data['package'];
		}

		return new WP_Error( 'cubewp_addons_no_package', __( 'No download package returned. Please verify the license is valid for this addon.', 'cubewp-framework' ), $data );
	}

	private function activate_license_for_site( $addon, $license_key ) {
		$item_id   = isset( $addon['item_id'] ) ? (string) $addon['item_id'] : '';
		$item_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : '';

		$params = array(
			'edd_action' => 'activate_license',
			'license'    => (string) $license_key,
			'url'        => home_url(),
		);

		if ( ! empty( $item_id ) ) {
			$params['item_id'] = $item_id;
		} else {
			$params['item_name'] = urlencode( $item_name );
		}

		$data = $this->edd_api_request( $params );
		return $data;
	}

	private function deactivate_license_for_site( $addon, $license_key ) {
		$item_id   = isset( $addon['item_id'] ) ? (string) $addon['item_id'] : '';
		$item_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : '';

		$params = array(
			'edd_action' => 'deactivate_license',
			'license'    => (string) $license_key,
			'url'        => home_url(),
		);

		if ( ! empty( $item_id ) ) {
			$params['item_id'] = $item_id;
		} else {
			$params['item_name'] = urlencode( $item_name );
		}

		$data = $this->edd_api_request( $params );
		return $data;
	}

	private function check_license_for_site( $addon, $license_key ) {
		$item_id   = isset( $addon['item_id'] ) ? (string) $addon['item_id'] : '';
		$item_name = isset( $addon['item_name'] ) ? (string) $addon['item_name'] : '';

		$params = array(
			'edd_action' => 'check_license',
			'license'    => (string) $license_key,
			'url'        => home_url(),
		);

		if ( ! empty( $item_id ) ) {
			$params['item_id'] = $item_id;
		} else {
			$params['item_name'] = urlencode( $item_name );
		}

		return $this->edd_api_request( $params );
	}

	private function notice_from_query() {
		$type = isset( $_GET['cwp_notice_type'] ) ? sanitize_key( wp_unslash( $_GET['cwp_notice_type'] ) ) : '';
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

		$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
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
			$act = $this->activate_license_for_site( $this->addon_for_edd_api( $addon ), $license_key );
			if ( is_wp_error( $act ) ) {
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $act->get_error_message() ) ) );
				exit;
			}
			$status = isset( $act['license'] ) ? (string) $act['license'] : '';
			if ( '' !== $status && ! in_array( $status, array( 'valid', 'active' ), true ) ) {
				$msg = __( 'License activation failed for this site.', 'cubewp-framework' ) . ' License: ' . $status;
				if ( ! empty( $act['error'] ) ) {
					$msg .= ' Error: ' . (string) $act['error'];
				}
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
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

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => 'Addon installed.' ) ) );
		exit;
	}

	public function handle_install_theme() {
		$this->verify_post();

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
			$act = $this->activate_license_for_site( $addon_for_api, $license_key );
			if ( is_wp_error( $act ) ) {
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $act->get_error_message() ) ) );
				exit;
			}
			$status = isset( $act['license'] ) ? (string) $act['license'] : '';
			if ( '' !== $status && ! in_array( $status, array( 'valid', 'active' ), true ) ) {
				$msg = __( 'License activation failed for this site.', 'cubewp-framework' ) . ' License: ' . $status;
				if ( ! empty( $act['error'] ) ) {
					$msg .= ' Error: ' . (string) $act['error'];
				}
				wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $msg ) ) );
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

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => __( 'Theme installed.', 'cubewp-framework' ) ) ) );
		exit;
	}

	public function handle_activate_theme() {
		$this->verify_post();

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

		switch_theme( $stylesheet );
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => __( 'Theme activated.', 'cubewp-framework' ) ) ) );
		exit;
	}

	public function handle_activate_plugin() {
		$this->verify_post();

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$slug = isset( $_POST['addon_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_slug'] ) ) : '';
		$base = isset( $_POST['addon_base'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_base'] ) ) : '';
		if ( empty( $base ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => 'Missing plugin base.' ) ) );
			exit;
		}

		// If the plugin was installed earlier without config.txt, fix it before activation.
		if ( ! empty( $slug ) ) {
			$catalog = $this->get_catalog_rows();
			$addon   = isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : null;
			if ( is_array( $addon ) ) {
				$this->maybe_write_config_txt( $addon, $slug, $base );
			}
		}

		$result = activate_plugin( $base );
		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'error', 'cwp_notice' => $result->get_error_message() ) ) );
			exit;
		}

		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => 'Plugin activated.' ) ) );
		exit;
	}

	public function handle_deactivate_plugin() {
		$this->verify_post();

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

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

		$msg = isset( $data['license'] ) ? 'License: ' . $data['license'] : 'License activation requested.';
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => $msg ) ) );
		exit;
	}

	public function handle_license_deactivate() {
		$this->verify_post();

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

		$msg = isset( $data['license'] ) ? 'License: ' . $data['license'] : 'License deactivation requested.';
		wp_safe_redirect( $this->back_url( array( 'cwp_notice_type' => 'success', 'cwp_notice' => $msg ) ) );
		exit;
	}

	public function handle_license_check() {
		$this->verify_post();

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
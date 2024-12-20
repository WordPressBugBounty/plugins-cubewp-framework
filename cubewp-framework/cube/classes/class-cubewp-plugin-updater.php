<?php

// uncomment this line for testing
//set_site_transient( 'update_plugins', null );

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allows plugins to use their own update API.
 *
 * @author Pippin Williamson, Brian Hogg
 * @version 1.7?
 */
class CubeWp_Plugin_Updater {
	private $api_url        = '';
	private $api_data       = array();
	private $name           = '';
	private $slug           = '';
	private $version        = '';
	private $license_status = '';
	private $admin_page_url = '';
	private $purchase_url = '';
	private $plugin_title = '';

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 */
	function __construct( $_api_url, $_plugin_file, $_api_data = null, $_plugin_update_data = array() ) {
		$this->api_url = trailingslashit( $_api_url );
		$this->api_data = $_api_data;
		$this->name = plugin_basename( $_plugin_file );
		$this->slug = basename( $_plugin_file, '.php' );
		$this->version = $_api_data['version'];
		if ( is_array( $_plugin_update_data ) and isset( $_plugin_update_data[ 'license_status' ], $_plugin_update_data[ 'admin_page_url' ], $_plugin_update_data[ 'purchase_url' ], $_plugin_update_data[ 'plugin_title' ] ) ) {
			$this->license_status = $_plugin_update_data [ 'license_status' ];
			$this->admin_page_url = $_plugin_update_data[ 'admin_page_url' ];
			$this->purchase_url = $_plugin_update_data[ 'purchase_url' ];
			$this->plugin_title = $_plugin_update_data[ 'plugin_title' ];
		}
		//delete_site_transient('update_plugins');
		// Set up hooks.
		$this->init();
		add_action( 'admin_init', array( $this, 'show_changelog' ) );

	}

	/**
	 * Set up WordPress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );

		remove_action( 'after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10, 2 );

		if ( 'valid' != $this->license_status and $this->admin_page_url )
			// Remove the after_plugin_row_ action after it's added via the admin_init hook in wp_plugin_update_rows
			// But only if vars are set to show the alternate message
			add_action( 'admin_init', array( $this, 'remove_plugin_update_message' ), 99 );
	}

	function remove_plugin_update_message() {
		remove_action( 'after_plugin_row_' . $this->name, 'wp_plugin_update_row', 10, 2 );
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update API just when WordPress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native WordPress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses api_request()
	 *
	 * @param array   $_transient_data Update array build by WordPress.
	 * @return array Modified update array with custom plugin data.
	 */
	function check_update( $_transient_data ) {

		global $pagenow;

		if( ! is_object( $_transient_data ) ) {
			$_transient_data = new stdClass;
		}

		if( 'plugins.php' == $pagenow && is_multisite() ) {
			return $_transient_data;
		}

		if ( empty( $_transient_data->response ) || empty( $_transient_data->response[ $this->name ] ) ) {

			$version_info = $this->api_request( 'plugin_latest_version', array( 'slug' => $this->slug ) );

			if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {

				if( version_compare( $this->version, $version_info->new_version, '<' ) ) {

					$_transient_data->response[ $this->name ] = $version_info;

				}

				$_transient_data->last_checked = time();
				$_transient_data->checked[ $this->name ] = $this->version;

			}

		}
		return $_transient_data;
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed   $_data
	 * @param string  $_action
	 * @param object  $_args
	 * @return object $_data
	 */
	function plugins_api_filter( $_data, $_action = '', $_args = null ) {


		if ( $_action != 'plugin_information' ) {

			return $_data;

		}

		if ( ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {

			return $_data;

		}

		$to_send = array(
			'slug'   => $this->slug,
			'is_ssl' => is_ssl(),
			'fields' => array(
				'banners' => false, // These will be supported soon hopefully
				'reviews' => false
			)
		);

		$api_response = $this->api_request( 'plugin_information', $to_send );

		if ( false !== $api_response ) {
			$_data = $api_response;
		}

		return $_data;
	}


	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array   $args
	 * @param string  $url
	 * @return object $array
	 */
	function http_request_args( $args, $url ) {
		// If it is an https request and we are performing a package download, disable ssl verification
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = false;
		}
		return $args;
	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string  $_action The requested action.
	 * @param array   $_data   Parameters for the API action.
	 * @return false|object
	 */
	private function api_request( $_action, $_data ) {

		global $wp_version;

		$data = array_merge( $this->api_data, $_data );

		if ( $data['slug'] != $this->slug ) {
			return;
		}

		if( $this->api_url == home_url() ) {
			return false; // Don't allow a plugin to ping itself
		}

		$api_params = array(
			'edd_action' => 'get_version',
			'license'    => ! empty( $data['license'] ) ? $data['license'] : '',
			'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
			'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
			'slug'       => $data['slug'],
			'author'     => $data['author'],
			'url'        => home_url()
		);

		$request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ) );
		}

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			$request = false;
		}

		return $request;
	}

	public function show_changelog() {


		if( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
			return;
		}

		if( empty( $_REQUEST['plugin'] ) ) {
			return;
		}

		if( empty( $_REQUEST['slug'] ) ) {
			return;
		}

		if( ! current_user_can( 'update_plugins' ) ) {
			wp_die( __( 'You do not have permission to install plugin updates', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		$response = $this->api_request( 'plugin_latest_version', array( 'slug' => $_REQUEST['slug'] ) );

		if( $response && isset( $response->sections['changelog'] ) ) {
			echo '<div style="background:#fff;padding:10px;">' . $response->sections['changelog'] . '</div>';
		}


		exit;
	}

}

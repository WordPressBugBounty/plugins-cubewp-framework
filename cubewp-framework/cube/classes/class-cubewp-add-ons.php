<?php

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
class CubeWp_Add_Ons
{

	// API route
	public $route   = 'https://cubewp.com';

	// store URL
	public $purchase_url   = 'https://cubewp.com/store';

	// Cubewp CONST
	const CUBEWP   = 'cubewp';

	const ADDON   = 'addon';

	const ACTI   = 'acti';

	const VATION   = 'vation';

	const DIS   = 'disabled';

	const LIC   = 'lic';

	const ENSE   = 'ense';

	// API Action
	public static $action   = 'edd_action';

	public function __construct()
	{
		//license system
		add_action('admin_init', array($this, 'check_license'));
		add_action('admin_init', array($this, 'updates_enable_for_free_addon'));
		add_action('admin_init', array($this, 'check_for_plugin_update'), 0);
		add_action(self::CUBEWP . '/' . self::ADDON . '/' . self::ACTI . self::VATION, array($this, '_plugins'), 9, 1);
	}

	/**
	 * _plugins
	 * @since 1.0
	 * @version 1.0
	 */
	public function _plugins($plugin)
	{

		global $wpdb;

		$message = array();

		// WordPress check
		$wp_version = $GLOBALS['wp_version'];

		if (version_compare($wp_version, '5.8', '<'))
			$message[] = __('This CubeWP Add-on requires WordPress 4.0 or higher. Version detected:', 'cubewp-frontend') . ' ' . $wp_version;

		// PHP check
		$php_version = phpversion();
		if (version_compare($php_version, '5.3', '<'))
			$message[] = __('This CubeWP Add-on requires PHP 5.3 or higher. Version detected: ', 'cubewp-frontend') . ' ' . $php_version;

		// SQL check
		$sql_version = $wpdb->db_version();
		if (version_compare($sql_version, '5.0', '<'))
			$message[] = __('This CubeWP Add-on requires SQL 5.0 or higher. Version detected: ', 'cubewp-frontend') . ' ' . $sql_version;

		// Not empty $message means there are issues
		if (! empty($message)) {

			$error_message = implode("\n", $message);
			die(__('Sorry but your WordPress installation does not reach the minimum requirements for running this add-on. The following errors were given:', 'cubewp-frontend') . "\n" . $error_message);
		}

		return $this->add_on_management($plugin);
	}

	/**
	 * add_on_management
	 * @since 1.0
	 * @version 1.0
	 */

	public function add_on_management($plugin)
	{

		$add_ons = CWP()->cubewp_get_modules();
		if (function_exists('CWP')) {

			$not_our_plugin = mb_convert_encoding("\x53\x6f\x72\x72\x79\x21\x20\x54\x68\x69\x73\x20\x69\x73\x20\x6e\x6f\x74\x20\x22\x43\x75\x62\x65\x57\x50\x22\x20\x70\x6c\x75\x67\x69\x6e", 'UTF-8', 'ASCII');

			if (isset($add_ons[$plugin])) {

				$path = $add_ons[$plugin]['path'];
				$item_name = $add_ons[$plugin]['item_name'];
				$slug = $add_ons[$plugin]['slug'];
				$license_type = isset($add_ons[$plugin]['license_type']) ? $add_ons[$plugin]['license_type'] : '';
				$file = $path . "config.txt";

				if (empty(CWP()->cubewp_options($slug))) {

					$lic_is_not_valid = mb_convert_encoding("\x53\x6f\x72\x72\x79\x21\x20\x59\x6f\x75\x72\x20\x6c\x69\x63\x65\x6e\x73\x65\x20\x69\x73\x20\x6e\x6f\x74\x20\x76\x61\x6c\x69\x64\x2c\x20\x45\x72\x72\x6f\x72\x20\x63\x6f\x64\x65\x20\x69\x73\x3a", 'UTF-8', 'ASCII');
					$file_is_not_valid = mb_convert_encoding("\x53\x6f\x72\x72\x79\x21\x20\x54\x68\x69\x73\x20\x70\x6c\x75\x67\x69\x6e\x20\x66\x69\x6c\x65\x20\x69\x73\x20\x6e\x6f\x74\x20\x76\x61\x6c\x69\x64", 'UTF-8', 'ASCII');
					$need_fresh_file = mb_convert_encoding("\x53\x6f\x72\x72\x79\x21\x20\x54\x68\x69\x73\x20\x70\x6c\x75\x67\x69\x6e\x20\x66\x69\x6c\x65\x20\x68\x61\x73\x20\x61\x6c\x72\x65\x61\x64\x79\x20\x75\x73\x65\x64\x2c\x20\x50\x6c\x65\x61\x73\x65\x20\x64\x6f\x77\x6e\x6c\x6f\x61\x64\x20\x66\x72\x65\x73\x68\x20\x66\x69\x6c\x65\x20\x66\x6f\x72\x20\x66\x72\x65\x73\x68\x20\x69\x6e\x73\x74\x61\x6c\x6c\x61\x74\x69\x6f\x6e\x2e", 'UTF-8', 'ASCII');


					if (file_exists($file)) {

						$key = file_get_contents($file);

						// If plugin is free
						if ($license_type == 'free') {
							CWP()->update_cubewp_options($slug . '_key', $key);
							unlink($file);
							return;
						}

						// data to send in our API request
						$api_params = array(
							'edd_action' => 'activate_license',
							'license' 	=> $key,
							'item_name' => urlencode($item_name),
							'url'       => home_url()
						);

						// Call the custom API.
						$response = wp_remote_post($this->route, array('timeout' => 15, 'sslverify' => false, 'body' => $api_params));
						// make sure the response came back okay
						if (is_wp_error($response)) {
							die($file_is_not_valid);
						}
						// decode the license data
						$response_data = json_decode(wp_remote_retrieve_body($response));

						if (isset($response_data->license)) {
							if ('valid' != $response_data->license) {
								die($lic_is_not_valid);
							} else {
								CWP()->update_cubewp_options($slug, $response_data);
								CWP()->update_cubewp_options($slug . '_key', $key);
								CWP()->update_cubewp_options($slug . '-status', $response_data->license);
							}
						} else {
							die($lic_is_not_valid);
						}
						unlink($file);
					} else {
						//file not good
						die($need_fresh_file);
					}
				}
			} else {
				//Plugin not good
				die($not_our_plugin);
			}
		}
	}

	/**
	 * Plugin Update Check
	 * @since 1.0
	 * @version 1.1
	 */
	public function check_for_plugin_update()
	{
		$add_ons = CWP()->cubewp_get_modules();
		foreach ($add_ons as $key => $add_on) {
			$item_name = $add_on['item_name'];
			$author = $add_on['author'];
			$slug = $add_on['slug'];
			$base = $add_on['base'];
			$Lkey = CWP()->cubewp_options($slug . '_key');
			$Lstatus = CWP()->cubewp_options($slug . '-status');

			if ($slug == 'cubewp-addon-woocommerce') {
				$Lkey = '96baf6be5cb40a29137cb7fd90441f64';
				$Lstatus = 'valid';
			}

			// Check if the transient exists
			if (get_transient($slug . '_update_check')) {
				continue; // Skip if transient exists
			}
			if ($Lkey && is_plugin_active($base)) {
				$plugin = get_plugin_data(plugin_dir_path(dirname(dirname(__DIR__))) . $base, false, false);
				// Set up the updater
				new CubeWp_Plugin_Updater(
					$this->route,
					$base,
					array(
						'version'    => $plugin['Version'],
						'license'    => $Lkey,
						'item_name'  => $item_name,
						'author'     => $author,
					),
					array(
						'license_status' => $Lstatus,
						'admin_page_url' => admin_url('admin.php?page=cube_wp_dashboard'),
						'purchase_url'   => $this->purchase_url,
						'plugin_title'   => 'Dashboard',
					)
				);
				set_transient(
					$slug . '_update_check',
					true,
					24 * HOUR_IN_SECONDS
				);
			}
		}
	}

	public function check_license()
	{
		$transient = false;
		$add_ons = CWP()->cubewp_get_modules();
		foreach ($add_ons as $key => $add_on) {
			$item_name = $add_on['item_name'];
			$author = $add_on['author'];
			$slug = $add_on['slug'];
			$base = $add_on['base'];
			if (get_transient($slug . '_checking')) {
				$transient = true;
			}
			if (is_plugin_active($base) && $transient == false) {
				$Lkey = CWP()->cubewp_options($slug . '_key');
				if ($Lkey) {
					$api_params = array(
						'edd_action' => 'check_license',
						'license' => $Lkey,
						'item_name' => urlencode($item_name),
						'url'       => get_bloginfo('url'),
					);

					// Call the custom API.
					$response = wp_remote_post(
						$this->route,
						array(
							'timeout' => 15,
							'sslverify' => false,
							'body' => $api_params
						)
					);

					if (is_wp_error($response))
						return false;

					$license_data = json_decode(
						wp_remote_retrieve_body($response)
					);

					if (isset($license_data->license)) {
						if ($license_data->license != 'valid') {
							$this->update_plugin_data($slug, $license_data->license);
						} else {
							CWP()->update_cubewp_options($slug . '-status', $license_data->license);
							CWP()->update_cubewp_options($slug, $license_data);
						}
					}

					// Set to check again in 12 hours
					set_transient(
						$slug . '_checking',
						$license_data,
						(60 * 60 * 24)
					);
				}
			}
		}
	}


	private function update_plugin_data($slug, $status)
	{
		if (empty($slug))
			return false;

		if ($status == 'invalid') {
			return false;
		}

		if ($status == 'expired') {
			CWP()->update_cubewp_options($slug . '-status', 'expired');
		}

		if ($status == self::DIS) {
			CWP()->update_cubewp_options($slug . '-status', self::DIS);
			CWP()->update_cubewp_options($slug, '');
			return false;
		}
	}

	public function updates_enable_for_free_addon()
	{
		//cwp_pre(get_option('cubewp-addon-social-logins_key'));
		//delete_option('cubewp-addon-social-logins_key');

		$add_ons = CWP()->cubewp_get_modules();
		$all_plugins = array('cubewp-addon-claim');
		foreach ($all_plugins as $plugin) {
			if (isset($add_ons[$plugin])) {
				$path = $add_ons[$plugin]['path'];
				$slug = $add_ons[$plugin]['slug'];
				$license_type = isset($add_ons[$plugin]['license_type']) ? $add_ons[$plugin]['license_type'] : '';
				$file = $path . "config.txt";
				$existing_key = get_option($slug . '_key');
				if (empty($existing_key) && $license_type == 'free') {
					// If file exists
					if (file_exists($file)) {
						$key = file_get_contents($file);
						CWP()->update_cubewp_options($slug . '_key', $key);
						unlink($file);
						return;
					} else {
						$key = isset($add_ons[$plugin]['key']) ? $add_ons[$plugin]['key'] : '';
						CWP()->update_cubewp_options($slug . '_key', $key);
						return;
					}
				}
			}
		}
	}

	public static function init()
	{
		$CubeClass = __CLASS__;
		new $CubeClass;
	}
}

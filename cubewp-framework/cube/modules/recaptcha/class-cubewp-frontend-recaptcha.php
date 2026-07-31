<?php
/**
 * CubeWp Frontend Recaptcha.
 *
 * @version 1.0
 * @package cubewp/cube/modules/recaptcha
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

if (! defined('ABSPATH')) {
	exit;
}
/**
 * CubeWp_Frontend_Recaptcha
 */
class CubeWp_Frontend_Recaptcha
{

	private static $recaptcha = false;
	private static $recaptcha_type = '';
	private static $site_key = '';
	private static $secret_key = '';
	private static $hooks_registered = false;
	private static $config_loaded = false;

	private static function maybe_load_config()
	{
		if (self::$config_loaded) {
			return;
		}

		self::$config_loaded = true;

		global $cwpOptions;
		if (empty($cwpOptions) || ! is_array($cwpOptions)) {
			$cwpOptions = get_option('cwpOptions');
		}

		if (isset($cwpOptions['recaptcha']) && $cwpOptions['recaptcha'] == '1') {
			self::$recaptcha      = true;
			self::$recaptcha_type = isset($cwpOptions['recaptcha_type']) && ! empty($cwpOptions['recaptcha_type']) ? $cwpOptions['recaptcha_type'] : '';
			self::$site_key       = isset($cwpOptions['google_recaptcha_sitekey']) && ! empty($cwpOptions['google_recaptcha_sitekey']) ? $cwpOptions['google_recaptcha_sitekey'] : '';
			self::$secret_key     = isset($cwpOptions['google_recaptcha_secretkey']) && ! empty($cwpOptions['google_recaptcha_secretkey']) ? $cwpOptions['google_recaptcha_secretkey'] : '';
		}

		if (self::$recaptcha && ! self::$hooks_registered) {
			new self();
		}
	}

	private function register_hooks()
	{
		if (self::$hooks_registered) {
			return;
		}

		self::$hooks_registered = true;
		add_filter('frontend/script/register', array($this, 'cubewp_register_captcha_scripts'));
		add_filter('get_frontend_script_data', array($this, 'cubewp_localize_captcha_scripts'), 10, 2);
		add_filter('frontend/script/enqueue', array($this, 'cubewp_enqueue_captcha_scripts'));
	}

	public function __construct()
	{
		self::maybe_load_config();

		if (self::$recaptcha) {
			$this->register_hooks();
		}
	}

	private static function is_form_recaptcha_enabled($form_option = '')
	{
		return in_array($form_option, array('enabled', 'enable', '1'), true);
	}

	public function cubewp_register_captcha_scripts($scripts)
	{
		$register_scripts = array();
		if (self::$recaptcha_type == 'google_v2') {
			$register_scripts['cubewp-google-recaptcha-v2'] = array(
				'src'     => 'https://www.google.com/recaptcha/api.js?onload=cubewpCaptchaLoaded&render=explicit',
				'deps'    => array(),
				'version' => CUBEWP_VERSION,
			);
		}
		if (self::$recaptcha_type == 'google_v3' && ! empty(self::$site_key)) {
			$register_scripts['cubewp-google-recaptcha-v3'] = array(
				'src'     => 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode(self::$site_key),
				'deps'    => array(),
				'version' => CUBEWP_VERSION,
			);
		}
		$register_scripts['cubewp-recaptcha'] = array(
			'src'     => CWP_PLUGIN_URI . 'cube/assets/frontend/js/cubewp-recaptcha.js',
			'deps'    => array("jquery"),
			'version' => CUBEWP_VERSION,
		);

		return array_merge($register_scripts, $scripts);
	}

	public function cubewp_localize_captcha_scripts($data, $handle)
	{
		if ($handle == 'cubewp-recaptcha') {
			return array(
				'recaptcha_type' => self::$recaptcha_type,
				'site_key' => self::$site_key,
			);
		}

		return $data;
	}

	public function cubewp_enqueue_captcha_scripts($data)
	{
		if (self::$recaptcha_type == 'google_v2') {
			CubeWp_Enqueue::enqueue_script('cubewp-google-recaptcha-v2');
			CubeWp_Enqueue::enqueue_script('cubewp-recaptcha');
		}
		if (self::$recaptcha_type == 'google_v3') {
			CubeWp_Enqueue::enqueue_script('cubewp-google-recaptcha-v3');
			CubeWp_Enqueue::enqueue_script('cubewp-recaptcha');
		}
	}

	public static function cubewp_captcha_form_attributes($form_option = "enabled")
	{
		self::maybe_load_config();

		if (! self::$recaptcha || ! self::is_form_recaptcha_enabled($form_option)) {
			return '';
		}

		$output = '';
		if (self::$recaptcha_type == 'google_v2') {
			$output .= '<div class="cubewp-form-recaptcha" id="cubewp-form-recaptcha-' . wp_rand(000000000, 999999999) . '"></div>';
		}
		if (self::$recaptcha_type == 'google_v3') {
			$output .= '<input type="hidden" name="g-recaptcha-response" class="cubewp-form-recaptcha-v3" value="" data-recaptcha-action="cubewp_form_submit" />';
		}
		return $output;
	}

	public static function cubewp_captcha_verification($verification_of, $response)
	{
		if (empty($response)) {
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => esc_html__('Error! Please Check Captcha.', 'cubewp-framework'),
				)
			);
		}
		$verify_captcha = CubeWp_Frontend_Recaptcha::cubewp_verify_captcha_response($response);
		if (! $verify_captcha) {
			wp_send_json(
				array(
					'type' => 'error',
					'msg'  => esc_html__('Captcha Error! Please Disable VPN Or Try Again Later.', 'cubewp-framework'),
				)
			);
		}
	}

	public static function cubewp_verify_captcha_response($response)
	{
		self::maybe_load_config();

		if (! self::$recaptcha) {
			return true;
		}
		if (self::$recaptcha_type == 'google_v2' || self::$recaptcha_type == 'google_v3') {
			$verify_response = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'timeout' => 15,
					'body'    => array(
						'secret'   => self::$secret_key,
						'response' => $response,
					),
				)
			);
			if (is_wp_error($verify_response)) {
				return false;
			}
			$response_keys = json_decode(wp_remote_retrieve_body($verify_response), true);
			if (empty($response_keys['success'])) {
				return false;
			}
			if (self::$recaptcha_type == 'google_v3') {
				$score_threshold = apply_filters('cubewp/recaptcha/v3/score_threshold', 0.5);
				return isset($response_keys['score']) && (float) $response_keys['score'] >= (float) $score_threshold;
			}
			return true;
		}

		return false;
	}

	public static function init()
	{
		$CubeClass = __CLASS__;
		new $CubeClass;
	}
}
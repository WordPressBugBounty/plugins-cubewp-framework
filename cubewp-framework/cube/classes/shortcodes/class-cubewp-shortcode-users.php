<?php
defined('ABSPATH') || exit;

/**
 * CubeWP Users Shortcode.
 *
 * @class CubeWp_Shortcode_Users
 */
class CubeWp_Shortcode_Users
{
	public function __construct()
	{
		add_shortcode('cubewp_shortcode_users', array($this, 'cubewp_shortcode_users_callback'));
		add_filter('cubewp_shortcode_users_output', array($this, 'cubewp_users_output'), 10, 2);

		add_action('user_register', array($this, 'clear_users_cache'), 10, 1);
		add_action('profile_update', array($this, 'clear_users_cache'), 10, 1);
		add_action('delete_user', array($this, 'clear_users_cache'), 10, 1);
		add_action('elementor/document/before_save', array($this, 'clear_elementor_usercard_cache'), 10, 2);
		add_action('elementor/document/after_save', array($this, 'clear_elementor_usercard_cache'), 10, 2);
		add_action('update_option_cwpOptions', array($this, 'clear_users_cache_when_reenabled'), 10, 3);
	}

	public static function cubewp_users_output($output, $parameters = array())
	{
		if (empty($parameters)) {
			return $output;
		}

		$cache_enabled = self::is_users_cache_enabled();
		$skip_cache    = cubewp_is_elementor_editing() || ! is_singular() || ! $cache_enabled;

		if (! $skip_cache) {
			$cache_key      = self::get_users_cache_key($parameters);
			$cached_content = self::get_users_cache($cache_key);
			if ($cached_content !== false) {
				return $cached_content;
			}
		}

		$output_style = isset($parameters['output_style']) ? $parameters['output_style'] : 'boxed_view';
		$result       = '';

		if (strpos($output_style, '_vp_elmentor_user_') !== 0) {
			$result = self::cubewp_users_get_default_output($parameters, $output);
		} else {
			$result = self::cubewp_users_get_user_card_output($parameters, $output);
		}

		if (! $skip_cache && $cache_enabled && $result !== '') {
			$cache_key = self::get_users_cache_key($parameters);
			$cache_ttl = self::get_users_cache_ttl_hours() * HOUR_IN_SECONDS;
			self::set_users_cache($cache_key, $result, $cache_ttl);
		}

		return $result;
	}

	/**
	 * Boxed / list fallback when no Elementor user card is selected.
	 */
	public static function cubewp_users_get_default_output($parameters, $output)
	{
		$user_role = '';
		if (! empty($parameters['user_role'])) {
			$user_role = $parameters['user_role'];
		} elseif (! empty($parameters['taxonomy'])) {
			$user_role = $parameters['taxonomy'];
		}
		if (empty($user_role)) {
			return $output;
		}

		$output_style    = isset($parameters['output_style']) ? $parameters['output_style'] : 'boxed_view';
		$users_box_color = isset($parameters['users_box_color']) ? $parameters['users_box_color'] : array();
		$users           = cubewp_get_users_from_parameters($parameters);

		if (empty($users)) {
			return $output;
		}

		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		$container_attrs   = self::get_slider_container_attrs($parameters, $users, $cwp_enable_slider);
		$slider_class      = ($cwp_enable_slider === 'yes') ? 'cubewp-user-slider' : '';

		ob_start();
		?>
		<div class="cwp-users-list <?php echo esc_attr($slider_class); ?>"<?php echo $container_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php
			$counter = 0;
			foreach ($users as $user) {
				if (! $user instanceof WP_User) {
					continue;
				}
				$display_name = $user->display_name ? $user->display_name : $user->user_login;
				$author_url   = get_author_posts_url($user->ID);
				$avatar       = get_avatar($user->ID, 96);

				if ($output_style === 'list_view') {
					?>
					<div class="cwp-user-card">
						<div class="cwp-user-card-list">
							<?php echo wp_kses_post($avatar); ?>
							<a href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($display_name); ?></a>
						</div>
					</div>
					<?php
				} else {
					$color_count = ! empty($users_box_color) && is_array($users_box_color) ? count($users_box_color) : 0;
					$color       = (! empty($users_box_color[$counter]['user_box_color']))
						? sanitize_hex_color($users_box_color[$counter]['user_box_color'])
						: '#f5f5f5';
					$counter++;
					if ($color_count > 0 && $counter >= $color_count) {
						$counter = 0;
					}
					?>
					<div class="cwp-user-card">
						<div class="cwp-user-card-box">
							<div class="cwp-user-card-box-heading" style="background-color: <?php echo esc_attr($color); ?>">
								<?php echo wp_kses_post($avatar); ?>
								<a href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($display_name); ?></a>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function cubewp_users_get_user_card_output($parameters, $output)
	{
		$output_style  = isset($parameters['output_style']) ? $parameters['output_style'] : '';
		$template_id   = 0;
		$elementor_key = str_replace('_vp_elmentor_user_', '', $output_style);

		if (ctype_digit((string) $elementor_key)) {
			$template_id = (int) $elementor_key;
		} else {
			$maybe_post = get_page_by_path($elementor_key, OBJECT, 'cubewp-tb');
			if ($maybe_post && ! is_wp_error($maybe_post)) {
				$template_id = (int) $maybe_post->ID;
			} else {
				$by_name = get_posts(
					array(
						'post_type'      => 'cubewp-tb',
						'name'           => $elementor_key,
						'posts_per_page' => 1,
						'fields'         => 'ids',
					)
				);
				if (! empty($by_name)) {
					$template_id = (int) $by_name[0];
				}
			}
		}

		if ($template_id <= 0) {
			return $output;
		}

		$user_role = '';
		if (! empty($parameters['user_role'])) {
			$user_role = $parameters['user_role'];
		} elseif (! empty($parameters['taxonomy'])) {
			$user_role = $parameters['taxonomy'];
		}
		if (empty($user_role)) {
			return $output;
		}

		$users = cubewp_get_users_from_parameters($parameters);
		if (empty($users)) {
			return $output;
		}

		$cwp_enable_slider = isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '';
		if ($cwp_enable_slider === 'yes') {
			CubeWp_Enqueue::enqueue_style('cubewp-slick');
			CubeWp_Enqueue::enqueue_script('cubewp-slick');
		}

		$container_attrs = self::get_slider_container_attrs($parameters, $users, $cwp_enable_slider);
		$slider_class      = ($cwp_enable_slider === 'yes') ? 'cubewp-user-slider' : '';

		$users_output  = '<div class="cwp-users-list cwp-elementor-users-list ' . esc_attr($slider_class) . '"' . $container_attrs . '>';
		$user_settings = isset($parameters['user_grid_settings']) ? $parameters['user_grid_settings'] : array();
		$dynamic_css   = '';
		$user_count    = 1;

		static $processed_templates = array();

		foreach ($users as $user) {
			if (! $user instanceof WP_User) {
				continue;
			}

			global $cubewp_user;
			$cubewp_user   = $user;
			$matched_index = null;

			foreach ($user_settings as $index => $item) {
				if (isset($item['user_position']) && (int) $item['user_position'] === $user_count) {
					$matched_index = $index;
					break;
				}
			}

			$wrapper_class = 'cwp-user-card cwp-elementor-user-card';
			if ($matched_index !== null) {
				$unique_class = 'cwp-user-' . $user_count . '-' . wp_generate_password(4, false, false);
				$wrapper_class .= ' cwp-user-card-' . $unique_class;
				$dynamic_css .= self::build_per_user_grid_css($user_settings[ $matched_index ], $unique_class);
				$wrapper_attrs  = apply_filters('cubewp_user_card_wrapper_attributes', array(), $user);
				$users_output  .= self::build_user_card_wrapper_open($wrapper_class, $wrapper_attrs);
			} else {
				$wrapper_attrs = apply_filters('cubewp_user_card_wrapper_attributes', array(), $user);
				$users_output .= self::build_user_card_wrapper_open($wrapper_class, $wrapper_attrs);
			}

			if (! in_array($template_id, $processed_templates, true)) {
				ob_start();
				echo CubeWp_Theme_Builder::do_cubewp_theme_builder('usercard', $template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$users_output .= ob_get_clean();
				$processed_templates[] = $template_id;
			} else {
				ob_start();
				echo cwp_get_elementor_content_without_styles($template_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$users_output .= ob_get_clean();
			}

			$users_output .= '</div>';
			unset($GLOBALS['cubewp_user']);
			$user_count++;
		}

		$users_output .= '</div>';

		if (! empty($dynamic_css)) {
			$users_output .= '<style>@media (min-width: 1024px) {' . $dynamic_css . '}</style>';
		}

		return $users_output;
	}

	protected static function build_per_user_grid_css($settings, $unique_class)
	{
		$css_rules = '';
		if (! empty($settings['column_span'])) {
			$col = $settings['column_span'];
			$css_rules .= "grid-column: span {$col} / span {$col};";
		}
		if (! empty($settings['row_span'])) {
			$row = $settings['row_span'];
			$css_rules .= "grid-row: span {$row} / span {$row};";
		}
		if (! empty($settings['custom_margin']['top'])) {
			$m = $settings['custom_margin'];
			$css_rules .= 'margin:' . $m['top'] . $m['unit'] . ' ' . $m['right'] . $m['unit'] . ' ' . $m['bottom'] . $m['unit'] . ' ' . $m['left'] . $m['unit'] . ';';
		}
		if (! empty($settings['custom_padding']['top'])) {
			$p = $settings['custom_padding'];
			$css_rules .= 'padding:' . $p['top'] . $p['unit'] . ' ' . $p['right'] . $p['unit'] . ' ' . $p['bottom'] . $p['unit'] . ' ' . $p['left'] . $p['unit'] . ';';
		}
		if (! empty($settings['custom_width']['size'])) {
			$css_rules .= 'width:' . $settings['custom_width']['size'] . ($settings['custom_width']['unit'] ?? 'px') . ';';
		}
		if (! empty($settings['custom_height']['size'])) {
			$css_rules .= 'height:' . $settings['custom_height']['size'] . ($settings['custom_height']['unit'] ?? 'px') . ';';
		}
		if (empty($css_rules)) {
			return '';
		}
		return '.cwp-user-card-' . $unique_class . ' {' . $css_rules . '}';
	}

	protected static function get_slider_container_attrs($parameters, $users, &$cwp_enable_slider)
	{
		if ($cwp_enable_slider !== 'yes' || empty($users)) {
			return '';
		}

		$slides_to_show         = isset($parameters['slides_to_show']) ? (int) $parameters['slides_to_show'] : 3;
		$slides_to_show_mobile  = isset($parameters['slides_to_show_mobile']) ? (int) $parameters['slides_to_show_mobile'] : 1;
		$count                  = count($users);

		if (wp_is_mobile() && $count <= $slides_to_show_mobile) {
			$cwp_enable_slider = 'no';
			return '';
		}
		if (! wp_is_mobile() && $count <= $slides_to_show) {
			$cwp_enable_slider = 'no';
			return '';
		}

		$slides_to_scroll = isset($parameters['slides_to_scroll']) ? intval($parameters['slides_to_scroll']) : 1;
		$slides_to_show_tablet = isset($parameters['slides_to_show_tablet']) ? intval($parameters['slides_to_show_tablet']) : 3;
		$slides_to_show_tablet_portrait = isset($parameters['slides_to_show_tablet_portrait']) ? intval($parameters['slides_to_show_tablet_portrait']) : 2;
		$slides_to_scroll_tablet = isset($parameters['slides_to_scroll_tablet']) ? intval($parameters['slides_to_scroll_tablet']) : 1;
		$slides_to_scroll_tablet_portrait = isset($parameters['slides_to_scroll_tablet_portrait']) ? intval($parameters['slides_to_scroll_tablet_portrait']) : 1;
		$slides_to_scroll_mobile = isset($parameters['slides_to_scroll_mobile']) ? intval($parameters['slides_to_scroll_mobile']) : 1;
		$autoplay = isset($parameters['autoplay']) ? $parameters['autoplay'] : 'false';
		$autoplay_speed = isset($parameters['autoplay_speed']) ? intval($parameters['autoplay_speed']) : 2000;
		$speed = isset($parameters['speed']) ? intval($parameters['speed']) : 500;
		$infinite = (isset($parameters['infinite']) && $parameters['infinite'] === 'yes') ? 'true' : 'false';
		$fade_effect = (isset($parameters['fade_effect']) && $parameters['fade_effect'] === 'yes') ? 'true' : 'false';
		$draggable = (isset($parameters['draggable']) && $parameters['draggable'] === 'yes') ? 'true' : 'false';
		$variable_width = (isset($parameters['variable_width']) && $parameters['variable_width'] === 'yes') ? 'true' : 'false';
		$custom_arrows = (isset($parameters['custom_arrows']) && $parameters['custom_arrows'] === 'yes') ? 'true' : 'false';
		$enable_progress_bar = (isset($parameters['enable_progress_bar']) && $parameters['enable_progress_bar'] === 'yes') ? 'true' : 'false';
		$custom_dots = (isset($parameters['custom_dots']) && $parameters['custom_dots'] === 'yes') ? 'true' : 'false';
		$enable_wrap_dots_arrows = (isset($parameters['enable_wrap_dots_arrows']) && $parameters['enable_wrap_dots_arrows'] === true) ? 'true' : 'false';

		$prev_icon = isset($parameters['prev_icon']) ? $parameters['prev_icon'] : 'fas fa-chevron-left';
		$next_icon = isset($parameters['next_icon']) ? $parameters['next_icon'] : 'fas fa-chevron-right';
		$prev_icon = cubewp_get_svg_content($prev_icon);
		$next_icon = cubewp_get_svg_content($next_icon);

		$attrs  = '';
		$attrs .= ' data-slides-to-show="' . esc_attr($slides_to_show) . '"';
		$attrs .= ' data-slides-to-scroll="' . esc_attr($slides_to_scroll) . '"';
		$attrs .= ' data-slides-to-show-tablet="' . esc_attr($slides_to_show_tablet) . '"';
		$attrs .= ' data-slides-show-tablet-portrait="' . esc_attr($slides_to_show_tablet_portrait) . '"';
		$attrs .= ' data-slides-to-show-mobile="' . esc_attr($slides_to_show_mobile) . '"';
		$attrs .= ' data-slides-to-show-mobile="' . esc_attr($slides_to_show_mobile) . '"';
		$attrs .= ' data-slides-to-scroll-tablet="' . esc_attr($slides_to_scroll_tablet) . '"';
		$attrs .= ' data-slides-scroll-tablet-portrait="' . esc_attr($slides_to_scroll_tablet_portrait) . '"';
		$attrs .= ' data-slides-to-scroll-mobile="' . esc_attr($slides_to_scroll_mobile) . '"';
		$attrs .= ' data-autoplay="' . esc_attr($autoplay) . '"';
		$attrs .= ' data-autoplay-speed="' . esc_attr($autoplay_speed) . '"';
		$attrs .= ' data-speed="' . esc_attr($speed) . '"';
		$attrs .= ' data-infinite="' . esc_attr($infinite) . '"';
		$attrs .= ' data-draggable="' . esc_attr($draggable) . '"';
		$attrs .= ' data-fade="' . esc_attr($fade_effect) . '"';
		$attrs .= ' data-variable-width="' . esc_attr($variable_width) . '"';
		$attrs .= ' data-custom-arrows="' . esc_attr($custom_arrows) . '"';
		$attrs .= ' data-custom-dots="' . esc_attr($custom_dots) . '"';
		$attrs .= ' data-enable-progress-bar="' . esc_attr($enable_progress_bar) . '"';
		$attrs .= ' data-enable-wrapper="' . esc_attr($enable_wrap_dots_arrows) . '"';

		if (is_string($prev_icon) && strpos(trim($prev_icon), '<svg') === 0) {
			$attrs .= " data-prev-arrow-svg='" . esc_attr($prev_icon) . "' data-is-prev-svg='true'";
		} else {
			$attrs .= ' data-prev-arrow="' . esc_attr($prev_icon) . '" data-is-prev-svg="false"';
		}
		if (is_string($next_icon) && strpos(trim($next_icon), '<svg') === 0) {
			$attrs .= " data-next-arrow-svg='" . esc_attr($next_icon) . "' data-is-next-svg='true'";
		} else {
			$attrs .= ' data-next-arrow="' . esc_attr($next_icon) . '" data-is-next-svg="false"';
		}

		return $attrs;
	}

	protected static function build_user_card_wrapper_open($class_base, $attrs = array())
	{
		$attrs = is_array($attrs) ? $attrs : array();
		$class = $class_base;
		if (! empty($attrs['class'])) {
			$class .= ' ' . trim($attrs['class']);
			unset($attrs['class']);
		}
		$html = '<div class="' . esc_attr($class) . '"';
		foreach ($attrs as $key => $value) {
			if ($key === 'style' && is_string($value)) {
				$html .= ' style="' . esc_attr($value) . '"';
			} elseif (is_string($key) && is_string($value)) {
				$html .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
			}
		}
		$html .= '>';
		return $html;
	}

	private static function is_users_cache_enabled()
	{
		$cwpOptions = get_option('cwpOptions', array());
		$enabled    = isset($cwpOptions['cwp_users_enable_cache']) ? ! empty($cwpOptions['cwp_users_enable_cache']) : true;
		if (! isset($cwpOptions['cwp_users_enable_cache']) && isset($cwpOptions['cwp_taxonomy_enable_cache'])) {
			$enabled = ! empty($cwpOptions['cwp_taxonomy_enable_cache']);
		}
		return (bool) apply_filters('cubewp/users/cache_enabled', $enabled);
	}

	private static function get_users_cache_ttl_hours()
	{
		$cwpOptions = get_option('cwpOptions', array());
		$ttl_hours  = isset($cwpOptions['cwp_users_cache_ttl']) ? (int) $cwpOptions['cwp_users_cache_ttl'] : 12;
		if (! isset($cwpOptions['cwp_users_cache_ttl']) && isset($cwpOptions['cwp_taxonomy_cache_ttl'])) {
			$ttl_hours = (int) $cwpOptions['cwp_taxonomy_cache_ttl'];
		}
		return (int) apply_filters('cubewp/users/cache_ttl_hours', max(1, $ttl_hours));
	}

	private static function get_users_cache_key($parameters)
	{
		$key_parts = array(
			'cubewp_users',
			isset($parameters['user_role']) ? $parameters['user_role'] : (isset($parameters['taxonomy']) ? $parameters['taxonomy'] : ''),
			isset($parameters['output_style']) ? $parameters['output_style'] : 'boxed_view',
			isset($parameters['users_per_page']) ? $parameters['users_per_page'] : 0,
			isset($parameters['users_source']) ? $parameters['users_source'] : 'all',
			isset($parameters['user_ids']) ? $parameters['user_ids'] : '',
			isset($parameters['user_roles']) ? $parameters['user_roles'] : '',
			isset($parameters['orderby']) ? $parameters['orderby'] : 'registered',
			isset($parameters['order']) ? $parameters['order'] : 'DESC',
			isset($parameters['cwp_enable_slider']) ? $parameters['cwp_enable_slider'] : '',
			get_the_ID(),
			get_site_url(),
		);
		$key_parts = array_map(
			function ($v) {
				return is_array($v) || is_object($v) ? md5(serialize($v)) : (string) $v; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			},
			$key_parts
		);
		return 'cubewp_users_' . md5(implode('|', $key_parts));
	}

	private static function get_users_cache($cache_key)
	{
		if (wp_using_ext_object_cache()) {
			$cache_version_key = 'cubewp_users_version';
			$cache_version     = wp_cache_get($cache_version_key, 'cubewp_users');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_users', 0);
			}
			return wp_cache_get($cache_key . '_v' . $cache_version, 'cubewp_users');
		}
		return get_transient($cache_key);
	}

	private static function set_users_cache($cache_key, $cache_data, $expiration)
	{
		if (wp_using_ext_object_cache()) {
			$cache_version_key = 'cubewp_users_version';
			$cache_version     = wp_cache_get($cache_version_key, 'cubewp_users');
			if ($cache_version === false) {
				$cache_version = 1;
				wp_cache_set($cache_version_key, $cache_version, 'cubewp_users', 0);
			}
			wp_cache_set($cache_key . '_v' . $cache_version, $cache_data, 'cubewp_users', $expiration);
		} else {
			set_transient($cache_key, $cache_data, $expiration);
		}
	}

	public static function flush_users_cache()
	{
		if (wp_using_ext_object_cache()) {
			if (function_exists('wp_cache_flush_group')) {
				wp_cache_flush_group('cubewp_users');
			} else {
				$cache_version_key = 'cubewp_users_version';
				$current           = wp_cache_get($cache_version_key, 'cubewp_users');
				$current           = ($current === false) ? 1 : (int) $current;
				wp_cache_set($cache_version_key, $current + 1, 'cubewp_users', 0);
			}
		} else {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- No core API can enumerate transient names by prefix; static query with no user input, run only when flushing this plugin's own cache.
			$cache_keys = $wpdb->get_col(
				"SELECT option_name FROM {$wpdb->options}
				WHERE option_name LIKE '_transient_cubewp_users_%'
				OR option_name LIKE '_transient_timeout_cubewp_users_%'"
			);
			if (is_array($cache_keys)) {
				foreach ($cache_keys as $cache_key) {
					$transient_name = str_replace(array('_transient_', '_transient_timeout_'), '', $cache_key);
					delete_transient($transient_name);
				}
			}
		}
	}

	public function clear_users_cache($user_id = 0)
	{
		self::flush_users_cache();
	}

	public function clear_users_cache_when_reenabled($old_value, $value, $option)
	{
		if (! is_array($value) || empty($value['cwp_users_enable_cache'])) {
			return;
		}
		$was_enabled = is_array($old_value) && ! empty($old_value['cwp_users_enable_cache']);
		if ($was_enabled) {
			return;
		}
		self::flush_users_cache();
	}

	public function clear_elementor_usercard_cache($document, $data = array())
	{
		$post_id       = $document->get_main_id();
		$template_type = get_post_meta($post_id, 'template_type', true);
		if ($template_type === 'usercard') {
			$this->clear_users_cache($post_id);
		}
	}

	public static function init()
	{
		new self();
	}

	public function cubewp_shortcode_users_callback($parameters)
	{
		$title  = isset($parameters['title']) ? sanitize_text_field($parameters['title']) : '';
		$output = '<div class="cwp-widget-shortcode">';

		if (! empty($title)) {
			$output .= '<h2 class="cwp-widget-shortcode-heading">' . esc_html($title) . '</h2>';
		}

		$output .= apply_filters('cubewp_shortcode_users_output', '', $parameters);
		$output .= '</div>';

		return $output;
	}
}

<?php
defined('ABSPATH') || exit;

/**
 * Shortcode For CubeWP Feature Search.
 *
 * @class cubewp_Search_Shortcode
 */

class CubeWp_Shortcode_Search
{

	public $type;
	public $form_container_class;
	public $form_class;
	public $custom_fields;
	public $form_id;
	public $search_fields;
	public $all_setings;
	public $icon_html;

	public function __construct()
	{
		add_filter('cubewp_search_shortcode_output', array($this, 'cubewp_search'), 10, 2);
		add_filter('cubewp/frontend/search/button/field', array($this, 'cubewp_search_button'), 11, 2);
		add_filter('cubewp/frontend/search/form', array($this, 'cubewp_search_form_container'), 11, 3);
	}


	public static function init()
	{
		$current_class = __CLASS__;
		new $current_class;
	}


	public function cubewp_search($output, $atts)
	{

		$get_post_type = isset($atts['post_type']) ? $atts['post_type'] : 'post';
		$submit_button_icon = isset($atts['submit_button_icon']) ? $atts['submit_button_icon'] : '';
		$this->all_setings = isset($atts['settings']) ? $atts['settings'] : '';
		$get_settings =  $this->all_setings;

		$icon_htmls = '';
		if (! empty($submit_button_icon)) {
			if (class_exists('\Elementor\Icons_Manager')) {
				ob_start();
				\Elementor\Icons_Manager::render_icon($submit_button_icon, ['aria-hidden' => 'true']);
				$icon_htmls = ob_get_clean();
			}
		}
		// Pass icon HTML as a global variable for use in the button function 
		$this->icon_html = $icon_htmls;
		echo '<div class="cubewp-search-element">';
		// Prepare post types as array
		$post_types = is_array($get_post_type) ? $get_post_type : array($get_post_type);
		if (count($post_types) === 1) {
			echo do_shortcode('[cwpSearch type="' . esc_attr($post_types[0]) . '"]');
		} else {
			// Multiple post types, render tabbed interface
			echo '<div class="cubewp-search-form-wrapper">';
			echo '<ul class="nav nav-tabs cubewp-tabber-buttons border-0" id="cubewp_searchTab" role="tablist">';
			foreach ($post_types as $index => $post_type) {

				$tab_icons = isset($get_settings['tabber_button_icon_' . $post_type]) ? $get_settings['tabber_button_icon_' . $post_type] : '';

				$tab_icon_html = '';
				if (! empty($tab_icons)) {
					if (class_exists('\Elementor\Icons_Manager')) {
						ob_start();
						\Elementor\Icons_Manager::render_icon($tab_icons, ['aria-hidden' => 'true']);
						$tab_icon_html = ob_get_clean();
					}
				}

				$post_type_obj = get_post_type_object($post_type);
				$title = $post_type_obj ? $post_type_obj->labels->singular_name : ucfirst($post_type);
				$active = $index === 0 ? 'active' : '';
				$aria_selected = $index === 0 ? 'true' : 'false';
				echo '<li class="nav-item" role="presentation">';
				echo '<button class="tabber-btn nav-link ' . $active . '" id="tab-' . esc_attr($post_type) . '" data-bs-toggle="tab" data-bs-target="#tab-content-' . esc_attr($post_type) . '" type="button" role="tab" aria-controls="tab-content-' . esc_attr($post_type) . '" aria-selected="' . $aria_selected . '">' . $tab_icon_html . ' ' . esc_html($title) . '</button>';
				echo '</li>';
			}
			echo '</ul>';

			// Build tabs content
			echo '<div class="tab-content" id="cubewp_searchTabContent">';
			foreach ($post_types as $index => $post_type) {
				$active = $index === 0 ? 'show active' : '';
				echo '<div class="tab-pane fade ' . $active . '" id="tab-content-' . esc_attr($post_type) . '" role="tabpanel" aria-labelledby="tab-' . esc_attr($post_type) . '">';
				echo do_shortcode('[cwpSearch type="' . esc_attr($post_type) . '"]');
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	public function cubewp_search_button($output = '', $args = array())
	{

		$args    =  apply_filters('cubewp/frontend/field/parametrs', $args);
		$output  = CubeWp_Frontend::cwp_frontend_post_field_container($args);
		$output .= '<button type="submit" class="cwp-submit-search ' . $args['class'] . '">' . $this->icon_html . $args['label'] . '</button>';
		$output .= '</div>';
		$output = apply_filters("cubewp/frontend/search/{$args['name']}/field", $output, $args);
		return $output;
	}


	public function cubewp_search_form_container($output, $params, $search_fields)
	{

		$this->custom_fields      =  CWP()->get_custom_fields('post_types');
		$cwp_search_fields = CWP()->get_form('search_fields');

		$type    =    $params['type'];
		$search_fields      =  isset($cwp_search_fields[$type]['fields']) ? $cwp_search_fields[$type]['fields'] : array();


		$form_container_class     =  isset($cwp_search_fields[$type]['form']['form_container_class']) ? $cwp_search_fields[$type]['form']['form_container_class']   : '';
		$form_class               =  isset($cwp_search_fields[$type]['form']['form_class'])           ? 'cwp-search-form ' . $cwp_search_fields[$type]['form']['form_class'] : 'cwp-search-form';
		$form_id                  =  isset($cwp_search_fields[$type]['form']['form_id'])              ? $cwp_search_fields[$type]['form']['form_id']                : 'cwp-search-' . $type;


		$html = '<div class="cwp-frontend-search-form ' . esc_attr($form_container_class) . '">
            <form method="GET" id="' . esc_attr($form_id) . '" class="' . esc_attr($form_class) . '" action="' . esc_url(home_url('/')) . '" class="cwp-search-form">
                <input type="hidden" name="post_type" value="' . esc_attr($type) . '">';

		$html .= $this->cubewp_frontend_search_form_fields($search_fields , $params);

		$html .= '</form>
        </div>';


		return $html;
	}


	public function cubewp_frontend_search_form_fields($search_fields , $params)
	{

		$only_fields = [];  
        $type    =    $params['type'];
		$output = '<div class="search-form-fields">';
		foreach ($search_fields as $name) {
			$fieldOptions = $name;
			if (isset($label) && $label != '') {
				$fieldOptions['label'] = $label;
			}
			if ($fieldOptions['type'] == 'google_address') {
				$fieldOptions['custom_name_lat'] =   $fieldOptions['name'] . '_lat';
				$fieldOptions['custom_name_lng'] =   $fieldOptions['name'] . '_lng';
				$fieldOptions['custom_name_range'] =   $fieldOptions['name'] . '_range';
			}
			if ($fieldOptions['type'] == 'taxonomy') {
				$fieldOptions['appearance'] = $fieldOptions['display_ui'];
			}
			$fieldOptions['form_type'] = 'search';

			if (isset($this->custom_fields[$name['name']]) && !empty($this->custom_fields[$name['name']])) {
				$fieldOptions = wp_parse_args($fieldOptions, $this->custom_fields[$name['name']]);
			}
			$only_fields[] = $fieldOptions;

			$settings = $this->all_setings; 
			$field_icon = isset($settings['field_' . esc_attr($fieldOptions['name']) . '_'.$type.'_icon']) ? $settings['field_' . esc_attr($fieldOptions['name']) . '_'.$type.'_icon']   : '';

			$icon_html = '';
			if (! empty($field_icon)) {
				if (class_exists('\Elementor\Icons_Manager')) {
					ob_start();
					\Elementor\Icons_Manager::render_icon($field_icon, ['aria-hidden' => 'true']);
					$icon_html = ob_get_clean();
				}
			}

			$output .= '<div class="cubewp-field-container ' . esc_attr($fieldOptions['field_size']) . ' ' . esc_attr($fieldOptions['type']) . '" data-name="' . esc_attr($fieldOptions['name']) . '">';
			$output .= '<span class="field-icons">' . $icon_html . ' </span>';
			$output .=  apply_filters("cubewp/frontend/search/{$fieldOptions['type']}/field", '', $fieldOptions);
			$output .= '</div>';
		}

		if (wp_is_serving_rest_request()) {
			return $only_fields;
		}

		$output .= '</div>';

		return $output;
	}
}
<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (class_exists('\Elementor\Group_Control_Typography') && !class_exists('CubeWp_Group_Control_Typography')) {
    /**
     * Override Elementor typography group control selector for font-family.
     * Keeps existing typography UI but allows CSS variable value without quotes.
     */
    class CubeWp_Group_Control_Typography extends \Elementor\Group_Control_Typography
    {
        protected function init_fields()
        {
            $fields = parent::init_fields();

            if (isset($fields['font_family'])) {
                $fields['font_family']['selector_value'] = 'font-family: {{VALUE}};';
            }

            return $fields;
        }
    }
}

/**
 * CubeWP Elementor typography integration.
 */
class CubeWp_Elementor_Typography
{
  

    /**
     * Register CubeWP font group at top.
     *
     * @param array<string, string> $font_groups Font groups.
     * @return array<string, string>
     */
    public static function register_cubewp_font_group($font_groups)
    {
        if (!is_array($font_groups)) {
            $font_groups = array();
        }

        $cubewp_group = array(
            'cubewp_setting_fonts' => esc_html__('CubeWP Setting Fonts', 'cubewp-framework'),
        );

        // Put CubeWP group first.
        return $font_groups + $cubewp_group;
    }

    /**
     * Add CubeWP font-family values into Elementor's existing typography control.
     *
     * @param array<string, string> $additional_fonts Elementor additional fonts.
     * @return array<string, string>
     */
    public static function add_fonts_to_elementor_typography_control($additional_fonts)
    {
        if (!is_array($additional_fonts)) {
            $additional_fonts = array();
        }

        $cwp_options = get_option('cwpOptions', array());
        if (empty($cwp_options) || !is_array($cwp_options)) {
            return $additional_fonts;
        }

        foreach ($cwp_options as $option_key => $option_value) {
            if (!is_array($option_value) || empty($option_value['font-family'])) {
                continue;
            }

            $safe_key = sanitize_key((string) $option_key);
            if ($safe_key === '') {
                continue;
            }

            $font_variable = 'var(--cwp-font-family-' . $safe_key . ')';
            if (!isset($additional_fonts[$font_variable])) {
                $additional_fonts[$font_variable] = 'cubewp_setting_fonts';
            }
        }

        return $additional_fonts;
    }

    /**
     * Relabel var(--cwp-font-family-*) options in Elementor editor dropdown.
     *
     * @return void
     */
    public static function enqueue_editor_font_option_label_script()
    {
        wp_enqueue_script(
            'cubewp-elementor-font-labels',
            CWP_PLUGIN_URI . 'cube/assets/admin/js/cubewp-elementor-font-labels.js',
            array('elementor-editor'),
            CUBEWP_VERSION,
            true
        );
    }


    /**
     * Register/override Elementor typography group control.
     *
     * @param \Elementor\Controls_Manager $controls_manager Elementor controls manager.
     * @return void
     */
    public static function register_typography_group_control_override($controls_manager)
    {
        if (!is_object($controls_manager) || !class_exists('CubeWp_Group_Control_Typography') || !class_exists('\Elementor\Group_Control_Typography')) {
            return;
        }

        $controls_manager->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            new CubeWp_Group_Control_Typography()
        );
    }

    /**
     * Print CubeWP typography CSS variables on :root.
     *
     * Variable examples:
     * --cwp-font-family-h1
     * --cwp-color-h1
     * --cwp-color-primary_color
     *
     * @return void
     */
    public static function print_root_typography_css_variables()
    {
        $css_vars = self::get_root_css_variable_map();
        if (empty($css_vars)) {
            return;
        }

        $declarations = array();
        foreach ($css_vars as $name => $value) {
            $safe_name  = sanitize_key((string) $name);
            $safe_value = sanitize_text_field((string) $value);
            if ($safe_name === '' || $safe_value === '') {
                continue;
            }
            $declarations[] = '--' . $safe_name . ': ' . $safe_value;
        }

        if (empty($declarations)) {
            return;
        }

        echo '<style id="cubewp-typography-root-vars">:root{' . esc_html(implode(';', $declarations)) . ';}</style>';
    }

    /**
     * Build map of root CSS variables from CubeWP options.
     *
     * @return array<string, string>
     */
    private static function get_root_css_variable_map()
    {
        $cwp_options = get_option('cwpOptions', array());
        $vars = array();

        if (empty($cwp_options) || !is_array($cwp_options)) {
            return $vars;
        }

        foreach ($cwp_options as $option_key => $option_value) {
            $safe_key = sanitize_key((string) $option_key);
            if ($safe_key === '') {
                continue;
            }

            if (is_array($option_value)) {
                if (!empty($option_value['font-family'])) {
                    $font_value = sanitize_text_field((string) $option_value['font-family']);
                    if ($font_value !== '') {
                        $vars['cwp-font-family-' . $safe_key] = $font_value;
                    }
                }
                if (!empty($option_value['color'])) {
                    $color_value = sanitize_text_field((string) $option_value['color']);
                    if ($color_value !== '' && self::is_color_like_value($color_value)) {
                        $vars['cwp-color-' . $safe_key] = $color_value;
                    }
                }
                continue;
            }

            // Also expose plain color settings from cwpOptions.
            if (is_string($option_value) && self::is_color_like_value($option_value)) {
                $vars['cwp-color-' . $safe_key] = sanitize_text_field($option_value);
            }
        }

        return $vars;
    }

    /**
     * Basic check for color-like CSS values.
     *
     * @param string $value Potential color value.
     * @return bool
     */
    private static function is_color_like_value($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }
        return (bool) preg_match('/^(#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})|rgba?\([^)]+\)|hsla?\([^)]+\)|var\(--[a-zA-Z0-9_-]+\))$/', $value);
    }
}

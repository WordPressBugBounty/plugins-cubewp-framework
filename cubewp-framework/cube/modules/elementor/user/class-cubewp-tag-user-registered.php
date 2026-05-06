<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_User_Registered extends \Elementor\Core\DynamicTags\Tag
{
    public function get_name()
    {
        return 'cubewp-user-registered';
    }

    public function get_title()
    {
        return esc_html__('User Registered Date', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-user-data'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls()
    {
        $this->add_control(
            'display_type',
            [
                'label'   => esc_html__('Display Type', 'cubewp-framework'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'           => esc_html__('Date Format', 'cubewp-framework'),
                    'human_readable' => esc_html__('Human-Readable ("time ago")', 'cubewp-framework'),
                ],
            ]
        );

        $this->add_control(
            'date_format',
            [
                'label'       => esc_html__('Date Format', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'default',
                'options'     => [
                    'default' => esc_html__('WordPress Default', 'cubewp-framework'),
                    'F j, Y'  => date('F j, Y'),
                    'Y-m-d'   => date('Y-m-d'),
                    'd/m/Y'   => date('d/m/Y'),
                    'm/d/Y'   => date('m/d/Y'),
                    'custom'  => esc_html__('Custom', 'cubewp-framework'),
                ],
                'condition' => [
                    'display_type' => 'date',
                ],
            ]
        );

        $this->add_control(
            'custom_format',
            [
                'label'       => esc_html__('Custom Date Format', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'F j, Y',
                'condition'   => [
                    'date_format'  => 'custom',
                    'display_type' => 'date',
                ],
            ]
        );

        $this->add_control(
            'fallback_text',
            [
                'label'       => esc_html__('Fallback Text', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('N/A', 'cubewp-framework'),
            ]
        );
    }

    public function is_settings_required()
    {
        return true; // Changed to true since you have controls
    }

    public function render()
    {
        $value = $this->get_value();

        if (!empty($value)) {
            echo esc_html($value);
        } else {
            $fallback = $this->get_settings('fallback_text');
            if (!empty($fallback)) {
                echo esc_html($fallback);
            }
        }
    }

    public function get_value(array $options = [])
    {
        $user_id = cubewp_get_elementor_tag_user_id();

        if (! $user_id) {
            return '';
        }

        $user = get_userdata($user_id);

        if (! $user || empty($user->user_registered)) {
            return '';
        }

        $timestamp    = strtotime($user->user_registered);
        $display_type = $this->get_settings('display_type');

        // Human-readable option
        if ('human_readable' === $display_type) {
            return sprintf(
                esc_html__('%s ago', 'cubewp-framework'),
                human_time_diff($timestamp, current_time('timestamp'))
            );
        }

        // Regular date format
        $format = $this->get_settings('date_format');

        if ('default' === $format) {
            $format = get_option('date_format');
        } elseif ('custom' === $format) {
            $custom = $this->get_settings('custom_format');
            $format = ! empty($custom) ? $custom : get_option('date_format');
        }

        return date_i18n($format, $timestamp);
    }
}

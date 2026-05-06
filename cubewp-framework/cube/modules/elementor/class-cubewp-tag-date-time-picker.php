<?php
if (! defined('ABSPATH')) {
    exit;
}

class CubeWp_Tag_Date_Time_Picker extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-date_time_picker-tag';
    }
    public function get_title()
    {
        return esc_html__('Fields type (date_time_picker)', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-fields'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required()
    {
        return true;
    }

    protected function register_controls()
    {

        $this->add_control(
            'field_type',
            [
                'type'    => \Elementor\Controls_Manager::SELECT,
                'label'   => esc_html__('Data Source', 'cubewp-framework'),
                'options' => [
                    'post' => esc_html__('Post Fields', 'cubewp-framework'),
                    'user' => esc_html__('User Fields', 'cubewp-framework'),
                ],
                'default' => 'post',
            ]
        );

        $this->add_control(
            'user_selected_field',
            [
                'type'      => \Elementor\Controls_Manager::SELECT,
                'label'     => esc_html__('Select Field', 'cubewp-framework'),
                'options'   => get_fields_by_type(['date_time_picker']),
                'condition' => [
                    'field_type' => 'post',
                ],
            ]
        );

        $this->add_control(
            'user_selected_user_field',
            [
                'type'      => \Elementor\Controls_Manager::SELECT,
                'label'     => esc_html__('Select User Field', 'cubewp-framework'),
                'options'   => get_user_fields_by_type(['date_time_picker']),
                'condition' => [
                    'field_type' => 'user',
                ],
            ]
        );

        $this->add_control(
            'enable_custom_date_format',
            [
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'label'   => esc_html__('Enable Custom Format', 'cubewp-framework'),
                'default' => 'no',
            ]
        );

        $this->add_control(
            'custom_date_format',
            [
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label'       => esc_html__('Custom Date Format', 'cubewp-framework'),
                'placeholder' => 'F j, Y g:i A',
                'default'     => get_option('date_format') . ' ' . get_option('time_format'),
                'condition'   => [
                    'enable_custom_date_format' => 'yes',
                ],
            ]
        );
    }

    public function render()
    {

        $settings   = $this->get_settings_for_display();
        $field_type = $settings['field_type'] ?? 'post';

        if ('user' === $field_type) {
            $field = $settings['user_selected_user_field'] ?? '';
            if (! $field) {
                return;
            }
            $timestamp = get_user_meta(
                cubewp_get_elementor_tag_user_id(),
                get_user_field_options($field)['name'],
                true
            );
        } else {
            $field = $settings['user_selected_field'] ?? '';
            if (! $field) {
                return;
            }
            $timestamp = get_post_meta(
                get_the_ID(),
                get_field_options($field)['name'],
                true
            );
        }

        if (empty($timestamp) || ! is_numeric($timestamp)) {
            return;
        }

        $format = get_option('date_format') . ' ' . get_option('time_format');

        if ('yes' === $settings['enable_custom_date_format'] && ! empty($settings['custom_date_format'])) {
            $format = $settings['custom_date_format'];
        }

        echo esc_html(wp_date($format, (int) $timestamp));
    }
}

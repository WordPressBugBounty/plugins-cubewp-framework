<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_User_Email extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-user-email';
    }

    public function get_title()
    {
        return esc_html__('User Email', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-user-data'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
        ];
    }

    protected function register_controls()
    {

        $this->add_control(
            'output_type',
            [
                'label'   => esc_html__('Output Type', 'cubewp-framework'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'text'   => esc_html__('Plain Email', 'cubewp-framework'),
                    'mailto' => esc_html__('Mailto Link', 'cubewp-framework'),
                ],
            ]
        );

        $this->add_control(
            'fallback_text',
            [
                'label'       => esc_html__('Fallback Text', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Email not available', 'cubewp-framework'),
            ]
        );
    }

    public function is_settings_required()
    {
        return false;
    }

    public function render()
    {

        $user_id = cubewp_get_elementor_tag_user_id();

        if (! $user_id) {
            return;
        }

        $user = get_user_by('id', $user_id);

        if (! $user || empty($user->user_email)) {

            $fallback = $this->get_settings('fallback_text');

            if (! empty($fallback)) {
                echo esc_html($fallback);
            }

            return;
        }

        $email       = $user->user_email;
        $output_type = $this->get_settings('output_type');

        if ('mailto' === $output_type) {
            echo esc_url('mailto:' . $email);
        } else {
            echo esc_html($email);
        }
    }
}

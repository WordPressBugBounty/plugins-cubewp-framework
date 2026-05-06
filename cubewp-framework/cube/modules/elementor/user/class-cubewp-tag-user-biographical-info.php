<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_User_Biographical_Info extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-user-biographical-info';
    }

    public function get_title()
    {
        return esc_html__('User Biographical Info', 'cubewp-framework');
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
            'fallback_text',
            [
                'label'       => esc_html__('Fallback Text', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'placeholder' => esc_html__('No biographical info available', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'strip_html',
            [
                'label'        => esc_html__('Strip HTML', 'cubewp-framework'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Yes', 'cubewp-framework'),
                'label_off'    => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'max_length',
            [
                'label'       => esc_html__('Maximum Length', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'min'         => 0,
                'placeholder' => esc_html__('Leave empty for full text', 'cubewp-framework'),
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

        if (! $user) {
            return;
        }

        $description = $user->description;

        if (empty($description)) {
            $fallback = $this->get_settings('fallback_text');

            if (! empty($fallback)) {
                echo esc_html($fallback);
            }

            return;
        }

        $strip_html = $this->get_settings('strip_html');
        $max_length = (int) $this->get_settings('max_length');

        if ('yes' === $strip_html) {
            $description = wp_strip_all_tags($description);
        }

        if ($max_length > 0) {
            $description = wp_trim_words($description, $max_length, '…');
        }

        echo esc_html($description);
    }
}

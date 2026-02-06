<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_Post_Content extends \Elementor\Core\DynamicTags\Tag
{
    public function get_name()
    {
        return 'cubewp-post-content-tag';
    }

    public function get_title()
    {
        return esc_html__('Post Content', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-single-fields'];
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
            'text_limit_enable',
            [
                'label' => esc_html__('Text Limit', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'default' => 'no',
                'return_value' => 'yes',
            ]
        );
        $this->add_control(
            'text_limit',
            [
                'label' => esc_html__('Text Limit', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'condition' => [
                    'text_limit_enable' => 'yes',
                ],
            ]
        );
    }

    public function render()
    {
        $text_limit_enable = $this->get_settings('text_limit_enable');
        $text_limit = $this->get_settings('text_limit');
        if (cubewp_is_elementor_editing()) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            if ($text_limit_enable == 'yes') {
                $content_raw = get_the_content(null, false, cubewp_get_elementor_preview_post_id());
                $content = wp_trim_words(wp_strip_all_tags($content_raw), $text_limit);
            } else {
                $content = get_the_content();
            }
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            if ($text_limit_enable == 'yes') {
                $content_raw = get_the_content();
                $content = wp_trim_words(wp_strip_all_tags($content_raw), $text_limit);
            } else {
                $content = get_the_content();
            }
        }
        echo $content;
    }
}

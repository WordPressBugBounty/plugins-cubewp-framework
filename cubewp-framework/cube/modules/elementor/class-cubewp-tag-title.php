<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_Title extends \Elementor\Core\DynamicTags\Tag {
    public function get_name() {
        return 'cubewp-title-tag';
    }

    public function get_title() {
        return esc_html__( 'Post Title', 'cubewp-framework' );
    }

    public function get_group() {
        return [ 'cubewp-single-fields' ];
    }

    public function get_categories() {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required() {
		return true;
	}

protected function register_controls()
    {
        // Toggle to enable/disable line limit
        $this->add_control(
            'line_limit_enable',
            [
                'label' => esc_html__('Enable Line Limit', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        // Line limit control — only visible if the above switch is ON
        $this->add_control(
            'line_limit',
            [
                'label' => esc_html__('Max Lines', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'description' => esc_html__('Set how many lines to show before truncating with ellipsis.', 'cubewp-framework'),
                'condition' => [
                    'line_limit_enable' => 'yes',
                ],
            ]
        );
        $this->add_control(
            'text_limit',
            [
                'label' => esc_html__('Text Limit', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'description' => esc_html__('Set how many characters to show before truncating with ellipsis.', 'cubewp-framework'),
            ]
        );
    }

    public function render()
    {
        if (cubewp_is_elementor_editing()) {
            $title = get_the_title(cubewp_get_elementor_preview_post_id());
        } else {
            $title = get_the_title();
        }
        if (empty($title)) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $enable_limit = ! empty($settings['line_limit_enable']) && $settings['line_limit_enable'] === 'yes';
        $line_limit   = ! empty($settings['line_limit']) ? intval($settings['line_limit']) : 1;
        $text_limit   = ! empty($settings['text_limit']) ? intval($settings['text_limit']) : 10;
        $title = wp_trim_words($title, $text_limit, '...'); 
        if ($enable_limit) { 
            echo '<span class="cubewp-post-title-tag cubewp-clamp-' . intval($line_limit) . '">' . esc_html($title) . '</span>';
        } else {
            echo esc_html($title);
        }
    }
}
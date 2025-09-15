<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * CubeWP Search Posts Widgets.
 *
 * Elementor Widget For Search Posts By CubeWP.
 *
 * @since 1.0.0
 */

class CubeWp_Elementor_Archive_Posts_Widget extends Widget_Base
{

    private static $post_types = array();

    public function get_name()
    {
        return 'search_posts_widget';
    }

    public function get_title()
    {
        return __('Archive Posts Display', 'cubewp-framework');
    }

    public function get_icon()
    {
        return 'eicon-archive-posts';
    }

    public function get_categories()
    {
        return ['cubewp'];
    }

    protected function register_controls()
    {
        self::get_post_types();


        $this->start_controls_section(
            'section_map',
            [
                'label' => __('Search Posts Settings', 'cubewp-framework'),
            ]
        );
        $this->add_post_type_controls();


        $this->end_controls_section();

        $this->add_promotional_card_controls();
    }

    private static function get_post_types()
    {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];
        foreach ($post_types as $post_type) {
            $options[$post_type->name] = $post_type->label;
        }
        unset($options['elementor_library']);
        unset($options['e-landing-page']);
        unset($options['attachment']);
        unset($options['page']);

        self::$post_types = $options;
    }

    private static function get_post_type_name_by_slug($post_type_slug)
    {
        $post_type_object = get_post_type_object($post_type_slug);
        // Check if the post type object exists and return its label (name)
        if ($post_type_object) {
            return $post_type_object->label;
        }
        return null;
    }

    private function add_post_type_controls()
    {
        $post_types = self::$post_types;
        if (is_array($post_types) && ! empty($post_types)) {
            $this->add_control('posttype', array(
                'type'        => Controls_Manager::SELECT2,
                //'multiple'    => true,
                'label'       => esc_html__('Select Post Types', 'cubewp-classifiad'),
                'options'     => $post_types,
                'default'     => array('post'),
                'label_block' => true,
            ));
            foreach ($post_types as $slug => $post_type) {
                $this->add_card_style_controls($slug);
            }
        }
    }

    private function add_card_style_controls($post_type)
    {
        if (!empty(cubewp_post_card_styles($post_type))) {
            $this->add_control($post_type . '_card_style', array(
                'type'        => Controls_Manager::SELECT,
                'label'       => esc_html__('Card Style for ' . self::get_post_type_name_by_slug($post_type), 'cubewp-framework'),
                'options'     => cubewp_post_card_styles($post_type),
                'default'     => 'default_style',
                'condition'   => array(
                    'posttype' => $post_type
                )
            ));
        }
    }
    
    private function add_promotional_card_controls()
    {
        global $cubewpOptions;
        $posts_per_page = isset($cubewpOptions['posts_per_page']) ? (int)$cubewpOptions['posts_per_page'] : 10;
        $this->start_controls_section('cubewp_widget_additional_setting_section', array(
            'label' => esc_html__('Promotional Card Settings', 'cubewp-framework'),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ));

        $this->add_control('cubewp_promotional_card', array(
            'type'    => Controls_Manager::SWITCHER,
            'label'   => esc_html__('Show Promotional Cards', 'cubewp-framework'),
            'default' => 'no',
        ));

        // Create Repeater
        $repeater_CARDS = new Repeater();

        $repeater_CARDS->add_control('cubewp_promotional_card_option', array(
            'type'        => Controls_Manager::SELECT,
            'label'       => esc_html__('Promotional Cards', 'cubewp-framework'),
            'options'     => cubewp_get_get_promotional_cards_list(),
        ));

        $repeater_CARDS->add_control('cubewp_promotional_card_position', array(
            'type'        => Controls_Manager::NUMBER,
            'label'       => esc_html__('Position', 'cubewp-framework'),
            'default'     => 3,
            'placeholder' => esc_html__("3", "cubewp-framework"),
            'min'         => 1,
            'max'         => $posts_per_page,
        ));

        $repeater_CARDS->add_responsive_control('cubewp_promotional_card_width', array(
            'label'      => esc_html__('Width', 'cubewp-framework'),
            'type'       => Controls_Manager::SLIDER,
            'size_units' => ['px', '%'],
            'default'    => [
            'unit' => '%',
            'size' => 100,
            ],
            'range'      => [
            'px' => [
                'min' => 50,
                'max' => 1000,
            ],
            '%' => [
                'min' => 10,
                'max' => 100,
            ],
            ],
            'description' => esc_html__('Set the width of the card.', 'cubewp-framework'),
        ));

        // Add Repeater Control
        $this->add_control('cubewp_promotional_cards_list', array(
            'type'        => Controls_Manager::REPEATER,
            'label'       => esc_html__('Promotional Cards List', 'cubewp-framework'),
            'fields'      => $repeater_CARDS->get_controls(),
            'default'     => [],
            'title_field' => '{{{ cubewp_promotional_card_option }}}',
            'condition'   => [
                'cubewp_promotional_card' => 'yes',
            ],
        ));

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings   = $this->get_settings_for_display();
        $type = isset($settings['posttype']) ? $settings['posttype'] : '';
        $card_style = isset($settings[$type . '_card_style']) ? $settings[$type . '_card_style'] : '';
        $page_num = '1';

        $promotional_card = $settings['cubewp_promotional_card'] === 'yes' ? true : false;
        $promotional_card_list = $settings['cubewp_promotional_cards_list'];

        CubeWp_Enqueue::enqueue_script('cwp-search-filters');

        echo CubeWp_Frontend_Search_Filter::cwp_filter_results();
        echo '<form name="cwp-search-filters" class="cwp-search-filters" method="post">';
        echo CubeWp_Frontend_Search_Filter::filter_hidden_fields($type, $page_num, $card_style);
        echo CubeWp_Frontend_Search_Filter::get_hidden_field_if_tax();
        $count = 1;
        if ($promotional_card && !empty($promotional_card_list) && is_array($promotional_card_list)) {
            foreach ($promotional_card_list as $_promotional_card) {
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_option-'.$count.'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_option']) . '" />';
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_position-'.$count.'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_position']) . '" />';
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_width-'.$count.'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_width']['size']) .esc_attr($_promotional_card['cubewp_promotional_card_width']['unit']). '" />';
                $count++;
            }
        }
        echo '</form>';

        //Only to load data while editing in elementor
        if (cubewp_is_elementor_editing()) {
?>
            <script>
                cwp_search_filters_ajax_content();
            </script>
            <?php
        }
    }
}

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

        //Processing grids per row
        $this->add_responsive_control('processing_grids_per_row', array(
            'type'        => Controls_Manager::SELECT,
            'label'       => esc_html__('Processing Grids Per Row', 'cubewp-framework'),
            'default'     => '33.33',
            'options'     => [
                '100' => esc_html__("1", "cubewp-framework"),
                '50' => esc_html__("2", "cubewp-framework"),
                '33.33' => esc_html__("3", "cubewp-framework"),
                '25' => esc_html__("4", "cubewp-framework"),
                '20' => esc_html__("5", "cubewp-framework"),
                '16.66' => esc_html__("6", "cubewp-framework"),
            ],
            'selectors'   => [
                '{{WRAPPER}} .cwp-processing-grids .cwp-grids-container.cwp-row>.cwp-col-md-4' => 'width: {{VALUE}}%;',
            ],
        ));

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
                'label'       => esc_html__('Select Post Types', 'cubewp-framework'),
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
                /* translators: %s: post type singular name. */
                'label'       => sprintf( esc_html__( 'Card Style for %s', 'cubewp-framework' ), self::get_post_type_name_by_slug($post_type) ),
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
        $this->add_pagination_style_controls(); 

    }
    private function add_pagination_style_controls()
    {
        //Adding Spacing controls
        $this->start_controls_section(
            'section_spacing',
            [
                'label' => __('Post Spacing', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        //container gap (row + column)
        $this->add_responsive_control(
            'container_gap',
            [
                'label' => __('Container Gap (Grid View)', 'cubewp-framework'),
                'type' => Controls_Manager::GAPS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'row' => 0,
                    'column' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.grid-view' => 'gap: {{ROW}}{{UNIT}} {{COLUMN}}{{UNIT}};',
                ],
            ]
        );

        //container gap (row + column)
        $this->add_responsive_control(
            'container_list_gap',
            [
                'label' => __('Container Gap (List View)', 'cubewp-framework'),
                'type' => Controls_Manager::GAPS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'row' => 0,
                    'column' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.list-view' => 'gap: {{ROW}}{{UNIT}} {{COLUMN}}{{UNIT}};',
                ],
            ]
        );
        //

        $this->add_responsive_control(
            'post_padding',
            [
                'label' => __('Padding(Grid View)', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    '%' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.grid-view>div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_margin',
            [
                'label' => __('Margin(Grid View)', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    '%' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.grid-view>div' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_width',
            [
                'label' => __('Width (Grid View)', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1200],
                    '%' => ['min' => 1, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 80],
                    'rem' => ['min' => 0, 'max' => 80],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.grid-view>div' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_list_padding',
            [
                'label' => __('Padding(List View)', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    '%' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.list-view>div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_list_margin',
            [
                'label' => __('Margin(List View)', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100],
                    '%' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.list-view>div' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'post_list_width',
            [
                'label' => __('Width (List View)', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem','custom'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 1200],
                    '%' => ['min' => 1, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 80],
                    'rem' => ['min' => 0, 'max' => 80],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-container .cwp-search-result-output .cwp-row.list-view>div' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
            'section_pagination_style',
            [
                'label' => __('Pagination Style', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
    
        $this->add_responsive_control(
            'pagination_alignment',
            [
                'label' => __('Alignment', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'cubewp-framework'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'cubewp-framework'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'cubewp-framework'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination' => 'width:100%; text-align: {{VALUE}};',
                    '{{WRAPPER}} .cwp-pagination ul' => 'display: inline-flex; justify-content: {{VALUE}}; width: 100%; list-style: none; padding: 0;',
                ],
            ]
        );
    
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'pagination_typography',
                'selector' => '{{WRAPPER}} .cwp-pagination ul li a, {{WRAPPER}} .cwp-pagination ul li span a',
            ]
        );
    
        $this->start_controls_tabs('pagination_tabs');
    
        // --- Normal State ---
        $this->start_controls_tab(
            'pagination_normal',
            ['label' => __('Normal', 'cubewp-framework')]
        );
    
        $this->add_control(
            'pagination_color',
            [
                'label' => __('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-pagination ul li a svg' => 'fill: {{VALUE}};',
                ],
            ]
        );
    
        $this->add_control(
            'pagination_bg_color',
            [
                'label' => __('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'background-color: {{VALUE}};',
                ],
            ]
        );
    
        $this->add_control(
            'pagination_border_color_normal',
            [
                'label' => esc_html__('Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'border-color: {{VALUE}};',
                ],
            ]
        );
    
        $this->end_controls_tab();
    
        // --- Active & Hover State ---
        $this->start_controls_tab(
            'pagination_active',
            ['label' => __('Active / Hover', 'cubewp-framework')]
        );
    
        $this->add_control(
            'pagination_active_color',
            [
                'label' => __('Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li.active a, {{WRAPPER}} .cwp-pagination ul li.active span a, {{WRAPPER}} .cwp-pagination ul li a:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-pagination ul li.active a svg, {{WRAPPER}} .cwp-pagination ul li a:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );
    
        $this->add_control(
            'pagination_active_bg_color',
            [
                'label' => __('Background', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li.active a, {{WRAPPER}} .cwp-pagination ul li.active span a, {{WRAPPER}} .cwp-pagination ul li a:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
    
        $this->add_control(
            'pagination_border_colors',
            [
                'label' => __('Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li.active a, {{WRAPPER}} .cwp-pagination ul li.active span a, {{WRAPPER}} .cwp-pagination ul li a:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
    
        $this->end_controls_tab();
    
        $this->end_controls_tabs();
    
        // --- Border, Radius & Shadow ---
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'pagination_border',
                'selector' => '{{WRAPPER}} .cwp-pagination ul li a',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'pagination_width',
            [
                'label' => __('Width', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 10, 'max' => 200],
                    '%' => ['min' => 1, 'max' => 100],
                    'em' => ['min' => 1, 'max' => 10],
                    'rem' => ['min' => 1, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'pagination_height',
            [
                'label' => __('Height', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 10, 'max' => 200],
                    '%' => ['min' => 1, 'max' => 100],
                    'em' => ['min' => 1, 'max' => 10],
                    'rem' => ['min' => 1, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'pagination_margin',
            [
                'label' => __('Margin', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 200],
                    '%' => ['min' => 0, 'max' => 100],
                    'em' => ['min' => 0, 'max' => 10],
                    'rem' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'pagination_border_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
    
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'pagination_box_shadow',
                'selector' => '{{WRAPPER}} .cwp-pagination ul li a',
            ]
        );
    
        $this->add_responsive_control(
            'pagination_padding',
            [
                'label' => __('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; display: flex; align-items: center; justify-content: center; min-width: 30px; min-height: 30px; text-decoration: none;',
                ],
                'separator' => 'before',
            ]
        );
    
        $this->add_responsive_control(
            'pagination_spacing',
            [
                'label' => __('Spacing Between Buttons', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-pagination ul li:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
    
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

        echo CubeWp_Frontend_Search_Filter::cwp_filter_results() ;
        echo '<form name="cwp-search-filters" class="cwp-search-filters" method="post">';
        echo CubeWp_Frontend_Search_Filter::filter_hidden_fields( $type, $page_num, $card_style );
        echo   CubeWp_Frontend_Search_Filter::get_hidden_field_if_tax() ;
        $count = 1;
        if ($promotional_card && !empty($promotional_card_list) && is_array($promotional_card_list)) {
            foreach ($promotional_card_list as $_promotional_card) {
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_option-'.esc_attr($count).'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_option']) . '" />';
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_position-'.esc_attr($count).'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_position']) . '" />';
                echo '<input type="hidden" class="cubewp-promotional-card" name="cubewp_promotional_card_width-'.esc_attr($count).'" value="' . esc_attr($_promotional_card['cubewp_promotional_card_width']['size']) .esc_attr($_promotional_card['cubewp_promotional_card_width']['unit']). '" />';
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
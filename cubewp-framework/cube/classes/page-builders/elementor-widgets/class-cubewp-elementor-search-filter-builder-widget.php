<?php

/**
 * CubeWP Search Filter Builder Widget
 *
 * Complete search filter builder widget for Elementor
 * Allows building custom filter layouts with various display options
 *
 * @package cubewp-framework
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

/**
 * CubeWP Search Filter Builder Widget
 */
class CubeWp_Elementor_Search_Filter_Builder_Widget extends Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        // Add filter to handle business hours status filtering
        // add_filter('cubewp/search/query/update', [$this, 'filter_business_hours_query'], 10, 2);
    }

    public function get_name()
    {
        return 'cubewp_search_filter_builder';
    }

    public function get_title()
    {
        return esc_html__('CubeWP Search Filter Builder', 'cubewp-framework');
    }

    public function get_icon()
    {
        return 'eicon-filter';
    }

    public function get_categories()
    {
        return ['cubewp'];
    }


    public function get_keywords()
    {
        return ['cubewp', 'search', 'filter', 'builder'];
    }

    protected function register_controls()
    {
        // Post Type Section
        // ============================================
        // Section 1: Post Type Selection
        // ============================================
        $this->start_controls_section(
            'section_post_type',
            [
                'label' => esc_html__('Post Type', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        if (CWP()->is_request('frontend') || cubewp_is_elementor_editing()) {
            $post_types = CWP_all_post_types();
            $this->add_control(
                'post_type',
                [
                    'label' => esc_html__('Select Post Type', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT,
                    'options' => $post_types,
                    'default' => 'post',
                    'frontend_available' => true,
                    'description' => esc_html__('Select the post type for this filter field.', 'cubewp-framework'),
                ]
            );
        }

        $this->add_control(
            'show_data_for',
            [
                'label' => esc_html__('Show Layout', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'mobile_desktop' => esc_html__('Mobile & Desktop', 'cubewp-framework'),
                    'desktop' => esc_html__('Desktop', 'cubewp-framework'),
                    'mobile' => esc_html__('Mobile', 'cubewp-framework'),
                ],
                'default' =>  'mobile_desktop',
            ]
        );

        $this->end_controls_section();

        // ============================================
        // Section 2: Filter Type Selection
        // ============================================
        $this->start_controls_section(
            'section_filter_type',
            [
                'label' => esc_html__('Filter Type', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Filter Type (Filters or Sorting)
        $this->add_control(
            'filter_type',
            [
                'label' => esc_html__('Type', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'filters',
                'options' => [
                    'filters' => esc_html__('Filters', 'cubewp-framework'),
                    'sorting' => esc_html__('Sorting', 'cubewp-framework'),
                    'reset' => esc_html__('Reset', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose whether to show filters or sorting options.', 'cubewp-framework'),
            ]
        );

        $this->end_controls_section();

        // Section 3: Reset Button
        // ============================================
        $this->start_controls_section(
            'section_reset_button',
            [
                'label' => esc_html__('Reset Button', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'filter_type' => 'reset',
                ],
            ]
        );

        // Reset Button Text
        $this->add_control(
            'reset_button_text',
            [
                'label' => esc_html__('Button Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Reset Filters', 'cubewp-framework'),
                'placeholder' => esc_html__('Enter reset button text...', 'cubewp-framework'),
            ]
        );

        // Reset Button Icon
        $this->add_control(
            'reset_button_icon',
            [
                'label' => esc_html__('Icon', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-redo',
                    'library' => 'fa-solid',
                ],
            ]
        );


        // Icon Direction (LTR or RTL)
        $this->add_control(
            'reset_button_icon_direction',
            [
                'label' => esc_html__('Icon Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'row',
                'options' => [
                    'row' => esc_html__('Left', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Right', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'display: flex;align-items: center;justify-content: center;flex-direction: {{VALUE}};',
                ],
                'condition' => [
                    'reset_button_icon[value]!' => '',
                ],
            ]
        );

        $this->end_controls_section();
        $this->start_controls_section(
            'section_reset_button_style',
            [
                'label' => esc_html__('Reset Button', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'reset',
                ],
            ]
        );

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'reset_button_typography',
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-reset-button-button .cubewp-button-text',
            ]
        );

        $this->start_controls_tabs('reset_button_style_tabs');

        // --- Normal Tab ---
        $this->start_controls_tab(
            'reset_button_normal_tab',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'reset_button_text_color',
            [
                'label'     => esc_html__('Text Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'reset_button_bg_color',
            [
                'label'     => esc_html__('Background Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#6ec1e4',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'transition: all 0.3s ease; background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'reset_button_icon_color',
            [
                'label'     => esc_html__('Icon Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button .cubewp-button-icon i'   => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button .cubewp-button-icon svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'reset_button_icon[value]!' => '',
                ],
            ]
        );

        // Icon Size
        $this->add_responsive_control(
            'reset_button_icon_size',
            [
                'label'      => esc_html__('Icon Size', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range'      => [
                    'px' => ['min' => 10, 'max' => 60],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'condition'  => [
                    'reset_button_icon[value]!' => '',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button .cubewp-button-icon i'   => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button .cubewp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // NEW: Icon Spacing (Gap)
        $this->add_responsive_control(
            'reset_button_icon_spacing',
            [
                'label'     => esc_html__('Icon Spacing', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'range'     => [
                    'px' => ['min' => 0, 'max' => 50],
                ],
                'default'   => [
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'gap: {{SIZE}}{{UNIT}}; display: inline-flex; align-items: center; justify-content: center;',
                ],
                'condition' => [
                    'reset_button_icon[value]!' => '',
                ],
            ]
        );
        $this->add_responsive_control(
            'reset_button_icon_margin',
            [
                'label'     => esc_html__('Icon Margin', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button svg' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'reset_button_icon[value]!' => '',
                ],
            ]
        );
        // Padding
        $this->add_responsive_control(
            'reset_button_padding',
            [
                'label'      => esc_html__('Padding', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default'    => [
                    'top'    => '12',
                    'right'  => '24',
                    'bottom' => '12',
                    'left'   => '24',
                    'unit'   => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'reset_button_margin',
            [
                'label'      => esc_html__('Margin', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'reset_button_border',
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-reset-button-button',
            ]
        );

        // Border Radius
        $this->add_responsive_control(
            'reset_button_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default'    => [
                    'top'    => '4',
                    'right'  => '4',
                    'bottom' => '4',
                    'left'   => '4',
                    'unit'   => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'reset_button_box_shadow',
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-reset-button-button',
            ]
        );

        $this->end_controls_tab();

        // --- Hover Tab ---
        $this->start_controls_tab(
            'reset_button_hover_tab',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'reset_button_text_color_hover',
            [
                'label'     => esc_html__('Text Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button:hover, {{WRAPPER}} .cubewp-filter-builder-reset-button-button:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'reset_button_bg_color_hover',
            [
                'label'     => esc_html__('Background Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#54595f',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button:hover, {{WRAPPER}} .cubewp-filter-builder-reset-button-button:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'reset_button_icon_color_hover',
            [
                'label'     => esc_html__('Icon Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button:hover .cubewp-button-icon i,
                     {{WRAPPER}} .cubewp-filter-builder-reset-button-button:focus .cubewp-button-icon i,
                     {{WRAPPER}} .cubewp-filter-builder-reset-button-button:hover .cubewp-button-icon svg,
                     {{WRAPPER}} .cubewp-filter-builder-reset-button-button:focus .cubewp-button-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
                'condition' => [
                    'reset_button_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'reset_button_border_color_hover',
            [
                'label'     => esc_html__('Border Color', 'cubewp-framework'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-reset-button-button:hover, {{WRAPPER}} .cubewp-filter-builder-reset-button-button:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        // Section 3: Field Selections (for Filters)
        // ============================================
        $this->start_controls_section(
            'section_field_selections',
            [
                'label' => esc_html__('Field Selections', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        // Display Type
        $this->add_control(
            'field_display_type',
            [
                'label' => esc_html__('How Field Show', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'simple',
                'options' => [
                    'simple' => esc_html__('Show Simple Field', 'cubewp-framework'),
                    'popup' => esc_html__('Show in Popup', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose how this filter field should be displayed.', 'cubewp-framework'),
            ]
        );

        // Display Type
        $this->add_control(
            'convert_popup_to_dropdown',
            [
                'label' => esc_html__('Convert Popup to Dropdown', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'cubewp-framework'),
                    'no' => esc_html__('No', 'cubewp-framework'),
                ],
                'condition' => [
                    'field_display_type' => 'popup',
                ],
                'description' => esc_html__('Convert Popup to Dropdown', 'cubewp-framework'),
            ]
        );
        // $this->add_control(
        //     'convert_simple_to_accordion',
        //     [
        //         'label' => esc_html__('Convert Simple to Accordion', 'cubewp-framework'),
        //         'type' => Controls_Manager::SELECT,
        //         'default' => 'no',
        //         'options' => [
        //             'yes' => esc_html__('Yes', 'cubewp-framework'),
        //             'no' => esc_html__('No', 'cubewp-framework'),
        //         ],
        //         'condition' => [
        //             'field_display_type' => 'simple',
        //         ],
        //         'description' => esc_html__('Convert Popup to Dropdown', 'cubewp-framework'),
        //     ]
        // );

        // Field Type Selection (Custom Fields or Taxonomies) - for Simple
        $this->add_control(
            'field_type_selection',
            [
                'label' => esc_html__('Field Type', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'custom_fields',
                'options' => [
                    'custom_fields' => esc_html__('Custom Fields', 'cubewp-framework'),
                    'taxonomies' => esc_html__('Taxonomies', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose whether to use custom fields or taxonomies.', 'cubewp-framework'),
                'frontend_available' => true,
                'condition' => [
                    'field_display_type' => 'simple',
                ],
            ]
        );





        // Field selection - will be populated dynamically based on post type
        // We'll create separate controls for each post type to show fields dynamically
        if (CWP()->is_request('frontend') || cubewp_is_elementor_editing()) {
            $post_types = CWP_all_post_types();
            foreach ($post_types as $post_type_key => $post_type_label) {
                // Custom Fields Options
                $custom_fields_options = $this->get_custom_fields_for_post_type($post_type_key);
                // Add keyword to custom fields
                $custom_fields_options = array_merge(['keyword' => esc_html__('Keyword', 'cubewp-framework')], $custom_fields_options);
                $custom_fields_options = apply_filters('cubewp/builder/custom_fields_options', $custom_fields_options, $post_type_key);
                // Taxonomy Options
                $taxonomy_options = $this->get_taxonomies_for_post_type($post_type_key);

                $this->add_control(
                    'taxonomy_name_' . $post_type_key,
                    [
                        'label' => esc_html__('Select Taxonomy', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => $taxonomy_options,
                        'default' => '',
                        'description' => esc_html__('Select a taxonomy for this filter.', 'cubewp-framework'),
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                $this->add_control(
                    'field_name_' . $post_type_key,
                    [
                        'label' => esc_html__('Select Custom Field', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => $custom_fields_options,
                        'default' => '',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                // Field Label for Taxonomies
                $this->add_control(
                    'taxonomy_label_' . $post_type_key,
                    [
                        'label' => esc_html__('Field Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'placeholder' => esc_html__('Leave empty to use default label', 'cubewp-framework'),
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                        ],
                        'description' => esc_html__('Custom label for the taxonomy field. Leave empty to use the default taxonomy label.', 'cubewp-framework'),
                    ]
                );

                // Field Label for Custom Fields
                $this->add_control(
                    'field_label_' . $post_type_key,
                    [
                        'label' => esc_html__('Field Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'placeholder' => esc_html__('Leave empty to use default label', 'cubewp-framework'),
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                        'description' => esc_html__('Custom label for the field. Leave empty to use the default field label.', 'cubewp-framework'),
                    ]
                );

                // Number Range Slider (optional overrides for this widget)
                $this->add_control(
                    'number_range_slider_ui_' . $post_type_key,
                    [
                        'label' => esc_html__('Number Range Slider UI', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'inherit',
                        'options' => [
                            'inherit' => esc_html__('Inherit (from CubeWP builder)', 'cubewp-framework'),
                            'no' => esc_html__('No', 'cubewp-framework'),
                            'yes' => esc_html__('Yes', 'cubewp-framework'),
                        ],
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                        'description' => esc_html__('Overrides the CubeWP builder setting for this widget instance only (Number fields only).', 'cubewp-framework'),
                    ]
                );

                $this->add_control(
                    'number_range_min_' . $post_type_key,
                    [
                        'label' => esc_html__('Range Min', 'cubewp-framework'),
                        'type' => Controls_Manager::NUMBER,
                        'default' => '',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                            'number_range_slider_ui_' . $post_type_key . '!' => 'inherit',
                        ],
                    ]
                );

                $this->add_control(
                    'number_range_max_' . $post_type_key,
                    [
                        'label' => esc_html__('Range Max', 'cubewp-framework'),
                        'type' => Controls_Manager::NUMBER,
                        'default' => '',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                            'number_range_slider_ui_' . $post_type_key . '!' => 'inherit',
                        ],
                    ]
                );

                $this->add_control(
                    'number_range_step_' . $post_type_key,
                    [
                        'label' => esc_html__('Range Step', 'cubewp-framework'),
                        'type' => Controls_Manager::NUMBER,
                        'default' => '',
                        'min' => 0.000001,
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                            'number_range_slider_ui_' . $post_type_key . '!' => 'inherit',
                        ],
                    ]
                );

                $this->add_control(
                    'taxonomy_display_type_placeholder' . $post_type_key,
                    [
                        'label' => esc_html__('Placeholder', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'condition' => [
                            'field_display_type' => 'simple',
                            'post_type' => $post_type_key,
                        ],
                    ]
                );


                // Taxonomy Display Type (only for taxonomies)
                $this->add_control(
                    'taxonomy_display_type_' . $post_type_key,
                    [
                        'label' => esc_html__('Taxonomy Display Type', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'checkbox',
                        'options' => [
                            'checkbox' => esc_html__('Checkbox', 'cubewp-framework'), 
                            'select' => esc_html__('Select', 'cubewp-framework'),
                            'select2' => esc_html__('Select2', 'cubewp-framework'),
                        ],
                        'condition' => [
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                            'post_type' => $post_type_key,
                        ],
                    ]
                );

                // Conditional Taxonomy Controls for Simple Display Type
                $this->add_control(
                    'enable_conditional_taxonomy_' . $post_type_key,
                    [
                        'type'         => Controls_Manager::SWITCHER,
                        'label'        => esc_html__('Enable Conditional Taxonomy', 'cubewp-framework'),
                        'description'  => esc_html__('Show this taxonomy field only when a term is selected in another taxonomy.', 'cubewp-framework'),
                        'label_on'     => esc_html__('Yes', 'cubewp-framework'),
                        'label_off'    => esc_html__('No', 'cubewp-framework'),
                        'return_value' => 'yes',
                        'default'      => 'no',
                        'condition'    => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                $this->add_control(
                    'conditional_taxonomy_' . $post_type_key,
                    [
                        'type'        => Controls_Manager::SELECT,
                        'label'       => esc_html__('Conditional Taxonomy', 'cubewp-framework'),
                        'description' => esc_html__('Select the taxonomy that will trigger this field to show.', 'cubewp-framework'),
                        'options'     => $taxonomy_options,
                        'default'     => array_key_first($taxonomy_options),
                        'label_block' => true,
                        'condition'   => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                            'enable_conditional_taxonomy_' . $post_type_key => 'yes',
                        ],
                    ]
                );

                // Get term meta keys from CubeWP taxonomy custom fields
                $term_meta_key_options = $this->get_term_meta_key_options($post_type_key);

                $this->add_control(
                    'conditional_term_meta_key_' . $post_type_key,
                    [
                        'type'        => Controls_Manager::SELECT,
                        'label'       => esc_html__('Term Meta Key', 'cubewp-framework'),
                        'description' => esc_html__('Select the term meta key that stores the associated terms.', 'cubewp-framework'),
                        'options'     => $term_meta_key_options,
                        'default'     => '',
                        'label_block' => true,
                        'condition'   => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                            'enable_conditional_taxonomy_' . $post_type_key => 'yes',
                            'conditional_taxonomy_' . $post_type_key . '!' => '',
                        ],
                    ]
                );

                // Field Icon for Taxonomies
                $this->add_control(
                    'taxonomy_icon_' . $post_type_key,
                    [
                        'label' => esc_html__('Field Icon', 'cubewp-framework'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'icon',
                        'skin' => 'inline',
                        'label_block' => false,
                        'default' => [
                            'value' => '',
                            'library' => '',
                        ],
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'taxonomies',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );


                // Field Icon for Custom Fields
                $this->add_control(
                    'field_icon_' . $post_type_key,
                    [
                        'label' => esc_html__('Field Icon', 'cubewp-framework'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'icon',
                        'skin' => 'inline',
                        'label_block' => false,
                        'default' => [
                            'value' => '',
                            'library' => '',
                        ],
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                // Display an alert box to inform users that the following options are just for Business Hours fields.
                $this->add_control(
                    'business_hours_notice_' . $post_type_key,
                    [
                        'type' => \Elementor\Controls_Manager::RAW_HTML,
                        'raw' => '<div class="elementor-alert elementor-alert-warning" style="margin: 0 0 20px 0; padding: 10px; background: #fffbeb; color: #b66509; border: 1px solid #ffe5b2; border-radius: 4px; font-size: 13px;">' .
                            esc_html__('Note: The following options apply only to Business Hours fields.', 'cubewp-framework') .
                            '</div>',
                        'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );


                $this->add_control(
                    'business_hours_filter_' . $post_type_key,
                    [
                        'label' => esc_html__('Business Hours Filter Option', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'open_now' => esc_html__('Open Now', 'cubewp-framework'),
                            'closed_now' => esc_html__('Closed Now', 'cubewp-framework'),
                            'open_24_hours' => esc_html__('Open 24 Hours', 'cubewp-framework'),
                            'day_off' => esc_html__('Day Off', 'cubewp-framework'),
                        ],
                        'default' => 'open_now',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                        'description' => esc_html__('Select a single business hours filter option to display.', 'cubewp-framework'),
                    ]
                );


                // Display an alert box to inform users that the following options are just for Business Hours fields.
                $this->add_control(
                    'business_hours_notice__2' . $post_type_key,
                    [
                        'type' => \Elementor\Controls_Manager::RAW_HTML,
                        'raw' => '<div class="elementor-alert elementor-alert-warning" style="margin: 0 0 20px 0; padding: 10px; background: #fffbeb; color: #b66509; border: 1px solid #ffe5b2; border-radius: 4px; font-size: 13px;">' .
                            esc_html__('Note: The following options apply only to Google Address and Business Hours fields.', 'cubewp-framework') .
                            '</div>',
                        'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                // Business Hours Button Text
                $this->add_control(
                    'allow_near_me_option' . $post_type_key,
                    [
                        'label' => esc_html__('Allow Near Me Option', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'yes' => esc_html__('Yes', 'cubewp-framework'),
                            'no' => esc_html__('No', 'cubewp-framework'),
                        ],
                        'default' => 'no',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );


                $this->add_control(
                    'business_hours_button_text_' . $post_type_key,
                    [
                        'label' => esc_html__('Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => esc_html__('Filter Label', 'cubewp-framework'),
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                // Business Hours Button Text (inner button text)
                $this->add_control(
                    'business_hours_button_inner_text_' . $post_type_key,
                    [
                        'label' => esc_html__('Button Text', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                        'description' => esc_html__('Custom text for the button. Leave empty to use default label.', 'cubewp-framework'),
                    ]
                );

                // Business Hours Button Icon
                $this->add_control(
                    'business_hours_button_icon_' . $post_type_key,
                    [
                        'label' => esc_html__('Button Icon', 'cubewp-framework'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'icon',
                        'skin' => 'inline',
                        'label_block' => false,
                        'default' => [
                            'value' => '',
                            'library' => '',
                        ],
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                        ],
                    ]
                );

                // Business Hours Button Icon Position
                $start = is_rtl() ? 'right' : 'left';
                $end = is_rtl() ? 'left' : 'right';
                $this->add_control(
                    'business_hours_button_icon_position_' . $post_type_key,
                    [
                        'label' => esc_html__('Icon Position', 'cubewp-framework'),
                        'type' => Controls_Manager::CHOOSE,
                        'default' => is_rtl() ? 'right' : 'left',
                        'options' => [
                            'left' => [
                                'title' => esc_html__('Left', 'cubewp-framework'),
                                'icon' => "eicon-h-align-{$start}",
                            ],
                            'right' => [
                                'title' => esc_html__('Right', 'cubewp-framework'),
                                'icon' => "eicon-h-align-{$end}",
                            ],
                        ],
                        'toggle' => false,
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_type_selection' => 'custom_fields',
                            'field_display_type' => 'simple',
                            'business_hours_button_icon_' . $post_type_key . '[value]!' => '',
                        ],
                    ]
                );




                // Popup repeater for custom fields and taxonomies
                $popup_custom_repeater = new Repeater();
                $popup_custom_repeater->add_control(
                    'popup_field_type',
                    [
                        'label' => esc_html__('Field Type', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'custom_fields',
                        'options' => [
                            'custom_fields' => esc_html__('Custom Field', 'cubewp-framework'),
                            'taxonomies' => esc_html__('Taxonomy', 'cubewp-framework'),
                        ],
                    ]
                );
                $popup_custom_repeater->add_control(
                    'popup_field_name',
                    [
                        'label' => esc_html__('Select Custom Field', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => $custom_fields_options,
                        'default' => '',
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                    ]
                );
                // Field Label for Popup Taxonomy
                $popup_custom_repeater->add_control(
                    'popup_taxonomy_label',
                    [
                        'label' => esc_html__('Field Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'placeholder' => esc_html__('Leave empty to use default label', 'cubewp-framework'),
                        'condition' => [
                            'popup_field_type' => 'taxonomies',
                        ],
                        'description' => esc_html__('Custom label for the taxonomy field. Leave empty to use the default taxonomy label.', 'cubewp-framework'),
                    ]
                );

                // Field Label for Popup Custom Field
                $popup_custom_repeater->add_control(
                    'popup_field_label',
                    [
                        'label' => esc_html__('Field Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'placeholder' => esc_html__('Leave empty to use default label', 'cubewp-framework'),
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                        'description' => esc_html__('Custom label for the field. Leave empty to use the default field label.', 'cubewp-framework'),
                    ]
                );

                $popup_custom_repeater->add_control(
                    'popup_taxonomy_display_placeholder',
                    [
                        'label' => esc_html__('Placeholder', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                    ]
                );
                $popup_custom_repeater->add_control(
                    'popup_taxonomy_name',
                    [
                        'label' => esc_html__('Select Taxonomy', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => $taxonomy_options,
                        'default' => '',
                        'condition' => [
                            'popup_field_type' => 'taxonomies',
                        ],
                    ]
                );
                $popup_custom_repeater->add_control(
                    'popup_taxonomy_display',
                    [
                        'label' => esc_html__('Taxonomy Display', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'checkbox',
                        'options' => [
                            'checkbox' => esc_html__('Checkbox', 'cubewp-framework'), 
                            'select' => esc_html__('Select', 'cubewp-framework'),
                            'select2' => esc_html__('Select2', 'cubewp-framework'),
                        ],
                        'condition' => [
                            'popup_field_type' => 'taxonomies',
                        ],
                    ]
                ); 

                // Allow Near Me Option for Popup Repeater (for Google Address fields)
                $popup_custom_repeater->add_control(
                    'popup_allow_near_me_option',
                    [
                        'label' => esc_html__('Allow Near Me Option', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'yes' => esc_html__('Yes', 'cubewp-framework'),
                            'no' => esc_html__('No', 'cubewp-framework'),
                        ],
                        'default' => 'no',
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                        'description' => esc_html__('Enable Near Me button for Google Address fields.', 'cubewp-framework'),
                    ]
                );

                // Business Hours Options for Popup Repeater
                $popup_custom_repeater->add_control(
                    'popup_business_hours_filter',
                    [
                        'label' => esc_html__('Business Hours Filter Option', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                            'open_now' => esc_html__('Open Now', 'cubewp-framework'),
                            'closed_now' => esc_html__('Closed Now', 'cubewp-framework'),
                            'open_24_hours' => esc_html__('Open 24 Hours', 'cubewp-framework'),
                            'day_off' => esc_html__('Day Off', 'cubewp-framework'),
                        ],
                        'default' => 'open_now',
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                        'description' => esc_html__('Select a single business hours filter option to display.', 'cubewp-framework'),
                    ]
                );

                $popup_custom_repeater->add_control(
                    'popup_business_hours_button_text',
                    [
                        'label' => esc_html__('Business Hours Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => esc_html__('Business Hours', 'cubewp-framework'),
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                    ]
                );

                $popup_custom_repeater->add_control(
                    'popup_business_hours_button_inner_text',
                    [
                        'label' => esc_html__('Button Text', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => '',
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                        'description' => esc_html__('Custom text for the button. Leave empty to use default label.', 'cubewp-framework'),
                    ]
                );

                $popup_custom_repeater->add_control(
                    'popup_business_hours_button_icon',
                    [
                        'label' => esc_html__('Button Icon', 'cubewp-framework'),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'icon',
                        'skin' => 'inline',
                        'label_block' => false,
                        'default' => [
                            'value' => '',
                            'library' => '',
                        ],
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                        ],
                    ]
                );

                $start = is_rtl() ? 'right' : 'left';
                $end = is_rtl() ? 'left' : 'right';
                $popup_custom_repeater->add_control(
                    'popup_business_hours_button_icon_position',
                    [
                        'label' => esc_html__('Icon Position', 'cubewp-framework'),
                        'type' => Controls_Manager::CHOOSE,
                        'default' => is_rtl() ? 'right' : 'left',
                        'options' => [
                            'left' => [
                                'title' => esc_html__('Left', 'cubewp-framework'),
                                'icon' => "eicon-h-align-{$start}",
                            ],
                            'right' => [
                                'title' => esc_html__('Right', 'cubewp-framework'),
                                'icon' => "eicon-h-align-{$end}",
                            ],
                        ],
                        'toggle' => false,
                        'condition' => [
                            'popup_field_type' => 'custom_fields',
                            'popup_business_hours_button_icon[value]!' => '',
                        ],
                    ]
                );

                $this->add_control(
                    'popup_fields_' . $post_type_key,
                    [
                        'label' => esc_html__('Popup Fields', 'cubewp-framework'),
                        'type' => Controls_Manager::REPEATER,
                        'fields' => $popup_custom_repeater->get_controls(),
                        'default' => [],
                        'title_field' => '{{{ popup_field_type }}} - {{{ popup_field_name }}}{{{ popup_taxonomy_name }}}',
                        'condition' => [
                            'post_type' => $post_type_key,
                            'field_display_type' => 'popup',
                        ],
                        'description' => esc_html__('Add multiple fields to show in the popup.', 'cubewp-framework'),
                    ]
                );
            }
        }

        $this->end_controls_section();

        // ============================================
        // Section 3: Sorting Options
        // ============================================
        // Add Sorting Options section, and add a dropdown containing all current post type number fields from CubeWP

        $this->start_controls_section(
            'section_sorting_options',
            [
                'label' => esc_html__('Sorting Options', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'filter_type' => 'sorting',
                ],
            ]
        );




        // Sorting Display Type
        $this->add_control(
            'sorting_display_type',
            [
                'label' => esc_html__('Display Type', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'buttons',
                'options' => [
                    'buttons' => esc_html__('Buttons', 'cubewp-framework'),
                    'dropdown' => esc_html__('Dropdown', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose how sorting options should be displayed.', 'cubewp-framework'),
            ]
        );
        // Sorting Display Type
        $this->add_control(
            'sorting_display_type_title_default',
            [
                'label' => esc_html__('Sorting', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Sort By',
                'description' => esc_html__('Enter the default title for the sorting options. Leave empty to use the default title.', 'cubewp-framework'),
                'condition' => [
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );



        $post_types = CWP_all_post_types();

        $number_fields = [];

        foreach ($post_types as $post_type_key => $post_type_label) {
            // Ensure the key exists even if no number fields are found
            $number_fields[$post_type_key] = [];

            $allGroups = cwp_get_groups_by_post_type($post_type_key);
            foreach ($allGroups as $group) {
                $group_fields = get_post_meta($group, '_cwp_group_fields', true);
                $group_fields_array = explode(',', $group_fields);
                foreach ($group_fields_array as $field_name) {
                    $field_data = get_field_options($field_name);
                    if (isset($field_data['type']) && $field_data['type'] === 'number') {
                        $label = isset($field_data['label']) ? $field_data['label'] : $field_name;
                        $number_fields[$post_type_key][$field_name] = $label;
                    }
                }
            }
        }

        // Sorting Display Type
        foreach ($number_fields as $post_type_key => $number_fields_array) {
            $custom_order_options = [
                'DESC' => esc_html__('Newest', 'cubewp-framework'),
                'ASC' => esc_html__('Oldest', 'cubewp-framework'),
                'title' => esc_html__('Title', 'cubewp-framework'),
                'rand' => esc_html__('Random', 'cubewp-framework'),
                'relevance' => esc_html__('Relevance', 'cubewp-framework'),
                'cubewp_post_views' => esc_html__('Most Viewed', 'cubewp-framework'),
            ];

            if (class_exists('CubeWp_Reviews_Load')) {
                $custom_order_options['cubewp_review_count'] = 'Review Count';
                $custom_order_options['cubewp_overall_rating'] = 'Post Rating';
            }

            $merged_options = $custom_order_options + $number_fields_array;

            // For Dropdown display type: Use a repeater field
            $this->add_control(
                'sorting_number_fields_repeater_' . $post_type_key,
                [
                    'label' => esc_html__('Sorting Options (Dropdown)', 'cubewp-framework'),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => [
                        [
                            'name' => 'title',
                            'label' => esc_html__('Title', 'cubewp-framework'),
                            'type' => Controls_Manager::TEXT,
                            'default' => '',
                            'description' => esc_html__('Enter the title of the sorting option.', 'cubewp-framework'),
                        ],
                        [
                            'name' => 'field',
                            'label' => esc_html__('Field', 'cubewp-framework'),
                            'type' => Controls_Manager::SELECT,
                            'multiple' => false,
                            'options' => $merged_options,
                            'default' => 'DESC',
                        ],
                        [
                            'name' => 'filter_types',
                            'label' => esc_html__('Filter Types', 'cubewp-framework'),
                            'type' => Controls_Manager::SELECT,
                            'options' => [
                                'filters' => esc_html__('Filters', 'cubewp-framework'),
                                'sorting' => esc_html__('Sorting', 'cubewp-framework'),
                            ],
                            'default' => 'filters',
                            'description' => esc_html__('Choose whether to show filters or sorting options for this field.', 'cubewp-framework'),
                        ],
                        [
                            'name' => 'filter_types_value',
                            'label' => esc_html__('Order By', 'cubewp-framework'),
                            'type' => Controls_Manager::SELECT,
                            'default' => '',
                            'description' => esc_html__('Choose the order by value for this field.', 'cubewp-framework'),
                            'options' => [
                                'DESC' => esc_html__('DESC', 'cubewp-framework'),
                                'ASC' => esc_html__('ASC', 'cubewp-framework'),
                            ],
                            'default' => 'DESC',
                            'condition' => [
                                'filter_types' => 'sorting',
                            ],
                        ],

                        [
                            'name' => 'operation',
                            'label' => esc_html__('Number Operation', 'cubewp-framework'),
                            'type' => Controls_Manager::SELECT,
                            'options' => [
                                '=' => esc_html__('=', 'cubewp-framework'),
                                '!=' => esc_html__('!=', 'cubewp-framework'),
                                '>' => esc_html__('>', 'cubewp-framework'),
                                '<' => esc_html__('<', 'cubewp-framework'),
                                '>=' => esc_html__('>=', 'cubewp-framework'),
                                '<=' => esc_html__('<=', 'cubewp-framework'),
                                'BETWEEN' => esc_html__('BETWEEN', 'cubewp-framework'),
                                'NOT BETWEEN' => esc_html__('NOT BETWEEN', 'cubewp-framework'),
                                'NOT IN' => esc_html__('NOT IN', 'cubewp-framework'),
                                'IN' => esc_html__('IN', 'cubewp-framework'),
                                'EXISTS' => esc_html__('EXISTS', 'cubewp-framework'),
                                'NOT EXISTS' => esc_html__('NOT EXISTS', 'cubewp-framework'),
                                'LIKE' => esc_html__('LIKE', 'cubewp-framework'),
                                'NOT LIKE' => esc_html__('NOT LIKE', 'cubewp-framework'),
                            ],
                            'default' => '=',
                            'condition' => [
                                'field!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                                'filter_types' => 'filters',
                            ],
                        ],
                        [
                            'name' => 'value',
                            'label' => esc_html__('Compare With Value', 'cubewp-framework'),
                            'type' => Controls_Manager::TEXT,
                            'default' => '',
                            'description' => esc_html__('Enter the value to compare with the number field.', 'cubewp-framework'),
                            'condition' => [
                                'operation' => ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE'],
                                'field!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                                'filter_types' => 'filters',
                            ],
                        ],
                        [
                            'name' => 'value_between',
                            'label' => esc_html__('Compare With Values (Comma Separated)', 'cubewp-framework'),
                            'type' => Controls_Manager::TEXT,
                            'default' => '',
                            'description' => esc_html__('Enter values separated by commas (e.g. 10,50).', 'cubewp-framework'),
                            'condition' => [
                                'operation' => ['BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN'],
                                'field!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                                'filter_types' => 'filters',
                            ],
                        ],
                    ],
                    'default' => [],
                    'description' => esc_html__('Add multiple sorting options for this post type (displayed as dropdown).', 'cubewp-framework'),
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'dropdown',
                    ],
                ]
            );

            // For Buttons display type: Use a simple field (single select)
            $this->add_control(
                'sorting_number_field_button_' . $post_type_key,
                [
                    'label' => esc_html__('Sorting Option (Buttons)', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT2,
                    'multiple' => false,
                    'options' => $merged_options,
                    'default' => '',
                    'description' => esc_html__('Select a single field to sort by (displayed as button).', 'cubewp-framework'),
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                    ],
                ]
            );
            $this->add_control(
                'sorting_number_field_button_filter_type_' . $post_type_key,
                [
                    'label' => esc_html__('Filter Type', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        'filters' => esc_html__('Filters', 'cubewp-framework'),
                        'sorting' => esc_html__('Sorting', 'cubewp-framework'),
                    ],
                    'default' => 'filters',
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                    ],
                ]
            );
            $this->add_control(
                'sorting_number_field_button_filter_type_value_' . $post_type_key,
                [
                    'label' => esc_html__('Order By', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        'DESC' => esc_html__('DESC', 'cubewp-framework'),
                        'ASC' => esc_html__('ASC', 'cubewp-framework'),
                    ],
                    'default' => 'DESC',
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                        'sorting_number_field_button_filter_type_' . $post_type_key => 'sorting',
                    ],
                ]
            );
            $this->add_control(
                'sorting_number_operation_button_' . $post_type_key,
                [
                    'label' => esc_html__('Number Operation (Button)', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        '=' => esc_html__('=', 'cubewp-framework'),
                        '!=' => esc_html__('!=', 'cubewp-framework'),
                        '>' => esc_html__('>', 'cubewp-framework'),
                        '<' => esc_html__('<', 'cubewp-framework'),
                        '>=' => esc_html__('>=', 'cubewp-framework'),
                        '<=' => esc_html__('<=', 'cubewp-framework'),
                        'BETWEEN' => esc_html__('BETWEEN', 'cubewp-framework'),
                        'NOT BETWEEN' => esc_html__('NOT BETWEEN', 'cubewp-framework'),
                        'NOT IN' => esc_html__('NOT IN', 'cubewp-framework'),
                        'IN' => esc_html__('IN', 'cubewp-framework'),
                        'EXISTS' => esc_html__('EXISTS', 'cubewp-framework'),
                        'NOT EXISTS' => esc_html__('NOT EXISTS', 'cubewp-framework'),
                        'LIKE' => esc_html__('LIKE', 'cubewp-framework'),
                        'NOT LIKE' => esc_html__('NOT LIKE', 'cubewp-framework'),
                    ],
                    'default' => '=',
                    'description' => esc_html__('Select the operation to perform on the number field.', 'cubewp-framework'),
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                        'sorting_number_field_button_' . $post_type_key . '!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                        'sorting_number_field_button_filter_type_' . $post_type_key => 'filters',
                    ],
                ]
            );
            $this->add_control(
                'sorting_number_value_button_' . $post_type_key,
                [
                    'label' => esc_html__('Compare With Value (Button)', 'cubewp-framework'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                    'description' => esc_html__('Enter the value to compare with the number field.', 'cubewp-framework'),
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                        'sorting_number_operation_button_' . $post_type_key => ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE'],
                        'sorting_number_field_button_' . $post_type_key . '!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                        'sorting_number_field_button_filter_type_' . $post_type_key => 'filters',
                    ],
                ]
            );
            $this->add_control(
                'sorting_number_value_between_button_' . $post_type_key,
                [
                    'label' => esc_html__('Compare With Values (Comma Separated, Button)', 'cubewp-framework'),
                    'type' => Controls_Manager::TEXT,
                    'default' => '',
                    'description' => esc_html__('Enter values separated by commas (e.g. 10,50).', 'cubewp-framework'),
                    'condition' => [
                        'filter_type' => 'sorting',
                        'post_type' => $post_type_key,
                        'sorting_display_type' => 'buttons',
                        'sorting_number_operation_button_' . $post_type_key => ['BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN'],
                        'sorting_number_field_button_' . $post_type_key . '!' => ['DESC', 'ASC', 'title', 'rand', 'relevance'],
                        'sorting_number_field_button_filter_type_' . $post_type_key => 'filters',
                    ],
                ]
            );
        }


        // Button Text Customization (for buttons display type)
        $this->add_control(
            'sorting_button_text',
            [
                'label' => esc_html__('Button Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'description' => esc_html__('Custom text for the sorting button. Leave empty to use default label.', 'cubewp-framework'),
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );

        // Button Icon (for buttons display type)
        $this->add_control(
            'sorting_button_icon',
            [
                'label' => esc_html__('Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'default' => [
                    'value' => '',
                    'library' => '',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );


        $this->end_controls_section();

        // ============================================
        // Section 4: Popup Options
        // ============================================
        $this->start_controls_section(
            'section_popup_options',
            [
                'label' => esc_html__('Popup Options', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'filter_type' => 'filters',
                    'field_display_type' => 'popup',
                ],
            ]
        );

        // Popup button text
        $this->add_control(
            'popup_button_text',
            [
                'label' => esc_html__('Popup Button Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Advanced Filters', 'cubewp-framework'),
            ]
        );

        // Popup button icon
        $this->add_control(
            'popup_button_icon',
            [
                'label' => esc_html__('Popup Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'default' => [
                    'value' => '',
                    'library' => '',
                ],
            ]
        );

        // Popup button icon position
        $start = is_rtl() ? 'right' : 'left';
        $end = is_rtl() ? 'left' : 'right';
        $this->add_control(
            'popup_button_icon_position',
            [
                'label' => esc_html__('Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'default' => is_rtl() ? 'right' : 'left',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'cubewp-framework'),
                        'icon' => "eicon-h-align-{$start}",
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'cubewp-framework'),
                        'icon' => "eicon-h-align-{$end}",
                    ],
                ],
                'toggle' => false,
                'condition' => [
                    'popup_button_icon[value]!' => '',
                ],
            ]
        );

        // Popup header text
        $this->add_control(
            'popup_header_text',
            [
                'label' => esc_html__('Popup Header Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Advanced Filters', 'cubewp-framework'),
                'separator' => 'before',
            ]
        );

        // Popup position
        $this->add_control(
            'popup_position',
            [
                'label' => esc_html__('Popup Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'center' => esc_html__('Center', 'cubewp-framework'),
                    'top' => esc_html__('Top', 'cubewp-framework'),
                    'bottom' => esc_html__('Bottom', 'cubewp-framework'),
                    'left' => esc_html__('Left', 'cubewp-framework'),
                    'right' => esc_html__('Right', 'cubewp-framework'),
                ],
            ]
        );

        // Show close button
        $this->add_control(
            'popup_show_close_button',
            [
                'label' => esc_html__('Show Close Button', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        // Close button icon
        $this->add_control(
            'popup_close_icon',
            [
                'label' => esc_html__('Close Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'default' => [
                    'value' => 'fas fa-times',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'popup_show_close_button' => 'yes',
                ],
            ]
        );

        // Show apply button
        $this->add_control(
            'popup_show_apply_button',
            [
                'label' => esc_html__('Show Apply Button', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        // Apply button text
        $this->add_control(
            'popup_apply_button_text',
            [
                'label' => esc_html__('Apply Button Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('Apply Filters', 'cubewp-framework'),
                'condition' => [
                    'popup_show_apply_button' => 'yes',
                ],
            ]
        );

        // Apply button icon
        $this->add_control(
            'popup_apply_button_icon',
            [
                'label' => esc_html__('Apply Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
                'default' => [
                    'value' => '',
                    'library' => '',
                ],
                'condition' => [
                    'popup_show_apply_button' => 'yes',
                ],
            ]
        );

        // Apply button icon position
        $this->add_control(
            'popup_apply_button_icon_position',
            [
                'label' => esc_html__('Apply Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'default' => is_rtl() ? 'right' : 'left',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'cubewp-framework'),
                        'icon' => "eicon-h-align-{$start}",
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'cubewp-framework'),
                        'icon' => "eicon-h-align-{$end}",
                    ],
                ],
                'toggle' => false,
                'condition' => [
                    'popup_show_apply_button' => 'yes',
                    'popup_apply_button_icon[value]!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Filter Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        $this->add_control(
            'filter_container_background',
            [
                'label' => esc_html__('Container Background', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container.cwp-field-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'filter_container_padding',
            [
                'label' => esc_html__('Container Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container.cwp-field-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'filter_container_margin',
            [
                'label' => esc_html__('Container Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container.cwp-field-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Range Slider UI Style Section (Number min/max)
        $this->start_controls_section(
            'section_range_slider_style',
            [
                'label' => esc_html__('Range Slider UI', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        //Flex Direction
        $this->add_control(
            'range_slider_flex_direction',
            [
                'label' => esc_html__('Flex Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'row',
                'options' => [
                    'row' => esc_html__('Row', 'cubewp-framework'),
                    'column' => esc_html__('Column', 'cubewp-framework'),
                    'column-reverse' => esc_html__('Column Reverse', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => 'display: flex; flex-wrap: wrap; flex-direction: {{VALUE}};',
                ],
            ]
        );

        //gap
        $this->add_responsive_control(
            'range_slider_gap',
            [
                'label' => esc_html__('Gap', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        //margin
        $this->add_responsive_control(
            'range_slider_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em','custom'],
                'default' => [
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '0',
                    'left' => '0',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'range_slider_track_height',
            [
                'label' => esc_html__('Track Height', 'cubewp-framework'),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 2,
                        'max' => 24,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-track-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_track_bg',
            [
                'label' => esc_html__('Track Color', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-track-bg: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_range_bg',
            [
                'label' => esc_html__('Active Range Color', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-range-bg: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'range_slider_handle_size',
            [
                'label' => esc_html__('Handle Size', 'cubewp-framework'),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 48,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-handle-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_handle_bg',
            [
                'label' => esc_html__('Handle Background', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-handle-bg: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'range_slider_handle_border',
                'label' => esc_html__('Handle Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-range-number-slider-ui .ui-slider-handle',
            ]
        );

        $this->add_responsive_control(
            'range_slider_handle_radius',
            [
                'label' => esc_html__('Handle Border Radius', 'cubewp-framework'),
                'type'  => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider-ui .ui-slider-handle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'range_slider_value_color',
            [
                'label' => esc_html__('Value Color', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-range-number-slider' => '--cwp-range-value-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'range_slider_value_typography',
                'selector' => '{{WRAPPER}} .cwp-range-number-slider-values',
            ]
        );

        $this->end_controls_section();

        // Field Icon Style Section
        $this->start_controls_section(
            'section_field_icon_style',
            [
                'label' => esc_html__('Field Icon Style', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        $this->add_control(
            'field_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon, {{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 128,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 10,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon, {{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon i, {{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'field_icon_position',
            [
                'label' => esc_html__('Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'absolute',
                'options' => [
                    'static' => esc_html__('Static', 'cubewp-framework'),
                    'absolute' => esc_html__('Absolute', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon' => 'position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_icon_top',
            [
                'label' => esc_html__('Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_icon_position' => 'absolute',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_icon_left',
            [
                'label' => esc_html__('Left', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon' => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_icon_position' => 'absolute',
                ],
            ]
        );

        $this->add_responsive_control(
            'field_icon_z_index',
            [
                'label' => esc_html__('Z-index', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [''],
                'range' => [
                    '' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-container .cubewp-field-icon' => 'z-index: {{SIZE}};',
                ],
                'condition' => [
                    'field_icon_position' => 'absolute',
                ],
            ]
        );

        $this->end_controls_section();
        $this->start_controls_section(
            'section_switch_style',
            [
                'label' => esc_html__('Switch Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],

            ]
        );

        // Control: Display Flex
        $this->add_control(
            'switch_display_flex',
            [
                'label' => esc_html__('Display Flex', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'flex',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch-container' => 'display: {{VALUE}};',
                ],
                'selectors_dictionary' => [
                    'flex' => 'flex',
                    '' => 'block',
                ],
            ]
        );

        // Control: Flex Direction
        $this->add_responsive_control(
            'switch_flex_direction',
            [
                'label' => esc_html__('Flex Direction', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'row' => [
                        'title' => esc_html__('Row', 'cubewp-framework'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'row-reverse' => [
                        'title' => esc_html__('Row Reverse', 'cubewp-framework'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'column' => [
                        'title' => esc_html__('Column', 'cubewp-framework'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'column-reverse' => [
                        'title' => esc_html__('Column Reverse', 'cubewp-framework'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'row',
                'toggle' => false,
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch-container' => 'flex-direction: {{VALUE}};',
                ],
                'condition' => [
                    'switch_display_flex' => 'flex',
                ],
            ]
        );

        // Control: Justify Content
        $this->add_responsive_control(
            'switch_justify_content',
            [
                'label' => esc_html__('Justify Content', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'flex-start'    => esc_html__('Start', 'cubewp-framework'),
                    'center'        => esc_html__('Center', 'cubewp-framework'),
                    'flex-end'      => esc_html__('End', 'cubewp-framework'),
                    'space-between' => esc_html__('Space Between', 'cubewp-framework'),
                    'space-around'  => esc_html__('Space Around', 'cubewp-framework'),
                    'space-evenly'  => esc_html__('Space Evenly', 'cubewp-framework'),
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch-container' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'switch_display_flex' => 'flex',
                ],
            ]
        );

        // Border Control for .cwp-switch .cwp-switch-slider
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'switch_slider_border',
                'label' => esc_html__('Switch Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-switch .cwp-switch-slider',
            ]
        );

        // Border Radius Control for .cwp-switch .cwp-switch-slider
        $this->add_responsive_control(
            'switch_slider_border_radius',
            [
                'label'      => esc_html__('Switch Border Radius', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-slider' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow Control for .cwp-switch .cwp-switch-slider
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'switch_slider_box_shadow',
                'label' => esc_html__('Switch Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-switch .cwp-switch-slider',
            ]
        );

        // Border Control for .cwp-switch .cwp-switch-slider:before
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'switch_slider_before_border',
                'label' => esc_html__('Slider Handle Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-switch .cwp-switch-slider:before',
            ]
        );

        // Border Radius Control for .cwp-switch .cwp-switch-slider:before
        $this->add_responsive_control(
            'switch_slider_before_border_radius',
            [
                'label'      => esc_html__('Slider Handle Border Radius', 'cubewp-framework'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-slider:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow Control for .cwp-switch .cwp-switch-slider:before
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'switch_slider_before_box_shadow',
                'label' => esc_html__('Slider Handle Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-switch .cwp-switch-slider:before',
            ]
        );

        // Background Color Controls for Switch
        $this->start_controls_tabs('switch_slider_bg_color_tabs');

        // Normal background tab
        $this->start_controls_tab(
            'switch_slider_bg_color_normal_tab',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_normal',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-slider' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_normal_slider',
            [
                'label' => esc_html__('Handle Background Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-slider:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_normal_text_handle',
            [
                'label' => esc_html__('Handle Text Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-text-no' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_normal_text_main',
            [
                'label' => esc_html__('Main Text Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-text-yes' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        // Active (Checked) background tab
        $this->start_controls_tab(
            'switch_slider_bg_color_active_tab',
            [
                'label' => esc_html__('Active', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_active',
            [
                'label' => esc_html__('Active Background Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-field:checked ~ .cwp-switch-slider' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_active_handle',
            [
                'label' => esc_html__('Handle Active Background Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-field:checked ~ .cwp-switch-slider:before' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_active_text_handle',
            [
                'label' => esc_html__('Handle Text Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-field:checked ~ .cwp-switch-text-yes' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'switch_slider_bg_color_active_text_main',
            [
                'label' => esc_html__('Main Text Color', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cwp-switch .cwp-switch-field:checked ~ .cwp-switch-text-no' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
        $this->start_controls_section(
            'section_label_style',
            [
                'label' => esc_html__('Label Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        // Show/Hide Label
        $this->add_control(
            'show_label',
            [
                'label'        => esc_html__('Show Label', 'cubewp-framework'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('Show', 'cubewp-framework'),
                'label_off'    => esc_html__('Hide', 'cubewp-framework'),
                'return_value' => 'yes',
                'default'      => 'yes',
                'selectors'    => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'display: {{VALUE}};',
                ],
                'selectors_dictionary' => [
                    'yes' => 'block',
                    ''    => 'none',
                ],
            ]
        );

        // Text Align
        $this->add_responsive_control(
            'label_text_align',
            [
                'label'     => esc_html__('Text Align', 'cubewp-framework'),
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'left'  => [
                        'title' => esc_html__('Left', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'toggle'    => true,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        // Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-field-container label',
            ]
        );

        // Label Color
        $this->add_control(
            'label_color',
            [
                'label'     => esc_html__('Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Label Background Color
        $this->add_control(
            'label_bg_color',
            [
                'label'     => esc_html__('Background Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Label Margin
        $this->add_responsive_control(
            'label_margin',
            [
                'label'      => esc_html__('Margin', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Label Padding
        $this->add_responsive_control(
            'label_padding',
            [
                'label'      => esc_html__('Padding', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'label_position',
            [
                'label'      => esc_html__('Position', 'cubewp-framework'),
                'type'       => Controls_Manager::SELECT,
                'options'    => [
                    'static'   => 'Static',
                    'absolute' => 'Absolute',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_zindex',
            [
                'label'      => esc_html__('Z-Index', 'cubewp-framework'),
                'type'       => Controls_Manager::NUMBER,
                'default'    => 1,
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'z-index: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_position_top',
            [
                'label'      => esc_html__('Top', 'cubewp-framework'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range'      => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'label_position' => 'absolute',
                ],
            ]
        );
        $this->add_responsive_control(
            'label_position_left',
            [
                'label'      => esc_html__('Left', 'cubewp-framework'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range'      => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container label' => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'label_position' => 'absolute',
                ],
            ]
        );



        $this->end_controls_section();

        // Button Style Section
        $this->start_controls_section(
            'section_button_style',
            [
                'label' => esc_html__('Button Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        // Start tabs for Normal / Hover
        $this->start_controls_tabs('tabs_button_style');

        $this->add_responsive_control(
            'button_width',
            [
                'label' => esc_html__('Width (px)', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10000,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button, {{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn' => 'width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );


        // Normal Tab
        $this->start_controls_tab(
            'tab_button_style_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .cubewp-filter-button, {{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn',
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6752eb',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );



        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .cubewp-filter-button,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 10,
                    'right' => 15,
                    'bottom' => 10,
                    'left' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_shadow',
                'label' => esc_html__('Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-button,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn',
            ]
        );

        //Button Text & Icon Wrapper
        //Flex Direction
        $this->add_control(
            'button_text_icon_flex_direction',
            [
                'label' => esc_html__('Flex Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'row' => esc_html__('Row', 'cubewp-framework'),
                    'column' => esc_html__('Column', 'cubewp-framework'),
                    'column-reverse' => esc_html__('Column Reverse', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-button-content-wrapper' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        //Justify Content
        $this->add_control(
            'button_text_icon_justify_content',
            [
                'label' => esc_html__('Justify Content', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'flex-start' => esc_html__('Flex Start', 'cubewp-framework'),
                    'flex-end' => esc_html__('Flex End', 'cubewp-framework'),
                    'center' => esc_html__('Center', 'cubewp-framework'),
                    'space-between' => esc_html__('Space Between', 'cubewp-framework'),
                    'space-around' => esc_html__('Space Around', 'cubewp-framework'),
                    'space-evenly' => esc_html__('Space Evenly', 'cubewp-framework'),
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-button-content-wrapper' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        //Align Items
        $this->add_control(
            'button_text_icon_align_items',
            [
                'label' => esc_html__('Align Items', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'flex-start' => esc_html__('Flex Start', 'cubewp-framework'),
                    'flex-end' => esc_html__('Flex End', 'cubewp-framework'),
                    'center' => esc_html__('Center', 'cubewp-framework'),
                    'stretch' => esc_html__('Stretch', 'cubewp-framework'),
                    'baseline' => esc_html__('Baseline', 'cubewp-framework'),
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-button-content-wrapper' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        //Gap
        $this->add_responsive_control(
            'button_text_icon_gap',
            [
                'label' => esc_html__('Gap', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5, 
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Business Hours Button Icon Style Section
        $this->add_control(
            'bh_button_icon_style_heading',
            [
                'label' => esc_html__('Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


        // Icon Size
        $this->add_responsive_control(
            'bh_button_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 5,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Icon Spacing
        $this->add_responsive_control(
            'bh_button_icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'size' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.cubewp-icon-left .cubewp-button-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.cubewp-icon-right .cubewp-button-icon' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Icon Normal Color
        $this->add_control(
            'bh_button_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn .cubewp-button-icon svg path' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'tab_button_style_hover',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'button_text_color_hover',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_text_color_active',
            [
                'label' => esc_html__('Text Color Active', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button.active' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_background_hover',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#332589',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button:hover,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_background_active',
            [
                'label' => esc_html__('Background Color Active', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#258933',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button.active,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button:hover' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'button_border_color_active',
            [
                'label' => esc_html__('Border Color Active', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-button.active,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_shadow_hover',
                'label' => esc_html__('Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-button:hover,{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover',
            ]
        );
        // Business Hours Button Icon Style Section
        $this->add_control(
            'bh_button_icon_style_headings',
            [
                'label' => esc_html__('Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        // Icon Hover Color
        $this->add_control(
            'bh_button_icon_color_hover',
            [
                'label' => esc_html__('Icon Hover Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover .cubewp-button-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover .cubewp-button-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn:hover .cubewp-button-icon svg path' => 'fill: {{VALUE}};',
                ],
            ]
        );

        // Icon Active State Color
        $this->add_control(
            'bh_button_icon_color_active',
            [
                'label' => esc_html__('Icon Active Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active .cubewp-button-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active .cubewp-button-icon svg' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-business-hours-buttons .cubewp-business-hours-btn.active .cubewp-button-icon svg path' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();


        // Input Style Section
        $this->start_controls_section(
            'section_input_style',
            [
                'label' => esc_html__('Input/Select Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        // Input Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"], {{WRAPPER}} .cubewp-filter-builder-field input[type="number"], {{WRAPPER}} .cubewp-filter-builder-field input[type="email"], {{WRAPPER}} .cubewp-filter-builder-field input[type="search"], {{WRAPPER}} .cubewp-filter-builder-field input[type="url"], {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"], {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"], {{WRAPPER}} .cubewp-filter-builder-field select, {{WRAPPER}} .cubewp-filter-builder-field textarea, {{WRAPPER}} .cwp-search-field input[type="text"], {{WRAPPER}} .cwp-search-field input[type="number"], {{WRAPPER}} .cwp-search-field input[type="email"], {{WRAPPER}} .cwp-search-field input[type="search"], {{WRAPPER}} .cwp-search-field input[type="url"], {{WRAPPER}} .cwp-search-field input[type="tel"], {{WRAPPER}} .cwp-search-field input[type="google_address"], {{WRAPPER}} .cwp-search-field select, {{WRAPPER}} .cwp-search-field textarea',
            ]
        );

        // Input Color
        $this->add_control(
            'input_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field textarea' => 'color: {{VALUE}};',

                ],
            ]
        );

        $this->add_control(
            'input_hover_color',
            [
                'label' => esc_html__('Text Hover Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="text"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="number"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="email"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="search"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="url"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="tel"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover input[type="google_address"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover select' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field:hover textarea' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="text"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="number"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="email"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="search"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="url"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="tel"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover input[type="google_address"]' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover select' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field:hover textarea' => 'color: {{VALUE}};',

                ],
            ]
        );

        // Placeholder Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_placeholder_typography',
                'label' => esc_html__('Placeholder Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-search-field .select2-container span,{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="number"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="email"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="search"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="url"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]::placeholder, {{WRAPPER}} .cubewp-filter-builder-field textarea::placeholder, {{WRAPPER}} .cwp-search-field input[type="text"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="number"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="email"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="search"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="url"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="tel"]::placeholder, {{WRAPPER}} .cwp-search-field input[type="google_address"]::placeholder, {{WRAPPER}} .cwp-search-field textarea::placeholder',
            ]
        );

        // Placeholder Color
        $this->add_control(
            'input_placeholder_color',
            [
                'label' => esc_html__('Placeholder Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field textarea::placeholder' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field .select2-container span' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        // Select Options Background Color
        $this->add_control(
            'select_option_bg_color',
            [
                'label' => esc_html__('Select Options Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field select option' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select option' => 'background-color: {{VALUE}};',
                    // For select2 dropdown
                    '{{WRAPPER}} .select2-container .select2-dropdown, {{WRAPPER}} .select2-container .select2-results__option' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Select Options Text Color
        $this->add_control(
            'select_option_text_color',
            [
                'label' => esc_html__('Select Options Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field select option' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select option' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .select2-container .select2-results__option' => 'color: {{VALUE}};',
                ],
            ]
        );


        // Input Background Color
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'input_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cwp-search-field .select2-container span,{{WRAPPER}} .cubewp-filter-builder-field input[type="text"], {{WRAPPER}} .cubewp-filter-builder-field input[type="number"], {{WRAPPER}} .cubewp-filter-builder-field input[type="email"], {{WRAPPER}} .cubewp-filter-builder-field input[type="search"], {{WRAPPER}} .cubewp-filter-builder-field input[type="url"], {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"], {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"], {{WRAPPER}} .cubewp-filter-builder-field select, {{WRAPPER}} .cubewp-filter-builder-field textarea, {{WRAPPER}} .cwp-search-field input[type="text"], {{WRAPPER}} .cwp-search-field input[type="number"], {{WRAPPER}} .cwp-search-field input[type="email"], {{WRAPPER}} .cwp-search-field input[type="search"], {{WRAPPER}} .cwp-search-field input[type="url"], {{WRAPPER}} .cwp-search-field input[type="tel"], {{WRAPPER}} .cwp-search-field input[type="google_address"], {{WRAPPER}} .cwp-search-field select, {{WRAPPER}} .cwp-search-field textarea',
            ]
        );

        $this->add_control(
            'input_hover_background',
            [
                'label' => esc_html__('Hover Background', 'cubewp-framework'),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-field .select2-container span:hover,
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="text"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="number"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="email"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="search"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="url"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="tel"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover input[type="google_address"],
            {{WRAPPER}} .cubewp-filter-builder-field:hover select,
            {{WRAPPER}} .cubewp-filter-builder-field:hover textarea,
            {{WRAPPER}} .cwp-search-field:hover input[type="text"],
            {{WRAPPER}} .cwp-search-field:hover input[type="number"],
            {{WRAPPER}} .cwp-search-field:hover input[type="email"],
            {{WRAPPER}} .cwp-search-field:hover input[type="search"],
            {{WRAPPER}} .cwp-search-field:hover input[type="url"],
            {{WRAPPER}} .cwp-search-field:hover input[type="tel"],
            {{WRAPPER}} .cwp-search-field:hover input[type="google_address"],
            {{WRAPPER}} .cwp-search-field:hover select,
            {{WRAPPER}} .cwp-search-field:hover textarea'
                    => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Input Padding
        $this->add_responsive_control(
            'input_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field .select2-selection' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Input Margin
        $this->add_responsive_control(
            'input_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field select' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field textarea' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field .select2-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Input Height
        $this->add_responsive_control(
            'input_height',
            [
                'label' => esc_html__('Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                    'rem' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field select' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .select2-selection.select2-selection--single' => 'height: {{SIZE}}{{UNIT}};',

                ],
            ]
        );

        // Input Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .cubewp-filter-builder-field input[type="text"], {{WRAPPER}} .cubewp-filter-builder-field input[type="number"], {{WRAPPER}} .cubewp-filter-builder-field input[type="email"], {{WRAPPER}} .cubewp-filter-builder-field input[type="search"], {{WRAPPER}} .cubewp-filter-builder-field input[type="url"], {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"], {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"], {{WRAPPER}} .cubewp-filter-builder-field select, {{WRAPPER}} .cubewp-filter-builder-field textarea, {{WRAPPER}} .cwp-search-field input[type="text"], {{WRAPPER}} .cwp-search-field input[type="number"], {{WRAPPER}} .cwp-search-field input[type="email"], {{WRAPPER}} .cwp-search-field input[type="search"], {{WRAPPER}} .cwp-search-field input[type="url"], {{WRAPPER}} .cwp-search-field input[type="tel"], {{WRAPPER}} .cwp-search-field input[type="google_address"], {{WRAPPER}} .cwp-search-field select, {{WRAPPER}} .cwp-search-field textarea',
            ]
        );

        // Input Border Radius
        $this->add_responsive_control(
            'input_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-search-field textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} span.select2-selection.select2-selection--single' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Input Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .select2-selection.select2-selection--single,{{WRAPPER}} .cubewp-filter-builder-field input[type="text"], {{WRAPPER}} .cubewp-filter-builder-field input[type="number"], {{WRAPPER}} .cubewp-filter-builder-field input[type="email"], {{WRAPPER}} .cubewp-filter-builder-field input[type="search"], {{WRAPPER}} .cubewp-filter-builder-field input[type="url"], {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"], {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"], {{WRAPPER}} .cubewp-filter-builder-field select, {{WRAPPER}} .cubewp-filter-builder-field textarea, {{WRAPPER}} .cwp-search-field input[type="text"], {{WRAPPER}} .cwp-search-field input[type="number"], {{WRAPPER}} .cwp-search-field input[type="email"], {{WRAPPER}} .cwp-search-field input[type="search"], {{WRAPPER}} .cwp-search-field input[type="url"], {{WRAPPER}} .cwp-search-field input[type="tel"], {{WRAPPER}} .cwp-search-field input[type="google_address"], {{WRAPPER}} .cwp-search-field select, {{WRAPPER}} .cwp-search-field textarea',
            ]
        );

        // Input Focus State
        $this->add_control(
            'input_focus_heading',
            [
                'label' => esc_html__('Focus State', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Input Focus Color
        $this->add_control(
            'input_focus_color',
            [
                'label' => esc_html__('Focus Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field textarea:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Input Focus Background
        $this->add_control(
            'input_focus_background',
            [
                'label' => esc_html__('Focus Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select:focus' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field textarea:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Input Focus Border Color
        $this->add_control(
            'input_focus_border_color',
            [
                'label' => esc_html__('Focus Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="number"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="email"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="search"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="url"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field select:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-builder-field textarea:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="text"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="number"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="email"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="search"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="url"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="tel"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field input[type="google_address"]:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field select:focus' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-search-field textarea:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        // Input Focus Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_focus_box_shadow',
                'label' => esc_html__('Focus Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-builder-field input[type="text"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="number"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="email"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="search"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="url"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="tel"]:focus, {{WRAPPER}} .cubewp-filter-builder-field input[type="google_address"]:focus, {{WRAPPER}} .cubewp-filter-builder-field select:focus, {{WRAPPER}} .cubewp-filter-builder-field textarea:focus, {{WRAPPER}} .cwp-search-field input[type="text"]:focus, {{WRAPPER}} .cwp-search-field input[type="number"]:focus, {{WRAPPER}} .cwp-search-field input[type="email"]:focus, {{WRAPPER}} .cwp-search-field input[type="search"]:focus, {{WRAPPER}} .cwp-search-field input[type="url"]:focus, {{WRAPPER}} .cwp-search-field input[type="tel"]:focus, {{WRAPPER}} .cwp-search-field input[type="google_address"]:focus, {{WRAPPER}} .cwp-search-field select:focus, {{WRAPPER}} .cwp-search-field textarea:focus',
            ]
        );


        // Input Focus State

        // Section heading for changing the select icon
        $this->add_control(
            'input_change_select_icon_heading',
            [
                'label' => esc_html__('Change Select Icon', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Switcher to enable/disable custom select icon
        $this->add_control(
            'enable_custom_select_icon',
            [
                'label' => esc_html__('Enable Custom Select Icon', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'input_focus_border_color_bg_icon',
            [
                'label' => esc_html__('Focus Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-field select' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        // Icon input (shows only if enabled)
        $this->add_control(
            'custom_select_icon_for_dropdown',
            [
                'label' => esc_html__('Select Icon', 'cubewp-framework'),
                'type'  => \Elementor\Controls_Manager::MEDIA,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => ' background-image: url({{URL}}) !important;position: absolute;background-repeat: no-repeat;
                background-position: center;
                background-size: contain;
                pointer-events: none;  content: "";',
                    '{{WRAPPER}} .cubewp-filter-builder-field-container select' => '-webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; -webkit-appearance: none; -moz-appearance: none;',
                    '{{WRAPPER}} .cubewp-filter-builder-field-container .select2-selection__arrow' => 'display: none;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                ],
            ]
        );


        // Control for icon size
        $this->add_control(
            'custom_select_icon_size',
            [
                'label' => esc_html__('Icon Size (px)', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 200,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 14,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                    'custom_select_icon_for_dropdown[url]!' => '',
                ]
            ]
        );
        // Control for icon right position
        // Right offset (px, %)
        $this->add_control(
            'custom_select_icon_right',
            [
                'label' => esc_html__('Icon Right Offset', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => 'right: {{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                    'custom_select_icon_for_dropdown[url]!' => '',
                ]
            ]
        );
        // Left offset (px, %)
        $this->add_control(
            'custom_select_icon_left',
            [
                'label' => esc_html__('Icon Left Offset', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => 'left: {{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                    'custom_select_icon_for_dropdown[url]!' => '',
                ]
            ]
        );
        // Bottom offset (px, %)
        $this->add_control(
            'custom_select_icon_bottom',
            [
                'label' => esc_html__('Icon Bottom Offset', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => -20,
                        'max' => 60,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => '',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => 'bottom: {{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                    'custom_select_icon_for_dropdown[url]!' => '',
                ]
            ]
        );
        // Control for icon top position
        $this->add_control(
            'custom_select_icon_top',
            [
                'label' => esc_html__('Icon Top Offset (px)', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -20,
                        'max' => 60,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 74,
                    'unit' => '%',
                ],
                'selectors' => [
                    // To leverage pixel or percent based on usage
                    '{{WRAPPER}} .cubewp-filter-builder-field-container::after' => 'top: {{SIZE}}{{UNIT}} !important; transform: translateY(-50%) !important;',
                ],
                'condition' => [
                    'enable_custom_select_icon' => 'yes',
                    'custom_select_icon_for_dropdown[url]!' => '',
                ]
            ]
        );

        $this->end_controls_section();

        // Checkbox/Radio Style Section
        $this->start_controls_section(
            'section_checkbox_radio_style',
            [
                'label' => esc_html__('Checkbox/Radio Style', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                ],
            ]
        );

        // Enable Custom Checkbox/Radio Styling
        $this->add_control(
            'enable_custom_checkbox_radio',
            [
                'label' => esc_html__('Enable Custom Styling', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Enable custom styling for checkboxes and radio buttons using ::before pseudo-element on labels.', 'cubewp-framework'),
            ]
        );

        // Start tabs for Normal / Hover
        $this->start_controls_tabs('tabs_checkbox_radio_style', [
            'condition' => [
                'enable_custom_checkbox_radio' => 'yes',
            ],
        ]);

        // Normal Tab
        $this->start_controls_tab(
            'tab_checkbox_radio_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        // Normal Background Color
        $this->add_control(
            'checkbox_radio_normal_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label::before' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Normal Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'checkbox_radio_normal_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label::before, {{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label::before',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => '1',
                            'right' => '1',
                            'bottom' => '1',
                            'left' => '1',
                            'unit' => 'px',
                        ],
                    ],
                    'color' => [
                        'default' => '#ddd',
                    ],
                ]
            ]
        );

        // Normal Border Radius
        $this->add_responsive_control(
            'checkbox_radio_normal_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                    'unit' => 'px',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Normal Size
        $this->add_responsive_control(
            'checkbox_radio_normal_size',
            [
                'label' => esc_html__('Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 3,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 3,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label::before' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label::before' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Normal Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'checkbox_radio_normal_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label::before, {{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label::before',
            ]
        );

        // Label Typography
        $this->add_control(
            'checkbox_radio_label_heading',
            [
                'label' => esc_html__('Label Style', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Label Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'checkbox_radio_label_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label, {{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Label Color (Normal State)
        $this->add_control(
            'checkbox_radio_label_color',
            [
                'label' => esc_html__('Label Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );


        // Spacing between checkbox/radio and label text
        $this->add_responsive_control(
            'checkbox_radio_label_spacing',
            [
                'label' => esc_html__('Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label' => 'padding-left: calc({{SIZE}}{{UNIT}} + 8px);',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label' => 'padding-left: calc({{SIZE}}{{UNIT}} + 8px);',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // List Item Styling
        $this->add_control(
            'checkbox_radio_list_item_heading',
            [
                'label' => esc_html__('List Item Style', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // List Item Width
        $this->add_responsive_control(
            'checkbox_radio_list_item_width',
            [
                'label' => esc_html__('Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'size' => 47,
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container > li' => 'width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container > li' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // List Item Margin
        $this->add_responsive_control(
            'checkbox_radio_list_item_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container > li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container > li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 7,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );



        // Custom Icon Options
        $this->add_control(
            'checkbox_radio_custom_icon_heading',
            [
                'label' => esc_html__('Custom Icon', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Icon Size
        $this->add_responsive_control(
            'checkbox_radio_icon_size_checked',
            [
                'label' => esc_html__('Icon Checked', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => '✓',
                'description' => esc_html__('Paste the FontAwesome unicode (e.g., ✓ or ● for check) or any Unicode symbol. FontAwesome icons require Font Awesome 5 Free loaded.', 'cubewp-framework'),
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after' => 'content: "{{VALUE}}" !important;',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after' => 'content: "{{VALUE}}" !important;',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Icon Size
        $this->add_responsive_control(
            'checkbox_radio_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 3,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 3,
                    ],
                ],
                'default' => [
                    'size' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Icon Position
        $this->add_responsive_control(
            'checkbox_radio_icon_position',
            [
                'label' => esc_html__('Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after' => 'left: {{LEFT}}{{UNIT}}; top: {{TOP}}{{UNIT}}; transform: translateY(calc(-50% + {{TOP}}{{UNIT}}));',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after' => 'left: {{LEFT}}{{UNIT}}; top: {{TOP}}{{UNIT}}; transform: translateY(calc(-50% + {{TOP}}{{UNIT}}));',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );
        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'tab_checkbox_radio_hover',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        // Hover Background Color
        $this->add_control(
            'checkbox_radio_hover_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label:hover::before' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label:hover::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Hover Border Color
        $this->add_control(
            'checkbox_radio_hover_border_color',
            [
                'label' => esc_html__('Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label:hover::before' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label:hover::before' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        // Hover Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'checkbox_radio_hover_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label:hover::before, {{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label:hover::before',
            ]
        );

        // Label Typography
        $this->add_control(
            'checkbox_radio_label_heading_hover',
            [
                'label' => esc_html__('Label Style', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Label Color (Checked State)
        $this->add_control(
            'checkbox_radio_label_color_hover',
            [
                'label' => esc_html__('Label Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox label:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio label:hover' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );




        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'tab_checkbox_radio_checked',
            [
                'label' => esc_html__('Checked', 'cubewp-framework'),
            ]
        );
        // Checked State
        $this->add_control(
            'checkbox_radio_checked_heading',
            [
                'label' => esc_html__('Checked State', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Checked Background Color
        $this->add_control(
            'checkbox_radio_checked_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::before' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::before' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Checked Border Color
        $this->add_control(
            'checkbox_radio_checked_border_color',
            [
                'label' => esc_html__('Border Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::before' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::before' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );
        $this->add_control(
            'checkbox_radio_checked_icon_color',
            [
                'label' => esc_html__('Checkmark/Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after' => 'color: {{VALUE}};',
                ],
                'default' => '#000',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );



        // Checked Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'checkbox_radio_checked_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::before, {{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );


        // Label Typography
        $this->add_control(
            'checkbox_radio_label_heading_checked',
            [
                'label' => esc_html__('Label Style (Checked)', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );

        // Label Color (Checked State)
        $this->add_control(
            'checkbox_radio_label_color_checked',
            [
                'label' => esc_html__('Label Color (Checked)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox_radio' => 'yes',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();

        // Dropdown Style Section


        // Popup Style Section
        $this->start_controls_section(
            'section_popup_style',
            [
                'label' => esc_html__('Popup Style', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'filters',
                    'field_display_type' => 'popup',
                ],
            ]
        );

        // Custom HTML: Notice that all these settings apply for both Dropdown and Popup
        $this->add_control(
            'popup_dropdown_settings_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div style="margin-bottom: 15px; color: #1e87f0;"><strong>' . esc_html__('Note:') . '</strong> ' . esc_html__('All these settings will apply to both Dropdown and Popup display modes.', 'cubewp-framework') . '</div>',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        // Popup Open Button Style
        $this->add_control(
            'popup_open_button_heading',
            [
                'label' => esc_html__('Popup Open Button', 'cubewp-framework'),
                'type'  => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs('popup_open_button_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'popup_open_button_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );


        $this->add_control(
            'popup_open_btn_bg',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#332589',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'popup_open_btn_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'popup_open_btn_typography',
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button, {{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-text',
            ]
        );
        $this->add_responsive_control(
            'popup_open_btn_icon_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                ],
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_open_btn_border',
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button',
            ]
        );
        $this->add_responsive_control(
            'popup_open_btn_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_open_btn_box_shadow',
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button',
            ]
        );
        // Icon color & size (Normal)
        $this->add_control(
            'popup_open_btn_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'popup_open_btn_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 60,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 5,
                    ],
                    'rem' => [
                        'min' => 0.5,
                        'max' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 18,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-icon svg,{{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-icon i' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'popup_open_btn_icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 40,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                    ],
                    'rem' => [
                        'min' => 0,
                        'max' => 3,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button .cubewp-button-content-wrapper' => 'display: flex; align-items: center; justify-content: center; gap: {{SIZE}}{{UNIT}};',
                    // For RTL, you may want to adjust this
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'popup_open_button_hover',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_open_btn_bg_hover',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button:hover',
            ]
        );

        $this->add_control(
            'popup_open_btn_color_hover',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-button:hover .cubewp-button-text' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_open_btn_border_hover',
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button:hover',
            ]
        );
        $this->add_responsive_control(
            'popup_open_btn_border_radius_hover',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_open_btn_box_shadow_hover',
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-button:hover',
            ]
        );
        // Icon color on hover
        $this->add_control(
            'popup_open_btn_icon_color_hover',
            [
                'label' => esc_html__('Icon Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-button:hover .cubewp-button-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        // Popup Overlay
        $this->add_control(
            'popup_overlay_heading',
            [
                'label' => esc_html__('Overlay', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_overlay_background',
                'label' => esc_html__('Overlay Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-overlay',
            ]
        );

        // Popup Content
        $this->add_control(
            'popup_content_heading',
            [
                'label' => esc_html__('Popup Content', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_content_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-content',
            ]
        );


        $this->add_responsive_control(
            'popup_content_width',
            [
                'label' => esc_html__('Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'],
                'range' => [
                    'px' => [
                        'min' => 300,
                        'max' => 1200,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 300,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'width: {{SIZE}}{{UNIT}}; max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_content_max_height',
            [
                'label' => esc_html__('Max Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh', '%'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1000,
                        'step' => 10,
                    ],
                    'vh' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'vh',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'max-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_content_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'unit' => 'px',
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_content_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_content_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-content',
            ]
        );

        $this->add_responsive_control(
            'popup_content_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'popup_content_z_index',
            [
                'label' => esc_html__('Z-Index', 'cubewp-framework'),
                'type' => Controls_Manager::NUMBER,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-content' => 'z-index: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_content_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-content',
            ]
        );

        // Popup Header
        $this->add_control(
            'popup_header_heading',
            [
                'label' => esc_html__('Header', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_header_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-header',
            ]
        );

        $this->add_responsive_control(
            'popup_header_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_header_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-header',
            ]
        );

        // Header Text
        $this->add_control(
            'popup_header_text_heading',
            [
                'label' => esc_html__('Header Text', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_header_text_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-popup-header-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'popup_header_text_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-popup-header-text',
            ]
        );

        // Close Button
        $this->add_control(
            'popup_close_button_heading',
            [
                'label' => esc_html__('Close Button', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'popup_close_button_color',
            [
                'label' => esc_html__('Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-close i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-close svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_close_button_hover_color',
            [
                'label' => esc_html__('Hover Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-close:hover i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-filter-popup-close:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'popup_close_button_hover_bg',
            [
                'label' => esc_html__('Hover Background', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#332589',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'background-color: {{VALUE}};',
                ],
            ]
        );



        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_close_button_hover_background',
                'label' => esc_html__('Hover Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-close:hover',
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_size',
            [
                'label' => esc_html__('Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 16,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close svg ,{{WRAPPER}} .cubewp-filter-popup-close i' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'popup_close_button_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'top' => 6,
                    'right' => 7,
                    'bottom' => 3,
                    'left' => 7,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'popup_close_button_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-close',
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'unit' => 'px',
                    'top' => 6,
                    'right' => 6,
                    'bottom' => 6,
                    'left' => 6,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );



        // Close Button Position
        $this->add_control(
            'popup_close_button_position',
            [
                'label' => esc_html__('Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'absolute',
                'options' => [
                    'static' => esc_html__('Static', 'cubewp-framework'),
                    'absolute' => esc_html__('Absolute', 'cubewp-framework'),
                    'relative' => esc_html__('Relative', 'cubewp-framework'),
                    'fixed' => esc_html__('Fixed', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_top',
            [
                'label' => esc_html__('Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 38,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'popup_close_button_position' => ['absolute', 'fixed'],
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_right',
            [
                'label' => esc_html__('Right', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'popup_close_button_position' => ['absolute', 'fixed'],
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_bottom',
            [
                'label' => esc_html__('Bottom', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'popup_close_button_position' => ['absolute', 'fixed'],
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_left',
            [
                'label' => esc_html__('Left', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                'range' => [
                    'px' => [
                        'min' => -200,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'popup_close_button_position' => ['absolute', 'fixed'],
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_close_button_z_index',
            [
                'label' => esc_html__('Z-index', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [''],
                'range' => [
                    '' => [
                        'min' => 0,
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-close' => 'z-index: {{SIZE}};',
                ],
                'condition' => [
                    'popup_close_button_position' => ['absolute', 'fixed', 'relative'],
                ],
            ]
        );

        // Popup Body
        $this->add_control(
            'popup_body_heading',
            [
                'label' => esc_html__('Body', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'popup_body_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        // Popup Footer
        $this->add_control(
            'enable_popup_footer',
            [
                'label' => esc_html__('Enable Footer', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'block' => esc_html__('Yes', 'cubewp-framework'),
                    'none' => esc_html__('No', 'cubewp-framework'),
                ],
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-footer' => 'display: {{VALUE}} !important;',
                ],
            ]
        );

        // Popup Footer
        $this->add_control(
            'popup_footer_heading',
            [
                'label' => esc_html__('Footer', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_footer_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-footer',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );


        $this->add_responsive_control(
            'popup_footer_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_footer_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-footer',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        // Apply Button
        $this->add_control(
            'popup_apply_button_heading',
            [
                'label' => esc_html__('Apply Button', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->start_controls_tabs('popup_apply_button_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'popup_apply_button_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_control(
            'popup_apply_button_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );
        $this->add_control(
            'popup_apply_button_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#332589',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'popup_apply_button_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply',
            ]
        );

        $this->add_responsive_control(
            'popup_apply_button_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'unit' => 'px',
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_apply_button_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_apply_button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_apply_button_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply',
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'popup_apply_button_hover',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_control(
            'popup_apply_button_hover_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_apply_button_hover_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply:hover',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_apply_button_hover_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply:hover',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_apply_button_hover_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-filter-popup-apply:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'popup_apply_button_hover_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply:hover',
                'condition' => [
                    'enable_popup_footer' => 'block',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        // ============================================
        // Sorting Style Section
        // ============================================
        $this->start_controls_section(
            'section_sorting_style',
            [
                'label' => esc_html__('Sorting Style', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'filter_type' => 'sorting',
                ],
            ]
        );

        // Sorting Buttons Style (when display type is buttons)
        $this->add_control(
            'sorting_buttons_heading',
            [
                'label' => esc_html__('Sorting Buttons', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );

        $this->start_controls_tabs(
            'sorting_button_tabs',
            [
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );

        // Normal Tab
        $this->start_controls_tab(
            'sorting_button_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'sorting_button_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sorting_button_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#6752eb',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );



        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sorting_button_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-btn',
            ]
        );

        $this->add_responsive_control(
            'sorting_button_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 10,
                    'right' => 15,
                    'bottom' => 10,
                    'left' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_button_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'sorting_button_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-btn',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'sorting_button_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-btn',
            ]
        );

        $this->add_control(
            'sorting_button_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn .cubewp-sorting-btn-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-sorting-btn .cubewp-sorting-btn-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sorting_button_icon_direction',
            [
                'label' => esc_html__('Icon Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'row',
                'options' => [
                    'row' => esc_html__('Left', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Right', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'display: flex; align-items: center; justify-content: center; flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_button_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn .cubewp-sorting-btn-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-sorting-btn .cubewp-sorting-btn-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_button_icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();

        // Active/Hover Tab
        $this->start_controls_tab(
            'sorting_button_active',
            [
                'label' => esc_html__('Active/Hover', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'sorting_button_color_active',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#fff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn.active, {{WRAPPER}} .cubewp-sorting-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'sorting_button_background_active',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#332589',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn.active, {{WRAPPER}} .cubewp-sorting-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'sorting_button_border_active',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-btn.active, {{WRAPPER}} .cubewp-sorting-btn:hover',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'sorting_button_box_shadow_active',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-btn.active, {{WRAPPER}} .cubewp-sorting-btn:hover',
            ]
        );

        $this->add_control(
            'sorting_button_icon_color_active',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-btn.active .cubewp-sorting-btn-icon, {{WRAPPER}} .cubewp-sorting-btn:hover .cubewp-sorting-btn-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-sorting-btn.active .cubewp-sorting-btn-icon svg, {{WRAPPER}} .cubewp-sorting-btn:hover .cubewp-sorting-btn-icon svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // Sorting Dropdown Style (when display type is dropdown)
        $this->add_control(
            'sorting_dropdown_heading',
            [
                'label' => esc_html__('Sorting Dropdown', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Dropdown Toggle Button
        $this->add_control(
            'sorting_dropdown_toggle_heading',
            [
                'label' => esc_html__('Toggle Button', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_toggle_icon',
            [
                'label' => esc_html__('Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'default' => [
                    'value' => '',
                    'library' => '',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_toggle_icon_direction',
            [
                'label' => esc_html__('Icon Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'row' => esc_html__('Row', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle' => 'display: flex;align-items: center; flex-direction: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_toggle_icon_size',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_toggle_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_toggle_icon_color_hover',
            [
                'label' => esc_html__('Icon Color Hover', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle:hover .cubewp-sorting-dropdown-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle:hover .cubewp-sorting-dropdown-icon svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_toggle_icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
                    'body.rtl {{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: 0;',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_responsive_control(
            'sorting_button_icon_offset',
            [
                'label' => esc_html__('Icon Offset', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],

                // ✅ ONLY allow Top & Right
                'allowed_dimensions' => ['top', 'right'],

                'fields_options' => [
                    'top' => [
                        'label' => esc_html__('X', 'cubewp-framework'),
                    ],
                    'right' => [
                        'label' => esc_html__('Y', 'cubewp-framework'),
                    ],
                ],

                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle .cubewp-sorting-dropdown-icon' =>
                    'transform: translate({{RIGHT}}{{UNIT}}, {{TOP}}{{UNIT}});',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_toggle_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_toggle_color_hover',
            [
                'label' => esc_html__('Text Color Hover', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle:hover' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_toggle_background',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_toggle_background_hover',
            [
                'label' => esc_html__('Background Color Hover', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sorting_dropdown_toggle_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-toggle',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_toggle_border_hover',
            [
                'label'    => esc_html__('Hover Border Color', 'cubewp-framework'),
                'type'     => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_toggle_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_toggle_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'sorting_dropdown_toggle_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-toggle',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'sorting_dropdown_toggle_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-toggle',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Dropdown Menu
        $this->add_control(
            'sorting_dropdown_menu_heading',
            [
                'label' => esc_html__('Dropdown Menu', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'sorting_dropdown_menu_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-menu',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_menu_min_width',
            [
                'label' => esc_html__('Minimum Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-menu' => 'min-width: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 150,
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_responsive_control(
            'sorting_dropdown_menu_layout_p',
            [
                'label' => esc_html__('Open Layout', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'flex' => esc_html__('Flex', 'cubewp-framework'),
                    'block' => esc_html__('block', 'cubewp-framework'),
                ],
                'selectors' => [
                    'body {{WRAPPER}} .cubewp-sorting-dropdown-menu.open' => 'display:{{VALUE}} !important;',
                ],
                'default' =>  'block',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );


        $this->add_responsive_control(
            'sorting_dropdown_menu_layout_gap',
            [
                'label' => esc_html__('Gap', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'unit' => 'px',
                    'size' => 150,
                ],
                'selectors' => [
                    'body {{WRAPPER}}  .cubewp-sorting-dropdown-menu.open' => 'flex-direction: row;gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                    'sorting_dropdown_menu_layout_p' => 'flex',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_menu_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_menu_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'sorting_dropdown_menu_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-menu',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'sorting_dropdown_menu_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-menu',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Dropdown Items
        $this->add_control(
            'sorting_dropdown_item_heading',
            [
                'label' => esc_html__('Dropdown Items', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_item_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_item_background_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sorting_dropdown_item_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-item',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Border for Dropdown Item
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'sorting_dropdown_item_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-item',
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_responsive_control(
            'sorting_dropdown_item_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'sorting_dropdown_item_color_hover',
            [
                'label' => esc_html__('Text Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item:hover' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_item_background_hover',
            [
                'label' => esc_html__('Background Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );


        $this->add_control(
            'sorting_dropdown_item_color_selected',
            [
                'label' => esc_html__('Text Color (Selected)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item.selected' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );
        $this->add_control(
            'sorting_dropdown_item_background_selected',
            [
                'label' => esc_html__('Background Color (Selected)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-sorting-dropdown-item.selected' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_google_address_button_style',
            [
                'label' => esc_html__('Near Me Slider', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Container Styles: .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range.cwp-hide
        $this->add_control(
            'google_address_range_container_heading',
            [
                'label' => esc_html__('Range Container', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Padding
        $this->add_responsive_control(
            'google_address_range_container_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        // Margin
        $this->add_responsive_control(
            'google_address_range_container_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        // Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'google_address_range_container_border',
                'selector' => '{{WRAPPER}} .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range',
            ]
        );
        // Border Radius
        $this->add_responsive_control(
            'google_address_range_container_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        // Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'google_address_range_container_box_shadow',
                'selector' => '{{WRAPPER}} .cubewp-google-address-filter-container.active .cubewp-filter-builder-field .cwp-address-range',
            ]
        );

        // Input Range Styling .cwp-address-range .range
        $this->add_control(
            'google_address_range_slider_heading',
            [
                'label' => esc_html__('Range Input', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'google_address_range_slider_bg_color',
            [
                'label' => esc_html__('Slider Track Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range .range' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'google_address_range_slider_height',
            [
                'label' => esc_html__('Slider Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 2,
                        'max' => 32,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range .range' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'google_address_range_slider_margin',
            [
                'label' => esc_html__('Slider Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range .range' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Range Paragraph Styling .cwp-address-range p
        $this->add_control(
            'google_address_range_paragraph_heading',
            [
                'label' => esc_html__('Description Text', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_control(
            'google_address_range_paragraph_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range p' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'google_address_range_paragraph_typography',
                'selector' => '{{WRAPPER}} .cwp-address-range p',
            ]
        );
        $this->add_control(
            'google_address_range_paragraph_margin',
            [
                'label' => esc_html__('Text Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'google_address_range_paragraph_padding',
            [
                'label' => esc_html__('Text Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-address-range p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }


    /**
     * Get fields for a specific post type
     */
    private function get_fields_for_post_type($post_type)
    {
        $fields = [];

        // Add default fields
        $fields['keyword'] = esc_html__('Keyword', 'cubewp-framework');

        // Add taxonomies
        $taxonomies = $this->get_taxonomies_for_post_type($post_type);
        $fields = array_merge($fields, $taxonomies);

        // Add custom fields
        $custom_fields = $this->get_custom_fields_for_post_type($post_type);
        $fields = array_merge($fields, $custom_fields);

        return $fields;
    }

    /**
     * Get default fields options (WordPress default fields like keyword)
     */
    private function get_default_fields_options()
    {
        return [
            'keyword' => esc_html__('Keyword', 'cubewp-framework'),
        ];
    }

    /**
     * Get taxonomies for a post type
     */
    private function get_taxonomies_for_post_type($post_type = 'post')
    {
        $taxonomies_options = [];

        if (empty($post_type)) {
            return $taxonomies_options;
        }

        $taxonomies = get_object_taxonomies($post_type, 'objects');

        if (!empty($taxonomies)) {
            foreach ($taxonomies as $taxonomy_name => $taxonomy) {
                $taxonomies_options[$taxonomy_name] = $taxonomy->label;
            }
        }

        return $taxonomies_options;
    }

    protected function get_term_meta_key_options($post_type = '')
	{
		$options = array('' => esc_html__('Select Term Meta Key', 'cubewp-frontend'));

		if (empty($post_type) || !function_exists('CWP')) {
			return $options;
		}

		// Get all taxonomies for this post type
		$taxonomies = get_object_taxonomies($post_type, 'objects');
		if (empty($taxonomies) || !is_array($taxonomies)) {
			return $options;
		}

		// Get taxonomy custom fields from CubeWP
		$tax_custom_fields = CWP()->get_custom_fields('taxonomy');

		if (!empty($tax_custom_fields) && is_array($tax_custom_fields)) {
			foreach ($taxonomies as $taxonomy) {
				$taxonomy_name = $taxonomy->name;
				
				// Check if this taxonomy has custom fields
				if (isset($tax_custom_fields[$taxonomy_name]) && !empty($tax_custom_fields[$taxonomy_name])) {
					foreach ($tax_custom_fields[$taxonomy_name] as $field) {
						// Only include taxonomy type fields (which can store term relationships)
						if (isset($field['type']) && $field['type'] === 'taxonomy' && isset($field['slug']) && isset($field['name'])) {
							$field_label = !empty($field['label']) ? $field['label'] : $field['name'];
							$options[$field['slug']] = $field_label . ' (' . $taxonomy->label . ')';
						}
					}
				}
			}
		}

		return $options;
	}

    /**
     * Get custom fields for a post type
     */
    private function get_custom_fields_for_post_type($post_type = 'post')
    {
        $fields_options = [];

        if (empty($post_type)) {
            return $fields_options;
        }

        // Allowed field types for filters
        $allowed_field_types = [
            'text',
            'switch',
            'google_address',
            'radio',
            'range',
            'checkbox',
            'dropdown',
            'number',
            'date_picker',
            'business_hours'
        ];

        $is_pro_active = class_exists("CubeWp_Frontend_Load");

        // Get custom fields for this post type
        if (function_exists('get_fields_by_post_type')) {
            $field_names = get_fields_by_post_type($post_type);

            if (!empty($field_names) && is_array($field_names)) {
                $all_custom_fields = CWP()->get_custom_fields('post_types');

                foreach ($field_names as $field_name => $field_label) {
                    if (isset($all_custom_fields[$field_name])) {
                        $field_data = $all_custom_fields[$field_name];
                        $field_type = isset($field_data['type']) ? $field_data['type'] : '';

                        if (!empty($field_type) && in_array($field_type, $allowed_field_types)) {
                            $is_pro_field = false;
                            if (!$is_pro_active) {
                                $groups = function_exists('cwp_get_groups_by_post_type') ? cwp_get_groups_by_post_type($post_type) : [];
                                if (!empty($groups)) {
                                    foreach ($groups as $group_id) {
                                        $group_fields = get_post_meta($group_id, '_cwp_group_fields', true);
                                        if (!empty($group_fields)) {
                                            $group_fields_array = explode(',', $group_fields);
                                            if (in_array($field_name, $group_fields_array)) {
                                                $is_pro_field = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($is_pro_field && !$is_pro_active) {
                                continue;
                            }

                            $label = !empty($field_label) ? $field_label : (isset($field_data['label']) ? $field_data['label'] : $field_name);
                            $fields_options[$field_name] = $label;
                        }
                    }
                }
            }
        }

        // Also check fields directly
        $all_custom_fields = CWP()->get_custom_fields('post_types');
        if (!empty($all_custom_fields)) {
            foreach ($all_custom_fields as $field_name => $field_data) {
                if (isset($fields_options[$field_name])) {
                    continue;
                }

                if (isset($field_data['post_types']) && is_array($field_data['post_types'])) {
                    if (in_array($post_type, $field_data['post_types'])) {
                        $field_type = isset($field_data['type']) ? $field_data['type'] : '';

                        if (!empty($field_type) && in_array($field_type, $allowed_field_types)) {
                            $is_pro_field = false;
                            if (!$is_pro_active) {
                                $groups = function_exists('cwp_get_groups_by_post_type') ? cwp_get_groups_by_post_type($post_type) : [];
                                if (!empty($groups)) {
                                    foreach ($groups as $group_id) {
                                        $group_fields = get_post_meta($group_id, '_cwp_group_fields', true);
                                        if (!empty($group_fields)) {
                                            $group_fields_array = explode(',', $group_fields);
                                            if (in_array($field_name, $group_fields_array)) {
                                                $is_pro_field = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($is_pro_field && !$is_pro_active) {
                                continue;
                            }

                            $label = isset($field_data['label']) ? $field_data['label'] : $field_name;
                            $fields_options[$field_name] = $label;
                        }
                    }
                }
            }
        }

        return $fields_options;
    }

    /**
     * Render widget output on the frontend
     */

    protected function render()
    {



        // Enqueue necessary scripts and styles
        $this->enqueue_filter_scripts();

        $settings = $this->get_settings_for_display();
        $filter_type = isset($settings['filter_type']) ? $settings['filter_type'] : 'filters';
        $show_data_for = isset($settings['show_data_for']) ? $settings['show_data_for'] : 'mobile_desktop';
        $post_type = isset($settings['post_type']) ? $settings['post_type'] : 'post';
        $display_type = isset($settings['field_display_type']) ? $settings['field_display_type'] : 'simple';
        $field_type_selection = isset($settings['field_type_selection']) ? $settings['field_type_selection'] : 'custom_fields';
        $allow_near_me_option = isset($settings['allow_near_me_option' . $post_type]) ? $settings['allow_near_me_option' . $post_type] : 'no';


        if (!cubewp_is_elementor_editing()) {
            if ($show_data_for == 'mobile') {
                if (!wp_is_mobile()) {
                    return;
                }
            } elseif ($show_data_for == 'desktop') {
                if (wp_is_mobile()) {
                    return;
                }
            }
        }
        // If sorting is selected, render sorting instead of filters
        if ($filter_type === 'sorting') {
            $this->render_sorting($settings, $post_type);
            return;
        }

        if ($filter_type === 'reset') {
            $this->render_reset_button($settings, $post_type);
            return;
        }

        // Check if post type is selected
        if (empty($post_type) || $post_type === 'post') {
            // Try to get the first available post type if not set
            $post_types = CWP_all_post_types();
            if (!empty($post_types) && is_array($post_types)) {
                $post_type_keys = array_keys($post_types);
                if (!empty($post_type_keys)) {
                    // Check if current post_type exists in available post types
                    if (!isset($post_types[$post_type])) {
                        $post_type = $post_type_keys[0];
                    }
                }
            }
        }

        // Check for popup repeater fields first
        $popup_fields = [];

        if ($display_type === 'popup') {
            $popup_fields_key = 'popup_fields_' . $post_type;
            $popup_fields = isset($settings[$popup_fields_key]) ? $settings[$popup_fields_key] : [];
            // If empty, try to get from any post type (fallback)
            if (empty($popup_fields)) {
                $post_types = CWP_all_post_types();
                foreach ($post_types as $pt_key => $pt_label) {
                    $pf_key = 'popup_fields_' . $pt_key;
                    if (isset($settings[$pf_key]) && !empty($settings[$pf_key])) {
                        $popup_fields = $settings[$pf_key];
                        $post_type = $pt_key;
                        break;
                    }
                }
            }
        }

        // Get field name or taxonomy name based on selection (for simple display type)
        $field_name = '';
        $taxonomy_name = '';
        $taxonomy_display_type = isset($settings['taxonomy_display_type_' . $post_type]) ? $settings['taxonomy_display_type_' . $post_type] : 'checkbox';
        $taxonomy_placeholder = isset($settings['taxonomy_display_type_placeholder' . $post_type]) ? $settings['taxonomy_display_type_placeholder' . $post_type] : '';
      
        $is_business_hours = false;
        $is_google_address = false;
        $business_hours_options = [];
        $business_hours_button_text = '';
        $business_hours_button_icon = '';
        $business_hours_button_inner_text = '';


        if ($display_type === 'simple') {
            if ($field_type_selection === 'custom_fields') {
                $field_name_key = 'field_name_' . $post_type;
                $field_name = isset($settings[$field_name_key]) ? $settings[$field_name_key] : '';

                // If field_name is empty, try to get it from any post type (fallback)
                if (empty($field_name)) {
                    $post_types = CWP_all_post_types();
                    foreach ($post_types as $pt_key => $pt_label) {
                        $fn_key = 'field_name_' . $pt_key;
                        if (isset($settings[$fn_key]) && !empty($settings[$fn_key])) {
                            $field_name = $settings[$fn_key];
                            $post_type = $pt_key; // Update post_type to match
                            break;
                        }
                    }
                }

                // Check if this is a business hours field
                if (!empty($field_name)) {
                    $is_business_hours = $this->is_business_hours_field($field_name, $post_type);
                    if ($is_business_hours) {
                        $business_hours_options_key = 'business_hours_filter_' . $post_type;
                        $business_hours_options = isset($settings[$business_hours_options_key]) ? $settings[$business_hours_options_key] : 'open_now';
                        $business_hours_button_text_key = 'business_hours_button_text_' . $post_type;
                        $business_hours_button_icon_key = 'business_hours_button_icon_' . $post_type;
                        $business_hours_button_inner_text_key = 'business_hours_button_inner_text_' . $post_type;
                        $business_hours_button_icon = isset($settings[$business_hours_button_icon_key]) ? $settings[$business_hours_button_icon_key] : 'fa fa-clock';
                        $business_hours_button_inner_text = isset($settings[$business_hours_button_inner_text_key]) ? $settings[$business_hours_button_inner_text_key] : esc_html__('Business Hours', 'cubewp-framework');
                        $business_hours_button_text = isset($settings[$business_hours_button_text_key]) ? $settings[$business_hours_button_text_key] : esc_html__('Filter by Hours', 'cubewp-framework');
                    }

                    $is_google_address = $this->is_google_address_field($field_name, $post_type);
                    if ($is_google_address) {
                        $business_hours_options_key = 'business_hours_filter_' . $post_type;
                        $business_hours_options = isset($settings[$business_hours_options_key]) ? $settings[$business_hours_options_key] : 'open_now';
                        $business_hours_button_text_key = 'business_hours_button_text_' . $post_type;
                        $business_hours_button_icon_key = 'business_hours_button_icon_' . $post_type;
                        $business_hours_button_inner_text_key = 'business_hours_button_inner_text_' . $post_type;
                        $business_hours_button_icon = isset($settings[$business_hours_button_icon_key]) ? $settings[$business_hours_button_icon_key] : 'fa fa-clock';
                        $business_hours_button_inner_text = isset($settings[$business_hours_button_inner_text_key]) ? $settings[$business_hours_button_inner_text_key] : esc_html__('Business Hours', 'cubewp-framework');
                        $business_hours_button_text = isset($settings[$business_hours_button_text_key]) ? $settings[$business_hours_button_text_key] : esc_html__('Filter by Hours', 'cubewp-framework');
                    }
                }
            } else {
                $taxonomy_name_key = 'taxonomy_name_' . $post_type;
                $taxonomy_name = isset($settings[$taxonomy_name_key]) ? $settings[$taxonomy_name_key] : '';

                // If taxonomy_name is empty, try to get it from any post type (fallback)
                if (empty($taxonomy_name)) {
                    $post_types = CWP_all_post_types();
                    foreach ($post_types as $pt_key => $pt_label) {
                        $tn_key = 'taxonomy_name_' . $pt_key;
                        if (isset($settings[$tn_key]) && !empty($settings[$tn_key])) {
                            $taxonomy_name = $settings[$tn_key];
                            $post_type = $pt_key; // Update post_type to match
                            break;
                        }
                    }
                }
            }
        }
        // Validate that fields are selected based on display type
        $has_fields = false;
        if ($display_type === 'popup') {
            $has_fields = !empty($popup_fields) && is_array($popup_fields) && count($popup_fields) > 0;
        } else {
            // Simple display type
            $has_fields = !empty($field_name) || !empty($taxonomy_name);
        }

        if (!$has_fields) {
            echo '<div class="cubewp-filter-builder-empty" style="padding: 20px; text-align: center; background: #f5f5f5; border: 1px dashed #ddd; border-radius: 4px; color: #666;">';
            echo '<p style="margin: 0;">' . esc_html__('Please select a field or taxonomy in the widget settings.', 'cubewp-framework') . '</p>';
            if ($display_type === 'popup') {
                echo '<p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">' . esc_html__('Go to Content tab → Field Selections → Add fields using the repeater', 'cubewp-framework') . '</p>';
            } else {
                echo '<p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">' . esc_html__('Go to Content tab → Select Post Type → Select Field Type → Choose a Field or Taxonomy', 'cubewp-framework') . '</p>';
            }
            echo '</div>';
            return;
        }

        // Check if custom checkbox/radio styling is enabled
        $enable_custom_checkbox_radio = isset($settings['enable_custom_checkbox_radio']) && $settings['enable_custom_checkbox_radio'] === 'yes';

        // Check convert settings for adding classes
        $convert_simple_to_accordion = isset($settings['convert_simple_to_accordion']) ? $settings['convert_simple_to_accordion'] : 'no';
        $convert_popup_to_dropdown = isset($settings['convert_popup_to_dropdown']) ? $settings['convert_popup_to_dropdown'] : 'no';

        // Add class to widget wrapper if custom styling is enabled


        // Generate unique ID for this widget instance
        $widget_id = 'cubewp-filter-builder-' . $this->get_id();

        // Start filter container with unique ID and data attributes
        // Using prefix for all classes
        $class_checkbox_enabled = '';
        if ($enable_custom_checkbox_radio) {
            $class_checkbox_enabled .= 'cubewp-custom-checkbox-radio-enabled';
        }

        // Add accordion class if convert simple to accordion is enabled
        $additional_classes = '';
        if ($display_type === 'simple' && $convert_simple_to_accordion === 'yes') {
            $additional_classes .= ' cubewp-filter-accordion-mode';
        }

        // Prepare data attributes for custom icons
        $data_attrs = '';
        echo '<div class="cubewp-filter-builder-container ' . esc_attr($class_checkbox_enabled) . esc_attr($additional_classes) . '" id="' . esc_attr($widget_id) . '" data-post-type="' . esc_attr($post_type) . '" data-widget-id="' . esc_attr($this->get_id()) . '"' . $data_attrs . '>';

        // Render based on display type
        switch ($display_type) {
            case 'simple':
                // Get field icon for simple fields
                $field_icon = [];
                if ($field_type_selection === 'custom_fields' && !empty($field_name)) {
                    $field_icon_key = 'field_icon_' . $post_type;
                    $field_icon = isset($settings[$field_icon_key]) ? $settings[$field_icon_key] : [];
                } elseif ($field_type_selection === 'taxonomies' && !empty($taxonomy_name)) {
                    $taxonomy_icon_key = 'taxonomy_icon_' . $post_type;
                    $field_icon = isset($settings[$taxonomy_icon_key]) ? $settings[$taxonomy_icon_key] : [];
                }

                if ($field_type_selection === 'taxonomies' && !empty($taxonomy_name)) {
                    // Get custom label for taxonomy
                    $taxonomy_label_key = 'taxonomy_label_' . $post_type;
                    $custom_taxonomy_label = isset($settings[$taxonomy_label_key]) ? $settings[$taxonomy_label_key] : '';
                    
                    // Get conditional taxonomy settings for simple display type
                    $enable_conditional_key = 'enable_conditional_taxonomy_' . $post_type;
                    $conditional_taxonomy_key = 'conditional_taxonomy_' . $post_type;
                    $conditional_term_meta_key_key = 'conditional_term_meta_key_' . $post_type;
                    
                    $enable_conditional = isset($settings[$enable_conditional_key]) ? $settings[$enable_conditional_key] : 'no';
                    $conditional_taxonomy = isset($settings[$conditional_taxonomy_key]) ? $settings[$conditional_taxonomy_key] : '';
                    $conditional_term_meta_key = isset($settings[$conditional_term_meta_key_key]) ? $settings[$conditional_term_meta_key_key] : '';
                    
                    
                    $this->render_simple_taxonomy($field_icon, $taxonomy_name, $post_type, $taxonomy_display_type, $custom_taxonomy_label, $taxonomy_placeholder, $enable_conditional, $conditional_taxonomy, $conditional_term_meta_key);
                } elseif ($is_business_hours && !empty($business_hours_options)) {

                    // Pass the single option directly (render function handles both formats)
                    // Get icon position for simple display type
                    $business_hours_button_icon_position_key = 'business_hours_button_icon_position_' . $post_type;
                    $business_hours_button_icon_position = isset($settings[$business_hours_button_icon_position_key]) ? $settings[$business_hours_button_icon_position_key] : (is_rtl() ? 'right' : 'left');
                    $array_settings = array(
                        'field_name' => $field_name,
                        'post_type' => $post_type,
                        'options' => $business_hours_options,
                        'button_text' => $business_hours_button_text,
                        'business_hours_button_inner_text' => $business_hours_button_inner_text,
                        'business_hours_button_icon' => $business_hours_button_icon,
                        'business_hours_button_icon_position' => $business_hours_button_icon_position,
                        'widget_id' => $this->get_id(),
                    );

                    $this->render_business_hours_filter($array_settings);
                } elseif ($is_google_address && !empty($is_google_address) && $allow_near_me_option === 'yes') {
                    // Pass the single option directly (render function handles both formats)
                    // Get icon position for simple display type
                    $business_hours_button_icon_position_key = 'business_hours_button_icon_position_' . $post_type;
                    $business_hours_button_icon_position = isset($settings[$business_hours_button_icon_position_key]) ? $settings[$business_hours_button_icon_position_key] : (is_rtl() ? 'right' : 'left');
                    $array_settings = array(
                        'field_name' => $field_name,
                        'post_type' => $post_type,
                        'options' => $business_hours_options,
                        'button_text' => $business_hours_button_text,
                        'business_hours_button_inner_text' => $business_hours_button_inner_text,
                        'business_hours_button_icon' => $business_hours_button_icon,
                        'business_hours_button_icon_position' => $business_hours_button_icon_position,
                        'widget_id' => $this->get_id(),
                    );
                    echo '<div class="cubewp-google-address-filter-container">';
                    $this->render_google_address_filter($array_settings);
                    $field_label_key = 'field_label_' . $post_type;
                    $custom_field_label = isset($settings[$field_label_key]) ? $settings[$field_label_key] : '';
                    $this->render_simple_field($field_name, $post_type, $field_icon, $custom_field_label, $taxonomy_placeholder);
                    echo '</div>';
                } else {
                    // Get custom label for custom field
                    $field_label_key = 'field_label_' . $post_type;
                    $custom_field_label = isset($settings[$field_label_key]) ? $settings[$field_label_key] : '';
                    $this->render_simple_field($field_name, $post_type, $field_icon, $custom_field_label, $taxonomy_placeholder);
                }
                break;

            case 'popup':
                $button_text = isset($settings['popup_button_text']) ? $settings['popup_button_text'] : esc_html__('Advanced Filters', 'cubewp-framework');
                $button_icon = isset($settings['popup_button_icon']) ? $settings['popup_button_icon'] : [];
                $button_icon_position = isset($settings['popup_button_icon_position']) ? $settings['popup_button_icon_position'] : 'left';
                $popup_header_text = isset($settings['popup_header_text']) ? $settings['popup_header_text'] : esc_html__('Advanced Filters', 'cubewp-framework');
                $popup_position = isset($settings['popup_position']) ? $settings['popup_position'] : 'center';
                $popup_show_close_button = isset($settings['popup_show_close_button']) ? $settings['popup_show_close_button'] : 'yes';
                $popup_close_icon = isset($settings['popup_close_icon']) ? $settings['popup_close_icon'] : [];
                $popup_show_apply_button = isset($settings['popup_show_apply_button']) ? $settings['popup_show_apply_button'] : 'yes';
                $popup_apply_button_text = isset($settings['popup_apply_button_text']) ? $settings['popup_apply_button_text'] : esc_html__('Apply Filters', 'cubewp-framework');
                $popup_apply_button_icon = isset($settings['popup_apply_button_icon']) ? $settings['popup_apply_button_icon'] : [];
                $popup_apply_button_icon_position = isset($settings['popup_apply_button_icon_position']) ? $settings['popup_apply_button_icon_position'] : 'left';

                $array_settings = array(
                    'post_type' => $post_type,
                    'popup_fields' => $popup_fields,
                    'button_text' => $button_text,
                    'button_icon' => $button_icon,
                    'popup_close_icon' => $popup_close_icon,
                    'popup_show_apply_button' => $popup_show_apply_button,
                    'popup_apply_button_text' => $popup_apply_button_text,
                    'popup_apply_button_icon' => $popup_apply_button_icon,
                    'popup_apply_button_icon_position' => $popup_apply_button_icon_position,
                    'widget_id' => $this->get_id(),
                    'button_icon_position' => $button_icon_position,
                    'popup_header_text' => $popup_header_text,
                    'popup_position' => $popup_position,
                    'popup_show_close_button' => $popup_show_close_button,
                    'convert_popup_to_dropdown' => $convert_popup_to_dropdown,
                    'allow_near_me_option' => $allow_near_me_option,
                );
                $this->render_popup_field($array_settings);
                break;
        }

        echo '</div>';
    }


    private function render_reset_button($settings, $post_type)
    {
        $reset_button_text = isset($settings['reset_button_text']) ? $settings['reset_button_text'] : esc_html__('Reset', 'cubewp-framework');
        $reset_button_icon = isset($settings['reset_button_icon']) ? $settings['reset_button_icon'] : [];
        echo '<div class="cubewp-filter-builder-reset-button">';
        echo '<button type="button" class="cubewp-filter-builder-reset-button-button">';
        if (!empty($reset_button_icon['value'])) {
            echo '<span class="cubewp-button-icon">';
            \Elementor\Icons_Manager::render_icon($reset_button_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }
        echo '<span class="cubewp-button-text">' . esc_html($reset_button_text) . '</span>';
        echo '</button>';
        echo '</div>';
    }

    /**
     * Render simple field
     */
    private function render_simple_field($field_name, $post_type, $field_icon = [], $custom_label = '', $placeholder = '')
    {
        // Wrap field output with prefix classes
        echo '<div class="cubewp-filter-builder-field" data-field-name="' . esc_attr($field_name) . '">';
        echo '<div class="cubewp-filter-builder-field-wrapper">';

        // Render field icon if set
        if (!empty($field_icon['value'])) {
            echo '<span class="cubewp-field-icon">';
            \Elementor\Icons_Manager::render_icon($field_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }

        $cwp_search_filters = CWP()->get_form('search_filters');


        if (!empty($custom_label)) {
            $custom_label = $custom_label;
        } else {
            $custom_label = '';
        }
        if (!empty($placeholder)) {
            $placeholder = $placeholder;
        } else {
            $placeholder = '';
        }


        $range_overrides = [];
        $settings = $this->get_settings_for_display();
        $slider_override_key = 'number_range_slider_ui_' . $post_type;
        if (isset($settings[$slider_override_key]) && $settings[$slider_override_key] !== 'inherit') {
            $range_overrides['range_slider_ui'] = ($settings[$slider_override_key] === 'yes') ? '1' : '0';

            $min_key = 'number_range_min_' . $post_type;
            $max_key = 'number_range_max_' . $post_type;
            $step_key = 'number_range_step_' . $post_type;

            if (isset($settings[$min_key]) && $settings[$min_key] !== '' && $settings[$min_key] !== null) {
                $range_overrides['min'] = $settings[$min_key];
            }
            if (isset($settings[$max_key]) && $settings[$max_key] !== '' && $settings[$max_key] !== null) {
                $range_overrides['max'] = $settings[$max_key];
            }
            if (isset($settings[$step_key]) && $settings[$step_key] !== '' && $settings[$step_key] !== null) {
                $range_overrides['step'] = $settings[$step_key];
            }
        }

        if (!empty($cwp_search_filters[$post_type]['fields']) && isset($cwp_search_filters[$post_type]['fields'][$field_name])) {
            $search_filter = $cwp_search_filters[$post_type]['fields'][$field_name];

            // Get the original label from multiple sources
            $field_options = get_field_options($field_name);
            $original_label = isset($field_options['label']) ? $field_options['label'] : '';
            if (empty($original_label) && isset($search_filter['label'])) {
                $original_label = $search_filter['label'];
            }

            // Apply custom label if provided - set it in search_filter so it gets used
            if (!empty($custom_label)) {
                $search_filter['label'] = $custom_label;
            }
            if (!empty($placeholder)) {
                $search_filter['placeholder'] = $placeholder;
            }

            if (!empty($range_overrides)) {
                $search_filter = array_merge($search_filter, $range_overrides);
            }

            $field_output = CubeWp_Frontend_Search_Filter::get_filters_content($search_filter, $field_name);
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);
            echo $field_output;
        } else {
            // Try to render field directly
            $this->render_field_directly($field_name, $post_type, $custom_label, $placeholder, $range_overrides);
        }

        echo '</div>';
        echo '</div>';
    }



    /**
     * Render popup field (popup with multiple fields)
     */
    /**
     * Render popup field (popup with multiple fields)
     */
    private function render_popup_field($array_settings)
    {
        $post_type = isset($array_settings['post_type']) ? $array_settings['post_type'] : '';
        $popup_fields = isset($array_settings['popup_fields']) ? $array_settings['popup_fields'] : [];
        $button_text = isset($array_settings['button_text']) ? $array_settings['button_text'] : '';
        $button_icon = isset($array_settings['button_icon']) ? $array_settings['button_icon'] : [];
        $popup_close_icon = isset($array_settings['popup_close_icon']) ? $array_settings['popup_close_icon'] : [];
        $popup_show_apply_button = isset($array_settings['popup_show_apply_button']) ? $array_settings['popup_show_apply_button'] : 'yes';
        $popup_apply_button_text = isset($array_settings['popup_apply_button_text']) ? $array_settings['popup_apply_button_text'] : '';
        $popup_apply_button_icon = isset($array_settings['popup_apply_button_icon']) ? $array_settings['popup_apply_button_icon'] : [];
        $popup_apply_button_icon_position = isset($array_settings['popup_apply_button_icon_position']) ? $array_settings['popup_apply_button_icon_position'] : 'left';
        $widget_id = isset($array_settings['widget_id']) ? $array_settings['widget_id'] : '';
        $button_icon_position = isset($array_settings['button_icon_position']) ? $array_settings['button_icon_position'] : 'left';
        $popup_header_text = isset($array_settings['popup_header_text']) ? $array_settings['popup_header_text'] : '';
        $popup_position = isset($array_settings['popup_position']) ? $array_settings['popup_position'] : 'center';
        $popup_show_close_button = isset($array_settings['popup_show_close_button']) ? $array_settings['popup_show_close_button'] : 'yes';
        $convert_popup_to_dropdown = isset($array_settings['convert_popup_to_dropdown']) ? $array_settings['convert_popup_to_dropdown'] : 'no';
        $unique_id = 'cubewp-filter-popup-' . $widget_id;
        $popup_id = 'cubewp-filter-popup-content-' . $widget_id;
        // Add dropdown class if convert popup to dropdown is enabled
        $popup_wrapper_class = 'cubewp-filter-popup-wrapper';
        if ($convert_popup_to_dropdown === 'yes') {
            $popup_wrapper_class .= ' cubewp-filter-dropdown-mode';
        }

        echo '<div class="' . $popup_wrapper_class . '">';
        echo '<button type="button" class="cubewp-filter-popup-button" data-target="' . esc_attr($popup_id) . '">';
        echo '<span class="cubewp-button-content-wrapper cubewp-icon-' . esc_attr($button_icon_position) . '">';

        if (!empty($button_icon['value'])) {
            echo '<span class="cubewp-button-icon">';
            \Elementor\Icons_Manager::render_icon($button_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }
        echo '<span class="cubewp-button-text">' . esc_html($button_text) . '</span>';
        echo '</span>';
        echo '</button>';

        // Popup overlay
        echo '<div class="cubewp-filter-popup-overlay" id="' . esc_attr($popup_id) . '-overlay"></div>';

        // Popup content with position class
        echo '<div class="cubewp-filter-popup-content cubewp-popup-position-' . esc_attr($popup_position) . '" id="' . esc_attr($popup_id) . '">';

        // Popup header
        if (!empty($popup_header_text) || $popup_show_close_button === 'yes') {
            echo '<div class="cubewp-filter-popup-header">';
            if (!empty($popup_header_text)) {
                echo '<h3 class="cubewp-popup-header-text">' . esc_html($popup_header_text) . '</h3>';
            }
            if ($popup_show_close_button === 'yes') {
                echo '<button type="button" class="cubewp-filter-popup-close" data-target="' . esc_attr($popup_id) . '">';
                if (!empty($popup_close_icon['value'])) {
                    \Elementor\Icons_Manager::render_icon($popup_close_icon, ['aria-hidden' => 'true']);
                } else {
                    echo '×';
                }
                echo '</button>';
            }
            echo '</div>';
        }

        echo '<div class="cubewp-filter-popup-body">';

        // Add popup fields
        if (!empty($popup_fields) && is_array($popup_fields)) {
            foreach ($popup_fields as $popup_field) {
                $popup_field_type = isset($popup_field['popup_field_type']) ? $popup_field['popup_field_type'] : 'custom_fields';
                $taxonomy_placeholder = isset($popup_field['popup_taxonomy_display_placeholder']) ? $popup_field['popup_taxonomy_display_placeholder'] : '';

                echo '<div class="cubewp-popup-field-wrapper">';

                if ($popup_field_type === 'taxonomies') {
                    $popup_taxonomy_name = isset($popup_field['popup_taxonomy_name']) ? $popup_field['popup_taxonomy_name'] : '';
                    $popup_taxonomy_display = isset($popup_field['popup_taxonomy_display']) ? $popup_field['popup_taxonomy_display'] : 'checkbox';
                    $popup_taxonomy_label = isset($popup_field['popup_taxonomy_label']) ? $popup_field['popup_taxonomy_label'] : '';
                    
                    // Get conditional taxonomy settings for popup display type
                    $popup_enable_conditional = isset($popup_field['popup_enable_conditional_taxonomy']) ? $popup_field['popup_enable_conditional_taxonomy'] : 'no';
                    $popup_conditional_taxonomy = isset($popup_field['popup_conditional_taxonomy']) ? $popup_field['popup_conditional_taxonomy'] : '';
                    $popup_conditional_term_meta_key = isset($popup_field['popup_conditional_term_meta_key']) ? $popup_field['popup_conditional_term_meta_key'] : '';
                    
                    if (!empty($popup_taxonomy_name)) {
                        $field_icon = '';
                        $this->render_simple_taxonomy($field_icon, $popup_taxonomy_name, $post_type, $popup_taxonomy_display, $popup_taxonomy_label, $taxonomy_placeholder, $popup_enable_conditional, $popup_conditional_taxonomy, $popup_conditional_term_meta_key);
                    }
                } else {
                    $popup_field_name = isset($popup_field['popup_field_name']) ? $popup_field['popup_field_name'] : '';
                    if (!empty($popup_field_name)) {
                        // Check if this is a business hours field
                        $is_business_hours = $this->is_business_hours_field($popup_field_name, $post_type);
                        if ($is_business_hours) {
                            $business_hours_options = isset($popup_field['popup_business_hours_filter']) ? $popup_field['popup_business_hours_filter'] : 'open_now';
                            $business_hours_button_text = isset($popup_field['popup_business_hours_button_text']) ? $popup_field['popup_business_hours_button_text'] : esc_html__('Filter by Hours', 'cubewp-framework');
                            $business_hours_button_inner_text = isset($popup_field['popup_business_hours_button_inner_text']) ? $popup_field['popup_business_hours_button_inner_text'] : '';
                            $business_hours_button_icon = isset($popup_field['popup_business_hours_button_icon']) ? $popup_field['popup_business_hours_button_icon'] : [];
                            $business_hours_button_icon_position = isset($popup_field['popup_business_hours_button_icon_position']) ? $popup_field['popup_business_hours_button_icon_position'] : 'left';

                            $array_settings = array(
                                'popup_field_name' => $popup_field_name,
                                'post_type' => $post_type,
                                'business_hours_options' => $business_hours_options,
                                'business_hours_button_text' => $business_hours_button_text,
                                'business_hours_button_inner_text' => $business_hours_button_inner_text,
                                'business_hours_button_icon' => $business_hours_button_icon,
                                'business_hours_button_icon_position' => $business_hours_button_icon_position,
                                'widget_id' => $this->get_id(),
                            );
                            $this->render_business_hours_filter($array_settings);
                        } else {
                            // Check if this is a google address field
                            $is_google_address = $this->is_google_address_field($popup_field_name, $post_type);
                            // Get allow_near_me_option from the repeater field (per-field setting)
                            $popup_allow_near_me_option = isset($popup_field['popup_allow_near_me_option']) ? $popup_field['popup_allow_near_me_option'] : 'no';

                            if ($is_google_address && $popup_allow_near_me_option === 'yes') {
                                // Get settings for google address Near Me button
                                $business_hours_button_text = isset($popup_field['popup_business_hours_button_text']) ? $popup_field['popup_business_hours_button_text'] : esc_html__('Near Me', 'cubewp-framework');
                                $business_hours_button_inner_text = isset($popup_field['popup_business_hours_button_inner_text']) ? $popup_field['popup_business_hours_button_inner_text'] : esc_html__('Near Me', 'cubewp-framework');
                                $business_hours_button_icon = isset($popup_field['popup_business_hours_button_icon']) ? $popup_field['popup_business_hours_button_icon'] : [];
                                $business_hours_button_icon_position = isset($popup_field['popup_business_hours_button_icon_position']) ? $popup_field['popup_business_hours_button_icon_position'] : 'left';
                                $business_hours_options = isset($popup_field['popup_business_hours_filter']) ? $popup_field['popup_business_hours_filter'] : 'open_now';

                                $google_address_settings = array(
                                    'field_name' => $popup_field_name,
                                    'post_type' => $post_type,
                                    'options' => $business_hours_options,
                                    'button_text' => $business_hours_button_text,
                                    'business_hours_button_inner_text' => $business_hours_button_inner_text,
                                    'business_hours_button_icon' => $business_hours_button_icon,
                                    'business_hours_button_icon_position' => $business_hours_button_icon_position,
                                    'widget_id' => $this->get_id(),
                                );

                                echo '<div class="cubewp-google-address-filter-container">';
                                $this->render_google_address_filter($google_address_settings);
                                $popup_field_label = isset($popup_field['popup_field_label']) ? $popup_field['popup_field_label'] : '';
                                $popup_field_icon = isset($popup_field['popup_field_icon']) ? $popup_field['popup_field_icon'] : [];
                                $this->render_simple_field($popup_field_name, $post_type, $popup_field_icon, $popup_field_label, $taxonomy_placeholder);
                                echo '</div>';
                            } else {
                                $popup_field_label = isset($popup_field['popup_field_label']) ? $popup_field['popup_field_label'] : '';
                                $popup_field_icon = isset($popup_field['popup_field_icon']) ? $popup_field['popup_field_icon'] : [];
                                $this->render_simple_field($popup_field_name, $post_type, $popup_field_icon, $popup_field_label, $taxonomy_placeholder);
                            }
                        }
                    }
                }

                echo '</div>';
            }
        }

        echo '</div>';

        // Popup footer
        if ($popup_show_apply_button === 'yes') {
            echo '<div class="cubewp-filter-popup-footer">';
            echo '<button type="button" class="cubewp-filter-popup-apply">';
            echo '<span class="cubewp-button-content-wrapper cubewp-icon-' . esc_attr($popup_apply_button_icon_position) . '">';
            if (!empty($popup_apply_button_icon['value'])) {
                echo '<span class="cubewp-button-icon">';
                \Elementor\Icons_Manager::render_icon($popup_apply_button_icon, ['aria-hidden' => 'true']);
                echo '</span>';
            }
            echo '<span class="cubewp-button-text">' . esc_html($popup_apply_button_text) . '</span>';
            echo '</span>';
            echo '</button>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }


    /**
     * Render simple taxonomy field
     */
    private function render_simple_taxonomy($field_icon, $taxonomy_name, $post_type, $display_type = 'checkbox', $custom_label = '', $taxonomy_placeholder = 'Select Option', $enable_conditional = 'no', $conditional_taxonomy = '', $conditional_term_meta_key = '')
    {
        $container_attrs = '';
        // Add conditional taxonomy attributes if enabled
        if ($enable_conditional === 'yes' && !empty($conditional_taxonomy)) {
            $container_attrs .= ' data-conditional-taxonomy="' . esc_attr($conditional_taxonomy) . '"' .
                ' data-conditional-term-meta-key="' . esc_attr($conditional_term_meta_key) . '"' .
                ' data-conditional-taxonomy-field="taxonomy_' . esc_attr($conditional_taxonomy) . '" style="display:none;"';
        }

        echo '<div class="cubewp-filter-builder-field" data-field-name="' . esc_attr($taxonomy_name) . '" ' . $container_attrs . '>';
        if (!empty($field_icon['value'])) {
            echo '<span class="cubewp-field-icon">';
            \Elementor\Icons_Manager::render_icon($field_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if (isset($taxonomies[$taxonomy_name])) {
            $taxonomy = $taxonomies[$taxonomy_name];

            // Map display types to CubeWP format 
            $cubewp_display_type = $display_type;
            if ($display_type === 'radio') {
                $cubewp_display_type = 'checkbox';
            } elseif ($display_type === 'multi_select') {
                $cubewp_display_type = 'multi_select';
            } elseif ($display_type === 'select') {
                $cubewp_display_type = 'select';
            } elseif ($display_type === 'select2') {
                $cubewp_display_type = 'select';
            } else {
                $cubewp_display_type = 'checkbox';
            }

            // Use custom label if provided, otherwise use default taxonomy label
            $field_label = !empty($custom_label) ? $custom_label : $taxonomy->label;

            $search_filter = [
                'label' => $field_label,
                'name' => $taxonomy->name,
                'placeholder' => $taxonomy_placeholder,
                'type' => 'taxonomy',
                'display_ui' => $cubewp_display_type,
                'appearance' => $cubewp_display_type,
                'select2_ui' => $display_type === 'select2',
            ];

            // Add data attribute for radio handling
            if ($display_type === 'radio') {
                $search_filter['radio_mode'] = true;
            }



            $field_output = CubeWp_Frontend_Search_Filter::get_filters_taxonomy($search_filter, $taxonomy_name);

            // Add prefix classes first
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);

            // For radio mode, add class to convert checkboxes to radio behavior
            if ($display_type === 'radio') {
                $field_output = str_replace('cwp-field-checkbox-container', 'cwp-field-checkbox-container cubewp-radio-mode', $field_output);
                // Add data-radio-mode attribute to checkboxes using regex
                $field_output = preg_replace('/(<input[^>]*type=["\']checkbox["\'][^>]*)(>)/i', '$1 data-radio-mode="1"$2', $field_output);
            }

            echo $field_output;
        }

        echo '</div>';
    }


    /**
     * Check if a field is a business hours field
     */
    private function is_business_hours_field($field_name, $post_type)
    {
        if (empty($field_name)) {
            return false;
        }

        $all_custom_fields = CWP()->get_custom_fields('post_types');
        if (isset($all_custom_fields[$field_name])) {
            $field_data = $all_custom_fields[$field_name];
            $field_type = isset($field_data['type']) ? $field_data['type'] : '';
            return $field_type === 'business_hours';
        }

        return false;
    }

    /**
     * Check if a field is a google address field
     */
    private function is_google_address_field($field_name, $post_type)
    {
        if (empty($field_name)) {
            return false;
        }

        $all_custom_fields = CWP()->get_custom_fields('post_types');
        if (isset($all_custom_fields[$field_name])) {
            $field_data = $all_custom_fields[$field_name];
            $field_type = isset($field_data['type']) ? $field_data['type'] : '';
            return $field_type === 'google_address';
        }

        return false;
    }

    /**
     * Render business hours filter buttons
     */
    private function render_business_hours_filter($array_settings)
    {
        $field_name = isset($array_settings['field_name']) ? $array_settings['field_name'] : '';
        $post_type = isset($array_settings['post_type']) ? $array_settings['post_type'] : '';
        $options = isset($array_settings['options']) ? $array_settings['options'] : [];
        $button_text = isset($array_settings['button_text']) ? $array_settings['button_text'] : '';
        $business_hours_button_inner_text = isset($array_settings['business_hours_button_inner_text']) ? $array_settings['business_hours_button_inner_text'] : '';
        $business_hours_button_icon = isset($array_settings['business_hours_button_icon']) ? $array_settings['business_hours_button_icon'] : [];
        $icon_position = isset($array_settings['business_hours_button_icon_position']) ? $array_settings['business_hours_button_icon_position'] : 'left';
        $index = isset($array_settings['widget_id']) ? $array_settings['widget_id'] : '';
        // Handle both array (legacy/converted) and string (single select) formats
        if (is_array($options)) {
            $option = !empty($options) ? $options[0] : 'open_now';
        } else {
            $option = !empty($options) ? $options : 'open_now';
        }

        if (empty($option)) {
            return;
        }

        $unique_id = 'cubewp-business-hours-' . $index;

        echo '<div class="cubewp-filter-builder-field cubewp-business-hours-filter" data-field-name="' . esc_attr($field_name) . '" data-post-type="' . esc_attr($post_type) . '">';
        echo '<div class="cubewp-filter-builder-field-container cwp-field-container">';
        echo '<label class="cubewp-filter-builder-label">' . esc_html($button_text) . '</label>';
        echo '<div class="cubewp-business-hours-buttons" id="' . esc_attr($unique_id) . '">';

        $option_labels = [
            'open_now' => esc_html__('Open Now', 'cubewp-framework'),
            'closed_now' => esc_html__('Closed Now', 'cubewp-framework'),
            'open_24_hours' => esc_html__('Open 24 Hours', 'cubewp-framework'),
            'day_off' => esc_html__('Day Off', 'cubewp-framework'),
        ];

        // Render single button for the selected option
        $option_id = $unique_id . '-' . $option;

        // Use icon position from parameter, fallback to settings if not provided
        if (empty($icon_position)) {
            $settings = $this->get_settings_for_display();
            $icon_position_key = 'business_hours_button_icon_position_' . $post_type;
            $icon_position = isset($settings[$icon_position_key]) ? $settings[$icon_position_key] : (is_rtl() ? 'right' : 'left');
        }

        // Build button classes
        $button_classes = 'cubewp-business-hours-btn';
        if (!empty($business_hours_button_icon) && isset($business_hours_button_icon['value']) && !empty($business_hours_button_icon['value'])) {
            $button_classes .= ' cubewp-icon-' . esc_attr($icon_position);
        }

        echo '<button type="button" class="' . esc_attr($button_classes) . '" id="' . esc_attr($option_id) . '" data-filter-type="' . esc_attr($option) . '" data-field-name="' . esc_attr($field_name) . '">';

        // Build button content wrapper
        $has_icon = !empty($business_hours_button_icon) && isset($business_hours_button_icon['value']) && !empty($business_hours_button_icon['value']);
        $button_text = !empty($business_hours_button_inner_text) ? $business_hours_button_inner_text : $option_labels[$option];

        if ($has_icon) {
            echo '<span class="cubewp-button-content-wrapper">';

            echo '<span class="cubewp-button-icon">';
            if (class_exists('\Elementor\Icons_Manager')) {
                \Elementor\Icons_Manager::render_icon($business_hours_button_icon, ['aria-hidden' => 'true']);
            }
            echo '</span>';
            // Button text
            if (!empty($button_text)) {
                echo '<span class="cubewp-button-text">' . esc_html($button_text) . '</span>';
            }



            echo '</span>';
        } else {
            // No icon, just text
            echo esc_html($button_text);
        }

        echo '</button>';

        echo '</div>';
        // Hidden input to store the selected filter value - make it unique per widget instance
        // Use widget ID in the name to ensure uniqueness when multiple widgets use the same field
        $unique_input_name = $field_name . '_status';
        echo '<input type="hidden" name="' . esc_attr($unique_input_name) . '" class="cubewp-business-hours-status" data-widget-id="' . esc_attr($index) . '" data-field-name="' . esc_attr($field_name) . '" value="">';
        echo '</div>';
        echo '</div>';
    }
    /**
     * Render google address filter buttons
     */
    private function render_google_address_filter($array_settings)
    {
        $field_name = isset($array_settings['field_name']) ? $array_settings['field_name'] : '';
        $post_type = isset($array_settings['post_type']) ? $array_settings['post_type'] : '';
        $button_text = isset($array_settings['button_text']) ? $array_settings['button_text'] : '';
        $business_hours_button_inner_text = isset($array_settings['business_hours_button_inner_text']) ? $array_settings['business_hours_button_inner_text'] : '';
        $business_hours_button_icon = isset($array_settings['business_hours_button_icon']) ? $array_settings['business_hours_button_icon'] : [];
        $icon_position = isset($array_settings['icon_position']) ? $array_settings['icon_position'] : 'left';
        $index = isset($array_settings['widget_id']) ? $array_settings['widget_id'] : '';
        // Handle both array (legacy/converted) and string (single select) formats


        $unique_id = 'cubewp-google-address-' . $index;

        echo '<div class="cubewp-filter-builder-field cubewp-google-address-filter" data-field-name="' . esc_attr($field_name) . '" data-post-type="' . esc_attr($post_type) . '">';
        echo '<div class="cubewp-filter-builder-field-container cwp-field-container">';
        echo '<label class="cubewp-filter-builder-label">' . esc_html($button_text) . '</label>';
        echo '<div class="cubewp-business-hours-buttons" id="' . esc_attr($unique_id) . '">';


        // Render single button for the selected option
        $option_id = $unique_id . '-' . 'near_me';

        // Use icon position from parameter, fallback to settings if not provided
        if (empty($icon_position)) {
            $settings = $this->get_settings_for_display();
            $icon_position_key = 'business_hours_button_icon_position_' . $post_type;
            $icon_position = isset($settings[$icon_position_key]) ? $settings[$icon_position_key] : (is_rtl() ? 'right' : 'left');
        }

        // Build button classes
        $button_classes = 'cubewp-business-hours-btn google-address-btn';
        if (!empty($business_hours_button_icon) && isset($business_hours_button_icon['value']) && !empty($business_hours_button_icon['value'])) {
            $button_classes .= ' cubewp-icon-' . esc_attr($icon_position);
        }

        echo '<button type="button" class="' . esc_attr($button_classes) . '" id="' . esc_attr($option_id) . '" data-filter-type="' . esc_attr('near_me') . '" data-field-name="' . esc_attr($field_name) . '">';

        // Build button content wrapper
        $has_icon = !empty($business_hours_button_icon) && isset($business_hours_button_icon['value']) && !empty($business_hours_button_icon['value']);
        $button_text = !empty($business_hours_button_inner_text) ? $business_hours_button_inner_text : $button_text;

        if ($has_icon) {
            echo '<span class="cubewp-button-content-wrapper">';

            // Icon on left
            if ($icon_position === 'left') {
                echo '<span class="cubewp-button-icon">';
                if (class_exists('\Elementor\Icons_Manager')) {
                    \Elementor\Icons_Manager::render_icon($business_hours_button_icon, ['aria-hidden' => 'true']);
                }
                echo '</span>';
            }

            // Button text
            if (!empty($button_text)) {
                echo '<span class="cubewp-button-text">' . esc_html($button_text) . '</span>';
            }

            // Icon on right
            if ($icon_position === 'right') {
                echo '<span class="cubewp-button-icon">';
                if (class_exists('\Elementor\Icons_Manager')) {
                    \Elementor\Icons_Manager::render_icon($business_hours_button_icon, ['aria-hidden' => 'true']);
                }
                echo '</span>';
            }

            echo '</span>';
        } else {
            // No icon, just text
            echo esc_html($button_text);
        }

        echo '</button>';

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render field directly (fallback when not in form builder)
     */
    private function render_field_directly($field_name, $post_type, $custom_label, $placeholder, $overrides = [])
    {
        // Check if it's a taxonomy
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if (isset($taxonomies[$field_name])) {
            $taxonomy = $taxonomies[$field_name];
            $search_filter = [
                'label' => $custom_label,
                'placeholder' => $placeholder,
                'name' => $taxonomy->name,
                'type' => 'taxonomy',
                'display_ui' => 'checkbox',
            ];
            $field_output = CubeWp_Frontend_Search_Filter::get_filters_taxonomy($search_filter, $field_name);
            // Add prefix classes
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);
            echo $field_output;
            return;
        }

        // Check if it's keyword
        if ($field_name === 'keyword') {
            $custom_label = !empty($custom_label) ? $custom_label : esc_html__('Keyword', 'cubewp-framework');
            $placeholder = !empty($placeholder) ? $placeholder : esc_html__('Search...', 'cubewp-framework');
            echo '<div class="cubewp-filter-builder-field-container cwp-field-container">';
            echo '<label class="cubewp-filter-builder-label">' . esc_html($custom_label) . '</label>';
            $keyword_value = isset($_GET['keyword']) && !empty($_GET['keyword']) ? sanitize_text_field(wp_unslash($_GET['keyword'])) : '';
            echo '<input type="text" name="s" class="cubewp-filter-builder-input" value="' . esc_attr($keyword_value) . '" placeholder="' . esc_attr($placeholder) . '" />';
            echo '</div>';
            return;
        }

        // Try to get custom field
        $field_options = get_field_options($field_name);
        if (!empty($field_options)) {
            // Merge Search Filters form-builder settings (e.g. range_slider_ui)
            $cwp_search_filters = CWP()->get_form('search_filters');
            if (isset($cwp_search_filters[$post_type]['fields'][$field_name]) && is_array($cwp_search_filters[$post_type]['fields'][$field_name])) {
                $field_options = array_merge($field_options, $cwp_search_filters[$post_type]['fields'][$field_name]);
            }

            if (!empty($overrides) && is_array($overrides)) {
                $field_options = array_merge($field_options, $overrides);
            }

            $field_type = isset($field_options['type']) ? $field_options['type'] : '';
            $fieldOptions = array_merge($field_options, [
                'label' => isset($field_options['label']) ? $field_options['label'] : $field_name,
                'name' => $field_name,
                'class' => 'cubewp-filter-builder-input',
                'container_class' => 'cubewp-filter-builder-field-container',
                'placeholder' => $placeholder,
            ]);

            if (isset($_GET[$field_name]) && !empty($_GET[$field_name])) {
                $fieldOptions['value'] = sanitize_text_field(wp_unslash($_GET[$field_name]));
            }

            $field_output = apply_filters("cubewp/search_filters/{$field_type}/field", '', $fieldOptions);
            // Ensure prefix classes are added
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);
            echo $field_output;
        } else {
            $field_output = apply_filters("cubewp/search_filter_builder/{$field_name}/field", '', $field_name, $post_type);
            if(!empty($field_output)) {
                echo $field_output;
            }
        }
    }

    /**
     * Enqueue necessary scripts and styles
     */
    private function enqueue_filter_scripts()
    {
        CubeWp_Enqueue::enqueue_script('cwp-search-filters');
        CubeWp_Enqueue::enqueue_script('cwp-filter-builder');
        CubeWp_Enqueue::enqueue_script('select2');
        CubeWp_Enqueue::enqueue_style('select2');
        CubeWp_Enqueue::enqueue_script('jquery-ui-datepicker');
        CubeWp_Enqueue::enqueue_style('frontend-fields');
        CubeWp_Enqueue::enqueue_script('cwp-frontend-fields');
        new CubeWp_Frontend();
    }

    /**
     * Render sorting options
     */
    private function render_sorting($settings, $post_type)
    {
        $sorting_display_type = isset($settings['sorting_display_type']) ? $settings['sorting_display_type'] : 'buttons';
        $sorting_title_default = isset($settings['sorting_display_type_title_default']) ? $settings['sorting_display_type_title_default'] : ['Sorting'];
        $sorting_button_icon = isset($settings['sorting_button_icon']) ? $settings['sorting_button_icon'] : [];
        $sorting_dropdown_toggle_icon = isset($settings['sorting_dropdown_toggle_icon']) ? $settings['sorting_dropdown_toggle_icon'] : [];
        $sorting_button_text = isset($settings['sorting_button_text']) ? $settings['sorting_button_text'] : '';
        $sorting_button_display_type = 'text';



        $sorting_field = isset($settings['sorting_number_field_button_' . $post_type]) ? $settings['sorting_number_field_button_' . $post_type] : 'DESC';
        $sorting_number_operation_button = isset($settings['sorting_number_operation_button_' . $post_type]) ? $settings['sorting_number_operation_button_' . $post_type] : '';
        $sorting_number_value_button = isset($settings['sorting_number_value_button_' . $post_type]) ? $settings['sorting_number_value_button_' . $post_type] : '';
        $sorting_number_value_between_button = isset($settings['sorting_number_value_between_button_' . $post_type]) ? $settings['sorting_number_value_between_button_' . $post_type] : '';

        $sorting_number_field_button_filter_type = isset($settings['sorting_number_field_button_filter_type_' . $post_type]) ? $settings['sorting_number_field_button_filter_type_' . $post_type] : '';
        $sorting_number_field_button_filter_type_value = isset($settings['sorting_number_field_button_filter_type_value_' . $post_type]) ? $settings['sorting_number_field_button_filter_type_value_' . $post_type] : '';
        $sorting_number_fields_repeater = isset($settings['sorting_number_fields_repeater_' . $post_type]) ? $settings['sorting_number_fields_repeater_' . $post_type] : '';



        if ($sorting_number_operation_button === 'BETWEEN' || $sorting_number_operation_button === 'NOT BETWEEN') {
            $raw_value = trim($sorting_number_value_between_button);
            if (strpos($raw_value, ',') === false) {
                echo esc_html__('Please enter two values separated by a comma (e.g. 10,50).', 'cubewp-framework');
                return;
            }
            $sorting_number_value_dropdown = preg_replace('/\s*,\s*/', ',', $raw_value);
        } else {
            $sorting_number_value_dropdown = $sorting_number_value_button;
        }


        // Get current orderby value from URL
        $current_orderby = isset($_GET['orderby']) && !empty($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : '';

        $widget_id = 'cubewp-sorting-' . $this->get_id();



        echo '<div class="cubewp-sorting-container" id="' . esc_attr($widget_id) . '" data-post-type="' . esc_attr($post_type) . '" data-button-display-type="' . esc_attr($sorting_button_display_type) . '">';

        if ($sorting_display_type === 'buttons') {
            // Single field for buttons
            $render_sorting_buttons = array(
                'sorting_field' => $sorting_field,
                'sorting_button_text' => $sorting_button_text,
                'sorting_button_icon' => $sorting_button_icon,
                'post_type' => $post_type,
                'display_type' => $sorting_button_display_type,
                'sorting_number_field_button' => $sorting_field,
                'sorting_number_operation_dropdown' => $sorting_number_operation_button,
                'sorting_number_value_dropdown' => $sorting_number_value_dropdown,
                'current_orderby' => $current_orderby,
                'sorting_number_field_button_filter_type' => $sorting_number_field_button_filter_type,
                'sorting_number_field_button_filter_type_value' => $sorting_number_field_button_filter_type_value,
            );
            $this->render_sorting_buttons_single($render_sorting_buttons);
        } else {
            // Multiple fields for dropdown
            $render_sorting_dropdown = array(
                'sorting_fields' => $sorting_number_fields_repeater,
                'all_options' => '',
                'current_orderby' => $current_orderby,
                'post_type' => $post_type,
                'toggle_icon' => $sorting_dropdown_toggle_icon,
                'sorting_display_type_custom' => 'dropdown',
                'sorting_display_type_title_default' => $sorting_title_default,
                'sorting_number_fields_dropdown' => $sorting_field,
                'sorting_number_operation_dropdown' => '',
                'sorting_number_value_dropdown' => $sorting_number_value_dropdown,
            );
            $this->render_sorting_dropdown_multiple($render_sorting_dropdown);
        }

        echo '</div>';
    }

    /**
     * Render sorting as single button
     */
    private function render_sorting_buttons_single($render_sorting_buttons)
    {
        $sorting_field = $render_sorting_buttons['sorting_field'];
        $button_text = $render_sorting_buttons['sorting_button_text'];
        $button_icon = $render_sorting_buttons['sorting_button_icon'];
        $post_type = $render_sorting_buttons['post_type'];
        $sorting_number_operation_dropdown = $render_sorting_buttons['sorting_number_operation_dropdown'];
        $sorting_number_value_dropdown = $render_sorting_buttons['sorting_number_value_dropdown'];

        $sorting_number_field_button_filter_type = $render_sorting_buttons['sorting_number_field_button_filter_type'];
        $sorting_number_field_button_filter_type_value = $render_sorting_buttons['sorting_number_field_button_filter_type_value'];
        // Process field to get actual field name



        $actual_sorting_field = $this->process_sorting_field($sorting_field, $post_type);

        // Check if this is a rating field  
        // Get label - use custom button text if provided, otherwise use option label
        $field_label = !empty($button_text) ? $button_text : (isset($all_options[$sorting_field]) ? $all_options[$sorting_field] : $sorting_field);

        echo '<div class="cubewp-sorting-buttons">';


        if ($sorting_field !== 'DESC' && $sorting_field !== 'ASC' && $sorting_field !== 'title' && $sorting_field !== 'rand' && $sorting_field !== 'relevance' && $sorting_field !== 'most_viewed') {
            $dat_sorting = '';
            if ($sorting_number_field_button_filter_type === 'sorting') {
                $dat_sorting = $sorting_number_field_button_filter_type_value;
            }

            echo '<button type="button" class="cubewp-sorting-btn cubewp-sorting-btn-custom " data-sortings="' . esc_attr($dat_sorting) . '" data-orderby="' . esc_attr($sorting_field) . '"  data-operation="' . esc_attr($sorting_number_operation_dropdown) . '" data-value="' . esc_attr($sorting_number_value_dropdown) . '">';
            if (!empty($button_icon['value'])) {
                echo '<span class="cubewp-sorting-btn-icon">';
                \Elementor\Icons_Manager::render_icon($button_icon, ['aria-hidden' => 'true']);
                echo '</span>';
            }

            echo '<span class="cubewp-sorting-btn-text">' . esc_html($field_label) . '</span>';

            echo '</button>';
        } else {

            echo '<button type="button" class="cubewp-sorting-btn" data-orderby="' . esc_attr($actual_sorting_field) . '" data-display-field="' . esc_attr($sorting_field) . '">';
            if (!empty($button_icon['value'])) {
                echo '<span class="cubewp-sorting-btn-icon">';
                \Elementor\Icons_Manager::render_icon($button_icon, ['aria-hidden' => 'true']);
                echo '</span>';
            }

            echo '<span class="cubewp-sorting-btn-text">' . esc_html($field_label) . '</span>';

            echo '</button>';
        }
        echo '</div>';
    }

    /**
     * Process sorting field to get actual field name
     */
    private function process_sorting_field($sorting_field, $post_type)
    {
        $reviews_active = defined('CUBEWP_REVIEWS') || class_exists('CubeWp_Reviews_Load');
        $actual_field = $sorting_field;

        // Handle reviews sorting fields
        if ($reviews_active) {
            if (strpos($sorting_field, 'rating_') === 0) {
                $star_count = str_replace('rating_', '', $sorting_field);
                $actual_field = 'rating_' . $star_count;
            } elseif ($sorting_field === 'most_viewed') {
                $actual_field = 'post_views';
            } elseif ($sorting_field === 'high_rated') {
                $actual_field = 'average_rating';
            }
        }

        return $actual_field;
    }

    /**
     * Render sorting as dropdown with multiple options
     */
    private function render_sorting_dropdown_multiple($render_sorting_dropdown)
    {
        $sorting_fields = $render_sorting_dropdown['sorting_fields'];
        $current_orderby = $render_sorting_dropdown['current_orderby'];
        $toggle_icon = $render_sorting_dropdown['toggle_icon'];
        $sorting_display_type_title_default = $render_sorting_dropdown['sorting_display_type_title_default'];
        $dropdown_id = 'cubewp-sorting-dropdown-' . $this->get_id();





        echo '<div class="cubewp-sorting-dropdown" id="' . esc_attr($dropdown_id) . '">';
        echo '<button type="button" class="cubewp-sorting-dropdown-toggle" data-button-text="' . esc_attr($sorting_display_type_title_default ?? 'Sort By') . '">';
        echo '<span class="cubewp-sorting-dropdown-text">' . esc_html($sorting_display_type_title_default ?? 'Sort By') . '</span>';
        if (!empty($toggle_icon['value'])) {
            echo '<span class="cubewp-sorting-dropdown-icon">';
            \Elementor\Icons_Manager::render_icon($toggle_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }
        echo '</button>';

        echo '<ul class="cubewp-sorting-dropdown-menu" style="display: block;">';


        // Multiple options
        // cwp_pre($sorting_fields);
        foreach ($sorting_fields as $field) {
            $title = $field['title'];
            $field_name = $field['field'] ?? '';
            $operation = $field['operation'] ?? '';
            $value = $field['value'] ?? '';

            $value_between = $field['value_between'] ?? '';
            $filter_types = $field['filter_types'] ?? '';

            $filter_types_value = $field['filter_types_value'] ?? '';


            $active_class = $current_orderby === $field_name ? 'selected' : '';
            if ($operation === 'BETWEEN' || $operation === 'NOT BETWEEN') {
                $raw_value = trim($value_between);
                if (strpos($raw_value, ',') === false) {
                    echo esc_html__('Please enter two values separated by a comma (e.g. 10,50).', 'cubewp-framework');
                    continue;
                }
                $value_between = preg_replace('/\s*,\s*/', ', ', $raw_value);
            } else {
                $value_between = $value;
            }

            // Check if this is a rating field and should display as stars 
            if ($field_name !== 'DESC' && $field_name !== 'ASC' && $field_name !== 'title' && $field_name !== 'rand' && $field_name !== 'relevance' && $field_name !== 'most_viewed') {

                $dat_sorting = '';
                if ($filter_types === 'sorting') {
                    $dat_sorting = $filter_types_value;
                }


                echo '<li class="cubewp-sorting-dropdown-item cubewp-sorting-btn-custom cubewp-sorting-btn ' . esc_attr($active_class) . '" data-sortings="' . esc_attr($dat_sorting) . '" data-orderby="' . esc_attr($field_name) . '" data-operation="' . esc_attr($operation) . '" data-value="' . esc_attr($value_between) . '">';
                echo '<span class="cubewp-sorting-dropdown-item-text">' . esc_html($title) . '</span>';
                echo '</li>';
            } else {
                echo '<li class="cubewp-sorting-dropdown-item cubewp-sorting-btn ' . esc_attr($active_class) . '" data-orderby="' . esc_attr($field_name) . '" data-display-field="' . esc_attr($field_name) . '">';
                echo '<span class="cubewp-sorting-dropdown-item-text">' . esc_html($title) . '</span>';
                echo '</li>';
            }
        }

        echo '</ul>';
        echo '</div>';
    }
}

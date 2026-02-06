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

                // Taxonomy Display Type (only for taxonomies)
                $this->add_control(
                    'taxonomy_display_type_' . $post_type_key,
                    [
                        'label' => esc_html__('Taxonomy Display Type', 'cubewp-framework'),
                        'type' => Controls_Manager::SELECT,
                        'default' => 'checkbox',
                        'options' => [
                            'checkbox' => esc_html__('Checkbox', 'cubewp-framework'),
                            'radio' => esc_html__('Radio', 'cubewp-framework'),
                            'select' => esc_html__('Select', 'cubewp-framework'),
                            'select2' => esc_html__('Select2', 'cubewp-framework'),
                            'multi_select' => esc_html__('Multi Select', 'cubewp-framework'),
                        ],
                        'condition' => [
                            'field_type_selection' => 'taxonomies',
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

                // Business Hours Button Text
                $this->add_control(
                    'business_hours_button_text_' . $post_type_key,
                    [
                        'label' => esc_html__('Business Hours Label', 'cubewp-framework'),
                        'type' => Controls_Manager::TEXT,
                        'default' => esc_html__('Business Hours', 'cubewp-framework'),
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
                            'radio' => esc_html__('Radio', 'cubewp-framework'),
                            'select' => esc_html__('Select', 'cubewp-framework'),
                            'select2' => esc_html__('Select2', 'cubewp-framework'),
                            'multi_select' => esc_html__('Multi Select', 'cubewp-framework'),
                        ],
                        'condition' => [
                            'popup_field_type' => 'taxonomies',
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

        // Check if reviews plugin is active
        $reviews_active = defined('CUBEWP_REVIEWS') || class_exists('CubeWp_Reviews_Load');

        // Sorting Field Selection (like filter field selection - single field)
        $sorting_options = [
            'DESC' => esc_html__('Newest', 'cubewp-framework'),
            'ASC' => esc_html__('Oldest', 'cubewp-framework'),
            'title' => esc_html__('Title', 'cubewp-framework'),
            'rand' => esc_html__('Random', 'cubewp-framework'),
            'relevance' => esc_html__('Relevance', 'cubewp-framework'),
            'most_viewed' => esc_html__('Most Viewed', 'cubewp-framework'),
        ];

        // Add reviews options if plugin is active
        if ($reviews_active) {
            $sorting_options['high_rated'] = esc_html__('High Rated', 'cubewp-framework');
            $sorting_options['most_reviewed'] = esc_html__('Most Reviewed', 'cubewp-framework');
            $sorting_options['rating_1'] = esc_html__('1 Star', 'cubewp-framework');
            $sorting_options['rating_2'] = esc_html__('2 Stars', 'cubewp-framework');
            $sorting_options['rating_3'] = esc_html__('3 Stars', 'cubewp-framework');
            $sorting_options['rating_4'] = esc_html__('4 Stars', 'cubewp-framework');
            $sorting_options['rating_5'] = esc_html__('5 Stars', 'cubewp-framework');
        }

        // Get custom sorting fields from CubeWP
        $cwp_search_filters = CWP()->get_form('search_filters');
        if (!empty($cwp_search_filters) && is_array($cwp_search_filters)) {
            foreach ($cwp_search_filters as $post_type_key => $filter_data) {
                if (!empty($filter_data['fields']) && is_array($filter_data['fields'])) {
                    foreach ($filter_data['fields'] as $field_name => $field_data) {
                        if (isset($field_data['sorting']) && $field_data['sorting'] == 1) {
                            $field_label = isset($field_data['label']) ? $field_data['label'] : $field_name;
                            $sorting_options[$field_name . '-ASC'] = $field_label . ': ' . esc_html__('Low to High', 'cubewp-framework');
                            $sorting_options[$field_name . '-DESC'] = $field_label . ': ' . esc_html__('High to Low', 'cubewp-framework');
                        }
                    }
                }
            }
        }

        // Select Sorting Fields for Dropdown (multiple options)
        $this->add_control(
            'sorting_fields_dropdown',
            [
                'label' => esc_html__('Select Sorting Options (Dropdown)', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $sorting_options,
                'default' => ['DESC'],
                'description' => esc_html__('Select multiple sorting options to display in dropdown.', 'cubewp-framework'),
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Select Sorting Field for Buttons (single option)
        $this->add_control(
            'sorting_field',
            [
                'label' => esc_html__('Select Sorting Field (Button)', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => $sorting_options,
                'default' => 'DESC',
                'description' => esc_html__('Select a single sorting option to display as button.', 'cubewp-framework'),
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );

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

        // Button Display Type (for buttons - text or stars)
        $this->add_control(
            'sorting_button_display_type',
            [
                'label' => esc_html__('Button Display Type', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'text' => esc_html__('Text', 'cubewp-framework'),
                    'stars' => esc_html__('Stars (Icons)', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose how to display rating options in button (text or star icons).', 'cubewp-framework'),
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'buttons',
                ],
            ]
        );

        // Star Rating Display Options (for dropdown)
        $this->add_control(
            'rating_display_type',
            [
                'label' => esc_html__('Rating Display Type', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'text',
                'options' => [
                    'text' => esc_html__('Text', 'cubewp-framework'),
                    'stars' => esc_html__('Stars (Icons)', 'cubewp-framework'),
                ],
                'description' => esc_html__('Choose how to display rating options (text or star icons).', 'cubewp-framework'),
                'condition' => [
                    'filter_type' => 'sorting',
                    'sorting_display_type' => 'dropdown',
                ],
            ]
        );

        // Star Color (for both buttons and dropdown)
        $this->add_control(
            'rating_star_color',
            [
                'label' => esc_html__('Star Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#FFA500',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-rating-star.filled' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cubewp-rating-star.filled svg' => 'fill: {{VALUE}};',
                ],
                'condition' => [
                    'filter_type' => 'sorting',
                ],
            ]
        );

        // Enable Best Match
        $this->add_control(
            'enable_best_match',
            [
                'label' => esc_html__('Enable Best Match', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => esc_html__('Show "Best Match" option that automatically applies optimal sorting.', 'cubewp-framework'),
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
        $this->add_control(
            'custom_select_icon_right',
            [
                'label' => esc_html__('Icon Right Offset (px)', 'cubewp-framework'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
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
            'popup_footer_heading',
            [
                'label' => esc_html__('Footer', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'popup_footer_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-footer',
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
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_footer_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-footer',
            ]
        );

        // Apply Button
        $this->add_control(
            'popup_apply_button_heading',
            [
                'label' => esc_html__('Apply Button', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs('popup_apply_button_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'popup_apply_button_normal',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
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
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_apply_button_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply',
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
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'popup_apply_button_hover_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-filter-popup-apply:hover',
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

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'sorting_dropdown_toggle_background',
                'label' => esc_html__('Background', 'cubewp-framework'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cubewp-sorting-dropdown-toggle',
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
        $post_type = isset($settings['post_type']) ? $settings['post_type'] : 'post';
        $display_type = isset($settings['field_display_type']) ? $settings['field_display_type'] : 'simple';
        $field_type_selection = isset($settings['field_type_selection']) ? $settings['field_type_selection'] : 'custom_fields';

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
        $is_business_hours = false;
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

        // Add class to widget wrapper if custom styling is enabled


        // Generate unique ID for this widget instance
        $widget_id = 'cubewp-filter-builder-' . $this->get_id();

        // Start filter container with unique ID and data attributes
        // Using prefix for all classes
        $class_checkbox_enabled = '';
        if ($enable_custom_checkbox_radio) {
            $class_checkbox_enabled .= 'cubewp-custom-checkbox-radio-enabled';
        }

        // Prepare data attributes for custom icons
        $data_attrs = '';
        echo '<div class="cubewp-filter-builder-container ' . esc_attr($class_checkbox_enabled) . '" id="' . esc_attr($widget_id) . '" data-post-type="' . esc_attr($post_type) . '" data-widget-id="' . esc_attr($this->get_id()) . '"' . $data_attrs . '>';

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
                    $this->render_simple_taxonomy($taxonomy_name, $post_type, $taxonomy_display_type, $custom_taxonomy_label);
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
                } else {
                    // Get custom label for custom field
                    $field_label_key = 'field_label_' . $post_type;
                    $custom_field_label = isset($settings[$field_label_key]) ? $settings[$field_label_key] : '';
                    $this->render_simple_field($field_name, $post_type, $field_icon, $custom_field_label);
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
    private function render_simple_field($field_name, $post_type, $field_icon = [], $custom_label = '')
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

            $field_output = CubeWp_Frontend_Search_Filter::get_filters_content($search_filter, $field_name);
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);

            // Replace label in output if custom label is provided
            if (!empty($custom_label) && !empty($original_label)) {
                // Escape both labels for HTML comparison
                $original_escaped = esc_html($original_label);
                $custom_escaped = esc_html($custom_label);

                // Strategy 1: Replace entire label tag content (most common case)
                $field_output = preg_replace(
                    '/(<label[^>]*>)([^<]*)' . preg_quote($original_escaped, '/') . '([^<]*)(<\/label>)/i',
                    '$1$2' . $custom_escaped . '$3$4',
                    $field_output
                );

                // Strategy 2: Replace simple label tag
                $field_output = preg_replace(
                    '/<label[^>]*>' . preg_quote($original_escaped, '/') . '<\/label>/i',
                    '<label>' . $custom_escaped . '</label>',
                    $field_output
                );

                // Strategy 3: Replace text content between tags (for any tag, not just label)
                $field_output = preg_replace(
                    '/(>)([^<]*)' . preg_quote($original_escaped, '/') . '([^<]*)(<)/i',
                    '$1$2' . $custom_escaped . '$3$4',
                    $field_output
                );

                // Strategy 4: Direct string replacement (fallback)
                $field_output = str_replace($original_escaped, $custom_escaped, $field_output);

                // Strategy 5: Also try with unescaped version (in case label wasn't escaped)
                if ($original_label !== $original_escaped) {
                    $field_output = str_replace($original_label, $custom_label, $field_output);
                }
            }

            echo $field_output;
        } else {
            // Try to render field directly
            $this->render_field_directly($field_name, $post_type);
        }

        echo '</div>';
        echo '</div>';
    }



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
        $unique_id = 'cubewp-filter-popup-' . $widget_id;
        $popup_id = 'cubewp-filter-popup-content-' . $widget_id;

        echo '<div class="cubewp-filter-popup-wrapper">';
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

                echo '<div class="cubewp-popup-field-wrapper">';


                if ($popup_field_type === 'taxonomies') {
                    $popup_taxonomy_name = isset($popup_field['popup_taxonomy_name']) ? $popup_field['popup_taxonomy_name'] : '';
                    $popup_taxonomy_display = isset($popup_field['popup_taxonomy_display']) ? $popup_field['popup_taxonomy_display'] : 'checkbox';
                    $popup_taxonomy_label = isset($popup_field['popup_taxonomy_label']) ? $popup_field['popup_taxonomy_label'] : '';
                    if (!empty($popup_taxonomy_name)) {
                        $this->render_simple_taxonomy($popup_taxonomy_name, $post_type, $popup_taxonomy_display, $popup_taxonomy_label);
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
                            $popup_field_label = isset($popup_field['popup_field_label']) ? $popup_field['popup_field_label'] : '';
                            $popup_field_icon = isset($popup_field['popup_field_icon']) ? $popup_field['popup_field_icon'] : [];
                            $this->render_simple_field($popup_field_name, $post_type, $popup_field_icon, $popup_field_label);
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
    private function render_simple_taxonomy($taxonomy_name, $post_type, $display_type = 'checkbox', $custom_label = '')
    {
        echo '<div class="cubewp-filter-builder-field" data-field-name="' . esc_attr($taxonomy_name) . '">';

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
                'type' => 'taxonomy',
                'display_ui' => $cubewp_display_type,
                'appearance' => $cubewp_display_type,
                'select2_ui' => $cubewp_display_type === 'select' ? true : false,
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
     * Render button taxonomy field
     */
    private function render_button_taxonomy($taxonomy_name, $post_type, $display_type, $button_text, $index)
    {
        $unique_id = 'cubewp-filter-button-' . $index;
        $field_container_id = 'cubewp-filter-field-' . $index;

        echo '<div class="cubewp-filter-button-wrapper">';
        echo '<button type="button" class="cubewp-filter-button cubewp-filter-builder-btn" id="' . esc_attr($unique_id) . '" data-target="' . esc_attr($field_container_id) . '">';
        echo esc_html($button_text);
        echo '</button>';
        echo '<div class="cubewp-filter-field-container" id="' . esc_attr($field_container_id) . '" style="display: none;">';
        $this->render_simple_taxonomy($taxonomy_name, $post_type, $display_type, '');
        echo '</div>';
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
        $icon_position = isset($array_settings['icon_position']) ? $array_settings['icon_position'] : 'left';
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
        // Hidden input to store the selected filter value - make it unique per widget instance
        // Use widget ID in the name to ensure uniqueness when multiple widgets use the same field
        $unique_input_name = $field_name . '_status';
        echo '<input type="hidden" name="' . esc_attr($unique_input_name) . '" class="cubewp-business-hours-status" data-widget-id="' . esc_attr($index) . '" data-field-name="' . esc_attr($field_name) . '" value="">';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render field directly (fallback when not in form builder)
     */
    private function render_field_directly($field_name, $post_type)
    {
        // Check if it's a taxonomy
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        if (isset($taxonomies[$field_name])) {
            $taxonomy = $taxonomies[$field_name];
            $search_filter = [
                'label' => $taxonomy->label,
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
            echo '<div class="cubewp-filter-builder-field-container cwp-field-container">';
            echo '<label class="cubewp-filter-builder-label">' . esc_html__('Keyword', 'cubewp-framework') . '</label>';
            $keyword_value = isset($_GET['keyword']) ? sanitize_text_field(wp_unslash($_GET['keyword'])) : '';
            echo '<input type="text" name="s" class="cubewp-filter-builder-input" value="' . esc_attr($keyword_value) . '" placeholder="' . esc_attr__('Search...', 'cubewp-framework') . '" />';
            echo '</div>';
            return;
        }

        // Try to get custom field
        $field_options = get_field_options($field_name);
        if (!empty($field_options)) {
            $field_type = isset($field_options['type']) ? $field_options['type'] : '';
            $fieldOptions = array_merge($field_options, [
                'label' => isset($field_options['label']) ? $field_options['label'] : $field_name,
                'name' => $field_name,
                'class' => 'cubewp-filter-builder-input',
                'container_class' => 'cubewp-filter-builder-field-container',
                'placeholder' => '',
            ]);

            if (isset($_GET[$field_name]) && !empty($_GET[$field_name])) {
                $fieldOptions['value'] = sanitize_text_field(wp_unslash($_GET[$field_name]));
            }

            $field_output = apply_filters("cubewp/search_filters/{$field_type}/field", '', $fieldOptions);
            // Ensure prefix classes are added
            $field_output = str_replace('cwp-field-container', 'cubewp-filter-builder-field-container cwp-field-container', $field_output);
            $field_output = str_replace('cwp-search-field', 'cubewp-filter-builder-search-field cwp-search-field', $field_output);
            echo $field_output;
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

        // Add custom CSS and UI JS for filter builder
        $filter_css = $this->get_filter_builder_css();
        if (!empty($filter_css)) {
            wp_add_inline_style('frontend-fields', $filter_css);
        }
        $filter_js = $this->get_filter_builder_js();
        if (!empty($filter_js)) {
            wp_add_inline_script('cwp-filter-builder', $filter_js);
        }
    }

    /**
     * Get custom CSS for filter builder
     */
    private function get_filter_builder_css()
    {
        $settings = $this->get_settings_for_display();


        $css = '
        .cubewp-filter-builder-container {
            position: relative;
        }
        
        /* Custom Icon Support for Checkbox/Radio */
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after,
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after i,
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-checkbox-container .cwp-field-checkbox input[type="checkbox"]:checked + label::after svg,
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after i,
        .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled .cwp-field-radio-container .cwp-field-radio input[type="radio"]:checked + label::after svg {
            display: inline-block;
            line-height: 1;
        }
        .cubewp-filter-button-wrapper {
            margin-bottom: 15px;
        }
        .cubewp-filter-button {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 10px 20px;
            background: #fff;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .cubewp-filter-button:hover {
            background: #f5f5f5;
        }
        .cubewp-filter-field-container {
            margin-top: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
        }
      
       
        .cubewp-filter-popup-wrapper {
            position: relative;
        }
        .cubewp-filter-popup-button {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 10px 20px;
            background: #fff;
            border-radius: 4px;
        }
        .cubewp-filter-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            z-index: 9998;
            display: none;
            overflow: hidden;
        }
        .cubewp-filter-popup-overlay.active {
            display: block;
        }
        .cubewp-filter-popup-content {
            position: fixed;
            z-index: 9999;
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        .cubewp-filter-popup-content.active {
            display: flex;
        }
        /* Center Position (Default) */
        .cubewp-filter-popup-content.cubewp-popup-position-center {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        /* Top Position */
        
        .cubewp-filter-popup-header {
            padding: 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cubewp-filter-popup-header h3 {
            margin: 0;
        }
        .cubewp-filter-popup-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            line-height: 30px;
        }
        .cubewp-filter-popup-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        .cubewp-filter-popup-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .cubewp-filter-popup-apply {
            padding: 10px 20px;
            background: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cubewp-filter-popup-apply:hover {
            background: #005a87;
        }
        .cubewp-business-hours-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 0;
        }
        .cubewp-business-hours-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .cubewp-business-hours-btn:hover {
            background: #f5f5f5;
            border-color: #0073aa;
        }
        .cubewp-business-hours-btn.active {
            background: #0073aa;
            color: #fff;
            border-color: #0073aa;
        }
        
        /* Sorting Dropdown Styles */
        .cubewp-sorting-dropdown {
            position: relative;
            display: inline-block;
        }
        .cubewp-sorting-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 200px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            list-style: none;
            padding: 5px 0;
            margin: 5px 0 0 0;
            z-index: 1000;
            display: none !important;
            max-height: 300px;
            overflow-y: auto;
        }
        .cubewp-sorting-dropdown-menu.open {
            display: block !important;
        }
        .cubewp-sorting-dropdown-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .cubewp-sorting-dropdown-item:hover {
            background: #f5f5f5;
        }
        .cubewp-sorting-dropdown-item.selected {
            background: #e8f4f8;
        }
        .cubewp-rating-stars {
            display: inline-flex;
            gap: 2px;
            align-items: center;
        }
        .cubewp-rating-star {
            color: #ddd;
            display: inline-flex;
            align-items: center;
        }
        .cubewp-rating-star.filled {
            color: #FFA500;
        }
        .cubewp-rating-star svg {
            width: 16px;
            height: 16px;
        }
        .cubewp-business-hours-btn .cubewp-button-content-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .cubewp-business-hours-btn.cubewp-icon-left .cubewp-button-content-wrapper {
            flex-direction: row;
        }
        .cubewp-business-hours-btn.cubewp-icon-right .cubewp-button-content-wrapper {
            flex-direction: row-reverse;
        }
        .cubewp-business-hours-btn .cubewp-button-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .cubewp-business-hours-btn .cubewp-button-text {
            display: inline-block;
        }
        ';
        
        return $css;
    }

    /**
     * Get custom JavaScript for filter builder UI interactions
     * The main AJAX functionality is in filter-builder.js module
     */
    private function get_filter_builder_js()
    {
        return '
        (function($) {
            // Use a flag to ensure handlers are only attached once
            if (typeof window.cubewpFilterBuilderInitialized === "undefined") {
                window.cubewpFilterBuilderInitialized = true;
                
                $(document).ready(function() {
                    // Remove any existing handlers to prevent duplicates
                    $(document).off("click.cubewpFilterBuilder", ".cubewp-filter-button.cubewp-filter-builder-btn");
                    
                    // Button toggle functionality - use namespaced event to prevent duplicates
                    $(document).on("click.cubewpFilterBuilder", ".cubewp-filter-button.cubewp-filter-builder-btn", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var $button = $(this);
                        var target = $button.data("target");
                        var $target = $("#" + target);
                        
                        // Prevent multiple toggles if already animating
                        if ($target.is(":animated")) {
                            return false;
                        }
                        
                        $target.slideToggle();
                    });
 

                // Popup open functionality
                $(document).on("click", ".cubewp-filter-popup-button", function() {
                    var target = $(this).data("target");
                    $("#" + target).addClass("active");
                    $("#" + target + "-overlay").addClass("active");
                    $("body").css("overflow", "hidden");
                });

                // Popup close functionality
                $(document).on("click", ".cubewp-filter-popup-close", function() {
                    var target = $(this).data("target");
                    $("#" + target).removeClass("active");
                    $("#" + target + "-overlay").removeClass("active");
                    $("body").css("overflow", "");
                });

                // Close popup when clicking overlay
                $(document).on("click", ".cubewp-filter-popup-overlay", function() {
                    $(this).removeClass("active");
                    $(".cubewp-filter-popup-content").removeClass("active");
                    $("body").css("overflow", "");
                });

                // Apply filters in popup
                $(document).on("click", ".cubewp-filter-popup-apply", function() {
                    var popup = $(this).closest(".cubewp-filter-popup-content");
                    var popupId = popup.attr("id");
                    popup.removeClass("active");
                    $("#" + popupId + "-overlay").removeClass("active");
                    $("body").css("overflow", "");
                    // Trigger filter builder data collection
                    if (typeof cubewpCollectFilterBuilderData === "function") {
                        cubewpCollectFilterBuilderData();
                    }
                    // Trigger AJAX if function exists
                    if (typeof cwp_search_filters_ajax_content === "function") {
                        cwp_search_filters_ajax_content();
                    }
                });

                // Handle radio mode for taxonomy fields (convert checkbox to radio behavior)
                $(document).on("change", ".cubewp-radio-mode input[type=\"checkbox\"][data-radio-mode=\"1\"]", function() {
                    var $checkbox = $(this);
                    var $container = $checkbox.closest(".cubewp-radio-mode");
                    // Uncheck all other checkboxes in the same container
                    $container.find("input[type=\"checkbox\"][data-radio-mode=\"1\"]").not($checkbox).prop("checked", false);
                    // Update hidden field with single value
                    var $hiddenField = $container.find("input[type=\"hidden\"]");
                    if ($hiddenField.length > 0) {
                        if ($checkbox.is(":checked")) {
                            $hiddenField.val($checkbox.val());
                        } else {
                            $hiddenField.val("");
                        }
                        // Trigger data collection
                        if (typeof cubewpCollectFilterBuilderData === "function") {
                            cubewpCollectFilterBuilderData();
                        }
                        // Trigger AJAX
                        if (typeof cwp_search_filters_ajax_content === "function") {
                            cwp_search_filters_ajax_content();
                        }
                    }
                });

                // Handle business hours filter buttons
                $(document).on("click", ".cubewp-business-hours-btn", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var $button = $(this);
                    // Ensure we get the correct container for this specific widget instance
                    var $container = $button.closest(".cubewp-business-hours-filter");
                    
                    // Double-check container exists
                    if (!$container.length) {
                        console.warn("CubeWP Business Hours: Container not found for button");
                        return;
                    }
                    
                    var filterType = $button.data("filter-type");
                    var fieldName = $button.data("field-name");
                    var $statusInput = $container.find(".cubewp-business-hours-status").first();
                    
                    // Ensure status input exists
                    if (!$statusInput.length) {
                        console.warn("CubeWP Business Hours: Status input not found");
                        return;
                    }
                    
                    // Get the actual field name from the status inputs data attribute (for form submission)
                    var actualFieldName = $statusInput.data("field-name") || fieldName;
                    var formFieldName = actualFieldName + "_status"; // Standard format for form submission
                    
                    var isCurrentlyActive = $button.hasClass("active");
                    var currentStatus = $statusInput.val();
                    
                    // If button is already active or status matches this filter type, deselect it
                    if (isCurrentlyActive || currentStatus === filterType) {
                        // Uncheck: Remove status and active class
                        $statusInput.val("");
                        $button.removeClass("active");
                        // Also remove active class from all other buttons in THIS container only
                        $container.find(".cubewp-business-hours-btn").removeClass("active");
                    } else {
                        // Check: Set status and add active class
                        // IMPORTANT: Clear values from all other widgets with the same field name
                        // This ensures only the clicked widget sends its value
                        $(".cubewp-business-hours-filter").each(function() {
                            var $otherContainer = $(this);
                            var $otherStatusInput = $otherContainer.find(".cubewp-business-hours-status").first();
                            var otherFieldName = $otherStatusInput.data("field-name");
                            
                            // If its a different widget but same field name, clear its value
                            if (otherFieldName === actualFieldName && $otherContainer[0] !== $container[0]) {
                                $otherStatusInput.val("");
                                $otherContainer.find(".cubewp-business-hours-btn").removeClass("active");
                            }
                        });
                        
                        // First remove active from all buttons in THIS container only
                        $container.find(".cubewp-business-hours-btn").removeClass("active");
                        // Then set the new status and activate this button
                        $statusInput.val(filterType);
                        $button.addClass("active");
                    }
                    
                    // Explicitly remove the parameter from the form and any collected data
                    // Use the standard field name format (field_name_status) for form operations
                    if (formFieldName && !$statusInput.val()) {
                        // Remove from any form that might contain it using the standard name format
                        $(".cwp-search-filters").find("input[name=\"" + formFieldName + "\"]").remove();
                        // Also remove from any dynamically added fields container
                        $(".cubewp-filter-builder-fields-container").find("input[name=\"" + formFieldName + "\"]").remove();
                    }
                    
                    // Trigger data collection first (this will handle removing empty parameters)
                    if (typeof cubewpCollectFilterBuilderData === "function") {
                        cubewpCollectFilterBuilderData();
                    }
                    
                    // Small delay to ensure data collection completes before AJAX
                    setTimeout(function() {
                        // Double-check: Remove parameter if status is still empty
                        if (!$statusInput.val() && formFieldName) {
                            $(".cwp-search-filters").find("input[name=\"" + formFieldName + "\"]").remove();
                            $(".cubewp-filter-builder-fields-container").find("input[name=\"" + formFieldName + "\"]").remove();
                        }
                        
                        // Trigger AJAX after ensuring parameter is removed
                        if (typeof cwp_search_filters_ajax_content === "function") {
                            cwp_search_filters_ajax_content();
                        }
                    }, 50);
                });
                
                // Restore button states on page load based on status input values
                // Use a function to restore state for each widget instance
                function restoreBusinessHoursState() {
                    $(".cubewp-business-hours-filter").each(function() {
                        var $container = $(this);
                        var $statusInput = $container.find(".cubewp-business-hours-status").first();
                        
                        if (!$statusInput.length) {
                            return; // Skip if no status input found
                        }
                        
                        var currentStatus = $statusInput.val();
                        
                        // Remove active class from all buttons in THIS container first
                        $container.find(".cubewp-business-hours-btn").removeClass("active");
                        
                        // If theres a status value, activate the corresponding button
                        if (currentStatus && currentStatus !== "") {
                            var $activeButton = $container.find(".cubewp-business-hours-btn[data-filter-type=\"" + currentStatus + "\"]");
                            if ($activeButton.length) {
                                $activeButton.addClass("active");
                            }
                        } else {
                            // No status means no button should be active
                            $statusInput.val("");
                        }
                    });
                }
                
                // Restore on page load
                restoreBusinessHoursState();
                
                // Also restore when widget is added/updated (for Elementor)
                $(document).on("cubewp_filter_builder_widget_added", function() {
                    setTimeout(restoreBusinessHoursState, 100);
                });

                // Custom Icon Rendering for Checkbox/Radio
                function renderCustomIcons() {
                    $(".cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled").each(function() {
                        var $container = $(this);
                        var checkboxIconData = $container.data("checkbox-icon");
                        var radioIconData = $container.data("radio-icon");
                        
                        // Render checkbox custom icons
                        if (checkboxIconData && typeof checkboxIconData === "object") {
                            $container.find(".cwp-field-checkbox-container .cwp-field-checkbox input[type=\"checkbox\"]:checked + label").each(function() {
                                var $label = $(this);
                                
                                // Remove existing ::after content by updating CSS
                                // Note: Well use CSS to handle the icon display
                            });
                        }
                        
                        // Render radio custom icons
                        if (radioIconData && typeof radioIconData === "object") {
                            $container.find(".cwp-field-radio-container .cwp-field-radio input[type=\"radio\"]:checked + label").each(function() {
                                var $label = $(this);
                                
                                // Remove existing ::after content by updating CSS
                                // Note: Well use CSS to handle the icon display
                            });
                        }
                    });
                }
                
                // Render custom icons on page load
                renderCustomIcons();
                
                // Re-render custom icons when checkboxes/radios change
                $(document).on("change", ".cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled input[type=\"checkbox\"], .cubewp-filter-builder-container.cubewp-custom-checkbox-radio-enabled input[type=\"radio\"]", function() {
                    setTimeout(renderCustomIcons, 50);
                });

                // Trigger event when widget is added
                $(document).trigger("cubewp_filter_builder_widget_added");
                }); // End of $(document).ready
            } // End of if (typeof window.cubewpFilterBuilderInitialized === "undefined")
        })(jQuery);
        ';
    }

    /**
     * Render sorting options
     */
    private function render_sorting($settings, $post_type)
    {
        $sorting_display_type = isset($settings['sorting_display_type']) ? $settings['sorting_display_type'] : 'buttons';
        $sorting_field = isset($settings['sorting_field']) ? $settings['sorting_field'] : 'DESC';
        $sorting_fields_dropdown = isset($settings['sorting_fields_dropdown']) ? $settings['sorting_fields_dropdown'] : ['DESC'];
        $enable_best_match = isset($settings['enable_best_match']) ? $settings['enable_best_match'] : 'no';
        $sorting_button_icon = isset($settings['sorting_button_icon']) ? $settings['sorting_button_icon'] : [];
        $sorting_dropdown_toggle_icon = isset($settings['sorting_dropdown_toggle_icon']) ? $settings['sorting_dropdown_toggle_icon'] : [];
        $sorting_button_text = isset($settings['sorting_button_text']) ? $settings['sorting_button_text'] : '';
        $sorting_button_display_type = isset($settings['sorting_button_display_type']) ? $settings['sorting_button_display_type'] : 'text';
        $rating_display_type = isset($settings['rating_display_type']) ? $settings['rating_display_type'] : 'text';
        $rating_star_color = isset($settings['rating_star_color']) ? $settings['rating_star_color'] : '#FFA500';

        // Get all available sorting options for labels
        $reviews_active = defined('CUBEWP_REVIEWS') || class_exists('CubeWp_Reviews_Load');
        $all_options = [
            'DESC' => esc_html__('Newest', 'cubewp-framework'),
            'ASC' => esc_html__('Oldest', 'cubewp-framework'),
            'title' => esc_html__('Title', 'cubewp-framework'),
            'rand' => esc_html__('Random', 'cubewp-framework'),
            'relevance' => esc_html__('Relevance', 'cubewp-framework'),
            'most_viewed' => esc_html__('Most Viewed', 'cubewp-framework'),
        ];

        if ($reviews_active) {
            $all_options['high_rated'] = esc_html__('High Rated', 'cubewp-framework');
            $all_options['most_reviewed'] = esc_html__('Most Reviewed', 'cubewp-framework');
            $all_options['rating_1'] = esc_html__('1 Star', 'cubewp-framework');
            $all_options['rating_2'] = esc_html__('2 Stars', 'cubewp-framework');
            $all_options['rating_3'] = esc_html__('3 Stars', 'cubewp-framework');
            $all_options['rating_4'] = esc_html__('4 Stars', 'cubewp-framework');
            $all_options['rating_5'] = esc_html__('5 Stars', 'cubewp-framework');
        }

        // Get custom sorting fields
        $cwp_search_filters = CWP()->get_form('search_filters');
        if (!empty($cwp_search_filters) && is_array($cwp_search_filters)) {
            foreach ($cwp_search_filters as $post_type_key => $filter_data) {
                if (!empty($filter_data['fields']) && is_array($filter_data['fields'])) {
                    foreach ($filter_data['fields'] as $field_name => $field_data) {
                        if (isset($field_data['sorting']) && $field_data['sorting'] == 1) {
                            $field_label = isset($field_data['label']) ? $field_data['label'] : $field_name;
                            $all_options[$field_name . '-ASC'] = $field_label . ': ' . esc_html__('Low to High', 'cubewp-framework');
                            $all_options[$field_name . '-DESC'] = $field_label . ': ' . esc_html__('High to Low', 'cubewp-framework');
                        }
                    }
                }
            }
        }

        $all_options['business_hours'] = esc_html__('Open Now (Business Hours)', 'cubewp-framework');

        // Get current orderby value from URL
        $current_orderby = isset($_GET['orderby']) && !empty($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : '';

        $widget_id = 'cubewp-sorting-' . $this->get_id();

        // Add star color CSS if using stars
        if (($sorting_display_type === 'dropdown' && $rating_display_type === 'stars') ||
            ($sorting_display_type === 'buttons' && $sorting_button_display_type === 'stars')
        ) {
            echo '<style>
                #' . esc_attr($widget_id) . ' .cubewp-rating-star.filled {
                    color: ' . esc_attr($rating_star_color) . ';
                }
                #' . esc_attr($widget_id) . ' .cubewp-rating-star.filled svg {
                    fill: ' . esc_attr($rating_star_color) . ';
                }
            </style>';
        }

        echo '<div class="cubewp-sorting-container" id="' . esc_attr($widget_id) . '" data-post-type="' . esc_attr($post_type) . '" data-rating-display-type="' . esc_attr($rating_display_type) . '" data-button-display-type="' . esc_attr($sorting_button_display_type) . '">';

        if ($sorting_display_type === 'buttons') {
            // Single field for buttons
            $this->render_sorting_buttons_single($sorting_field, $sorting_button_text, $all_options, $current_orderby, $enable_best_match, $sorting_button_icon, $post_type, $sorting_button_display_type);
        } else {
            // Multiple fields for dropdown
            $this->render_sorting_dropdown_multiple($sorting_fields_dropdown, $all_options, $current_orderby, $enable_best_match, $rating_display_type, $post_type, $sorting_dropdown_toggle_icon);
        }

        echo '</div>';
    }

    /**
     * Render sorting as single button
     */
    private function render_sorting_buttons_single($sorting_field, $button_text, $all_options, $current_orderby, $enable_best_match, $button_icon, $post_type, $display_type = 'text')
    {
        // Process field to get actual field name
        $actual_sorting_field = $this->process_sorting_field($sorting_field, $post_type);

        // Check if this is a rating field
        $is_rating = (strpos($sorting_field, 'rating_') === 0);

        // Get label - use custom button text if provided, otherwise use option label
        $field_label = !empty($button_text) ? $button_text : (isset($all_options[$sorting_field]) ? $all_options[$sorting_field] : $sorting_field);

        echo '<div class="cubewp-sorting-buttons">';

        // Best Match button if enabled
        if ($enable_best_match === 'yes') {
            $best_match_active = ($current_orderby === 'best_match' || $current_orderby === 'relevance') ? 'active' : '';
            echo '<button type="button" class="cubewp-sorting-btn cubewp-best-match-btn ' . esc_attr($best_match_active) . '" data-orderby="best_match">';
            if (!empty($button_icon['value'])) {
                echo '<span class="cubewp-sorting-btn-icon">';
                \Elementor\Icons_Manager::render_icon($button_icon, ['aria-hidden' => 'true']);
                echo '</span>';
            }
            echo '<span class="cubewp-sorting-btn-text">' . esc_html__('Best Match', 'cubewp-framework') . '</span>';
            echo '</button>';
        }

        // Single sorting button for selected field
        $active = ($current_orderby === $actual_sorting_field || ($current_orderby === '' && $actual_sorting_field === 'DESC'));

        $active_class = $active ? 'active' : '';
        echo '<button type="button" class="cubewp-sorting-btn ' . esc_attr($active_class) . '" data-orderby="' . esc_attr($actual_sorting_field) . '" data-display-field="' . esc_attr($sorting_field) . '">';
        if (!empty($button_icon['value'])) {
            echo '<span class="cubewp-sorting-btn-icon">';
            \Elementor\Icons_Manager::render_icon($button_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }

        // Display as stars or text
        if ($is_rating && $display_type === 'stars') {
            $star_count = (int) str_replace('rating_', '', $sorting_field);
            echo '<span class="cubewp-rating-stars">';
            for ($i = 1; $i <= 5; $i++) {
                $filled_class = ($i <= $star_count) ? 'filled' : '';
                echo '<span class="cubewp-rating-star ' . esc_attr($filled_class) . '">';
                echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                echo '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>';
                echo '</svg>';
                echo '</span>';
            }
            echo '</span>';
        } else {
            echo '<span class="cubewp-sorting-btn-text">' . esc_html($field_label) . '</span>';
        }
        echo '</button>';

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
    private function render_sorting_dropdown_multiple($sorting_fields, $all_options, $current_orderby, $enable_best_match, $rating_display_type, $post_type, $toggle_icon = [])
    {
        if (!is_array($sorting_fields) || empty($sorting_fields)) {
            $sorting_fields = ['DESC'];
        }

        $dropdown_id = 'cubewp-sorting-dropdown-' . $this->get_id();

        // Get current label
        $current_label = esc_html__('Sort By', 'cubewp-framework');
        foreach ($sorting_fields as $field) {
            $actual_field = $this->process_sorting_field($field, $post_type);

            if ($current_orderby === $actual_field || $current_orderby === $field) {
                if (isset($all_options[$field])) {
                    $current_label = $all_options[$field];
                    break;
                }
            }
        }

        if ($current_orderby === 'best_match' || $current_orderby === 'relevance') {
            $current_label = esc_html__('Best Match', 'cubewp-framework');
        }

        echo '<div class="cubewp-sorting-dropdown" id="' . esc_attr($dropdown_id) . '">';
        echo '<button type="button" class="cubewp-sorting-dropdown-toggle">';
        echo '<span class="cubewp-sorting-dropdown-text">' . esc_html($current_label) . '</span>';
        if (!empty($toggle_icon['value'])) {
            echo '<span class="cubewp-sorting-dropdown-icon">';
            \Elementor\Icons_Manager::render_icon($toggle_icon, ['aria-hidden' => 'true']);
            echo '</span>';
        }
        echo '</button>';

        echo '<ul class="cubewp-sorting-dropdown-menu">';

        // Best Match option if enabled
        if ($enable_best_match === 'yes') {
            $best_match_selected = ($current_orderby === 'best_match' || $current_orderby === 'relevance') ? 'selected' : '';
            echo '<li class="cubewp-sorting-dropdown-item cubewp-best-match-option ' . esc_attr($best_match_selected) . '" data-orderby="best_match">';
            echo '<span class="cubewp-sorting-dropdown-item-text">' . esc_html__('Best Match', 'cubewp-framework') . '</span>';
            echo '</li>';
        }

        // Multiple options
        foreach ($sorting_fields as $field) {
            if (!isset($all_options[$field])) {
                continue;
            }

            $actual_field = $this->process_sorting_field($field, $post_type);

            $selected = ($current_orderby === $actual_field || $current_orderby === $field);

            $selected_class = $selected ? 'selected' : '';
            $label = $all_options[$field];

            // Check if this is a rating field and should display as stars
            $is_rating = (strpos($field, 'rating_') === 0);

            echo '<li class="cubewp-sorting-dropdown-item ' . esc_attr($selected_class) . '" data-orderby="' . esc_attr($actual_field) . '" data-display-field="' . esc_attr($field) . '">';

            if ($is_rating && $rating_display_type === 'stars') {
                // Display as stars
                $star_count = (int) str_replace('rating_', '', $field);
                echo '<span class="cubewp-rating-stars">';
                for ($i = 1; $i <= 5; $i++) {
                    $filled_class = ($i <= $star_count) ? 'filled' : '';
                    echo '<span class="cubewp-rating-star ' . esc_attr($filled_class) . '">';
                    echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                    echo '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>';
                    echo '</svg>';
                    echo '</span>';
                }
                echo '</span>';
            } else {
                // Display as text
                echo '<span class="cubewp-sorting-dropdown-item-text">' . esc_html($label) . '</span>';
            }

            echo '</li>';
        }

        echo '</ul>';
        echo '</div>';
    }
}
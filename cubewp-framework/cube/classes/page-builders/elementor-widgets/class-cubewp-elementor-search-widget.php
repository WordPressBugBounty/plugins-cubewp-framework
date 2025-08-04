<?php

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}

class CubeWp_Elementor_Search_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'cubewp_search';
    }

    public function get_title()
    {
        return esc_html__('Search Form', 'cubewp-framework');
    }

    public function get_icon()
    {
        return 'eicon-search';
    }

    public function get_categories()
    {
        return ['cubewp'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Search Settings', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        if (CWP()->is_request('frontend') || cubewp_is_elementor_editing()) {
            $post_types = CWP_all_post_types();
            $this->add_control(
                'post_type',
                [
                    'label' => esc_html__('Post Type', 'cubewp-framework'),
                    'type' => Controls_Manager::SELECT2,
                    'multiple' => true,
                    'options' => $post_types,
                    'default' => ['post'],
                    'description' => esc_html__('Select one or more post types.', 'cubewp-framework'),
                ]
            );
        }


        $this->end_controls_section();

        // Tabber Controls Section
        $this->start_controls_section(
            'tabber_style_section',
            [
                'label' => esc_html__('Tabber Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Tabber Button Position
        $this->add_control(
            'tabber_button_position',
            [
                'label' => esc_html__('Button Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'cubewp-framework'),
                    'center' => esc_html__('Center', 'cubewp-framework'),
                    'right' => esc_html__('Right', 'cubewp-framework'),
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        // Tabber Button Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tabber_button_typography',
                'label' => esc_html__('Button Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn',
            ]
        );

        // Tabber Button Text Color
        $this->add_control(
            'tabber_button_text_color',
            [
                'label' => esc_html__('Button Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'color: {{VALUE}};',
                ],
                'default' => '#333',
            ]
        );

        // Tabber Button Background Color
        $this->add_control(
            'tabber_button_bg_color',
            [
                'label' => esc_html__('Button Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'background-color: {{VALUE}};',
                ],
                'default' => '#f5f5f5',
            ]
        );

        // Tabber Button Border
        // Add heading for advanced border controls
        $this->add_control(
            'tabber_button_advanced_border_heading',
            [
                'label' => esc_html__('Advanced Button Borders', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // General Button Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabber_button_border',
                'label' => esc_html__('Button Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn',
            ]
        );
        // Advanced Border Switcher
        $this->add_control(
            'tabber_button_advanced_border',
            [
                'label' => esc_html__('Enable Advanced Border', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        // Border Radius for Last Button
        $this->add_control(
            'tabber_button_last_border_radius',
            [
                'label' => esc_html__('Last Button Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons li:last-child .tabber-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'tabber_button_advanced_border' => 'yes',
                ],
            ]
        );

        // Border for Last Button
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabber_button_last_border',
                'label' => esc_html__('Last Button Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons  li:last-child .tabber-btn',
                'condition' => [
                    'tabber_button_advanced_border' => 'yes',
                ],
            ]
        );

        // Border Radius for First Button
        $this->add_control(
            'tabber_button_first_border_radius',
            [
                'label' => esc_html__('First Button Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons li:first-child .tabber-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'tabber_button_advanced_border' => 'yes',
                ],
            ]
        );

        // Border for First Button
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabber_button_first_border',
                'label' => esc_html__('First Button Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons li:first-child .tabber-btn',
                'condition' => [
                    'tabber_button_advanced_border' => 'yes',
                ],
            ]
        );

        // Tabber Button Border Radius
        $this->add_control(
            'tabber_button_border_radius',
            [
                'label' => esc_html__('Button Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                ],
            ]
        );

        // Tabber Button Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabber_button_box_shadow',
                'label' => esc_html__('Button Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn',
            ]
        );

        // Tabber Button Padding
        $this->add_responsive_control(
            'tabber_button_padding',
            [
                'label' => esc_html__('Button Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 8,
                    'right' => 20,
                    'bottom' => 8,
                    'left' => 20,
                    'unit' => 'px',
                ],
            ]
        );

        // Tabber Button Margin
        $this->add_responsive_control(
            'tabber_button_margin',
            [
                'label' => esc_html__('Button Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 10,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );


        // Add icon selection for each selected post type
        $post_types = CWP_all_post_types();
        if (!empty($post_types)) {
            foreach ($post_types as $post_type_key => $post_type_label) {
                $this->add_control(
                    'tabber_button_icon_' . $post_type_key,
                    [
                        'label' => sprintf(esc_html__('%s Tab Icon', 'cubewp-framework'), $post_type_label),
                        'type' => Controls_Manager::ICONS,
                        'fa4compatibility' => 'icon',
                        'condition' => [
                            'post_type' => $post_type_key,
                        ],
                    ]
                );
            }
        }

        // Tabber Button Icon Size
        $this->add_responsive_control(
            'tabber_button_icon_size',
            [
                'label' => esc_html__('Button Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
                ],
            ]
        );
        // Tabber Button Icon Color
        $this->add_control(
            'tabber_button_icon_color',
            [
                'label' => esc_html__('Button Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn i, {{WRAPPER}} .cubewp-tabber-buttons .tabber-btn svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );

        // Tabber Button Icon Direction (flex-direction)
        $this->add_control(
            'tabber_button_icon_direction',
            [
                'label' => esc_html__('Button Icon Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'row' => esc_html__('Row', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
                    'column' => esc_html__('Column', 'cubewp-framework'),
                    'column-reverse' => esc_html__('Column Reverse', 'cubewp-framework'),
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'display: flex; align-items: center; justify-content: center; flex-direction: {{VALUE}};',
                ],
            ]
        );

        // Tabber Button Icon Gap
        $this->add_responsive_control(
            'tabber_button_icon_gap',
            [
                'label' => esc_html__('Button Icon Gap', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 8,
                    'unit' => 'px',
                ],
            ]
        );


        // Tabber Button Hover Styles
        $this->start_controls_tabs('tabber_button_tabs');


        // Hover Tab
        $this->start_controls_tab(
            'tabber_button_hover_tab',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'tabber_button_hover_text_color',
            [
                'label' => esc_html__('Hover Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabber_button_hover_bg_color',
            [
                'label' => esc_html__('Hover Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabber_button_hover_box_shadow',
                'label' => esc_html__('Hover Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabber_button_hover_border',
                'label' => esc_html__('Hover Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover',
            ]
        );

        // Tabber Button Icon Hover Color
        $this->add_control(
            'tabber_button_icon_hover_color',
            [
                'label' => esc_html__('Button Icon Hover Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover i, {{WRAPPER}} .cubewp-tabber-buttons .tabber-btn:hover svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Active Tab
        $this->start_controls_tab(
            'tabber_button_active_tab',
            [
                'label' => esc_html__('Active', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'tabber_button_active_text_color',
            [
                'label' => esc_html__('Active Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tabber_button_active_bg_color',
            [
                'label' => esc_html__('Active Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'tabber_button_active_box_shadow',
                'label' => esc_html__('Active Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'tabber_button_active_border',
                'label' => esc_html__('Active Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active',
            ]
        );

        // Tabber Button Icon Active Color
        $this->add_control(
            'tabber_button_icon_active_color',
            [
                'label' => esc_html__('Button Icon Active Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active i, {{WRAPPER}} .cubewp-tabber-buttons .tabber-btn.active svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();



        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Form Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'background_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form' => 'background-color: {{VALUE}};',
                ],
                'default' => '#f9f9f9',
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 30,
                    'right' => 30,
                    'bottom' => 20,
                    'left' => 30,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-search-form',
                'default' => [
                    'width' => [
                        'top' => 1,
                        'right' => 1,
                        'bottom' => 1,
                        'left' => 1,
                    ],
                    'color' => '#ddd',
                    'style' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-search-form',
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 2,
                    'blur' => 5,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.1)',
                ],
            ]
        );

        $this->add_responsive_control(
            'margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 10,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'label_style_section',
            [
                'label' => esc_html__('Label Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'label_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form .cubewp-field-container label' => 'color: {{VALUE}};',
                ],
                'default' => '#333',
            ]
        );

        $this->add_control(
            'show_label',
            [
                'label' => esc_html__('Show Label', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => esc_html__('Show', 'cubewp-framework'),
                'label_on' => esc_html__('Hide', 'cubewp-framework'),
                'return_value' => 'none',
                'default' => 'block',
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form .cubewp-field-container label' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-search-form .cubewp-field-container label',
                'default' => [
                    'font_size' => '14px',
                    'font_weight' => '400',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form .cubewp-field-container label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 5,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'label_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-search-form .cubewp-field-container label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
            'input_style_section',
            [
                'label' => esc_html__('Input Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple , {{WRAPPER}}  .select2-selection.select2-selection--single span.select2-selection__rendered' => 'color: {{VALUE}};',
                ],
                'default' => '#333',
            ]
        );

        $this->add_control(
            'placeholder_color',
            [
                'label' => esc_html__('Placeholder Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container input::placeholder ,{{WRAPPER}}  .select2-container--default .select2-selection--single .select2-selection__placeholder' => 'color: {{VALUE}};',
                ],
                'default' => '#999',
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => esc_html__('Input Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple' => 'background-color: {{VALUE}};',
                ],
                'default' => '#fff',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} .cubewp-field-container input::placeholder, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple,{{WRAPPER}}  .select2-container--default .select2-selection--single .select2-selection__placeholder , {{WRAPPER}}  .select2-selection.select2-selection--single span.select2-selection__rendered',
                'default' => [
                    'font_size' => '14px',
                    'font_family' => 'Arial',
                ],
            ]
        );
        $this->add_responsive_control(
            'input_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_height',
            [
                'label' => esc_html__('Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple' => 'height: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 5,
                    'right' => 0,
                    'bottom' => 5,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple',
                'default' => [
                    'width' => [
                        'top' => 1,
                        'right' => 1,
                        'bottom' => 1,
                        'left' => 1,
                    ],
                    'color' => '#ddd',
                    'style' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container select, {{WRAPPER}} .cubewp-field-container input, {{WRAPPER}} span.select2-selection.select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple',
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 2,
                    'blur' => 5,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.1)',
                ],
            ]
        );
        $this->end_controls_section();

        // Radio Style Tab
        $this->start_controls_section(
            'radio_style_section',
            [
                'label' => esc_html__('Radio Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Container Padding
        $this->add_responsive_control(
            'radio_container_padding',
            [
                'label' => esc_html__('Container Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio,{{WRAPPER}} .cwp-field-radio-container li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};margin: 0;',
                ],
            ]
        );

        // Tabs: Normal & Active
        $this->start_controls_tabs('radio_label_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'radio_label_normal_tab',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        // Label Color
        $this->add_control(
            'radio_label_color',
            [
                'label' => esc_html__('Label Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label' => 'color: {{VALUE}}; display: block;position: relative;',
                ],
            ]
        );
        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'radio_label_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label',
            ]
        );
        // Switcher to hide/show radio input
        $this->add_control(
            'radio_hide_input',
            [
                'label' => esc_html__('Hide Input', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Hide', 'cubewp-framework'),
                'label_off' => esc_html__('Show', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio input[type="radio"]' => 'display: none;',
                ],
                'condition' => [],
            ]
        );

        // Label Padding
        $this->add_responsive_control(
            'radio_label_padding',
            [
                'label' => esc_html__('Label Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        // Label Margin
        $this->add_responsive_control(
            'radio_label_margin',
            [
                'label' => esc_html__('Label Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        // Label Background Color
        $this->add_control(
            'radio_label_bg_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'radio_label_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label',
            ]
        );

        // Border Radius
        $this->add_control(
            'radio_label_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box shadow for label (existing)
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'radio_label_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label',
            ]
        );


        // Box Shadow
        // Add a heading for custom radio
        $this->add_control(
            'radio_custom_styles_heading',
            [
                'label' => esc_html__('Custom Radio Styles', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Enable custom radio
        $this->add_control(
            'enable_custom_radio',
            [
                'label' => esc_html__('Enable Custom Radio', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );



        // Border for label:after
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'radio_label_after_border',
                'label' => esc_html__('Radio Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );

        // Box shadow for label:after
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'radio_label_after_box_shadow',
                'label' => esc_html__('Radio Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );

        // Width for label:after
        $this->add_responsive_control(
            'radio_label_after_width',
            [
                'label' => esc_html__('Radio Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after' => 'width: {{SIZE}}{{UNIT}}; content: ""; position: absolute;',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
            ]
        );

        // Height for label:after
        $this->add_responsive_control(
            'radio_label_after_height',
            [
                'label' => esc_html__('Radio Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
            ]
        );

        // Border radius for label:after
        $this->add_control(
            'radio_label_after_border_radius',
            [
                'label' => esc_html__('Radio Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
                'default' => [
                    'top' => 50,
                    'right' => 50,
                    'bottom' => 50,
                    'left' => 50,
                    'unit' => '%',
                ],
            ]
        );

        // Background color for label:after
        $this->add_control(
            'radio_label_after_bg_color',
            [
                'label' => esc_html__('Radio Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
                'default' => '#fff',
            ]
        );




        // Choose horizontal position: left or right
        $this->add_control(
            'field_radio_dots_horizontal_position',
            [
                'label' => esc_html__('Horizontal Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'cubewp-framework'),
                    'right' => esc_html__('Right', 'cubewp-framework'),
                ],
                'default' => 'left',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );
        // Show left control if left is selected
        $this->add_responsive_control(
            'field_radio_dots_position_left',
            [
                'label' => esc_html__('Left', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label::after'  => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_radio_dots_horizontal_position' => 'left',
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );
        // Show right control if right is selected
        $this->add_responsive_control(
            'field_radio_dots_position_right',
            [
                'label' => esc_html__('Right', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label::after' => 'right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_radio_dots_horizontal_position' => 'right',
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );

        // Choose vertical position: top or bottom
        $this->add_control(
            'field_radio_dots_vertical_position',
            [
                'label' => esc_html__('Vertical Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'top' => esc_html__('Top', 'cubewp-framework'),
                    'bottom' => esc_html__('Bottom', 'cubewp-framework'),
                ],
                'default' => 'top',
            ]
        );
        // Show top control if top is selected
        $this->add_responsive_control(
            'field_radio_dots_position_top',
            [
                'label' => esc_html__('Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label::after' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_radio_dots_vertical_position' => 'top',
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );
        // Show bottom control if bottom is selected
        $this->add_responsive_control(
            'field_radio_dots_position_bottom',
            [
                'label' => esc_html__('Bottom', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label::after' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_radio_dots_vertical_position' => 'bottom',
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );

        // Add transform control for radio dot
        $this->add_control(
            'field_radio_dots_transform',
            [
                'label' => esc_html__('Radio Dot Transform', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Enter CSS transform properties for the radio dot, e.g., scale(1.1) or rotate(5deg)', 'cubewp-framework'),
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-radio label:after' => 'transform: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );



        $this->end_controls_tab();

        // Active Tab
        $this->start_controls_tab(
            'radio_label_active_tab',
            [
                'label' => esc_html__('Active', 'cubewp-framework'),
            ]
        );

        // Checked Label Color
        $this->add_control(
            'radio_label_checked_color',
            [
                'label' => esc_html__('Checked Label Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Checked Label Background Color
        $this->add_control(
            'radio_label_checked_bg_color',
            [
                'label' => esc_html__('Checked Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Checked Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'radio_label_checked_typography',
                'label' => esc_html__('Checked Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-radio input:checked + label',
            ]
        );

        $this->add_responsive_control(
            'radio_label_checked_padding',
            [
                'label' => esc_html__('Checked Label Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        // Checked background color for label:after
        $this->add_control(
            'radio_label_after_checked_bg_color',
            [
                'label' => esc_html__('Radio Checked Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
                'default' => '#0073e6',
            ]
        );

        // Enable Checkmark Option for Radio
        $this->add_control(
            'radio_enable_checkmark',
            [
                'label' => esc_html__('Enable Checkmark', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                ],
            ]
        );

        // Checkmark Content (tick) for Radio
        $this->add_control(
            'radio_checkmark_content',
            [
                'label' => esc_html__('Checkmark Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => '✔',
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label:after' => 'content: "{{VALUE}}";',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                    'radio_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Color for Radio
        $this->add_control(
            'radio_checkmark_color',
            [
                'label' => esc_html__('Checkmark Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label:after' => 'color: {{VALUE}};',
                ],
                'default' => '#fff',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                    'radio_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Typography for Radio
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'radio_checkmark_typography',
                'label' => esc_html__('Checkmark Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-radio input:checked + label:after',
                'condition' => [
                    'enable_custom_radio' => 'yes',
                    'radio_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Padding for Radio
        $this->add_responsive_control(
            'radio_checkmark_padding',
            [
                'label' => esc_html__('Checkmark Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-radio input:checked + label:after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'condition' => [
                    'enable_custom_radio' => 'yes',
                    'radio_enable_checkmark' => 'yes',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Checkbox Style Tab (copy of Radio Style Tab, but for checkbox)
        $this->start_controls_section(
            'checkbox_style_section',
            [
                'label' => esc_html__('Checkbox Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Container Padding
        $this->add_responsive_control(
            'checkbox_container_padding',
            [
                'label' => esc_html__('Container Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => 0,
                    'right' => 20,
                    'bottom' => 5,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox, {{WRAPPER}} .cwp-field-checkbox-container li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};margin: 0;',
                ],
            ]
        );

        // Tabs: Normal & Active
        $this->start_controls_tabs('checkbox_label_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'checkbox_label_normal_tab',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        // Label Color
        $this->add_control(
            'checkbox_label_color',
            [
                'label' => esc_html__('Label Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label' => 'color: {{VALUE}}; display: block;position: relative;',
                ],
            ]
        );
        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'checkbox_label_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label',
            ]
        );
        // Switcher to hide/show checkbox input
        $this->add_control(
            'checkbox_hide_input',
            [
                'label' => esc_html__('Hide Input', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Hide', 'cubewp-framework'),
                'label_off' => esc_html__('Show', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox input[type="checkbox"]' => 'display: none;',
                ],
                'condition' => [],
            ]
        );

        // Label Padding
        $this->add_responsive_control(
            'checkbox_label_padding',
            [
                'label' => esc_html__('Label Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        // Label Margin
        $this->add_responsive_control(
            'checkbox_label_margin',
            [
                'label' => esc_html__('Label Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        // Label Background Color
        $this->add_control(
            'checkbox_label_bg_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'checkbox_label_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label',
            ]
        );

        // Border Radius
        $this->add_control(
            'checkbox_label_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box shadow for label (existing)
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'checkbox_label_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label',
            ]
        );

        // Add a heading for custom checkbox
        $this->add_control(
            'checkbox_custom_styles_heading',
            [
                'label' => esc_html__('Custom Checkbox Styles', 'cubewp-framework'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Enable custom checkbox
        $this->add_control(
            'enable_custom_checkbox',
            [
                'label' => esc_html__('Enable Custom Checkbox', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        // Border for label:after
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'checkbox_label_after_border',
                'label' => esc_html__('Checkbox Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        // Box shadow for label:after
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'checkbox_label_after_box_shadow',
                'label' => esc_html__('Checkbox Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        // Width for label:after
        $this->add_responsive_control(
            'checkbox_label_after_width',
            [
                'label' => esc_html__('Checkbox Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after' => 'width: {{SIZE}}{{UNIT}}; content: ""; position: absolute;',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
            ]
        );

        // Height for label:after
        $this->add_responsive_control(
            'checkbox_label_after_height',
            [
                'label' => esc_html__('Checkbox Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px',
                ],
            ]
        );

        // Border radius for label:after
        $this->add_control(
            'checkbox_label_after_border_radius',
            [
                'label' => esc_html__('Checkbox Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
                'default' => [
                    'top' => 4,
                    'right' => 4,
                    'bottom' => 4,
                    'left' => 4,
                    'unit' => 'px',
                ],
            ]
        );

        // Background color for label:after
        $this->add_control(
            'checkbox_label_after_bg_color',
            [
                'label' => esc_html__('Checkbox Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
                'default' => '#fff',
            ]
        );



        // Choose horizontal position: left or right
        $this->add_control(
            'field_checkbox_dots_horizontal_position',
            [
                'label' => esc_html__('Horizontal Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'left' => esc_html__('Left', 'cubewp-framework'),
                    'right' => esc_html__('Right', 'cubewp-framework'),
                ],
                'default' => 'left',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );
        // Show left control if left is selected
        $this->add_responsive_control(
            'field_checkbox_dots_position_left',
            [
                'label' => esc_html__('Left', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label::after'  => 'left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_checkbox_dots_horizontal_position' => 'left',
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );
        // Show right control if right is selected
        $this->add_responsive_control(
            'field_checkbox_dots_position_right',
            [
                'label' => esc_html__('Right', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label::after' => 'right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_checkbox_dots_horizontal_position' => 'right',
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        // Choose vertical position: top or bottom
        $this->add_control(
            'field_checkbox_dots_vertical_position',
            [
                'label' => esc_html__('Vertical Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'top' => esc_html__('Top', 'cubewp-framework'),
                    'bottom' => esc_html__('Bottom', 'cubewp-framework'),
                ],
                'default' => 'top',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );
        // Show top control if top is selected
        $this->add_responsive_control(
            'field_checkbox_dots_position_top',
            [
                'label' => esc_html__('Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label::after' => 'top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_checkbox_dots_vertical_position' => 'top',
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );
        // Show bottom control if bottom is selected
        $this->add_responsive_control(
            'field_checkbox_dots_position_bottom',
            [
                'label' => esc_html__('Bottom', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label::after' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'field_checkbox_dots_vertical_position' => 'bottom',
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        // Add transform control for checkbox dot
        $this->add_control(
            'field_checkbox_dots_transform',
            [
                'label' => esc_html__('Checkbox Dot Transform', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Enter CSS transform properties for the checkbox dot, e.g., scale(1.1) or rotate(5deg)', 'cubewp-framework'),
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-field-checkbox label:after' => 'transform: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        // Active Tab
        $this->start_controls_tab(
            'checkbox_label_active_tab',
            [
                'label' => esc_html__('Active', 'cubewp-framework'),
            ]
        );

        // Checked Label Color
        $this->add_control(
            'checkbox_label_checked_color',
            [
                'label' => esc_html__('Checked Label Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Checked Label Background Color
        $this->add_control(
            'checkbox_label_checked_bg_color',
            [
                'label' => esc_html__('Checked Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Checked Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'checkbox_label_checked_typography',
                'label' => esc_html__('Checked Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox input:checked + label',
            ]
        );
        // Checked background color for label:after
        $this->add_control(
            'checkbox_label_after_checked_bg_color',
            [
                'label' => esc_html__('Checkbox Checked Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label:after' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
                'default' => '#0073e6',
            ]
        );
        $this->add_responsive_control(
            'checkbox_label_checked_padding',
            [
                'label' => esc_html__('Checked Label Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Enable Checkmark Option
        $this->add_control(
            'checkbox_enable_checkmark',
            [
                'label' => esc_html__('Enable Checkmark', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                ],
            ]
        );

        // Checkmark Content (tick)
        $this->add_control(
            'checkbox_checkmark_content',
            [
                'label' => esc_html__('Checkmark Text', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => '✔',
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label:after' => 'content: "{{VALUE}}";',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                    'checkbox_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Color
        $this->add_control(
            'checkbox_checkmark_color',
            [
                'label' => esc_html__('Checkmark Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label:after' => 'color: {{VALUE}};',
                ],
                'default' => '#fff',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                    'checkbox_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'checkbox_checkmark_typography',
                'label' => esc_html__('Checkmark Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-field-checkbox input:checked + label:after',
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                    'checkbox_enable_checkmark' => 'yes',
                ],
            ]
        );

        // Checkmark Padding
        $this->add_responsive_control(
            'checkbox_checkmark_padding',
            [
                'label' => esc_html__('Checkmark Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-field-checkbox input:checked + label:after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'condition' => [
                    'enable_custom_checkbox' => 'yes',
                    'checkbox_enable_checkmark' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        // Select Arrow Icon Controls
        $this->start_controls_section(
            'select_arrow_icon_section',
            [
                'label' => esc_html__('Select Arrow Icon', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Arrow Icon Top
        $this->add_responsive_control(
            'select_arrow_icon_top',
            [
                'label' => esc_html__('Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-search-field-dropdown::after' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Arrow Icon Right
        $this->add_responsive_control(
            'select_arrow_icon_right',
            [
                'label' => esc_html__('Right', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-search-field-dropdown::after' => 'right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Arrow Icon Color
        $this->add_control(
            'select_arrow_icon_color',
            [
                'label' => esc_html__('Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-search-field-dropdown::after' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Arrow Icon Size (font-size)
        $this->add_responsive_control(
            'select_arrow_icon_size',
            [
                'label' => esc_html__('Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cubewp-field-container .cwp-search-field-dropdown::after' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();





        $cwp_search_fields = CWP()->get_form('search_fields');
        // Add a separate Style Tab for each search field with icon
        if (!empty($cwp_search_fields)) {
            foreach ($cwp_search_fields as $postType => $field) {
                $fields = isset($field['fields']) ? $field['fields'] : array();
                foreach ($fields as $field_key => $field_value) {
                    // Skip adding icon control for the search_button field
                    $field_key =  $field_key . '_' . $postType;

                    if ($field_key === 'search_button') {
                        $add_icon = false;
                    } else {
                        $add_icon = true;
                    }

                    $field_label = isset($field_value['label']) ? $field_value['label'] : '';
                    if (!empty($field_label)) {
                        $words = explode(' ', $field_label);
                        if (count($words) > 3) {
                            $field_label = implode(' ', array_slice($words, 0, 3)) . '..';
                        }
                    }
                    $field_name = isset($field_value['name']) ? $field_value['name'] : '';
                    $field_type = isset($field_value['type']) ? $field_value['type'] : '';
                    if (isset($field_type) && $field_type == 'taxonomy') {
                        $field_type = isset($field_value['display_ui']) ? $field_value['display_ui'] : '';
                    }

                    $selector = '{{WRAPPER}} .cubewp-field-container[data-name="' . esc_attr($field_name) . '"]';
                    // Reduce label size for field section heading
                    $this->start_controls_section(
                        'field_' . $field_key . '_style_section',
                        [
                            'label' =>  esc_html__($field_label . ' ( ' . $postType . ' Field Container)', 'cubewp-framework'),
                            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                            'condition' => [
                                'post_type' => $postType,
                            ],
                            // Add Elementor's 'heading_size' to reduce label size
                            'heading_size' => 'h6',
                        ]
                    );

                    $this->add_responsive_control(
                        'field_' . $field_key . '_width',
                        [
                            'label' => esc_html__('Width', 'cubewp-framework'),
                            'type' => Controls_Manager::SLIDER,
                            'size_units' => ['px', '%', 'vw'],
                            'default' => [
                                'size' => 100,
                                'unit' => '%',
                            ],
                            'selectors' => [
                                $selector => 'width: {{SIZE}}{{UNIT}}; position: relative;',
                                '{{WRAPPER}} .cwp-field-container' => 'padding: 0;margin: 0;',
                            ],
                            'condition' => [
                                'post_type' => $postType,
                            ],
                        ]
                    );

                    $this->add_responsive_control(
                        'field_' . $field_key . '_margin',
                        [
                            'label' => esc_html__('Margin', 'cubewp-framework'),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => ['px', '%', 'em'],
                            'default' => [
                                'top' => 0,
                                'right' => 0,
                                'bottom' => 20,
                                'left' => 0,
                                'unit' => 'px',
                            ],
                            'selectors' => [
                                $selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;position: relative;',
                            ],
                            'condition' => [
                                'post_type' => $postType,
                            ],
                        ]
                    );

                    $this->add_responsive_control(
                        'field_' . $field_key . '_padding',
                        [
                            'label' => esc_html__('Padding', 'cubewp-framework'),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => ['px', '%', 'em'],
                            'selectors' => [
                                $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;position: relative;',
                            ],
                            'condition' => [
                                'post_type' => $postType,
                            ],
                        ]
                    );


                    // Field Background Color
                    $this->add_control(
                        'field_' . $field_key . '_bg_color',
                        [
                            'label' => esc_html__('Background Color', 'cubewp-framework'),
                            'type' => Controls_Manager::COLOR,
                            'selectors' => [
                                $selector => 'background-color: {{VALUE}};',
                            ],
                        ]
                    );

                    // Field Box Shadow
                    $this->add_group_control(
                        \Elementor\Group_Control_Box_Shadow::get_type(),
                        [
                            'name' => 'field_' . $field_key . '_box_shadow',
                            'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                            'selector' => $selector,
                        ]
                    );

                    // Field Border
                    $this->add_group_control(
                        \Elementor\Group_Control_Border::get_type(),
                        [
                            'name' => 'field_' . $field_key . '_border',
                            'label' => esc_html__('Border', 'cubewp-framework'),
                            'selector' => $selector,
                        ]
                    );

                    // Field Border Radius
                    $this->add_control(
                        'field_' . $field_key . '_border_radius',
                        [
                            'label' => esc_html__('Border Radius', 'cubewp-framework'),
                            'type' => Controls_Manager::DIMENSIONS,
                            'size_units' => ['px', '%', 'em'],
                            'selectors' => [
                                $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                            ],
                        ]
                    );

                    // If field type is radio, add position controls
                    if ($field_type == 'radio' || $field_type == 'checkbox') {
                        // Position type: static, relative, absolute, fixed, sticky
                        $this->add_control(
                            'field_' . $field_key . '_position_type',
                            [
                                'label' => esc_html__('Position', 'cubewp-framework'),
                                'type' => Controls_Manager::SELECT,
                                'options' => [
                                    'static' => esc_html__('Static', 'cubewp-framework'),
                                    'relative' => esc_html__('Relative', 'cubewp-framework'),
                                    'absolute' => esc_html__('Absolute', 'cubewp-framework'),
                                    'fixed' => esc_html__('Fixed', 'cubewp-framework'),
                                    'sticky' => esc_html__('Sticky', 'cubewp-framework'),
                                ],
                                'default' => 'relative',
                                'selectors' => [
                                    $selector => 'position: {{VALUE}};    overflow: hidden;',
                                ],
                            ]
                        );

                        // Choose horizontal position: left or right
                        $this->add_control(
                            'field_' . $field_key . '_horizontal_position',
                            [
                                'label' => esc_html__('Horizontal Position', 'cubewp-framework'),
                                'type' => Controls_Manager::SELECT,
                                'options' => [
                                    'left' => esc_html__('Left', 'cubewp-framework'),
                                    'right' => esc_html__('Right', 'cubewp-framework'),
                                ],
                                'default' => 'left',
                            ]
                        );
                        // Show left control if left is selected
                        $this->add_responsive_control(
                            'field_' . $field_key . '_position_left',
                            [
                                'label' => esc_html__('Left', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', '%', 'em', 'rem'],
                                'selectors' => [
                                    $selector => 'left: {{SIZE}}{{UNIT}};',
                                ],
                                'condition' => [
                                    'field_' . $field_key . '_horizontal_position' => 'left',
                                ],
                            ]
                        );
                        // Show right control if right is selected
                        $this->add_responsive_control(
                            'field_' . $field_key . '_position_right',
                            [
                                'label' => esc_html__('Right', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', '%', 'em', 'rem'],
                                'selectors' => [
                                    $selector => 'right: {{SIZE}}{{UNIT}};',
                                ],
                                'condition' => [
                                    'field_' . $field_key . '_horizontal_position' => 'right',
                                ],
                            ]
                        );

                        // Choose vertical position: top or bottom
                        $this->add_control(
                            'field_' . $field_key . '_vertical_position',
                            [
                                'label' => esc_html__('Vertical Position', 'cubewp-framework'),
                                'type' => Controls_Manager::SELECT,
                                'options' => [
                                    'top' => esc_html__('Top', 'cubewp-framework'),
                                    'bottom' => esc_html__('Bottom', 'cubewp-framework'),
                                ],
                                'default' => 'top',
                            ]
                        );
                        // Show top control if top is selected
                        $this->add_responsive_control(
                            'field_' . $field_key . '_position_top',
                            [
                                'label' => esc_html__('Top', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', '%', 'em', 'rem'],
                                'selectors' => [
                                    $selector => 'top: {{SIZE}}{{UNIT}};',
                                ],
                                'condition' => [
                                    'field_' . $field_key . '_vertical_position' => 'top',
                                ],
                            ]
                        );
                        // Show bottom control if bottom is selected
                        $this->add_responsive_control(
                            'field_' . $field_key . '_position_bottom',
                            [
                                'label' => esc_html__('Bottom', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', '%', 'em', 'rem'],
                                'selectors' => [
                                    $selector => 'bottom: {{SIZE}}{{UNIT}};',
                                ],
                                'condition' => [
                                    'field_' . $field_key . '_vertical_position' => 'bottom',
                                ],
                            ]
                        );

                        // Enable Grid for Radio
                        $this->add_control(
                            'field_' . $field_key . '_enable_grid',
                            [
                                'label' => esc_html__('Enable Grid', 'cubewp-framework'),
                                'type' => Controls_Manager::SWITCHER,
                                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                                'label_off' => esc_html__('No', 'cubewp-framework'),
                                'return_value' => 'yes',
                                'default' => '',
                            ]
                        );

                        // Grid Columns Counter
                        $this->add_control(
                            'field_' . $field_key . '_grid_columns',
                            [
                                'label' => esc_html__('Grid Columns', 'cubewp-framework'),
                                'type' => Controls_Manager::NUMBER,
                                'min' => 1,
                                'max' => 12,
                                'step' => 1,
                                'default' => 3,
                                'condition' => [
                                    'field_' . $field_key . '_enable_grid' => 'yes',
                                ],
                                'selectors' => [
                                    $selector . ' .cwp-field-radio-container li , ' . $selector . ' .cwp-field-checkbox-container li'  => 'width: calc(100% / {{VALUE}});',
                                    '{{WRAPPER}} .cwp-field-container .cwp-field-radio-container,{{WRAPPER}} .cwp-field-container .cwp-field-checkbox-container' => ' width: 100%;',
                                ],
                            ]
                        );
                    }

                    if ($add_icon) {
                        $this->add_control(
                            'field_' . $field_key . '_icon',
                            [
                                'label' => esc_html__('Field Icon', 'cubewp-framework'),
                                'type' => Controls_Manager::ICONS,
                                'fa4compatibility' => 'icon',
                            ]
                        );

                        // Icon Size (font-size for i, width for svg)
                        $this->add_responsive_control(
                            'field_' . $field_key . '_icon_size',
                            [
                                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', 'em', 'rem'],
                                'default' => [
                                    'size' => 16,
                                    'unit' => 'px',
                                ],
                                'selectors' => [
                                    $selector . ' .field-icons i' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: auto;',
                                    $selector . ' .field-icons svg' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
                                ],
                            ]
                        );

                        // Icon Color
                        $this->add_control(
                            'field_' . $field_key . '_icon_color',
                            [
                                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                                'type' => Controls_Manager::COLOR,
                                'selectors' => [
                                    $selector . ' .field-icons i' => 'color: {{VALUE}};',
                                    $selector . ' .field-icons svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
                                    $selector . ' .field-icons path' => 'fill: {{VALUE}}; color: {{VALUE}};',
                                ],
                            ]
                        );

                        // Icon Position Left
                        $this->add_responsive_control(
                            'field_' . $field_key . '_icon_left',
                            [
                                'label' => esc_html__('Icon Position Left', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', 'em', '%'],
                                'selectors' => [
                                    $selector . ' .field-icons' => 'left: {{SIZE}}{{UNIT}};position: absolute;',
                                ],
                                'default' => [
                                    'size' => 0,
                                    'unit' => 'px',
                                ],
                            ]
                        );

                        $this->add_responsive_control(
                            'field_' . $field_key . '_icon_top',
                            [
                                'label' => esc_html__('Icon Position Top', 'cubewp-framework'),
                                'type' => Controls_Manager::SLIDER,
                                'size_units' => ['px', 'em', '%'],
                                'selectors' => [
                                    $selector . ' .field-icons' => 'top: {{SIZE}}{{UNIT}};position: absolute;',
                                ],
                                'default' => [
                                    'size' => 0,
                                    'unit' => 'px',
                                ],
                            ]
                        );
                    }

                    $this->end_controls_section();
                }
            }
        }




        $this->start_controls_section(
            'submit_button_style_section',
            [
                'label' => esc_html__('Submit Button Style', 'cubewp-framework'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('submit_button_tabs');

        // Normal Tab
        $this->start_controls_tab(
            'submit_button_normal_tab',
            [
                'label' => esc_html__('Normal', 'cubewp-framework'),
            ]
        );

        // Button Background Color
        $this->add_control(
            'submit_button_bg_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'background-color: {{VALUE}};',
                ],
                'default' => '#0073e6',
            ]
        );

        // Button Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'submit_button_typography',
                'label' => esc_html__('Typography', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-submit-search',
            ]
        );

        // Button Icon
        $this->add_control(
            'submit_button_icon',
            [
                'label' => esc_html__('Button Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default' => [
                    'value' => '',
                    'library' => 'solid',
                ],
            ]
        );


        // Button Flex Direction
        $this->add_control(
            'submit_button_direction',
            [
                'label' => esc_html__('Direction', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    'row' => esc_html__('Row', 'cubewp-framework'),
                    'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
                    'column' => esc_html__('Column', 'cubewp-framework'),
                    'column-reverse' => esc_html__('Column Reverse', 'cubewp-framework'),
                ],
                'default' => 'row',
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'display: flex; align-items: center; justify-content: center; flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_gap',
            [
                'label' => esc_html__('Gap', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 8,
                    'unit' => 'px',
                ],
            ]
        );

        // Button Icon Color
        $this->add_control(
            'submit_button_icon_color',
            [
                'label' => esc_html__('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search i, {{WRAPPER}} .cwp-submit-search svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );


        // Button Icon Width
        $this->add_responsive_control(
            'submit_button_icon_width',
            [
                'label' => esc_html__('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search svg' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
                    '{{WRAPPER}} .cwp-submit-search i' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: auto;',
                ],
            ]
        );

        $this->add_control(
            'submit_button_text_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'color: {{VALUE}};',
                ],
                'default' => '#ffffff',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'submit_button_border',
                'label' => esc_html__('Border', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-submit-search',
                'default' => [
                    'width' => [
                        'top' => 1,
                        'right' => 1,
                        'bottom' => 1,
                        'left' => 1,
                    ],
                    'color' => '#ddd',
                    'style' => 'solid',
                ],
            ]
        );

        $this->add_control(
            'submit_button_border_radius',
            [
                'label' => esc_html__('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_control(
            'submit_button_hover_transform',
            [
                'label' => esc_html__('Hover Transform', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'description' => esc_html__('Enter CSS transform properties for hover effect, e.g., scale(1.1) or rotate(5deg)', 'cubewp-framework'),
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search:hover' => 'transform: {{VALUE}};',
                ],
                'default' => 'scale(1.01)',
            ]
        );

        $this->add_responsive_control(
            'submit_button_padding',
            [
                'label' => esc_html__('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 10,
                    'right' => 20,
                    'bottom' => 10,
                    'left' => 20,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_height',
            [
                'label' => esc_html__('Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 40,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_button_margin',
            [
                'label' => esc_html__('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 20,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ],
            ]
        );

        // Submit Button Box Shadow (existing)
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_button_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-submit-search',
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 2,
                    'blur' => 5,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.1)',
                ],
            ]
        );



        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'submit_button_hover_tab',
            [
                'label' => esc_html__('Hover', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'submit_button_hover_bg_color',
            [
                'label' => esc_html__('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search:hover' => 'background-color: {{VALUE}};',
                ],
                'default' => '#005bb5',
            ]
        );

        $this->add_control(
            'submit_button_hover_text_color',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search:hover' => 'color: {{VALUE}};',
                ],
                'default' => '#ffffff',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'submit_button_hover_box_shadow',
                'label' => esc_html__('Box Shadow', 'cubewp-framework'),
                'selector' => '{{WRAPPER}} .cwp-submit-search:hover',
                'default' => [
                    'horizontal' => 0,
                    'vertical' => 3,
                    'blur' => 7,
                    'spread' => 0,
                    'color' => 'rgba(0, 0, 0, 0.2)',
                ],
            ]
        );
        // Button Icon Hover Color
        $this->add_control(
            'submit_button_icon_hover_color',
            [
                'label' => esc_html__('Icon Hover Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-submit-search:hover i, {{WRAPPER}} .cwp-submit-search:hover svg ,  {{WRAPPER}} .cwp-submit-search:hover svg path' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render()
    { 
        CubeWp_Enqueue::enqueue_style('cwp-styles'); 
        CubeWp_Enqueue::enqueue_script('cwp-search');

        $settings = $this->get_settings_for_display();
        $post_type = $settings['post_type'];
        $submit_button_icon = $settings['submit_button_icon'];


        $args = array(
            'post_type' => $post_type,
            'submit_button_icon' => $submit_button_icon,
            'settings' => $settings,
        );

        echo apply_filters('cubewp_search_shortcode_output', '', $args);
    }
}
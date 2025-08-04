<?php
defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

/**
 * CubeWP Sorting Widgets.
 *
 * Elementor Widget For Sorting By CubeWP.
 *
 * @since 1.0.0
 */

class CubeWp_Elementor_Archive_Sorting_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'sorting_widget';
    }

    public function get_title()
    {
        return __('Sorting Fields Display', 'cubewp-framework'); // Changed domain for consistency
    }

    public function get_icon()
    {
        return 'eicon-sort-amount-desc';
    }

    public function get_categories()
    {
        return ['cubewp'];
    }

    protected function _register_controls()
    {
        // Container Style Section
        $this->start_controls_section(
            'container_style',
            [
                'label' => __('Container', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'container_background',
                'label'    => __('Background', 'cubewp-framework'),
                'types'    => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .cwp-filtered-sorting',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'container_border',
                'selector' => '{{WRAPPER}} .cwp-filtered-sorting',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label'      => __('Border Radius', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-filtered-sorting' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_padding',
            [
                'label'      => __('Padding', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-filtered-sorting' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_margin',
            [
                'label'      => __('Margin', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-filtered-sorting' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .cwp-filtered-sorting',
            ]
        );

        $this->end_controls_section();

        // Select Field Style Section
        $this->start_controls_section(
            'select_style',
            [
                'label' => __('Select Field', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'select_text_color',
            [
                'label'     => __('Text Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-sorting select' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'select_background_color',
            [
                'label'     => __('Background Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-sorting select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'select_typography',
                'selector' => '{{WRAPPER}} select#cwp-sorting-filter, {{WRAPPER}} select#cwp-order-filter',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'select_border',
                'selector' => '{{WRAPPER}} .cwp-filtered-sorting select',
            ]
        );

        $this->add_control(
            'select_border_radius',
            [
                'label'      => __('Border Radius', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors'  => [
                    '{{WRAPPER}} .cwp-filtered-sorting select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'select_padding',
            [
                'label'      => __('Padding', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} #cwp-sorting-filter, {{WRAPPER}} #cwp-order-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_control(
            'select_margin',
            [
                'label'      => __('Margin', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} #cwp-sorting-filter, {{WRAPPER}} #cwp-order-filter' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    '{{WRAPPER}} #cwp-sorting-filter, {{WRAPPER}} #cwp-order-filter' => 'height: auto !important;',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'select_box_shadow',
                'selector' => '{{WRAPPER}} select#cwp-sorting-filter, {{WRAPPER}} select#cwp-order-filter',
            ]
        );

        $this->end_controls_section();

        // Option Field Style Section
        $this->start_controls_section(
            'option_style',
            [
                'label' => __('Option Fields', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'option_text_color',
            [
                'label'     => __('Text Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-sorting select option' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'option_background_color',
            [
                'label'     => __('Background Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-sorting select option' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'option_typography',
                'selector' => '{{WRAPPER}} .cwp-filtered-sorting select option',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        // Assuming CubeWp_Frontend_Search_Filter exists and cwp_filter_sorting() outputs the HTML
        echo \CubeWp_Frontend_Search_Filter::cwp_filter_sorting();
    }
}
<?php
defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

/**
 * CubeWP Search Map Widgets.
 *
 * Elementor Widget For Search Map By CubeWP.
 *
 * @since 1.0.0
 */

class CubeWp_Elementor_Archive_Map_Widget extends Widget_Base
{

    public function get_name()
    {
        return 'search_map_display_widget';
    }

    public function get_title()
    {
        return __('Archive Map', 'cubewp-framework');
    }

    public function get_icon()
    {
        return 'eicon-google-maps';
    }

    public function get_categories()
    {
        return ['cubewp'];
    }

    protected function _register_controls()
    {

        /* =====================
         * MAP DISPLAY SETTINGS
         * ===================== */
        $this->start_controls_section(
            'section_map_popup',
            [
                'label' => __('Map Display Settings', 'cubewp-framework'),
            ]
        );

        $this->add_control(
            'enable_map_popup',
            [
                'label'        => __('Enable Map Toggle/Popup', 'cubewp-framework'),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'cubewp-framework'),
                'label_off'    => __('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default'      => '',
            ]
        );
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


        $this->add_control(
            'map_display_type',
            [
                'label'   => __('Display Type', 'cubewp-framework'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'toggle',
                'options' => [
                    'toggle' => __('Toggle (In-line)', 'cubewp-framework'),
                    'popup'  => __('Full Popup (Modal)', 'cubewp-framework'),
                ],
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'popup_button_text',
            [
                'label'     => __('Open Button Text', 'cubewp-framework'),
                'type'      => Controls_Manager::TEXT,
                'default'   => __('View Map', 'cubewp-framework'),
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'popup_button_icon',
            [
                'label'     => __('Open Button Icon', 'cubewp-framework'),
                'type'      => Controls_Manager::ICONS,
                'default'   => [
                    'value'   => 'fas fa-map-marked-alt',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'close_button_icon',
            [
                'label'     => __('Close Button Icon', 'cubewp-framework'),
                'type'      => Controls_Manager::ICONS,
                'default'   => [
                    'value'   => 'fas fa-times',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'enable_map_popup' => 'yes',
                    'map_display_type!' => 'toggle',
                ],
            ]
        );

        $this->end_controls_section();

        /* =====================
         * MAP STYLE
         * ===================== */
        $this->start_controls_section(
            'section_map_style',
            [
                'label' => __('Map Container', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'map_height',
            [
                'label' => __('Map Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vh'],
                'range' => [
                    'px' => ['min' => 100, 'max' => 1000],
                    'vh' => ['min' => 10, 'max' => 100],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 400,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-content-map' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-archive-content-map #archive-map' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_width',
            [
                'label' => __('Map Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => ['min' => 100, 'max' => 1000],
                    '%' => ['min' => 10, 'max' => 100],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-content-map-main .cwp-archive-content-map-inner' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'map_border',
                'selector' => '{{WRAPPER}} .cwp-archive-content-map',
            ]
        );

        $this->add_responsive_control(
            'map_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-content-map' => 'overflow: hidden; border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* =====================
         * OPEN BUTTON STYLE
         * ===================== */
        $this->start_controls_section(
            'section_map_popup_btn_style',
            [
                'label' => __('Toggle/Open Button', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'popup_btn_typography',
                'selector' => '{{WRAPPER}} .cwp-map-toggle-btn',
            ]
        );

        $this->add_control(
            'popup_btn_icon_align',
            [
                'label' => __('Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'row',
                'options' => [
                    'row' => __('Left', 'cubewp-framework'),
                    'row-reverse' => __('Right', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-toggle-btn' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_btn_icon_indent',
            [
                'label' => __('Icon Spacing', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-toggle-btn' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'popup_btn_icon_size',
            [
                'label' => __('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 10, 'max' => 100]],
                'default' => ['size' => 18, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-toggle-btn' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-map-toggle-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('popup_btn_tabs');

        $this->start_controls_tab('popup_btn_normal', ['label' => __('Normal', 'cubewp-framework')]);

        $this->add_control(
            'popup_btn_color',
            [
                'label' => __('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => ['{{WRAPPER}} .cwp-map-toggle-btn' => 'color: {{VALUE}}'],
            ]
        );

        $this->add_control(
            'popup_btn_icon_color',
            [
                'label' => __('Icon Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-toggle-btn i' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .cwp-map-toggle-btn svg' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'popup_btn_bg',
            [
                'label' => __('Background', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#3b82f6',
                'selectors' => ['{{WRAPPER}} .cwp-map-toggle-btn' => 'background-color: {{VALUE}}'],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('popup_btn_hover', ['label' => __('Hover', 'cubewp-framework')]);

        $this->add_control(
            'popup_btn_hvr_color',
            [
                'label' => __('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} .cwp-map-toggle-btn:hover' => 'color: {{VALUE}}'],
            ]
        );

        $this->add_control(
            'popup_btn_hvr_bg',
            [
                'label' => __('Background', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} .cwp-map-toggle-btn:hover' => 'background-color: {{VALUE}}'],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_responsive_control(
            'popup_btn_padding',
            [
                'label' => __('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => 12,
                    'right' => 24,
                    'bottom' => 12,
                    'left' => 24,
                    'unit' => 'px',
                    'isLinked' => false
                ],
                'selectors' => ['{{WRAPPER}} .cwp-map-toggle-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        /* =====================
         * CLOSE BUTTON STYLE
         * ===================== */
        $this->start_controls_section(
            'section_popup_setting',
            [
                'label' => __('Popup Settings', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );
        $this->add_control(
            'popup_overlay_background_color',
            [
                'label' => __('Overlay Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .cwp-archive-content-map-main' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();


        /* =====================
         * CLOSE BUTTON STYLE
         * ===================== */
        $this->start_controls_section(
            'section_close_btn_style',
            [
                'label' => __('Close Button (Popup Mode)', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_map_popup' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_btn_size',
            [
                'label' => __('Icon Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'range' => ['px' => ['min' => 10, 'max' => 100]],
                'default' => ['size' => 20, 'unit' => 'px'],

                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwp-map-close-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'close_btn_color',
            [
                'label' => __('Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .cwp-map-close-btn svg' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'close_btn_bg_color',
            [
                'label' => __('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'background: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_btn_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_btn_padding',
            [
                'label' => __('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_btn_position',
            [
                'label' => __('Position Offset Top', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vh'],
                'range' => ['px' => ['min' => 0, 'max' => 100], '%' => ['min' => 0, 'max' => 100], 'vh' => ['min' => 0, 'max' => 100]],
                'default' => ['size' => 20, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'close_btn_position_right',
            [
                'label' => __('Position Offset Right', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vh'],
                'range' => ['px' => ['min' => 0, 'max' => 100], '%' => ['min' => 0, 'max' => 100], 'vh' => ['min' => 0, 'max' => 100]],
                'default' => ['size' => 20, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-close-btn' => 'right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // New Map Pin Tab Section
        $this->start_controls_section(
            'map_pin_section',
            [
                'label' => __('Map Pin', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'custom_map_pin_switcher',
            [
                'label' => __('Custom Map Pin', 'cubewp-framework'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cubewp-framework'),
                'label_off' => __('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => 'no'
            ]
        );

        $this->add_control(
            'custom_map_pin_icon',
            [
                'label' => __('Pin Icon', 'cubewp-framework'),
                'type' => Controls_Manager::ICONS,
                'condition' => [
                    'custom_map_pin_switcher' => 'yes',
                ],
            ]
        );


        $this->add_control(
            'map_pin_style_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw'  => __('Map Pin Styling available when Custom Map Pin is enabled', 'cubewp-framework'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition' => ['custom_map_pin_switcher' => 'yes'],
            ]
        );
        $this->end_controls_section();

        // Map Pin Wrapper (.leaflet-marker-icon .cwp-pin)
        $this->start_controls_section(
            'map_pin_wrapper_style',
            [
                'label' => __('Pin Wrapper', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['custom_map_pin_switcher' => 'yes'],
            ]
        );

        $this->add_control(
            'map_pin_wrapper_bg_color',
            [
                'label' => __('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'background-color: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'map_pin_wrapper_transition',
            [
                'label' => __('Transition', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => 'all 0.3s',
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'transition: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'map_pin_wrapper_width',
            [
                'label' => __('Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => ['px' => ['min' => 10, 'max' => 200], '%' => ['min' => 1, 'max' => 100], 'em' => ['min' => 1, 'max' => 10]],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'map_pin_wrapper_height',
            [
                'label' => __('Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => ['px' => ['min' => 10, 'max' => 200], '%' => ['min' => 1, 'max' => 100], 'em' => ['min' => 1, 'max' => 10]],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'map_pin_wrapper_padding',
            [
                'label' => __('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'map_pin_wrapper_border',
                'selector' => '{{WRAPPER}} .leaflet-marker-icon .cwp-pin',
            ]
        );

        $this->add_responsive_control(
            'map_pin_wrapper_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'map_pin_wrapper_shadow',
                'selector' => '{{WRAPPER}} .leaflet-marker-icon .cwp-pin',
            ]
        );

        $this->add_control(
            'map_pin_wrapper_transform_rotate',
            [
                'label' => __('Rotate (deg)', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['deg'],
                'range' => ['deg' => ['min' => 0, 'max' => 360]],
                'default' => ['size' => 0, 'unit' => 'deg'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin' => 'transform: rotate({{SIZE}}deg);',
                ],
            ]
        );

        // Hover options for .cwp-pin
        $this->start_controls_tabs('map_pin_wrapper_style_tabs');
        $this->start_controls_tab(
            'map_pin_wrapper_normal',
            [
                'label' => __('Normal', 'cubewp-framework'),
            ]
        );
        // Nothing - fields above apply to normal state
        $this->end_controls_tab();

        $this->start_controls_tab(
            'map_pin_wrapper_hover',
            [
                'label' => __('Hover', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'map_pin_wrapper_hover_bg_color',
            [
                'label' => __('Background Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin:hover' => 'background-color: {{VALUE}};',
                ]
            ]
        );
        $this->add_control(
            'map_pin_wrapper_hover_transition',
            [
                'label' => __('Transition (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => 'all 0.3s',
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin:hover' => 'transition: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();


        // Icon Wrapper (.leaflet-marker-icon .cwp-pin .cwp-map-pin-icon)
        $this->start_controls_section(
            'map_pin_icon_wrapper_style',
            [
                'label' => __('Icon Wrapper', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['custom_map_pin_switcher' => 'yes'],
            ]
        );

        $this->add_responsive_control(
            'map_pin_icon_wrapper_width',
            [
                'label' => __('Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => ['px' => ['min' => 10, 'max' => 200], '%' => ['min' => 1, 'max' => 100], 'em' => ['min' => 1, 'max' => 10]],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'width: {{SIZE}}{{UNIT}};display: inline-block;',
                ]
            ]
        );
        $this->add_responsive_control(
            'map_pin_icon_wrapper_height',
            [
                'label' => __('Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => ['px' => ['min' => 10, 'max' => 200], '%' => ['min' => 1, 'max' => 100], 'em' => ['min' => 1, 'max' => 10]],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'height: {{SIZE}}{{UNIT}};',
                ]
            ]
        );
        $this->add_responsive_control(
            'map_pin_icon_wrapper_padding',
            [
                'label' => __('Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'map_pin_icon_wrapper_border',
                'selector' => '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon',
            ]
        );
        $this->add_responsive_control(
            'map_pin_icon_wrapper_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'map_pin_icon_wrapper_shadow',
                'selector' => '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon',
            ]
        );
        $this->add_control(
            'map_pin_icon_wrapper_bg_color',
            [
                'label' => __('Background Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'background-color: {{VALUE}};',
                ]
            ]
        );
        $this->add_control(
            'map_pin_icon_wrapper_transform_rotate',
            [
                'label' => __('Rotate (deg)', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['deg'],
                'range' => ['deg' => ['min' => 0, 'max' => 360]],
                'default' => ['size' => 0, 'unit' => 'deg'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon' => 'transform: rotate({{SIZE}}deg);',
                ],
            ]
        );
        $this->end_controls_section();

        // Icon SVG (.leaflet-marker-icon .cwp-pin .cwp-map-pin-icon svg)
        $this->start_controls_section(
            'map_pin_icon_svg_style',
            [
                'label' => __('Pin Icon SVG', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => ['custom_map_pin_switcher' => 'yes'],
            ]
        );

        $this->add_control(
            'map_pin_icon_svg_color',
            [
                'label' => __('SVG Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ]
            ]
        );

        $this->add_responsive_control(
            'map_pin_icon_svg_size',
            [
                'label' => __('SVG Size', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => ['min' => 8, 'max' => 200],
                    'em' => ['min' => 0.5, 'max' => 10],
                    'rem' => ['min' => 0.5, 'max' => 10],
                    '%' => ['min' => 10, 'max' => 100]
                ],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'map_pin_icon_svg_rotate',
            [
                'label' => __('SVG Rotate (deg)', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['deg'],
                'range' => [
                    'deg' => ['min' => 0, 'max' => 360],
                ],
                'default' => ['size' => 0, 'unit' => 'deg'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-marker-icon .cwp-pin .cwp-map-pin-icon svg' => 'transform: rotate({{SIZE}}deg);',
                ],
            ]
        );

        $this->end_controls_section();
        // Icon SVG (.leaflet-marker-icon .cwp-pin .cwp-map-pin-icon svg)
        $this->start_controls_section(
            'map_post_card',
            [
                'label' => __('Map Post Card', 'cubewp-framework'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        // Main Card Controls (background, box shadow, border, margin, radius)
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'map_popup_card_bg',
                'selector' => '{{WRAPPER}} .leaflet-popup-content-wrapper,{{WRAPPER}} .leaflet-popup-tip-container .leaflet-popup-tip',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'map_popup_card_shadow',
                'selector' => '{{WRAPPER}} .leaflet-popup-content-wrapper ,{{WRAPPER}} .leaflet-popup-tip-container .leaflet-popup-tip',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'map_popup_card_border',
                'selector' => '{{WRAPPER}} .leaflet-popup-content-wrapper ,{{WRAPPER}} .leaflet-popup-tip-container .leaflet-popup-tip',
            ]
        );
        $this->add_control(
            'map_popup_card_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 50],
                    '%' => ['min' => 0, 'max' => 50],
                    'em' => ['min' => 0, 'max' => 10],
                ],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-popup-content-wrapper' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_card_margin',
            [
                'label' => __('Margin', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-popup' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Content Wrapper Controls
        $this->add_responsive_control(
            'map_popup_content_padding',
            [
                'label' => __('Content Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-popup-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .leaflet-popup-content a' => 'display:inline-block;max-width: 100%;',
                    '{{WRAPPER}} .cwp-map-popover' => 'width: 100%;',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_content_width',
            [
                'label' => __('Content Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 100, 'max' => 500],
                    '%' => ['min' => 10, 'max' => 100],
                    'em' => ['min' => 5, 'max' => 30],
                ],
                'selectors' => [
                    '{{WRAPPER}} .leaflet-popup-content' => 'width: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .cwp-map-popover' => 'width: {{SIZE}}{{UNIT}} !important;',
                ],
            ]
        );

        $this->add_responsive_control(
            'map_popup_content_radius',
            [
                'label' => __('Border Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Image Controls
        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'map_popup_image',
                'label' => __('Image Size', 'cubewp-framework'),
                'default' => 'full',
            ]
        );
        $this->add_responsive_control(
            'map_popup_image_width',
            [
                'label' => __('Image Width', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 40, 'max' => 400],
                    '%' => ['min' => 10, 'max' => 100],
                    'em' => ['min' => 2, 'max' => 30],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_image_height',
            [
                'label' => __('Image Height', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => ['min' => 40, 'max' => 400],
                    '%' => ['min' => 10, 'max' => 100],
                    'em' => ['min' => 2, 'max' => 30],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'map_popup_image_object_fit',
            [
                'label' => __('Image Object Fit', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'fill' => __('Fill', 'cubewp-framework'),
                    'contain' => __('Contain', 'cubewp-framework'),
                    'cover' => __('Cover', 'cubewp-framework'),
                    'none' => __('None', 'cubewp-framework'),
                    'scale-down' => __('Scale Down', 'cubewp-framework'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover img' => 'object-fit: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_image_position',
            [
                'label' => __('Image Position', 'plugin-name'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'left top' => __('Top Left', 'plugin-name'),
                    'center top' => __('Top Center', 'plugin-name'),
                    'right top' => __('Top Right', 'plugin-name'),
                    'left center' => __('Center Left', 'plugin-name'),
                    'center center' => __('Center Center', 'plugin-name'),
                    'right center' => __('Center Right', 'plugin-name'),
                    'left bottom' => __('Bottom Left', 'plugin-name'),
                    'center bottom' => __('Bottom Center', 'plugin-name'),
                    'right bottom' => __('Bottom Right', 'plugin-name'),
                ],
                'default' => 'center center',
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover img' => 'object-position: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_image_radius',
            [
                'label' => __('Image Radius', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title Controls
        $this->add_responsive_control(
            'map_popup_title_padding',
            [
                'label' => __('Title Padding', 'cubewp-framework'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover h3' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'map_popup_title_alignment',
            [
                'label' => __('Title Alignment', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left'    => [
                        'title' => __('Left', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'right'   => [
                        'title' => __('Right', 'cubewp-framework'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover h3' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'map_popup_title_color',
            [
                'label' => __('Title Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover h3' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'map_popup_title_color_hover',
            [
                'label' => __('Title Color (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover h3:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'map_popup_title_color_hover_transition',
            [
                'label' => __('Transition (Hover)', 'cubewp-framework'),
                'type' => Controls_Manager::TEXT,
                'default' => 'color 0.3s',
                'selectors' => [
                    '{{WRAPPER}} .cwp-map-popover h3' => 'transition: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'map_popup_title_typography',
                'selector' => '{{WRAPPER}} .cwp-map-popover h3',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        CubeWp_Enqueue::enqueue_style('cwp-map-cluster');
        CubeWp_Enqueue::enqueue_style('cwp-leaflet-css');
        CubeWp_Enqueue::enqueue_script('cubewp-map');
        CubeWp_Enqueue::enqueue_script('cubewp-leaflet');
        CubeWp_Enqueue::enqueue_script('cubewp-leaflet-cluster');
        CubeWp_Enqueue::enqueue_script('cubewp-leaflet-fullscreen');

        $show_data_for = isset($settings['show_data_for']) ? $settings['show_data_for'] : 'mobile_desktop';

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


        $popup_enabled = ($settings['enable_map_popup'] === 'yes');
        $display_type  = $settings['map_display_type'];
?>
        <?php
        // Build the custom map pin HTML if icon enabled
        $map_pin_html = '';
        if (isset($settings['custom_map_pin_switcher']) && $settings['custom_map_pin_switcher'] === 'yes' && !empty($settings['custom_map_pin_icon'])) {
            ob_start();
        ?>
            <span class="cwp-map-pin-icon">
                <?php
                if (class_exists('Elementor\Icons_Manager')) {
                    \Elementor\Icons_Manager::render_icon($settings['custom_map_pin_icon'], ['aria-hidden' => 'true']);
                }
                ?>
            </span>
        <?php
            $map_pin_html = trim(ob_get_clean());
        }
        ?>
        <div class="cwp-archive-map-wrapper map-display-<?php echo esc_attr($display_type); ?> <?php echo $popup_enabled ? 'map-hidden' : ''; ?>">

            <?php if ($popup_enabled) : ?>
                <button type="button" class="cwp-map-toggle-btn">
                    <?php Icons_Manager::render_icon($settings['popup_button_icon'], ['aria-hidden' => 'true']); ?>
                    <span><?php echo esc_html($settings['popup_button_text']); ?></span>
                </button>
            <?php endif; ?>

            <div class="cwp-archive-content-map-main">
                <?php
                if ($popup_enabled && $display_type === 'popup') : ?>
                    <div class="cwp-map-close-btn">
                        <?php Icons_Manager::render_icon($settings['close_button_icon'], ['aria-hidden' => 'true']); ?>
                    </div>
                <?php endif; ?>
                <div class="cwp-archive-content-map-inner">

                    <div class="cwp-archive-content-map" <?php if (!empty($map_pin_html)) echo 'data-mappin-html="' . esc_attr($map_pin_html) . '"'; ?>>

                    </div>
                </div>
            </div>

        </div>

        <script>
            (function($) {
                const initMap = () => {
                    if (typeof CWP_Cluster_Map === 'function') {
                        CWP_Cluster_Map();
                    }
                };
                initMap();
                $(document).ready(function() {
                    initMap();

                    $('.cwp-map-toggle-btn').off('click.cwpMapToggle').on('click.cwpMapToggle', function() {
                        const wrapper = $(this).closest('.cwp-archive-map-wrapper');

                        if (wrapper.hasClass('map-display-popup')) {
                            wrapper.addClass('map-active');
                            $('body').addClass('cwp-map-open');
                        } else {
                            wrapper.toggleClass('map-hidden');
                        }

                        // Rebuild map with latest AJAX coordinates after becoming visible.
                        if (typeof window.CWP_Refresh_Cluster_Map === 'function') {
                            window.CWP_Refresh_Cluster_Map();
                        }

                        // Force refresh Leaflet viewport size
                        setTimeout(() => {
                            window.dispatchEvent(new Event('resize'));
                        }, 200);
                    });

                    $('.cwp-map-close-btn').off('click.cwpMapClose').on('click.cwpMapClose', function() {
                        const wrapper = $(this).closest('.cwp-archive-map-wrapper');
                        wrapper.removeClass('map-active');
                        $('body').removeClass('cwp-map-open');
                    }); 
                 
                });
            })(jQuery);
        </script>
<?php
    }
}
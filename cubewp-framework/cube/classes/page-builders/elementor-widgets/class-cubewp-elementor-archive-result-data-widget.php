<?php
defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;

class CubeWp_Elementor_Archive_Result_Data_Widget extends Widget_Base {

    public function get_name() {
        return 'result_data_widget';
    }

    public function get_title() {
        return __( 'Result Data Display', 'cubewp-framework' );
    }

    public function get_icon() {
        return 'eicon-number-field';
    }

    public function get_categories() {
        return [ 'cubewp' ];
    }

    protected function _register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'Content', 'cubewp-framework' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'default_text',
            [
                'label' => __( 'Default Text', 'cubewp-framework' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Results Found', 'cubewp-framework' ),
                'placeholder' => __( 'Results Found', 'cubewp-framework' ),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __( 'Style', 'cubewp-framework' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Text Color Control
        $this->add_control(
            'text_color',
            [
                'label' => __( 'Text Color', 'cubewp-framework' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwp-total-results h5' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Typography Control
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .cwp-total-results h5',
            ]
        );

        // Text Shadow
        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .cwp-total-results h5',
            ]
        );

        $this->end_controls_section();

        // Container Style Section
        $this->start_controls_section(
            'container_style',
            [
                'label' => __( 'Container', 'cubewp-framework' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        ); 

        // Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .cwp-filtered-result-count',
                'separator' => 'before',
            ]
        );

        // Border Radius
        $this->add_control(
            'container_border_radius',
            [
                'label' => __( 'Border Radius', 'cubewp-framework' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-result-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Padding Control
        $this->add_control(
            'container_padding',
            [
                'label' => __( 'Padding', 'cubewp-framework' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-result-count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Margin Control
        $this->add_control(
            'container_margin',
            [
                'label' => __( 'Margin', 'cubewp-framework' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .cwp-filtered-result-count' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'label' => __( 'Box Shadow', 'cubewp-framework' ),
                'selector' => '{{WRAPPER}} .cwp-filtered-result-count',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $default_text = $settings['default_text'] ? $settings['default_text'] : __( 'Results Found', 'cubewp-framework' );
        
        $output = '<div class="cwp-filtered-result-count">';
        $data =  esc_html__( '0 ', 'cubewp-framework' ) . $default_text;
        $output .= '<div class="cwp-total-results">'. $data .'</div>';
        $output .= '</div>';
        
        echo $output;
    }
}
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

        $popup_enabled = ($settings['enable_map_popup'] === 'yes');
        $display_type  = $settings['map_display_type'];
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

                    <div class="cwp-archive-content-map">

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

                $(document).ready(function() {
                    initMap();

                    $('.cwp-map-toggle-btn').on('click', function() {
                        const wrapper = $(this).closest('.cwp-archive-map-wrapper');

                        if (wrapper.hasClass('map-display-popup')) {
                            wrapper.addClass('map-active');
                            $('body').addClass('cwp-map-open');
                        } else {
                            wrapper.toggleClass('map-hidden');
                        }

                        // Force refresh Leaflet
                        setTimeout(() => {
                            window.dispatchEvent(new Event('resize'));
                        }, 200);
                    });

                    $('.cwp-map-close-btn').on('click', function() {
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
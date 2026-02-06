<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;

class CubeWp_Elementor_Archive_Posts_List_Grid_Widget extends Widget_Base {

    public function get_name() { return 'archive_posts_list_grid_widget'; }
    public function get_title() { return __('Archive Posts List/Grid', 'cubewp-framework'); }
    public function get_icon() { return 'eicon-view-list'; }
    public function get_categories() { return ['cubewp']; }
    public function get_script_depends() { return ['cubewp-view-toggle']; }

    protected function register_controls() {
        
        /* =========================
         * CONTENT SECTION
         * ========================= */
        $this->start_controls_section(
            'section_view_toggle',
            ['label' => __('View Toggle Buttons', 'cubewp-framework')]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'button_text',
            [
                'label'   => __('Text', 'cubewp-framework'),
                'type'    => Controls_Manager::TEXT,
                'default' => __('Grid', 'cubewp-framework'),
            ]
        );

        $repeater->add_control(
            'button_icon',
            [
                'label' => __('Icon', 'cubewp-framework'),
                'type'  => Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-th-large',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $repeater->add_control(
            'icon_align',
            [
                'label' => __('Icon Position', 'cubewp-framework'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Before', 'cubewp-framework'),
                    'right' => __('After', 'cubewp-framework'),
                ],
            ]
        );

        $repeater->add_control(
            'trigger_type',
            [
                'label'   => __('Trigger View', 'cubewp-framework'),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'grid' => __('Grid View', 'cubewp-framework'),
                    'list' => __('List View', 'cubewp-framework'), 
                ],
                'default' => 'grid',
            ]
        );
       
        

        $this->add_control(
            'view_buttons',
            [
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'button_text' => __('Grid', 'cubewp-framework'), 
                        'trigger_type' => 'grid',
                        'button_icon' => ['value' => 'fas fa-th-large', 'library' => 'fa-solid']
                    ],
                    [
                        'button_text' => __('List', 'cubewp-framework'), 
                        'trigger_type' => 'list',
                        'button_icon' => ['value' => 'fas fa-list', 'library' => 'fa-solid']
                    ],
                ],
                'title_field' => '{{{ button_text }}}',
            ]
        );

        $this->end_controls_section();

        /* =========================
         * STYLE SECTION: BUTTONS
         * ========================= */
        $this->start_controls_section(
            'section_view_style',
            [
                'label' => __('View Buttons Style', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'btns_alignment',
            [
                'label' => __('Alignment', 'cubewp-framework'),
                'type' => Controls_Manager::CHOOSE,
                'default' => 'flex-start',
                'options' => [
                    'flex-start' => ['title' => __('Left', 'cubewp-framework'), 'icon' => 'eicon-text-align-left'],
                    'center'     => ['title' => __('Center', 'cubewp-framework'), 'icon' => 'eicon-text-align-center'],
                    'flex-end'   => ['title' => __('Right', 'cubewp-framework'), 'icon' => 'eicon-text-align-right'],
                ],
                'selectors' => ['{{WRAPPER}} .cubewp-view-switcher' => 'display: flex; justify-content: {{VALUE}};'],
            ]
        );

        $this->add_responsive_control(
            'btns_gap',
            [
                'label' => __('Spacing Between Buttons', 'cubewp-framework'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => ['px' => ['min' => 0, 'max' => 50]],
                'default' => ['size' => 10, 'unit' => 'px'],
                'selectors' => ['{{WRAPPER}} .cubewp-view-switcher' => 'gap: {{SIZE}}{{UNIT}};'],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'btn_typography',
                'selector' => '{{WRAPPER}} .cubewp-view-btn',
                'fields_options' => [
                    'font_size' => ['default' => ['size' => 14, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                ],
            ]
        );

        $this->start_controls_tabs('view_btn_tabs');

        // --- NORMAL TAB ---
        $this->start_controls_tab('view_btn_normal', ['label' => __('Normal', 'cubewp-framework')]);

        $this->add_control(
            'btn_color',
            [
                'label'     => __('Text Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#555555',
                'selectors' => ['{{WRAPPER}} .cubewp-view-btn' => 'color: {{VALUE}}'],
            ]
        );

        $this->add_control(
            'btn_bg',
            [
                'label'     => __('Background Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#f1f1f1',
                'selectors' => ['{{WRAPPER}} .cubewp-view-btn' => 'background-color: {{VALUE}}'],
            ]
        );
        // --- Common Button Properties ---
        $this->add_responsive_control(
            'btn_padding',
            [
                'label'      => __('Padding', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default'    => [
                    'top' => '10', 'right' => '20', 'bottom' => '10', 'left' => '20', 'unit' => 'px', 'isLinked' => false
                ],
                'selectors'  => ['{{WRAPPER}} .cubewp-view-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
                'separator'  => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'btn_border',
                'selector' => '{{WRAPPER}} .cubewp-view-btn',
                'fields_options' => [
                    'border' => ['default' => 'solid'],
                    'width' => ['default' => ['top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'unit' => 'px']],
                    'color' => ['default' => '#dddddd'],
                ],
            ]
        );

        $this->add_control(
            'btn_radius',
            [
                'label'      => __('Border Radius', 'cubewp-framework'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default'    => ['top' => '4', 'right' => '4', 'bottom' => '4', 'left' => '4', 'unit' => 'px'],
                'selectors'  => ['{{WRAPPER}} .cubewp-view-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'btn_shadow',
                'selector' => '{{WRAPPER}} .cubewp-view-btn',
            ]
        );

        $this->end_controls_tab();

        // --- ACTIVE/HOVER TAB ---
        $this->start_controls_tab('view_btn_active', ['label' => __('Active/Hover', 'cubewp-framework')]);

        $this->add_control(
            'btn_active_color',
            [
                'label'     => __('Text Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-view-btn.active, {{WRAPPER}} .cubewp-view-btn:hover' => 'color: {{VALUE}}'
                ],
            ]
        );

        $this->add_control(
            'btn_active_bg',
            [
                'label'     => __('Background Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-view-btn.active, {{WRAPPER}} .cubewp-view-btn:hover' => 'background-color: {{VALUE}}'
                ],
            ]
        );
        $this->add_control(
            'btn_active_color_border_color',
            [
                'label'     => __('Border Color', 'cubewp-framework'),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cubewp-view-btn.active, {{WRAPPER}} .cubewp-view-btn:hover' => 'border-color: {{VALUE}}'
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        

        $this->end_controls_section();

        /* =========================
         * STYLE SECTION: ICONS
         * ========================= */
        $this->start_controls_section(
            'section_icon_style',
            [
                'label' => __('Icon Styling', 'cubewp-framework'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'default' => '#555555',
                'selectors' => ['{{WRAPPER}} .cubewp-view-btn i,{{WRAPPER}} .cubewp-view-btn svg' => 'fill: {{VALUE}};stroke: {{VALUE}};color: {{VALUE}};'],
            ]
        );
        $this->add_responsive_control(
            'active_icon_color',
            [
                'label' => __('Active Icon Color', 'cubewp-framework'),
                'type'  => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => ['{{WRAPPER}} .cubewp-view-btn.active i,{{WRAPPER}} .cubewp-view-btn.active svg' => 'fill: {{VALUE}};stroke: {{VALUE}};color: {{VALUE}};'],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'cubewp-framework'),
                'type'  => Controls_Manager::SLIDER,
                'default' => ['size' => 16, 'unit' => 'px'],
                'selectors' => ['{{WRAPPER}} .cubewp-view-btn i,{{WRAPPER}} .cubewp-view-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};'],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => __('Icon Spacing', 'cubewp-framework'),
                'type'  => Controls_Manager::SLIDER,
                'default' => ['size' => 8, 'unit' => 'px'],
                'selectors' => [
                    '{{WRAPPER}} .icon-left i,{{WRAPPER}} .icon-left svg' => 'margin-right: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .icon-right i,{{WRAPPER}} .icon-right svg' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (empty($settings['view_buttons'])) {
            return;
        }
        ?>
        <div class="cubewp-view-switcher" data-default="grid">
            <?php foreach ($settings['view_buttons'] as $index => $button) :
                $active = ($index === 0) ? 'active' : '';
                $icon_pos = !empty($button['icon_align']) ? 'icon-' . $button['icon_align'] : 'icon-left';
                 ?>
                <button
                    type="button"
                    class="cubewp-view-btn <?php echo esc_attr($active . ' ' . $icon_pos); ?>"
                    data-view="<?php echo esc_attr($button['trigger_type']); ?>"
                >
                    <?php if ($button['icon_align'] === 'left' && !empty($button['button_icon']['value'])) : ?>
                        <?php Icons_Manager::render_icon($button['button_icon'], ['aria-hidden' => 'true']); ?>
                    <?php endif; ?>

                    <?php if (!empty($button['button_text'])) : ?>
                        <span class="cubewp-btn-text"><?php echo esc_html($button['button_text']); ?></span>
                    <?php endif; ?>

                    <?php if ($button['icon_align'] === 'right' && !empty($button['button_icon']['value'])) : ?>
                        <?php Icons_Manager::render_icon($button['button_icon'], ['aria-hidden' => 'true']); ?>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <style>
            .cubewp-view-switcher { display: flex; flex-wrap: wrap; align-items: center; }
            .cubewp-view-btn { 
                display: inline-flex; 
                align-items: center; 
                justify-content: center; 
                cursor: pointer; 
                transition: all 0.3s;
                border-style: solid; 
                line-height: 1;
                outline: none;
            }
            .cubewp-view-btn i, .cubewp-view-btn svg {
                line-height: 1;
                width: auto;
                height: auto;
            }
        </style>
        <?php
    }
}
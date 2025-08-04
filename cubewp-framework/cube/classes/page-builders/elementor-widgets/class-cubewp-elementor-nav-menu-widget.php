<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

/**
 * CubeWP Nav Mneu Widgets.
 *
 * Elementor Widget For Nav Mneu By CubeWP.
 *
 * @since 1.0.0
 */
class CubeWp_Elementor_Nav_Menu_Widget extends Widget_Base
{

	protected $nav_menu_index = 1;

	public function get_name()
	{
		return 'cubewp_navmenu';
	}

	public function get_title()
	{
		return esc_html__('CubeWP Nav Menu', 'cubewp-framework');
	}

	public function get_icon()
	{
		return 'eicon-nav-menu';
	}

	public function get_categories()
	{
		return array('cubewp');
	}

	public function get_keywords()
	{
		return array(
			'banner',
			'featured',
			'click',
			'promotion',
			'promotional',
			'website',
			'search',
			'searches',
			'multi'
		);
	}

	private function get_wordpress_menu_options()
	{
		$menus = wp_get_nav_menus();
		$menu_options = [];

		foreach ($menus as $menu) {
			$menu_options[$menu->term_id] = $menu->name;
		}

		return $menu_options;
	}

	protected function register_controls()
	{
		$this->start_controls_section('cubewp_menu_setting_section', array(
			'label' => esc_html__('Menu Settings', 'cubewp-framework'),
			'tab'   => Controls_Manager::TAB_CONTENT,
		));
		$menu_options = $this->get_wordpress_menu_options();
		$this->add_control(
			'menu_id',
			[
				'label' => __('Select Menu', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $menu_options,
				'default' => array_key_first($menu_options),
			]
		);
		$this->add_control('layout_type', array(
			'type'        => Controls_Manager::SELECT,
			'multiple'    => false,
			'label'       => esc_html__('Layout Type', 'cubewp-framework'),
			'options'     => array(
				'horizontal' => esc_html__('Horizontal', 'cubewp-framework'),
				'vertical' => esc_html__('Vertical', 'cubewp-framework'),
				'dropdown' => esc_html__('Dropdown', 'cubewp-framework'),
			),
			'default'     => 'horizontal'
		));

		$this->add_control(
			'item_alignment',
			[
				'label' => esc_html__('Align Items', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'align-left' => [
						'title' => esc_html__('Align Left', 'cubewp-framework'),
						'icon' => 'eicon-text-align-left',
					],
					'align-center' => [
						'title' => esc_html__('Align Center', 'cubewp-framework'),
						'icon' => 'eicon-text-align-center',
					],
					'align-right' => [
						'title' => esc_html__('Align Right', 'cubewp-framework'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'prefix_class' => 'cubewp-nav-menu__align-',
				'condition' => [
					'layout_type!' => 'dropdown',
				],

			]
		);


		$icon_prefix = Icons_Manager::is_migration_allowed() ? 'fas ' : 'fa ';


		$this->add_control(
			'submenu_icon',
			[
				'label' => esc_html__('Submenu Indicator', 'cubewp-framework'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => $icon_prefix . 'fa-caret-down',
					'library' => 'fa-solid',
				],
				'recommended' => [
					'fa-solid' => [
						'chevron-down',
						'angle-down',
						'caret-down',
						'plus',
					],
				],
			]
		);


		$this->add_control(
			'off_canvas_logo',
			[
				'label' => esc_html__('Off-Canvas Logo', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'default' => [
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				],
				'label_block' => true,
				'separator' => 'before',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'hamburgur_icon',
			[
				'label' => esc_html__('Hamburgur Icon', 'cubewp-framework'),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
				'skin_settings' => [
					'inline' => [
						'none' => [
							'label' => esc_html__('Default', 'cubewp-framework'),
							'icon' => 'eicon-menu-bar',
						],
						'icon' => [
							'icon' => 'eicon-star',
						],
					],
				],
				'recommended' => [
					'fa-solid' => [
						'plus-square',
						'plus',
						'plus-circle',
						'bars',
					],
					'fa-regular' => [
						'plus-square',
					],
				],
				'condition' => [
					'layout_type' => 'dropdown',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_main-menu',
			[
				'label' => esc_html__('Main Menu', 'cubewp-framework'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type!' => 'dropdown',
				],

			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu .elementor-cubewp-item',
			]
		);

		$this->start_controls_tabs('tabs_menu_item_style');

		$this->start_controls_tab(
			'tab_menu_item_normal',
			[
				'label' => esc_html__('Normal', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'color_menu_item',
			[
				'label' => esc_html__('Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_menu_item_hover',
			[
				'label' => esc_html__('Hover', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'color_menu_item_hover',
			[
				'label' => esc_html__('Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item:hover,
					{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item.elementor-cubewp-item-active,
					{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item.highlighted,
					{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item:focus' => 'color: {{VALUE}}; fill: {{VALUE}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border_main_hover',
				'label' => esc_html__('Border', 'cubewp-framework'),
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item:hover',
				'separator' => 'before',
			]
		);
		$this->add_control(
			'dropdown_border_radius_main_hover',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item li:first-child a:hover' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item li:last-child a:hover' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();


		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border_main',
				'label' => esc_html__('Border', 'cubewp-framework'),
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'dropdown_border_radius_main',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item li:first-child a' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item li:last-child a' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				],
			]
		);


		/* This control is required to handle with complicated conditions */
		$this->add_control(
			'hr',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_responsive_control(
			'padding_horizontal_menu_item',
			[
				'label' => esc_html__('Horizontal Padding', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'padding_vertical_menu_item',
			[
				'label' => esc_html__('Vertical Padding', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 13,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-item' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'menu_space_between',
			[
				'label' => esc_html__('Column Gap', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--e-nav-menu-horizontal-menu-item-margin: calc( {{SIZE}}{{UNIT}} / 2 );',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-nav-menu' => 'column-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_column_gap',
			[
				'label' => esc_html__('Row Gap', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--e-nav-menu-horizontal-menu-item-margin: calc( {{SIZE}}{{UNIT}} / 2 );',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-nav-menu' => 'row-gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_dropdown',
			[
				'label' => esc_html__('Dropdown', 'cubewp-framework'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'dropdown_description',
			[
				'raw' => esc_html__('On desktop, this will affect the submenu. On mobile, this will affect the entire menu.', 'cubewp-framework'),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->start_controls_tabs('tabs_dropdown_item_style');

		$this->start_controls_tab(
			'tab_dropdown_item_normal',
			[
				'label' => esc_html__('Normal', 'cubewp-framework'),
			]
		);

		$this->add_responsive_control(
            'color_dropdown_item',
            [
                'label' => esc_html__('Text Color', 'cubewp-framework'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a, {{WRAPPER}} .elementor-cubewp-menu-toggle' => 'color: {{VALUE}}',
                ],
            ]
        );

		$this->add_responsive_control(
			'background_color_dropdown_item',
			[
				'label' => esc_html__('Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown' => 'background-color: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_dropdown_item_hover',
			[
				'label' => esc_html__('Hover', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'color_dropdown_item_hover',
			[
				'label' => esc_html__('Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '#fff',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a:hover, 
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.hover i,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active i,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active i,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.highlighted i, 

					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.hover fvg,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active fvg,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active fvg,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.highlighted ifvg 


					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.highlighted,
					{{WRAPPER}} .elementor-cubewp-menu-toggle:hover' => 'color: {{VALUE}};fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'background_color_dropdown_item_hover',
			[
				'label' => esc_html__('Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '#000',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a:hover,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.elementor-cubewp-item-active,
					{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a.highlighted' => 'background-color: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'dropdown_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'exclude' => ['line_height'],
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown .elementor-cubewp-item, {{WRAPPER}} .elementor-cubewp-nav-menu--dropdown  .elementor-sub-item , {{WRAPPER}} .elementor-cubewp-nav-menu--dropdown  .elementor-cubewp-sub-item',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border',
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown li:first-child a' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown li:last-child a' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'dropdown_box_shadow',
				'exclude' => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .elementor-cubewp-nav-menu--main .elementor-cubewp-nav-menu--dropdown, {{WRAPPER}} .elementor-cubewp-nav-menu__container.elementor-cubewp-nav-menu--dropdown',
			]
		);

		$this->add_responsive_control(
			'padding_horizontal_dropdown_item',
			[
				'label' => esc_html__('Horizontal Padding', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'before',

			]
		);

		$this->add_responsive_control(
			'padding_vertical_dropdown_item',
			[
				'label' => esc_html__('Vertical Padding', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 13,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--dropdown a' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);


		$this->add_responsive_control(
			'dropdown_top_distance',
			[
				'label' => esc_html__('Distance', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', 'custom'],
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu--main > .elementor-cubewp-nav-menu > li > .elementor-cubewp-nav-menu--dropdown, {{WRAPPER}} .elementor-cubewp-nav-menu__container.elementor-cubewp-nav-menu--dropdown' => 'margin-top: {{SIZE}}{{UNIT}} !important',
				],
				'separator' => 'before',
			]
		);


		$this->add_responsive_control(
			'toggle_color',
			[
				'label' => esc_html__('Hamburgur Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} div.elementor-cubewp-menu-toggle' => 'color: {{VALUE}}', // Harder selector to override text color control
					'{{WRAPPER}} div.elementor-cubewp-menu-toggle svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_background_color',
			[
				'label' => esc_html__('Hamburgur Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-menu-toggle' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'toggle_size',
			[
				'label' => esc_html__('Size', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 15,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-menu-toggle' => 'width: {{SIZE}}{{UNIT}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'toggle_border_radius',
			[
				'label' => esc_html__('Hamburgur Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-menu-toggle' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);


		$this->end_controls_section();

		$this->start_controls_section('icon_style_section', [
			'label' => esc_html__('Icon Style', 'cubewp-framework'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_control(
			'icon_size',
			[
				'label' => esc_html__('Icon Size', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', '%'],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a i,{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a svg' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};    height: auto; ',
				],
			]
		);

		$this->add_control(
			'icon_direction',
			[
				'label' => esc_html__('Icon Direction', 'cubewp-framework'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'row' => [
						'title' => esc_html__('Left', 'cubewp-framework'),
						'icon' => 'eicon-h-align-left',
					],
					'row-reverse' => [
						'title' => esc_html__('Right', 'cubewp-framework'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'row',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_space',
			[
				'label' => esc_html__('Icon Space', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'dropdown_border_radius_icon',
			[
				'label' => esc_html__('Icon Padding', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a i,{{WRAPPER}} .elementor-cubewp-nav-menu .menu-item-has-children>a svg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',

				],
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'section_style_offcanvas_menu',
			[
				'label' => esc_html__('Off-Canvas Menu Button', 'cubewp-framework'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_control(
			'offcanvas_menu_icon',
			[
				'label' => __('Off-Canvas Menu Icon', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-bars',
					'library' => 'solid',
				],
			]
		);
		
		$this->start_controls_tabs('tabs_offcanvas_style');
		
		// Normal Tab
		$this->start_controls_tab(
			'tab_offcanvas_normal',
			[
				'label' => esc_html__('Normal', 'cubewp-framework'),
			]
		);
		
		// Icon Color
		$this->add_control(
			'offcanvas_menu_icon_color',
			[
				'label' => esc_html__('Icon Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open svg' => 'fill: {{VALUE}};',
				],
			]
		); 
			// Hover Background Color
			$this->add_control(
				'offcanvas_menu_bg_color',
				[
					'label' => esc_html__('Background Color', 'cubewp-framework'),
					'type' => \Elementor\Controls_Manager::COLOR,
					'default' => 'rgba(255, 0, 0, 0.1)',
					'selectors' => [
						'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open' => 'background-color: {{VALUE}} !important;',
					],
				]
			);
			
		
		// Border
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'offcanvas_menu_border',
				'label' => esc_html__('Border', 'cubewp-framework'),
				'selector' => '{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open',
			]
		);
		
		// Border Radius
		$this->add_responsive_control(
			'offcanvas_menu_border_radius',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		// Padding
		$this->add_responsive_control(
			'offcanvas_menu_padding',
			[
				'label' => esc_html__('Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		
		// Icon Size
		$this->add_responsive_control(
			'offcanvas_menu_icon_size',
			[
				'label' => esc_html__('Icon Size', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open svg' => 'width: {{SIZE}}{{UNIT}};height: auto;',
				],
			]
		);
		
		$this->end_controls_tab(); // End Normal Tab
		
		// Hover Tab
		$this->start_controls_tab(
			'tab_offcanvas_hover',
			[
				'label' => esc_html__('Hover', 'cubewp-framework'),
			]
		);
		
		// Hover Icon Color
		$this->add_control(
			'offcanvas_menu_icon_hover_color',
			[
				'label' => esc_html__('Hover Icon Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#FF0000',
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open:hover i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);
		
		// Hover Background Color
		$this->add_control(
			'offcanvas_menu_hover_bg_color',
			[
				'label' => esc_html__('Hover Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => 'rgba(255, 0, 0, 0.1)',
				'selectors' => [
					'{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);
		
		// Hover Border
		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'offcanvas_menu_border_hover',
				'label' => esc_html__('Hover Border', 'cubewp-framework'),
				'selector' => '{{WRAPPER}} .cubwp-menu-desktop.mobile.cubewp-cubewp-menus-open:hover',
			]
		);
		
		$this->end_controls_tab(); // End Hover Tab
		
		$this->end_controls_tabs(); // End Tabs
		
		$this->end_controls_section();
		
	
	
	}


	protected function render()
	{
		$menu_options = $this->get_wordpress_menu_options();
		if (!$menu_options) {
			return;
		}

		$settings = $this->get_active_settings();

		$args = [
			'echo' => false,
			'menu' => $settings['menu_id'],
			'menu_class' => 'elementor-cubewp-nav-menu',
			'menu_id' => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
			'fallback_cb' => '__return_empty_string',
			'container' => '',
		];

		if ('vertical' === $settings['layout_type']) {
			$args['menu_class'] .= ' sm-vertical';
		}

		// Add custom filter to handle Nav Menu HTML output.
		add_filter('nav_menu_link_attributes', [$this, 'handle_link_classes'], 10, 4);
		add_filter('nav_menu_link_attributes', [$this, 'handle_link_tabindex'], 10, 4);
		add_filter('nav_menu_submenu_css_class', [$this, 'handle_sub_menu_classes']);
		add_filter('nav_menu_item_id', '__return_empty_string');

		// General Menu.
		$menu_html = wp_nav_menu($args);

		// Dropdown Menu.
		$args['menu_id'] = 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id();
		$args['menu_type'] = 'dropdown';
		$dropdown_menu_html = wp_nav_menu($args);

		// Remove all our custom filters.
		remove_filter('nav_menu_link_attributes', [$this, 'handle_link_classes']);
		remove_filter('nav_menu_link_attributes', [$this, 'handle_link_tabindex']);
		remove_filter('nav_menu_submenu_css_class', [$this, 'handle_sub_menu_classes']);
		remove_filter('nav_menu_item_id', '__return_empty_string');

		if (empty($menu_html)) {
			return;
		}
		if ('dropdown' !== $settings['layout_type']) :
			$this->add_render_attribute('main-menu', 'class', [
				'elementor-cubewp-nav-menu--main',
				'elementor-cubewp-nav-menu__container cubwp-menu-desktop',
				'elementor-cubewp-nav-menu--layout-' . $settings['layout_type'],
			]); 
?>

			<nav <?php $this->print_render_attribute_string('main-menu'); ?> data-icons='<?php echo Icons_Manager::render_icon($settings['submenu_icon']); ?>'>
				<?php

				// PHPCS - escaped by WordPress with "wp_nav_menu"
				echo $menu_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>

			</nav>
			<button class="cubwp-menu-desktop mobile cubewp-cubewp-menus-open" type="button" data-bs-toggle="offcanvas" data-bs-target="#cubewp-menus" aria-controls="cubewp-menus">
				 <?php 
				 if (!empty($settings['offcanvas_menu_icon']['value'])) {
					Icons_Manager::render_icon($settings['offcanvas_menu_icon'], ['aria-hidden' => 'true']);
				}
				 ?>
			</button>

		<?php
		endif;
		if ('dropdown'  == $settings['layout_type']) :
			$this->render_menu_toggle($settings);
		?>
			<nav class="elementor-cubewp-nav-menu--dropdown  elementor-cubewp-nav-menu__container" aria-hidden="true" data-icons='<?php echo Icons_Manager::render_icon($settings['submenu_icon']); ?>'>
				<?php
				echo $menu_html;
				?>
			</nav>
		<?php
		endif;
		$off_canvas_logo = 	$settings['off_canvas_logo'];
		?>
		<div class="cubewp-offcanvas-menus">
			<div class="offcanvas-header">
				<a href="<?php echo  site_url();  ?>"> <img src="<?php echo esc_url($off_canvas_logo['url']);  ?>" alt="logo"></a>
				<button class="cubewp-menu-closed"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
						<path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
					</svg></button>
			</div>
			<div class="offcanvas-body">
				<nav class="menu-offcanvas elementor-cubewp-nav-menu__container" aria-hidden="true">
					<?php
					echo $menu_html;
					?>
				</nav>
			</div>
		</div>
	<?php
	}

	protected function get_nav_menu_index()
	{
		return $this->nav_menu_index++;
	}

	public function handle_link_classes($atts, $item, $args, $depth)
	{
		$classes = $depth ? 'elementor-cubewp-sub-item' : 'elementor-cubewp-item';
		$is_anchor = false !== strpos($atts['href'], '#');

		if (!$is_anchor && in_array('current-menu-item', $item->classes)) {
			$classes .= ' elementor-cubewp-item-active';
		}

		if ($is_anchor) {
			$classes .= ' elementor-cubewp-item-anchor';
		}

		if (empty($atts['class'])) {
			$atts['class'] = $classes;
		} else {
			$atts['class'] .= ' ' . $classes;
		}

		return $atts;
	}

	public function handle_link_tabindex($atts, $item, $args)
	{
		$settings = $this->get_active_settings();

		// Add `tabindex = -1` to the links if it's a dropdown, for A11y.
		$is_dropdown = 'dropdown' === $settings['layout_type'];
		$is_dropdown = $is_dropdown || (isset($args->menu_type) && 'dropdown' === $args->menu_type);

		if ($is_dropdown) {
			$atts['tabindex'] = '-1';
		}

		return $atts;
	}

	public function handle_sub_menu_classes($classes)
	{
		$classes[] = ' elementor-cubewp-nav-menu--dropdown';

		return $classes;
	}

	private function render_menu_toggle($settings)
	{
		if ('dropdown' !== $settings['layout_type']) {
			return;
		}

		$this->add_render_attribute('menu-toggle', [
			'class' => 'elementor-cubewp-menu-toggle  cubwp-menu-desktop',
			'role' => 'button',
			'tabindex' => '0',
			'aria-label' => esc_html__('Menu Toggle', 'cubewp-framework'),
			'aria-expanded' => 'false',
		]);

	?>
		<button class="cubwp-menu-desktop mobile cubewp-cubewp-menus-open" data-target="cubewp-menus"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
				<path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
			</svg>
		</button>
		<div <?php $this->print_render_attribute_string('menu-toggle'); ?>>
			<?php

			$open_class = 'elementor-cubewp-menu-toggle__icon--open active';
			$close_class = 'elementor-cubewp-menu-toggle__icon--close';

			$normal_icon = !empty($settings['hamburgur_icon']['value'])
				? $settings['hamburgur_icon']
				: [
					'library' => 'eicons',
					'value' => 'eicon-menu-bar',
				];

			$is_normal_icon_svg = 'svg' === $normal_icon['library'];

			if ($is_normal_icon_svg) {
				echo '<span class="' . esc_attr($open_class) . '">';
			}

			Icons_Manager::render_icon(
				$normal_icon,
				[
					'aria-hidden' => 'true',
					'role' => 'presentation',
					'class' => $open_class,
				]
			);

			if ($is_normal_icon_svg) {
				echo '</span>';
			}

			$active_icon = !empty($settings['toggle_icon_active']['value'])
				? $settings['toggle_icon_active']
				: [
					'library' => 'eicons',
					'value' => 'eicon-close',
				];

			$is_active_icon_svg = 'svg' === $active_icon['library'];

			if ($is_active_icon_svg) {
				echo '<span class="' . esc_attr($close_class) . '">';
			}

			Icons_Manager::render_icon(
				$active_icon,
				[
					'aria-hidden' => 'true',
					'role' => 'presentation',
					'class' => $close_class,
				]
			);

			if ($is_active_icon_svg) {
				echo '</span>';
			}
			?>
		</div>
<?php
	}

	public function render_plain_content() {}
}
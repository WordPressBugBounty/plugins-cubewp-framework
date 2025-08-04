<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;


/**
 * CubeWP Mega Menu Widget
 *
 * Elementor Widget For Mega Menu By CubeWP
 *
 * @since 1.1.25
 */
class CubeWp_Elementor_Mega_Menu_Widget extends Widget_Base
{

	protected $mega_nav_menu_index = 1;

	public function get_name()
	{
		return 'cubewp_mega_menu';
	}

	public function get_title()
	{
		return esc_html__('CubeWP Mega Menu', 'cubewp-framework');
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
		$cubewp_megaID = CubeWp_Theme_Builder::cwp_elementor_builder_options('mega-menu');
		$menu_options = $this->get_wordpress_menu_options();

		$this->start_controls_section('cubewp_menu_setting_section', array(
			'label' => esc_html__('Menu Settings', 'cubewp-framework'),
			'tab'   => Controls_Manager::TAB_CONTENT,
		));

		$repeater = new Repeater();

		$repeater->add_control(
			'menu_name',
			[
				'label' => esc_html__('Menu Name', 'cubewp-framework'),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__('Menu Name', 'cubewp-framework'),
				'label_block' => true,
			]
		);

		$repeater->add_control('menu_visibility', array(
			'label' => esc_html__('Menu Visibility', 'cubewp-framework'),
			'type' => Controls_Manager::SELECT,
			'options' => array(
				'mega_menu' => 'Mega Menu',
				'nav_menu'  => 'Nav Menu',
				'custom_link'  => 'Custom Link',
			),
			'default' => 'mega_menu',
		));


		$repeater->add_control(
			'select_mega_menu',
			[
				'label' => esc_html__('Select Mega Menu', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => $cubewp_megaID,
				'condition' => [
					'menu_visibility' => 'mega_menu'
				]
			]
		);

		$repeater->add_control(
			'select_nav_menu',
			[
				'label' => esc_html__('Select Nav Menu', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => $menu_options,
				'condition' => [
					'menu_visibility' => 'nav_menu'
				]
			]
		);

		$repeater->add_control(
			'custom_link_url',
			[
				'label' => esc_html__('Custom Link', 'cubewp-framework'),
				'type' => Controls_Manager::URL,
				'default' => [
					'url' => '#',
					'is_external' => false,
					'nofollow' => false,
				],
				'show_external' => true,
				'label_block' => true,
				'dynamic' => ['active' => true],
				'condition' => [
					'menu_visibility' => 'custom_link'
				]
			]
		);

		$repeater->add_control(
			'menu_icon',
			[
				'label' => esc_html__('Icon', 'cubewp-framework'),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],

			]
		);

		$this->add_control(
			'menu_items',
			[
				'label' => esc_html__('Menu Items', 'cubewp-framework'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [],
				'title_field' => '{{{ menu_name }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section('menu_style_section', [
			'label' => esc_html__('Menu Style', 'cubewp-framework'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'menu_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item',
			]
		);

		$this->add_control(
			'flex_direction',
			[
				'label' => esc_html__('Flex Direction', 'cubewp-framework'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'row',
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
				'selectors' => [
					'{{WRAPPER}} .cubewp-menu-terms-parent' => 'flex-direction: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'justify_content',
			[
				'label' => esc_html__('Justify Content', 'cubewp-framework'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'flex-start',
				'options' => [
					'flex-start' => [
						'title' => esc_html__('Start', 'cubewp-framework'),
						'icon' => 'eicon-justify-start-h',
					],
					'flex-end' => [
						'title' => esc_html__('End', 'cubewp-framework'),
						'icon' => 'eicon-justify-end-h',
					],
					'center' => [
						'title' => esc_html__('Center', 'cubewp-framework'),
						'icon' => 'eicon-justify-center-h',
					],
					'space-between' => [
						'title' => esc_html__('Space Between', 'cubewp-framework'),
						'icon' => 'eicon-justify-space-between-h',
					],
					'space-around' => [
						'title' => esc_html__('Space Around', 'cubewp-framework'),
						'icon' => 'eicon-justify-space-around-h',
					],
					'space-evenly' => [
						'title' => esc_html__('Space Evenly', 'cubewp-framework'),
						'icon' => 'eicon-justify-space-evenly-h',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-menu-terms-parent' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'align_items',
			[
				'label' => esc_html__('Align Items', 'cubewp-framework'),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'stretch',
				'options' => [
					'stretch' => [
						'title' => esc_html__('Stretch', 'cubewp-framework'),
						'icon' => 'eicon-v-align-stretch',
					],
					'flex-start' => [
						'title' => esc_html__('Start', 'cubewp-framework'),
						'icon' => 'eicon-v-align-top',
					],
					'flex-end' => [
						'title' => esc_html__('End', 'cubewp-framework'),
						'icon' => 'eicon-v-align-bottom',
					],
					'center' => [
						'title' => esc_html__('Center', 'cubewp-framework'),
						'icon' => 'eicon-v-align-middle',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-menu-terms-parent' => 'align-items: {{VALUE}};',
				],
			]
		);


		$this->start_controls_tabs('tabs_menu_item_style');

		// Start "Normal" tab
		$this->start_controls_tab(
			'tab_menu_item_normal',
			[
				'label' => esc_html__('Normal', 'cubewp-framework'),
			]
		);
		$this->add_control(
			'color_menu_item_normal_bg',
			[
				'label' => esc_html__('Text Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'background-color: {{VALUE}};',
				]
			]
		);
		$this->add_control(
			'color_menu_item_normal',
			[
				'label' => esc_html__('Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item path' => 'color: {{VALUE}}; fill: {{VALUE}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border_main',
				'selector' => '{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius_main',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:first-child' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:last-child' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_item_padding',
			[
				'label' => esc_html__('Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'menu_item_margin',
			[
				'label' => esc_html__('Margin', 'value-pack'),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .cubewp-menu-terms-parent ' => 'column-gap: {{SIZE}}{{UNIT}}',
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
					'{{WRAPPER}} .cubewp-menu-terms-parent ' => 'row-gap: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_tab();

		// Start "Hover" tab
		$this->start_controls_tab(
			'tab_menu_item_hover',
			[
				'label' => esc_html__('Hover', 'cubewp-framework'),
			]
		);
		$this->add_control(
			'color_menu_item_normal_bg_hover',
			[
				'label' => esc_html__('Text Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover' => 'background-color: {{VALUE}};',
				]
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
					'
					{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover,
					{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover svg,
					{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover svg path,
						{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item.elementor-cubewp-item-active,
						{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item.active,
						{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item.highlighted,
						{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:focus' => 'color: {{VALUE}}; fill: {{VALUE}};',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border_hover',
				'selector' => '{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover,{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item.active',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius_hover',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem', 'custom'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover:first-child' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item:hover:last-child' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_hover_color',
			[
				'label' => esc_html__('Border Bottom Hover Effect Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#00000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item::after' => 'background: {{VALUE}};',
				],

			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();


		$this->start_controls_section('icon_style_section', [
			'label' => esc_html__('Icon Style', 'cubewp-framework'),
			'tab' => Controls_Manager::TAB_STYLE,
		]);

		$this->add_control(
			'icon_spaces',
			[
				'label' => esc_html__('Icon Size', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item i' => 'font-size: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item svg' => 'width: {{SIZE}}{{UNIT}} !important; height: auto !important;',
				],
			]
		);

		$this->add_control(
			'icon_direction',
			[
				'label' => esc_html__('Icon Direction', 'cubewp-framework'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'0' => [
						'title' => esc_html__('Left', 'cubewp-framework'),
						'icon' => 'eicon-h-align-left',
					],
					'1' => [
						'title' => esc_html__('Right', 'cubewp-framework'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => '0',
				'selectors' => [
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item span' => 'order: {{VALUE}};',
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
					'{{WRAPPER}} .cubewp-mega-menu .cubewp-mega-menu-item' => 'gap: {{SIZE}}{{UNIT}};',
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

		$this->add_responsive_control(
			'background_color_dropdown',
			[
				'label' => esc_html__('Dropdown Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu' => 'background-color: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'dropdown_padding',
			[
				'label' => esc_html__('Dropdown Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_margin',
			[
				'label' => esc_html__('Dropdown Margin', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'dropdown_border',
				'selector' => '{{WRAPPER}} .elementor-cubewp-mega-nav-menu,{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_border_radius',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em', 'rem'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
				'selector' => '{{WRAPPER}} .elementor-cubewp-mega-nav-menu,{{WRAPPER}} .elementor-cubewp-mega-nav-menu .menu-item-has-children .sub-menu',
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
				'label' => esc_html__('Item Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '#1d1d1d',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a,
					{{WRAPPER}} .elementor-cubewp-menu-toggle' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'background_color_dropdown_item',
			[
				'label' => esc_html__('Item Background Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a,' => 'background-color: {{VALUE}}',
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

		// new issue fixes
		$this->add_control(
			'color_dropdown_item_hover',
			[
				'label' => esc_html__('Text Color', 'cubewp-framework'),
				'type' => Controls_Manager::COLOR,
				'default' => '#fff',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a:hover, 
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu a:hover i,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu a:hover svg,
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
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a:hover,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu a.elementor-cubewp-item-active,
					{{WRAPPER}} .elementor-cubewp-mega-nav-menu a.highlighted' => 'background-color: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->add_control(
			'item_border_hover_color',
			[
				'label' => esc_html__('Border Bottom Hover Effect Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#00000000',
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a::after' => 'background: {{VALUE}};',
				],

			]
		);

		$this->end_controls_tab();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'dropdown_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .elementor-cubewp-mega-nav-menu a',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'dropdown_item_padding',
			[
				'label' => esc_html__('Item Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'dropdown_item_margin',
			[
				'label' => esc_html__('Item Margin', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-cubewp-mega-nav-menu a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();

		echo '<div class="cubewp-mega-menu cubewp-menu-terms-parent">';

		if (!empty($settings['menu_items']) && is_array($settings['menu_items'])) {
			foreach ($settings['menu_items'] as $index => $item) {
				$random_id = rand(100000, 999999);
				$menu_visibility = isset($item['menu_visibility']) ? $item['menu_visibility'] : 'mega_menu';

				// Mega Menu
				if ($menu_visibility == 'mega_menu') {
					echo '<div class="cubewp-mega-menu-item hover" data-showID="' . esc_attr($random_id) . '">';
					echo '<span>' . esc_html($item['menu_name']) . '</span>';

					if (!empty($item['menu_icon']) && is_array($item['menu_icon']) && !empty($item['menu_icon']['value'])) {
						Icons_Manager::render_icon($item['menu_icon'], ['aria-hidden' => 'true']);
					}

					if (!empty($item['select_mega_menu'])) {
						echo '<div class="cubewp-mega-menu-item-dropdown" id="' . esc_attr($random_id) . '">';
						$this->cubewp_mega_menu_template($item['select_mega_menu']);
						echo '</div>';
					}

					echo '</div>';
				}
				// Custom Link
				elseif ($menu_visibility == 'custom_link') {
					$custom_link = !empty($item['custom_link_url']['url']) ? esc_url($item['custom_link_url']['url']) : '#';
					$custom_target = !empty($item['custom_link_url']['is_external']) ? ' target="_blank"' : '';
					$custom_nofollow = !empty($item['custom_link_url']['nofollow']) ? ' rel="nofollow"' : '';

					echo '<a class="cubewp-mega-menu-item hover" href="' . $custom_link . '"' . $custom_target . $custom_nofollow . '>';
					echo '<span>' . esc_html($item['menu_name']) . '</span>';

					if (!empty($item['menu_icon']) && is_array($item['menu_icon']) && !empty($item['menu_icon']['value'])) {
						Icons_Manager::render_icon($item['menu_icon'], ['aria-hidden' => 'true']);
					}

					echo '</a>';
				}
				// Navigation Menu
				elseif ($menu_visibility == 'nav_menu') {
					echo '<div class="cubewp-mega-menu-item hover menu-item-has-children menu-item-' . esc_attr($random_id) . '" data-showID="' . esc_attr($random_id) . '">';
					echo '<span>' . esc_html($item['menu_name']) . '</span>';

					if (!empty($item['menu_icon']) && is_array($item['menu_icon']) && !empty($item['menu_icon']['value'])) {
						Icons_Manager::render_icon($item['menu_icon'], ['aria-hidden' => 'true']);
					}

					if (!empty($item['select_nav_menu'])) {
						$args = [
							'echo' => false,
							'menu' => $item['select_nav_menu'],
							'menu_class' => 'elementor-cubewp-mega-nav-menu',
							'menu_id' => 'menu-' . $this->get_mega_nav_menu_index() . '-' . $this->get_id(),
							'fallback_cb' => '__return_empty_string',
							'container' => '',
						];
						$menu_html = wp_nav_menu($args);

						if (!empty($menu_html)) {
							echo '<div class="cubewp-mega-nav-menu-dropdown" id="' . esc_attr($random_id) . '">';
							echo '<h3 class="container-back-slide" style="display:none;">' . esc_html($item['menu_name']) . '</h3>';
							echo $menu_html;
							echo '</div>';
						}
					}

					echo '</div>';
				}
			}
		}

		echo '</div>';
	}

	// Function to manage mega menu index
	protected function get_mega_nav_menu_index()
	{
		return $this->mega_nav_menu_index++;
	}


	public function cubewp_mega_menu_template($tempID)
	{
		return	 CubeWp_Theme_Builder::do_cubewp_theme_builder('mega-menu', $tempID);
	}
}

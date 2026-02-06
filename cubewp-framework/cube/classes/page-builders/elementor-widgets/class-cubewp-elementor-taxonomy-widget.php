<?php
defined('ABSPATH') || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * CubeWP Taxonomies Widgets.
 *
 * Elementor Widget For Taxonomies By CubeWP.
 *
 * @since 1.0.0
 */
class CubeWp_Elementor_Taxonomy_Widget extends Widget_Base
{

	public function get_name()
	{
		return 'cubewp_taxonomy';
	}

	public function get_title()
	{
		return esc_html__('CubeWP Taxonomy', 'cubewp-framework');
	}

	public function get_icon()
	{
		return 'eicon-posts-grid';
	}

	public function get_categories()
	{
		return array('cubewp');
	}

	public function get_keywords()
	{
		return array(
			'cubewp',
			'featured',
			'elements',
			'widgets',
			'terms',
			'taxonomy',
			'category',
			'categories',
			'term',
			'taxonomies',
			'posts',
			'post',
			'archive',
			'locations'
		);
	}

	protected function register_controls()
	{
		$args       = array(
			'public'   => true,
			'_builtin' => false
		);
		$taxonomies = get_taxonomies($args);
		$taxonomies = self::cwp_get_taxonomies_label($taxonomies);
		$this->start_controls_section('cubewp_widgets_section', array(
			'label' => esc_html__('Widget Options', 'cubewp-framework'),
			'tab'   => Controls_Manager::TAB_CONTENT,
		));
		$this->add_control('taxonomy', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Select Taxonomy', 'cubewp-framework'),
			'options' => $taxonomies,
		));
		$this->add_control('terms_per_page', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('No Of Terms To Show', 'cubewp-framework'),
			'options' => array(
				'0'  => esc_html__('All', 'cubewp-framework'),
				'2'  => esc_html__('2', 'cubewp-framework'),
				'3'  => esc_html__('3', 'cubewp-framework'),
				'4'  => esc_html__('4', 'cubewp-framework'),
				'5'  => esc_html__('5', 'cubewp-framework'),
				'6'  => esc_html__('6', 'cubewp-framework'),
				'7'  => esc_html__('7', 'cubewp-framework'),
				'8'  => esc_html__('8', 'cubewp-framework'),
				'9'  => esc_html__('9', 'cubewp-framework'),
				'12' => esc_html__('12', 'cubewp-framework'),
				'16' => esc_html__('16', 'cubewp-framework'),
				'15' => esc_html__('15', 'cubewp-framework'),
				'20' => esc_html__('20', 'cubewp-framework'),
				'custom' => esc_html__('Custom', 'cubewp-framework'),
			),
			'default' => '0'
		));

		$this->add_control('custom_terms_count', array(
			'type'        => Controls_Manager::NUMBER,
			'label'       => esc_html__('Custom Terms Count', 'cubewp-framework'),
			'description' => esc_html__('Enter number of terms to show. Works only if "No Of Terms To Show" is set to "Show Custom Number of Terms".', 'cubewp-framework'),
			'default'     => 5,
			'min'         => 1,
			'condition'   => array(
				'terms_per_page' => 'custom',
			),
		));

		$this->cwp_get_output_style_controls();

		$this->add_responsive_control('layout_display', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Layout Display', 'cubewp-framework'),
			'options' => array(
				'grid' => esc_html__('Grid', 'cubewp-framework'),
				'flex' => esc_html__('Flex', 'cubewp-framework'),
			),
			'default' => 'grid'
		));
		$this->add_responsive_control('column_per_row', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Column Per Row', 'cubewp-framework'),
			'options' => array(
				'1' => esc_html__('1', 'cubewp-framework'),
				'2' => esc_html__('2', 'cubewp-framework'),
				'3' => esc_html__('3', 'cubewp-framework'),
				'4' => esc_html__('4', 'cubewp-framework'),
				'5' => esc_html__('5', 'cubewp-framework'),
				'6' => esc_html__('6', 'cubewp-framework'),
				'7' => esc_html__('7', 'cubewp-framework'),
				'8' => esc_html__('8', 'cubewp-framework'),
				'9' => esc_html__('9', 'cubewp-framework'),
				'10' => esc_html__('10', 'cubewp-framework'),
				'11' => esc_html__('11', 'cubewp-framework'),
				'12' => esc_html__('12', 'cubewp-framework'),
			),
			'selectors'   => array(
				'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: grid ;grid-template-columns: repeat({{VALUE}}, 1fr);'
			),
			'default' => '4',
			'condition' => array(
				'layout_display' => 'grid',
			),
		));
		$this->add_responsive_control(
			'terms_row_span',
			[
				'type'      => Controls_Manager::SELECT,
				'label'     => esc_html__('Terms Row', 'cubewp-framework'),
				'options' => array(
					'repeat(1, 1fr)' => esc_html__('1', 'cubewp-framework'),
					'repeat(2, 1fr)' => esc_html__('2', 'cubewp-framework'),
					'repeat(3, 1fr)' => esc_html__('3', 'cubewp-framework'),
					'repeat(4, 1fr)' => esc_html__('4', 'cubewp-framework'),
					'repeat(5, 1fr)' => esc_html__('5', 'cubewp-framework'),
					'repeat(6, 1fr)' => esc_html__('6', 'cubewp-framework'),
					'repeat(7, 1fr)' => esc_html__('7', 'cubewp-framework'),
					'repeat(8, 1fr)' => esc_html__('8', 'cubewp-framework'),
					'repeat(9, 1fr)' => esc_html__('9', 'cubewp-framework'),
					'repeat(10, 1fr)' => esc_html__('10', 'cubewp-framework'),
					'repeat(11, 1fr)' => esc_html__('11', 'cubewp-framework'),
					'repeat(12, 1fr)' => esc_html__('12', 'cubewp-framework'),
					'unset' => esc_html__('Auto', 'cubewp-framework'),
				),
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: grid; grid-template-rows: {{VALUE}};'
				],
				'default'   => 'unset',
				'condition' => [
					'layout_display' => 'grid',
				],
			]
		);
		$this->add_responsive_control('flex_wrap', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Flex Wrap', 'cubewp-framework'),
			'options' => array(
				'wrap'   => esc_html__('Wrap', 'cubewp-framework'),
				'nowrap' => esc_html__('No Wrap', 'cubewp-framework'),
			),
			'selectors'   => array(
				'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: flex; flex-wrap: {{VALUE}};'
			),
			'default' => 'wrap',
			'condition' => array(
				'layout_display' => 'flex',
			),
		));
		$this->add_responsive_control('flex_justify', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Flex Justify Content', 'cubewp-framework'),
			'options' => array(
				'flex-start'     => esc_html__('Start', 'cubewp-framework'),
				'center'         => esc_html__('Center', 'cubewp-framework'),
				'flex-end'       => esc_html__('End', 'cubewp-framework'),
				'space-between'  => esc_html__('Space Between', 'cubewp-framework'),
				'space-around'   => esc_html__('Space Around', 'cubewp-framework'),
				'space-evenly'   => esc_html__('Space Evenly', 'cubewp-framework'),
			),
			'selectors'   => array(
				'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: flex; justify-content: {{VALUE}};'
			),
			'default' => 'flex-start',
			'condition' => array(
				'layout_display' => 'flex',
			),
		));
		$this->add_responsive_control('flex_align_items', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Flex Align Items', 'cubewp-framework'),
			'options' => array(
				'stretch'   => esc_html__('Stretch', 'cubewp-framework'),
				'flex-start' => esc_html__('Start', 'cubewp-framework'),
				'center'    => esc_html__('Center', 'cubewp-framework'),
				'flex-end'  => esc_html__('End', 'cubewp-framework'),
				'baseline'  => esc_html__('Baseline', 'cubewp-framework'),
			),
			'selectors'   => array(
				'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: flex; align-items: {{VALUE}};'
			),
			'default' => 'stretch',
			'condition' => array(
				'layout_display' => 'flex',
			),
		));
		$this->add_responsive_control('flex_align_content', array(
			'type'    => Controls_Manager::SELECT,
			'label'   => esc_html__('Flex Align Content', 'cubewp-framework'),
			'options' => array(
				'stretch'       => esc_html__('Stretch', 'cubewp-framework'),
				'flex-start'    => esc_html__('Start', 'cubewp-framework'),
				'center'        => esc_html__('Center', 'cubewp-framework'),
				'flex-end'      => esc_html__('End', 'cubewp-framework'),
				'space-between' => esc_html__('Space Between', 'cubewp-framework'),
				'space-around'  => esc_html__('Space Around', 'cubewp-framework'),
			),
			'selectors'   => array(
				'{{WRAPPER}} .cwp-taxonomy-terms' => 'display: flex; align-content: {{VALUE}};'
			),
			'default' => 'stretch',
			'condition' => array(
				'layout_display' => 'flex',
				'flex_wrap' => 'wrap',
			),
		));
		$this->add_responsive_control(
			'terms_gap',
			[
				'label' => esc_html__('Gap Between Terms', 'cubewp-framework'),
				'type' => Controls_Manager::GAPS,
				'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
				'default' => [
					'row' => 10,
					'column' => 10,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms' => 'gap: {{ROW}}{{UNIT}} {{COLUMN}}{{UNIT}};',
				],
			]
		);

		$this->add_control('child_terms', array(
			'type'    => Controls_Manager::SWITCHER,
			'label'   => esc_html__('Show Child Terms', 'cubewp-framework'),
			'default' => 'no'
		));
		$this->add_control('hide_empty', array(
			'type'    => Controls_Manager::SWITCHER,
			'label'   => esc_html__('Hide Empty Terms', 'cubewp-framework'),
			'default' => 'no'
		));

		do_action('cubewp_taxonomy_widget_additional_controls', $this);
		$this->end_controls_section();
		$this->cwp_get_term_grids_controls();
		$this->cubewp_taxonomy_add_slider_controls();
	}

	private function cwp_get_output_style_controls()
	{
		$taxonomies = get_taxonomies(array(), 'objects');
		$options = array(
			'boxed_view' => esc_html__('Boxed View', 'cubewp-framework'),
			'list_view'  => esc_html__('List View', 'cubewp-framework'),
		);
		foreach ($taxonomies as $taxonomy => $taxonomy_obj) {
			if (!is_object($taxonomy_obj) || !isset($taxonomy_obj->labels->name)) {
				continue;
			}
			$termcards = cwp_get_elemetor_termcards_by_type($taxonomy);
			if (!empty($termcards) && is_array($termcards)) {
				$options = array_merge($options, $termcards);
			}
			$label = $taxonomy_obj->labels->name;
			$this->add_control('output_style_' . $taxonomy, array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__('Select Term Style for', 'cubewp-framework') . ' ' . esc_html($label),
				'options' => $options,
				'default' => 'boxed_view',
				'condition' => array(
					'taxonomy' => $taxonomy,
				),
			));
			$this->add_control('icon_media_name_' . $taxonomy, array(
				'type'        => Controls_Manager::TEXT,
				'label'       => esc_html__('Icon Or Image', 'cubewp-framework'),
				'description' => esc_html__('Enter taxonomy custom field slug for term icon or image.', 'cubewp-framework'),
				'condition'   => array(
					'taxonomy' => $taxonomy,
					'output_style_' . $taxonomy => 'boxed_view',
				),
			));
			$repeater = new Repeater();
			$repeater->add_control('term_box_color', array(
				'label'       => esc_html__('Color', 'cubewp-framework'),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array(
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}}'
				),
				'label_block' => true,
			));
			$this->add_control('terms_box_color_' . $taxonomy, array(
				'label'       => esc_html__('Terms Box Color', 'cubewp-framework'),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'term_box_color' => '#faf7d9',
					),
					array(
						'term_box_color' => '#e1f0ee',
					),
					array(
						'term_box_color' => '#fcece3',
					),
					array(
						'term_box_color' => '#e3effb',
					),
					array(
						'term_box_color' => '#ffeff7',
					),
				),
				'title_field' => '{{{ term_box_color }}}',
				'refresh_preview' => true,
				'condition'   => array(
					'taxonomy' => $taxonomy,
					'output_style_' . $taxonomy => 'boxed_view',
				),
			));
		}
	}

	private function cwp_get_term_grids_controls()
	{
		// Add new section for individual term grid settings
		$this->start_controls_section(
			'per_term_grid_settings',
			[
				'label' => esc_html__('Per Term Grid Settings', 'cubewp-framework'),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'layout_display' => 'grid',
				],
			]
		);

		$this->add_control(
			'enable_per_term_grid',
			[
				'label' => esc_html__('Enable Per Term Grid Settings', 'cubewp-framework'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'cubewp-framework'),
				'label_off' => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default' => 'no',
				'condition' => [
					'layout_display' => 'grid',
				],
			]
		);
		// Add helper HTML below
		$this->add_control(
			'grid_guide_note',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => __(
					'<div style="padding: 10px;color: #2a7607; border-left: 3px solid #2271b1; background: #f0f6fc; margin-top: 10px;">
							<strong>Need help creating grid structure?</strong><br>
							Use this free CSS Grid Generator: 
							<a href="https://cssgridgenerator.io/" target="_blank" style="color:#2271b1; text-decoration:underline;">
								cssgridgenerator.io
							</a>
						</div>',
					'cubewp-framework'
				),
				'content_classes' => 'cwp-info-box',
				'condition' => [
					'layout_display' => 'grid',
				],
			]
		);
		$repeater = new Repeater();

		$repeater->add_control(
			'term_position',
			[
				'label' => esc_html__('Term Position', 'cubewp-framework'),
				'type' => Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'description' => esc_html__('Which term number to apply these settings to (1 for first term, 2 for second, etc.)', 'cubewp-framework'),
			]
		);

		$repeater->add_responsive_control(
			'column_span',
			[
				'label' => esc_html__('Column Span', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
					'full' => esc_html__('Full Width', 'cubewp-framework'),
				],
				'default' => '1',
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position.VALUE}})' => 'grid-column: span {{VALUE}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'row_span',
			[
				'label' => esc_html__('Row Span', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'1' => esc_html__('1', 'cubewp-framework'),
					'2' => esc_html__('2', 'cubewp-framework'),
					'3' => esc_html__('3', 'cubewp-framework'),
					'4' => esc_html__('4', 'cubewp-framework'),
					'5' => esc_html__('5', 'cubewp-framework'),
					'6' => esc_html__('6', 'cubewp-framework'),
					'7' => esc_html__('7', 'cubewp-framework'),
					'8' => esc_html__('8', 'cubewp-framework'),
					'9' => esc_html__('9', 'cubewp-framework'),
					'10' => esc_html__('10', 'cubewp-framework'),
					'11' => esc_html__('11', 'cubewp-framework'),
					'12' => esc_html__('12', 'cubewp-framework'),
				],
				'default' => '1',
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position}})' => 'grid-row: span {{VALUE}};',
				],
			]
		);
		$repeater->add_responsive_control(
			'row_span_start',
			[
				'label' => esc_html__('Row Span Start', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'1' => esc_html__('1', 'cubewp-framework'),
					'2' => esc_html__('2', 'cubewp-framework'),
					'3' => esc_html__('3', 'cubewp-framework'),
					'4' => esc_html__('4', 'cubewp-framework'),
					'5' => esc_html__('5', 'cubewp-framework'),
					'6' => esc_html__('6', 'cubewp-framework'),
					'7' => esc_html__('7', 'cubewp-framework'),
					'8' => esc_html__('8', 'cubewp-framework'),
					'9' => esc_html__('9', 'cubewp-framework'),
					'10' => esc_html__('10', 'cubewp-framework'),
					'11' => esc_html__('11', 'cubewp-framework'),
					'12' => esc_html__('12', 'cubewp-framework'),
				],
				'default' => '1',
			]
		);

		$repeater->add_responsive_control(
			'column_span_start',
			[
				'label' => esc_html__('Column Span Start', 'cubewp-framework'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'1' => esc_html__('1', 'cubewp-framework'),
					'2' => esc_html__('2', 'cubewp-framework'),
					'3' => esc_html__('3', 'cubewp-framework'),
					'4' => esc_html__('4', 'cubewp-framework'),
					'5' => esc_html__('5', 'cubewp-framework'),
					'6' => esc_html__('6', 'cubewp-framework'),
					'7' => esc_html__('7', 'cubewp-framework'),
					'8' => esc_html__('8', 'cubewp-framework'),
					'9' => esc_html__('9', 'cubewp-framework'),
					'10' => esc_html__('10', 'cubewp-framework'),
					'11' => esc_html__('11', 'cubewp-framework'),
					'12' => esc_html__('12', 'cubewp-framework'),
				],
				'default' => '1',
			]
		);
		$repeater->add_responsive_control(
			'custom_margin',
			[
				'label' => esc_html__('Custom Margin', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position}})' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$repeater->add_responsive_control(
			'custom_padding',
			[
				'label' => esc_html__('Custom Padding', 'cubewp-framework'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position}}) .cwp-taxonomy-term-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$repeater->add_control(
			'custom_width',
			[
				'label' => esc_html__('Custom Width', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position}})' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$repeater->add_control(
			'custom_height',
			[
				'label' => esc_html__('Custom Height', 'cubewp-framework'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms .cwp-taxonomy-term:nth-child({{term_position}}) .cwp-taxonomy-term-box' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'term_grid_settings',
			[
				'label' => esc_html__('Individual Term Grid Settings', 'cubewp-framework'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => 'Term #{{{ term_position }}}',
				'condition' => [
					'layout_display' => 'grid',
					'enable_per_term_grid' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function cwp_get_taxonomies_label($taxonomies)
	{
		$taxonomy_labels = array();
		if (!empty($taxonomies) && is_array($taxonomies))
			foreach ($taxonomies as $slug => $taxonomy) {
				if (taxonomy_exists($taxonomy)) {
					$label = get_taxonomy($taxonomy)->labels->name;
					$taxonomy_labels[$slug] = $label;
				}
			}
		return $taxonomy_labels;
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		if (isset($settings['terms_per_page']) && $settings['terms_per_page'] === 'custom' && isset($settings['custom_terms_count']) && is_numeric($settings['custom_terms_count']) && $settings['custom_terms_count'] > 0) {
			$settings['terms_per_page'] = intval($settings['custom_terms_count']);
		}
		$taxonomies = get_taxonomies();
		foreach ($taxonomies as $taxonomy) {
			if (isset($settings['taxonomy']) && $settings['taxonomy'] == $taxonomy && isset($settings['output_style_' . $taxonomy])) {
				$settings['output_style'] = $settings['output_style_' . $taxonomy];
				$settings['icon_media_name'] = isset($settings['icon_media_name_' . $taxonomy]) ? $settings['icon_media_name_' . $taxonomy] : '';
				$settings['terms_box_color'] = isset($settings['terms_box_color_' . $taxonomy]) ? $settings['terms_box_color_' . $taxonomy] : array();
			}
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters('cubewp_shortcode_taxonomy_output', '', $settings);
	}

	private function cubewp_taxonomy_add_slider_controls()
	{
		$this->start_controls_section(
			'slider_style_section',
			[
				'label' => esc_html__('Terms Slider', 'cubewp-framework'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'cwp_enable_slider',
			[
				'label'        => esc_html__('Enable Slider', 'cubewp-framework'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'cubewp-framework'),
				'label_off'    => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default'      => '',
			]
		);


		$this->add_responsive_control(
			'slider_post_spacing',
			[
				'label'        => esc_html__('Post Spacing', 'cubewp-framework'),
				'type'         => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units'   => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .cubewp-term-slider .slick-slide>div ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
					'{{WRAPPER}} .cwp-taxonomy-terms>div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				]
			]
		);

		$this->add_control('slides_to_show', array(
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'label'   => esc_html__('Slides To Show', 'cubewp-framework'),
			'default' => 3,
			'min'     => 1,
			'max'     => 10,
			'step'    => 1,
			'description' => esc_html__('Number of slides to show at once in the slider.', 'cubewp-framework'),
			'condition'   => [
				'cwp_enable_slider' => 'yes',
			],
		));

		$this->add_control(
			'slides_to_scroll',
			[
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'label'   => esc_html__('Slides To Scroll', 'cubewp-framework'),
				'default' => 1,
				'min'     => 1,
				'max'     => 10,
				'step'    => 1,
				'description' => esc_html__('Number of slides to scroll at once in the slider.', 'cubewp-framework'),
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'autoplay',
			[
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'label'   => esc_html__('Autoplay', 'cubewp-framework'),
				'default' => 'yes',
				'description' => esc_html__('Enable or disable autoplay for the slider.', 'cubewp-framework'),
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'autoplay_speed',
			[
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'label'   => esc_html__('Autoplay Speed (ms)', 'cubewp-framework'),
				'default' => 2000,
				'min'     => 0,
				'step'    => 500,
				'description' => esc_html__('Set the speed for autoplay in milliseconds.', 'cubewp-framework'),
				'condition'   => [
					'cwp_enable_slider' => 'yes',
					'autoplay'      => 'yes',
				],
			]
		);

		$this->add_control(
			'speed',
			[
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'label'   => esc_html__('Speed (ms)', 'cubewp-framework'),
				'default' => 500,
				'min'     => 0,
				'step'    => 100,
				'description' => esc_html__('Set the speed for the slider transition in milliseconds.', 'cubewp-framework'),
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'fade_effect',
			[
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label' => esc_html__('Fade Effect', 'cubewp-framework'),
				'default' => '',
				'description' => esc_html__('Enable fade effect for slides transition.', 'cubewp-framework'),
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'infinite',
			[
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'label'   => esc_html__('Infinite Loop', 'cubewp-framework'),
				'default' => 'yes',
				'description' => esc_html__('Enable or disable infinite loop for the slider.', 'cubewp-framework'),
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'variable_width',
			[
				'label' => __('Variable Width', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __('Yes', 'cubewp-framework'),
				'label_off' => __('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default' => 'no',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'overflow_setting',
			[
				'label' => esc_html__('Overflow Setting', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-list.draggable' => 'overflow: inherit;',
				],
			]
		);

		$this->add_control(
			'enable_progress_bar',
			[
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label' => esc_html__('Enable Progress Bar', 'cubewp-framework'),
				'default' => '',
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'progress_bar_height',
			[
				'label' => esc_html__('Progress Bar Height', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} .slick-progress, {{WRAPPER}} .slick-progress .slick-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'enable_progress_bar' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'progress_bar_back_color',
			[
				'label' => esc_html__('Progress Bar Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .slick-progress' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'enable_progress_bar' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'progress_bar_color',
			[
				'label' => esc_html__('Progress Bar Fill Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ddd',
				'selectors' => [
					'{{WRAPPER}} .slick-progress .slick-progress-bar' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'enable_progress_bar' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'scroll_bar_margin_top',
			[
				'label' => esc_html__('Progress Bar Margin Top', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => -100,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'size' => '',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .slick-progress' => 'margin-top: {{SIZE}}px;',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'enable_progress_bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_dots_arrow_settings_divider',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'custom_arrows',
			[
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label' => esc_html__('Enable Arrows', 'cubewp-framework'),
				'default' => 'yes',
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'prev_icon',
			[
				'label' => __('Previous Icon', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-left',
					'library' => 'fa-solid',
				],
				'label_block' => true,
				'condition'   => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'next_icon',
			[
				'label' => __('Next Icon', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-right',
					'library' => 'fa-solid',
				],
				'label_block' => true,
				'condition'   => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__('Icon Size (px)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 5,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev i, {{WRAPPER}} .cubewp-term-slider .slick-next i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_color',
			[
				'label' => esc_html__('Icon Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev i, {{WRAPPER}} .cubewp-term-slider .slick-next i' => 'color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_hover_color',
			[
				'label' => esc_html__('Icon Hover Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev:hover i, {{WRAPPER}} .cubewp-term-slider .slick-next:hover i' => 'color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_background_color',
			[
				'label' => esc_html__('Icon & Svg Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_hover_background_color',
			[
				'label' => esc_html__('Icon & Svg Hover Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev:hover, {{WRAPPER}} .cubewp-term-slider .slick-next:hover' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'svg_color',
			[
				'label' => esc_html__('SVG Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev svg path, {{WRAPPER}} .cubewp-term-slider .slick-next svg path' => 'fill: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'svg_hover_color',
			[
				'label' => esc_html__('SVG Hover Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#FF0000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev:hover svg path, {{WRAPPER}} .cubewp-term-slider .slick-next:hover svg path' => 'fill: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'svg_width',
			[
				'label' => esc_html__('SVG Width', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev svg, {{WRAPPER}} .cubewp-term-slider .slick-next svg' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'svg_height',
			[
				'label' => esc_html__('SVG Height', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 24,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'em' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev svg, {{WRAPPER}} .cubewp-term-slider .slick-next svg' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border',
			[
				'label' => esc_html__('Border', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; border-style: solid;',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border_radius',
			[
				'label' => esc_html__('Border Radius', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border_color',
			[
				'label' => esc_html__('Border Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border_color_hover',
			[
				'label' => esc_html__('Border Color on Hover', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev:hover, {{WRAPPER}} .cubewp-term-slider .slick-next:hover' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_border_transition',
			[
				'label' => esc_html__('Transition Duration', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.3,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'transition: background-color {{SIZE}}s, color {{SIZE}}s, border-color {{SIZE}}s;',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label' => esc_html__('Icon & Svg Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'slider_arrow_box_shadow',
				'label' => __('Arrow Box Shadow', 'cubewp-framework'),
				'selector' => '{{WRAPPER}} .cubewp-term-slider .slick-arrow',
				'separator' => 'before',
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_position_divider_heading',
			[
				'label' => esc_html__('Set the Icons Positions', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_top_position',
			[
				'label' => esc_html__('Top Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'top: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'    => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_bottom_position',
			[
				'label' => esc_html__('Bottom Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-prev, {{WRAPPER}} .cubewp-term-slider .slick-next' => 'bottom: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'    => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_prev_left_position',
			[
				'label' => esc_html__('Left Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
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
					'{{WRAPPER}} .cubewp-term-slider .slick-prev' => 'left: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'    => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'icon_next_right_position',
			[
				'label' => esc_html__('Right Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
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
					'{{WRAPPER}} .cubewp-term-slider .slick-next' => 'right: {{SIZE}}{{UNIT}} !important;',
				],
				'condition'    => [
					'cwp_enable_slider' => 'yes',
					'custom_arrows' => 'yes',
				],
			]
		);

		$this->add_control(
			'custom_dots',
			[
				'label' => esc_html__('Enable Dots', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'cubewp-framework'),
				'label_off'    => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default'      => '',
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_display_flex',
			[
				'label' => esc_html__('Dots Display', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'block' => esc_html__('Block', 'cubewp-framework'),
					'flex' => esc_html__('Flex', 'cubewp-framework'),
				],
				'default' => 'flex',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'display: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_dots' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_flex_direction',
			[
				'label' => esc_html__('Dots Flex Direction', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'row' => esc_html__('Row', 'cubewp-framework'),
					'row-reverse' => esc_html__('Row Reverse', 'cubewp-framework'),
					'column' => esc_html__('Column', 'cubewp-framework'),
					'column-reverse' => esc_html__('Column Reverse', 'cubewp-framework'),
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'flex-direction: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_display_flex' => 'flex',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_gap',
			[
				'label' => __('Dots Gap', 'cubewp-framework'),
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
					'unit' => 'px',
					'size' => 5,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'gap:{{SIZE}}{{UNIT}};',
				],
				'condition'   => [
					'cwp_enable_slider' => 'yes',
					'custom_dots' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_position_select',
			[
				'label' => esc_html__('Dots Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'static' => esc_html__('Static', 'cubewp-framework'),
					'absolute' => esc_html__('Absolute', 'cubewp-framework'),
					'relative' => esc_html__('Relative', 'cubewp-framework'),
					'fixed' => esc_html__('Fixed', 'cubewp-framework'),
				],
				'default' => 'static',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'position: {{VALUE}};',
				],
				'condition' => [
					'cwp_enable_slider' => 'yes',
					'custom_dots' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_top_position',
			[
				'label' => esc_html__('Dots Top Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'top: {{SIZE}}{{UNIT}} !important;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_position_select' => 'absolute',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_bottom_position',
			[
				'label' => esc_html__('Dots Bottom Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 50,
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'bottom: {{SIZE}}{{UNIT}} !important;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_position_select' => 'absolute',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_left_position',
			[
				'label' => esc_html__('Dots Left Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
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
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'left: {{SIZE}}{{UNIT}} !important;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_position_select' => 'absolute',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_right_position',
			[
				'label' => esc_html__('Dots Right Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 2000,
						'step' => 1,
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
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'right: {{SIZE}}{{UNIT}} !important;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_position_select' => 'absolute',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'dots_position_z_index',
			[
				'label' => esc_html__('Dots Z-Index', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => -9999,
				'max' => 9999,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots' => 'z-index: {{VALUE}} !important;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'dots_position_select' => 'absolute',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_padding',
			[
				'label' => esc_html__('Dots Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_border_style',
			[
				'label' => __('Dots Border Style', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'None',
				'options' => [
					'none' => __('None', 'cubewp-framework'),
					'solid' => __('Solid', 'cubewp-framework'),
					'dotted' => __('Dotted', 'cubewp-framework'),
					'dashed' => __('Dashed', 'cubewp-framework'),
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li' => 'border-style: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_border_width',
			[
				'label' => __('Dots Border Width', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px'],
				'default' => [
					'top' => '',
					'right' => '',
					'bottom' => '',
					'left' => '',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
					'dots_border_style!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'dots_outside_padding',
			[
				'label' => esc_html__('Dots Outside Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_outside_color',
			[
				'label' => esc_html__('Dots Outside Backgroud Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'active_dot_outside_color',
			[
				'label' => esc_html__('Active Dot Outside Backgroud Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots .slick-active' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_border_color',
			[
				'label' => __('Dots Border Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_active_border_color',
			[
				'label' => __('Active Dot Border Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots .slick-active' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_border_radius',
			[
				'label' => __('Dots Border Radius', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 0,
				'min' => 0,
				'max' => 500,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li,{{WRAPPER}} .cubewp-term-slider .slick-dots li button' => 'border-radius: {{VALUE}}px;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_background_color',
			[
				'label' => __('Dots Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li button' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_active_background_color',
			[
				'label' => __('Active Dot Background Color', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'default' => '#000000',
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots .slick-active button' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_width',
			[
				'label' => __('Dots Width', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li button' => 'width: {{VALUE}}px;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'dots_height',
			[
				'label' => __('Dots Height', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 10,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots li button' => 'height: {{VALUE}}px;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'active_dot_width',
			[
				'label' => __('Active Dot Width', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots .slick-active button' => 'width: {{VALUE}}px;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'active_dot_height',
			[
				'label' => __('Active Dot Height', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 12,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'selectors' => [
					'{{WRAPPER}} .cubewp-term-slider .slick-dots .slick-active button' => 'height: {{VALUE}}px;',
				],
				'condition' => [
					'custom_dots' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_responsive_settings_heading',
			[
				'label' => esc_html__('Responsive Settings For Slides To Show And Scroll', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_show_tablet',
			[
				'label' => esc_html__('Slides To Show On (Tablet)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 3,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_show_tablet_portrait',
			[
				'label' => esc_html__('Slides To Show On (Tablet Portrait)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 2,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_show_mobile',
			[
				'label' => esc_html__('Slides To Show On (Mobile)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 1,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slider_responsive_settings_divider',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'style' => 'thick',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_scroll_tablet',
			[
				'label' => esc_html__('Slides To Scroll On (Tablet)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 1,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_scroll_tablet_portrait',
			[
				'label' => esc_html__('Slides To Scroll On (Tablet Portrait)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 1,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'slides_to_scroll_mobile',
			[
				'label' => esc_html__('Slides To Scroll On (Mobile)', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'min' => 1,
				'max' => 10,
				'step' => 1,
				'default' => 1,
				'condition' => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		// slider wrape dots 
		$this->add_control(
			'slider_dots_wrap_settings_heading',
			[
				'label' => esc_html__('Wrap Dots With Arrows', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'enable_wrap_dots_arrows',
			[
				'label'        => esc_html__('Enable Wrap Dots With Arrows', 'cubewp-framework'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'cubewp-framework'),
				'label_off'    => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default'      => '',
				'condition'   => [
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_direction_position_vertical',
			[
				'label' => esc_html__('Vertical Direction', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => esc_html__('Top', 'cubewp-framework'),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => esc_html__('Bottom', 'cubewp-framework'),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'bottom',
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'vp_scrollbar_Top_position',
			[
				'label' => esc_html__('Top Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => -10,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' => 'top: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'icon_direction_position_vertical' => 'top',
				],
			]
		);
		$this->add_responsive_control(
			'vp_scrollbar_bottom_position',
			[
				'label' => esc_html__('Bottom Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => -10,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' =>  'bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'icon_direction_position_vertical' => 'bottom',
				],
			]
		);

		$this->add_responsive_control(
			'icon_direction_position',
			[
				'label' => esc_html__('horizontal Direction', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Left', 'cubewp-framework'),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__('Right', 'cubewp-framework'),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'right',
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
				],

			]
		);

		$this->add_responsive_control(
			'vp_scrollbar_right_position',
			[
				'label' => esc_html__('Right Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => -10,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' =>  'right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'icon_direction_position' => 'right',
				],
			]
		);
		$this->add_responsive_control(
			'vp_scrollbar_left_position',
			[
				'label' => esc_html__('Left Position', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'unit' => 'px',
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -500,
						'max' => 500,
						'step' => 1,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
						'step' => 1,
					],
					'em' => [
						'min' => -10,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' =>  'left: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'icon_direction_position' => 'left',
				],
			]
		);
		$this->add_responsive_control(
			'gap_between_items',
			[
				'label' => esc_html__('Gap Between Items', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'unit' => 'px',
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 10,
						'step' => 0.1,
					],
					'em' => [
						'min' => 0,
						'max' => 5,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' =>  'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'wrap_justify_content',
			[
				'label' => esc_html__('Justify Content', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'flex-start' => esc_html__('Flex Start', 'cubewp-framework'),
					'center' => esc_html__('Center', 'cubewp-framework'),
					'flex-end' => esc_html__('Flex End', 'cubewp-framework'),
					'space-between' => esc_html__('Space Between', 'cubewp-framework'),
					'space-around' => esc_html__('Space Around', 'cubewp-framework'),
					'space-evenly' => esc_html__('Space Evenly', 'cubewp-framework'),
				],
				'default' => 'center',
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
					'cwp_enable_slider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .slick-arrows-wrapper' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wrap_dots_arrows_padding',
			[
				'label' => esc_html__('Padding', 'cubewp-framework'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .cwp-taxonomy-terms > .slick-arrows-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
				'condition' => [
					'enable_wrap_dots_arrows' => 'yes',
				],
			]
		);
		$this->end_controls_section();
	}
}

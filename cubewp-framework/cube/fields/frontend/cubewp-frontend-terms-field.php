<?php

/**
 * CubeWp admin terms field 
 *
 * @version 1.0
 * @package cubewp/cube/fields/frontend
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * CubeWp_Frontend_Terms_Field
 */
class CubeWp_Frontend_Terms_Field extends CubeWp_Frontend
{

    public function __construct()
    {
        add_filter('cubewp/search_filters/checkbox/taxonomy/field', array($this, 'render_search_filters_checkbox_taxonomy_field'), 10, 2);
        add_filter('cubewp/search_filters/dropdown/taxonomy/field', array($this, 'render_search_filters_dropdown_taxonomy_field'), 10, 2);

        add_filter('cubewp/frontend/search/checkbox/taxonomy/field', array($this, 'render_search_checkbox_taxonomy_field'), 10, 2);
        add_filter('cubewp/frontend/search/dropdown/taxonomy/field', array($this, 'render_search_filters_dropdown_taxonomy_field'), 10, 2);
        add_filter('cubewp/frontend/checkbox/taxonomy/field', array($this, 'render_checkbox_taxonomy_field'), 10, 2);
        add_filter('cubewp/frontend/dropdown/taxonomy/field', array($this, 'render_dropdown_field'), 10, 2);
    }


    /**
     * Method render_checkbox_taxonomy_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_checkbox_taxonomy_field($output = '', $args = array())
    {

        $args           =  apply_filters('cubewp/frontend/field/parametrs', $args);
        $options        = cwp_convert_choices_to_array($args['options']);
        $required       = self::cwp_frontend_field_required($args['required']);
        $required       = !empty($required['class']) ? $required['class'] : '';
        $output         = self::cwp_frontend_post_field_container($args);
        $output .= '<div class="cwp-checkbox-container">';
        $output .= self::cwp_frontend_field_label($args);
        $output .= '<div class="cwp-field-checkbox-container">';
        foreach ($options as $value => $label) {
            if (!empty($label)) {
                $value = isset($label['term_id']) && !empty($label['term_id']) ? $label['term_id'] : 0;
                $output .= '<div class="cwp-field-checkbox">';
                $input_attrs = array(
                    'type'         =>  'checkbox',
                    'id'           =>  esc_attr($value),
                    'name'         =>  !empty($args['custom_name']) ? $args['custom_name'] . '[]' : $args['name'],
                    'value'        =>  $value,
                    'class'        =>  'custom-control-input ' . $args['class'] . ' ' . $required,
                );
                if (isset($args['value']) && is_array($args['value']) && in_array($value, $args['value'])) {
                    $input_attrs['extra_attrs'] = ' checked="checked"';
                } else if (isset($args['value']) && $args['value'] == $value) {
                    $input_attrs['extra_attrs'] = ' checked="checked"';
                }

                $output .= cwp_render_text_input($input_attrs);
                $output .= '<label for="' . esc_attr($value) . '">' . esc_html($label['term_name']) . '</label>';
                $output .= '</div>';
                if (isset($label['childern']) && !empty($label['childern'])) {
                    foreach ($label['childern'] as $value => $label) {
                        $value = $label['term_id'];
                        $output .= '<div class="cwp-field-checkbox">';
                        $input_attrs = array(
                            'type'         =>  'checkbox',
                            'id'           =>  esc_attr($args['id'] . ' ' . $label['term_name']),
                            'name'         =>  !empty($args['custom_name']) ? $args['custom_name'] . '[]' : $args['name'],
                            'value'        =>  $value,
                            'class'        =>  'custom-control-input ' . $args['class'],
                        );
                        if (isset($args['value']) && is_array($args['value']) && in_array($value, $args['value'])) {
                            $input_attrs['extra_attrs'] = ' checked="checked"';
                        } else if (isset($args['value']) && $args['value'] == $value) {
                            $input_attrs['extra_attrs'] = ' checked="checked"';
                        }

                        $output .= cwp_render_text_input($input_attrs);
                        $output .= '<label for="' . esc_attr($args['id'] . ' ' . $label['term_name']) . '">' . esc_html($label['term_name']) . '</label>';
                        $output .= '</div>';
                        if (isset($label['childern']) && !empty($label['childern'])) {
                            foreach ($label['childern'] as $value => $label) {
                                $value = $label['term_id'];
                                $output .= '<div class="cwp-field-checkbox">';
                                $input_attrs = array(
                                    'type'         =>  'checkbox',
                                    'id'           =>  esc_attr($args['id'] . ' ' . $label['term_name']),
                                    'name'         =>  !empty($args['custom_name']) ? $args['custom_name'] . '[]' : $args['name'],
                                    'value'        =>  $value,
                                    'class'        =>  'custom-control-input ' . $args['class'],
                                );
                                if (isset($args['value']) && is_array($args['value']) && in_array($value, $args['value'])) {
                                    $input_attrs['extra_attrs'] = ' checked="checked"';
                                } else if (isset($args['value']) && $args['value'] == $value) {
                                    $input_attrs['extra_attrs'] = ' checked="checked"';
                                }

                                $output .= cwp_render_text_input($input_attrs);
                                $output .= '<label for="' . esc_attr($args['id'] . ' ' . $label['term_name']) . '">' . esc_html($label['term_name']) . '</label>';
                                $output .= '</div>';
                            }
                        }
                    }
                }
            }
        }
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';

        $output = apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);

        return $output;
    }

    /**
     * Method render_search_filters_checkbox_taxonomy_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_search_filters_checkbox_taxonomy_field($output = '', $args = array())
    {
        $args           = apply_filters('cubewp/frontend/field/parametrs', $args);
        $args           = $this->cubewp_merge_filter_builder_taxonomy_args($args);
        $options        = cwp_convert_choices_to_array($args['options']);
        $values         = !empty($args['value']) ? explode(',', $args['value']) : $args['value'];
        $name           = '';
        $currentVal     = '';
        $output         = self::cwp_frontend_post_field_container($args);
        $args['custom_name']  = !empty($args['name']) ? '_ST_' . $args['name'] : $args['name'];

        $output .= '<div class="cwp-search-field cwp-search-field-checkbox ' . $args['container_class'] . '">';
        $output .= self::cwp_frontend_search_field_label($args);
        $output .= '<ul class="cwp-field-checkbox-container cwp-taxonomy-checkbox-tree">';
        if (is_array($options) && count($options) > 0) {
            $output .= $this->render_taxonomy_checkbox_terms_list($options, $args, $values, $currentVal, 0, true);
        }
        $input_attrs = array(
            'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'class'        => !empty($currentVal) ? 'is_tax' : '',
            'value'        => !empty($currentVal) ? $currentVal : $args['value'],
            'extra_attrs'  => 'data-current-tax="' . esc_attr((string) $currentVal) . '"',
        );

        $output .= cwp_render_hidden_input($input_attrs);
        $output .= $this->cubewp_taxonomy_show_more_button_html($args);
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';

        $output = apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);

        return $output;
    }

    /**
     * Method render_search_checkbox_taxonomy_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_search_checkbox_taxonomy_field($output = '', $args = array())
    {
        $args           = apply_filters('cubewp/frontend/field/parametrs', $args);
        $args           = $this->cubewp_merge_filter_builder_taxonomy_args($args);
        $options        = cwp_convert_choices_to_array($args['options']);
        $values         = !empty($args['value']) ? explode(',', $args['value']) : $args['value'];
        $name           = '';
        $currentVal     = '';
        $output         = self::cwp_frontend_search_field_container($args);
        $args['custom_name']  = !empty($args['name']) ? '_ST_' . $args['name'] : $args['name'];

        $output .= '<div class="cwp-search-field cwp-search-field-checkbox ' . $args['container_class'] . '">';
        $output .= self::cwp_frontend_search_field_label($args);
        $output .= '<ul class="cwp-field-checkbox-container cwp-taxonomy-checkbox-tree">';
        if (!empty($options) && (is_array($options) || is_object($options))) {
            $output .= $this->render_taxonomy_checkbox_terms_list($options, $args, $values, $currentVal, 0, false);
        }
        $input_attrs = array(
            'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'value'        => '',
        );

        $output .= cwp_render_hidden_input($input_attrs);
        $output .= $this->cubewp_taxonomy_show_more_button_html($args);
        $output .= '</ul>';
        $output .= '</div>';
        $output .= '</div>';

        $output = apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);

        return $output;
    }

    /**
     * Method render_dropdown_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_dropdown_field($output = '', $args = array())
    {
        $args           =  apply_filters('cubewp/frontend/field/parametrs', $args);
        $required       = self::cwp_frontend_field_required($args['required']);
        $required       = !empty($required['class']) ? $required['class'] : '';
        $options        = cwp_convert_choices_to_array($args['options']);
        $output         = self::cwp_frontend_post_field_container($args);
        $output .= self::cwp_frontend_field_label($args);
        $input_attrs = array(
            'id'           => $args['id'],
            'class'        => $args['class'] . ' ' . $required,
            'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'value'        => $args['value'],
            'placeholder'  => !empty($args['placeholder']) ? $args['placeholder'] : esc_html__('Choose your option', 'cubewp-framework'),
            'options'      => isset($args['options']) ? self::cwp_sub_terms_dropdown_output_id($args['options']) : array(),
            'extra_attrs'  => isset($args['extra_attrs']) ? $args['extra_attrs'] : '',
            'select2_ui'   => isset($args['select2_ui']) ? $args['select2_ui'] : false
        );
        if ((isset($args['multi']) && $args['multi'] == true) || (isset($args['multiple']) && $args['multiple'] == 1)) {
            $output .= cwp_render_multi_dropdown_input($input_attrs);
        } else {
            $output .= cwp_render_dropdown_input($input_attrs);
        }

        $output .= '</div>';

        $output = apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);
        return $output;
    }

    /**
     * Method render_search_filters_dropdown_taxonomy_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_search_filters_dropdown_taxonomy_field($output = '', $args = array())
    {

        $args    =  apply_filters('cubewp/frontend/field/parametrs', $args);

        if (is_tax() && !is_search() && !is_page()) {
            $queried_object = get_queried_object();
            if (is_object($queried_object) && !empty($queried_object) && !is_wp_error($queried_object)) {
                $CurrentSlug = $queried_object->term_id;
                if (isset($CurrentSlug) && $queried_object->taxonomy === $args['name']) {
                    $args['value'] = $CurrentSlug;
                }
            }
        }

        $values  =  !empty($args['value']) ? explode(',', $args['value']) : $args['value'];
        $args['custom_name']  =  !empty($args['name']) ? '_ST_' . $args['name'] : $args['name'];

        if (isset($args['options']) && !empty($args['options'])) {
            $output  = self::cwp_frontend_search_field_container($args);
            $output .= self::cwp_frontend_search_field_label($args);
            $input_attrs = array(
                'id'           => !empty($args['id']) ? $args['id'] : $args['name'],
                'class'        => $args['class'],
                'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
                'value'        => $values,
                'placeholder'  => !empty($args['placeholder']) ? $args['placeholder'] : esc_html__('Choose your option', 'cubewp-framework'),
                'options'      => isset($args['options']) ? self::cwp_sub_terms_dropdown_output_id($args['options']) : array(),
            );
            if ((isset($args['multi']) && $args['multi'] == true) || (isset($args['multiple']) && $args['multiple'] == 1)) {
                unset($input_attrs['name']);
                $input_attrs['hidden_input'] = false;
                $input_attrs['class']  = $args['class'] . ' ' . $args['multi'] . ' multi-select';
                $output .= cwp_render_multi_dropdown_input($input_attrs);
                $input_attrs = array(
                    'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
                    'value'        => isset($args['value']) ? $args['value'] : '',
                );
                $output .= cwp_render_hidden_input($input_attrs);
            } else {
                $output .= cwp_render_dropdown_input($input_attrs);
            }

            $output .= '</div>';
        }

        $output = apply_filters("cubewp/search_filters/{$args['name']}/field", $output, $args);

        return $output;
    }

    /**
     * Merge Elementor filter builder taxonomy UI flags into field args.
     *
     * @param array $args Field arguments.
     * @return array
     */
    private function cubewp_merge_filter_builder_taxonomy_args($args = array())
    {
        global $cubewp_elementor_filter_taxonomy_ui;

        if (!is_array($cubewp_elementor_filter_taxonomy_ui)) {
            return $args;
        }

        $args['taxonomy_accordion']          = !empty($cubewp_elementor_filter_taxonomy_ui['accordion']);
        $args['taxonomy_collapsed_default']  = !empty($cubewp_elementor_filter_taxonomy_ui['collapsed_default']);
        $args['taxonomy_toggle_position']    = isset($cubewp_elementor_filter_taxonomy_ui['toggle_position']) ? $cubewp_elementor_filter_taxonomy_ui['toggle_position'] : 'right';
        $args['taxonomy_toggle_absolute']    = !empty($cubewp_elementor_filter_taxonomy_ui['toggle_absolute']);
        $args['taxonomy_show_more']          = !empty($cubewp_elementor_filter_taxonomy_ui['show_more']);
        $args['taxonomy_visible_count']      = isset($cubewp_elementor_filter_taxonomy_ui['visible_count']) ? absint($cubewp_elementor_filter_taxonomy_ui['visible_count']) : 10;
        $args['taxonomy_more_text']          = isset($cubewp_elementor_filter_taxonomy_ui['more_text']) ? $cubewp_elementor_filter_taxonomy_ui['more_text'] : '';
        $args['taxonomy_less_text']          = isset($cubewp_elementor_filter_taxonomy_ui['less_text']) ? $cubewp_elementor_filter_taxonomy_ui['less_text'] : '';

        return $args;
    }

    /**
     * View more / less button markup for long taxonomy lists.
     *
     * @param array $args Field args.
     * @return string
     */
    private function cubewp_taxonomy_show_more_button_html($args = array())
    {
        if (empty($args['taxonomy_show_more'])) {
            return '';
        }

        $more_text = !empty($args['taxonomy_more_text']) ? $args['taxonomy_more_text'] : esc_html__('View more', 'cubewp-framework');
        $less_text = !empty($args['taxonomy_less_text']) ? $args['taxonomy_less_text'] : esc_html__('View less', 'cubewp-framework');
        $visible   = isset($args['taxonomy_visible_count']) ? max(1, absint($args['taxonomy_visible_count'])) : 10;

        return '<li class="cwp-taxonomy-show-more-item" aria-hidden="true">'
            . '<div class="cwp-taxonomy-terms-show-more-wrap">'
            . '<button type="button" class="cwp-taxonomy-terms-show-more" data-expanded="0" data-visible-count="' . esc_attr((string) $visible) . '"'
            . ' data-more-text="' . esc_attr($more_text) . '" data-less-text="' . esc_attr($less_text) . '" aria-expanded="false">'
            . '<span class="cwp-taxonomy-terms-show-more-text">' . esc_html($more_text) . '</span>'
            . '</button></div></li>';
    }

    /**
     * Accordion toggle button HTML.
     *
     * @param array $term_data Term row.
     * @return string
     */
    private function cubewp_render_taxonomy_accordion_toggle($term_data)
    {
        return '<button type="button" class="cwp-taxonomy-accordion-toggle" aria-expanded="false" aria-label="' . esc_attr(sprintf(
            /* translators: %s: parent term name */
            __('Toggle child terms for %s', 'cubewp-framework'),
            $term_data['term_name']
        )) . '"><span class="cwp-taxonomy-accordion-icon" aria-hidden="true"></span></button>';
    }

    /**
     * Stable checkbox input id for a taxonomy term.
     *
     * @param array $args  Field args.
     * @param array $term  Term data.
     * @return string
     */
    private function cubewp_taxonomy_checkbox_term_input_id($args, $term)
    {
        $base = !empty($args['id']) ? $args['id'] : 'cwp-taxonomy';
        return esc_attr($base . '-term-' . absint($term['term_id']));
    }

    /**
     * Checked attribute for taxonomy checkbox inputs.
     *
     * @param array  $args       Field args.
     * @param int    $term_id    Term ID.
     * @param mixed  $values     Selected values.
     * @param string $currentVal Current taxonomy archive value (by reference).
     * @param int    $depth      Nesting depth.
     * @param bool   $is_filter  Search filter field context.
     * @return string
     */
    private function cubewp_taxonomy_checkbox_checked_extra($args, $term_id, $values, &$currentVal, $depth, $is_filter)
    {
        $term_id = absint($term_id);

        if ($is_filter && $depth === 0 && is_tax() && !is_search() && !is_page()) {
            $queried_object = get_queried_object();
            if (is_object($queried_object) && !empty($queried_object) && !is_wp_error($queried_object)) {
                $archive_term_id = isset($queried_object->term_id) ? (int) $queried_object->term_id : 0;
                if ($archive_term_id > 0 && $archive_term_id === $term_id) {
                    $currentVal = (string) $archive_term_id;
                    return ' checked="checked"';
                }
            }
        }

        if (isset($args['value']) && is_array($values) && in_array((string) $term_id, array_map('strval', (array) $values), true)) {
            return ' checked="checked"';
        }

        if (isset($args['value']) && (string) $args['value'] === (string) $term_id) {
            return ' checked="checked"';
        }

        return '';
    }

    /**
     * Render hierarchical taxonomy checkbox list (recursive).
     *
     * @param array  $terms      Term tree.
     * @param array  $args       Field args.
     * @param mixed  $values     Selected values.
     * @param string $currentVal Current value (by reference).
     * @param int    $depth      Depth level.
     * @param bool   $is_filter  Search filter context.
     * @return string
     */
    private function render_taxonomy_checkbox_terms_list($terms, $args, $values, &$currentVal, $depth = 0, $is_filter = true)
    {
        $html = '';
        $name = '';

        if (!is_array($terms)) {
            return $html;
        }

        foreach ($terms as $term_data) {
            if (empty($term_data) || !is_array($term_data) || empty($term_data['term_id'])) {
                continue;
            }

            $children     = isset($term_data['childern']) && is_array($term_data['childern']) ? $term_data['childern'] : array();
            $has_children = !empty($children);
            $input_id     = $this->cubewp_taxonomy_checkbox_term_input_id($args, $term_data);
            $li_classes   = trim(
                $args['class']
                . ' cwp-taxonomy-depth-' . absint($depth)
                . ($has_children ? ' cwp-taxonomy-has-children' : '')
            );

            $toggle_position = isset($args['taxonomy_toggle_position']) && $args['taxonomy_toggle_position'] === 'left' ? 'left' : 'right';
            $toggle_html     = ($has_children && !empty($args['taxonomy_accordion'])) ? $this->cubewp_render_taxonomy_accordion_toggle($term_data) : '';
            $checkbox_html   = '';

            $checkbox_html .= '<div class="cwp-field-checkbox">';
            $input_attrs = array(
                'type'  => 'checkbox',
                'id'    => $input_id,
                'name'  => $name,
                'value' => $term_data['term_id'],
                'class' => 'custom-control-input ' . $args['class'],
            );
            $checked = $this->cubewp_taxonomy_checkbox_checked_extra($args, $term_data['term_id'], $values, $currentVal, $depth, $is_filter);
            if ($checked !== '') {
                $input_attrs['extra_attrs'] = $checked;
            }
            $checkbox_html .= cwp_render_text_input($input_attrs);
            $checkbox_html .= '<label for="' . $input_id . '">' . esc_html($term_data['term_name']) . '</label>';
            $checkbox_html .= '</div>';

            $html .= '<li class="' . esc_attr($li_classes) . '">';
            $html .= '<div class="cwp-taxonomy-term-head">';
            if ($toggle_position === 'left' && $toggle_html !== '') {
                $html .= $toggle_html;
            }
            $html .= $checkbox_html;
            if ($toggle_position === 'right' && $toggle_html !== '') {
                $html .= $toggle_html;
            }
            $html .= '</div>';

            if ($has_children) {
                $child_ul_class = 'cwp-taxonomy-children-list';
                if (!empty($args['taxonomy_accordion']) && !empty($args['taxonomy_collapsed_default'])) {
                    $child_ul_class .= ' is-collapsed';
                }
                $html .= '<ul class="' . esc_attr($child_ul_class) . '">';
                $html .= $this->render_taxonomy_checkbox_terms_list($children, $args, $values, $currentVal, $depth + 1, $is_filter);
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }

    /**
     * Method cwp_sub_terms_dropdown_output
     *
     * @param array $args
     *
     * @return array
     * @since  1.0.0
     */
    private function cwp_sub_terms_dropdown_output($args = array())
    {
        $output = array();
        foreach ($args as $k => $v) {
            $output[$k] = $v['term_name'];
            foreach ($v['childern'] as $k2 => $v2) {
                $output[$k2] = '-' . $v2['term_name'];
                foreach ($v2['childern'] as $k3 => $v3) {
                    $output[$k3] = '--' . $v3['term_name'];
                }
            }
        }
        return $output;
    }

    /**
     * Method cwp_sub_terms_dropdown_output_id
     *
     * @param array $args
     *
     * @return array
     * @since  1.0.0
     */
    private function cwp_sub_terms_dropdown_output_id($args = array())
    {
        $output = array();
        foreach ($args as $k => $v) {
            $output[$v['term_id']] = $v['term_name'];
            if (isset($v['childern']) && ! empty($v['childern'])) {
                foreach ($v['childern'] as $k2 => $v2) {
                    $output[$v2['term_id']] = '-' . $v2['term_name'];
                    foreach ($v2['childern'] as $k3 => $v3) {
                        $output[$v3['term_id']] = '--' . $v3['term_name'];
                    }
                }
            }
        }
        return $output;
    }
}
new CubeWp_Frontend_Terms_Field();
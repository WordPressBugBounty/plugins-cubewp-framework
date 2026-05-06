<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_Business_hours extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-business-hours-tag';
    }

    public function get_title()
    {
        return esc_html__('Fields Type (Business Hours)', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-fields'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required()
    {
        return true;
    }

    protected function register_controls()
    {
        $this->add_control(
            'field_type',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__('Data Source', 'cubewp-framework'),
                'description' => esc_html__(
                    'Choose whether to display post data or user data (author, post author, or logged-in user depending on context).',
                    'cubewp-framework'
                ),
                'options' => [
                    'post' => esc_html__('Post Fields', 'cubewp-framework'),
                    'user' => esc_html__('User Fields', 'cubewp-framework'),
                ],
                'default' => 'post',
            ]
        );

        $options = get_fields_by_type(array('business_hours'));

        $this->add_control(
            'user_selected_field',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__('Select custom field', 'cubewp-framework'),
                'options' => $options,
                'condition' => [
                    'field_type' => 'post',
                ],
            ]
        );

        $user_field_options = get_user_fields_by_type(array('business_hours'));

        $this->add_control(
            'user_selected_user_field',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__('Select user field', 'cubewp-framework'),
                'options' => $user_field_options,
                'condition' => [
                    'field_type' => 'user',
                ],
            ]
        );

        $this->add_control(
            'open_now_text',
            [
                'type' => \Elementor\Controls_Manager::TEXT,
                'label' => esc_html__('Open Now Text', 'cubewp-framework'),
                'default' => esc_html__('Open now', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'open_now_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__('Open Now Color', 'cubewp-framework'),
                'default' => '#000000',
            ]
        );
        $this->add_control(
            'closed_now_text',
            [
                'type' => \Elementor\Controls_Manager::TEXT,
                'label' => esc_html__('Closed Now Text', 'cubewp-framework'),
                'default' => esc_html__('Closed now', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'closed_now_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__('Closed Now Color', 'cubewp-framework'),
                'default' => '#ff0000',
            ]
        );
        $this->add_control(
            '24_hours_open_text',
            [
                'type' => \Elementor\Controls_Manager::TEXT,
                'label' => esc_html__('24 Hours Open Text', 'cubewp-framework'),
                'default' => esc_html__('24 hours open', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            '24_hours_open_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__('24 Hours Open Color', 'cubewp-framework'),
                'default' => '#00ff00',
            ]
        );
        $this->add_control(
            'day_off_text',
            [
                'type' => \Elementor\Controls_Manager::TEXT,
                'label' => esc_html__('Day Off Text', 'cubewp-framework'),
                'default' => esc_html__('Day off', 'cubewp-framework'),
            ]
        );
        $this->add_control(
            'day_off_color',
            [
                'type' => \Elementor\Controls_Manager::COLOR,
                'label' => esc_html__('Day Off Color', 'cubewp-framework'),
                'default' => '#999999',
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label' => esc_html__('Show Icon with Text', 'cubewp-framework'),
                'label_on' => esc_html__('Yes', 'cubewp-framework'),
                'label_off' => esc_html__('No', 'cubewp-framework'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'icon_position',
            [
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label' => esc_html__('Icon Position', 'cubewp-framework'),
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'cubewp-framework'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'cubewp-framework'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'above' => [
                        'title' => esc_html__('Above', 'cubewp-framework'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'below' => [
                        'title' => esc_html__('Below', 'cubewp-framework'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'left',
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_gap',
            [
                'label' => __('Gap', 'plugin-name'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'condition' => [
                    'show_icon' => 'yes',
                ],
            ]
        );
    }

    public function render()
    {
        $field_type = $this->get_settings('field_type');
        $field_type = isset($field_type) ? $field_type : 'post';
        if ('user' === $field_type) {
            $field = $this->get_settings('user_selected_user_field');
        } else {
            $field = $this->get_settings('user_selected_field');
        }
        $open_now_text = $this->get_settings('open_now_text');
        $open_now_color = $this->get_settings('open_now_color');
        $closed_now_text = $this->get_settings('closed_now_text');
        $closed_now_color = $this->get_settings('closed_now_color');
        $hours_open_text = $this->get_settings('24_hours_open_text');
        $hours_open_color = $this->get_settings('24_hours_open_color');
        $day_off_text = $this->get_settings('day_off_text');
        $day_off_color = $this->get_settings('day_off_color');
        $show_icon = $this->get_settings('show_icon') === 'yes';
        $icon_gap = $this->get_settings('icon_gap');
        $icon_position = $this->get_settings('icon_position');
        $icon_position = !empty($icon_position) ? $icon_position : 'left';
        $gap_value = isset($icon_gap['size']) ? $icon_gap['size'] . $icon_gap['unit'] : '6px';
        if (! $field) {
            return;
        }

        // Get post ID - handle Elementor editing and loop contexts
        if ('user' === $field_type) {
            $user_id = cubewp_get_elementor_tag_user_id();
            if (empty($user_id)) {
                return;
            }
            $value = get_user_meta($user_id, $field, true);
            if (empty($value) || ! is_array($value)) {
                return;
            }
        } else {
            if (cubewp_is_elementor_editing()) {
                $post_id = cubewp_get_elementor_preview_post_id();
            } else {
                global $post;
                if (isset($post) && !empty($post->ID)) {
                    $post_id = (int) $post->ID;
                } else {
                    $post_id = (int) get_queried_object_id();
                }
            }

            if (empty($post_id)) {
                return;
            }

            $value = get_post_meta($post_id, $field, true);
        }

        if (empty($value) || ! is_array($value)) {
            return;
        }

        // Use existing function to get status 
        $status = $this->cwp_business_hours_status_tag($value);

        if ($status) {
            $output = '';
            if ($status == 'open') {
                $text = !empty($open_now_text) ? $open_now_text : esc_html__("Open now", "cubewp-framework");
                $color = !empty($open_now_color) ? $open_now_color : '#000000';
                $output = $this->format_status_output($text, $color, $show_icon, $icon_position, $gap_value);
            } else if ($status == 'closed') {
                $text = !empty($closed_now_text) ? $closed_now_text : esc_html__("Closed now", "cubewp-framework");
                $color = !empty($closed_now_color) ? $closed_now_color : '#ff0000';
                $output = $this->format_status_output($text, $color, $show_icon, $icon_position, $gap_value);
            } else if ($status == '24_hours_open') {
                $text = !empty($hours_open_text) ? $hours_open_text : esc_html__("24 hours open", "cubewp-framework");
                $color = !empty($hours_open_color) ? $hours_open_color : '#00ff00';
                $output = $this->format_status_output($text, $color, $show_icon, $icon_position, $gap_value);
            } else if ($status == 'day_off') {
                $text = !empty($day_off_text) ? $day_off_text : esc_html__("Day off", "cubewp-framework");
                $color = !empty($day_off_color) ? $day_off_color : '#999999';
                $output = $this->format_status_output($text, $color, $show_icon, $icon_position, $gap_value);
            }

            if (!empty($output)) {
                echo wp_kses_post($output);
            }
        }
    }
    /**
     * Format status output with optional icon.
     *
     * @param string $text          The status text.
     * @param string $color         The color for both icon and text.
     * @param bool   $show_icon     Whether to show the clock icon.
     * @param string $icon_position Icon position: left, right, above, below.
     * @return string HTML output.
     */
    private function format_status_output($text, $color, $show_icon, $icon_position = 'below', $gap = '6px')
    {
        $style = 'color: ' . esc_attr($color) . ';';
        if ($show_icon) {
            $icon_html = '<i class="fa fa-clock-o" style=" margin-right:' . esc_attr($gap) . ';color: ' . esc_attr($color) . ';" aria-hidden="true"></i>';
            $text_html = '<span>' . esc_html($text) . '</span>';
            $style .= ' display: inline-flex; align-items: center; gap: 6px;';
            if (in_array($icon_position, ['above', 'below'], true)) {
                $style .= ' flex-direction: column; align-items: flex-start;';
                $order = ($icon_position === 'above') ? $icon_html . $text_html : $text_html . $icon_html;
            } else {
                $order = ($icon_position === 'right') ? $text_html . $icon_html : $icon_html . $text_html;
            }
            return '<span style="' . $style . '">' . $order . '</span>';
        }
        return '<span style="' . $style . '">' . esc_html($text) . '</span>';
    }

    public function cwp_business_hours_status_tag($schedule)
    {

        if (!is_array($schedule) || empty($schedule)) return;

        // Get the WordPress timezone
        $timezone = wp_timezone_string();

        // Check if the timezone is valid
        if (empty($timezone)) {
            $timezone = 'UTC'; // Default to UTC if no timezone is set in WordPress
        }

        // Create a DateTime object and set the timezone
        $currentDateTime = new DateTime('now', new DateTimeZone($timezone));

        // Get the current day and time in WordPress timezone
        $currentDay = strtolower($currentDateTime->format('l'));
        $currentTime = $currentDateTime->format('H:i:s');

        if (array_key_exists($currentDay, $schedule)) {
            $isOpen = false;
            $is24Hours = false;
            $times = $schedule[$currentDay];

            if (!is_array($times) && is_string($times) && $times == '24-hours-open') {
                $isOpen = true;
                $is24Hours = true;
            } else {
                $openTimes = $times['open'];
                $closeTimes = $times['close'];
                // Check if the current time falls within any open and close period
                for ($i = 0; $i < count($openTimes); $i++) {
                    $openTime = $openTimes[$i];
                    $closeTime = $closeTimes[$i];

                    if ($currentTime >= $openTime && $currentTime <= $closeTime) {
                        $isOpen = true;
                        break;
                    }
                }
            }

            if ($is24Hours) {
                return '24_hours_open';
            } elseif ($isOpen) {
                return 'open';
            } else {
                return 'closed';
            }
        } else {
            // Current day is not in the schedule - it's a day off
            return 'day_off';
        }
    }
}

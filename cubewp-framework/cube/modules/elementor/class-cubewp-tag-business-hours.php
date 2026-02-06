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
        return esc_html__('Business Hours Status', 'cubewp-framework');
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
        $options = get_fields_by_type(array('business_hours'));

        $this->add_control(
            'user_selected_field',
            [
                'type' => \Elementor\Controls_Manager::SELECT,
                'label' => esc_html__('Select Business Hours Field', 'cubewp-framework'),
                'options' => $options,
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
    }

    public function render()
    {
        $field = $this->get_settings('user_selected_field');
        $open_now_text = $this->get_settings('open_now_text');
        $open_now_color = $this->get_settings('open_now_color');
        $closed_now_text = $this->get_settings('closed_now_text');
        $closed_now_color = $this->get_settings('closed_now_color');
        $hours_open_text = $this->get_settings('24_hours_open_text');
        $hours_open_color = $this->get_settings('24_hours_open_color');
        $day_off_text = $this->get_settings('day_off_text');
        $day_off_color = $this->get_settings('day_off_color');
        if (! $field) {
            return;
        }

        // Get post ID - handle Elementor editing and loop contexts
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
                $output = '<span style="color: ' . esc_attr($color) . ';">' . esc_html($text) . '</span>';
            } else if ($status == 'closed') {
                $text = !empty($closed_now_text) ? $closed_now_text : esc_html__("Closed now", "cubewp-framework");
                $color = !empty($closed_now_color) ? $closed_now_color : '#ff0000';
                $output = '<span style="color: ' . esc_attr($color) . ';">' . esc_html($text) . '</span>';
            } else if ($status == '24_hours_open') {
                $text = !empty($hours_open_text) ? $hours_open_text : esc_html__("24 hours open", "cubewp-framework");
                $color = !empty($hours_open_color) ? $hours_open_color : '#00ff00';
                $output = '<span style="color: ' . esc_attr($color) . ';">' . esc_html($text) . '</span>';
            } else if ($status == 'day_off') {
                $text = !empty($day_off_text) ? $day_off_text : esc_html__("Day off", "cubewp-framework");
                $color = !empty($day_off_color) ? $day_off_color : '#999999';
                $output = '<span  style="color: ' . esc_attr($color) . ';">' . esc_html($text) . '</span>';
            }

            if (!empty($output)) {
                echo wp_kses_post($output);
            }
        }
    }
    public  function cwp_business_hours_status_tag($schedule)
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

<?php
/**
 * CubeWp Business hours field 
 *
 * @version 1.1.15
 * @package cubewp/cube/fields/frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Frontend_Business_Hours_Field
 */
class CubeWp_Frontend_Business_Hours_Field extends CubeWp_Frontend {

    private static $timings = [];
    private static function cwp_format_business_display_time($time)
    {
        if (empty($time) || $time === '24-hours-open') {
            return '';
        }
        $timestamp = strtotime($time);
        if (!$timestamp) {
            return '';
        }
        return date_i18n('h:i A', $timestamp);
    }

    public function __construct( ) {
        add_filter('cubewp/frontend/business_hours/field', array($this, 'render_business_hours_frontend'), 10, 2);
    }

    /**
     * Method cwp_booking_days
     *
     * @return string
     * @since  1.0.0
     */
    public static function cwp_booking_days()
    {
        return json_encode(array(
            'label' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
            'value' => array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
        ));
    }
    
    /**
     * Method render_business_hours_frontend
     *
     * @param string $output
     * @param array $args
     *
     * @return string
     * @since  1.0.0
     */
    public static function render_business_hours_frontend($output = '', $args = array())
    {
        wp_enqueue_script('cwp-business-hours-fields');
        global $cubewp_frontend;
        $args          = apply_filters('cubewp/frontend/field/parametrs', $args);
        $output = $cubewp_frontend::cwp_frontend_post_field_container($args);
        $output .= $cubewp_frontend::cwp_frontend_field_label($args);
        $args['options']       = self::cwp_booking_days();
        $field_name = $args['name'];
        $field_id = $args['id'];
        $business_hours = $args['value'];
        $output .= '<div class="yb-business-hours-display">';
        if (is_array($business_hours) && !empty($business_hours)) {
            foreach ($business_hours as $day => $business_hour) {

                if(!is_array($business_hour) && is_string($business_hour) && $business_hour == '24-hours-open'){
                    self::$timings = [
                        'fullday' => __('24 hours open', 'cubewp-framework'),
                        'open_time' => '24-hours-open',
                        'close_time' => '24-hours-open',
                        'start_time' => '',
                        'end_time' => '',
                        'day' => $day,
                        'fullhoursclass' => 'fullhours',
                        'field_name' => $field_name,
                        'dash' => '',
                        'meta_open' => '',
                        'meta_close' => '',
                    ];
                    $output .= self::business_hours_display_render();
                }else{
                    if ( empty($fullhoursclass) && !empty($business_hour['open']) && !empty($business_hour['close'])) {
                        $open_time = $business_hour['open'];
                        $close_time = $business_hour['close'];
                        for ($i = 0; $i < count($open_time); $i++) {
                            self::$timings = [
                                'fullday' => '',
                                'open_time' => $open_time[$i],
                                'close_time' => $close_time[$i],
                                'start_time' => self::cwp_format_business_display_time($open_time[$i]),
                                'end_time' => self::cwp_format_business_display_time($close_time[$i]),
                                'day' => $day,
                                'fullhoursclass' => '',
                                'field_name' => $field_name,
                                'dash' => ' - ',
                                'meta_open' => '[open][]',
                                'meta_close' => '[close][]',
                            ];
                            $output .= self::business_hours_display_render();
                        }
                        
                    }
                }
                
            }
        }
        $output .= '</div>';
        $default_day = 'monday';
        $default_open_time = '09:00';
        $default_close_time = '17:00';
        $args['value'] = '';
        $output .= '<div class="yb-business-hours-fields">';
        $args['type'] = 'dropdown';
        $args['name'] = $field_name . '_day';
        $args['id'] = $field_id . '_day';
        $args['custom_name'] = $field_name . '_day';
        $args['label'] = '';
        $args['placeholder'] = esc_html__('Select Day', 'cubewp-framework');
        $args['field_size'] = 'size-1-3';
        $args['class'] = 'business-days';
        $args['value'] = $default_day;
        $output .= apply_filters("cubewp/frontend/dropdown/field", $output, $args);
        $args['name'] = $field_name . '_open_time';
        $args['id'] = $field_id . '_open_time';
        $args['custom_name'] = $field_name . '_open_time';
        $args['type'] = 'time_picker';
        $args['label'] = '';
        $args['placeholder'] = esc_html__('Open Time', 'cubewp-framework');
        $args['field_size'] = 'size-1-3';
        $args['class'] = 'business-open-time';
        $args['value'] = $default_open_time;
        $output .= apply_filters("cubewp/frontend/time_picker/field", $output, $args);
        $args['name'] = $field_name . '_close_time';
        $args['id'] = $field_id . '_close_time';
        $args['custom_name'] = $field_name . '_close_time';
        $args['label'] = '';
        $args['placeholder'] = esc_html__('Close Time', 'cubewp-framework');
        $args['field_size'] = 'size-1-3';
        $args['class'] = 'business-close-time';
        $args['value'] = $default_close_time;
        $output .= apply_filters("cubewp/frontend/time_picker/field", $output, $args);
        $output .= '<div class="yb_business_hour_fulldayopen">
                        <input type="checkbox" id="yb_fulldayopen" class="yb_fulldayopen">
                        <label>' . esc_html__('24 Hours', 'cubewp-framework') . '</label>
                    </div>';
        $output .= '</div>';
        $output .= '<button class="cwp-add-new-business-hour" data-id="' . $field_id . '" data-name="' . $field_name . '"  data-fullday="' . __('24 hours open', 'cubewp-framework') . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                        </svg>
                    </button>';
        $output .= '</div>';
        return apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);
    }

    private static function business_hours_display_render(){
        $timings = self::$timings;
        $output = '<div class="business-hours ' . $timings['day'] . ' ' . $timings['day'] . '-' . $timings['field_name'] . ' ' . $timings['fullhoursclass'] . ' ">
                    <div class="day-hours">
                    <span class="weekday">' . ucfirst($timings['day']) . '</span>
                    <span class="start-end fullday">' . $timings['fullday'] . '</span>
                    <span class="open">' . $timings['start_time'] . '</span>
                    <span class="dash">' . $timings['dash'] . '</span>
                    <span class="close">' . $timings['end_time'] . '</span>
                    <a class="remove-business-hours" href="#" data-field_name ="' . $timings['field_name'] . '" data-weekday ="' . $timings['day'] . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"></path></svg>
                    </a>';
                    
        $output .= '<input class="' . $timings['day'] . '-open" name="cwp_user_form[cwp_meta][' . $timings['field_name'] . '][' . $timings['day'] . ']' .$timings['meta_open'] . '" value="' . $timings['open_time'] . '" type="hidden">
        <input class="' . $timings['day'] . '-close" name="cwp_user_form[cwp_meta][' . $timings['field_name'] . '][' . $timings['day'] . ']' .$timings['meta_close'] . '" value="' . $timings['close_time'] . '" type="hidden">';
    
        $output .= '</div></div>';
        return $output;
    }
    
}

new CubeWp_Frontend_Business_Hours_Field();
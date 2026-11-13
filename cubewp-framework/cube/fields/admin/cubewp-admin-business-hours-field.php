<?php
/**
 * CubeWp Business hours field 
 *
 * @version 1.1.15
 * @package cubewp/cube/fields/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Admin_Business_Hours_Field
 */
class CubeWp_Admin_Business_Hours_Field extends CubeWp_Frontend {

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
        add_filter('cubewp/admin/post/business_hours/field', array($this, 'render_business_hours_admin'), 10, 2);
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
     * Method render_business_hours_admin
     *
     * @param string $output
     * @param array $args
     *
     * @return string
     * @since  1.0.0
     */
    public static function render_business_hours_admin($output = '', $args = array())
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('cwp-timepicker');
        wp_enqueue_style('cwp-timepicker');
        wp_enqueue_script('cubewp-business-hour');
        wp_enqueue_style('yellow-books-admin-styles');
        $args = apply_filters('cubewp/admin/field/parametrs', $args);
        if ($args['wrap'] != true) {
            return '';
        }
        $output = CubeWp_Admin::cwp_field_wrap_start($args);
        $output .= '<div class="cwp-field-business_hours">';
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
                        /* A day may store one slot as a scalar ('09:00') or several as
                         * an array. Normalise both to a list before counting: count() on
                         * a string is a TypeError in PHP 8 and took the edit screen down.
                         * array_values() also re-indexes gappy keys left by a removed slot,
                         * which $open_time[$i] would otherwise miss. */
                        $open_time = array_values((array) $business_hour['open']);
                        $close_time = array_values((array) $business_hour['close']);
                        $slot_count = min(count($open_time), count($close_time));
                        for ($i = 0; $i < $slot_count; $i++) {
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
        $output .= '<div class="yb-business-hour-fields">';
        $args['type'] = 'dropdown';
        $args['name'] = $args['custom_name'] = $field_name . '_day';
        $args['id'] = $field_id . '_day';
        $args['placeholder'] = esc_html__('Select Day', 'cubewp-framework');
        $args['class'] = 'business-days';
        $args['value'] = $default_day;
        $output .= self::yellow_books_render_dropdown_admin($args);

        $args['name'] = $args['custom_name'] = $field_name . '_open_time';
        $args['id'] = $field_id . '_open_time';
        $args['type'] = 'time_picker';
        $args['placeholder'] = esc_html__('Open Time', 'cubewp-framework');
        $args['class'] = 'business-open-time';
        $args['value'] = $default_open_time;
        $output .= self::yellow_books_render_timepicker_admin($args);

        $args['name'] = $args['custom_name'] = $field_name . '_close_time';
        $args['id'] = $field_id . '_close_time';
        $args['placeholder'] = esc_html__('Close Time', 'cubewp-framework');
        $args['admin_size'] = '1/3';
        $args['class'] = 'business-close-time';
        $args['value'] = $default_close_time;
        $output .= self::yellow_books_render_timepicker_admin($args);

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
        $output      .= '</div></div>';
        $output .= CubeWp_Admin::cwp_field_wrap_end($args);

        return apply_filters("cubewp/admin/{$args['name']}/field", $output, $args);
    }

    private static function business_hours_display_render(){
        
        $timings = self::$timings;

        /* Security: the day key and field name are stored values and land in
         * attribute and text contexts, so they are escaped here rather than
         * trusted. The write path also constrains day keys to week-day
         * literals; this is the matching output-side guarantee. */
        $day_attr        = esc_attr( $timings['day'] );
        $field_name_attr = esc_attr( $timings['field_name'] );

        if ( current_cubewp_page() == 'cubewp_settings'){
            $open_name = 'name="' . $field_name_attr . '[' . $day_attr . ']' .$timings['meta_open'] . '"';
            $close_name = 'name="' . $field_name_attr . '[' . $day_attr . ']' .$timings['meta_close'] . '"';
        }else{
            $open_name = 'name="cwp_meta[' . $field_name_attr . '][' . $day_attr . ']' .$timings['meta_open'] . '"';
            $close_name = 'name="cwp_meta[' . $field_name_attr . '][' . $day_attr . ']' .$timings['meta_close'] . '"';
        }

        $output = '<div class="business-hours ' . $day_attr . ' ' . $day_attr . '-' . $field_name_attr . ' ' . esc_attr( $timings['fullhoursclass'] ) . ' ">
                    <div class="day-hours">
                    <span class="weekday">' . esc_html( ucfirst( $timings['day'] ) ) . '</span>
                    <span class="start-end fullday">' . esc_html( $timings['fullday'] ) . '</span>
                    <span class="open">' . esc_html( $timings['start_time'] ) . '</span>
                    <span class="dash">' . esc_html( $timings['dash'] ) . '</span>
                    <span class="close">' . esc_html( $timings['end_time'] ) . '</span>
                    <a class="remove-business-hours" href="#" data-field_name ="' . $field_name_attr . '" data-weekday ="' . $day_attr . '">
                        <span class="dashicons dashicons-no"></span>
                    </a>';

        $output .= '<input class="' . $day_attr . '-open" '.$open_name.' value="' . esc_attr( $timings['open_time'] ) . '" type="hidden">
        <input class="' . $day_attr . '-close" '.$close_name.' value="' . esc_attr( $timings['close_time'] ) . '" type="hidden">';

        $output .= '</div></div>';
        return $output;
    }

    /**
     * Method yellow_books_render_timepicker_admin
     *
     * @param array $args
     *
     * @return string
     * @since  1.0.0
     */
    public static function yellow_books_render_timepicker_admin($args)
    {
        $output = '';
        $output .= '<div class="cwp-time-picker cwp-field-time_picker  yb-business-hour-field-admin">';
        $input_attrs = array(
            'id'           => $args['id'],
            'class'        => $args['class'] . '',
            'name'         => '',
            'value'        => $args['value'],
            'placeholder'  => $args['placeholder']
        );
        $extra_attrs = isset($args['extra_attrs']) ? $args['extra_attrs'] : '';
        if (isset($args['required']) && $args['required'] == 1) {
            $input_attrs['class'] .= ' required';
            $validation_msg = isset($args['validation_msg']) ? $args['validation_msg'] : '';
            $extra_attrs .= ' data-validation_msg="' . $validation_msg . '"';
        }
        $input_attrs['extra_attrs'] = $extra_attrs;

        $output .= cwp_render_text_input($input_attrs);

        $input_attrs = array(
            'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'value'        => $args['value']
        );
        $output .= cwp_render_hidden_input($input_attrs);
        $output .= '</div>';
        return $output;
    }

    /**
     * Method yellow_books_render_dropdown_admin
     *
     * @param array $args
     *
     * @return string
     * @since  1.0.0
     */
    public static function yellow_books_render_dropdown_admin($args)
    {
        $output = '';
        $output .= '<div class="yb-business-hour-field-admin">';
        $input_attrs = array(
            'options'     => isset($args['options']) ? $args['options'] : '',
            'id'          => $args['id'],
            'class'       => $args['class'],
            'name'        => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'value'       => $args['value'],
            'placeholder' => $args['placeholder']
        );
        $extra_attrs = isset($args['extra_attrs']) ? $args['extra_attrs'] : '';
        if (isset($args['required']) && $args['required'] == 1) {
            $input_attrs['class'] .= ' required';
            $validation_msg       = isset($args['validation_msg']) ? $args['validation_msg'] : '';
            $extra_attrs          .= ' data-validation_msg="' . $validation_msg . '" ';
        }
        $output .= cwp_render_dropdown_input($input_attrs);
        $output .= '</div>';
        return $output;
    }

}
new CubeWp_Admin_Business_Hours_Field();
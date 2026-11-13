<?php
/**
 * CubeWp admin datepicker field 
 *
 * @version 1.0
 * @package cubewp/cube/fields/frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Frontend_Date_Picker_Field
 */
class CubeWp_Frontend_Date_Picker_Field extends CubeWp_Frontend {
    
    public function __construct( ) {
        add_filter('cubewp/frontend/date_picker/field', array($this, 'render_date_picker_field'), 10, 2);
        
        add_filter('cubewp/user/registration/date_picker/field', array($this, 'render_date_picker_field'), 10, 2);
        add_filter('cubewp/user/profile/date_picker/field', array($this, 'render_date_picker_field'), 10, 2);
        
        add_filter('cubewp/search_filters/date_picker/field', array($this, 'render_search_date_picker_field'), 10, 2);
        add_filter('cubewp/frontend/search/date_picker/field', array($this, 'render_search_date_picker_field'), 10, 2);
    }
        
    /**
     * Method render_date_picker_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_date_picker_field( $output = '', $args = array() ) {

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('cubewp-datepicker');

        $args           =  apply_filters( 'cubewp/frontend/field/parametrs', $args );
        $required       = self::cwp_frontend_field_required($args['required']);
        $required       = !empty($required['class']) ? $required['class'] : '';
        $output         = self::cwp_frontend_post_field_container($args);

        if($args['value'] != '' && is_numeric($args['value'])){
            $args['value'] = wp_date(get_option('date_format'), $args['value']);
        }
            $output .= self::cwp_frontend_field_label($args);
            $input_attrs = array( 
                'type'         =>    'text',
                'id'           =>    $args['id'],
                'class'        =>    'form-control '. $args['class'].' '.$required,
                'name'         =>    !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
                'value'        =>    $args['value'],
                'placeholder'  =>    $args['placeholder'],
                'extra_attrs'  =>    'autocomplete="off"'
            );
            $output .= cwp_render_text_input( $input_attrs );
            
            $input_attrs = array( 
                'name'         => !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
                'value'        => $args['value']
            );
            $output .= cwp_render_hidden_input( $input_attrs );

        $output .= '</div>';
        
        $output =  CubeWp_Frontend::cwp_field_output_filter($output, $args);
        
        return $output;
    }

        
    /**
     * Method render_search_date_picker_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_search_date_picker_field( $output = '', $args = array() ) {
	    wp_enqueue_style( 'cwp-jquery-ui' );
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('cubewp-datepicker');

	    $value = $args['value'] ?? '';
	    $value = explode('-', $value);
		$fromdate = $todate = '';
        if(isset($value[0])){
	        $fromdate = sanitize_text_field($value[0]);
        }
	    if(isset($value[1])){
	        $todate = sanitize_text_field($value[1]);
        }

        $args   =  apply_filters( 'cubewp/frontend/field/parametrs', $args );

        // Per-field style selector saved in the Search Form builder. Default
        // behaviour is unchanged for any saved field that does not carry the
        // key; Style 1 is signalled to the frontend JS via a data attribute on
        // the wrapper so the init can choose the themed popup, scoped to this
        // one wrapper. CSS is injected only when the style is actually used,
        // so Default forms stay a byte lighter.
        $date_picker_style = isset($args['date_picker_style']) && !empty($args['date_picker_style'])
            ? sanitize_html_class($args['date_picker_style'])
            : 'default';
        if ($date_picker_style !== 'default' && $date_picker_style !== 'style_1') {
            $date_picker_style = 'default';
        }
        $wrap_classes = 'cubewp-date-range-picker';
        $primary_color = isset($args['date_picker_primary_color']) && !empty($args['date_picker_primary_color'])
            ? trim((string) $args['date_picker_primary_color'])
            : '#007bff';
        if ($date_picker_style === 'style_1') {
            $wrap_classes .= ' cubewp-date-range-picker--style-1';
            self::cwp_dp_style_1_enqueue_inline_css();
        }
        $wrap_data_attr = ' data-cwp-dp-style="' . esc_attr($date_picker_style) . '"'
                        . ' data-cwp-dp-primary="' . esc_attr($primary_color) . '"';

	    $output = self::cwp_frontend_search_field_container($args);
	    $output .= self::cwp_frontend_search_field_label($args);
	    $output .= '<div class="' . esc_attr($wrap_classes) . '"' . $wrap_data_attr . '>'."\n";
	    $output .= '<div class="cubewp-date-range-picker-field">'."\n";
	    $output .= '<input value="' . $fromdate . '" type="cwp-date-range" class="cubewp-date-range-picker-from '. $args['class'].'" placeholder="'.esc_html__( 'From', 'cubewp-framework' ).'" id="'. esc_attr($args['name']) .'-from">'."\n";
	    $output .= '</div>'."\n";
	    $output .= '<span class="cubewp-date-range-picker-field-seprator"> - </span>'."\n";
	    $output .= '<div class="cubewp-date-range-picker-field">'."\n";
	    $output .= '<input value="' . $todate . '" type="cwp-date-range" class="cubewp-date-range-picker-to '. $args['class'].'" placeholder="'.esc_html__( 'To', 'cubewp-framework' ).'" id="'. esc_attr($args['name']) .'-to">'."\n";
	    $output .= '</div>'."\n";
	    $output .= '<input class="cubewp-date-range-picker-input" type="hidden" name="'. esc_attr($args['name']) .'" value="'. esc_attr($args['value']) .'">'."\n";
	    $output .= '</div>'."\n";
	    $output .= '</div>';
        $output =  apply_filters("cubewp/frontend/search/{$args['name']}/field", $output, $args);

        return $output;
    }

    /**
     * Enqueue Style 1 CSS + a one-shot JS init that:
     *   - opens the popup with one compact month
     *   - adds a scoped class (`cubewp-dp-style-1`) to the jQuery UI popup via
     *     beforeShow, so the inline CSS only paints Style 1 pickers and never
     *     bleeds onto other datepickers on the page
     *   - paints the From..To range via beforeShowDay
     *
     * Runs at most once per request — the static guard ensures multiple Style 1
     * fields on the same page share one asset instead of duplicating it.
     *
     * NOTE ON !important AND THE DOUBLED SELECTORS
     * Three shipped stylesheets paint `.ui-datepicker` unscoped and win against
     * a plain `.ui-datepicker.cubewp-dp-style-1` rule:
     *   1. frontend-fields.css:1052 — only applies once frontend-fields.js:282
     *      wraps #ui-datepicker-div in `.cwp-ui-datepicker`, which happens on
     *      search/listing/home pages but not on WooCommerce cart. That is why
     *      the same popup looked correct on cart and wrong elsewhere. Every
     *      structural rule below is therefore written twice — once with the
     *      `.cwp-ui-datepicker` prefix, once without.
     *   2. cubewp-listings-frontend.css:4877+ — unscoped `.ui-datepicker*`.
     *   3. cwp-booking-frontend.css:438 — an `.ui-state-active::after` blue
     *      circle (30px, z-index:-1) that is a separate element, not a border,
     *      outline or shadow, which is why resetting those never removed it.
     */
    protected static function cwp_dp_style_1_enqueue_inline_css()
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;

        $css = <<<CSS
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1,
.ui-datepicker.cubewp-dp-style-1{
    --cwp-dp-primary:#007bff;
    --cwp-dp-primary-light:rgba(0,123,255,.18);
    border:1px solid #ececec !important;border-radius:16px !important;
    padding:16px 20px !important;background:#fff !important;
    box-shadow:0 10px 30px rgba(0,0,0,.08) !important;
    width:auto !important;max-width:none !important;min-width:300px;
    font-size:13px !important;font-family:inherit;z-index:999999 !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-header,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-header{
    background:transparent !important;border:0 !important;border-bottom:0 !important;
    color:#111;padding:0 36px 10px !important;position:relative;min-height:30px;
    display:block !important;margin:0 0 4px 0 !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title{
    font-weight:600;color:#111 !important;text-align:center;letter-spacing:.06em;
    text-transform:uppercase;white-space:nowrap;line-height:30px !important;
    margin:0 !important;padding:0 !important;width:auto !important;
    display:block !important;gap:0 !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title select,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title input,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title .ui-datepicker-month,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title .ui-datepicker-year,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title select,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title input,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title .ui-datepicker-month,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-title .ui-datepicker-year{
    border:0 !important;background:transparent !important;color:inherit !important;
    padding:0 4px !important;margin:0 !important;box-shadow:none !important;
    outline:none;width:auto !important;height:auto !important;
    border-radius:0 !important;display:inline !important;font:inherit !important;
    line-height:inherit !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next{
    position:absolute !important;top:0 !important;margin:0 !important;
    width:28px !important;height:30px !important;
    background:transparent !important;border:0 !important;cursor:pointer;
    overflow:hidden;text-indent:-9999px;font-size:0 !important;
    line-height:0 !important;color:transparent !important;z-index:2;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev{left:4px !important;right:auto !important;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next{right:4px !important;left:auto !important;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev::before,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next::before{
    content:'';position:absolute;top:50%;left:50%;width:9px;height:9px;
    border-top:2px solid var(--cwp-dp-primary,#007bff);
    border-right:2px solid var(--cwp-dp-primary,#007bff);
    transform-origin:center;text-indent:0;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev::before{
    transform:translate(-30%,-50%) rotate(-135deg);
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next::before{
    transform:translate(-70%,-50%) rotate(45deg);
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev span,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next span,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev span,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next span{display:none !important;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev:hover,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next:hover,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-prev:hover,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-next:hover{background:transparent !important;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar{
    border-collapse:separate;border-spacing:0;width:auto;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tbody,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tfoot,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar thead,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tr,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar th,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tbody,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tfoot,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar thead,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar tr,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar th{
    padding:0 !important;border:0 !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar th,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar th{
    color:#8c8c8c;font-weight:500;text-align:center;font-size:12px;
    padding:6px 0 !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td{
    text-align:center;position:relative;background:transparent;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-in-range::before,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-start::before,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-end::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-in-range::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-start::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-end::before{
    content:'';position:absolute;top:50%;left:0;right:0;height:26px;
    transform:translateY(-50%);background:var(--cwp-dp-primary-light,rgba(0,123,255,.18));
    z-index:0;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-start::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-start::before{left:50%;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-end::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-range-end::before{right:50%;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td span,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td span{
    display:inline-flex;align-items:center;justify-content:center;
    width:34px;height:34px;border-radius:50%;border:0 !important;
    background:transparent !important;color:#111 !important;text-align:center;
    transition:background .15s ease,color .15s ease;font-size:13px;
    text-decoration:none;margin:0 auto;padding:0;position:relative;z-index:1;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td span,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td span{color:#c7c7c7 !important;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:hover,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:hover{
    background:var(--cwp-dp-primary-light,rgba(0,123,255,.18)) !important;
    color:var(--cwp-dp-primary,#007bff) !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.ui-datepicker-current-day a,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-endpoint a,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.ui-datepicker-current-day a,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-endpoint a{
    background:var(--cwp-dp-primary,#007bff) !important;color:#fff !important;
    background-image:none !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-in-range a,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td.cwp-dp-in-range a{
    background:transparent !important;background-image:none !important;
    color:var(--cwp-dp-primary,#007bff) !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a::before,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a::after,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active::after,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a::before,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a::after,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active::after{
    content:none !important;background:transparent !important;display:none !important;
    width:0 !important;height:0 !important;border:0 !important;box-shadow:none !important;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-hover,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-focus,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-highlight,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-default,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-visual-focus,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:hover,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:focus,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:focus-visible,
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:active,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-hover,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-focus,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-highlight,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-default,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-visual-focus,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a.ui-state-active,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:hover,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:focus,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:focus-visible,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-calendar td a:active{
    border:0 !important;outline:0 none !important;box-shadow:none !important;
    -webkit-tap-highlight-color:transparent;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1.ui-datepicker-multi,
.ui-datepicker.cubewp-dp-style-1.ui-datepicker-multi{width:auto !important;display:inline-block;}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1.ui-datepicker-multi .ui-datepicker-group,
.ui-datepicker.cubewp-dp-style-1.ui-datepicker-multi .ui-datepicker-group{
    padding:0 10px;float:left;width:auto;
}
.cwp-ui-datepicker .ui-datepicker.cubewp-dp-style-1 .ui-datepicker-group table,
.ui-datepicker.cubewp-dp-style-1 .ui-datepicker-group table{width:auto;}
CSS;

        // JS: on-the-fly init override. The existing cwp-search.js already
        // creates a datepicker on `.cubewp-date-range-picker-from/-to`. For
        // wrappers tagged Style 1 we re-init with the compact single-month
        // layout, attach a beforeShow that adds the themed class and pushes the
        // operator's colour in as CSS variables, and a beforeShowDay that marks
        // the range. Running after cwp-search.js (document-ready + a 0ms
        // setTimeout) means we always win the race without touching that file.
        $js = <<<'JS'
(function($){
    function toDate(str){
        if (!str) return null;
        var p = str.split('/');  // mm/dd/yy
        if (p.length !== 3) return null;
        var d = new Date(parseInt(p[2],10) < 100 ? 2000+parseInt(p[2],10) : parseInt(p[2],10),
                         parseInt(p[0],10)-1, parseInt(p[1],10));
        return isNaN(d.getTime()) ? null : d;
    }
    function sameDay(a,b){
        return a && b && a.getFullYear()===b.getFullYear()
            && a.getMonth()===b.getMonth() && a.getDate()===b.getDate();
    }
    // Let the browser parse whatever CSS colour the operator typed/picked, so
    // hex, named and rgb()/rgba() all work and a bad value falls back to blue
    // instead of breaking the popup.
    function parseCssColor(input){
        if (!input) return null;
        var s = String(input).trim();
        if (!s) return null;
        var d = document.createElement('div');
        d.style.color = '';
        d.style.color = s;
        if (!d.style.color) return null;
        document.body.appendChild(d);
        var cs = window.getComputedStyle(d).color;
        document.body.removeChild(d);
        var m = cs.match(/rgba?\((\d+)[,\s]+(\d+)[,\s]+(\d+)/i);
        if (!m) return null;
        return { r: parseInt(m[1],10), g: parseInt(m[2],10), b: parseInt(m[3],10) };
    }
    function rgbaFromRgb(rgb, a){
        if (!rgb) return 'rgba(0,123,255,0.18)';
        return 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ',' + a + ')';
    }

    $(function(){
        setTimeout(function(){
            $('.cubewp-date-range-picker--style-1').each(function(){
                var $wrap = $(this);
                var $from = $wrap.find('.cubewp-date-range-picker-from');
                var $to   = $wrap.find('.cubewp-date-range-picker-to');
                if (!$from.length || !$to.length) { return; }

                var primary = ($wrap.data('cwp-dp-primary') || '').toString().trim() || '#007bff';
                var primaryRgb = parseCssColor(primary);
                var primaryLight = rgbaFromRgb(primaryRgb, 0.18);

                // beforeShowDay: paint the in-range cells + the two endpoints.
                // jQuery UI calls this for every day cell in the open popup,
                // every time the picker refreshes. Reading the current From/To
                // on each call keeps the highlight live as the user types or
                // picks dates.
                function beforeShowDay(date){
                    var from = toDate($from.val());
                    var to   = toDate($to.val());
                    if (!from || !to) {
                        if (from && sameDay(date, from)) return [true, 'cwp-dp-endpoint'];
                        if (to   && sameDay(date, to))   return [true, 'cwp-dp-endpoint'];
                        return [true, ''];
                    }
                    if (from > to) { var t = from; from = to; to = t; }
                    if (sameDay(date, from)) return [true, 'cwp-dp-endpoint cwp-dp-range-start'];
                    if (sameDay(date, to))   return [true, 'cwp-dp-endpoint cwp-dp-range-end'];
                    if (date > from && date < to) return [true, 'cwp-dp-in-range'];
                    return [true, ''];
                }

                var common = {
                    // Single month per popup — 2-month was requested smaller
                    // and ended up stacking vertically on narrow containers.
                    // One compact month per picker is the simplest reliable fit;
                    // the From / To fields still live side-by-side in the wrap.
                    numberOfMonths: 1,
                    showButtonPanel: false,
                    changeMonth: false,
                    changeYear: false,
                    beforeShow: function(){
                        setTimeout(function(){
                            var $dp = $('#ui-datepicker-div').addClass('cubewp-dp-style-1');
                            if ($dp.length && $dp[0] && $dp[0].style) {
                                $dp[0].style.setProperty('--cwp-dp-primary', primary);
                                $dp[0].style.setProperty('--cwp-dp-primary-light', primaryLight);
                            }
                        }, 0);
                    },
                    onClose: function(){
                        $('#ui-datepicker-div').removeClass('cubewp-dp-style-1');
                    },
                    beforeShowDay: beforeShowDay
                };
                try {
                    $from.datepicker('destroy').datepicker($.extend({}, common, {
                        dateFormat:'mm/dd/yy', defaultDate:'+1w',
                        onSelect:function(){
                            $from.trigger('change');
                            // refresh the live picker so range painting updates
                            try { $to.datepicker('refresh'); } catch(e){}
                        }
                    }));
                    $to.datepicker('destroy').datepicker($.extend({}, common, {
                        dateFormat:'mm/dd/yy', defaultDate:'+1w',
                        onSelect:function(){
                            $to.trigger('change');
                            try { $from.datepicker('refresh'); } catch(e){}
                        }
                    }));
                } catch(e) {}
            });
        }, 0);
    });
})(jQuery);
JS;

        // Attach to the jQuery UI stylesheet already enqueued above so the CSS
        // ships in the same request that renders the field. Same for the JS,
        // tied to jquery-ui-datepicker so it loads after. `cwp-jquery-ui` is
        // only ever enqueued from this very method's caller, so the handle has
        // not been printed yet and the inline additions ride along with it.
        wp_add_inline_style('cwp-jquery-ui', $css);
        wp_add_inline_script('jquery-ui-datepicker', $js);
    }
}
new CubeWp_Frontend_Date_Picker_Field();
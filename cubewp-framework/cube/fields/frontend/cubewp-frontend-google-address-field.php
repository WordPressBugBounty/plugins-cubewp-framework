<?php
/**
 * CubeWp admin google address field 
 *
 * @version 1.0
 * @package cubewp/cube/fields/frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Frontend_Google_Address_Field
 */
class CubeWp_Frontend_Google_Address_Field extends CubeWp_Frontend {
    
    public function __construct( ) {
        add_filter('cubewp/frontend/google_address/field', array($this, 'render_google_address_field'), 10, 2);
        
        add_filter('cubewp/user/registration/google_address/field', array($this, 'render_google_address_field'), 10, 2);
        add_filter('cubewp/user/profile/google_address/field', array($this, 'render_google_address_field'), 10, 2);
        
        add_filter('cubewp/search_filters/google_address/field', array($this, 'render_search_google_address_field'), 10, 2);
        add_filter('cubewp/frontend/search/google_address/field', array($this, 'render_search_google_address_field'), 10, 2);
    }
        
    /**
     * Method render_google_address_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_google_address_field( $output = '', $args = array() ) {
    
        wp_enqueue_script('cwp-google-address-field');
        
        $args           =  apply_filters( 'cubewp/frontend/field/parametrs', $args );
        $required       = self::cwp_frontend_field_required($args['required']);
        $required       = !empty($required['class']) ? $required['class'] : '';
        $output         = self::cwp_frontend_post_field_container($args);
    
        $output .= self::cwp_frontend_field_label($args);

        $input_attrs = array( 
            'type'         =>    !empty($args['type']) ? $args['type'] : 'text',
            'id'           =>    isset($args['id']) ? $args['id'] : $args['name'],
            'class'        =>    'form-control address '. $args['class'].' '.$required,
            'name'         =>    !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
            'value'        =>    isset($args['value']) ? $args['value'] : '',
            'placeholder'  =>    $args['placeholder']
        );
        $input_attrs['extra_attrs'] = isset($args['extra_attrs']) ? $args['extra_attrs'] : '';
        $input_attrs['extra_attrs'] .= ' autocomplete="off" data-placeholder="' . esc_html__("Enter a Location", "cubewp-framework") . '"';
        if ( isset( $args['map_use'] ) && $args['map_use'] == '1' ) {
            $input_attrs['extra_attrs'] .= ' data-city-location="1"';
            $cwp_options = get_option( 'cwpOptions', array() );
            if ( ! empty( $cwp_options['google_address_country_code'] ) ) {
                $input_attrs['extra_attrs'] .= ' data-country-code="' . esc_attr( sanitize_text_field( $cwp_options['google_address_country_code'] ) ) . '"';
            }
        }
        
        $output .= '<div class="cwp-field-google-address-input-container">';
            $output .= cwp_render_text_input( $input_attrs );
        $output .= '
            <span class="cwp-icon-tooltip-wrapper cwp-get-current-location-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" class="'.$args['id'].'-cwp-get-current-location cwp-get-current-location" viewBox="0 0 28.278 28.278" title="'.esc_attr__("Get Current Location", "cubewp-framework").'">
                    <path d="M17.995,12.854a5.141,5.141,0,1,0,5.141,5.141,5.14,5.14,0,0,0-5.141-5.141ZM29.486,16.71A11.561,11.561,0,0,0,19.28,6.5V3.856H16.71V6.5A11.561,11.561,0,0,0,6.5,16.71H3.856V19.28H6.5A11.561,11.561,0,0,0,16.71,29.486v2.648H19.28V29.486A11.561,11.561,0,0,0,29.486,19.28h2.648V16.71H29.486ZM17.995,26.992a9,9,0,1,1,9-9A9,9,0,0,1,17.995,26.992Z" transform="translate(-3.856 -3.856)"/>
                </svg>
                <span class="cwp-icon-tooltip">'.esc_html__("Get Current Location", "cubewp-framework").'</span>
            </span>
            <span class="cwp-icon-tooltip-wrapper cwp-drop-pin-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" class="'.$args['id'].'-cwp-drop-pin cwp-drop-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" title="'.esc_attr__("Drop Pin on Map", "cubewp-framework").'">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span class="cwp-icon-tooltip">'.esc_html__("Drop Pin on Map", "cubewp-framework").'</span>
            </span>
        </div>';
        
        $input_attrs = array( 
            'id'           => $args['id'].'_latitude',
            'class'        => 'latitude',
            'name'         => !empty($args['custom_name_lat']) ? $args['custom_name_lat'] : $args['name'],
            'value'        => isset($args['lat']) ? $args['lat'] : '',
            'placeholder' => esc_html__("Enter Latitude", "cubewp-framework")
        );
        $output .= cwp_render_hidden_input( $input_attrs );

        $input_attrs = array( 
            'id'           => $args['id'].'_longitude',
            'class'        => 'longitude',
            'name'         => !empty($args['custom_name_lng']) ? $args['custom_name_lng'] : $args['name'],
            'value'        => isset($args['lng']) ? $args['lng'] : '',
            'placeholder' => esc_html__("Enter Longitude", "cubewp-framework")
        );
        $output .= cwp_render_hidden_input( $input_attrs );
        
        $id = isset($args['id']) ? $args['id'] : $args['name'];
        $output .= '<div class="cwp-map-holder" id="map-'. esc_attr($id) .'"></div>';
    
        // Drop Pin Modal
        $output .= '<div class="cwp-drop-pin-modal" id="cwp-drop-pin-modal-'. esc_attr($id) .'" style="display: none;">';
            $output .= '<div class="cwp-drop-pin-modal-overlay"></div>';
            $output .= '<div class="cwp-drop-pin-modal-content">';
                $output .= '<div class="cwp-drop-pin-modal-header">';
                    $output .= '<h3>'. esc_html__("Drop Pin on Map", "cubewp-framework") .'</h3>';
                    $output .= '<span class="cwp-drop-pin-modal-close">&times;</span>';
                $output .= '</div>';
                $output .= '<div class="cwp-drop-pin-modal-body">';
                    $output .= '<div class="cwp-drop-pin-map-holder" id="cwp-drop-pin-map-'. esc_attr($id) .'"></div>';
                    $output .= '<p class="cwp-drop-pin-instruction">'. esc_html__("Click anywhere on the map to drop a pin and select the location.", "cubewp-framework") .'</p>';
                $output .= '</div>';
                $output .= '<div class="cwp-drop-pin-modal-footer">';
                    $output .= '<button type="button" class="cwp-drop-pin-cancel">'. esc_html__("Cancel", "cubewp-framework") .'</button>';
                    $output .= '<button type="button" class="cwp-drop-pin-confirm">'. esc_html__("Confirm Location", "cubewp-framework") .'</button>';
                $output .= '</div>';
            $output .= '</div>';
        $output .= '</div>';
    
        $output .= '</div>';
    
        $output = apply_filters("cubewp/frontend/{$args['name']}/field", $output, $args);
        
        return $output;
        
    }
        
    /**
     * Method render_search_google_address_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_search_google_address_field( $output = '', $args = array() ) {
	    global $cwpOptions;
        wp_enqueue_script('cwp-google-address-field');
        
        $args   =  apply_filters( 'cubewp/frontend/field/parametrs', $args );
        
        $output         = self::cwp_frontend_search_field_container($args);
            $output .= self::cwp_frontend_search_field_label($args);

            $input_attrs = array( 
                'type'         =>    !empty($args['type']) ? $args['type'] : 'text',
                'id'           =>    isset($args['id']) && !empty($args['id']) ? $args['id'] : $args['name'],
                'class'        =>    'address '. $args['class'],
                'name'         =>    !empty($args['custom_name']) ? $args['custom_name'] : $args['name'],
                'value'        =>    isset($args['value']) ? $args['value'] : '',
                'placeholder'  =>    $args['placeholder']
            );
            if ( isset( $args['map_use'] ) && $args['map_use'] == '1' ) {
                $input_attrs['extra_attrs'] = isset( $input_attrs['extra_attrs'] ) ? $input_attrs['extra_attrs'] : '';
                $input_attrs['extra_attrs'] .= ' data-city-location="1"';
                $cwp_options = get_option( 'cwpOptions', array() );
                if ( ! empty( $cwp_options['google_address_country_code'] ) ) {
                    $input_attrs['extra_attrs'] .= ' data-country-code="' . esc_attr( sanitize_text_field( $cwp_options['google_address_country_code'] ) ) . '"';
                }
            }
            
            $output .= '<div class="cwp-field-google-address-input-container">';
                $output .= cwp_render_text_input( $input_attrs );
            $output .= '
                <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="15px" class="cwp-get-current-location" viewBox="0 0 28.278 28.278">
                  <path d="M17.995,12.854a5.141,5.141,0,1,0,5.141,5.141,5.14,5.14,0,0,0-5.141-5.141ZM29.486,16.71A11.561,11.561,0,0,0,19.28,6.5V3.856H16.71V6.5A11.561,11.561,0,0,0,6.5,16.71H3.856V19.28H6.5A11.561,11.561,0,0,0,16.71,29.486v2.648H19.28V29.486A11.561,11.561,0,0,0,29.486,19.28h2.648V16.71H29.486ZM17.995,26.992a9,9,0,1,1,9-9A9,9,0,0,1,17.995,26.992Z" transform="translate(-3.856 -3.856)"/>
                </svg>
            </div>';
            
            $input_attrs = array( 
                'id'           => $args['id'].'_latitude',
                'class'        => 'latitude',
                'name'         => !empty($args['custom_name_lat']) ? $args['custom_name_lat'] : $args['name'],
                'value'        => isset($args['lat']) ? $args['lat'] : '',
            );
            $output .= cwp_render_hidden_input( $input_attrs );

            $input_attrs = array( 
                'id'           => $args['id'].'_longitude',
                'class'        => 'longitude',
                'name'         => !empty($args['custom_name_lng']) ? $args['custom_name_lng'] : $args['name'],
                'value'        => isset($args['lng']) ? $args['lng'] : '',
            );
            $output .= cwp_render_hidden_input( $input_attrs );

		    $class = "";
			if (empty($args['lat']) || empty($args['lng'])) {
				$class = "cwp-hide";
			}

			if (isset($cwpOptions['google_address_radius']) && $cwpOptions['google_address_radius'] == '1') {
			    $output .= '<div class="cwp-address-range ' . $class . '">';
			    $output .= '<label for="' . $args['id'] . '_range">' . esc_html__("Search Radius", "cubewp-framework") . '</label>';
				$min_radius = $cwpOptions['google_address_min_radius'];
				$default_radius = $cwpOptions['google_address_default_radius'];
				$max_radius = $cwpOptions['google_address_max_radius'];
				$radius_unit = $cwpOptions['google_address_radius_unit'];
				$type = 'hidden';
				$value = '';
				$extra_attrs = '';
                $extra_attrs = 'min="' . $min_radius . '" max="' . $max_radius . '" ';
				if (isset($args['range']) && !empty($args['range'])) {
					$type = 'range';
					$value = $args['range'];
					$extra_attrs = 'min="' . $min_radius . '" max="' . $max_radius . '" ';
				}else {
					$args['range'] = $default_radius;
				}
			    $input_attrs = array(
				    'id'           => $args['id'].'_range',
				    'type'         => $type,
				    'class'        => 'range',
				    'extra_attrs'  => $extra_attrs. ' data-min="' . $min_radius . '" data-max="' . $max_radius . '" data-value="' . $args["range"] . '"',
				    'name'         => !empty($args['custom_name_range']) ? $args['custom_name_range'] : $args['name'],
				    'value'        => $value
			    );
			    $output .= cwp_render_text_input( $input_attrs );
			    $output .= '<p>' . esc_html__("Current Radius:", "cubewp-framework") . ' <span>' . $args['range'] . '</span>' . $radius_unit . '</p>';
			    $output .= '</div>';
			}
	    $output .= '</div>';

        $output = apply_filters("cubewp/frontend/search/{$args['name']}/field", $output, $args);
        
        return $output;
        
    }
    
}
new CubeWp_Frontend_Google_Address_Field();
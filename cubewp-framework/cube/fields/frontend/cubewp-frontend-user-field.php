<?php
/**
 * CubeWp admin user field 
 *
 * @version 1.0
 * @package cubewp/cube/fields/frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CubeWp_Frontend_User_Field
 */
class CubeWp_Frontend_User_Field extends CubeWp_Frontend {
    public function __construct( ) {
        add_filter('cubewp/frontend/user/field', array($this, 'render_user_field'), 10, 2);
    }
        
    /**
     * Method render_user_field
     *
     * @param string $output
     * @param array $args
     *
     * @return string html
     * @since  1.0.0
     */
    public function render_user_field($output = '', $args = array()) {
        $args = apply_filters('cubewp/frontend/field/parametrs', $args);
        if ($args['appearance'] == 'select') {
           $args['type'] = 'dropdown';
        } else if ($args['appearance'] == 'multi_select') {
           $args['type']  = 'dropdown';
           $args['multi'] = true;
        } else {
           $args['type'] = $args['appearance'];
        }

        if (isset($args['auto_complete']) && ! empty($args['auto_complete']) && $args['type'] == 'dropdown') {
         $args['select2_ui'] = true;
		    $options = array();
		    if ( ! empty($args['value']) && is_array($args['value'])) {
			    foreach ($args['value'] as $user_id) {
				    $user              = get_userdata($user_id);
				    $options[$user_id] = esc_html($user->display_name);
			    }
		    } else if ( ! empty($args['value']) && ! is_array($args['value'])) {
			    $user                    = get_userdata($args['value']);
			    $options[$args['value']] = esc_html($user->display_name);
		    }
		    $args['options'] = $options;
		    $args['class']  = $args['class'] . ' cubewp-remote-options ';
		    $args['extra_attrs']  = $args['extra_attrs'] . ' data-dropdown-type="user" data-dropdown-values="' . $args['filter_user_roles'] . '" ';
	    }else {
		    /*
		     * Security (2026-10-07): the PRELOAD path needs the same gate as the
		     * autocomplete path above, and it never had one.
		     *
		     * Without `auto_complete`, this field does not call
		     * `cubewp_dynamic_options` at all — it renders EVERY user of the
		     * configured role straight into the select, because
		     * `cwp_get_users_by_role()` applies no capability check, no login check
		     * and no limit. On a public form that is a bulk disclosure of display
		     * names and ids to anonymous visitors: strictly worse than the AJAX
		     * branch, which at least demands a keyword and caps the result set.
		     *
		     * The 2026-09-25 hardening covered only the AJAX branch, so this one
		     * stayed open and unnoticed. It has no live instance — the 2026-10-07
		     * inventory found no site on the platform configuring a `user` field —
		     * which is precisely why it is cheap to close now, before the first
		     * person configures one on a public form and leaks a member list.
		     */
		    $args['options'] = current_user_can('list_users')
			    ? cwp_get_users_by_role($args['filter_user_roles'])
			    : array();
	    }

	    return apply_filters("cubewp/frontend/{$args['type']}/field", $output, $args);
    }
}
new CubeWp_Frontend_User_Field();
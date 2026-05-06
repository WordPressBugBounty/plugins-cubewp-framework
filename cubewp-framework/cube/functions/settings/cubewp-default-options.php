<?php

/**
 * Creates the cubewp settings fields in wordpress admin.
 *
 * @version 1.0
 * @package cubewp/cube/functions/settings
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

if (! defined('ABSPATH')) {
	exit;
}
$all_post_types = get_post_types(array('public' => true));
$exclude_post_types = CWP_types();
$exclude_post_types['cubewp-report'] = 'cubewp-report';
$exclude_post_types['cwp_reviews'] = 'cwp_reviews';
$exclude_post_types['attachment'] = 'attachment';
$exclude_post_types['post'] = 'post';
$exclude_post_types = apply_filters('cubewp/settings/excluded/external/post_types', $exclude_post_types);
$external_post_types = array_diff_key($all_post_types, $exclude_post_types);
$settings['general-settings'] = array(
	'title'  => __('General', 'cubewp-framework'),
	'id'     => 'general-settings',
	'fields' => array(
		array(
			'id'      => 'external_cpt_into_cubewp',
			'type'    => 'switch',
			'title'   => __('External Custom Post Types Into CubeWP Builders', 'cubewp-framework'),
			'desc'    => __('Enable if you want to add custom post types created by code or 3rd party plugins into CubeWP builders', 'cubewp-framework'),
			'default' => '0',
		),
		array(
			'id'       => 'external_cpt_for_cubewp_builders',
			'type'     => 'select',
			'title'    => __('Select Post Types', 'cubewp-framework'),
			'options'  => $external_post_types,
			'multi'   =>  true,
			'desc'     => __('Select custom post types created by code or 3rd party plugins', 'cubewp-framework'),
			'required' => array(
				array('external_cpt_into_cubewp', 'equals', '1')
			)
		),
		array(
			'id'      => 'delete_custom_posts_attachments',
			'type'    => 'switch',
			'title'   => __('Delete Attachments Upon Post Deletion', 'cubewp-framework'),
			'desc'    => __('Enable this option if you wish to delete post attachments along with the post when you delete it from the trash.', 'cubewp-framework'),
			'default' => '0',
		),
		array(
            'id'      => 'default_featured_image',
            'type'    => 'media',
            'title'   => __('Default Featured Image', 'cubewp-framework'),
            'default' => '',
            'desc'    => __('Upload a default featured image to display when a post has no featured image set. Used in Elementor dynamic tags and builders.', 'cubewp-framework'),
        ),
	)
);
// Add "Keywords Suggestions" tab/section separately from General
$settings['keywords_suggestions'] = array(
	'title'  => __('Keywords Suggestions', 'cubewp-framework'),
	'id'     => 'keywords_suggestions',
	'icon'   => 'dashicons-search',
	'fields' => array(),
);
// Add keyword suggestions settings for each post type
$cubewp_post_types = CWP_all_post_types('settings');
foreach ($cubewp_post_types as $cwp_pt_slug => $cwp_pt_label) {
	$post_type_obj = get_post_type_object($cwp_pt_slug);
	$label = isset($post_type_obj->labels->singular_name) ? $post_type_obj->labels->singular_name : $cwp_pt_label;
	// Get all taxonomies for this post type
	$taxonomies = get_object_taxonomies($cwp_pt_slug, 'objects');
	$taxonomy_options = array();
	foreach ($taxonomies as $tax_slug => $tax_obj) {
		$taxonomy_options[$tax_slug] = $tax_obj->labels->singular_name;
	}
	$settings['keywords_suggestions']['fields'][] = array(
		'id'      => 'enable_keywords_suggestions_' . esc_attr($cwp_pt_slug),
		'type'    => 'switch',
		'title'   => sprintf(
			__('Enable Keyword Suggestions for %s', 'cubewp-framework'),
			$label
		),
		'desc'    => sprintf(
			__('Enable keyword suggestions for %s post type. When enabled, select a taxonomy to use for suggestions.', 'cubewp-framework'),
			$label
		),
		'default' => 0,
	);
	$settings['keywords_suggestions']['fields'][] = array(
		'id'       => 'keywords_suggestions_taxonomy_' . esc_attr($cwp_pt_slug),
		'type'     => 'select',
		'multi'  => true,
		'title'    => sprintf(
			__('Select Taxonomy for Keyword Suggestions (%s)', 'cubewp-framework'),
			$label
		),
		'options'  => $taxonomy_options,
		'required' => array(
			array(
				'enable_keywords_suggestions_' . esc_attr($cwp_pt_slug),
				'equals',
				1
			),
		),
		'desc'     => __(
			'Choose which taxonomy to use for keyword suggestions. Only shown when keyword suggestions are enabled for this post type.',
			'cubewp-framework'
		),
	);
	$settings['keywords_suggestions']['fields'][] = array(
		'id'       => 'keywords_suggestions_default_taxonomy_' . esc_attr($cwp_pt_slug),
		'type'     => 'select',
		'multi'    => false,
		'title'    => sprintf(
			__('Default Taxonomy for Empty Keyword (%s)', 'cubewp-framework'),
			$label
		),
		'options'  => $taxonomy_options,
		'default'  => '',
		'required' => array(
			array(
				'enable_keywords_suggestions_' . esc_attr($cwp_pt_slug),
				'equals',
				1
			),
		),
		'desc'     => __(
			'When keyword field is empty, suggestions will come from this taxonomy only (10 default terms).',
			'cubewp-framework'
		),
	);
	//icon term meta key for taxonomy
	if(is_array($taxonomy_options) && !empty($taxonomy_options)) {
		foreach($taxonomy_options as $taxonomy_slug => $taxonomy_label) {
			$settings['keywords_suggestions']['fields'][] = array(
				'id'       => 'keywords_suggestions_icon_' . esc_attr($taxonomy_slug),
				'type'     => 'text',
				'title'    => sprintf(
					__('Icon Term Meta key for %s', 'cubewp-framework'),
					$taxonomy_label
				),
				'default'  => 'term_image',
				'required' => array(
					array(
						'enable_keywords_suggestions_' . esc_attr($cwp_pt_slug),
						'equals',
						1
					),
					array(
						'keywords_suggestions_taxonomy_' . esc_attr($cwp_pt_slug),
						'contains',
						$taxonomy_slug
					),
				),
			);
		}
	}
}
$settings['search_filters'] = array(
	'title'  => __('Search & Filters', 'cubewp-framework'),
	'id'     => 'search_filters',
	'icon'   => 'dashicons-filter',
	'fields' => array(
		array(
			'id'      => 'google_address_radius',
			'type'    => 'switch',
			'title'   => __('Google Address Search Radius', 'cubewp-framework'),
			'default' => '1',
			'desc'    => __('Gives you a range bar in google address field on search and filter', 'cubewp-framework'),
		),
		array(
			'id'       => 'google_address_min_radius',
			'type'     => 'text',
			'title'    => __('Minimum Radius', 'cubewp-framework'),
			'default'  => '5',
			'desc'     => __('Minimum radius for google address field on search and filter', 'cubewp-framework'),
			'required' => array(
				array('google_address_radius', 'equals', '1')
			)
		),
		array(
			'id'       => 'google_address_default_radius',
			'type'     => 'text',
			'title'    => __('Default Radius', 'cubewp-framework'),
			'default'  => '30',
			'desc'     => __('Default radius for google address field on search and filter', 'cubewp-framework'),
			'required' => array(
				array('google_address_radius', 'equals', '1')
			)
		),
		array(
			'id'       => 'google_address_max_radius',
			'type'     => 'text',
			'title'    => __('Maximum Radius', 'cubewp-framework'),
			'default'  => '500',
			'desc'     => __('Maximum radius for google address field on search and filter', 'cubewp-framework'),
			'required' => array(
				array('google_address_radius', 'equals', '1')
			)
		),
		array(
			'id'       => 'google_address_radius_unit',
			'type'     => 'select',
			'title'    => __('Radius Unit', 'cubewp-framework'),
			'options'  => array(
				'mi' => __('Mile', 'cubewp-framework'),
				'km' => __('Kilometre', 'cubewp-framework')
			),
			'default'  => 'mi',
			'desc'     => __('Unit of radius for google address field on search and filter', 'cubewp-framework'),
			'required' => array(
				array('google_address_radius', 'equals', '1')
			)
		),
		array(
            'id'      => 'google_address_city_location',
            'type'    => 'switch',
            'title'   => __('Enable City Location Mode', 'cubewp-framework'),
            'default' => '0',
            'desc'    => __('When enabled, the Google Address field will show city names only in suggestions instead of full addresses. Applies to submission forms, search filters, and admin.', 'cubewp-framework'),
        ),
        array(
            'id'      => 'google_address_country_code',
            'type'    => 'text',
            'title'   => __('Country Code for Google Address', 'cubewp-framework'),
            'default' => '',
            'required' => array(
                array('google_address_city_location', 'equals', '1')
            ),
            'desc'    => __('Optional. Enter ISO 3166-1 Alpha-2 country code (e.g., us, gb) to restrict suggestions to cities from that country. Use comma for multiple: us,ca,gb. Leave empty for worldwide. See <a href="https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes" target="_blank" rel="noopener">list of country codes</a>.', 'cubewp-framework'),
            'tag'     => 'ISO_3166_country_code', // Tag to indicate what codes to use (see https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes)
        ),
        array(
            'id'      => 'google_address_use_terms',
            'type'    => 'switch',
            'title'   => __('Use Terms for Google Location', 'cubewp-framework'),
            'default' => '0',
            'required' => array(
                array('google_address_city_location', 'equals', '1')
            ),
            'desc'    => __('When enabled, searching by city (e.g., US) will filter results by matching taxonomy term instead of geographic proximity. Select a taxonomy for each post type below.', 'cubewp-framework'),
        )
	)
);

// Add per-post-type taxonomy select for Google Address terms (taxonomies related to each post type)
$cubewp_pt_for_location = function_exists( 'CWP_all_post_types' ) ? CWP_all_post_types( 'settings' ) : array();
foreach ( $cubewp_pt_for_location as $cwp_pt_slug => $cwp_pt_label ) {
    $post_type_obj   = get_post_type_object( $cwp_pt_slug );
    $pt_label        = isset( $post_type_obj->labels->singular_name ) ? $post_type_obj->labels->singular_name : $cwp_pt_label;
    $taxonomies      = get_object_taxonomies( $cwp_pt_slug, 'objects' );
    $taxonomy_opts   = array( '' => __( 'Select Taxonomy', 'cubewp-framework' ) );
    foreach ( $taxonomies as $tax_slug => $tax_obj ) {
        $taxonomy_opts[ $tax_slug ] = $tax_obj->labels->singular_name . ' (' . $tax_slug . ')';
    }
    $settings['search_filters']['fields'][] = array(
        'id'       => 'google_address_taxonomy_' . esc_attr( $cwp_pt_slug ),
        'type'     => 'select',
        'title'    => sprintf( __( 'Taxonomy for %s', 'cubewp-framework' ), $pt_label ),
        'options'  => $taxonomy_opts,
        'default'  => '',
        'desc'     => sprintf( __( 'Select taxonomy for location terms when searching %s. When user searches "US", results filter by term "US" in this taxonomy.', 'cubewp-framework' ), $pt_label ),
        'required' => array(
            array( 'google_address_use_terms', 'equals', '1' )
        )
        );
}

$settings['map']    = array(
	'title'  => __('Map', 'cubewp-framework'),
	'id'     => 'map',
	'icon'   => 'dashicons-location-alt',
	'fields' => array(
		array(
			'id'      => 'google_map_api',
			'title'   => __('Google Map & Places API Key', 'cubewp-framework'),
			'desc'    => __('Get your Google Maps API Key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>.', 'cubewp-framework'),
			'type'    => 'text',
			'default' => '',
		),
		array(
			'id'      => 'map_option',
			'type'    => 'select',
			'title'   => __('Map Type', 'cubewp-framework'),
			'options' => array(
				'openstreet' => 'OpenStreet Map',
				'google'     => 'Google Map',
				'mapbox'     => 'MapBox API',
			),
			'default' => 'openstreet',
		),
		array(
			'id'       => 'mapbox_token',
			'type'     => 'text',
			'title'    => __('Mapbox Token', 'cubewp-framework'),
			'subtitle' => __('Put here MapBox token, If you leave it empty then Google map will work', 'cubewp-framework'),
			'desc'     => __('Get your Mapbox Key here.<br>https://account.mapbox.com/access-tokens/create', 'cubewp-framework'),
			'default'  => '',
			'required' => array(
				array('map_option', 'equals', 'mapbox')
			)
		),
		array(
			'id'       => 'map_style',
			'type'     => 'text',
			'title'    => esc_html__('Mapbox Map Style Id', 'cubewp-framework'),
			'subtitle' => esc_html__('Type Your Custom Style ID', 'cubewp-framework'),
			'desc'     => esc_html__('Type how you want the Mapbox map to show. Only use YOUR_USERNAME/YOUR_STYLE_ID No slashes before and after.', 'cubewp-framework'),
			'default'  => 'mapbox/streets-v11',
			'required' => array(
				array('map_option', 'equals', 'mapbox')
			)
		),
		array(
			'id'      => 'map_zoom',
			'title'   => __('Set Map Default Zoom Level', 'cubewp-framework'),
			'desc'    => __('Write Value Between 1 - 18 For Default Zoom Level', 'cubewp-framework'),
			'type'    => 'text',
			'default' => '15',
		),
		array(
			'id'      => 'map_latitude',
			'title'   => __('Set Map Default Latitude', 'cubewp-framework'),
			'desc'    => __('Write Valid Latitude For Default Map', 'cubewp-framework'),
			'type'    => 'text',
			'default' => '40.68924104083928',
		),
		array(
			'id'      => 'map_longitude',
			'title'   => __('Set Map Default Longitude', 'cubewp-framework'),
			'desc'    => __('Write Valid Longitude For Default Map', 'cubewp-framework'),
			'type'    => 'text',
			'default' => '-74.04450284527532',
		),
	)
);

$settings['archive_settings']    = array(
	'title'  => __('Archive Settings', 'cubewp-framework'),
	'id'     => 'archive_settings',
	'icon'   => 'dashicons-archive',
	'fields' => array(
		array(
			'id'      => 'archive_map',
			'title'   => __('Map', 'cubewp-framework'),
			'desc'    => __('You can easily On/Off map on CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'switch',
			'default' => '0',
		),
		array(
			'id'      => 'archive_filters',
			'title'   => __('Filters', 'cubewp-framework'),
			'desc'    => __('You can easily On/Off filters on CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'switch',
			'default' => '0',
		),
		array(
			'id'      => 'archive_sort_filter',
			'title'   => __('Sorting Filter', 'cubewp-framework'),
			'desc'    => __('You can easily On/Off sorting filter on CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'switch',
			'default' => '1',
		),
		array(
			'id'      => 'archive_layout',
			'title'   => __('Layout Switcher', 'cubewp-framework'),
			'desc'    => __('You can easily On/Off layout switcher on CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'switch',
			'default' => '1',
		),
		array(
			'id'      => 'archive_found_text',
			'title'   => __('Found Text', 'cubewp-framework'),
			'desc'    => __('You can easily On/Off found text on CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'switch',
			'default' => '1',
		),
		array(
			'id'      => 'archive_posts_per_page',
			'title'   => __('Posts Per Page', 'cubewp-framework'),
			'desc'    => __('Set number of posts per page in search for CubeWP default archive page', 'cubewp-framework'),
			'type'    => 'text',
			'default' => 10,
		),
	)
);

$settings['post_settings'] = array(
	'title'  => __('Post Settings', 'cubewp-framework'),
	'id'     => 'post_settings',
	'fields' => array_merge(
		array(
			array(
				'id'       => 'cwp_loop_style',
				'type'     => 'post_type_assignment',
				'title'    => __('Create Post Card Styles', 'cubewp-framework'),
				'parent_options'  => CWP_all_post_types('settings'),
				'child_type'  => 'text',
				'multi'  => true,
				'desc'     => __('Select post type and assign style names to post type and then you can create different styles output using CubeWP Post Card Builder', 'cubewp-framework'),
			),
			array(
				'id'      => 'cubewp_singular',
				'title'   => __('CubeWP Single Post Layout Builder', 'cubewp-framework'),
				'desc'    => __('Enable/Disable CubeWP Single Post Layout Builder for managing default single post layout if you are not using CubeWP Theme Builder.', 'cubewp-framework'),
				'type'    => 'switch',
				'default' => '1',
			),
			array(
				'id'      => 'post_type_save_button',
				'type'    => 'switch',
				'title'   => __('Save Button', 'cubewp-framework'),
				'default' => '1',
				'desc'    => __('Gives you a button on single page to save post type', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1')
				)
			),
			array(
				'id'      => 'post_type_share_button',
				'type'    => 'switch',
				'title'   => __('Share Button', 'cubewp-framework'),
				'default' => '1',
				'desc'    => __('Gives you a button on single page to share post type', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1')
				)
			),
			array(
				'id'       => 'twitter_share',
				'type'     => 'switch',
				'title'    => __('X (twitter) Share', 'cubewp-framework'),
				'default'  => '1',
				'desc'     => __('By enabling this option, you can share post on X (twitter)', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1'),
					array('post_type_share_button', 'equals', '1')
				)
			),
			array(
				'id'       => 'facebook_share',
				'type'     => 'switch',
				'title'    => __('Facebook Share', 'cubewp-framework'),
				'default'  => '1',
				'desc'     => __('By enabling this option, you can share post on facebook', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1'),
					array('post_type_share_button', 'equals', '1')
				)
			),
			array(
				'id'       => 'pinterest_share',
				'type'     => 'switch',
				'title'    => __('Pinterest Share', 'cubewp-framework'),
				'default'  => '1',
				'desc'     => __('By enabling this option, you can share post on pinterest', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1'),
					array('post_type_share_button', 'equals', '1')
				)
			),
			array(
				'id'       => 'linkedin_share',
				'type'     => 'switch',
				'title'    => __('LinkedIn Share', 'cubewp-framework'),
				'default'  => '1',
				'desc'     => __('By enabling this option, you can share post on linkedIn', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1'),
					array('post_type_share_button', 'equals', '1')
				)
			),
			array(
				'id'       => 'reddit_share',
				'type'     => 'switch',
				'title'    => __('Reddit Share', 'cubewp-framework'),
				'default'  => '1',
				'desc'     => __('By enabling this option, you can share post on reddit', 'cubewp-framework'),
				'required' => array(
					array('cubewp_singular', 'equals', '1'),
					array('post_type_share_button', 'equals', '1')
				)
			),
		)
	)
);
$settings['author_settings'] = array(
	'title'  => __('Author Settings', 'cubewp-framework'),
	'id'     => 'author_settings',
	'fields' => array(
		array(
			'id'      => 'show_author_template',
			'type'    => 'switch',
			'title'   => __('Author Page Template', 'cubewp-framework'),
			'default' => '0',
			'desc'    => __('If you have your author page template by any theme or plugin then you do not need to enable this option, Otherwise you can enable CubeWP Author page template ', 'cubewp-framework'),
		),
		array(
			'id'      => 'author_banner_image',
			'type'    => 'media',
			'title'   => __('Banner Image', 'cubewp-framework'),
			'default' => '',
			'desc'    => __('Please upload banner image for author page', 'cubewp-framework'),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'      => 'author_share_button',
			'type'    => 'switch',
			'title'   => __('Share Button', 'cubewp-framework'),
			'default' => '1',
			'desc'    => __('Gives you a share button on author page', 'cubewp-framework'),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'       => 'author_edit_profile',
			'type'     => 'switch',
			'title'    => __('Edit Profile', 'cubewp-framework'),
			'default'  => '1',
			'desc'     => __('By enabling this option, author can edit profile from author page', 'cubewp-framework'),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'       => 'profile_page',
			'type'     => 'pages',
			'title'    => __('User Profile Form Page', 'cubewp-framework'),
			'subtitle' => __('This must be an URL.', 'cubewp-framework'),
			'validate' => 'url',
			'desc'     => __('Select the page used for the User Profile Form (Page must include the Profile Form Shortcode)', 'cubewp-framework'),
			'default'  => '',
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'       => 'author_contact_info',
			'type'     => 'switch',
			'title'    => __('Contact Info', 'cubewp-framework'),
			'default'  => '1',
			'desc'     => __('By enabling this option, author contact info will be visible on author page', 'cubewp-framework'),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'       => 'author_post_types',
			'type'     => 'select',
			'multi'   =>  true,
			'title'    => __('Select Post Types', 'cubewp-framework'),
			'subtitle' => '',
			'desc'     => __('Tabs for above selected post types will be added other than all posts tab on author page', 'cubewp-framework'),
			'options'  => cwp_post_types(),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
		array(
			'id'       => 'author_custom_fields',
			'type'     => 'switch',
			'title'    => __('User Custom Field', 'cubewp-framework'),
			'default'  => '1',
			'desc'     => __('By enabling this option, author custom fields will be shown on author page', 'cubewp-framework'),
			'required' => array(
				array('show_author_template', 'equals', '1')
			)
		),
	)
);
 
$settings['cubewp-theme-builder'] = array(
	'title'  => __('Theme Builder', 'cubewp-framework'),
	'id'     => 'cubewp-theme-builder',
	'icon'   => 'dashicons-editor-code',
	'fields' => array(
		array(
			'id'       => 'cwp_tb_hooks',
			'type'     => 'repeating_field',
			'title'    => __('Add WordPress Hooks For Theme builder', 'cubewp-framework'),
			'child_type'  => 'text',
			'desc'     => __('Add WordPress Hooks here and then you will be able to select these hooks in theme builder and you can create template to show on these hooks wherever you want.', 'cubewp-framework'),
		),
		array(
			'id'      => 'cwp_tb_enable_cache',
			'type'    => 'switch',
			'title'   => __('Cache Header & Footer (Theme Builder)', 'cubewp-framework'),
			'default' => '0',
			'desc'    => __('Cache the rendered header and footer templates generated by the theme builder to improve load times.', 'cubewp-framework'),
		),
		array(
			'id'       => 'cwp_tb_cache_ttl',
			'type'     => 'text',
			'title'    => __('Header & Footer Cache Duration (Hours)', 'cubewp-framework'),
			'desc'     => __('Set how long the header and footer cache should be stored in hours. Default is 12 hours. Cache is also cleared when Elementor clears its cache or when you save a header/footer template.', 'cubewp-framework'),
			'default'  => '12',
			'required' => array(
				array('cwp_tb_enable_cache', 'equals', '1')
			)
		),
		array(
			'id'      => 'cwp_posts_enable_cache',
			'type'    => 'switch',
			'title'   => __('Enable Posts Cache', 'cubewp-framework'),
			'default' => '1',
			'desc'    => __('Enable caching for posts output to improve performance. Cache type is automatically detected based on server capabilities (Object Cache when available, SQL Cache otherwise).', 'cubewp-framework'),
		),
		array(
			'id'       => 'cwp_posts_cache_ttl',
			'type'     => 'text',
			'title'    => __('Posts Cache Duration (Hours)', 'cubewp-framework'),
			'desc'     => __('Set how long the posts cache should be stored in hours. Default is 12 hours.', 'cubewp-framework'),
			'default'  => '12',
			'required' => array(
				array('cwp_posts_enable_cache', 'equals', '1')
			)
		),
		array(
			'id'      => 'cwp_taxonomy_enable_cache',
			'type'    => 'switch',
			'title'   => __('Enable Taxonomy Cache', 'cubewp-framework'),
			'default' => '1',
			'desc'    => __('Enable caching for taxonomy terms output to improve performance. Cache type is automatically detected based on server capabilities (Object Cache when available, SQL Cache otherwise).', 'cubewp-framework'),
		),
		array(
			'id'       => 'cwp_taxonomy_cache_ttl',
			'type'     => 'text',
			'title'    => __('Taxonomy Cache Duration (Hours)', 'cubewp-framework'),
			'desc'     => __('Set how long the taxonomy cache should be stored in hours. Default is 12 hours.', 'cubewp-framework'),
			'default'  => '12',
			'required' => array(
				array('cwp_taxonomy_enable_cache', 'equals', '1')
			)
		),
	)
	);

$settings['cubewp-css-js'] = array(
	'title'  => __('CSS & JS', 'cubewp-framework'),
	'id'     => 'cubewp-css-js',
	'icon'   => 'dashicons-editor-code',
	'fields' => array(
		array(
			'id'      => 'cubewp-css',
			'type'    => 'ace_editor',
			'mode'    => 'css',
			'title'   => __('CSS ( Cascading Style Sheets )', 'cubewp-framework'),
			'desc'    => __('Put CSS code above. It will be enqueued on frontend only', 'cubewp-framework'),
		),
		array(
			'id'      => 'cubewp-js',
			'type'    => 'ace_editor',
			'mode'    => 'javascript',
			'title'   => __('JS or jQ ( JavaScript Or jQuery )', 'cubewp-framework'),
			'desc'    => __('Put JS code above. It will be enqueued on frontend only', 'cubewp-framework'),
		),
	)
);



if (! function_exists('cubewp_settings_get_field_types_and_ids')) {
	/**
	 * Build a map of settings field types and their field IDs.
	 *
	 * @param array $settings CubeWP settings array (sections => fields).
	 *
	 * @return array
	 */
	function cubewp_settings_get_field_types_and_ids($settings = array())
	{
		$result = array();
		if (empty($settings) || ! is_array($settings)) {
			return $result;
		}

		foreach ($settings as $section) {
			if (! is_array($section) || empty($section['fields']) || ! is_array($section['fields'])) {
				continue;
			}

			foreach ($section['fields'] as $field) {
				if (! is_array($field)) {
					continue;
				}

				$field_type = isset($field['type']) ? (string) $field['type'] : '';
				$field_id   = isset($field['id']) ? (string) $field['id'] : '';

				if ($field_type === '' || $field_id === '') {
					continue;
				}

				if (! isset($result[$field_type])) {
					$result[$field_type] = array();
				}
				$result[$field_type][] = array('id' => $field_id, 'title' => isset($field['title']) ? $field['title'] : '');
			}
		}

		return $result;
	}
}

if (! function_exists('cubewp_add_custom_typography_fonts')) {
	/**
	 * Add missing Google fonts for typography field.
	 *
	 * @param array $fonts Existing Google fonts.
	 * @return array
	 */
	function cubewp_add_custom_typography_fonts($fonts = array())
	{
		if (! is_array($fonts)) {
			$fonts = array();
		}

		$fonts['Lexend Deca'] = array(
			'variants' => array(
				array('id' => '100', 'name' => 'Thin 100'),
				array('id' => '200', 'name' => 'Extra-Light 200'),
				array('id' => '300', 'name' => 'Light 300'),
				array('id' => '400', 'name' => 'Regular 400'),
				array('id' => '500', 'name' => 'Medium 500'),
				array('id' => '600', 'name' => 'Semi-Bold 600'),
				array('id' => '700', 'name' => 'Bold 700'),
				array('id' => '800', 'name' => 'Extra-Bold 800'),
				array('id' => '900', 'name' => 'Black 900'),
			),
			'subsets'  => array(
				array('id' => 'latin', 'name' => 'Latin'),
				array('id' => 'latin-ext', 'name' => 'Latin Extended'),
				array('id' => 'vietnamese', 'name' => 'Vietnamese'),
			),
		);

		$fonts['Space Grotesk'] = array(
			'variants' => array(
				array('id' => '300', 'name' => 'Light 300'),
				array('id' => '400', 'name' => 'Regular 400'),
				array('id' => '500', 'name' => 'Medium 500'),
				array('id' => '600', 'name' => 'Semi-Bold 600'),
				array('id' => '700', 'name' => 'Bold 700'),
			),
			'subsets'  => array(
				array('id' => 'latin', 'name' => 'Latin'),
				array('id' => 'latin-ext', 'name' => 'Latin Extended'),
				array('id' => 'vietnamese', 'name' => 'Vietnamese'),
			),
		);

		return $fonts;
	}
}
add_filter('cubewp/settings/google_fonts', 'cubewp_add_custom_typography_fonts', 20);

return $settings;


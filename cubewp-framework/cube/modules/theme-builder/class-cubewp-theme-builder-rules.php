<?php

/**
 * CubeWp Theme builder RUles options
 *
 * @version 1.1.16
 * @package cubewp/cube/mobules/theme-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * CubeWp_Theme_Builder_Rules
 */
class CubeWp_Theme_Builder_Rules {

    public function __construct() {
        add_action('wp_ajax_get_template_options', [$this, 'get_template_options']);
    }

    /**
     * Method get_public_taxonomies
     *
     * @return array
	 * * @since   1.1.16
     */
	private static function get_public_taxonomies() {
		$core                  = get_taxonomies( [ '_builtin' => true, 'public' => true, 'hierarchical' => true ], 'objects', 'and' );
		$public                = get_taxonomies( [ '_builtin' => false, 'public' => true, 'hierarchical' => true ], 'objects', 'and' );
		$taxonomies = array_merge($core,$public);
		$taxonomies = array_map(function($taxonomy) {
            return array(
                'name' => esc_attr($taxonomy->name),
                'label' => esc_html($taxonomy->label),
            );
        }, $taxonomies);
		return $taxonomies;
	}

    /**
	 * Method get_public_post_types
	 *
	 * @return array
	 * * @since   1.1.16
	 */
	private static function get_public_post_types() {
		$array = [];
		$core                  = get_post_types( [ '_builtin' => true, 'show_in_menu' => true ], 'objects', 'and' );
        $public                = get_post_types( [ '_builtin' => false, 'public' => true, 'show_in_menu' => true ], 'objects', 'and'  );
		$post_types = array_merge($core,$public);
		$post_types = array_map(function($post_type) {
            return array(
                'name' => esc_attr($post_type->name),
                'label' => esc_html($post_type->label),
                'hasArchive' => $post_type->has_archive,
            );
        }, $post_types);
		
		if(isset($post_types['attachment'])){
			unset($post_types['attachment']);
		}
		if(isset($post_types['e-landing-page'])){
			unset($post_types['e-landing-page']);
		}
		if(isset($post_types['elementor_library'])){
			unset($post_types['elementor_library']);
		}
        return $post_types;
	}
    
    /**
     * Method render_single_options
     *
     * @return HTML
     * @since   1.1.16
     */
    public static function render_single_options() {
        $template_options = '';
        $template_options .= '<optgroup label="Single Posts">';
        $template_options .= '<option value="single_all">All Single</option>';
        foreach (self::get_public_post_types() as $post_type) {
            $template_options .= '<option value="single_' . esc_attr($post_type['name']) . '">Single ' . esc_html($post_type['label']) . '</option>';
            //$exclude_options .= '<option value="exclude_single_' . esc_attr($post_type['name']) . '">Exclude Single ' . esc_html($post_type['label']) . '</option>';
        }
        $template_options .= '</optgroup>';
        return $template_options;
    }
    
    /**
     * Method render_archive_options
     *
     * @return html
     * @since   1.1.16
     */
    public static function render_archive_options() {
        $template_options = '';
        $template_options .= '<optgroup label="Archive">';
        $template_options .= '<option value="archive_all">All Archives</option>';
        foreach (self::get_public_taxonomies() as $taxonomy) {
            $template_options .= '<option value="archive_' . esc_attr($taxonomy['name']) . '">Archive ' . esc_html($taxonomy['label']) . '</option>';
            //$exclude_options .= '<option value="exclude_archive_' . esc_attr($taxonomy['name']) . '">Exclude Archive ' . esc_html($taxonomy['label']) . '</option>';
        }
        $template_options .= '<option value="archive_author">Author Archive</option>';
        $template_options .= '<option value="archive_search">Search Results</option>';
        $template_options .= '</optgroup>';

        $template_options .= '<optgroup label="Post Type Archives">';
        foreach (self::get_public_post_types() as $post_type) {
            if ($post_type['hasArchive']) {
                $template_options .= '<option value="archive_' . esc_attr($post_type['name']) . '">Archive ' . esc_html($post_type['label']) . '</option>';
                //$exclude_options .= '<option value="exclude_archive_' . esc_attr($post_type['name']) . '">Exclude Archive ' . esc_html($post_type['label']) . '</option>';
            }
        }
        $template_options .= '</optgroup>';
        $template_options .= '<optgroup label="Search Results">';
        foreach (self::get_public_post_types() as $post_type) {
            if ($post_type['hasArchive']) {
                $template_options .= '<option value="archive_search_' . esc_attr($post_type['name']) . '">' . esc_html($post_type['label']) . '</option>';
            }
        }
        $template_options .= '</optgroup>';
        return $template_options;
    }

    /**
     * Method render_postcard_options
     *
     * @return string
     * @since   1.1.28
     */
    public static function render_postcard_options() {
        $template_options = '';
        $template_options .= '<optgroup label="Posts">';
        foreach (self::get_public_post_types() as $post_type) {
            $template_options .= '<option value="postcard_' . esc_attr($post_type['name']) . '">Post Card ' . esc_html($post_type['label']) . '</option>';
        }
        $template_options .= '</optgroup>';
        return $template_options;
    }

    /**
     * Method render_termcard_options
     *
     * @return string
     * @since   1.1.28
     */
    public static function render_termcard_options() {
        $template_options = '';
        $template_options .= '<optgroup label="Taxonomies">';
        foreach (self::get_public_taxonomies() as $taxonomy) {
            $template_options .= '<option value="termcard_' . esc_attr($taxonomy['name']) . '">' . esc_html($taxonomy['label']) . '</option>';
        }
        $template_options .= '</optgroup>';
        return $template_options;
    }
    
    /**
     * Method render_pages_options
     *
     * @return string
     * @since   1.1.xx
     */
    public static function render_pages_options()
    {
        $template_options = '';
        $pages = get_pages(array(
            'sort_column' => 'post_title',
            'sort_order'  => 'ASC',
        ));
        if (! empty($pages)) {
            $template_options .= '<optgroup label="Specific Pages">';
            foreach ($pages as $page) {
                $template_options .= '<option value="single_page_' . esc_attr($page->ID) . '">' . esc_html($page->post_title) . '</option>';
            }
            $template_options .= '</optgroup>';
        }
        return $template_options;
    }
    
    /**
     * Method render_block_options
     *
     * @return HTML
     * @since   1.1.16
     */
    public static function render_block_options() {
        $template_options = '';
        $template_options .= '<option value="cubewp_post_loop_promotional_card">' . esc_html('CubeWP Post Loop Promotional Card' , 'cubewp-framework') . '</option>';
        $blocks = is_array(apply_filters('cubewp/theme_builder/blocks', array())) ? apply_filters('cubewp/theme_builder/blocks', array()): array();
        foreach ($blocks as $key => $label) {
            $template_options .= '<option value="' . esc_attr($key) . '">' . esc_html($label) . '</option>';
        }
        return $template_options;
    }
    
    /**
     * Method render_default_options
     *
     * @return HTML
     * @since   1.1.16
     */
    public static function render_default_options() {
        $template_options = '';
        $template_options .= '<optgroup label="General">';
        $template_options .= '<option value="entire_site">Entire Site</option>';
        $template_options .= '<option value="single_all">All Single</option>';
        $template_options .= '<option value="archive_all">All Archives</option>';
        $template_options .= '<option value="archive_author">Author Archive</option>';
        $template_options .= '<option value="archive_search">Search Results</option>';
        $template_options .= '<option value="home">Home Page</option>';
        $template_options .= '<option value="blog">Blog Page</option>';
        $template_options .= '</optgroup>';

        $template_options .= '<optgroup label="Single Post">';
        foreach (self::get_public_post_types() as $post_type) {
            $template_options .= '<option value="single_' . esc_attr($post_type['name']) . '">' . esc_html($post_type['label']) . '</option>';
        }
        $template_options .= '</optgroup>';
        $template_options .= '<optgroup label="Archive">';
        // foreach (self::get_public_post_types() as $post_type) {
        //     if ($post_type['hasArchive']) {
        //         $template_options .= '<option value="archive_' . esc_attr($post_type['name']) . '">' . esc_html($post_type['label']) . '</option>';
        //     }
        // }
        foreach (self::get_public_taxonomies() as $taxonomy) {
            $template_options .= '<option value="archive_' . esc_attr($taxonomy['name']) . '">' . esc_html($taxonomy['label']) . '</option>';
        }
        $template_options .= '</optgroup>';

        $template_options .= '<optgroup label="Search Results">';
        foreach (self::get_public_post_types() as $post_type) {
            if ($post_type['hasArchive']) {
                $template_options .= '<option value="archive_search_' . esc_attr($post_type['name']) . '">' . esc_html($post_type['label']) . '</option>';
            }
        }
        $template_options .= '</optgroup>';
        return $template_options;
    }
    
    /**
     * Method get_template_options
     *
     * @return JSON
     * @since   1.1.16
     */
    public static function get_template_options()
    {
        /* phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing */
        if (!isset($_POST['template_type'])) {
            wp_send_json_error(['message' => 'Template type not specified']);
        }
        /* phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing */
        $template_type = sanitize_text_field(wp_unslash($_POST['template_type']));
        $custom_template_types = apply_filters('cubewp/theme_builder/options/register', array());
        $template_options = '';
        //$exclude_options = '';
        if (isset($custom_template_types[$template_type])) {
            $custom_template_options = apply_filters('cubewp_tb_custom_template_options', '', $template_type);
            if (!empty($custom_template_options)) {
                $template_options .= $custom_template_options;
            } else {
                $template_options .= self::render_default_options();
            }
        } else {
            switch ($template_type) {
                case 'single':
                    $template_options .= self::render_single_options();
                    break;

                case 'archive':
                    $template_options .= self::render_archive_options();
                    break;

                case 'postcard':
                    $template_options .= self::render_postcard_options();
                    break;
                
                case 'termcard':
                    $template_options .= self::render_termcard_options();
                    break;

                case 'block':
                    $template_options .= self::render_block_options();
                    break;

                case 'header':
                case 'footer':
                    $template_options .= self::render_default_options();
                    $template_options .= self::render_pages_options();
                    break;

                case '404':
                case 'mega-menu':
                case 'shop':
                    $template_options .= '<option value="all">all</option>';
                    break;

                default:
                    $template_options .= self::render_default_options();
                    break;
            }
        }
        wp_send_json_success([
            'template_options' => $template_options,
            //'exclude_options' => $exclude_options
        ]);
    }

    public static function init() {
        $CubeClass = __CLASS__;
        new $CubeClass;
    }

}
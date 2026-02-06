<?php
/**
 * CubeWp Theme builder for display dynamic templates
 *
 * @version 1.0.0
 * @package cubewp/cube/mobules/theme builder
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals

if (! defined('ABSPATH')) {
    exit;
}


/**
 * CubeWp_Theme_Builder
 */
class CubeWp_Theme_Builder
{
    /**
     * Cache resolved template ids per request to avoid duplicate lookups.
     *
     * @var array<string, int|array|false>
     */
    protected static $template_cache = array();

    /**
     * Cache rendered Elementor output per template id for this request.
     *
     * @var array<int, string>
     */
    protected static $render_cache = array();

    /**
     * Transient prefix used when an external object cache is not available.
     */
    const RENDER_TRANSIENT_PREFIX = 'cubewp_tb_render_';

    public function __construct()
    {
        add_action('cubewp_theme_builder', array($this, 'display_cubewp_tb_admin_page'));
        add_filter('cubewp/theme_builder/blocks', array($this, 'hooks_from_settings'));

        add_action('save_post_cubewp-tb', array(__CLASS__, 'flush_template_cache'), 10, 1);
        add_action('delete_post', array(__CLASS__, 'maybe_flush_deleted_template_cache'), 10, 1);
        add_action('update_option_cwpOptions', array(__CLASS__, 'maybe_flush_cache_on_settings_change'), 10, 3);
    }

    /**
     * Method display_cubewp_tb_admin_page
     *
     * @return void
     */
    function hooks_from_settings($return)
    {
        global $cwpOptions;
        $hooks = isset($cwpOptions['cwp_tb_hooks']) ? $cwpOptions['cwp_tb_hooks'] : '';
        if (!empty($hooks)) {
            foreach ($hooks as $hook) {
                $return[$hook] = $hook;
            }
        }
        return $return;
    }

    /**
     * Method display_cubewp_tb_admin_page
     *
     * @return void
     */
    function display_cubewp_tb_admin_page()
    {
        // Create an instance of our custom list table class
        $theme_builders_list_table = new CubeWp_Theme_Builder_Table();
        $theme_builders_list_table->prepare_items();

        // Display the admin page
?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html__('CubeWP Theme Builder', 'cubewp-framework'); ?></h1>
            <?php
            if (!cubewp_check_if_elementor_active()) {
                echo '<div class="notice notice-error is-dismissible">
                    <p>
                        <strong><span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
                            CubeWP Theme Builder requires the Elementor plugin to be installed.
                        </strong>
                    </p>
               </div>';
            } else {
                echo '<a href="#" class="ctb-add-new-template page-title-action">' . esc_html__('Add New Template', 'cubewp-framework') . '</a>';
            }
            /* phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing */
            $search_type = isset($_GET['cwp-template-type']) && !empty($_GET['cwp-template-type']) ? sanitize_text_field(wp_unslash($_GET['cwp-template-type'])) : 'activated';
            $tabs = [
                'all' => esc_html__("All", 'cubewp-framework'),
                'activated' => esc_html__("Activated", 'cubewp-framework'),
                'deactivated' => esc_html__("Deactivated", 'cubewp-framework'),
                'header' => esc_html__("Header", 'cubewp-framework'),
                'footer' => esc_html__("Footer", 'cubewp-framework'),
                'single' => esc_html__("Single", 'cubewp-framework'),
                'archive' => esc_html__("Archive", 'cubewp-framework'),
                'postcard' => esc_html__("Post Cards", 'cubewp-framework'),
                'termcard' => esc_html__("Term Cards", 'cubewp-framework'),
                'block' => esc_html__("Hooks", 'cubewp-framework'),
                'shop' => esc_html__("Shop", 'cubewp-framework'),
                'mega-menu' => esc_html__("Mega Menu", 'cubewp-framework'),
                '404' => esc_html__("404", 'cubewp-framework'),
            ];
            $custom_options = apply_filters('cubewp/theme_builder/options/register', array());
            if (!empty($custom_options) && is_array($custom_options)) {
                foreach ($custom_options as $key => $label) {
                    if (is_string($label)) {
                        $tabs[$key] = $label;
                    }
                }
            }
            if (!class_exists('WooCommerce')) {
                unset($tabs['shop']);
            }
            if (!$theme_builders_list_table->check_if_post_available_by_status('inactive')) {
                unset($tabs['deactivated']);
            }
            if (!$theme_builders_list_table->check_if_post_available_by_status('publish')) {
                unset($tabs['activated']);
            }
            ?>

            <hr class="wp-header-end">
            <div class="wrap cwp-post-type-title flex-none margin-minus-20">
                <div class="cwp-post-type-title-nav">
                    <h1 class="wp-heading-inline"><?php esc_html_e("Theme Builder templates", 'cubewp-framework'); ?></h1>
                    <nav class="nav-tab-wrapper wp-clearfix">
                        <?php
                        foreach ($tabs as $key => $tab) {
                            $active_tab = !empty($search_type) && $search_type == $key  ? 'nav-tab-active' : '';
                            echo ' <a class="nav-tab ' . esc_attr($active_tab) . '" href="?page=cubewp-theme-builder&cwp-template-type=' . esc_attr($key) . '">' . esc_html($tab) . '</a>';
                        }
                        ?>
                    </nav>
                </div>
            </div>
            <!-- Display the list table -->
            <form method="post">
                <?php
                $theme_builders_list_table->display();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Method add_custom_popup_form
     *
     * @return void
     */
    public static function add_custom_popup_form()
    {
        if (CWP()->is_admin_screen('cubewp_theme_builder')) {
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('wp-jquery-ui-dialog');
        ?>
            <div id="ctb-add-template-dialog" title="<?php esc_attr_e('Add New Template', 'cubewp-framework'); ?>" style="display:none;">
                <form id="add-template-form">
                    <div class="cubewp-elements-builder cubewp-theme-builder-popup">
                        <div class="page-content">
                            <h1 class="heading"><?php esc_html_e('Templates Help You', 'cubewp-framework'); ?> <br><span><?php esc_html_e('Work Efficiently', 'cubewp-framework'); ?></span></h1>
                            <p class="paragraph">
                                <?php esc_html_e('CubeWP theme builder is a powerful tool in WordPress that empowers users to design and customize nearly every aspect of their website without needing to write code. Instead of relying on pre-built templates, a theme builder allows you to create unique layouts for various parts of your website, such as the header, footer, single post pages, archive pages, and even error pages (like 404 pages)', 'cubewp-framework'); ?>
                            </p>
                        </div>
                        <div class="cubewp-main-form">
                            <h2 class="heading">Choose Template Type</h2>
                            <div class="cubewp-form-fileds">
                                <label for="template_type">Select the type of template you want to work on</label>
                                <select name="template_type" id="template_type" required>
                                    <option value="">Select Template Type</option>
                                    <option value="header">Header</option>
                                    <option value="footer">Footer</option>
                                    <option value="single">Single</option>
                                    <option value="archive">Archive</option>
                                    <option value="postcard">Post Card</option>
                                    <option value="termcard">Term Card</option>
                                    <?php
                                    // Check if there are blocks available via PHP
                                    $blocks = apply_filters('cubewp/theme_builder/blocks', array());
                                    if (!empty($blocks)) {
                                        echo '<option value="block">Hooks</option>';
                                    }
                                    if (class_exists('WooCommerce')) {
                                        echo '<option value="shop">Shop Page</option>';
                                    }
                                    ?>
                                    <option value="mega-menu">Mega Menu</option>
                                    <option value="404">Error 404</option>
                                    <?php
                                    $custom_options = apply_filters('cubewp/theme_builder/options/register', array());
                                    if (!empty($custom_options) && is_array($custom_options)) {
                                        foreach ($custom_options as $key => $label) {
                                            // Ensure the label is a string for safe output
                                            if (is_string($label)) {
                                                echo '<option value="' . esc_attr($key) . '">' . esc_html($label) . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="cubewp-form-fileds">
                                <label for="template_name">Name your template</label>
                                <input type="text" name="template_name" id="template_name" placeholder="Enter Template Name..." required>
                            </div>
                            <div class="cubewp-form-fileds">
                                <label for="template_location">Display On</label>
                                <select name="template_location" id="template_location" required>
                                </select>
                            </div>
                            <div class="form-fileds-buttons">
                                <button type="button" name="submit" value="save" class="button button-primary cwp-save-template"><?php esc_html_e('Save', 'cubewp-framework'); ?></button>
                                <button type="button" name="submit" value="save-edit" class="button button-primary cwp-save-template"><?php esc_html_e('Save & Edit', 'cubewp-framework'); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
<?php
        }
    }


    /**
     * Method cubewp_theme_builder_template
     *
     * @return void
     */
    public static function cubewp_theme_builder_template()
    {
        // Check nonce
        if(!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'cubewp-admin-nonce')){
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'User not logged in']);
            return;
        }

        // Check if data is set
        
        if (!isset($_POST['data'])) {
            wp_send_json_error(['message' => 'No data received']);
            return;
        }

        // Parse and sanitize form data
        parse_str($_POST['data'], $form_data); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        $template_type = sanitize_text_field($form_data['template_type']);
        $template_name = sanitize_text_field($form_data['template_name']);
        $template_location = sanitize_text_field($form_data['template_location']);

        // Check if fields are empty
        if (empty($template_type)) {
            wp_send_json_error(['message' => 'Template type is required']);
            return;
        }

        if (empty($template_name)) {
            wp_send_json_error(['message' => 'Template name is required']);
            return;
        }

        if (empty($template_location)) {
            wp_send_json_error(['message' => 'Template location is required']);
            return;
        }

        $post_id = false;
        // FOR EDITING EXISTING TEMPLATE
        if (isset($form_data['ctb_edit_template_id']) && !empty($form_data['ctb_edit_template_id'])) {
            $post_id = $form_data['ctb_edit_template_id'];
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $template_name,
            ));
        }

        // Query for existing posts with the same name and location
        $args = array(
            'post_type' => 'cubewp-tb',
            'post_status' => 'publish',
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => 'template_type',
                    'value' => $template_type,
                    'compare' => '='
                ),
                array(
                    'key' => 'template_location',
                    'value' => $template_location,
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        );

        $existing_posts = new WP_Query($args);

        // Change status to 'inactive' for existing posts
        if ($existing_posts->have_posts()) {
            // Bulk load meta cache to prevent N+1 queries
            update_meta_cache('post', $existing_posts->posts);
            // Deactivate other published templates of these types at this location
            $singleActiveTypes = ['mega-menu', 'cubewp_post_promotional_card', 'postcard', 'termcard'];
            if (in_array($template_type, $singleActiveTypes, true)) {
                foreach ($existing_posts->posts as $existing_post_id) {
                    if ((int) $existing_post_id === (int) $post_id) {
                        continue; // don't deactivate the one we're editing/creating
                    }
                    wp_update_post([
                        'ID'          => $existing_post_id,
                        'post_status' => 'inactive',
                    ]);
                }
            }
        }

        // FOR CREATING NEW TEMPLATE
        if (!$post_id) {
            $new_post = array(
                'post_title'    => $template_name,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'     => 'cubewp-tb',
            );

            $post_id = wp_insert_post($new_post);
        }

        if ($post_id) {
            update_post_meta($post_id, 'template_type', $template_type);
            update_post_meta($post_id, 'template_location', $template_location);

            $response = '';
            if (isset($_POST['template_action']) && sanitize_text_field(wp_unslash($_POST['template_action'])) === 'save-edit') {
                $response = ['redirect' => get_edit_post_link($post_id, 'url')];
                $response['redirect'] = add_query_arg(['action' => 'elementor'], $response['redirect']);

                if ($template_type == 'single') {
                    $post_type_slug = CubeWp_Theme_Builder_Table::get_post_type_slug($template_location);
                    $tb_demo_id = CubeWp_Theme_Builder_Table::get_first_post_id_by_post_type($post_type_slug);
                    $response['redirect'] = add_query_arg(['action' => 'elementor', 'tb_demo_id' => $tb_demo_id], $response['redirect']);
                }
            }
            wp_send_json_success($response);
        } else {
            wp_send_json_error(['message' => 'There was an error saving the template.']);
        }
    }


    /**
     * Method get_current_template_post_id
     *
     * @param $type $type 
     *
     * @return void
     */
    public static function get_current_template_post_id($type = '')
    {
        if ($type == '') return false;

        if (array_key_exists($type, self::$template_cache)) {
            return self::$template_cache[$type];
        }

        global $post;

        $template_post_id = false;
        
        // Specific page override for header templates
        if (($type === 'header' || $type === 'footer') && is_page()) {
            $page_id = get_queried_object_id();
            if ($page_id) {
                $template_post_id = self::get_template_post_id_by_location('single_page_' . $page_id, $type);
                if ($template_post_id) {
                    return self::set_template_cache($type, (int) $template_post_id);
                }
            }
        }

        if ($type == 'block') {
            $template_ids = self::get_template_post_ids_by_location($type);
            return self::set_template_cache($type, !empty($template_ids) ? $template_ids : false);
        }

        if ($type == '404' && is_404()) {
            $template_post_id = self::get_template_post_id_by_location('all', $type);
            return self::set_template_cache($type, $template_post_id ? (int) $template_post_id : false);
        }

        if ($type == 'archive' && is_post_type_archive('product')) {
            $template_post_id = self::get_template_post_id_by_location('all', 'shop');
            return self::set_template_cache($type, $template_post_id ? (int) $template_post_id : false);
        }

        if (is_singular() && !is_front_page()) {
            // Single Post Page
            $post_type = get_post_type($post);
            $template_post_id = self::get_template_post_id_by_location('single_' . $post_type, $type);

            // If no specific template found, look for 'single_all'
            if (!$template_post_id) {
                $template_post_id = self::get_template_post_id_by_location('single_all', $type);
            }
        } elseif (is_front_page()) {
            // For Front page
            $template_post_id = self::get_template_post_id_by_location('home', $type);
        } elseif (is_home()) {
            // For Front page
            $template_post_id = self::get_template_post_id_by_location('blog', $type);
        } elseif (is_archive()) {

            // Archive Page
            if (is_author()) {
                $template_post_id = self::get_template_post_id_by_location('archive_author', $type);
            } elseif (is_search()) {
                $get_postType = get_post_type();
                if (!isset($get_postType) && empty($get_postType)) {
                    /* phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing */
                    if (isset($_GET['post_type'])) {
                        /* phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing */
                        $get_postType = sanitize_text_field(wp_unslash($_GET['post_type']));
                    }
                }
                $template_post_id = self::get_template_post_id_by_location('archive_search_' .  $get_postType, $type);
                // If no specific template found, look for 'single_all'
                if (!$template_post_id) {
                    $template_post_id = self::get_template_post_id_by_location('archive_search', $type);
                }
            } else {
                $taxonomy = get_queried_object();

                if (!empty($taxonomy->taxonomy)) {

                    $template_post_id = self::get_template_post_id_by_location('archive_' . $taxonomy->taxonomy, $type);
                } elseif (is_post_type_archive()) {

                    $template_post_id = self::get_template_post_id_by_location('archive_' . get_post_type(), $type);
                }
            }

            if (!$template_post_id) {
                // Default to archive_all
                $template_post_id = self::get_template_post_id_by_location('archive_all', $type);
            }
        }
        if (!$template_post_id) {
            // Default to Entire Site
            $template_post_id = self::get_template_post_id_by_location('entire_site', $type);
        }

        return self::set_template_cache($type, $template_post_id ? (int) $template_post_id : false);
    }

    /**
     * Method get_template_post_id_by_location
     *
     * @param $location $location 
     * @param $type $type 
     *
     * @return void
     */
    public static function get_template_post_id_by_location($location, $type)
    {
        $args = array(
            'post_type' => 'cubewp-tb',
            'post_status' => 'publish',
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'AND', // Ensure that both conditions are met
                array(
                    'key' => 'template_location',
                    'value' => $location,
                    'compare' => '='
                ),
                array(
                    'key' => 'template_type',
                    'value' => $type,
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $post_id = $query->posts[0];
            // Bulk load meta cache to prevent N+1 queries
            update_meta_cache('post', $query->posts);
            wp_reset_postdata();
            return $post_id;
        }

        return false;
    }

    /**
     * Method get_template_post_ids_by_location
     *
     * @param $type $type 
     *
     * @return void
     */
    public static function get_template_post_ids_by_location($type)
    {
        $args = array(
            'post_type' => 'cubewp-tb',
            'post_status' => 'publish',
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => 'template_type',
                    'value' => $type,
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $post_id = $query->posts;
            // Bulk load meta cache to prevent N+1 queries
            update_meta_cache('post', $query->posts);
            wp_reset_postdata();
            return $post_id;
        }

        return false;
    }

    /**
     * Method is_cubewp_theme_builder_active
     *
     * @param $type $type 
     *
     * @return void
     */
    public static function is_cubewp_theme_builder_active($type = '')
    {
        if (empty($type)) return false;

        if (self::get_current_template_post_id($type)) {
            return true;
        }
    }

    /**
     * Method do_cubewp_theme_builder
     *
     * @param $template $template 
     * @param $static_template_id $static_template_id 
     * @param $return $return 
     *
     * @return void
     */
    public static function do_cubewp_theme_builder($template = '', $static_template_id = 0, $return = false)
    { 
        if (empty($template)) return;
 
        $template_id = $static_template_id > 0 ? $static_template_id : self::get_current_template_post_id($template);

  
      
        if (! empty($template_id) && ! is_array($template_id)) {
            $should_bypass_cache       = self::should_bypass_render_cache($template_id);
            $allow_persistent_caching  = self::is_cache_enabled() && in_array($template, array('header', 'footer'), true);

            $cache_group = 'cubewp_theme_builder';
            $cache_key   = 'template_' . $template_id;

            if ($allow_persistent_caching && !$should_bypass_cache) {
                $cached_content = self::get_cached_render($cache_key, $cache_group);
               
              
                if (false !== $cached_content && null !== $cached_content) {
                    if ($return === true) {
                        return $cached_content;
                    }
                    echo /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */$cached_content;
                    return;
                }
            }

            if (isset(self::$render_cache[$template_id])) {
                $content = self::$render_cache[$template_id];
                if ($return === true) {
                    return $content;
                }
                echo /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */$content;
                return;
            }

            $elementor_frontend_builder = null;

            if (class_exists('\Elementor\Plugin')) {
                $plugin_instance = \Elementor\Plugin::$instance;
                if ($plugin_instance && isset($plugin_instance->frontend)) {
                    $elementor_frontend_builder = $plugin_instance->frontend;
                }
            }

            if (!$elementor_frontend_builder && class_exists('\Elementor\Frontend')) {
                static $standalone_frontend = null;
                if (!$standalone_frontend) {
                    $standalone_frontend = new \Elementor\Frontend();
                    $standalone_frontend->init();
                }
                $elementor_frontend_builder = $standalone_frontend;
            }

            if ($elementor_frontend_builder) {
                $content = $elementor_frontend_builder->get_builder_content_for_display($template_id, true);
                self::$render_cache[$template_id] = $content;

                if ($allow_persistent_caching && !$should_bypass_cache) {
                    self::set_cached_render($cache_key, $cache_group, $content, self::get_render_cache_ttl($template_id));
                }

                if ($return === true) {
                    return $content;
                } else {
                    echo /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */$content;
                }
            }
        }
    }

    /**
     * Method cubewp_set_custom_template
     *
     * @return void
     */
    public static function cubewp_set_custom_template()
    {
        if (is_singular('cubewp-tb')) {
            $template = '';
            $page_template = CUBEWP_FILES . 'templates/cubewp-template-single.php';
            if (file_exists($page_template)) {
                $template = $page_template;
            }
            return $template;
        }
    }

    /**
     * Method mega_menu_options
     *
     * @return void
     */
    public static function cwp_elementor_builder_options($template_type = '')
    {
        $args = array(
            'post_type' => 'cubewp-tb',
            'post_status' => 'publish',
            'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key' => 'template_type',
                    'value' => $template_type,
                    'compare' => '='
                ),
                array(
                    'key' => 'template_location',
                    'value' => 'all',
                    'compare' => '='
                )
            ),
            'fields' => 'ids'
        );
        $existing_posts = new WP_Query($args);
        $options = [];
        if ($existing_posts->have_posts()) {
            // Bulk load meta cache to prevent N+1 queries
            update_meta_cache('post', $existing_posts->posts);
            foreach ($existing_posts->posts as $existing_post_id) {
                $options[$existing_post_id] = get_the_title($existing_post_id);
            }
        }
        return $options;
    }



    public static function init()
    {
        $CubeClass = __CLASS__;
        new $CubeClass;
    }

    /**
     * Clear cached content when template changes.
     *
     * @param int $post_id
     *
     * @return void
     */
    public static function flush_template_cache($post_id)
    {
        if (get_post_type($post_id) !== 'cubewp-tb') {
            return;
        }

        $cache_group = 'cubewp_theme_builder';
        $cache_key   = 'template_' . $post_id;
        self::delete_cached_render($cache_key, $cache_group);

        if (isset(self::$render_cache[$post_id])) {
            unset(self::$render_cache[$post_id]);
        }

        if (!empty(self::$template_cache)) {
            foreach (self::$template_cache as $type => $cached_id) {
                if ((int) $cached_id === (int) $post_id) {
                    unset(self::$template_cache[$type]);
                }
            }
        }
    }

    /**
     * Clear cache when a template is deleted.
     *
     * @param int $post_id
     *
     * @return void
     */
    public static function maybe_flush_deleted_template_cache($post_id)
    {
        if (get_post_type($post_id) === 'cubewp-tb') {
            self::flush_template_cache($post_id);
        }
    }

    /**
     * Handle settings changes that affect caching.
     *
     * @param array|false $old_value
     * @param array|false $value
     * @param string      $option
     *
     * @return void
     */
    public static function maybe_flush_cache_on_settings_change($old_value, $value, $option = '')
    {
        if ($option !== 'cwpOptions') {
            return;
        }

        $old_value = is_array($old_value) ? $old_value : array();
        $value     = is_array($value) ? $value : array();

        $old_enabled = ! empty($old_value['cwp_tb_enable_cache']);
        $new_enabled = ! empty($value['cwp_tb_enable_cache']);

        if ($old_enabled !== $new_enabled) {
            self::flush_header_footer_cache();
        }
    }

    /**
     * Persist template cache values.
     *
     * @param string                $type
     * @param int|array|false $value
     *
     * @return int|array|false
     */
    protected static function set_template_cache($type, $value)
    {
        self::$template_cache[$type] = $value;
        return self::$template_cache[$type];
    }

    /**
     * Determine if theme builder caching is enabled.
     *
     * @return bool
     */
    protected static function is_cache_enabled()
    {
        global $cwpOptions;
        if (empty($cwpOptions)) {
            $cwpOptions = get_option('cwpOptions');
        }

        $enabled = ! empty($cwpOptions['cwp_tb_enable_cache']);

        /**
         * Filter whether theme builder caching should be enabled.
         *
         * @param bool $enabled
         */
        return (bool) apply_filters('cubewp/theme_builder/cache_enabled', $enabled);
    }

    /**
     * Determine if we should bypass render cache for current request.
     *
     * @param int $template_id
     *
     * @return bool
     */
    protected static function should_bypass_render_cache($template_id)
    {
        if (class_exists('\Elementor\Plugin')) {
            $plugin_instance = \Elementor\Plugin::$instance;
            if ($plugin_instance && method_exists($plugin_instance, 'editor')) {
                $editor = $plugin_instance->editor;
                if ($editor && method_exists($editor, 'is_edit_mode') && $editor->is_edit_mode()) {
                    return true;
                }
            }
        }

        if (isset($_GET['elementor-preview'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return true;
        }

        /**
         * Allow overriding cache bypass logic.
         */
        return (bool) apply_filters('cubewp/theme_builder/bypass_cache', false, $template_id);
    }

    /**
     * Compute cache TTL for rendered template.
     *
     * @param int $template_id
     *
     * @return int
     */
    protected static function get_render_cache_ttl($template_id)
    {
        /**
         * Filter the cache TTL for theme builder rendered output.
         */
        return (int) apply_filters('cubewp/theme_builder/cache_ttl', HOUR_IN_SECONDS, $template_id);
    }

    /**
     * Flush cached renders for all header and footer templates.
     *
     * @return void
     */
    protected static function flush_header_footer_cache()
    {
        $args = array(
            'post_type'      => 'cubewp-tb',
            'post_status'    => 'any',
            'fields'         => 'ids',
            'posts_per_page' => -1,
            'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                array(
                    'key'     => 'template_type',
                    'value'   => array('header', 'footer'),
                    'compare' => 'IN',
                ),
            ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            // Bulk load meta cache to prevent N+1 queries
            update_meta_cache('post', $query->posts);
            foreach ($query->posts as $post_id) {
                self::flush_template_cache($post_id);
            }
        }

    }

    /**
     * Check if object cache is enabled and functional.
     * Falls back to SQL cache (transients) if object cache is not available.
     *
     * @return bool True if object cache is available and working, false otherwise.
     */
    protected static function is_object_cache_available()
    {
        // Check if WordPress reports external object cache is available
        if (!wp_using_ext_object_cache()) {
            return false;
        }

        // Verify object cache is actually functional by testing it
        static $cache_available = null;
        
        if ($cache_available === null) {
            $test_key = 'cubewp_tb_cache_test_' . time();
            $test_value = 'test_' . wp_rand(1000, 9999);
            $test_group = 'cubewp_theme_builder';
            
            // Try to set and get a test value
            $set_result = wp_cache_set($test_key, $test_value, $test_group, 60);
            $get_result = wp_cache_get($test_key, $test_group);
            
            // Clean up test value
            wp_cache_delete($test_key, $test_group);
            
            // Object cache is available if set succeeded and get returned the same value
            $cache_available = ($set_result !== false && $get_result === $test_value);
        }
        
        return $cache_available;
    }

    /**
     * Retrieve cached render using object cache or transients (SQL cache).
     * Automatically falls back to SQL cache if object cache is not available.
     *
     * @param string $cache_key
     * @param string $cache_group
     *
     * @return string|false|null
     */
    protected static function get_cached_render($cache_key, $cache_group)
    {
        // Use object cache if available, otherwise fall back to SQL cache (transients)
        if (self::is_object_cache_available()) {
            return wp_cache_get($cache_key, $cache_group);
        }

        // Fall back to SQL cache using transients
        return get_transient(self::RENDER_TRANSIENT_PREFIX . $cache_key);
    }

    /**
     * Store cached render using object cache or transients (SQL cache).
     * Automatically falls back to SQL cache if object cache is not available.
     *
     * @param string $cache_key
     * @param string $cache_group
     * @param string $content
     * @param int    $ttl
     *
     * @return void
     */
    protected static function set_cached_render($cache_key, $cache_group, $content, $ttl)
    {
        // Use object cache if available, otherwise fall back to SQL cache (transients)
        if (self::is_object_cache_available()) {
            wp_cache_set($cache_key, $content, $cache_group, $ttl);
            return;
        }

        // Fall back to SQL cache using transients
        set_transient(self::RENDER_TRANSIENT_PREFIX . $cache_key, $content, $ttl);
    }

    /**
     * Delete cached render using object cache or transients (SQL cache).
     * Automatically falls back to SQL cache if object cache is not available.
     *
     * @param string $cache_key
     * @param string $cache_group
     *
     * @return void
     */
    protected static function delete_cached_render($cache_key, $cache_group)
    {
        // Use object cache if available, otherwise fall back to SQL cache (transients)
        if (self::is_object_cache_available()) {
            wp_cache_delete($cache_key, $cache_group);
            return;
        }

        // Fall back to SQL cache using transients
        delete_transient(self::RENDER_TRANSIENT_PREFIX . $cache_key);
    }
}
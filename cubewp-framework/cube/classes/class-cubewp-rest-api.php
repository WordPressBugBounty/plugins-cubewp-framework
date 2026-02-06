<?php

/**
 * CubeWp rest api for custom field data.
 *
 * @version 1.0
 * @package cubewp/cube/classes
 */

if (! defined('ABSPATH')) {
	exit;
}

/**
 * CubeWp_Rest_API
 */
class CubeWp_Rest_API extends WP_REST_Controller
{

	//Query Type.
	const F_TYPE = 'fields_type';

	//Query source.
	const F_SOURCE = 'fields_source';

	//Query source.
	const F_INPUT_TYPE = 'fields_input_type';

	//Query Name.
	const F_NAME = 'field_name';

	//Query ID.
	const P_ID = 'post_id';

	//Query POST TYPE.
	const POST_TYPE = 'post_type';

	//Query FORM TYPE.
	const FORM_TYPE = 'form_type';

	//Query PLAN ID.
	const PLAN_ID = 'plan_id';

	//Query CWP_QUERY.
	const CWP_QUERY = 'cwp_query';

	public $CF_namespace = '';
	public $base = '';
	public $custom_fields = '';
	public $cubewp_stats = '';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->CF_namespace = 'cubewp-custom-fields/v1';
		$this->FORMS_namespace = 'cubewp-forms/v1';
		$this->QUERY_namespace = 'cubewp-posts/v1';
		$this->base = 'render';
		$this->custom_fields = 'custom_fields';
		$this->cwp_query = 'query';
		$this->cubewp_forms = 'get_form';
		$this->edit_post = 'edit_post';
		$this->rest_field_init();
		$this->register_routes();
	}


	public function rest_field_init()
	{
		register_rest_field(self::get_types(), 'cubewp_post_meta', [
			'get_callback'    => [__CLASS__, 'get_post_meta'],
			'update_callback' => [__CLASS__, 'update_post_meta'],
		]);
		register_rest_field(self::get_types(), 'taxonomies', [
			'get_callback'    => [__CLASS__, 'get_taxonomies'],
			'update_callback' => '',
		]);
		register_rest_field('user', 'cubewp_user_meta', [
			'get_callback'    => [__CLASS__, 'get_user_meta'],
			'update_callback' => [__CLASS__, 'update_user_meta'],
		]);
		register_rest_field(self::get_types('taxonomy'), 'cubewp_term_meta', [
			'get_callback'    => [__CLASS__, 'get_term_meta'],
			'update_callback' => [__CLASS__, 'update_term_meta'],
		]);
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 */
	public function register_routes()
	{

		register_rest_route(
			'cubewp-framework/v1',
			'/verify',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => function () {
						return true;
					},
					'permission_callback' => function () {
						return true;
					},
					'args'                => [],
				),
			)
		);

		register_rest_route(
			$this->CF_namespace,
			'/' . $this->base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_render_field'),
					'permission_callback' => array($this, 'get_permission_check'),
					'args'                => $this->get_render_params(),
				),
			)
		);
		register_rest_route(
			$this->CF_namespace,
			'/' . $this->custom_fields,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_custom_fields'),
					'permission_callback' => array($this, 'get_permission_check'),
					'args'                => $this->get_render_params(),
				),
			)
		);
		register_rest_route(
			$this->QUERY_namespace,
			'/' . $this->cwp_query,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_cubewp_posts'),
					'permission_callback' => array($this, 'get_posts_permission_check'),
					'args'                => $this->get_render_params(),
				),
			)
		);

		register_rest_route(
			$this->QUERY_namespace,
			'/query-new',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'get_cubewp_posts_object'),
					'permission_callback' => array($this, 'get_posts_permission_check'),
					'args'                => $this->get_render_params(),
				),
			)
		);

		register_rest_route(
			$this->FORMS_namespace,
			'/' . $this->cubewp_forms,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'cubewp_get_forms'),
					'permission_callback' => function () {
						return true;
					},
					'args'                => $this->get_render_params(),
				),
			)
		);

		register_rest_route(
			$this->FORMS_namespace,
			'/' . $this->edit_post,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array($this, 'cubewp_update_custom_post_type'),
					'permission_callback' => array($this, 'get_permission_check'),
					'args'                => $this->get_render_params(),
				),
			)
		);
	}

	/**
	 * Checks if a given request has permission to access content.
	 *
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has search access, WP_Error object otherwise.
	 */
	public function get_permission_check($request)
	{
		return current_user_can('edit_posts');
	}

	/**
	 * Checks if a given request has permission to access posts via query endpoints.
	 * Allows public read access but respects WordPress post visibility rules.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_posts_permission_check($request)
	{
		// Allow public read access (posts will be filtered for visibility)
		return true;
	}

	/**
	 * Retrieves the query params for the search results.
	 *
	 * @return array Collection parameters.
	 */
	public function get_render_params()
	{
		$query_params  = parent::get_collection_params();

		$query_params[self::F_TYPE] = array(
			'description' => __('The custom field Key.', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::F_SOURCE] = array(
			'description' => __('The source of the content', 'cubewp-framework'),
			'type'        => 'string',
			'default'     => 'post',
		);
		$query_params[self::F_NAME] = array(
			'description' => __('The custom field Name.', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::F_INPUT_TYPE] = array(
			'description' => __('The custom field Type.', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::P_ID] = array(
			'description' => __('The custom field Name.', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::POST_TYPE] = array(
			'description' => __('Post type', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::FORM_TYPE] = array(
			'description' => __('Form type', 'cubewp-framework'),
			'type'        => 'string',
		);
		$query_params[self::PLAN_ID] = array(
			'description' => __('Plan ID', 'cubewp-framework'),
			'type'        => 'string',
		);
		return $query_params;
	}

	public function get_cubewp_posts($request)
	{
		$cwp_query =   $request->get_param(self::CWP_QUERY);
		if ($cwp_query) {
			$query  = new CubeWp_Query($cwp_query);
			$posts  = $query->cubewp_post_query();
			if ($posts->have_posts()) {
				// Filter posts for visibility and security
				$filtered_posts = array();
				foreach ($posts->posts as $post) {
					// Check if user can read this post
					if (! $this->can_user_read_post($post)) {
						continue;
					}
					
					// Remove sensitive fields
					$safe_post = $this->sanitize_post_for_response($post);
					if ($safe_post) {
						$filtered_posts[] = $safe_post;
					}
				}
				
				$data = array(
					'total_posts'    => count($filtered_posts),
					'paged' => isset($posts->query['paged']) ? $posts->query['paged'] : 1,
					'max_num_pages' => $posts->max_num_pages,
					'posts' => $filtered_posts,
				);
				return $data;
			} else {
				return 'Sorry no post available.';
			}
		}
	}

	public function get_cubewp_posts_object($request)
	{
		$cwp_query =   $request->get_param(self::CWP_QUERY);
		if ($cwp_query) {
			$query  = new CubeWp_Query($cwp_query);
			$posts  = $query->cubewp_post_query();
			if ($posts->have_posts()) {

				$return = [];
				while ($posts->have_posts()) {
					$posts->the_post();
					$post_id = get_the_ID();
					$post = get_post($post_id);
					
					// Check if user can read this post
					if (! $this->can_user_read_post($post)) {
						continue;
					}
					
					$return[$post_id]['ID'] = $post_id;
					$return[$post_id]['title'] = get_the_title();
					if (has_post_thumbnail()) {
						$return[$post_id]['profileImg'] = get_the_post_thumbnail_url(get_the_ID(), 'full'); // 'full' can be changed to any registered image size
					} else {
						$return[$post_id]['profileImg'] = '';
					}
					$post_terms = array();
					$taxonomies = get_object_taxonomies(get_post_type($post_id));
					if (! empty($taxonomies) && is_array($taxonomies)) {
						foreach ($taxonomies as $taxonomy) {
							$all_terms = get_the_terms($post_id, $taxonomy);
							if (!is_wp_error($all_terms) && !empty($all_terms)) {
								foreach ($all_terms as $all_term) {
									$post_terms[$taxonomy][] = $all_term->name;
								}
							}
						}
					}
					$return[$post_id]['taxonomies'] = isset($post_terms) && ! empty($post_terms) ? array_filter($post_terms) : array();
					
					// Get and filter post meta - only expose safe/public meta
					$post_meta = $this->get_safe_post_meta($post_id);

					$return[$post_id]['post_meta'] = $post_meta;
				}
				$data = array(
					'total_posts'    => count($return),
					'paged' => isset($posts->query['paged']) ? $posts->query['paged'] : 1,
					'max_num_pages' => $posts->max_num_pages,
					'posts' => $return,

				);
				return $data;
			} else {
				return 'Sorry no post available.';
			}
		}
	}

	/**
	 * Retrieves cubewp Forms.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */

	public function cubewp_get_forms($request)
	{
		$post_type =   $request->get_param(self::POST_TYPE);
		$form_type =   $request->get_param(self::FORM_TYPE);
		$plan_id =   $request->get_param(self::PLAN_ID);
		if ($form_type == 'post_type') {
			return $this->cubewp_get_post_type_form($post_type, $form_type, array(), $plan_id);
		} elseif ($form_type == 'search_fields') {
			return $this->cubewp_get_search_form($post_type);
		} elseif ($form_type == 'search_filters') {
			return $this->cubewp_get_search_filters($post_type);
		}
	}

	public function cubewp_get_search_form($type = '')
	{
		$search_fields = new CubeWp_Frontend_Search_Fields;
		$search_fields->custom_fields      =  CWP()->get_custom_fields('post_types');
		$cwp_search_fields        =   CWP()->get_form('search_fields');
		if (!empty($cwp_search_fields)) {
			$search_fields->search_fields =  isset($cwp_search_fields[$type]['fields']) ? $cwp_search_fields[$type]['fields'] : array();
			$fields = $search_fields->cwp_search_form_fields();
			$cwp_search_fields[$type]['fields'] = $fields;
			return $cwp_search_fields[$type];
		}
	}

	public function cubewp_get_search_filters($post_type = '')
	{
		$cwp_search_filters        =   CWP()->get_form('search_filters');
		if (!empty($cwp_search_filters[$post_type]['fields']) && count($cwp_search_filters[$post_type]['fields']) > 0) {
			if (isset($cwp_search_filters[$post_type]['fields']) && !empty($cwp_search_filters[$post_type]['fields'])) {
				$fields = [];
				foreach ($cwp_search_filters[$post_type]['fields'] as $field_name => $search_filter) {
					$fields[$field_name] = CubeWp_Frontend_Search_Filter::get_filters_fields($search_filter, $field_name);
				}
				$cwp_search_filters[$post_type]['fields'] = $fields;
			}
			return $cwp_search_filters;
		}
	}

	public function cubewp_get_post_type_form($type = '', $form_type = '', $post_content = array(), $plan_id = 0)
	{
		$cwp_form = CWP()->get_form($form_type);

		$form_fields = isset($cwp_form[$type]) ? $cwp_form[$type] : array();
		if (isset($post_content) && !empty($post_content)) {
			$plan_id = get_post_meta($post_content->ID, 'plan_id', true);
		}
		if (!empty($plan_id) && $plan_id > 0) {
			$form_fields  =  isset($form_fields[$plan_id]) ? $form_fields[$plan_id] : array();
		}
		if (empty($form_fields) || ! isset($form_fields['groups']) || empty($form_fields['groups'])) {
			return 'Sorry! You can\'t submit post due to empty form fields.';
		}
		$sections = [];
		if (isset($form_fields['groups']) && !empty($form_fields['groups'])) {
			$section_number = 1;

			foreach ($form_fields['groups'] as $key => $section_data) {
				$sections[$key]['total_sections'] = count($form_fields['groups']);
				$sections[$key]['section_number'] = $section_number;
				$sections[$key]['section_id'] = $section_data['section_id'];
				$sections[$key]['section_class'] = $section_data['section_class'];
				$sections[$key]['section_title'] = $section_data['section_title'];
				$sections[$key]['section_description'] = $section_data['section_description'];
				if (wp_is_serving_rest_request()) {
					$sections[$key]['fields'] = (new CubeWp_Frontend_Form)->fields($type, $section_data['fields'], $post_content);
				}
				$section_number++;
			}
			$form_fields['groups'] = $sections;
		}

		return wp_send_json($form_fields);
	}

	public function cubewp_update_custom_post_type($request)
	{
		$type =   $request->get_param(self::POST_TYPE);
		$form_type =   $request->get_param(self::FORM_TYPE);
		$cwp_form = CWP()->get_form($form_type);

		$pid = $request->get_param(self::P_ID);
		$url = get_permalink();
		if (!empty($pid) && $pid > 0) {
			$pid = sanitize_text_field($pid);
			$post_content = get_post($pid);
			if ($post_content->post_author != get_current_user_id()) {
				return 'Sorry! You can\'t update this post.';
			}
			if ($form_type == 'post_type') {
				return $this->cubewp_get_post_type_form($type, $form_type, $post_content);
			}
		}
	}

	/**
	 * Retrieves Custom field value.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_render_field($request)
	{
		$args = [];
		$args['f_type']  = $request->get_param(self::F_TYPE);
		$args['f_name']  = $request->get_param(self::F_NAME);
		$args['p_id'] = $request->get_param(self::P_ID);
		if ($args['f_type']  == 'user_custom_fields') {
			$args['p_id']  = get_post_field('post_author', $args['p_id']);
		}
		$value = get_any_field_value($args);
		return wp_send_json($value);
	}

	/**
	 * Retrieves Custom fields.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_custom_fields($request)
	{
		$source        = $request->get_param(self::F_SOURCE);
		$type          = $request->get_param(self::F_TYPE);
		$input_type    = $request->get_param(self::F_INPUT_TYPE);
		$output = array();
		$output[''] = 'Select Field';
		if ($type == 'post_custom_fields' || $type == 'user_custom_fields') {
			if (isset($source) && !empty($source)) {
				if ($type == 'post_custom_fields') {
					$groups = cwp_get_groups_by_post_type($source);
				} elseif ($type == 'user_custom_fields') {
					$groups = cwp_get_groups_by_user_role($source);
				}
				if (isset($groups) && !empty($groups)) {
					foreach ($groups as $group) {
						$fields = get_post_meta($group, '_cwp_group_fields', true);
						if ($type == 'post_custom_fields') {
							$fields = isset($fields) && !empty($fields) ? explode(',', $fields) : '';
						} elseif ($type == 'user_custom_fields') {
							$fields = isset($fields) && !empty($fields) ? json_decode($fields, true) : '';
						}
						if (is_array($fields)) {
							foreach ($fields as $field) {
								if ($type == 'post_custom_fields') {
									$option = get_field_options($field);
								} elseif ($type == 'user_custom_fields') {
									$option = get_user_field_options($field);
								}
								$field_type = $option['type'];
								if ($input_type) {
									$input_typeArray = isset($input_type) && !empty($input_type) ? explode(',', $input_type) : array();
									if (in_array($field_type, $input_typeArray)) {
										$output[$field] = $option['label'];
									}
								}
							}
						}
					}
				}
			}
		} elseif ($type == 'taxonomy_custom_fields') {
			// $fields = CWP()->get_custom_fields('taxonomy');
			// if(isset($fields) && !empty($fields)){
			// 	foreach($fields as $field){
			// 		if(is_array($fields)){
			// 			foreach($fields as $field){
			// 				$option = get_field_options($field);
			// 				$output[$field] = $option['label'];
			// 			}
			// 		}
			// 	}
			// }
		}
		return wp_send_json($output);
	}


	/**
	 * Get post meta for the rest API.
	 *
	 * @param array $object Post object.
	 *
	 * @return array
	 */
	public static function get_post_meta($object)
	{
		if ($object && isset($object['id'])) {
			$post_id   = $object['id'];
			$fields = CubeWp_Single_Cpt::cubewp_post_metas($post_id, true);
			if (isset($object['type']) && $object['type'] == 'price_plan') {
				$fields = get_post_meta($post_id);
			}
			return $fields;
		}
	}

	/**
	 * Get all taxonomies and terms for the rest API.
	 *
	 * @param array $object Post object.
	 *
	 * @return array
	 */
	public static function get_taxonomies($object)
	{
		if ($object && isset($object['id'])) {
			$post_id   = $object['id'];
			$post_terms = array();
			$taxonomies = get_object_taxonomies(get_post_type($post_id));
			if (! empty($taxonomies) && is_array($taxonomies)) {
				foreach ($taxonomies as $taxonomy) {
					$all_terms = get_the_terms($post_id, $taxonomy);
					if (!is_wp_error($all_terms) && !empty($all_terms)) {
						foreach ($all_terms as $all_term) {
							$post_terms[] = $all_term->name;
						}
					}
				}
			}

			return isset($post_terms) && ! empty($post_terms) ? array_filter($post_terms) : array();
		}
	}

	/**
	 * Update post meta for the rest API.
	 *
	 * @param string|array $data   Post meta values in either JSON or array format.
	 * @param object       $object Post object.
	 */
	public static function update_post_meta($data, $object)
	{
		$data = is_array($data) ? $data : array();
		foreach ($data as $field_id => $value) {
			$options = get_field_options($field_id);
			$meta_val = !empty($value) ? $value : '';
			if ($options['type'] == 'google_address') {
				if (isset($meta_val['address']) && !empty($meta_val['address'])) {
					update_post_meta($object->ID, $field_id, $meta_val['address']);
				} elseif (isset($meta_val['lat']) && !empty($meta_val['lat'])) {
					update_post_meta($object->ID, $field_id . '_lat', $meta_val['lat']);
				} elseif (isset($meta_val['lng']) && !empty($meta_val['lng'])) {
					update_post_meta($object->ID, $field_id . '_lng', $meta_val['lng']);
				}
			} elseif ($options['type'] == 'repeating_field') {
				$repeater_vals =  get_post_meta($object->ID, $field_id, true);
				$sub_meta_val = [];
				for ($i = 0; $i < count($repeater_vals); $i++) {
					foreach ($repeater_vals[$i] as $k => $sub_field) {
						$org_val = $sub_field;
						$sub_data = $meta_val[$i][$k];
						$lat_key = str_replace("_lat", "", $k);
						$lng_key = str_replace("_lng", "", $k);
						$sub_lat_data = $meta_val[$i][$lat_key];
						$sub_lng_data = $meta_val[$i][$lng_key];
						$subOptions = get_field_options($k);
						if ($subOptions['type'] == 'google_address') {
							if (isset($sub_data['value']['address']) && !empty($sub_data['value']['address'])) {
								$sub_meta_val[$i][$k] = $sub_data['value']['address'];
							} else {
								$sub_meta_val[$i][$k] = $org_val;
							}
						} else {
							if (isset($sub_lat_data['value']['lat']) || isset($sub_lng_data['value']['lng'])) {
								if (isset($sub_lat_data['value']['lat']) && !empty($sub_lat_data['value']['lat'])) {
									$sub_meta_val[$i][$lat_key . '_lat'] = $sub_lat_data['value']['lat'];
								}
								if (isset($sub_lng_data['value']['lng']) && !empty($sub_lng_data['value']['lng'])) {
									$sub_meta_val[$i][$lng_key . '_lng'] = $sub_lng_data['value']['lng'];
								}
							} else {
								if (isset($sub_data['value']) && !empty($sub_data['value'])) {
									$sub_meta_val[$i][$k] = $sub_data['value'];
								} else {
									$sub_meta_val[$i][$k] = $org_val;
								}
							}
						}
					}
				}
				update_post_meta($object->ID, $field_id, $sub_meta_val);
			} else {
				update_post_meta($object->ID, $field_id, $meta_val);
			}
		}
	}

	/**
	 * Get term meta for the rest API.
	 *
	 * @param array $object Term object.
	 *
	 * @return array
	 */
	public static function get_term_meta($object)
	{
		if ($object && isset($object['id'])) {
			$term_id = $object['id'];
			$term    = get_term($term_id);
			if (is_wp_error($term) || ! $term) {
				return [];
			}

			return CubeWp_Taxonomy_Metabox::cubewp_taxonomy_metas($term->taxonomy, $term_id);
		}
	}

	/**
	 * Update term meta for the rest API.
	 *
	 * @param string|array $data   Term meta values in either JSON or array format.
	 * @param object       $object Term object.
	 */
	public static function update_term_meta($data, $object)
	{
		$data = is_array($data) ? $data : array();
		foreach ($data as $field_id => $value) {
			$meta_val = isset($value['meta_value']) ? $value['meta_value'] : '';
			if (isset($meta_val['lng']) || isset($meta_val['lat']) || isset($meta_val['address'])) {
				if (isset($meta_val['address']) && !empty($meta_val['address'])) {
					update_term_meta($object->term_id, $field_id, $meta_val['address']);
				} elseif (isset($meta_val['lat']) && !empty($meta_val['lat'])) {
					update_term_meta($object->term_id, $field_id . '_lat', $meta_val['lat']);
				} elseif (isset($meta_val['lng']) && !empty($meta_val['lng'])) {
					update_term_meta($object->term_id, $field_id . '_lng', $meta_val['lng']);
				}
			} else {
				update_term_meta($object->term_id, $field_id, $meta_val);
			}
		}
	}

	/**
	 * Get user meta for the rest API.
	 *
	 * @param array $object User object.
	 *
	 * @return array
	 */
	public static function get_user_meta($object)
	{
		if ($object && isset($object['id'])) {
			$user_id   = $object['id'];
			if (! $user_id) {
				return [];
			}

			return CubeWp_Custom_Fields::cubewp_user_metas($user_id, true);
		}
	}

	/**
	 * Update user meta for the rest API.
	 *
	 * @param string|array $data   User meta values in either JSON or array format.
	 * @param object       $object User object.
	 */
	public static function update_user_meta($data, $object)
	{
		$allowed_fields = self::get_all_cwp_field_names_by_roles();
		$data = is_array($data) ? $data : array();
		foreach ($data as $field_id => $value) {
			if ( !in_array( $field_id, $allowed_fields, true ) ) {
				continue;
			}
			$options = get_user_field_options($field_id);
			$meta_val = isset($value['meta_value']) ? $value['meta_value'] : '';
			if ($options['type'] == 'google_address') {
				if (isset($meta_val['address']) && !empty($meta_val['address'])) {
					update_user_meta($object->ID, $field_id, $meta_val['address']);
				} elseif (isset($meta_val['lat']) && !empty($meta_val['lat'])) {
					update_user_meta($object->ID, $field_id . '_lat', $meta_val['lat']);
				} elseif (isset($meta_val['lng']) && !empty($meta_val['lng'])) {
					update_user_meta($object->ID, $field_id . '_lng', $meta_val['lng']);
				}
			} elseif ($options['type'] == 'repeating_field') {
				$repeater_vals =  get_post_meta($object->ID, $field_id, true);
				$sub_meta_val = [];
				for ($i = 0; $i < count($repeater_vals); $i++) {
					foreach ($repeater_vals[$i] as $k => $sub_field) {
						$org_val = $sub_field;
						$sub_data = $meta_val[$i][$k];
						$lat_key = str_replace("_lat", "", $k);
						$lng_key = str_replace("_lng", "", $k);
						$sub_lat_data = $meta_val[$i][$lat_key];
						$sub_lng_data = $meta_val[$i][$lng_key];
						$subOptions = get_user_field_options($k);
						if ($subOptions['type'] == 'google_address') {
							if (isset($sub_data['value']['address']) && !empty($sub_data['value']['address'])) {
								$sub_meta_val[$i][$k] = $sub_data['value']['address'];
							} else {
								$sub_meta_val[$i][$k] = $org_val;
							}
						} else {
							if (isset($sub_lat_data['value']['lat']) || isset($sub_lng_data['value']['lng'])) {
								if (isset($sub_lat_data['value']['lat']) && !empty($sub_lat_data['value']['lat'])) {
									$sub_meta_val[$i][$lat_key . '_lat'] = $sub_lat_data['value']['lat'];
								}
								if (isset($sub_lng_data['value']['lng']) && !empty($sub_lng_data['value']['lng'])) {
									$sub_meta_val[$i][$lng_key . '_lng'] = $sub_lng_data['value']['lng'];
								}
							} else {
								if (isset($sub_data['value']) && !empty($sub_data['value'])) {
									$sub_meta_val[$i][$k] = $sub_data['value'];
								} else {
									$sub_meta_val[$i][$k] = $org_val;
								}
							}
						}
					}
				}
				update_user_meta($object->ID, $field_id, $sub_meta_val);
			} else {
				update_user_meta($object->ID, $field_id, $meta_val);
			}
		}
	}

	/**
	 * Get supported types in Rest API.
	 *
	 * @param string $type 'post' or 'taxonomy'.
	 *
	 * @return array
	 */
	private static function get_types($type = 'post')
	{
		$types = get_post_types([], 'objects');
		if ('taxonomy' === $type) {
			$types = get_taxonomies([], 'objects');
		}
		foreach ($types as $type => $object) {
			if (empty($object->show_in_rest)) {
				unset($types[$type]);
			}
		}

		return array_keys($types);
	}

	/**
	 * Get supported meta keys.
	 *
	 * @return array
	 */
	public static function get_all_cwp_field_names_by_roles()
	{
		global $wp_roles;

		$all_roles = $wp_roles->roles;
		$field_names = array();
		$processed_group_ids = array(); // Prevent duplicate processing

		foreach ($all_roles as $role_key => $role_data) {
			$group_ids = cwp_get_groups_by_user_role($role_key);

			if (!empty($group_ids)) {
				foreach ($group_ids as $group_id) {
					if (in_array($group_id, $processed_group_ids)) {
						continue;
					}

					$processed_group_ids[] = $group_id;

					$fields = cwp_get_user_fields_by_group_id($group_id);
					if (!empty($fields) && is_array($fields)) {
						$field_names = array_merge($field_names, $fields);
					}
				}
			}
		}

		return array_unique($field_names); // Remove duplicates
	}

	/**
	 * Check if the current user can read a specific post.
	 * Respects WordPress post visibility rules including password protection.
	 *
	 * @param WP_Post|object|int $post Post object or post ID.
	 * @return bool True if user can read the post, false otherwise.
	 */
	private function can_user_read_post($post)
	{
		if (is_numeric($post)) {
			$post = get_post($post);
		}
		
		if (! $post || ! is_object($post)) {
			return false;
		}
		
		// Check post status
		if ($post->post_status !== 'publish') {
			// Only allow non-published posts if user has edit permission
			if (! current_user_can('edit_post', $post->ID)) {
				return false;
			}
		}
		
		// Check password protection - password-protected posts should not be exposed via public API
		if (! empty($post->post_password)) {
			// Only allow if user has edit permission (admin/author)
			if (! current_user_can('edit_post', $post->ID)) {
				// For REST API, we don't have cookie-based password verification
				// So we exclude password-protected posts from public API responses
				return false;
			}
		}
		
		// Check if post type is publicly queryable
		$post_type = get_post_type_object($post->post_type);
		if (! $post_type) {
			return false;
		}
		
		if (! $post_type->publicly_queryable) {
			// Only allow if user can read this post type
			if (! current_user_can($post_type->cap->read_post, $post->ID)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Sanitize post object for API response by removing sensitive fields.
	 *
	 * @param WP_Post|object $post Post object.
	 * @return array|false Sanitized post array or false if post should be excluded.
	 */
	private function sanitize_post_for_response($post)
	{
		if (is_object($post)) {
			$post_array = (array) $post;
		} else {
			$post_array = $post;
		}
		
		// Remove sensitive fields
		unset($post_array['post_password']);
		
		// If post is password protected and user doesn't have edit permission, hide content
		if (! empty($post_array['post_password']) && ! current_user_can('edit_post', $post_array['ID'])) {
			// Don't expose password-protected content
			$post_array['post_content'] = '';
			$post_array['post_excerpt'] = '';
		}
		
		return $post_array;
	}

	/**
	 * Get safe post meta that can be exposed via API.
	 * Filters out private meta keys (starting with _) and sensitive data.
	 *
	 * @param int $post_id Post ID.
	 * @return array Filtered post meta array.
	 */
	private function get_safe_post_meta($post_id)
	{
		$all_meta = get_post_meta($post_id);
		$safe_meta = array();
		
		// Get list of CubeWP custom fields that should be exposed
		$cubewp_fields = array();
		if (class_exists('CubeWp_Single_Cpt')) {
			$cubewp_fields = CubeWp_Single_Cpt::cubewp_post_metas($post_id, true);
			if (is_array($cubewp_fields)) {
				$cubewp_fields = array_keys($cubewp_fields);
			}
		}
		
		foreach ($all_meta as $key => $values) {
			// Skip private meta keys (starting with _) unless they're registered CubeWP fields
			if (strpos($key, '_') === 0 && ! in_array($key, $cubewp_fields, true)) {
				continue;
			}
			
			// Skip sensitive WordPress internal meta
			$sensitive_keys = array(
				'_edit_lock',
				'_edit_last',
				'_wp_old_slug',
				'_wp_old_date',
			);
			if (in_array($key, $sensitive_keys, true)) {
				continue;
			}
			
			// Process meta values - get_post_meta returns array of values
			if (is_array($values) && ! empty($values)) {
				$value = $values[0]; // Get first value
				// Check if the value is serialized
				if (is_serialized($value)) {
					$safe_meta[$key] = maybe_unserialize($value);
				} else {
					$safe_meta[$key] = $value;
				}
			}
		}
		
		return $safe_meta;
	}

	public static function init()
	{
		$CubeClass = __CLASS__;
		new $CubeClass;
	}
}

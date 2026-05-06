<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;


class CubeWp_Tag_Post extends Data_Tag
{

	use CubeWp_Single_Page_Trait;

	public function get_name()
	{
		return 'cubewp-post-tag';
	}

	public function get_title()
	{
		return esc_html__('Fields type (post relation)', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-fields'];
	}

	public function get_categories()
	{
		return [
			Module::TEXT_CATEGORY,
			Module::URL_CATEGORY,
			Module::IMAGE_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	protected function register_controls()
	{
		$this->add_control(
			'field_type',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Data Source', 'cubewp-framework'),
				'description' => esc_html__(
					'Choose whether to display post data or user data (author, post author, or logged-in user depending on context).',
					'cubewp-framework'
				),
				'options' => [
					'post' => esc_html__('Post Fields', 'cubewp-framework'),
					'user' => esc_html__('User Fields', 'cubewp-framework'),
				],
				'default' => 'post',
			]
		);

		$options = get_fields_by_type(array('post'));

		$this->add_control(
			'user_selected_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select custom field', 'cubewp-framework'),
				'options' => $options,
				'condition' => [
					'field_type' => 'post',
				],
			]
		);

		$user_field_options = get_user_fields_by_type(array('post'));

		$this->add_control(
			'user_selected_user_field',
			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__('Select user field', 'cubewp-framework'),
				'options' => $user_field_options,
				'condition' => [
					'field_type' => 'user',
				],
			]
		);

		$this->add_control('content_type', [
			'type'      => Controls_Manager::SELECT,
			'label'     => esc_html__('Select content type', 'cubewp-framework'),
			'options'   => [
				'full'                   => esc_html__('Full Grid', 'cubewp-framework'),
				'title'                  => esc_html__('Post Title', 'cubewp-framework'),
				'content'                => esc_html__('Post Content', 'cubewp-framework'),
				'image'                  => esc_html__('Post Featured Image', 'cubewp-framework'),
				'URL'                    => esc_html__('Post URL', 'cubewp-framework'),
				'author'                 => esc_html__('Post Author Name', 'cubewp-framework'),
				'author_avatar'          => esc_html__('Post Author Avatar', 'cubewp-framework'),
				'author_url'             => esc_html__('Post Author URL', 'cubewp-framework'),
				'reverse_relation_count' => esc_html__('Count of posts linked to this post', 'cubewp-framework'),
				//'post_meta' => esc_html__( 'Post meta', 'cubewp-framework' ),
			],
			'default'   => 'title',
		]);
		$this->add_control('post_meta', [
			'type'        => Controls_Manager::TEXT,
			'label'       => esc_html__('Meta Key', 'cubewp-framework'),
			'description' => esc_html__('Put here post meta that is in relation with original post', 'cubewp-framework'),
			'condition'   => array(
				'content_type'         => 'post_meta',
			),
		]);

		$this->add_control('reverse_relation_post_type', [
			'type'        => Controls_Manager::TEXT,
			'label'       => esc_html__('Post type(s) to count', 'cubewp-framework'),
			'description' => esc_html__('Optional. Comma-separated post types (e.g. listing,post). Leave empty to count all published post types.', 'cubewp-framework'),
			'condition'   => [
				'field_type'    => 'post',
				'content_type'  => 'reverse_relation_count',
			],
		]);
	}

	public function get_value($options = array())
	{
		$field_type = $this->get_settings('field_type');
		$field_type = isset($field_type) ? $field_type : 'post';
		if ('user' === $field_type) {
			$field = $this->get_settings('user_selected_user_field');
		} else {
			$field = $this->get_settings('user_selected_field');
		}

		if (! $field) {
			return '';
		}
		$content_type = $this->get_settings('content_type');

		if (! $content_type) {
			return '';
		}

		// Reverse relation count: count posts that have the current post in the selected relation field.
		if ($content_type === 'reverse_relation_count' && $field_type === 'post') {
			$post_type_setting = $this->get_settings('reverse_relation_post_type');
			return self::get_reverse_relation_count($field, $post_type_setting);
		}

		if ('user' === $field_type) {
			$value = get_user_field_value($field);
		} else {
			$value = get_field_value($field);
		}
		if (! $value) {
			return '';
		}

		if ($content_type == 'full') {
			$args = array('container_class' => '', 'label' => '', 'value' => $value);
			return $this->field_post($args);
		} else {
			if (is_array($value)) {
				foreach ($value as $val) {
					$return = self::get_output($content_type, $val);
					if (! empty($return)) {
						return $return;
					}
				}

				return '';
			} else {
				return self::get_output($content_type, $value);
			}
		}
	}

	/**
	 * Count posts that have the current post ID in the given relation field (meta).
	 *
	 * @param string $field              Field key (e.g. from user_selected_field).
	 * @param string $post_type_setting  Optional comma-separated post types to limit the count.
	 * @return string Count as string for dynamic tag output.
	 */
	private static function get_reverse_relation_count($field, $post_type_setting = '')
	{
		$post_id = get_the_ID();
		if (function_exists('cubewp_is_elementor_editing') && cubewp_is_elementor_editing()
			&& function_exists('cubewp_check_if_elementor_active') && cubewp_check_if_elementor_active() && ! cubewp_check_if_elementor_active(true)) {
			if (function_exists('cubewp_get_elementor_preview_post_id')) {
				$preview_id = cubewp_get_elementor_preview_post_id();
				if (! empty($preview_id)) {
					$post_id = $preview_id;
				}
			}
		}
		if (empty($post_id)) {
			return '0';
		}

		$meta_key = $field;
		if (function_exists('get_field_options')) {
			$field_options = get_field_options($field);
			if (! empty($field_options['name'])) {
				$meta_key = $field_options['name'];
			}
		}

		$post_types = array();
		if (! empty($post_type_setting)) {
			$post_types = array_map('trim', explode(',', (string) $post_type_setting));
			$post_types = array_filter($post_types);
		}
		if (empty($post_types)) {
			$post_types = array_keys(get_post_types(array('public' => true)));
		}

		$meta_query = array(
			'relation' => 'OR',
			array(
				'key'     => $meta_key,
				'value'   => $post_id,
				'compare' => '=',
			),
			array(
				'key'     => $meta_key,
				'value'   => '"' . $post_id . '"',
				'compare' => 'LIKE',
			),
			array(
				'key'     => $meta_key,
				'value'   => ';i:' . $post_id . ';',
				'compare' => 'LIKE',
			),
		);

		$query = new \WP_Query(array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => $meta_query,
			'no_found_rows'  => true,
		));

		$count = $query->post_count;
		wp_reset_postdata();

		return (string) $count;
	}

	private static function get_output($content_type, $value)
	{
		if ($content_type == 'title') {
			$value = get_the_title($value);

			return cubewp_core_data($value);
		} else if ($content_type == 'content') {
			$value = get_the_content(null, false, $value);

			return cubewp_core_data($value);
		} else if ($content_type == 'URL') {
			$value = get_permalink($value);

			return cubewp_core_data($value);
		} else if ($content_type == 'author') {
			$value = get_the_author_meta("display_name", $value);

			return cubewp_core_data($value);
		} else if ($content_type == 'author_avatar') {
            $user_id = get_post_field('post_author', $value);  
            return [
                'id' => $user_id,
                'url' => get_avatar_url($user_id ),
            ];
		} else if ($content_type == 'author_url') {
			$value = get_author_posts_url($value);

			return cubewp_core_data($value);
		} else if ($content_type == 'image') {
			$returnArr = array();
			$imageID   = get_post_thumbnail_id($value);
			if ($imageID) {
				$returnArr = [
					'id'  => $imageID,
					'url' => wp_get_attachment_image_src($imageID, 'full')[0],
				];
			}

			return $returnArr;
		}

		return '';
	}
}

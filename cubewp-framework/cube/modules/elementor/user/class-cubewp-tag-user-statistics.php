<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class CubeWp_Tag_User_Statistics extends \Elementor\Core\DynamicTags\Tag
{

	public function get_name()
	{
		return 'cubewp-user-statistics';
	}

	public function get_title()
	{
		return esc_html__('User Statistics', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-user-data'];
	}

	public function get_categories()
	{
		return [
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
		];
	}

	protected function register_controls()
	{

		$this->add_control(
			'stat_type',
			[
				'label'   => esc_html__('Statistic Type', 'cubewp-framework'),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'total_posts',
				'options' => $this->get_statistics_options(),
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'       => esc_html__('Post Type', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_post_types(),
				'default'     => 'post',
				'multiple'    => false,
				'condition'   => [
					'stat_type' => ['total_posts', 'post_type_count'],
				],
				'description' => esc_html__('Select post type to count', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'reviewd_type',
			[
				'label'       => esc_html__('Reviews Type', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'post' => esc_html__('Post', 'cubewp-framework'),
					'user' => esc_html__('user', 'cubewp-framework'),
					'all' => esc_html__('All', 'cubewp-framework'),
				],
				'default'     => ['all'],
				'condition'   => [
					'stat_type' => ['total_reviews', 'reviews_received', 'reviews_submitted'],
				],
				'description' => esc_html__('Select review type', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'post_status',
			[
				'label'       => esc_html__('Post Status', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => [
					'publish' => esc_html__('Published', 'cubewp-framework'),
					'pending' => esc_html__('Pending', 'cubewp-framework'),
					'draft'   => esc_html__('Draft', 'cubewp-framework'),
					'private' => esc_html__('Private', 'cubewp-framework'),
					'trash'   => esc_html__('Trash', 'cubewp-framework'),
				],
				'default'     => ['publish'],
				'multiple'    => true,
				'condition'   => [
					'stat_type' => ['total_posts', 'post_type_count'],
				],
				'description' => esc_html__('Select post statuses to include', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'display_format',
			[
				'label'   => esc_html__('Display Format', 'cubewp-framework'),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'number',
				'options' => [
					'number'       => esc_html__('Number Only', 'cubewp-framework'),
					'number_label' => esc_html__('Number with Label', 'cubewp-framework'),
					'label_number' => esc_html__('Label with Number', 'cubewp-framework'),
					'custom'       => esc_html__('Custom Format', 'cubewp-framework'),
				],
			]
		);

		$this->add_control(
			'custom_format',
			[
				'label'       => esc_html__('Custom Format', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '{number} {label}',
				'placeholder' => esc_html__('{number} {label}', 'cubewp-framework'),
				'condition'   => [
					'display_format' => 'custom',
				],
				'description' => esc_html__('Use {number} for count and {label} for statistic label', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'custom_label',
			[
				'label'       => esc_html__('Custom Label', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('Posts', 'cubewp-framework'),
				'description' => esc_html__('Override the default label text', 'cubewp-framework'),
				'condition'   => [
					'display_format' => ['number_label', 'label_number', 'custom'],
				],
			]
		);

		$this->add_control(
			'plural_label',
			[
				'label'       => esc_html__('Plural Label', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('Posts', 'cubewp-framework'),
				'description' => esc_html__('Label for plural numbers (optional)', 'cubewp-framework'),
				'condition'   => [
					'display_format' => ['number_label', 'label_number', 'custom'],
				],
			]
		);

		$this->add_control(
			'singular_label',
			[
				'label'       => esc_html__('Singular Label', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('Post', 'cubewp-framework'),
				'description' => esc_html__('Label for singular number (optional)', 'cubewp-framework'),
				'condition'   => [
					'display_format' => ['number_label', 'label_number', 'custom'],
				],
			]
		);

		$this->add_control(
			'fallback_text',
			[
				'label'       => esc_html__('Fallback Text', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('0', 'cubewp-framework'),
				'default'     => esc_html__('0', 'cubewp-framework'),
				'description' => esc_html__('Text to display when count is zero or not available', 'cubewp-framework'),
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label'        => esc_html__('Show Icon', 'cubewp-framework'),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__('Yes', 'cubewp-framework'),
				'label_off'    => esc_html__('No', 'cubewp-framework'),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'icon',
			[
				'label'       => esc_html__('Icon', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::ICONS,
				'default'     => [
					'value'   => 'fas fa-chart-bar',
					'library' => 'solid',
				],
				'condition'   => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_position',
			[
				'label'     => esc_html__('Icon Position', 'cubewp-framework'),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'before',
				'options'   => [
					'before' => esc_html__('Before Text', 'cubewp-framework'),
					'after'  => esc_html__('After Text', 'cubewp-framework'),
				],
				'condition' => [
					'show_icon' => 'yes',
				],
			]
		);

		$this->add_control(
			'number_format',
			[
				'label'       => esc_html__('Number Format', 'cubewp-framework'),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'none',
				'options'     => [
					'none'            => esc_html__('No Formatting', 'cubewp-framework'),
					'comma_separated' => esc_html__('Comma Separated (1,234)', 'cubewp-framework'),
					'human_readable'  => esc_html__('Human Readable (1.2K)', 'cubewp-framework'),
				],
				'description' => esc_html__('Format the number for better readability', 'cubewp-framework'),
			]
		);
	}

	public function is_settings_required()
	{
		return true;
	}

	private function get_statistics_options()
	{
		$options = [
			'total_posts'       => esc_html__('Total Posts', 'cubewp-framework'),
			'post_type_count'   => esc_html__('Specific Post Type Count', 'cubewp-framework'),
			'total_comments'    => esc_html__('Total Comments', 'cubewp-framework'),
		];

		// Add review statistics if CubeWp_Reviews_load class exists
		if (class_exists('CubeWp_Reviews_load')) {
			$review_options = [
				'reviews_submitted' => esc_html__('Reviews Submitted', 'cubewp-framework'),
				'reviews_received'  => esc_html__('Reviews Received', 'cubewp-framework'),
				'total_reviews'     => esc_html__('Total Reviews (Submitted + Received)', 'cubewp-framework'),
			];
			$options = array_merge($options, $review_options);
		}

		// Add custom user meta count option
		$options['custom_meta_count'] = esc_html__('Custom Meta Field Count', 'cubewp-framework');

		return $options;
	}

	private function get_post_types()
	{
		$post_types = get_post_types([
			'public' => true,
		], 'objects');

		$options = [];
		foreach ($post_types as $post_type) {
			if ($post_type->name !== 'attachment') {
				$options[$post_type->name] = $post_type->label;
			}
		}

		return $options;
	}

	public function render()
	{
		$user_id = cubewp_get_elementor_tag_user_id();

		if (! $user_id) {
			echo esc_html($this->get_settings('fallback_text'));
			return;
		}

		$user = get_user_by('id', $user_id);

		if (! $user) {
			echo esc_html($this->get_settings('fallback_text'));
			return;
		}

		$stat_type   = $this->get_settings('stat_type');
		$reviewd_type = $this->get_settings('reviewd_type');
		if(is_array($reviewd_type)) {
			$reviewd_type = reset($reviewd_type);

		}
		$count       = 0;
		$default_label = '';

		// Get count based on stat type
		switch ($stat_type) {
			case 'total_posts':
				$count = $this->get_user_post_count($user_id);
				$default_label = esc_html__('Posts', 'cubewp-framework');
				break;

			case 'post_type_count':
				$post_type = $this->get_settings('post_type');
				$count = $this->get_user_post_type_count($user_id, $post_type);
				$post_type_obj = get_post_type_object($post_type);
				$default_label = $post_type_obj ? $post_type_obj->labels->name : esc_html__('Items', 'cubewp-framework');
				break;

			case 'total_comments':
				$count = $this->get_user_comment_count($user_id);
				$default_label = esc_html__('Comments', 'cubewp-framework');
				break;

			case 'reviews_submitted':
				if (class_exists('CubeWp_Reviews_load')) {
					$count = $this->get_reviews_submitted_count($user_id, $reviewd_type);
					$default_label = esc_html__('Reviews Submitted', 'cubewp-framework');
				}
				break;

			case 'reviews_received':
				if (class_exists('CubeWp_Reviews_load')) {
					$count = $this->get_reviews_received_count($user_id, $reviewd_type);
					$default_label = esc_html__('Reviews Received', 'cubewp-framework');
				}
				break;

			case 'total_reviews':
				if (class_exists('CubeWp_Reviews_load')) {
					$submitted = $this->get_reviews_submitted_count($user_id, $reviewd_type);
					$received = $this->get_reviews_received_count($user_id, $reviewd_type);
					$count = $submitted + $received;
					$default_label = esc_html__('Total Reviews', 'cubewp-framework');
				}
				break;

			case 'custom_meta_count':
				// You can extend this to handle custom meta field counts
				$count = 0;
				$default_label = esc_html__('Items', 'cubewp-framework');
				break;
		}

		// If count is 0 and we have a fallback, use it
		if ($count === 0 && $this->get_settings('fallback_text')) {
			$fallback = $this->get_settings('fallback_text');
			if (! empty($fallback) && $fallback !== '0') {
				echo esc_html($fallback);
				return;
			}
		}

		// Format the number
		$formatted_number = $this->format_number($count);

		// Get the display text
		$display_text = $this->get_display_text($formatted_number, $count, $default_label);

		// Add icon if enabled
		$display_text = $this->add_icon_to_text($display_text);

		echo $display_text;
	}

	private function get_user_post_count($user_id)
	{
		$post_status = $this->get_settings('post_status');

		if (empty($post_status) || ! is_array($post_status)) {
			$post_status = ['publish'];
		}

		$args = [
			'post_type'      => 'any',
			'post_status'    => $post_status,
			'author'         => $user_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$query = new WP_Query($args);
		return $query->found_posts;
	}

	private function get_user_post_type_count($user_id, $post_type)
	{
		$post_status = $this->get_settings('post_status');

		if (empty($post_status) || ! is_array($post_status)) {
			$post_status = ['publish'];
		}

		$args = [
			'post_type'      => $post_type,
			'post_status'    => $post_status,
			'author'         => $user_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$query = new WP_Query($args);
		return $query->found_posts;
	}

	private function get_user_comment_count($user_id)
	{
		global $wpdb;

		$count = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $wpdb->comments WHERE user_id = %d AND comment_approved = '1'",
			$user_id
		));

		return (int) $count;
	}

	private function get_reviews_submitted_count($user_id, $reviews_type)
	{
		$meta_query = [
			'relation' => 'AND',
			[
				'key'     => 'cwp_review_associated',
				'compare' => 'EXISTS',
			],
		];

		// Add review type condition if not 'all'
		if ($reviews_type !== 'all') {
			$meta_query[] = [
				'key'     => 'cwp_review_type',
				'value'   => $reviews_type,
				'compare' => '=',
			];
		}

		$args = [
			'post_type'      => 'cwp_reviews',
			'post_status'    => ['publish'],
			'author'         => $user_id,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => $meta_query,
		];

		$query = new WP_Query($args);
		return $query->found_posts;
	}

	private function get_reviews_received_count($user_id, $reviews_type)
	{
		$meta_query = [
			'relation' => 'AND',
			[
				'key'     => 'cwp_review_associated',
				'compare' => 'EXISTS',
			],
			[
				'key'     => 'cwp_review_associated_post_user',
				'value'   => $user_id,
				'compare' => '=',
			],
		];

		// Add review type condition if not 'all'
		if ($reviews_type !== 'all') {
			$meta_query[] = [
				'key'     => 'cwp_review_type',
				'value'   => $reviews_type,
				'compare' => '=',
			];
		}

		$args = [
			'post_type'      => 'cwp_reviews',
			'post_status'    => ['publish'],
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => $meta_query,
		];

		$query = new WP_Query($args);
		return $query->found_posts;
	}

	private function format_number($number)
	{
		$format = $this->get_settings('number_format');

		switch ($format) {
			case 'comma_separated':
				return number_format($number);

			case 'human_readable':
				return $this->human_readable_number($number);

			case 'none':
			default:
				return $number;
		}
	}

	private function human_readable_number($number)
	{
		if ($number >= 1000000) {
			return round($number / 1000000, 1) . 'M';
		} elseif ($number >= 1000) {
			return round($number / 1000, 1) . 'K';
		}
		return $number;
	}

	private function get_display_text($formatted_number, $original_number, $default_label)
	{
		$display_format = $this->get_settings('display_format');
		$custom_label = $this->get_settings('custom_label');
		$plural_label = $this->get_settings('plural_label');
		$singular_label = $this->get_settings('singular_label');
		$custom_format = $this->get_settings('custom_format');

		// Determine label
		$label = ! empty($custom_label) ? $custom_label : $default_label;

		// Handle singular/plural labels
		if ($original_number == 1 && ! empty($singular_label)) {
			$label = $singular_label;
		} elseif ($original_number != 1 && ! empty($plural_label)) {
			$label = $plural_label;
		}

		switch ($display_format) {
			case 'number':
				return esc_html($formatted_number);

			case 'number_label':
				return esc_html($formatted_number . ' ' . $label);

			case 'label_number':
				return esc_html($label . ' ' . $formatted_number);

			case 'custom':
				$text = str_replace(
					['{number}', '{label}'],
					[$formatted_number, $label],
					$custom_format
				);
				return esc_html($text);

			default:
				return esc_html($formatted_number);
		}
	}

	private function add_icon_to_text($text)
	{
		if ('yes' !== $this->get_settings('show_icon')) {
			return $text;
		}

		$icon = $this->get_settings('icon');
		$icon_position = $this->get_settings('icon_position');

		$icon_html = '';
		if (! empty($icon['value'])) {
			\Elementor\Plugin::instance()->icons_manager->enqueue_shim();
			$icon_html = \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true']);
		}

		if ('before' === $icon_position) {
			return $icon_html . ' ' . $text;
		} else {
			return $text . ' ' . $icon_html;
		}
	}
}

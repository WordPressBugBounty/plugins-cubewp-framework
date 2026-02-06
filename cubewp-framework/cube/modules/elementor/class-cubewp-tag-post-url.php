<?php
if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_Post_URL extends Tag
{
	public function get_name()
	{
		return 'cubewp-post-url-tag';
	}

	public function get_title()
	{
		return esc_html__('Post URL', 'cubewp-framework');
	}

	public function get_group()
	{
		return ['cubewp-single-fields'];
	}

	public function get_categories()
	{
		return [
			Module::URL_CATEGORY,
		];
	}

	public function is_settings_required()
	{
		return true;
	}

	public function render()
	{

		if (cubewp_is_elementor_editing()) {
			echo esc_url(get_post_permalink(cubewp_get_elementor_preview_post_id()));
		} else {
			echo esc_url(get_post_permalink());
		}
	}

	protected function register_controls() {}
}

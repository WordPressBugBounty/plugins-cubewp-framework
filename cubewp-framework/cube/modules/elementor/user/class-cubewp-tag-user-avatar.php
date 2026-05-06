<?php
if (! defined('ABSPATH')) {
    exit;
}

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;

class CubeWp_Tag_User_Avatar extends Data_Tag
{

    public function get_name()
    {
        return 'cubewp-user-avatar';
    }

    public function get_title()
    {
        return esc_html__('User Avatar', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-user-data'];
    }

    public function get_categories()
    {
        return [
            Module::IMAGE_CATEGORY,
        ];
    }

    public function is_settings_required()
    {
        return false;
    }

    protected function register_controls()
    {

        $this->add_control(
            'avatar_size',
            [
                'label'      => esc_html__('Avatar Size', 'cubewp-framework'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'default'    => [
                    'size' => 96,
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [
                        'min' => 16,
                        'max' => 512,
                    ],
                ],
            ]
        );
    }

    public function get_value($options = [])
    {
        $user_id = cubewp_get_elementor_tag_user_id();

        if (! $user_id) {
            return null;
        }

        $size_setting = $this->get_settings('avatar_size');
        $size         = ! empty($size_setting['size']) ? (int) $size_setting['size'] : 96;

        $avatar_url = get_avatar_url(
            $user_id,
            [
                'size' => $size,
            ]
        );

        if (! $avatar_url) {
            return null;
        }

        return [
            'id'  => (int) $user_id,
            'url' => esc_url($avatar_url),
        ];
    }
}

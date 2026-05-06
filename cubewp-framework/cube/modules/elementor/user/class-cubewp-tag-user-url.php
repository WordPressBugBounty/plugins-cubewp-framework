<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class CubeWp_Tag_User_Url extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-user-url';
    }

    public function get_title()
    {
        return esc_html__('User URL', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-user-data'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls()
    {

        $this->add_control(
            'url_type',
            [
                'label'   => esc_html__('URL Type', 'cubewp-framework'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'author',
                'options' => [
                    'website' => esc_html__('Website URL', 'cubewp-framework'),
                    'profile' => esc_html__('Profile URL', 'cubewp-framework'),
                ],
            ]
        );

        $this->add_control(
            'fallback_url',
            [
                'label'       => esc_html__('Fallback URL', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://example.com', 'cubewp-framework'),
            ]
        );
    }

    public function is_settings_required()
    {
        return false;
    }

    public function render()
    {

        $user_id = cubewp_get_elementor_tag_user_id();

        if (! $user_id) {
            return;
        }

        $user = get_user_by('id', $user_id);

        if (! $user) {
            return;
        }

        $url_type = $this->get_settings('url_type');
        $url      = '';

        switch ($url_type) {

            case 'website':
                $url = $user->user_url;
                break;

            case 'profile':
                $url = get_author_posts_url($user_id);
                break;
        }

        if (empty($url)) {
            $fallback = $this->get_settings('fallback_url');
            $url      = $fallback['url'] ?? '';
        }

        if (empty($url)) {
            return;
        }

        echo esc_url($url);
    }
}

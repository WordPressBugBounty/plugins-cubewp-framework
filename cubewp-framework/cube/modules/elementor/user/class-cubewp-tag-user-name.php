<?php
if (! defined('ABSPATH')) {
    exit;
}

class CubeWp_Tag_User_Name extends \Elementor\Core\DynamicTags\Tag
{

    public function get_name()
    {
        return 'cubewp-user-name';
    }

    public function get_title()
    {
        return esc_html__('User Name', 'cubewp-framework');
    }

    public function get_group()
    {
        return ['cubewp-user-data'];
    }

    public function get_categories()
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    protected function register_controls()
    {
        $this->add_control(
            'name_format',
            [
                'label'   => esc_html__('Name Format', 'cubewp-framework'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'auto',
                'description' => esc_html__('Auto (Recommended) will try to use the most appropriate name format based on available user data: Display Name → First+Last Name → Username.', 'cubewp-framework'),
                'options' => [
                    'auto'        => esc_html__('Auto (Recommended)', 'cubewp-framework'),
                    'display'     => esc_html__('Display Name', 'cubewp-framework'),
                    'first_name'  => esc_html__('First Name', 'cubewp-framework'),
                    'last_name'   => esc_html__('Last Name', 'cubewp-framework'),
                    'first_last'  => esc_html__('First Name + Last Name', 'cubewp-framework'),
                    'username'    => esc_html__('Username', 'cubewp-framework'),
                    'nickname'    => esc_html__('Nickname', 'cubewp-framework'), // Consider adding
                ],
            ]
        );

        $this->add_control(
            'name_fallback',
            [
                'label'       => esc_html__('Fallback Text', 'cubewp-framework'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('User', 'cubewp-framework'),
                'default'     => esc_html__('User', 'cubewp-framework'),
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

        $format   = $this->get_settings('name_format');
        $fallback = $this->get_settings('name_fallback');

        $name = '';

        switch ($format) {
            case 'display':
                $name = $user->display_name;
                break;

            case 'first_name':
                $name = get_user_meta($user_id, 'first_name', true);
                break;

            case 'last_name':
                $name = get_user_meta($user_id, 'last_name', true);
                break;

            case 'first_last':
                $first = get_user_meta($user_id, 'first_name', true);
                $last  = get_user_meta($user_id, 'last_name', true);
                $name  = trim($first . ' ' . $last);
                break;

            case 'username':
                $name = $user->user_login;
                break;

            case 'nickname':
                $name = get_user_meta($user_id, 'nickname', true);
                break;

            case 'auto':
            default:
                // Try display name first
                $name = $user->display_name;

                // If empty, try first + last name
                if (empty($name)) {
                    $first = get_user_meta($user_id, 'first_name', true);
                    $last  = get_user_meta($user_id, 'last_name', true);
                    $name  = trim($first . ' ' . $last);
                }

                // If still empty, try nickname
                if (empty($name)) {
                    $name = get_user_meta($user_id, 'nickname', true);
                }

                // Finally, fallback to username
                if (empty($name)) {
                    $name = $user->user_login;
                }
                break;
        }

        // Apply fallback if name is still empty
        if (empty($name) && ! empty($fallback)) {
            $name = $fallback;
        }

        // Sanitize output
        echo esc_html($name);
    }
}

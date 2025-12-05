<?php

/**
 * Module Configuration: Custom Login URL
 */

return array(
    'name'        => __('Custom Login URL', 'dailybuddy'),
    'description' => __('Hide your wp-login.php and wp-admin by changing the login URL. Protects against brute force attacks.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-right-to-bracket',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('security', 'dailybuddy'),
        __('login', 'dailybuddy'),
        __('protection', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_custom_login_url_settings',
);

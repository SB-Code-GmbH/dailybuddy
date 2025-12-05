<?php

/**
 * Module Configuration: Dashboard Access Control
 */

return array(
    'name'        => __('Dashboard Access Control', 'dailybuddy'),
    'description' => __('Control which user roles can access the WordPress dashboard. Redirect unauthorized users to the frontend.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-shield-halved',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('security', 'dailybuddy'),
        __('access', 'dailybuddy'),
        __('dashboard', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_dashboard_access_settings',
);

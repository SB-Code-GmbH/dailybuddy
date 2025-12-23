<?php

/**
 * Module Configuration: Under Construction
 */

return array(
    'name'        => __('Under Construction Mode', 'dailybuddy'),
    'description' => __('Shows an "Under Construction" page for non-logged-in users.', 'dailybuddy'),
    'important_description' => __('Maintenance mode is controlled via settings, not when activating the module.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-person-digging',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('maintenance', 'dailybuddy'),
        __('frontend', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_under_construction_settings',
);

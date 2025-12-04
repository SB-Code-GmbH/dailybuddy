<?php

/**
 * Module Configuration: Security Monitor Widget
 */

return array(
    'name'        => __('Security Monitor', 'dailybuddy'),
    'description' => __('Compact security overview showing admin logins, failed attempts, available updates, and outdated components.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-shield-halved',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('security', 'dailybuddy'),
        __('monitoring', 'dailybuddy')
    ),
    'has_settings' => false,
);

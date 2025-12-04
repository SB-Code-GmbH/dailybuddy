<?php

/**
 * Module Configuration: User Activity Widget
 */

return array(
    'name'        => __('User Activity', 'dailybuddy'),
    'description' => __('Shows who is currently online and the last activity of all users. Perfect for multi-user sites.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-users',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('users', 'dailybuddy'),
        __('activity', 'dailybuddy'),
        __('online', 'dailybuddy')
    ),
    'has_settings' => false,
);

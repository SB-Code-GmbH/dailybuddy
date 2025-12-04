<?php

/**
 * Module Configuration: Server & Performance Widget
 */

return array(
    'name'        => __('Server & Performance', 'dailybuddy'),
    'description' => __('Quick health check showing PHP version, memory limit, MySQL version, WordPress version, and disk space usage.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-server',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('server', 'dailybuddy'),
        __('performance', 'dailybuddy'),
        __('health', 'dailybuddy')
    ),
    'has_settings' => false,
);

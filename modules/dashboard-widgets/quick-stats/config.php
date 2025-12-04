<?php

/**
 * Module Configuration: Quick Stats Widget
 */

return array(
    'name'        => __('Quick Stats', 'dailybuddy'),
    'description' => __('Displays key website statistics at a glance: posts, pages, comments, users, and media.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-chart-simple',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('statistics', 'dailybuddy'),
        __('overview', 'dailybuddy')
    ),
    'has_settings' => false,
);

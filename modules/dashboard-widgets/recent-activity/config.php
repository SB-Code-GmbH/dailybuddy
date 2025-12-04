<?php

/**
 * Module Configuration: Recent Activity Widget
 */

return array(
    'name'        => __('Recent Activity', 'dailybuddy'),
    'description' => __('Shows the last 10 changes on your website: post updates, new comments, plugin updates, and more with timestamps and direct links.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-clock-rotate-left',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('activity', 'dailybuddy'),
        __('timeline', 'dailybuddy')
    ),
    'has_settings' => false,
);

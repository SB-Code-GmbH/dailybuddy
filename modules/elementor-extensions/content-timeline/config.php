<?php

/**
 * Module Configuration: Content Timeline Widget
 */

return array(
    'name'        => __('Content Timeline', 'dailybuddy'),
    'description' => __('Display your content in an elegant timeline layout. Supports dynamic posts, custom content, and ACF repeater fields with horizontal and vertical layouts.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-timeline',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('timeline', 'dailybuddy'),
        __('content', 'dailybuddy'),
        __('posts', 'dailybuddy'),
        __('history', 'dailybuddy')
    ),
    'has_settings' => false,
);

<?php

/**
 * Module Configuration: Advanced Tabs Widget
 */

return array(
    'name'        => __('Advanced Tabs', 'dailybuddy'),
    'description' => __('Create beautiful tabbed content with multiple design styles. Supports icons, images, scheduled tabs, and Elementor templates.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-folder-tree',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('tabs', 'dailybuddy'),
        __('accordion', 'dailybuddy'),
        __('content', 'dailybuddy'),
        __('navigation', 'dailybuddy')
    ),
    'has_settings' => false,
);

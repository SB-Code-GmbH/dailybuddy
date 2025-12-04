<?php

/**
 * Module Configuration: Toggle Widget
 */

return array(
    'name'        => __('Content Toggle', 'dailybuddy'),
    'description' => __('Switch between two content sections with beautiful effects. Perfect for pricing tables, light/dark mode, or any dual content display.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-toggle-on',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('toggle', 'dailybuddy'),
        __('switcher', 'dailybuddy'),
        __('content', 'dailybuddy'),
        __('tabs', 'dailybuddy')
    ),
    'has_settings' => false,
);

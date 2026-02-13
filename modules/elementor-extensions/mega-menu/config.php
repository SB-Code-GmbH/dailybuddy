<?php

/**
 * Module Configuration: Mega Menu Widget
 */

return array(
    'name'        => __('Mega Menu', 'dailybuddy'),
    'description' => __('Create beautiful navigation menus with editable dropdown content and mobile toggle.', 'dailybuddy'),
    'version'     => '1.0.2',
    'icon'        => 'fa-solid fa-bars',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('menu', 'dailybuddy'),
        __('navigation', 'dailybuddy')
    ),
    'has_settings' => false,
);

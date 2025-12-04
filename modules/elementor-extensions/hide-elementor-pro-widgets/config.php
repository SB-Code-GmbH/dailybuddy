<?php

/**
 * Module Configuration: Hide Elementor Pro Widgets
 */

return array(
    'name'        => __('Hide Elementor Pro Widgets', 'dailybuddy'),
    'description' => __(
        'Hides Elementor Pro widgets, Pro categories, upsell elements and promotional notices inside the Elementor editor.',
        'dailybuddy'
    ),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-eye-slash',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('ui', 'dailybuddy'),
        __('cleanup', 'dailybuddy'),
    ),
    'has_settings' => false, // Only ON/OFF in dailybuddy
);

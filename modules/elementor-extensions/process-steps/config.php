<?php

/**
 * Module Configuration: Process Steps Widget
 */

return array(
    'name'         => __('Process Steps', 'dailybuddy'),
    'description'  => __('Display step-by-step processes and workflows with customizable icons, titles, descriptions, and connectors.', 'dailybuddy'),
    'version'      => '1.0.0',
    'icon'         => 'fa-solid fa-arrows-split-up-and-left',
    'is_premium'   => false,
    'requires'     => array('elementor'),
    'tags'         => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('process', 'dailybuddy'),
        __('steps', 'dailybuddy'),
        __('workflow', 'dailybuddy'),
        __('walkthrough', 'dailybuddy'),
    ),
    'has_settings' => false,
);

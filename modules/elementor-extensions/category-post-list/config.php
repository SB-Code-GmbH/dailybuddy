<?php

/**
 * Module Configuration: Category Post List
 */

return array(
    'name'              => __('Category Post List', 'dailybuddy'),
    'description'       => __('Displays a linked list of all posts in a chosen category. Highlights the current post for easy sidebar navigation.', 'dailybuddy'),
    'version'           => '1.0.0',
    'icon'              => 'fa-solid fa-list',
    'is_premium'        => false,
    'requires'          => array('elementor'),
    'tags'              => array(
        __('category', 'dailybuddy'),
        __('post list', 'dailybuddy'),
        __('navigation', 'dailybuddy'),
        __('sidebar', 'dailybuddy'),
        __('links', 'dailybuddy'),
    ),
    'has_settings'      => false,
);

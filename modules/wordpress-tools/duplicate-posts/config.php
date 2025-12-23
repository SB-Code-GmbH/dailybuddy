<?php

/**
 * Module Configuration: Duplicate Posts
 */

return array(
    'name'        => __('Duplicate Posts/Pages', 'dailybuddy'),
    'description' => __('Adds a "Duplicate" link to posts and pages.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-copy',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('content', 'dailybuddy'),
        __('admin', 'dailybuddy')
    ),
);

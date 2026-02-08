<?php

/**
 * Module Configuration: Content Folders
 */

return array(
    'name'        => __('Content Folders', 'dailybuddy'),
    'description' => __('Organize your Posts, Pages, and Media files in folders with drag & drop functionality.', 'dailybuddy'),
    'version'     => '1.0.2',
    'icon'        => 'fa-solid fa-folder',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('organization', 'dailybuddy'),
        __('media', 'dailybuddy'),
        __('content', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_content_folders_settings',
);

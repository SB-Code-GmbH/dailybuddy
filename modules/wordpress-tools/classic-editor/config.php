<?php

/**
 * Module Configuration: Classic Editor
 */

return array(
    'name'        => __('Classic Editor', 'dailybuddy'),
    'description' => __('Replaces the Gutenberg block editor with the classic TinyMCE editor for all post types.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-pen-to-square',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('wordpress', 'dailybuddy'),
        __('editor', 'dailybuddy'),
        __('classic', 'dailybuddy'),
        __('gutenberg', 'dailybuddy'),
        __('tinymce', 'dailybuddy'),
    ),
    'has_settings' => false,
);

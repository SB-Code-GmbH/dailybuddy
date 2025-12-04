<?php

/**
 * Module Configuration: Quick Notes Widget
 */

return array(
    'name'        => __('Quick Notes', 'dailybuddy'),
    'description' => __('Personal notes and todo list in your dashboard. Each user has their own notes with checkboxes for tasks.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-clipboard-list',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('dashboard', 'dailybuddy'),
        __('notes', 'dailybuddy'),
        __('todo', 'dailybuddy'),
        __('productivity', 'dailybuddy')
    ),
    'has_settings' => false,
);

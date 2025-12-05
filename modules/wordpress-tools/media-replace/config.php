<?php

/**
 * Module Configuration: Media Replace
 */

return array(
    'name'        => __('Media Replace', 'dailybuddy'),
    'description' => __('Replace media files easily and effectively. Upload a new file to replace an existing one without changing the URL or breaking links.', 'dailybuddy'),
    'version'     => '1.0.1',
    'icon'        => 'fa-solid fa-image',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('media', 'dailybuddy'),
        __('upload', 'dailybuddy'),
        __('replace', 'dailybuddy')
    ),
    'has_settings' => false,
);

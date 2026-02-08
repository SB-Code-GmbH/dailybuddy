<?php

/**
 * Module Configuration: Filterable Gallery Widget
 */

return array(
    'name'        => __('Filterable Gallery', 'dailybuddy'),
    'description' => __('Create beautiful filterable image galleries with multiple layout styles, search functionality, and lightbox support.', 'dailybuddy'),
    'version'     => '1.0.1',
    'icon'        => 'fa-solid fa-images',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('gallery', 'dailybuddy'),
        __('filter', 'dailybuddy'),
        __('portfolio', 'dailybuddy'),
        __('images', 'dailybuddy'),
        __('lightbox', 'dailybuddy')
    ),
    'has_settings' => false,
);

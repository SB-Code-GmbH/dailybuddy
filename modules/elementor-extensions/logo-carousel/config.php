<?php

/**
 * Module Configuration: Logo Carousel Widget
 */

return array(
    'name'        => __('Logo Carousel', 'dailybuddy'),
    'description' => __('Display logos in an animated carousel/slider. Perfect for showcasing clients, partners, or sponsors with various animation effects.', 'dailybuddy'),
    'version'     => '1.0.1',
    'icon'        => 'fa-solid fa-images',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('carousel', 'dailybuddy'),
        __('slider', 'dailybuddy'),
        __('logo', 'dailybuddy'),
        __('brand', 'dailybuddy')
    ),
    'has_settings' => false,
);

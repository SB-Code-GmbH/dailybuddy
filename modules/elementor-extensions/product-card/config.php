<?php

/**
 * Module Configuration: Product Card Widget
 */

return array(
    'name'        => __('Product Card', 'dailybuddy'),
    'description' => __('Showcase products with style! Features badges, countdown timers, quick view, and social sharing. Perfect for e-commerce and product displays.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-bag-shopping',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('product', 'dailybuddy'),
        __('card', 'dailybuddy'),
        __('ecommerce', 'dailybuddy'),
        __('showcase', 'dailybuddy')
    ),
    'has_settings' => false,
);

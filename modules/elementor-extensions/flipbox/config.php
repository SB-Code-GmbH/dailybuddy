<?php

/**
 * Module Configuration: FlipBox Widget
 */

return array(
    'name'        => __('FlipBox', 'dailybuddy'),
    'description' => __('Animated flip box with front and back content. Perfect for showcasing features, services, or team members.', 'dailybuddy'),
    'version'     => '1.0.1',
    'icon'        => 'fa-solid fa-retweet',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('flipbox', 'dailybuddy'),
        __('animation', 'dailybuddy')
    ),
    'has_settings' => false,
);

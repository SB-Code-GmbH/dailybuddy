<?php

/**
 * Module Configuration: Advanced Accordion Widget
 */

return array(
    'name'        => __('Advanced Accordion', 'dailybuddy'),
    'description' => __('Create beautiful, feature-rich accordions with multiple design styles, auto-numbering, icon animations, and more. Perfect for FAQs, content organization, and interactive layouts.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-bars-staggered',
    'is_premium'  => false,
    'requires'    => array('elementor'),
    'tags'        => array(
        __('elementor', 'dailybuddy'),
        __('widget', 'dailybuddy'),
        __('accordion', 'dailybuddy'),
        __('toggle', 'dailybuddy'),
        __('faq', 'dailybuddy'),
        __('collapsible', 'dailybuddy')
    ),
    'has_settings' => false,
);

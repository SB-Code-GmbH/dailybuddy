<?php

/**
 * Module Configuration: Pageviews Widget
 */

return array(
    'name'         => __('Pageviews', 'dailybuddy'),
    'description'  => __('30-day traffic chart with today, 7-day and 30-day totals. Uses the same lightweight counter as the WPBuddy connector — no external service, no cookies.', 'dailybuddy'),
    'version'      => '1.0.0',
    'icon'         => 'fa-solid fa-chart-line',
    'is_premium'   => false,
    'requires'     => array(),
    'tags'         => array(
        __('dashboard', 'dailybuddy'),
        __('statistics', 'dailybuddy'),
        __('traffic', 'dailybuddy'),
        __('pageviews', 'dailybuddy'),
    ),
    'has_settings' => false,
);

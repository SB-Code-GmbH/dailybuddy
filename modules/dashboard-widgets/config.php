<?php
// modules/dashboard-widgets/config.php

if (! defined('ABSPATH')) {
    exit;
}

return array(
    'id'          => 'dashboard-widgets',
    'name'        => __('Dashboard Widgets', 'dailybuddy'),
    'description' => __('Custom dashboard widgets and displays', 'dailybuddy'),
    'icon'        => 'fa-solid fa-gauge',
    'order'       => 20,
);

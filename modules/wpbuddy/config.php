<?php
// modules/wpbuddy/config.php

if (! defined('ABSPATH')) {
    exit;
}

return array(
    'id'          => 'wpbuddy',
    'name'        => __('WPBuddy', 'dailybuddy'),
    'description' => __('Connect this site to a WPBuddy platform for central monitoring, updates and remote management.', 'dailybuddy'),
    'icon'        => 'fa-solid fa-satellite-dish',
    'order'       => 40,

    // When set, main-page.php includes this file for the category tab
    // INSTEAD of the default module-card grid. The file has access to
    // $dailybuddy_category (string) and $dailybuddy_category_modules
    // (array of module data) — check activation state via
    // $dailybuddy_category_modules['connector']['active'].
    'custom_view' => __DIR__ . '/views/category-view.php',
);

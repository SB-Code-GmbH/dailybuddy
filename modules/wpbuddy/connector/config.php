<?php

/**
 * Module Configuration: Platform Connector (WPBuddy)
 *
 * Connects this WordPress installation to a WPBuddy-compatible platform
 * (e.g. dailybuddy.net or any self-hosted white-label instance). Once
 * connected, the platform receives periodic signed heartbeats (site
 * health, WP/PHP versions, plugin & theme update status).
 */

if (! defined('ABSPATH')) {
    exit;
}

return array(
    'name'              => __('Connector', 'dailybuddy'),
    'description'       => __('Connect this site to a WPBuddy platform for central monitoring and update visibility. Sends signed status snapshots on a schedule — you decide what the platform sees.', 'dailybuddy'),
    'version'           => '1.0.0',
    'icon'              => 'fa-solid fa-satellite-dish',
    'is_premium'        => false,
    'requires'          => array(),
    'tags'              => array(
        __('monitoring', 'dailybuddy'),
        __('platform', 'dailybuddy'),
        __('remote', 'dailybuddy'),
    ),
    // No inline settings — this module has its own top-level submenu
    // ("Connector") under DailyBuddy. Registered in module.php via
    // Dailybuddy_Platform_Connector_Admin_Page::register_menu().
    'has_settings'      => false,
);

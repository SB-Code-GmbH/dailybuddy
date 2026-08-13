<?php

/**
 * Module: Platform Connector (WPBuddy)
 *
 * Entry point. Loaded by the module loader when the module is enabled.
 * Wires the individual classes (keypair storage, admin page, REST
 * endpoints, transport, cron) into WordPress.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

// Module base path (used by the class files below).
define('DAILYBUDDY_PC_PATH', plugin_dir_path(__FILE__));
define('DAILYBUDDY_PC_URL',  plugin_dir_url(__FILE__));

// Class files — each one is a focused concern (keypair, rest, …).
// The connector UI lives in the "WPBuddy" category tab (rendered by
// modules/wpbuddy/views/category-view.php), so no separate WP submenu.
require_once DAILYBUDDY_PC_PATH . 'includes/class-keypair.php';
require_once DAILYBUDDY_PC_PATH . 'includes/class-connection.php';
require_once DAILYBUDDY_PC_PATH . 'includes/class-rest.php';
require_once DAILYBUDDY_PC_PATH . 'includes/class-platform-connector.php';

// Boot the module once WordPress is ready.
add_action('init', function () {
    // Instantiating registers all hooks in the class constructors.
    new Dailybuddy_Platform_Connector();
});

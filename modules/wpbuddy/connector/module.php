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
require_once DAILYBUDDY_PC_PATH . 'includes/class-traffic.php';
require_once DAILYBUDDY_PC_PATH . 'includes/class-rest.php';
require_once DAILYBUDDY_PC_PATH . 'includes/class-platform-connector.php';

// Boot the module.
new Dailybuddy_Platform_Connector();

// SSO consume — run it DIRECTLY at module load, not via a WP hook.
// This module is loaded inside DailyBuddy's own init(5) callback, so a
// later `add_action('init', …, 1)` would never fire (priority 1 is
// already past). Handling the token inline here is the same trick the
// ManageWP worker uses (it runs its request handler at plugin bootstrap
// rather than waiting for a WP action). Output hasn't started yet at
// init(5), so setcookie() + wp_safe_redirect() both work.
if (!empty($_GET['wpbuddy_sso'])) {
    (new Dailybuddy_Platform_Connector_Connection())->maybe_consume_sso_token();
}

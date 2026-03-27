<?php

/**
 * Plugin Name: DailyBuddy — Free All-in-One Toolkit
 * Plugin URI: https://dailybuddy.net/
 * Description: Free all-in-one toolkit: content folders, duplicate posts, custom login URL, maintenance mode, media replace, classic editor, language detection, dashboard widgets & 10 Elementor extensions.
 * Version: 1.2.3
 * Author: Ilja Becker
 * Author URI: https://profiles.wordpress.org/beckerilja/
 * Text Domain: dailybuddy
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('DAILYBUDDY_VERSION', '1.2.3');
define('DAILYBUDDY_PATH', plugin_dir_path(__FILE__));
define('DAILYBUDDY_URL', plugin_dir_url(__FILE__));
define('DAILYBUDDY_BASENAME', plugin_basename(__FILE__));
define('DAILYBUDDY_DEV_MODE', false);

// Include core files
require_once DAILYBUDDY_PATH . 'includes/helpers.php';
require_once DAILYBUDDY_PATH . 'includes/class-dailybuddy.php';
require_once DAILYBUDDY_PATH . 'includes/class-module-loader.php';
require_once DAILYBUDDY_PATH . 'includes/class-settings.php';
require_once DAILYBUDDY_PATH . 'admin/class-admin-page.php';

// Include Translation Scanner (only in DEV_MODE)
if (defined('DAILYBUDDY_DEV_MODE') && DAILYBUDDY_DEV_MODE) {
    require_once DAILYBUDDY_PATH . 'includes/class-translation-scanner.php';

    // Initialize scanner after WordPress is loaded
    add_action('init', function () {
        new Dailybuddy_Translation_Scanner();
    });
}

// Initialize the plugin
function dailybuddy_init()
{
    $dailybuddy = new Dailybuddy();
    $dailybuddy->init();
}
add_action('init', 'dailybuddy_init', 5);

// Activation hook
function dailybuddy_activate()
{
    // Set default options
    if (! get_option('dailybuddy_modules')) {
        add_option('dailybuddy_modules', array());
    }

    // Track installation time for review notice.
    if (! get_option('dailybuddy_installed_at')) {
        add_option('dailybuddy_installed_at', time());
    }
}
register_activation_hook(__FILE__, 'dailybuddy_activate');

// Deactivation hook
function dailybuddy_deactivate()
{
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'dailybuddy_deactivate');

/**
 * Elementor Editor Styles
 */
add_action('elementor/editor/after_enqueue_styles', function () {
    wp_enqueue_style(
        'dailybuddy-elementor-editor',
        DAILYBUDDY_URL . 'assets/css/elementor-editor.css',
        array(),
        DAILYBUDDY_VERSION
    );
});

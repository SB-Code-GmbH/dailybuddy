<?php

/**
 * Plugin Name: DailyBuddy
 * Plugin URI: https://dailybuddy.net/
 * Description: A modular collection of essential WordPress features that can be individually enabled or disabled. Includes tools for duplicating posts, maintenance mode, media organization, custom widgets, and Elementor extensions - all in one place.
 * Version: 1.0.4
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
define('DAILYBUDDY_VERSION', '1.0.0');
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
        new WP_Dailybuddy_Translation_Scanner();
    });
}

// Initialize the plugin
function dailybuddy_init()
{
    $dailybuddy = new WP_Dailybuddy();
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

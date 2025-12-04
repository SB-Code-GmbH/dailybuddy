<?php

/**
 * Main dailybuddy Class
 */

if (! defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy
{

    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        // Initialize module loader
        $module_loader = new WP_Dailybuddy_Module_Loader();
        $module_loader->load_modules();

        // Initialize admin page
        if (is_admin()) {
            $admin_page = new WP_Dailybuddy_Admin_Page();
            $admin_page->init();
        }

        // Load assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function enqueue_admin_assets($hook)
    {
        // ALLOWED admin pages for dailybuddy
        $valid_pages = [
            'toplevel_page_dailybuddy',              // Main module page
            'dailybuddy_page_dailybuddy-settings',   // General settings
            'dailybuddy_page_dailybuddy'             // Module settings (subpages)
        ];

        if (!in_array($hook, $valid_pages)) {
            return;
        }

        wp_enqueue_style(
            'font-awesome',
            DAILYBUDDY_URL . 'assets/css/font-awesome/css/all.min.css',
            [],
            '6.5.1'
        );

        wp_enqueue_script(
            'tweenmax-min',
            DAILYBUDDY_URL . 'assets/js/TweenMax.min',
            ['jquery', 'wp-i18n'],
            DAILYBUDDY_VERSION,
            true
        );

        wp_enqueue_style(
            'dailybuddy-snackbar',
            DAILYBUDDY_URL . 'assets/css/snackbar.css',
            [],
            DAILYBUDDY_VERSION
        );

        wp_enqueue_style(
            'dailybuddy-admin',
            DAILYBUDDY_URL . 'assets/css/admin.css',
            [],
            DAILYBUDDY_VERSION
        );

        wp_enqueue_script(
            'dailybuddy-admin',
            DAILYBUDDY_URL . 'assets/js/admin.js',
            ['jquery', 'wp-i18n'],
            DAILYBUDDY_VERSION,
            true
        );

        wp_set_script_translations(
            'dailybuddy-admin',
            'dailybuddy',
            DAILYBUDDY_PATH . 'languages'
        );

        wp_localize_script(
            'dailybuddy-admin',
            'wpToolboxData',
            [
                'nonce'   => wp_create_nonce('dailybuddy_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php')
            ]
        );
    }
}

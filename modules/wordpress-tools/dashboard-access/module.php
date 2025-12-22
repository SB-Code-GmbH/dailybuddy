<?php

/**
 * Module: Dashboard Access Control
 * 
 * Controls which user roles can access the WordPress dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Dashboard_Access
{
    private $settings;

    public function __construct()
    {
        // Load settings
        $this->settings = get_option('dailybuddy_dashboard_access_settings', array(
            'allowed_roles' => array('administrator', 'editor'),
        ));

        // Check dashboard access
        add_action('admin_init', array($this, 'check_dashboard_access'));

        // Hide admin bar for non-allowed users (always)
        add_action('after_setup_theme', array($this, 'hide_admin_bar'));

        // Remove admin menu items for non-allowed users
        add_action('admin_menu', array($this, 'remove_admin_menu_items'), 999);

        // Remove admin bar nodes for non-allowed users
        add_action('admin_bar_menu', array($this, 'remove_admin_bar_nodes'), 999);

        // Add tools menu entry
        add_action('admin_menu', array($this, 'add_tools_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook)
    {
        // Only load on DailyBuddy settings page
        if (strpos($hook, 'dailybuddy') === false) {
            return;
        }

        if (defined('DAILYBUDDY_URL') && defined('DAILYBUDDY_VERSION')) {
            wp_enqueue_style(
                'dailybuddy-uc',
                DAILYBUDDY_URL . 'assets/css/modul-settings.css',
                array(),
                DAILYBUDDY_VERSION
            );
        }
    }

    /**
     * Check if user can access dashboard
     */
    public function check_dashboard_access()
    {
        // Skip AJAX requests
        if (wp_doing_ajax()) {
            return;
        }

        // Allow access to profile page for all users
        global $pagenow;
        if ($pagenow === 'profile.php') {
            return;
        }

        // Get current user
        $user = wp_get_current_user();

        // Allow if no user (login page)
        if (!$user || !$user->ID) {
            return;
        }

        // Allow administrators always
        if (in_array('administrator', $user->roles)) {
            return;
        }

        // Check if user role is allowed
        $user_roles = $user->roles;
        $allowed_roles = isset($this->settings['allowed_roles']) ? $this->settings['allowed_roles'] : array();

        $has_access = false;
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Redirect if no access
        if (!$has_access) {
            // Redirect to user profile page
            wp_safe_redirect(admin_url('profile.php'));
            exit;
        }
    }

    /**
     * Hide admin bar for non-allowed users
     */
    public function hide_admin_bar()
    {
        $user = wp_get_current_user();

        // Allow if no user
        if (!$user || !$user->ID) {
            return;
        }

        // Allow administrators always
        if (in_array('administrator', $user->roles)) {
            return;
        }

        // Check if user role is allowed
        $user_roles = $user->roles;
        $allowed_roles = isset($this->settings['allowed_roles']) ? $this->settings['allowed_roles'] : array();

        $has_access = false;
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Hide admin bar if no access
        if (!$has_access) {
            show_admin_bar(false);
        }
    }

    /**
     * Remove admin menu items for non-allowed users
     */
    public function remove_admin_menu_items()
    {
        $user = wp_get_current_user();

        // Allow if no user
        if (!$user || !$user->ID) {
            return;
        }

        // Allow administrators always
        if (in_array('administrator', $user->roles)) {
            return;
        }

        // Check if user has access
        $user_roles = $user->roles;
        $allowed_roles = isset($this->settings['allowed_roles']) ? $this->settings['allowed_roles'] : array();

        $has_access = false;
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Remove only Dashboard menu item if no access
        if (!$has_access) {
            remove_menu_page('index.php'); // Dashboard
        }
    }

    /**
     * Remove admin bar nodes for non-allowed users
     */
    public function remove_admin_bar_nodes($wp_admin_bar)
    {
        $user = wp_get_current_user();

        // Allow if no user
        if (!$user || !$user->ID) {
            return;
        }

        // Allow administrators always
        if (in_array('administrator', $user->roles)) {
            return;
        }

        // Check if user has access
        $user_roles = $user->roles;
        $allowed_roles = isset($this->settings['allowed_roles']) ? $this->settings['allowed_roles'] : array();

        $has_access = false;
        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Remove only Dashboard node if no access
        if (!$has_access) {
            $wp_admin_bar->remove_node('dashboard'); // Dashboard link in admin bar
        }
    }

    /**
     * Add menu entry under Tools
     */
    public function add_tools_menu()
    {
        $hook = add_submenu_page(
            'tools.php',
            __('Dashboard Access Control', 'dailybuddy'),
            __('Dashboard Access', 'dailybuddy'),
            'manage_options',
            'dailybuddy-dashboard-access-tools',
            array($this, 'redirect_to_settings')
        );

        // Redirect on page load
        add_action('load-' . $hook, array($this, 'redirect_to_settings'));
    }

    /**
     * Redirect to DailyBuddy settings page
     */
    public function redirect_to_settings()
    {
        wp_safe_redirect(admin_url('admin.php?page=dailybuddy&view=settings&module=wordpress-tools/dashboard-access'));
        exit;
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting(
            'dailybuddy_dashboard_access_settings',
            'dailybuddy_dashboard_access_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
            )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        // Sanitize allowed roles
        if (isset($input['allowed_roles']) && is_array($input['allowed_roles'])) {
            $sanitized['allowed_roles'] = array_map('sanitize_text_field', $input['allowed_roles']);
        } else {
            $sanitized['allowed_roles'] = array();
        }

        // Always include administrator
        if (!in_array('administrator', $sanitized['allowed_roles'])) {
            $sanitized['allowed_roles'][] = 'administrator';
        }

        return $sanitized;
    }
}

/**
 * Render settings page
 */
function dailybuddy_render_dashboard_access_settings()
{
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'dailybuddy'));
    }

    // Get current settings
    $settings = get_option('dailybuddy_dashboard_access_settings', array(
        'allowed_roles' => array('administrator', 'editor'),
    ));

    // Get all WordPress roles
    global $wp_roles;
    $all_roles = $wp_roles->roles;

    // Handle form submission
    if (isset($_POST['dailybuddy_dashboard_access_submit'])) {
        check_admin_referer('dailybuddy_dashboard_access_settings');

        $new_settings = array(
            'allowed_roles' => isset($_POST['allowed_roles'])
                ? array_map('sanitize_text_field', wp_unslash($_POST['allowed_roles']))
                : array(),
        );

        // Always include administrator
        if (!in_array('administrator', $new_settings['allowed_roles'])) {
            $new_settings['allowed_roles'][] = 'administrator';
        }

        update_option('dailybuddy_dashboard_access_settings', $new_settings);
        $settings = $new_settings;

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'dailybuddy') . '</p></div>';
    }

    // Include template
    include DAILYBUDDY_PATH . 'modules/wordpress-tools/dashboard-access/templates/settings-page.php';
}

// Initialize module
new Dailybuddy_Dashboard_Access();

<?php

/**
 * Admin Page Class with Settings Support
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Admin_Page
{

    private $modules = array();

    public function init()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_dailybuddy_toggle_module', array($this, 'ajax_toggle_module'));

        // Show dev mode notice on all admin pages
        if (defined('DAILYBUDDY_DEV_MODE') && DAILYBUDDY_DEV_MODE) {
            add_action('admin_notices', array($this, 'show_dev_mode_notice'));
        }

    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu()
    {

        add_menu_page(
            __('DailyBuddy', 'dailybuddy'),
            __('DailyBuddy', 'dailybuddy'),
            'manage_options',
            'dailybuddy',
            array($this, 'render_admin_page'),
            plugin_dir_url(dirname(__FILE__)) . 'assets/images/dailybuddy_logo_navbar.svg',
            75
        );

        add_action('admin_head', function () {
?>
        <?php
        });

        // Add submenu for main modules page
        add_submenu_page(
            'dailybuddy',
            __('Modules', 'dailybuddy'),
            __('Modules', 'dailybuddy'),
            'manage_options',
            'dailybuddy',
            array($this, 'render_admin_page')
        );

        // General Settings submenu
        add_submenu_page(
            'dailybuddy',
            __('Settings', 'dailybuddy'),
            __('Settings', 'dailybuddy'),
            'manage_options',
            'dailybuddy-settings',
            array($this, 'render_general_settings')
        );

        // Development submenu (only visible in DEV_MODE)
        if (defined('DAILYBUDDY_DEV_MODE') && DAILYBUDDY_DEV_MODE) {
            add_submenu_page(
                'dailybuddy',
                __('Development', 'dailybuddy'),
                __('Development', 'dailybuddy'),
                'manage_options',
                'dailybuddy-development',
                array($this, 'render_development_page')
            );
        }
    }

    /**
     * Render general settings page
     */
    public function render_general_settings()
    {
        include DAILYBUDDY_PATH . 'admin/views/general-settings-page.php';
    }

    /**
     * Render development page (only in DEV_MODE)
     */
    public function render_development_page()
    {
        include DAILYBUDDY_PATH . 'admin/views/development-page.php';
    }

    /**
     * Render admin page - decides which view to show
     */
    public function render_admin_page()
    {
        $module_loader = new Dailybuddy_Module_Loader();
        $module_loader->load_modules();
        $this->modules = $module_loader->get_modules();

        // Read-only GET parameter; controls which view is shown.
        // Safe without nonce because no data is being changed.
        // Only used to switch admin view; no data is modified.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $view = isset($_GET['view'])
            ? sanitize_key(wp_unslash($_GET['view']))
            : 'modules';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $allowed_views = array('modules', 'settings');

        if (! in_array($view, $allowed_views, true)) {
            $view = 'modules';
        }

        // Read-only GET parameter: selects module for display.
        // Safe without nonce because no data is being changed.
        // Only used for display switching.
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $module_id = isset($_GET['module'])
            ? sanitize_text_field(wp_unslash($_GET['module']))
            : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ('settings' === $view && ! empty($module_id)) {
            $this->render_module_settings($module_id);
        } else {
            $this->render_modules_page();
        }
    }

    /**
     * Render main modules page
     */
    private function render_modules_page()
    {
        $modules = $this->modules;
        include DAILYBUDDY_PATH . 'admin/views/main-page.php';
    }

    /**
     * Render module settings page
     */
    private function render_module_settings($module_id)
    {
        // Find the module
        $module_data = null;
        foreach ($this->modules as $category => $category_modules) {
            foreach ($category_modules as $module_name => $data) {
                if ($data['id'] === $module_id) {
                    $module_data = $data;
                    break 2;
                }
            }
        }

        if (! $module_data) {
            wp_die(esc_html__('Module not found', 'dailybuddy'));
        }

        // Check if module has settings
        $config = $module_data['config'];
        $has_settings = isset($config['has_settings']) && $config['has_settings'];

        if (! $has_settings) {
            wp_die(esc_html__('This module has no settings', 'dailybuddy'));
        }

        // Get settings callback
        $settings_callback = isset($config['settings_callback']) ? $config['settings_callback'] : null;

        include DAILYBUDDY_PATH . 'admin/views/module-settings-page.php';
    }

    /**
     * Show development mode notice on all admin pages
     */
    public function show_dev_mode_notice()
    {
        // Only show on dailybuddy pages
        $current_screen = get_current_screen();
        if (! $current_screen || strpos($current_screen->id, 'dailybuddy') === false) {
            return;
        }

        ?>
        <div class="notice notice-warning is-dismissible" style="border-left-color: #d63638;">
            <p>
                <strong><?php esc_html_e('Development Mode Active', 'dailybuddy'); ?></strong> –
                <?php esc_html_e('DailyBuddy is currently running in development mode. Set DAILYBUDDY_DEV_MODE to false in production.', 'dailybuddy'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy-development')); ?>" style="margin-left: 10px;">
                    <?php esc_html_e('View Development Page', 'dailybuddy'); ?> →
                </a>
            </p>
        </div>
<?php
    }

    /**
     * AJAX handler for toggling modules
     */
    public function ajax_toggle_module()
    {
        // Security check
        check_ajax_referer('dailybuddy_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('No permission', 'dailybuddy')));
        }

        $module_id = isset($_POST['module_id'])
            ? sanitize_text_field(wp_unslash($_POST['module_id']))
            : '';

        $is_active = isset($_POST['is_active']) && $_POST['is_active'] === 'true';

        if (empty($module_id)) {
            wp_send_json_error(array('message' => __('Invalid module ID', 'dailybuddy')));
        }

        // Get current modules
        $modules = Dailybuddy_Settings::get_modules();

        // Update module status
        $modules[$module_id] = $is_active;

        // Save
        $saved = Dailybuddy_Settings::save_modules($modules);

        if ($saved) {
            wp_send_json_success(array(
                'message' => $is_active
                    ? __('Module activated!', 'dailybuddy')
                    : __('Module deactivated!', 'dailybuddy')
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save settings', 'dailybuddy')));
        }
    }

}

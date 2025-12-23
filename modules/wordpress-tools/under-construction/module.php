<?php

/**
 * Module: Under Construction
 * 
 * Shows a maintenance page for non-logged-in users
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Under_Construction
{

    public function __construct()
    {
        add_action('template_redirect', array($this, 'show_under_construction'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_notice'), 999);
        add_action('init', array($this, 'check_auto_end'));

        add_action('wp_ajax_dailybuddy_toggle_maintenance', array($this, 'ajax_toggle_maintenance'));

        // Dynamically add tools menu
        add_action('admin_menu', array($this, 'maybe_add_tools_menu'));

        // Execute redirect before the page is rendered
        add_action(
            'load-tools_page_dailybuddy-under-construction',
            array($this, 'redirect_to_uc_settings')
        );

        add_action('admin_enqueue_scripts', array($this, 'enqueue_uc_code_editor'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_uc_code_editor($hook)
    {
        // Nur im Admin
        if (!is_admin()) {
            return;
        }

        // Sind wir auf der DailyBuddy-Einstellungen-Seite für "Under Construction"?
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only admin routing, no data is changed
        $dailybuddy_page   = isset($_GET['page'])
            ? sanitize_key(wp_unslash($_GET['page']))
            : '';
        $dailybuddy_view   = isset($_GET['view'])
            ? sanitize_key(wp_unslash($_GET['view']))
            : '';
        $dailybuddy_module = isset($_GET['module'])
            ? sanitize_text_field(wp_unslash($_GET['module']))
            : '';

        if (
            'dailybuddy' !== $dailybuddy_page
            || 'settings' !== $dailybuddy_view
            || 'wordpress-tools/under-construction' !== $dailybuddy_module
        ) {
            return;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        // Code Editor für CSS aktivieren (liefert Settings-Array zurück)
        $settings = wp_enqueue_code_editor(array(
            'type' => 'text/css',
        ));

        // Falls der Editor nicht verfügbar ist (ältere WP-Version etc.)
        if (!$settings) {
            return;
        }

        // Standard-WP-Code-Editor-Skripte/-Styles
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        wp_add_inline_script(
            'wp-theme-plugin-editor',
            'jQuery(function($){ 
                if (window.wp && wp.codeEditor){ 
                    window.dailybuddyUcCssEditor = wp.codeEditor.initialize("uc_custom_css", ' . wp_json_encode($settings) . ');
                } 
            });'
        );
    }

    /**
     * Enqueue admin scripts
     * Moved from inline <script> tags for WordPress.org compliance
     */
    public function enqueue_admin_scripts($hook)
    {
        // Enqueue settings page script
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only admin routing
        $dailybuddy_page   = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        $dailybuddy_view   = isset($_GET['view']) ? sanitize_key(wp_unslash($_GET['view'])) : '';
        $dailybuddy_module = isset($_GET['module']) ? sanitize_text_field(wp_unslash($_GET['module'])) : '';
        // phpcs:enable

        if (
            'dailybuddy' === $dailybuddy_page
            && 'settings' === $dailybuddy_view
            && 'wordpress-tools/under-construction' === $dailybuddy_module
        ) {
            wp_enqueue_script(
                'dailybuddy-uc-settings',
                DAILYBUDDY_URL . 'modules/wordpress-tools/under-construction/assets/settings.js',
                array('jquery'),
                DAILYBUDDY_VERSION,
                true
            );

            // Get current settings
            $settings = get_option('dailybuddy_under_construction_settings', array());
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Only checking if form was submitted, not processing data
            $settings_saved = isset($_POST['dailybuddy_save_uc_settings']);
            $maintenance_active = isset($settings['maintenance_active']) && $settings['maintenance_active'];

            wp_localize_script(
                'dailybuddy-uc-settings',
                'dailybuddyUnderConstruction',
                array(
                    'settingsSaved' => $settings_saved,
                    'maintenanceActive' => $maintenance_active,
                    'activeText' => esc_html__('Maintenance Mode Active', 'dailybuddy'),
                    'inactiveText' => esc_html__('Maintenance Mode Inactive', 'dailybuddy'),
                )
            );
        }

        // Enqueue admin bar toggle script (for all admin pages)
        wp_enqueue_script(
            'dailybuddy-uc-admin-bar-toggle',
            DAILYBUDDY_URL . 'modules/wordpress-tools/under-construction/assets/admin-bar-toggle.js',
            array('jquery'),
            DAILYBUDDY_VERSION,
            true
        );

        wp_localize_script(
            'dailybuddy-uc-admin-bar-toggle',
            'dailybuddyUcToggle',
            array(
                'nonce' => wp_create_nonce('dailybuddy_toggle_maintenance'),
                'activeText' => esc_html__('Maintenance Mode Active', 'dailybuddy'),
                'inactiveText' => esc_html__('Maintenance Mode Inactive', 'dailybuddy'),
            )
        );
    }

    /**
     * AJAX handler for maintenance mode toggle
     */
    public function ajax_toggle_maintenance()
    {
        check_ajax_referer('dailybuddy_toggle_maintenance', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'dailybuddy')));
        }

        $enabled = isset($_POST['enabled']) && $_POST['enabled'] == '1';

        $settings = $this->get_settings();
        $settings['maintenance_active'] = $enabled;

        update_option('dailybuddy_under_construction_settings', $settings);

        wp_send_json_success(array(
            'enabled' => $enabled,
            'message' => $enabled
                ? __('Maintenance mode activated', 'dailybuddy')
                : __('Maintenance mode deactivated', 'dailybuddy')
        ));
    }

    /**
     * Add "Maintenance Mode" link under Tools when module is active
     */
    public function maybe_add_tools_menu()
    {
        // Nur im Admin und nur für Admins
        if (! is_admin() || ! current_user_can('manage_options')) {
            return;
        }

        // Prüfen, ob die Dailybuddy_Settings-Klasse verfügbar ist
        if (! class_exists('Dailybuddy_Settings')) {
            return;
        }

        $modules   = Dailybuddy_Settings::get_modules();
        $module_id = 'wordpress-tools/under-construction';

        /**
         * Default-Logik:
         * - Wenn es KEINEN Eintrag für das Modul gibt -> wir nehmen an, es ist aktiv
         * - Wenn es einen Eintrag gibt -> true/false entsprechend dem gespeicherten Wert
         */
        $is_module_active = true;
        if (isset($modules[$module_id])) {
            $is_module_active = (bool) $modules[$module_id];
        }

        if (! $is_module_active) {
            return;
        }

        add_submenu_page(
            'tools.php',
            __('Maintenance Mode', 'dailybuddy'),
            __('Maintenance Mode', 'dailybuddy'),
            'manage_options',
            'dailybuddy-under-construction',
            array($this, 'redirect_to_uc_settings')
        );
    }

    /**
     * Check if auto-end time has passed
     */
    public function check_auto_end()
    {
        $settings = $this->get_settings();

        if ($settings['auto_end_enabled'] && ! empty($settings['auto_end_datetime'])) {
            $end_time = strtotime($settings['auto_end_datetime']);
            $now = current_time('timestamp');

            if ($now >= $end_time) {
                // Disable maintenance mode
                $settings['maintenance_active'] = false;
                update_option('dailybuddy_under_construction_settings', $settings);
            }
        }
    }

    /**
     * Add notice to admin bar
     */
    public function add_admin_bar_notice($wp_admin_bar)
    {
        $settings = $this->get_settings();

        // Admin-Bar-Hinweis deaktiviert?
        if (empty($settings['admin_bar_notice'])) {
            return;
        }

        // ---------------------------
        // Parent-Node (Status-Anzeige)
        // ---------------------------
        $is_active   = ! empty($settings['maintenance_active']);
        $status_color = $is_active ? '#00a32a' : '#646970';
        $status_icon  = $is_active ? '✓' : '○';
        $status_text  = $is_active
            ? __('Maintenance Mode Active', 'dailybuddy')
            : __('Maintenance Mode Inactive', 'dailybuddy');

        $wp_admin_bar->add_node(array(
            'id'    => 'dailybuddy-uc-notice',
            'title' => '<span style="color: ' . esc_attr($status_color) . ';" id="dailybuddy-uc-status">'
                . esc_html($status_icon . ' ' . $status_text) .
                '</span>',
            'href'  => false,
        ));

        // ---------------------------
        // Toggle-Submenu (Switch)
        // ---------------------------
        $label_text = __('Enable Maintenance Mode', 'dailybuddy');
        $is_active  = ! empty($settings['maintenance_active']);

        $title_html  = '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;min-width:230px;">';
        $title_html .= '  <span style="font-size:13px;line-height:1.4;color:#ccc;">' . esc_html($label_text) . '</span>';
        $title_html .= '  <label style="position:relative;display:inline-block;width:42px;height:22px;margin:0;">';
        $title_html .= '    <input type="checkbox" id="dailybuddy-uc-toggle-switch" value="1" ' . checked($is_active, true, false) . '';
        $title_html .= '           style="opacity:0;width:0;height:0;margin:0;" />';
        $title_html .= '    <span style="';
        $title_html .= '      position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;';
        $title_html .= '      background-color:' . ($is_active ? '#00a32a' : '#8c8f94') . ';';
        $title_html .= '      border-radius:22px;transition:.2s;';
        $title_html .= '    "></span>';
        $title_html .= '    <span style="';
        $title_html .= '      position:absolute;height:18px;width:18px;';
        $title_html .= '      left:' . ($is_active ? '22px' : '2px') . ';';
        $title_html .= '      bottom:2px;background-color:#fff;border-radius:50%;';
        $title_html .= '      box-shadow:0 0 2px rgba(0,0,0,.3);transition:.2s;';
        $title_html .= '    "></span>';
        $title_html .= '  </label>';
        $title_html .= '</div>';

        $wp_admin_bar->add_node(array(
            'id'     => 'dailybuddy-uc-toggle',
            'parent' => 'dailybuddy-uc-notice',
            'title'  => $title_html,
            'href'   => false,
        ));

        // ---------------------------
        // Settings-Link
        // ---------------------------
        $wp_admin_bar->add_node(array(
            'id'     => 'dailybuddy-uc-settings',
            'parent' => 'dailybuddy-uc-notice',
            'title'  => esc_html__('Show Settings', 'dailybuddy'),
            'href'   => admin_url('admin.php?page=dailybuddy&view=settings&module=wordpress-tools/under-construction'),
        ));

        // Toggle script moved to assets/admin-bar-toggle.js and enqueued in enqueue_admin_scripts()
    }

    /**
     * Redirect Tools submenu to the module settings page
     */
    public function redirect_to_uc_settings()
    {
        $url = add_query_arg(
            array(
                'page'   => 'dailybuddy',
                'view'   => 'settings',
                'module' => 'wordpress-tools/under-construction',
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }


    /**
     * Show under construction page
     */
    public function show_under_construction()
    {
        $settings = $this->get_settings();

        // Check if maintenance mode is ACTIVE in settings
        if (! $settings['maintenance_active']) {
            return;
        }

        // Allow logged-in admins to view site
        if (current_user_can('manage_options')) {
            return;
        }

        $this->render_page();
        exit;
    }

    /**
     * Get settings with defaults
     */
    private function get_settings()
    {
        return get_option('dailybuddy_under_construction_settings', array(
            // General
            'maintenance_active' => false,
            'title' => __('Website Under Construction', 'dailybuddy'),
            'message' => __('We are currently working on improvements. Please check back soon!', 'dailybuddy'),
            'show_login_button' => true,
            'auto_end_enabled' => false,
            'auto_end_datetime' => '',
            'admin_bar_notice' => true,

            // Design
            'layout' => 'centered',
            'custom_css' => '',

            // Social
            'social_enabled' => false,
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',
            'social_linkedin' => '',
            'social_youtube' => '',
        ));
    }

    /**
     * Render under construction page
     */
    private function render_page()
    {
        status_header(503);
        nocache_headers();

        $settings = $this->get_settings();
        $layout = esc_attr($settings['layout']);

        // Generate social HTML
        $dailybuddy_social_html = $this->get_social_html($settings);

        // Enqueue layout-specific CSS
        $layout_css_file = DAILYBUDDY_URL . 'modules/wordpress-tools/under-construction/assets/layouts/' . $layout . '.css';
        wp_enqueue_style(
            'dailybuddy-uc-layout-' . $layout,
            $layout_css_file,
            array(),
            DAILYBUDDY_VERSION
        );

        // Enqueue layout-specific JS (if countdown is enabled)
        $has_countdown = !empty($settings['auto_end_enabled']) && !empty($settings['auto_end_datetime']);
        if ($has_countdown && in_array($layout, array('clean-minimal', 'parallax-rocket'))) {
            $layout_js_file = DAILYBUDDY_URL . 'modules/wordpress-tools/under-construction/assets/layouts/' . $layout . '.js';
            wp_enqueue_script(
                'dailybuddy-uc-layout-' . $layout,
                $layout_js_file,
                array(),
                DAILYBUDDY_VERSION,
                true
            );
        }

?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="noindex, nofollow">
            <title><?php esc_html_e('Maintenance Mode', 'dailybuddy'); ?> - <?php bloginfo('name'); ?></title>
            <?php
            // Font Awesome
            if (!is_admin()) {
                wp_enqueue_style(
                    'dailybuddy-fontawesome',
                    DAILYBUDDY_URL . 'assets/css/font-awesome/css/all.min.css',
                    array(),
                    '6.5.1'
                );
            }
            ?>
            <?php wp_head(); ?>
        </head>

        <body>
            <?php
            // Load layout template
            $layout_file = __DIR__ . '/layouts/' . $layout . '.php';
            if (file_exists($layout_file)) {
                include $layout_file;
            }
            ?>

            <?php if ($settings['show_login_button']) : ?>
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="login-button">
                    <i class="fas fa-sign-in-alt"></i> <?php esc_html_e('Admin Login', 'dailybuddy'); ?>
                </a>
            <?php endif; ?>
            <?php wp_footer(); ?>
        </body>

        </html>
<?php
    }

    /**
     * Get social links HTML
     */
    private function get_social_html($settings)
    {
        if (! $settings['social_enabled']) {
            return '';
        }

        $social_links = array(
            'facebook' => array('icon' => 'fab fa-facebook', 'url' => $settings['social_facebook']),
            'twitter' => array('icon' => 'fab fa-twitter', 'url' => $settings['social_twitter']),
            'instagram' => array('icon' => 'fab fa-instagram', 'url' => $settings['social_instagram']),
            'linkedin' => array('icon' => 'fab fa-linkedin', 'url' => $settings['social_linkedin']),
            'youtube' => array('icon' => 'fab fa-youtube', 'url' => $settings['social_youtube']),
        );

        $has_links = false;
        foreach ($social_links as $link) {
            if (! empty($link['url'])) {
                $has_links = true;
                break;
            }
        }

        if (! $has_links) {
            return '';
        }

        $html = '<div class="social-links">';
        foreach ($social_links as $key => $link) {
            if (! empty($link['url'])) {
                $html .= '<a href="' . esc_url($link['url']) . '" target="_blank" rel="noopener noreferrer">';
                $html .= '<i class="' . esc_attr($link['icon']) . '"></i>';
                $html .= '</a>';
            }
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Get available layouts
     */
    public static function get_available_layouts()
    {
        $layouts_dir = __DIR__ . '/layouts/';
        $layouts = array();

        if (is_dir($layouts_dir)) {
            $files = scandir($layouts_dir);

            foreach ($files as $file) {
                if (substr($file, -4) !== '.php') {
                    continue;
                }

                if (in_array($file, array('module.php', 'config.php'))) {
                    continue;
                }

                $layout_id      = substr($file, 0, -4);
                $layout_file    = $layouts_dir . $file;
                $layout_content = file_get_contents($layout_file);

                // Layout-Name aus Kommentar: * Layout: Name
                if (preg_match('/\* Layout:\s*(.+)/i', $layout_content, $matches)) {
                    $layout_name = trim($matches[1]);
                } else {
                    $layout_name = ucfirst($layout_id);
                }

                // Preview-Pfad aus Kommentar: * Preview: pfad/zum/bild.png
                $layout_preview = '';
                if (preg_match('/\* Preview:\s*(.+)/i', $layout_content, $matches)) {
                    $layout_preview = trim($matches[1]);
                }

                $layouts[$layout_id] = array(
                    'id'      => $layout_id,
                    'name'    => $layout_name,
                    'file'    => $layout_file,
                    'preview' => $layout_preview, // RELATIVER Pfad
                );
            }
        }

        return $layouts;
    }

    /**
     * Generate layout preview HTML
     */
    public static function get_layout_preview_html($layout_id)
    {
        $layouts = self::get_available_layouts();

        if (! isset($layouts[$layout_id])) {
            return '';
        }

        $layout = $layouts[$layout_id];

        if (empty($layout['preview'])) {
            return '';
        }

        $src = DAILYBUDDY_URL . 'modules/wordpress-tools/under-construction/layouts/images/' . ltrim($layout['preview'], '/\\');

        return '<img src="' . esc_url($src) . '" alt="' . esc_attr($layout['name']) . '" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">';
    }
}

// Initialize module
new Dailybuddy_Under_Construction();

/**
 * Render settings page for Under Construction module
 */
/**
 * Render settings page
 */
function dailybuddy_render_under_construction_settings($module_data)
{
    // Enqueue styles
    wp_enqueue_style('dailybuddy-uc', DAILYBUDDY_URL . 'assets/css/modul-settings.css', array(), DAILYBUDDY_VERSION);
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media();

    // Include template (relative to this module file)
    include __DIR__ . '/templates/settings-page.php';
}

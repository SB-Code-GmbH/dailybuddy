<?php

/**
 * Module: Under Construction
 * 
 * Shows a maintenance page for non-logged-in users
 */

if (! defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Under_Construction
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

        // Prüfen, ob die WP_Dailybuddy_Settings-Klasse verfügbar ist
        if (! class_exists('WP_Dailybuddy_Settings')) {
            return;
        }

        $modules   = WP_Dailybuddy_Settings::get_modules();
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

        // ---------------------------
        // Toggle-Script für Admin-Bar
        // ---------------------------
        add_action('admin_footer', array($this, 'admin_bar_toggle_script'));
        add_action('wp_footer', array($this, 'admin_bar_toggle_script'));
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
     * Admin bar toggle script
     */
    public function admin_bar_toggle_script()
    {




?>
        <script>
            jQuery(document).ready(function($) {

                var $toggle = $('#dailybuddy-uc-toggle-switch');
                var $statusText = $('#dailybuddy-uc-status');

                if (!$toggle.length) {
                    return;
                }

                // Knopf + Hintergrund visuell an den Checkbox-Status anpassen
                function updateSwitchVisual() {
                    var label = $toggle.parent()[0];
                    if (!label) return;

                    var spans = label.getElementsByTagName('span');
                    if (spans.length < 2) return;

                    var track = spans[0]; // Hintergrund
                    var knob = spans[1]; // runde "Kugel"

                    if ($toggle.is(':checked')) {
                        track.style.backgroundColor = '#00a32a';
                        knob.style.left = '22px';
                    } else {
                        track.style.backgroundColor = '#8c8f94';
                        knob.style.left = '2px';
                    }
                }

                // Initialer Zustand beim Laden
                updateSwitchVisual();

                $toggle.on('change', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var isChecked = $toggle.is(':checked');

                    // Optimistic UI – Status-Text
                    if (isChecked) {
                        $statusText
                            .css('color', '#00a32a')
                            .html('✓ <?php echo esc_js(__('Maintenance Mode Active', 'dailybuddy')); ?>');
                    } else {
                        $statusText
                            .css('color', '#646970')
                            .html('○ <?php echo esc_js(__('Maintenance Mode Inactive', 'dailybuddy')); ?>');
                    }

                    // Optik des Switches direkt anpassen
                    updateSwitchVisual();

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'dailybuddy_toggle_maintenance',
                            enabled: isChecked ? 1 : 0,
                            nonce: '<?php echo esc_js(wp_create_nonce('dailybuddy_toggle_maintenance')); ?>'
                        },
                        success: function(response) {
                            if (!response || !response.success) {
                                // Revert on error
                                $toggle.prop('checked', !isChecked);
                                updateSwitchVisual();

                                if (!isChecked) {
                                    $statusText
                                        .css('color', '#00a32a')
                                        .html('✓ <?php echo esc_js(__('Maintenance Mode Active', 'dailybuddy')); ?>');
                                } else {
                                    $statusText
                                        .css('color', '#646970')
                                        .html('○ <?php echo esc_js(__('Maintenance Mode Inactive', 'dailybuddy')); ?>');
                                }
                            }
                        },
                        error: function() {
                            // Revert on error
                            $toggle.prop('checked', !isChecked);
                            updateSwitchVisual();

                            if (!isChecked) {
                                $statusText
                                    .css('color', '#00a32a')
                                    .html('✓ <?php echo esc_js(__('Maintenance Mode Active', 'dailybuddy')); ?>');
                            } else {
                                $statusText
                                    .css('color', '#646970')
                                    .html('○ <?php echo esc_js(__('Maintenance Mode Inactive', 'dailybuddy')); ?>');
                            }
                        }
                    });
                });

                // Verhindern, dass das Dropdown schließt, wenn man den Switch klickt
                $('#dailybuddy-uc-toggle').on('click', function(e) {
                    e.stopPropagation();
                });
            });
        </script>
    <?php
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
        $social_html = $this->get_social_html($settings);

    ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>

        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="robots" content="noindex, nofollow">
            <title><?php esc_html_e('Maintenance Mode', 'dailybuddy'); ?> - <?php bloginfo('name'); ?></title>
            <?php
            // NUR Frontend
            if (! is_admin()) {

                // Pfad anpassen, je nachdem wo die Datei liegt:
                // z.B. DAILYBUDDY_URL . 'assets/css/font-awesome/css/all.min.css'
                wp_register_style(
                    'dailybuddy-fontawesome',
                    DAILYBUDDY_URL . 'assets/css/font-awesome/css/all.min.css',
                    array(),
                    '6.5.1'
                );

                // in die Queue hängen
                wp_enqueue_style('dailybuddy-fontawesome');

                // und JETZT direkt ausgeben – ohne wp_head()
                wp_print_styles('dailybuddy-fontawesome');
            }
            ?>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #ffffff;
                    min-height: 100vh;
                }

                /* Social Links */
                .social-links {
                    display: flex;
                    gap: 20px;
                    margin-top: 30px;
                    justify-content: center;
                }

                .social-links a {
                    color: #ffffff;
                    font-size: 1.5rem;
                    transition: opacity 0.3s, transform 0.3s;
                    opacity: 0.8;
                }

                .social-links a:hover {
                    opacity: 1;
                    transform: scale(1.2);
                }

                /* Login Button */
                .login-button {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: rgba(255, 255, 255, 0.2);
                    backdrop-filter: blur(10px);
                    padding: 12px 24px;
                    border-radius: 50px;
                    text-decoration: none;
                    color: #ffffff;
                    font-size: 0.9rem;
                    transition: all 0.3s;
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }

                .login-button:hover {
                    background: rgba(255, 255, 255, 0.3);
                    transform: translateY(-2px);
                }

                <?php
                /* Custom CSS – sanitized on save in control callback */
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $settings['custom_css'];
                ?>
            </style>
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
new WP_Dailybuddy_Under_Construction();

/**
 * Render settings page for Under Construction module
 */
function dailybuddy_render_under_construction_settings($module_data)
{
    // Enqueue styles
    wp_enqueue_style('dailybuddy-uc', DAILYBUDDY_URL . 'assets/css/modul-settings.css', array(), DAILYBUDDY_VERSION);
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media();

    // Get current settings
    $settings = get_option('dailybuddy_under_construction_settings', array(
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

    // Get current tab
    $current_tab = isset($_POST['current_tab'])
        ? sanitize_text_field(wp_unslash($_POST['current_tab']))
        : 'general';


    // Handle form submission
    if (isset($_POST['dailybuddy_save_uc_settings'])) {
        check_admin_referer('dailybuddy_uc_settings');

        // Helper: sichere bool-Werte (Checkboxen)
        $dailybuddy_maintenance_active   = ! empty($_POST['uc_maintenance_active']);
        $dailybuddy_show_login_button    = ! empty($_POST['uc_show_login']);
        $dailybuddy_auto_end_enabled     = ! empty($_POST['uc_auto_end_enabled']);
        $dailybuddy_admin_bar_notice     = ! empty($_POST['uc_admin_bar_notice']);
        $dailybuddy_social_enabled       = ! empty($_POST['uc_social_enabled']);

        // Texte
        $dailybuddy_title   = isset($_POST['uc_title'])
            ? sanitize_text_field(wp_unslash($_POST['uc_title']))
            : '';

        $dailybuddy_message = isset($_POST['uc_message'])
            ? sanitize_textarea_field(wp_unslash($_POST['uc_message']))
            : '';

        $dailybuddy_auto_end_datetime = isset($_POST['uc_auto_end_datetime'])
            ? sanitize_text_field(wp_unslash($_POST['uc_auto_end_datetime']))
            : '';

        $dailybuddy_layout = isset($_POST['uc_layout'])
            ? sanitize_text_field(wp_unslash($_POST['uc_layout']))
            : '';

        $dailybuddy_custom_css = isset($_POST['uc_custom_css'])
            ? sanitize_textarea_field(wp_unslash($_POST['uc_custom_css']))
            : '';

        // Social URLs
        $dailybuddy_social_facebook = isset($_POST['uc_social_facebook'])
            ? esc_url_raw(wp_unslash($_POST['uc_social_facebook']))
            : '';

        $dailybuddy_social_twitter = isset($_POST['uc_social_twitter'])
            ? esc_url_raw(wp_unslash($_POST['uc_social_twitter']))
            : '';

        $dailybuddy_social_instagram = isset($_POST['uc_social_instagram'])
            ? esc_url_raw(wp_unslash($_POST['uc_social_instagram']))
            : '';

        $dailybuddy_social_linkedin = isset($_POST['uc_social_linkedin'])
            ? esc_url_raw(wp_unslash($_POST['uc_social_linkedin']))
            : '';

        $dailybuddy_social_youtube = isset($_POST['uc_social_youtube'])
            ? esc_url_raw(wp_unslash($_POST['uc_social_youtube']))
            : '';

        $new_settings = array(
            // General
            'maintenance_active' => $dailybuddy_maintenance_active,
            'title'              => $dailybuddy_title,
            'message'            => $dailybuddy_message,
            'show_login_button'  => $dailybuddy_show_login_button,
            'auto_end_enabled'   => $dailybuddy_auto_end_enabled,
            'auto_end_datetime'  => $dailybuddy_auto_end_datetime,
            'admin_bar_notice'   => $dailybuddy_admin_bar_notice,

            // Design
            'layout'     => $dailybuddy_layout,
            'custom_css' => $dailybuddy_custom_css,

            // Social
            'social_enabled'  => $dailybuddy_social_enabled,
            'social_facebook' => $dailybuddy_social_facebook,
            'social_twitter'  => $dailybuddy_social_twitter,
            'social_instagram' => $dailybuddy_social_instagram,
            'social_linkedin' => $dailybuddy_social_linkedin,
            'social_youtube'  => $dailybuddy_social_youtube,
        );

        update_option('dailybuddy_under_construction_settings', $new_settings);
        $settings = $new_settings;

        echo '<div class="notice notice-success"><p>' .
            esc_html__('Settings saved!', 'dailybuddy') .
            '</p></div>';
    }

    // Get available layouts
    $available_layouts = WP_Dailybuddy_Under_Construction::get_available_layouts();

    ?>

    <form method="post" action="">
        <?php wp_nonce_field('dailybuddy_uc_settings'); ?>
        <input type="hidden" name="current_tab" id="current_tab" value="<?php echo esc_attr($current_tab); ?>">

        <!-- Tabs -->
        <div class="dailybuddy-uc-tabs">
            <button type="button" class="dailybuddy-uc-tab <?php echo $current_tab === 'general' ? 'active' : ''; ?>" data-tab="general">
                <i class="fas fa-cog"></i> <?php esc_html_e('General', 'dailybuddy'); ?>
            </button>
            <button type="button" class="dailybuddy-uc-tab <?php echo $current_tab === 'design' ? 'active' : ''; ?>" data-tab="design">
                <i class="fas fa-palette"></i> <?php esc_html_e('Design', 'dailybuddy'); ?>
            </button>
            <button type="button" class="dailybuddy-uc-tab <?php echo $current_tab === 'social' ? 'active' : ''; ?>" data-tab="social">
                <i class="fas fa-share-alt"></i> <?php esc_html_e('Social Media', 'dailybuddy'); ?>
            </button>
        </div>

        <!-- General Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $current_tab === 'general' ? 'active' : ''; ?>" data-tab="general">

            <!-- Maintenance Mode Toggle -->
            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Wartungsmodus', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Enable or disable the under construction page', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_maintenance_active" value="1" <?php checked($settings['maintenance_active'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_title"><?php esc_html_e('Seitentitel', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="uc_title" name="uc_title"
                                value="<?php echo esc_attr($settings['title']); ?>"
                                class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_message"><?php esc_html_e('Nachricht', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <textarea id="uc_message" name="uc_message" rows="4"
                                class="large-text"><?php echo esc_textarea($settings['message']); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Show Login Button', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show admin login link in bottom right corner', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_show_login" value="1" <?php checked($settings['show_login_button'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Admin Bar Notice', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show warning in WordPress admin bar when mode is active', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_admin_bar_notice" value="1" <?php checked($settings['admin_bar_notice'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Auto-End Mode', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Automatically disable maintenance mode at specific date/time', 'dailybuddy'); ?></p>
                    <input type="datetime-local" name="uc_auto_end_datetime"
                        value="<?php echo esc_attr($settings['auto_end_datetime']); ?>"
                        class="regular-text" style="margin-top: 10px;">
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_auto_end_enabled" value="1" <?php checked($settings['auto_end_enabled'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Design Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $current_tab === 'design' ? 'active' : ''; ?>" data-tab="design">

            <h3><?php esc_html_e('Layout', 'dailybuddy'); ?></h3>
            <p class="description"><?php esc_html_e('Select a layout style for your maintenance page', 'dailybuddy'); ?></p>

            <div class="layout-preview-grid">
                <?php foreach ($available_layouts as $layout) : ?>
                    <label class="layout-preview <?php echo $settings['layout'] === $layout['id'] ? 'selected' : ''; ?>">
                        <input type="radio" name="uc_layout" value="<?php echo esc_attr($layout['id']); ?>"
                            <?php checked($settings['layout'], $layout['id']); ?>>
                        <div class="layout-preview-inner">
                            <?php
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo WP_Dailybuddy_Under_Construction::get_layout_preview_html($layout['id']);
                            ?>
                        </div>
                        <div class="layout-preview-label"><?php echo esc_html($layout['name']); ?></div>
                    </label>
                <?php endforeach; ?>
            </div>


            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_custom_css"><?php esc_html_e('Custom CSS', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <textarea id="uc_custom_css" name="uc_custom_css" rows="10"
                                class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                            <p class="description"><?php esc_html_e('Add custom CSS to further customize your maintenance page', 'dailybuddy'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <!-- Social Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $current_tab === 'social' ? 'active' : ''; ?>" data-tab="social">

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Enable Social Links', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show social media links on maintenance page', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_social_enabled" value="1" <?php checked($settings['social_enabled'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_social_facebook">
                                <i class="fab fa-facebook dailybuddy-uc-social-icon"></i> Facebook
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_facebook" name="uc_social_facebook"
                                value="<?php echo esc_attr($settings['social_facebook']); ?>"
                                class="regular-text" placeholder="https://facebook.com/yourpage">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_twitter">
                                <i class="fab fa-twitter dailybuddy-uc-social-icon"></i> Twitter/X
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_twitter" name="uc_social_twitter"
                                value="<?php echo esc_attr($settings['social_twitter']); ?>"
                                class="regular-text" placeholder="https://twitter.com/yourhandle">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_instagram">
                                <i class="fab fa-instagram dailybuddy-uc-social-icon"></i> Instagram
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_instagram" name="uc_social_instagram"
                                value="<?php echo esc_attr($settings['social_instagram']); ?>"
                                class="regular-text" placeholder="https://instagram.com/yourhandle">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_linkedin">
                                <i class="fab fa-linkedin dailybuddy-uc-social-icon"></i> LinkedIn
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_linkedin" name="uc_social_linkedin"
                                value="<?php echo esc_attr($settings['social_linkedin']); ?>"
                                class="regular-text" placeholder="https://linkedin.com/company/yourcompany">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_youtube">
                                <i class="fab fa-youtube dailybuddy-uc-social-icon"></i> YouTube
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_youtube" name="uc_social_youtube"
                                value="<?php echo esc_attr($settings['social_youtube']); ?>"
                                class="regular-text" placeholder="https://youtube.com/@yourchannel">
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <p class="submit">
            <button type="submit" name="dailybuddy_save_uc_settings" class="button button-primary button-large">
                <i class="fas fa-save"></i> <?php esc_html_e('Save Settings', 'dailybuddy'); ?>
            </button>
        </p>

    </form>

    <script>
        jQuery(document).ready(function($) {
            // Tab Switching
            $('.dailybuddy-uc-tab').on('click', function() {
                var tab = $(this).data('tab');

                // Update hidden field
                $('#current_tab').val(tab);

                // Switch tabs
                $('.dailybuddy-uc-tab').removeClass('active');
                $(this).addClass('active');

                $('.dailybuddy-uc-tab-content').removeClass('active');
                $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');
            });

            // Layout Selection
            $('.layout-preview').on('click', function() {
                $('.layout-preview').removeClass('selected');
                $(this).addClass('selected');
            });

            // Update admin bar on page load if settings were saved
            <?php if (isset($_POST['dailybuddy_save_uc_settings'])) : ?>
                if (window.parent && window.parent.jQuery) {
                    var isActive = <?php echo $settings['maintenance_active'] ? 'true' : 'false'; ?>;
                    var $statusText = window.parent.jQuery('#dailybuddy-uc-status');
                    var $toggle = window.parent.jQuery('#dailybuddy-uc-toggle-switch');

                    if (isActive) {
                        $statusText.css('color', '#00a32a').html('✓ <?php echo esc_js(__('Maintenance Mode Active', 'dailybuddy')); ?>');
                        $toggle.prop('checked', true);
                    } else {
                        $statusText.css('color', '#646970').html('○ <?php echo esc_js(__('Maintenance Mode Inactive', 'dailybuddy')); ?>');
                        $toggle.prop('checked', false);
                    }
                }
            <?php endif; ?>
        });
    </script>

<?php
}

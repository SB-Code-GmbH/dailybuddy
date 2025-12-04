<?php

/**
 * Module: Security Monitor Dashboard Widget
 * 
 * Compact security overview
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Security_Monitor_Widget
{
    private $transient_prefix = 'dailybuddy_security_';

    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

        // Track login attempts
        add_action('wp_login', array($this, 'track_successful_login'), 10, 2);
        add_action('wp_login_failed', array($this, 'track_failed_login'));

        // Remove icon from screen options
        add_filter('hidden_meta_boxes', array($this, 'filter_widget_title'), 10, 3);
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_security_monitor',
            __('security monitor', 'dailybuddy'), // OHNE Icon
            array($this, 'render_widget'),
            null,
            null,
            'normal',
            'high'
        );
    }

    /**
     * Filter widget title for screen options
     */
    public function filter_widget_title($hidden, $screen, $use_defaults)
    {
        // Add custom CSS to hide the icon in screen options only
        add_action('admin_head', function () {
            echo '<style>
            #screen-meta label[for="dailybuddy_security_monitor-hide"] .dashicons {
                display: none !important;
            }
        </style>';
        });

        return $hidden;
    }

    /**
     * Enqueue widget styles
     */
    public function enqueue_styles($hook)
    {
        if ($hook !== 'index.php') {
            return;
        }

        $css = "
        #dailybuddy_security_monitor .hndle::before {
            content: '\\f332';
            font-family: dashicons;
            margin-right: 12px;
            font-size: 20px;
            color: #000;
        }

        #dailybuddy_security_monitor .security-overview {
            margin: 12px 0;
        }
        
        #dailybuddy_security_monitor .security-status {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f0f6ff;
            border-left: 4px solid #2271b1;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        #dailybuddy_security_monitor .security-status.good {
            background: #f0fff4;
            border-left-color: #00a32a;
        }
        
        #dailybuddy_security_monitor .security-status.warning {
            background: #fffbf0;
            border-left-color: #f0b849;
        }
        
        #dailybuddy_security_monitor .security-status.critical {
            background: #fff0f0;
            border-left-color: #d63638;
        }
        
        #dailybuddy_security_monitor .security-status-icon {
            font-size: 24px;
            width: 24px;
            height: 24px;
        }
        
        #dailybuddy_security_monitor .security-status-icon.good {
            color: #00a32a;
        }
        
        #dailybuddy_security_monitor .security-status-icon.warning {
            color: #f0b849;
        }
        
        #dailybuddy_security_monitor .security-status-icon.critical {
            color: #d63638;
        }
        
        #dailybuddy_security_monitor .security-status-text {
            flex: 1;
        }
        
        #dailybuddy_security_monitor .security-status-title {
            font-size: 13px;
            font-weight: 600;
            margin: 0 0 2px 0;
        }
        
        #dailybuddy_security_monitor .security-status-message {
            font-size: 11px;
            color: #646970;
            margin: 0;
        }
        
        #dailybuddy_security_monitor .security-metrics {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        #dailybuddy_security_monitor .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        
        #dailybuddy_security_monitor .metric-item:hover {
            background: #f0f0f1;
        }
        
        #dailybuddy_security_monitor .metric-item.alert {
            background: #fff0f0;
            border-left: 3px solid #d63638;
        }
        
        #dailybuddy_security_monitor .metric-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #646970;
        }
        
        #dailybuddy_security_monitor .metric-icon {
            font-size: 16px;
            width: 16px;
            height: 16px;
            color: #8c8f94;
        }
        
        #dailybuddy_security_monitor .metric-item.alert .metric-icon {
            color: #d63638;
        }
        
        #dailybuddy_security_monitor .metric-value {
            font-size: 16px;
            font-weight: 700;
            color: #1d2327;
        }
        
        #dailybuddy_security_monitor .metric-item.alert .metric-value {
            color: #d63638;
        }
        
        #dailybuddy_security_monitor .updates-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dcdcde;
        }
        
        #dailybuddy_security_monitor .updates-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #646970;
            margin: 0 0 10px 0;
            letter-spacing: 0.5px;
        }
        
        #dailybuddy_security_monitor .update-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        #dailybuddy_security_monitor .update-item:last-child {
            margin-bottom: 0;
        }
        
        #dailybuddy_security_monitor .update-item.outdated {
            border-color: #d63638;
            background: #fff0f0;
        }
        
        #dailybuddy_security_monitor .update-name {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #1d2327;
        }
        
        #dailybuddy_security_monitor .update-name .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
            color: #f0b849;
        }
        
        #dailybuddy_security_monitor .update-item.outdated .update-name .dashicons {
            color: #d63638;
        }
        
        #dailybuddy_security_monitor .update-badge {
            padding: 2px 6px;
            background: #f0b849;
            color: #fff;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }
        
        #dailybuddy_security_monitor .update-item.outdated .update-badge {
            background: #d63638;
        }
        
        #dailybuddy_security_monitor .no-updates {
            text-align: center;
            padding: 15px;
            color: #00a32a;
            font-size: 12px;
        }
        
        #dailybuddy_security_monitor .no-updates .dashicons {
            font-size: 24px;
            width: 24px;
            height: 24px;
            display: block;
            margin: 0 auto 8px;
        }
        ";

        wp_add_inline_style('dashboard', $css);
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        $security_data = $this->get_security_data();
        $security_score = $this->calculate_security_score($security_data);
?>
        <div class="security-overview">

            <!-- Security Status -->
            <div class="security-status <?php echo esc_attr($security_score['status']); ?>">
                <span class="dashicons dashicons-<?php echo esc_attr($security_score['icon']); ?> security-status-icon <?php echo esc_attr($security_score['status']); ?>"></span>
                <div class="security-status-text">
                    <h4 class="security-status-title"><?php echo esc_html($security_score['title']); ?></h4>
                    <p class="security-status-message"><?php echo esc_html($security_score['message']); ?></p>
                </div>
            </div>

            <!-- Security Metrics -->
            <div class="security-metrics">

                <!-- Admin Logins Today -->
                <div class="metric-item">
                    <span class="metric-label">
                        <span class="dashicons dashicons-admin-users metric-icon"></span>
                        <?php esc_html_e('Admin Logins Today', 'dailybuddy'); ?>
                    </span>
                    <span class="metric-value"><?php echo esc_html($security_data['logins_today']); ?></span>
                </div>

                <!-- Failed Login Attempts -->
                <div class="metric-item <?php echo $security_data['failed_logins'] > 5 ? 'alert' : ''; ?>">
                    <span class="metric-label">
                        <span class="dashicons dashicons-dismiss metric-icon"></span>
                        <?php esc_html_e('Failed Logins (24h)', 'dailybuddy'); ?>
                    </span>
                    <span class="metric-value"><?php echo esc_html($security_data['failed_logins']); ?></span>
                </div>

                <!-- Available Updates -->
                <div class="metric-item <?php echo $security_data['total_updates'] > 0 ? 'alert' : ''; ?>">
                    <span class="metric-label">
                        <span class="dashicons dashicons-update metric-icon"></span>
                        <?php esc_html_e('Updates Available', 'dailybuddy'); ?>
                    </span>
                    <span class="metric-value"><?php echo esc_html($security_data['total_updates']); ?></span>
                </div>

                <!-- Outdated Components -->
                <div class="metric-item <?php echo $security_data['outdated_count'] > 0 ? 'alert' : ''; ?>">
                    <span class="metric-label">
                        <span class="dashicons dashicons-warning metric-icon"></span>
                        <?php esc_html_e('Outdated Components', 'dailybuddy'); ?>
                    </span>
                    <span class="metric-value"><?php echo esc_html($security_data['outdated_count']); ?></span>
                </div>

            </div>

            <!-- Updates Section -->
            <?php if (!empty($security_data['updates_list'])) : ?>
                <div class="updates-section">
                    <h5 class="updates-title"><?php esc_html_e('Updates Required', 'dailybuddy'); ?></h5>
                    <?php foreach ($security_data['updates_list'] as $update) : ?>
                        <div class="update-item <?php echo $update['is_outdated'] ? 'outdated' : ''; ?>">
                            <span class="update-name">
                                <span class="dashicons dashicons-<?php echo esc_attr($update['icon']); ?>"></span>
                                <?php echo esc_html($update['name']); ?>
                            </span>
                            <span class="update-badge"><?php echo esc_html($update['count']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="updates-section">
                    <div class="no-updates">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php esc_html_e('All plugins and themes are up to date!', 'dailybuddy'); ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
<?php
    }

    /**
     * Get security data
     */
    private function get_security_data()
    {
        $data = array(
            'logins_today'    => $this->get_logins_today(),
            'failed_logins'   => $this->get_failed_logins(),
            'total_updates'   => 0,
            'outdated_count'  => 0,
            'updates_list'    => array(),
        );

        // Get plugin updates
        $plugin_updates = get_site_transient('update_plugins');
        $plugin_count = !empty($plugin_updates->response) ? count($plugin_updates->response) : 0;

        // Get theme updates
        $theme_updates = get_site_transient('update_themes');
        $theme_count = !empty($theme_updates->response) ? count($theme_updates->response) : 0;

        // Get core updates
        $core_updates = get_site_transient('update_core');
        $core_count = 0;
        if (
            $core_updates && isset($core_updates->updates[0]) &&
            version_compare(get_bloginfo('version'), $core_updates->updates[0]->version, '<')
        ) {
            $core_count = 1;
        }

        $data['total_updates'] = $plugin_count + $theme_count + $core_count;

        // Check for outdated components
        $outdated = $this->check_outdated_components();
        $data['outdated_count'] = $outdated['count'];

        // Build updates list
        if ($core_count > 0) {
            $data['updates_list'][] = array(
                'name'        => 'WordPress Core',
                'count'       => $core_count,
                'icon'        => 'wordpress',
                'is_outdated' => $outdated['core'],
            );
        }

        if ($plugin_count > 0) {
            $data['updates_list'][] = array(
                'name'        => __('Plugins', 'dailybuddy'),
                'count'       => $plugin_count,
                'icon'        => 'admin-plugins',
                'is_outdated' => $outdated['plugins'],
            );
        }

        if ($theme_count > 0) {
            $data['updates_list'][] = array(
                'name'        => __('Themes', 'dailybuddy'),
                'count'       => $theme_count,
                'icon'        => 'admin-appearance',
                'is_outdated' => $outdated['themes'],
            );
        }

        return $data;
    }

    /**
     * Get successful logins today
     */
    private function get_logins_today()
    {
        $logins = get_transient($this->transient_prefix . 'logins_today');
        return $logins ? count($logins) : 0;
    }

    /**
     * Get failed logins in last 24 hours
     */
    private function get_failed_logins()
    {
        $failed = get_transient($this->transient_prefix . 'failed_logins');
        return $failed ? count($failed) : 0;
    }

    /**
     * Track successful login
     */
    public function track_successful_login($user_login, $user)
    {
        // Only track admin users
        if (!in_array('administrator', $user->roles)) {
            return;
        }

        $logins = get_transient($this->transient_prefix . 'logins_today');
        if (!$logins) {
            $logins = array();
        }

        $logins[] = array(
            'user'      => $user_login,
            'timestamp' => current_time('timestamp'),
        );

        set_transient($this->transient_prefix . 'logins_today', $logins, DAY_IN_SECONDS);
    }

    /**
     * Track failed login
     */
    public function track_failed_login($username)
    {
        $failed = get_transient($this->transient_prefix . 'failed_logins');
        if (!$failed) {
            $failed = array();
        }

        $ip_address = isset($_SERVER['REMOTE_ADDR'])
            ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
            : 'unknown';

        $failed[] = array(
            'username'  => $username,
            'timestamp' => current_time('timestamp'),
            'ip'        => $ip_address,
        );
    }

    /**
     * Check for outdated components
     */
    private function check_outdated_components()
    {
        $outdated = array(
            'count'   => 0,
            'core'    => false,
            'plugins' => false,
            'themes'  => false,
        );

        // Check WordPress Core (older than 2 minor versions)
        $current_version = get_bloginfo('version');
        $core_updates = get_site_transient('update_core');
        if ($core_updates && isset($core_updates->updates[0])) {
            $latest_version = $core_updates->updates[0]->version;
            $version_parts_current = explode('.', $current_version);
            $version_parts_latest = explode('.', $latest_version);

            if (isset($version_parts_current[1]) && isset($version_parts_latest[1])) {
                $diff = (int)$version_parts_latest[1] - (int)$version_parts_current[1];
                if ($diff >= 2) {
                    $outdated['core'] = true;
                    $outdated['count']++;
                }
            }
        }

        // Check for plugins with major version differences
        $plugin_updates = get_site_transient('update_plugins');
        if (!empty($plugin_updates->response)) {
            $outdated['plugins'] = true;
            $outdated['count']++;
        }

        // Check for theme updates
        $theme_updates = get_site_transient('update_themes');
        if (!empty($theme_updates->response)) {
            $outdated['themes'] = true;
            $outdated['count']++;
        }

        return $outdated;
    }

    /**
     * Calculate security score
     */
    private function calculate_security_score($data)
    {
        $score = array(
            'status'  => 'good',
            'icon'    => 'yes-alt',
            'title'   => __('Security Status: Good', 'dailybuddy'),
            'message' => __('No immediate security concerns.', 'dailybuddy'),
        );

        // Check for critical issues
        if ($data['failed_logins'] > 10) {
            return array(
                'status'  => 'critical',
                'icon'    => 'warning',
                'title'   => __('Security Alert: High Risk', 'dailybuddy'),
                'message' => __('Multiple failed login attempts detected!', 'dailybuddy'),
            );
        }

        if ($data['outdated_count'] > 0) {
            return array(
                'status'  => 'critical',
                'icon'    => 'warning',
                'title'   => __('Security Alert: Outdated', 'dailybuddy'),
                'message' => __('Critical components need updates!', 'dailybuddy'),
            );
        }

        // Check for warnings
        if ($data['failed_logins'] > 5 || $data['total_updates'] > 3) {
            return array(
                'status'  => 'warning',
                'icon'    => 'info',
                'title'   => __('Security Status: Monitor', 'dailybuddy'),
                'message' => __('Some issues require attention.', 'dailybuddy'),
            );
        }

        if ($data['total_updates'] > 0) {
            return array(
                'status'  => 'warning',
                'icon'    => 'update',
                'title'   => __('Security Status: Updates Available', 'dailybuddy'),
                'message' => __('Updates are available for installation.', 'dailybuddy'),
            );
        }

        return $score;
    }
}

// Initialize module
new WP_Dailybuddy_Security_Monitor_Widget();

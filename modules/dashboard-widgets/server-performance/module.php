<?php

/**
 * Module: Server & Performance Dashboard Widget
 * 
 * Shows server information and health status
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Server_Performance_Widget
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_server_performance',
            __('Server & Performance', 'dailybuddy'),
            array($this, 'render_widget'),
            null,
            null,
            'normal',
            'high'
        );
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

        #dailybuddy_server_performance .hndle::before {
            content: '\\f239';
            font-family: dashicons;
            margin-right: 12px;
            font-size: 20px;
            color: #000;
        }

            #dailybuddy_server_performance .inside {
                margin: 0;
                padding: 0;
            }
            
            #dailybuddy_server_performance .server-info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 10px;
                margin: 0;
                padding: 12px;
            }
            
            #dailybuddy_server_performance .info-card {
                background: #f8f9fa;
                border-radius: 6px;
                padding: 10px;
                border-left: 3px solid #2271b1;
                transition: all 0.2s ease;
            }
            
            #dailybuddy_server_performance .info-card:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            }
            
            #dailybuddy_server_performance .info-card.status-good {
                border-left-color: #00a32a;
            }
            
            #dailybuddy_server_performance .info-card.status-warning {
                border-left-color: #f0b849;
            }
            
            #dailybuddy_server_performance .info-card.status-critical {
                border-left-color: #d63638;
            }
            
            #dailybuddy_server_performance .info-header {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 6px;
            }
            
            #dailybuddy_server_performance .info-icon {
                font-size: 16px;
                width: 16px;
                height: 16px;
                opacity: 0.7;
            }
            
            #dailybuddy_server_performance .info-icon.status-good {
                color: #00a32a;
            }
            
            #dailybuddy_server_performance .info-icon.status-warning {
                color: #f0b849;
            }
            
            #dailybuddy_server_performance .info-icon.status-critical {
                color: #d63638;
            }
            
            #dailybuddy_server_performance .info-title {
                font-size: 11px;
                font-weight: 600;
                color: #646970;
                text-transform: uppercase;
                letter-spacing: 0.3px;
            }
            
            #dailybuddy_server_performance .info-value {
                font-size: 16px;
                font-weight: 700;
                color: #1d2327;
                margin: 4px 0;
            }
            
            #dailybuddy_server_performance .info-label {
                font-size: 11px;
                color: #646970;
                margin-top: 2px;
            }
            
            #dailybuddy_server_performance .info-status {
                display: inline-flex;
                align-items: center;
                gap: 3px;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 10px;
                font-weight: 600;
                margin-top: 4px;
            }
            
            #dailybuddy_server_performance .info-status.good {
                background: #e6f7ec;
                color: #00a32a;
            }
            
            #dailybuddy_server_performance .info-status.warning {
                background: #fff7e6;
                color: #f0b849;
            }
            
            #dailybuddy_server_performance .info-status.critical {
                background: #ffe7e7;
                color: #d63638;
            }
            
            #dailybuddy_server_performance .info-status .dashicons {
                font-size: 12px;
                width: 12px;
                height: 12px;
            }
            
            #dailybuddy_server_performance .progress-bar {
                height: 4px;
                background: rgba(0, 0, 0, 0.1);
                border-radius: 2px;
                overflow: hidden;
                margin-top: 4px;
            }
            
            #dailybuddy_server_performance .progress-fill {
                height: 100%;
                border-radius: 2px;
                transition: width 0.3s ease;
            }
            
            #dailybuddy_server_performance .progress-fill.good {
                background: #00a32a;
            }
            
            #dailybuddy_server_performance .progress-fill.warning {
                background: #f0b849;
            }
            
            #dailybuddy_server_performance .progress-fill.critical {
                background: #d63638;
            }
            
            #dailybuddy_server_performance .health-summary {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 6px;
                padding: 10px 12px;
                margin: 12px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            #dailybuddy_server_performance .health-icon {
                font-size: 24px;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
            }
            
            #dailybuddy_server_performance .health-icon.good {
                color: #00a32a;
            }
            
            #dailybuddy_server_performance .health-icon.warning {
                color: #f0b849;
            }
            
            #dailybuddy_server_performance .health-icon.critical {
                color: #d63638;
            }
            
            #dailybuddy_server_performance .health-content {
                flex: 1;
            }
            
            #dailybuddy_server_performance .health-title {
                font-size: 13px;
                font-weight: 600;
                color: #1d2327;
                margin: 0 0 2px 0;
            }
            
            #dailybuddy_server_performance .health-message {
                font-size: 11px;
                color: #646970;
                margin: 0;
            }
            
            #dailybuddy_server_performance .info-details {
                font-size: 10px;
                color: #8c8f94;
                margin-top: 4px;
                line-height: 1.4;
            }
            
            @media screen and (max-width: 782px) {
                #dailybuddy_server_performance .server-info-grid {
                    grid-template-columns: 1fr;
                }
            }
            ";

        wp_add_inline_style('dashboard', $css);
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        $server_info = $this->get_server_info();
        $health_status = $this->calculate_health_status($server_info);
?>

        <!-- Health Summary -->
        <div class="health-summary">
            <span class="dashicons dashicons-<?php echo esc_attr($health_status['icon']); ?> health-icon <?php echo esc_attr($health_status['status']); ?>"></span>
            <div class="health-content">
                <h3 class="health-title"><?php echo esc_html($health_status['title']); ?></h3>
                <p class="health-message"><?php echo esc_html($health_status['message']); ?></p>
            </div>
        </div>

        <!-- Server Info Grid -->
        <div class="server-info-grid">

            <!-- PHP Version -->
            <div class="info-card <?php echo esc_attr($server_info['php']['status_class']); ?>">
                <div class="info-header">
                    <span class="dashicons dashicons-code-standards info-icon <?php echo esc_attr($server_info['php']['status_class']); ?>"></span>
                    <div class="info-title"><?php esc_html_e('PHP Version', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value"><?php echo esc_html($server_info['php']['version']); ?></div>
                <div class="info-label"><?php esc_html_e('Recommended: 8.0+', 'dailybuddy'); ?></div>
                <span class="info-status <?php echo esc_attr($server_info['php']['status']); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr($server_info['php']['status_icon']); ?>"></span>
                    <?php echo esc_html($server_info['php']['status_text']); ?>
                </span>
            </div>

            <!-- Memory Limit -->
            <div class="info-card <?php echo esc_attr($server_info['memory']['status_class']); ?>">
                <div class="info-header">
                    <span class="dashicons dashicons-database info-icon <?php echo esc_attr($server_info['memory']['status_class']); ?>"></span>
                    <div class="info-title"><?php esc_html_e('Memory Limit', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value"><?php echo esc_html($server_info['memory']['limit_formatted']); ?></div>
                <div class="info-label">
                    <?php
                    printf(
                        /* translators: 1: formatted memory usage (e.g., "256 MB"), 2: percentage (e.g., 75) */
                        esc_html__('Usage: %1$s (%2$d%%)', 'dailybuddy'),
                        esc_html($server_info['memory']['usage_formatted']),
                        (int) $server_info['memory']['percentage']
                    );
                    ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?php echo esc_attr($server_info['memory']['status']); ?>"
                        style="width: <?php echo esc_attr($server_info['memory']['percentage']); ?>%"></div>
                </div>
                <span class="info-status <?php echo esc_attr($server_info['memory']['status']); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr($server_info['memory']['status_icon']); ?>"></span>
                    <?php echo esc_html($server_info['memory']['status_text']); ?>
                </span>
            </div>

            <!-- MySQL Version -->
            <div class="info-card <?php echo esc_attr($server_info['mysql']['status_class']); ?>">
                <div class="info-header">
                    <span class="dashicons dashicons-database-view info-icon <?php echo esc_attr($server_info['mysql']['status_class']); ?>"></span>
                    <div class="info-title"><?php esc_html_e('Database', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value"><?php echo esc_html($server_info['mysql']['version']); ?></div>
                <div class="info-label"><?php echo esc_html($server_info['mysql']['type']); ?></div>
                <span class="info-status <?php echo esc_attr($server_info['mysql']['status']); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr($server_info['mysql']['status_icon']); ?>"></span>
                    <?php echo esc_html($server_info['mysql']['status_text']); ?>
                </span>
            </div>

            <!-- WordPress Version -->
            <div class="info-card <?php echo esc_attr($server_info['wordpress']['status_class']); ?>">
                <div class="info-header">
                    <span class="dashicons dashicons-wordpress info-icon <?php echo esc_attr($server_info['wordpress']['status_class']); ?>"></span>
                    <div class="info-title"><?php esc_html_e('WordPress', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value"><?php echo esc_html($server_info['wordpress']['version']); ?></div>
                <div class="info-label">
                    <?php if ($server_info['wordpress']['update_available']) : ?>
                        <?php
                        printf(
                            /* translators: %s is the latest WordPress version number */
                            esc_html__('Update available: %s', 'dailybuddy'),
                            esc_html($server_info['wordpress']['latest_version'])
                        );
                        ?>
                    <?php else : ?>
                        <?php esc_html_e('Up to date', 'dailybuddy'); ?>
                    <?php endif; ?>
                </div>
                <span class="info-status <?php echo esc_attr($server_info['wordpress']['status']); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr($server_info['wordpress']['status_icon']); ?>"></span>
                    <?php echo esc_html($server_info['wordpress']['status_text']); ?>
                </span>
            </div>

            <!-- Disk Space -->
            <div class="info-card <?php echo esc_attr($server_info['disk']['status_class']); ?>">
                <div class="info-header">
                    <span class="dashicons dashicons-media-default info-icon <?php echo esc_attr($server_info['disk']['status_class']); ?>"></span>
                    <div class="info-title"><?php esc_html_e('Disk Space', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value"><?php echo esc_html($server_info['disk']['free_formatted']); ?></div>
                <div class="info-label">
                    <?php
                    printf(
                        /* translators: 1: total disk space, 2: used disk space */
                        esc_html__('Total: %1$s | Used: %2$s', 'dailybuddy'),
                        esc_html($server_info['disk']['total_formatted']),
                        esc_html($server_info['disk']['used_formatted'])
                    );
                    ?>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill <?php echo esc_attr($server_info['disk']['status']); ?>"
                        style="width: <?php echo esc_attr($server_info['disk']['percentage']); ?>%"></div>
                </div>
                <span class="info-status <?php echo esc_attr($server_info['disk']['status']); ?>">
                    <span class="dashicons dashicons-<?php echo esc_attr($server_info['disk']['status_icon']); ?>"></span>
                    <?php echo esc_html($server_info['disk']['status_text']); ?>
                </span>
            </div>

            <!-- Server Software -->
            <div class="info-card status-good">
                <div class="info-header">
                    <span class="dashicons dashicons-cloud info-icon status-good"></span>
                    <div class="info-title"><?php esc_html_e('Web Server', 'dailybuddy'); ?></div>
                </div>
                <div class="info-value" style="font-size: 14px;"><?php echo esc_html($server_info['server']['software']); ?></div>
                <div class="info-details">
                    <strong><?php esc_html_e('OS:', 'dailybuddy'); ?></strong> <?php echo esc_html($server_info['server']['os']); ?><br>
                    <strong><?php esc_html_e('Max Upload:', 'dailybuddy'); ?></strong> <?php echo esc_html($server_info['server']['max_upload']); ?><br>
                    <strong><?php esc_html_e('Max Execution:', 'dailybuddy'); ?></strong> <?php echo esc_html($server_info['server']['max_execution']); ?>s
                </div>
            </div>

        </div>
<?php
    }

    /**
     * Get all server information
     */
    private function get_server_info()
    {
        global $wpdb;

        $info = array();

        // PHP Version
        $php_version = phpversion();
        $php_recommended = version_compare($php_version, '8.0', '>=');
        $php_minimum = version_compare($php_version, '7.4', '>=');

        $info['php'] = array(
            'version' => $php_version,
            'status' => $php_recommended ? 'good' : ($php_minimum ? 'warning' : 'critical'),
            'status_class' => $php_recommended ? 'status-good' : ($php_minimum ? 'status-warning' : 'status-critical'),
            'status_icon' => $php_recommended ? 'yes-alt' : ($php_minimum ? 'warning' : 'dismiss'),
            'status_text' => $php_recommended ? __('Good', 'dailybuddy') : ($php_minimum ? __('Update Recommended', 'dailybuddy') : __('Critical', 'dailybuddy')),
        );

        // Memory Limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $memory_usage = memory_get_usage(true);
        $memory_percentage = ($memory_usage / $memory_limit) * 100;

        $info['memory'] = array(
            'limit' => $memory_limit,
            'limit_formatted' => size_format($memory_limit),
            'usage' => $memory_usage,
            'usage_formatted' => size_format($memory_usage),
            'percentage' => round($memory_percentage),
            'status' => $memory_percentage < 70 ? 'good' : ($memory_percentage < 90 ? 'warning' : 'critical'),
            'status_class' => $memory_percentage < 70 ? 'status-good' : ($memory_percentage < 90 ? 'status-warning' : 'status-critical'),
            'status_icon' => $memory_percentage < 70 ? 'yes-alt' : ($memory_percentage < 90 ? 'warning' : 'dismiss'),
            'status_text' => $memory_percentage < 70 ? __('Good', 'dailybuddy') : ($memory_percentage < 90 ? __('Monitor', 'dailybuddy') : __('Critical', 'dailybuddy')),
        );

        // MySQL Version
        $mysql_version = $wpdb->db_version();
        $is_mysql = stripos($wpdb->db_server_info(), 'MariaDB') === false;

        $info['mysql'] = array(
            'version' => $mysql_version,
            'type' => $is_mysql ? 'MySQL' : 'MariaDB',
            'status' => 'good',
            'status_class' => 'status-good',
            'status_icon' => 'yes-alt',
            'status_text' => __('Good', 'dailybuddy'),
        );

        // WordPress Version
        $wp_version = get_bloginfo('version');
        $update_core = get_site_transient('update_core');
        $update_available = false;
        $latest_version = $wp_version;

        if ($update_core && isset($update_core->updates[0])) {
            $latest_version = $update_core->updates[0]->version;
            $update_available = version_compare($wp_version, $latest_version, '<');
        }

        $info['wordpress'] = array(
            'version' => $wp_version,
            'latest_version' => $latest_version,
            'update_available' => $update_available,
            'status' => $update_available ? 'warning' : 'good',
            'status_class' => $update_available ? 'status-warning' : 'status-good',
            'status_icon' => $update_available ? 'warning' : 'yes-alt',
            'status_text' => $update_available ? __('Update Available', 'dailybuddy') : __('Up to Date', 'dailybuddy'),
        );

        // Disk Space
        $upload_dir = wp_upload_dir();
        $disk_free = @disk_free_space($upload_dir['basedir']);
        $disk_total = @disk_total_space($upload_dir['basedir']);

        if ($disk_free !== false && $disk_total !== false) {
            $disk_used = $disk_total - $disk_free;
            $disk_percentage = ($disk_used / $disk_total) * 100;

            $info['disk'] = array(
                'free' => $disk_free,
                'free_formatted' => size_format($disk_free),
                'total' => $disk_total,
                'total_formatted' => size_format($disk_total),
                'used' => $disk_used,
                'used_formatted' => size_format($disk_used),
                'percentage' => round($disk_percentage),
                'status' => $disk_percentage < 80 ? 'good' : ($disk_percentage < 95 ? 'warning' : 'critical'),
                'status_class' => $disk_percentage < 80 ? 'status-good' : ($disk_percentage < 95 ? 'status-warning' : 'status-critical'),
                'status_icon' => $disk_percentage < 80 ? 'yes-alt' : ($disk_percentage < 95 ? 'warning' : 'dismiss'),
                'status_text' => $disk_percentage < 80 ? __('Good', 'dailybuddy') : ($disk_percentage < 95 ? __('Monitor', 'dailybuddy') : __('Critical', 'dailybuddy')),
            );
        } else {
            $info['disk'] = array(
                'free_formatted' => __('Unknown', 'dailybuddy'),
                'total_formatted' => __('Unknown', 'dailybuddy'),
                'used_formatted' => __('Unknown', 'dailybuddy'),
                'percentage' => 0,
                'status' => 'good',
                'status_class' => 'status-good',
                'status_icon' => 'yes-alt',
                'status_text' => __('Unknown', 'dailybuddy'),
            );
        }

        $dailybuddy_server_software_raw = isset($_SERVER['SERVER_SOFTWARE'])
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw server value, unslashed and sanitized below
            ? $_SERVER['SERVER_SOFTWARE']
            : __('Unknown', 'dailybuddy');

        $dailybuddy_server_software = sanitize_text_field(
            wp_unslash($dailybuddy_server_software_raw)
        );

        $info['server'] = array(
            'software'      => $dailybuddy_server_software,
            'os'            => sanitize_text_field(PHP_OS),
            'max_upload'    => size_format(wp_max_upload_size()),
            'max_execution' => sanitize_text_field((string) ini_get('max_execution_time')),
        );


        return $info;
    }

    /**
     * Calculate overall health status
     */
    private function calculate_health_status($server_info)
    {
        $critical_count = 0;
        $warning_count = 0;

        // Check each component
        foreach ($server_info as $key => $component) {
            if ($key === 'server') continue; // Skip server info

            if (isset($component['status'])) {
                if ($component['status'] === 'critical') {
                    $critical_count++;
                } elseif ($component['status'] === 'warning') {
                    $warning_count++;
                }
            }
        }

        if ($critical_count > 0) {
            return array(
                'status' => 'critical',
                'icon' => 'warning',
                'title' => __('Server Health: Critical', 'dailybuddy'),
                'message' => sprintf(
                    // translators: %d is the number of critical issues detected
                    __('%d critical issue(s) detected. Please check the details below.', 'dailybuddy'),
                    $critical_count
                ),
            );
        } elseif ($warning_count > 0) {
            return array(
                'status' => 'warning',
                'icon' => 'info',
                'title' => __('Server Health: Good (Improvements Possible)', 'dailybuddy'),
                'message' => sprintf(
                    // translators: %d is the number of optimization recommendations
                    __('%d recommendation(s) for optimization. Your site is running fine.', 'dailybuddy'),
                    $warning_count
                ),
            );
        } else {
            return array(
                'status' => 'good',
                'icon' => 'yes-alt',
                'title' => __('Server Health: Excellent', 'dailybuddy'),
                'message' => __('All systems are running optimally. Great job!', 'dailybuddy'),
            );
        }
    }
}

// Initialize module
new WP_Dailybuddy_Server_Performance_Widget();

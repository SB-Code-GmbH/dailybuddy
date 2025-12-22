<?php

/**
 * Module: User Activity Dashboard Widget
 * 
 * Shows currently online users and last activity
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_User_Activity_Widget
{
    private $online_threshold = 300; // 5 minutes in seconds

    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));

        // Track user activity
        add_action('admin_init', array($this, 'track_user_activity'));
        add_action('wp_ajax_dailybuddy_refresh_users', array($this, 'ajax_refresh_users'));
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_user_activity',
            __('User Activity', 'dailybuddy'),
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
        #dailybuddy_user_activity .hndle::before {
            content: '\\f307';
            font-family: dashicons;
            margin-right: 12px;
            font-size: 20px;
            color: #000;
        }

        #dailybuddy_user_activity .user-activity-container {
            margin: 15px 0;
        }
        
        #dailybuddy_user_activity .activity-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        
        #dailybuddy_user_activity .stat-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #2271b1;
            text-align: center;
        }
        
        #dailybuddy_user_activity .stat-box.online {
            border-left-color: #00a32a;
            background: linear-gradient(135deg, #f8f9fa 0%, #f0fff4 100%);
        }
        
        #dailybuddy_user_activity .stat-box.idle {
            border-left-color: #f0b849;
            background: linear-gradient(135deg, #f8f9fa 0%, #fffbf0 100%);
        }
        
        #dailybuddy_user_activity .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1d2327;
            line-height: 1;
            margin-bottom: 4px;
        }
        
        #dailybuddy_user_activity .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #646970;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        #dailybuddy_user_activity .users-section {
            margin-top: 15px;
        }
        
        #dailybuddy_user_activity .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f0f1;
        }
        
        #dailybuddy_user_activity .section-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        
        #dailybuddy_user_activity .section-title .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        #dailybuddy_user_activity .refresh-btn {
            padding: 4px 10px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 3px;
            font-size: 11px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            color: #2271b1;
            transition: all 0.2s ease;
        }
        
        #dailybuddy_user_activity .refresh-btn:hover {
            background: #f0f0f1;
            border-color: #2271b1;
        }
        
        #dailybuddy_user_activity .refresh-btn .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        
        #dailybuddy_user_activity .refresh-btn.loading .dashicons {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        #dailybuddy_user_activity .user-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        #dailybuddy_user_activity .user-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        #dailybuddy_user_activity .user-item:hover {
            border-color: #2271b1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        #dailybuddy_user_activity .user-avatar {
            flex-shrink: 0;
            position: relative;
        }
        
        #dailybuddy_user_activity .user-avatar img {
            border-radius: 50%;
            display: block;
        }
        
        #dailybuddy_user_activity .online-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
        }
        
        #dailybuddy_user_activity .online-indicator.online {
            background: #00a32a;
            box-shadow: 0 0 4px rgba(0, 163, 42, 0.5);
        }
        
        #dailybuddy_user_activity .online-indicator.idle {
            background: #f0b849;
        }
        
        #dailybuddy_user_activity .online-indicator.offline {
            background: #8c8f94;
        }
        
        #dailybuddy_user_activity .user-info {
            flex: 1;
            min-width: 0;
        }
        
        #dailybuddy_user_activity .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            margin: 0 0 2px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        #dailybuddy_user_activity .user-name a {
            color: #1d2327;
            text-decoration: none;
        }
        
        #dailybuddy_user_activity .user-name a:hover {
            color: #2271b1;
        }
        
        #dailybuddy_user_activity .user-role {
            display: inline-block;
            padding: 2px 6px;
            background: #f0f0f1;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        #dailybuddy_user_activity .user-meta {
            font-size: 12px;
            color: #646970;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        #dailybuddy_user_activity .user-meta .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        
        #dailybuddy_user_activity .user-status {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }
        
        #dailybuddy_user_activity .status-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        #dailybuddy_user_activity .status-badge.online {
            background: #e6f7ec;
            color: #00a32a;
        }
        
        #dailybuddy_user_activity .status-badge.idle {
            background: #fff7e6;
            color: #f0b849;
        }
        
        #dailybuddy_user_activity .status-badge.offline {
            background: #f0f0f1;
            color: #646970;
        }
        
        #dailybuddy_user_activity .status-time {
            font-size: 11px;
            color: #8c8f94;
        }
        
        #dailybuddy_user_activity .no-users {
            text-align: center;
            padding: 30px 20px;
            color: #646970;
        }
        
        #dailybuddy_user_activity .no-users .dashicons {
            font-size: 36px;
            width: 36px;
            height: 36px;
            opacity: 0.3;
            margin-bottom: 8px;
        }
        
        #dailybuddy_user_activity .tab-nav {
            display: flex;
            gap: 5px;
            margin-bottom: 15px;
            border-bottom: 1px solid #dcdcde;
        }
        
        #dailybuddy_user_activity .tab-btn {
            padding: 8px 16px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            font-size: 12px;
            font-weight: 600;
            color: #646970;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        #dailybuddy_user_activity .tab-btn:hover {
            color: #2271b1;
        }
        
        #dailybuddy_user_activity .tab-btn.active {
            color: #2271b1;
            border-bottom-color: #2271b1;
        }
        
        #dailybuddy_user_activity .tab-content {
            display: none;
        }
        
        #dailybuddy_user_activity .tab-content.active {
            display: block;
        }
        
        @media screen and (max-width: 782px) {
            #dailybuddy_user_activity .activity-stats {
                grid-template-columns: 1fr;
            }
        }
        ";

        wp_add_inline_style('dashboard', $css);

        // Enqueue JavaScript
        wp_enqueue_script(
            'dailybuddy-user-activity',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/user-activity/assets/script.js',
            array('jquery'),
            DAILYBUDDY_VERSION,
            true
        );

        wp_localize_script(
            'dailybuddy-user-activity',
            'wpToolboxUserActivity',
            array(
                'nonce'   => wp_create_nonce('dailybuddy_user_activity_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
            )
        );
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        $users_data = $this->get_users_activity();
?>
        <div class="user-activity-container">

            <!-- Activity Stats -->
            <div class="activity-stats">
                <div class="stat-box online">
                    <div class="stat-value"><?php echo esc_html($users_data['stats']['online']); ?></div>
                    <div class="stat-label"><?php esc_html_e('Online Now', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-box idle">
                    <div class="stat-value"><?php echo esc_html($users_data['stats']['idle']); ?></div>
                    <div class="stat-label"><?php esc_html_e('Idle (15m)', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo esc_html($users_data['stats']['total']); ?></div>
                    <div class="stat-label"><?php esc_html_e('Total Users', 'dailybuddy'); ?></div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="online">
                    <?php
                    printf(
                        // translators: %d is the number of online users
                        esc_html__('Online (%d)', 'dailybuddy'),
                        intval($users_data['stats']['online'])
                    );
                    ?>
                </button>
                <button class="tab-btn" data-tab="all">
                    <?php
                    printf(
                        // translators: %d is the total number of users
                        esc_html__('All Users (%d)', 'dailybuddy'),
                        intval($users_data['stats']['total'])
                    );
                    ?>
                </button>
            </div>

            <!-- Online Users Tab -->
            <div class="tab-content active" data-tab-content="online">
                <div class="users-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Currently Online', 'dailybuddy'); ?>
                        </h4>
                        <button class="refresh-btn" id="refresh-users-btn">
                            <span class="dashicons dashicons-update"></span>
                            <span><?php esc_html_e('Refresh', 'dailybuddy'); ?></span>
                        </button>
                    </div>
                    <div class="user-list" id="online-users-list">
                        <?php if (!empty($users_data['online'])) : ?>
                            <?php foreach ($users_data['online'] as $user) : ?>
                                <?php $this->render_user_item($user); ?>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="no-users">
                                <span class="dashicons dashicons-admin-users"></span>
                                <p><?php esc_html_e('No users online right now.', 'dailybuddy'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- All Users Tab -->
            <div class="tab-content" data-tab-content="all">
                <div class="users-section">
                    <div class="section-header">
                        <h4 class="section-title">
                            <span class="dashicons dashicons-groups"></span>
                            <?php esc_html_e('All Users', 'dailybuddy'); ?>
                        </h4>
                    </div>
                    <div class="user-list" id="all-users-list">
                        <?php foreach ($users_data['all'] as $user) : ?>
                            <?php $this->render_user_item($user); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    <?php
    }

    /**
     * Render a single user item
     */
    private function render_user_item($user_data)
    {
        $user = $user_data['user'];
        $status = $user_data['status'];
        $last_seen = $user_data['last_seen'];
    ?>
        <div class="user-item">
            <div class="user-avatar">
                <?php echo get_avatar($user->ID, 40); ?>
                <span class="online-indicator <?php echo esc_attr($status); ?>"></span>
            </div>
            <div class="user-info">
                <h5 class="user-name">
                    <a href="<?php echo esc_url(get_edit_user_link($user->ID)); ?>">
                        <?php echo esc_html($user->display_name); ?>
                    </a>
                    <span class="user-role"><?php echo esc_html($user_data['role_label']); ?></span>
                </h5>
                <div class="user-meta">
                    <span class="dashicons dashicons-clock"></span>
                    <?php echo esc_html($user_data['activity_text']); ?>
                </div>
            </div>
            <div class="user-status">
                <span class="status-badge <?php echo esc_attr($status); ?>">
                    <?php echo esc_html($user_data['status_text']); ?>
                </span>
                <?php if ($status !== 'online') : ?>
                    <span class="status-time"><?php echo esc_html($user_data['time_ago']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get users activity data
     */
    private function get_users_activity()
    {
        $query = new WP_User_Query(array(
            'orderby'   => 'meta_value_num',

            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ordering by meta_value is acceptable here for last activity widget.
            'meta_key'  => 'dailybuddy_last_activity',

            'meta_type' => 'NUMERIC',
            'order'     => 'DESC',
            'fields'    => 'all',
        ));

        $users = $query->get_results();

        $current_time = current_time('timestamp');
        $online_users = array();
        $all_users = array();
        $stats = array(
            'online' => 0,
            'idle' => 0,
            'total' => count($users),
        );

        foreach ($users as $user) {
            $last_activity = get_user_meta($user->ID, 'dailybuddy_last_activity', true);
            $time_diff = $last_activity ? ($current_time - $last_activity) : PHP_INT_MAX;

            // Determine status
            $status = 'offline';
            $status_text = __('Offline', 'dailybuddy');

            if ($time_diff <= $this->online_threshold) {
                $status = 'online';
                $status_text = __('Online', 'dailybuddy');
                $stats['online']++;
            } elseif ($time_diff <= 900) { // 15 minutes
                $status = 'idle';
                $status_text = __('Idle', 'dailybuddy');
                $stats['idle']++;
            }

            // Get user role
            $user_roles = $user->roles;
            $role = !empty($user_roles) ? $user_roles[0] : 'subscriber';
            $role_obj = get_role($role);
            $role_label = ucfirst($role);

            $user_data = array(
                'user' => $user,
                'status' => $status,
                'status_text' => $status_text,
                'last_seen' => $last_activity,
                'time_diff' => $time_diff,
                'time_ago' => $last_activity ? human_time_diff($last_activity, $current_time) . ' ' . __('ago', 'dailybuddy') : __('Never', 'dailybuddy'),
                'role_label' => $role_label,
                'activity_text' => (
                    $status === 'online'
                    ? __('Active now', 'dailybuddy')
                    : (
                        $last_activity
                        ? (
                            sprintf(
                                // translators: %s is a human-readable time difference (e.g., "2 hours")
                                __('Last seen %s ago', 'dailybuddy'),
                                human_time_diff($last_activity, $current_time)
                            )
                        )
                        : __('Never logged in', 'dailybuddy')
                    )
                ),
            );

            $all_users[] = $user_data;

            if ($status === 'online' || $status === 'idle') {
                $online_users[] = $user_data;
            }
        }

        return array(
            'online' => $online_users,
            'all' => $all_users,
            'stats' => $stats,
        );
    }

    /**
     * Track user activity
     */
    public function track_user_activity()
    {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'dailybuddy_last_activity', current_time('timestamp'));
        }
    }

    /**
     * AJAX: Refresh users list
     */
    public function ajax_refresh_users()
    {
        check_ajax_referer('dailybuddy_user_activity_nonce', 'nonce');

        $users_data = $this->get_users_activity();

        ob_start();
        if (!empty($users_data['online'])) {
            foreach ($users_data['online'] as $user) {
                $this->render_user_item($user);
            }
        } else {
        ?>
            <div class="no-users">
                <span class="dashicons dashicons-admin-users"></span>
                <p><?php esc_html_e('No users online right now.', 'dailybuddy'); ?></p>
            </div>
<?php
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'stats' => $users_data['stats'],
        ));
    }
}

// Initialize module
new Dailybuddy_User_Activity_Widget();

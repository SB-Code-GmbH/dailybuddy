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

        wp_enqueue_style(
            'user-activity',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/user-activity/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );

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

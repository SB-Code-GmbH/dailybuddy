<?php

/**
 * Module: Quick Stats Dashboard Widget (Kompakte Version)
 * 
 * Displays key website statistics at a glance
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Quick_Stats_Widget
{

    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_quick_stats',
            __('Quick Stats', 'dailybuddy'),
            array($this, 'render_widget')
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
        #dailybuddy_quick_stats .hndle::before {
            content: '\\f185';
            font-family: dashicons;
            margin-right: 12px;
            font-size: 20px;
            color: #000;
        }

        #dailybuddy_quick_stats .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }

        #dailybuddy_quick_stats .quick-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            margin-top: 8px;
        }
        
        #dailybuddy_quick_stats .stat-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 10px;
            border-left: 3px solid #2271b1;
            transition: all 0.3s ease;
        }
        
        #dailybuddy_quick_stats .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        #dailybuddy_quick_stats .stat-card.posts { border-left-color: #2271b1; }
        #dailybuddy_quick_stats .stat-card.pages { border-left-color: #00a32a; }
        #dailybuddy_quick_stats .stat-card.comments { border-left-color: #d63638; }
        #dailybuddy_quick_stats .stat-card.users { border-left-color: #8c44b3; }
        #dailybuddy_quick_stats .stat-card.media { border-left-color: #f0b849; }
        
        #dailybuddy_quick_stats .stat-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
        }
        
        #dailybuddy_quick_stats .stat-icon {
            font-size: 18px;
            opacity: 0.7;
        }
        
        #dailybuddy_quick_stats .stat-title {
            font-size: 11px;
            font-weight: 600;
            color: #1d2327;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        #dailybuddy_quick_stats .stat-items {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        #dailybuddy_quick_stats .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
        }
        
        #dailybuddy_quick_stats .stat-label {
            font-size: 11px;
            color: #646970;
        }
        
        #dailybuddy_quick_stats .stat-value {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
        }
        
        #dailybuddy_quick_stats .stat-value.highlight {
            color: #d63638;
        }
        
        #dailybuddy_quick_stats .stat-divider {
            height: 1px;
            background: #dcdcde;
            margin: 2px 0;
        }
        
        #dailybuddy_quick_stats .storage-bar {
            margin-top: 6px;
            height: 6px;
            background: #dcdcde;
            border-radius: 3px;
            overflow: hidden;
        }
        
        #dailybuddy_quick_stats .storage-fill {
            height: 100%;
            background: linear-gradient(90deg, #00a32a 0%, #f0b849 70%, #d63638 100%);
            transition: width 0.3s ease;
        }
        
        #dailybuddy_quick_stats .storage-text {
            font-size: 10px;
            color: #646970;
            margin-top: 3px;
        }
        ";

        wp_add_inline_style('dashboard', $css);
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        // Get statistics
        $stats = $this->get_statistics();

?>
        <div class="quick-stats-grid">

            <!-- Posts Card -->
            <div class="stat-card posts">
                <div class="stat-header">
                    <span class="stat-icon dashicons dashicons-admin-post"></span>
                    <div class="stat-title"><?php esc_html_e('Posts', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Published', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['posts']['publish']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Draft', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['posts']['draft']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Scheduled', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['posts']['future']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Pages Card -->
            <div class="stat-card pages">
                <div class="stat-header">
                    <span class="stat-icon dashicons dashicons-admin-page"></span>
                    <div class="stat-title"><?php esc_html_e('Pages', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Published', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['pages']['publish']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Draft', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['pages']['draft']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Comments Card -->
            <div class="stat-card comments">
                <div class="stat-header">
                    <span class="stat-icon dashicons dashicons-admin-comments"></span>
                    <div class="stat-title"><?php esc_html_e('Comments', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Approved', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['comments']['approved']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Pending', 'dailybuddy'); ?></span>
                        <span class="stat-value highlight"><?php echo esc_html($stats['comments']['moderated']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Spam', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['comments']['spam']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Users Card -->
            <div class="stat-card users">
                <div class="stat-header">
                    <span class="stat-icon dashicons dashicons-admin-users"></span>
                    <div class="stat-title"><?php esc_html_e('Users', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Total', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['users']['total']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('This Week', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['users']['this_week']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Media Card -->
            <div class="stat-card media">
                <div class="stat-header">
                    <span class="stat-icon dashicons dashicons-admin-media"></span>
                    <div class="stat-title"><?php esc_html_e('Media', 'dailybuddy'); ?></div>
                </div>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Images', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['media']['images']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Videos', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['media']['videos']); ?></span>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-item">
                        <span class="stat-label"><?php esc_html_e('Total Files', 'dailybuddy'); ?></span>
                        <span class="stat-value"><?php echo esc_html($stats['media']['total']); ?></span>
                    </div>

                    <?php if ($stats['media']['storage_percentage'] !== null) : ?>
                        <div class="storage-bar">
                            <div class="storage-fill" style="width: <?php echo esc_attr($stats['media']['storage_percentage']); ?>%"></div>
                        </div>
                        <div class="storage-text">
                            <?php echo esc_html($stats['media']['storage_used']); ?> / <?php echo esc_html($stats['media']['storage_total']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
<?php
    }

    /**
     * Get all statistics
     */
    private function get_statistics()
    {
        return array(
            'posts'    => $this->get_post_stats(),
            'pages'    => $this->get_page_stats(),
            'comments' => $this->get_comment_stats(),
            'users'    => $this->get_user_stats(),
            'media'    => $this->get_media_stats(),
        );
    }

    /**
     * Get post statistics
     */
    private function get_post_stats()
    {
        $counts = wp_count_posts('post');

        return array(
            'publish' => $counts->publish ?? 0,
            'draft'   => $counts->draft ?? 0,
            'future'  => $counts->future ?? 0,
        );
    }

    /**
     * Get page statistics
     */
    private function get_page_stats()
    {
        $counts = wp_count_posts('page');

        return array(
            'publish' => $counts->publish ?? 0,
            'draft'   => $counts->draft ?? 0,
        );
    }

    /**
     * Get comment statistics
     */
    private function get_comment_stats()
    {
        $counts = wp_count_comments();

        return array(
            'approved'  => $counts->approved ?? 0,
            'moderated' => $counts->moderated ?? 0,
            'spam'      => $counts->spam ?? 0,
        );
    }

    /**
     * Get user statistics
     */
    private function get_user_stats()
    {
        $total = count_users();

        $now_gmt   = current_time('timestamp', true); // GMT-Timestamp
        $week_ago  = gmdate('Y-m-d H:i:s', $now_gmt - WEEK_IN_SECONDS);

        $new_users = count(get_users(array(
            'date_query' => array(
                array(
                    'after'     => $week_ago,
                    'inclusive' => true,
                ),
            ),
            'fields' => 'ID',
        )));

        return array(
            'total'     => $total['total_users'] ?? 0,
            'this_week' => $new_users,
        );
    }

    /**
     * Get media statistics
     */
    private function get_media_stats()
    {
        // Bilder zählen (alle image/*)
        $image_counts = wp_count_attachments('image');
        $images       = 0;

        if ($image_counts instanceof stdClass) {
            // Alle MIME-Typen für images aufsummieren
            $images = array_sum((array) $image_counts);
        }

        // Videos zählen (alle video/*)
        $video_counts = wp_count_attachments('video');
        $videos       = 0;

        if ($video_counts instanceof stdClass) {
            $videos = array_sum((array) $video_counts);
        }

        // Gesamt-Attachments
        $total       = wp_count_posts('attachment');
        $total_count = isset($total->inherit) ? (int) $total->inherit : 0;

        // Speicherplatz
        $upload_dir   = wp_upload_dir();
        $storage_data = $this->calculate_directory_size($upload_dir['basedir']);

        return array(
            'images'             => (int) $images,
            'videos'             => (int) $videos,
            'total'              => $total_count,
            'storage_used'       => $storage_data['formatted_size'],
            'storage_bytes'      => $storage_data['size'],
            'storage_total'      => $this->get_disk_total_space(),
            'storage_percentage' => $storage_data['percentage'],
        );
    }

    /**
     * Calculate directory size recursively
     */
    private function calculate_directory_size($directory)
    {
        $size = 0;

        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        return array(
            'size'           => $size,
            'formatted_size' => size_format($size, 2),
            'percentage'     => $this->calculate_storage_percentage($size),
        );
    }

    /**
     * Get total disk space
     */
    private function get_disk_total_space()
    {
        $upload_dir = wp_upload_dir();
        $total = @disk_total_space($upload_dir['basedir']);

        if ($total === false) {
            return null;
        }

        return size_format($total, 2);
    }

    /**
     * Calculate storage percentage
     */
    private function calculate_storage_percentage($used_bytes)
    {
        $upload_dir = wp_upload_dir();
        $total = @disk_total_space($upload_dir['basedir']);

        if ($total === false || $total === 0) {
            return null;
        }

        return min(100, round(($used_bytes / $total) * 100, 1));
    }
}

// Initialize module
new WP_Dailybuddy_Quick_Stats_Widget();

<?php

/**
 * Module: Recent Activity Dashboard Widget
 * 
 * Shows the last 10 changes on the website
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Recent_Activity_Widget
{
    private $activity_limit = 10;

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
            'dailybuddy_recent_activity',
            __('Recent Activity', 'dailybuddy'),
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
        #dailybuddy_recent_activity .hndle::before {
            content: '\\f463';
            font-family: dashicons;
            margin-right: 12px;
            font-size: 20px;
            color: #000;
        }

        #dailybuddy_recent_activity .activity-timeline {
            margin: 15px 0;
            padding: 0;
        }
        
        #dailybuddy_recent_activity .activity-item {
            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f1;
            transition: background 0.2s ease;
        }
        
        #dailybuddy_recent_activity .activity-item:last-child {
            border-bottom: none;
        }
        
        #dailybuddy_recent_activity .activity-item:hover {
            background: #f6f7f7;
            margin: 0 -12px;
            padding: 12px 12px;
        }
        
        #dailybuddy_recent_activity .activity-icon {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 14px;
        }
        
        #dailybuddy_recent_activity .activity-icon.post {
            background: #e7f3ff;
            color: #2271b1;
        }
        
        #dailybuddy_recent_activity .activity-icon.page {
            background: #e6f7ec;
            color: #00a32a;
        }
        
        #dailybuddy_recent_activity .activity-icon.comment {
            background: #ffe7e7;
            color: #d63638;
        }
        
        #dailybuddy_recent_activity .activity-icon.user {
            background: #f3e7ff;
            color: #8c44b3;
        }
        
        #dailybuddy_recent_activity .activity-icon.plugin {
            background: #fff7e6;
            color: #f0b849;
        }
        
        #dailybuddy_recent_activity .activity-icon.theme {
            background: #ffe6f0;
            color: #e91e63;
        }
        
        #dailybuddy_recent_activity .activity-icon.media {
            background: #fff7e6;
            color: #f0b849;
        }
        
        #dailybuddy_recent_activity .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        #dailybuddy_recent_activity .activity-title {
            font-size: 13px;
            font-weight: 500;
            color: #1d2327;
            margin: 0 0 4px 0;
            line-height: 1.4;
        }
        
        #dailybuddy_recent_activity .activity-title a {
            color: #2271b1;
            text-decoration: none;
        }
        
        #dailybuddy_recent_activity .activity-title a:hover {
            color: #135e96;
            text-decoration: underline;
        }
        
        #dailybuddy_recent_activity .activity-meta {
            font-size: 12px;
            color: #646970;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        #dailybuddy_recent_activity .activity-time {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        #dailybuddy_recent_activity .activity-time .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        
        #dailybuddy_recent_activity .activity-author {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        #dailybuddy_recent_activity .activity-author .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        
        #dailybuddy_recent_activity .activity-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #f0f0f1;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        #dailybuddy_recent_activity .activity-badge.new {
            background: #e6f7ec;
            color: #00a32a;
        }
        
        #dailybuddy_recent_activity .activity-badge.updated {
            background: #e7f3ff;
            color: #2271b1;
        }
        
        #dailybuddy_recent_activity .activity-badge.deleted {
            background: #ffe7e7;
            color: #d63638;
        }
        
        #dailybuddy_recent_activity .no-activity {
            text-align: center;
            padding: 40px 20px;
            color: #646970;
        }
        
        #dailybuddy_recent_activity .no-activity .dashicons {
            font-size: 48px;
            width: 48px;
            height: 48px;
            opacity: 0.3;
            margin-bottom: 10px;
        }
        ";

        wp_add_inline_style('dashboard', $css);
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        $activities = $this->get_recent_activities();

        if (empty($activities)) {
?>
            <div class="no-activity">
                <span class="dashicons dashicons-update"></span>
                <p><?php esc_html_e('No recent activity found.', 'dailybuddy'); ?></p>
            </div>
        <?php
            return;
        }

        ?>
        <div class="activity-timeline">
            <?php foreach ($activities as $activity) : ?>
                <div class="activity-item">
                    <div class="activity-icon <?php echo esc_attr($activity['type']); ?>">
                        <span class="dashicons <?php echo esc_attr($activity['icon']); ?>"></span>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?php echo wp_kses_post($activity['title']); ?>
                        </div>
                        <div class="activity-meta">
                            <span class="activity-time">
                                <span class="dashicons dashicons-clock"></span>
                                <?php echo esc_html($activity['time_ago']); ?>
                            </span>
                            <?php if (!empty($activity['author'])) : ?>
                                <span class="activity-author">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <?php echo esc_html($activity['author']); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($activity['badge'])) : ?>
                                <span class="activity-badge <?php echo esc_attr($activity['badge_class']); ?>">
                                    <?php echo esc_html($activity['badge']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
<?php
    }

    /**
     * Get recent activities from various sources
     */
    private function get_recent_activities()
    {
        $activities = array();

        // Get recent posts
        $activities = array_merge($activities, $this->get_post_activities());

        // Get recent pages
        $activities = array_merge($activities, $this->get_page_activities());

        // Get recent comments
        $activities = array_merge($activities, $this->get_comment_activities());

        // Get recent media
        $activities = array_merge($activities, $this->get_media_activities());

        // Get recent users
        $activities = array_merge($activities, $this->get_user_activities());

        // Sort by timestamp (newest first)
        usort($activities, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        // Return only the most recent items
        return array_slice($activities, 0, $this->activity_limit);
    }

    /**
     * Get recent post activities
     */
    private function get_post_activities()
    {
        $activities = array();
        $posts = get_posts(array(
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'post_status'    => array('publish', 'draft', 'future'),
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        foreach ($posts as $post) {
            $author = get_userdata($post->post_author);
            $edit_link = get_edit_post_link($post->ID);

            $badge = '';
            $badge_class = '';
            if ($post->post_status === 'publish') {
                $badge = __('Published', 'dailybuddy');
                $badge_class = 'updated';
            } elseif ($post->post_status === 'draft') {
                $badge = __('Draft', 'dailybuddy');
                $badge_class = 'new';
            } elseif ($post->post_status === 'future') {
                $badge = __('Scheduled', 'dailybuddy');
                $badge_class = 'new';
            }

            $activities[] = array(
                'type'        => 'post',
                'icon'        => 'dashicons-admin-post',
                'title'       => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($edit_link),
                    esc_html($post->post_title ?: __('(No title)', 'dailybuddy'))
                ),
                'time_ago'    => human_time_diff(strtotime($post->post_modified), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy'),
                'timestamp'   => strtotime($post->post_modified),
                'author'      => $author ? $author->display_name : '',
                'badge'       => $badge,
                'badge_class' => $badge_class,
            );
        }

        return $activities;
    }

    /**
     * Get recent page activities
     */
    private function get_page_activities()
    {
        $activities = array();
        $pages = get_posts(array(
            'post_type'      => 'page',
            'posts_per_page' => 3,
            'post_status'    => array('publish', 'draft'),
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        foreach ($pages as $page) {
            $author = get_userdata($page->post_author);
            $edit_link = get_edit_post_link($page->ID);

            $activities[] = array(
                'type'        => 'page',
                'icon'        => 'dashicons-admin-page',
                'title'       => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($edit_link),
                    esc_html($page->post_title ?: __('(No title)', 'dailybuddy'))
                ),
                'time_ago'    => human_time_diff(strtotime($page->post_modified), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy'),
                'timestamp'   => strtotime($page->post_modified),
                'author'      => $author ? $author->display_name : '',
                'badge'       => $page->post_status === 'publish' ? __('Updated', 'dailybuddy') : __('Draft', 'dailybuddy'),
                'badge_class' => 'updated',
            );
        }

        return $activities;
    }

    /**
     * Get recent comment activities
     */
    private function get_comment_activities()
    {
        $activities = array();
        $comments = get_comments(array(
            'number' => 5,
            'status' => 'all',
            'orderby' => 'comment_date',
            'order' => 'DESC',
        ));

        foreach ($comments as $comment) {
            $post = get_post($comment->comment_post_ID);
            $edit_link = admin_url('comment.php?action=editcomment&c=' . $comment->comment_ID);

            $badge = '';
            $badge_class = '';
            if ($comment->comment_approved === '1') {
                $badge = __('Approved', 'dailybuddy');
                $badge_class = 'updated';
            } elseif ($comment->comment_approved === '0') {
                $badge = __('Pending', 'dailybuddy');
                $badge_class = 'new';
            } elseif ($comment->comment_approved === 'spam') {
                $badge = __('Spam', 'dailybuddy');
                $badge_class = 'deleted';
            }

            $activities[] = array(
                'type'        => 'comment',
                'icon'        => 'dashicons-admin-comments',
                'title'       => sprintf(
                    // translators: 1: post URL, 2: post title
                    __('Comment on <a href="%1$s">%2$s</a>', 'dailybuddy'),
                    esc_url($edit_link),
                    esc_html($post ? $post->post_title : __('(unknown post)', 'dailybuddy'))
                ),
                'time_ago'    => human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy'),
                'timestamp'   => strtotime($comment->comment_date),
                'author'      => $comment->comment_author,
                'badge'       => $badge,
                'badge_class' => $badge_class,
            );
        }

        return $activities;
    }

    /**
     * Get recent media activities
     */
    private function get_media_activities()
    {
        $activities = array();
        $media = get_posts(array(
            'post_type'      => 'attachment',
            'posts_per_page' => 3,
            'post_status'    => 'inherit',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));

        foreach ($media as $item) {
            $author = get_userdata($item->post_author);
            $edit_link = get_edit_post_link($item->ID);

            $file_type = '';
            if (strpos($item->post_mime_type, 'image') !== false) {
                $file_type = __('Image', 'dailybuddy');
            } elseif (strpos($item->post_mime_type, 'video') !== false) {
                $file_type = __('Video', 'dailybuddy');
            } elseif (strpos($item->post_mime_type, 'audio') !== false) {
                $file_type = __('Audio', 'dailybuddy');
            } else {
                $file_type = __('File', 'dailybuddy');
            }

            $activities[] = array(
                'type'        => 'media',
                'icon'        => 'dashicons-admin-media',
                'title'       => sprintf(
                    // translators: 1: file type, 2: media edit URL, 3: media title
                    __('%1$s uploaded: <a href="%2$s">%3$s</a>', 'dailybuddy'),
                    esc_html($file_type),
                    esc_url($edit_link),
                    esc_html($item->post_title ?: basename($item->guid))
                ),
                'time_ago'    => human_time_diff(strtotime($item->post_date), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy'),
                'timestamp'   => strtotime($item->post_date),
                'author'      => $author ? $author->display_name : '',
                'badge'       => __('New', 'dailybuddy'),
                'badge_class' => 'new',
            );
        }

        return $activities;
    }

    /**
     * Get recent user activities
     */
    private function get_user_activities()
    {
        $activities = array();
        $users = get_users(array(
            'number'  => 3,
            'orderby' => 'registered',
            'order'   => 'DESC',
        ));

        foreach ($users as $user) {
            $edit_link = get_edit_user_link($user->ID);

            $activities[] = array(
                'type'        => 'user',
                'icon'        => 'dashicons-admin-users',
                'title'       => sprintf(
                    // translators: 1: user edit URL, 2: user display name
                    __('New user registered: <a href="%1$s">%2$s</a>', 'dailybuddy'),
                    esc_url($edit_link),
                    esc_html($user->display_name)
                ),
                'time_ago'    => human_time_diff(strtotime($user->user_registered), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy'),
                'timestamp'   => strtotime($user->user_registered),
                'author'      => '',
                'badge'       => __('New', 'dailybuddy'),
                'badge_class' => 'new',
            );
        }

        return $activities;
    }
}

// Initialize module
new WP_Dailybuddy_Recent_Activity_Widget();

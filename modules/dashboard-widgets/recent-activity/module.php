<?php

/**
 * Module: Recent Activity Dashboard Widget
 * 
 * Shows the last 10 changes on the website
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Recent_Activity_Widget
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

        wp_enqueue_style(
            'recent-activity',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/recent-activity/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
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

        // Get post status based on user capabilities
        $post_status = array('publish');
        if (current_user_can('edit_private_posts')) {
            $post_status[] = 'draft';
            $post_status[] = 'future';
            $post_status[] = 'private';
        }

        $posts = get_posts(array(
            'post_type'      => 'post',
            'posts_per_page' => 5,
            'post_status'    => $post_status,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        foreach ($posts as $post) {
            // Skip if user can't edit this post
            if (!current_user_can('edit_post', $post->ID)) {
                continue;
            }

            $author = get_userdata($post->post_author);
            $edit_link = get_edit_post_link($post->ID);

            // Skip if no edit link available
            if (!$edit_link) {
                continue;
            }

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
                'time_ago'    => $post->post_modified ? human_time_diff(strtotime($post->post_modified), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy') : __('Unknown', 'dailybuddy'),
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

        // Get page status based on user capabilities
        $post_status = array('publish');
        if (current_user_can('edit_pages')) {
            $post_status[] = 'draft';
            $post_status[] = 'private';
        }

        $pages = get_posts(array(
            'post_type'      => 'page',
            'posts_per_page' => 3,
            'post_status'    => $post_status,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        foreach ($pages as $page) {
            // Skip if user can't edit this page
            if (!current_user_can('edit_page', $page->ID)) {
                continue;
            }

            $author = get_userdata($page->post_author);
            $edit_link = get_edit_post_link($page->ID);

            // Skip if no edit link available
            if (!$edit_link) {
                continue;
            }

            $activities[] = array(
                'type'        => 'page',
                'icon'        => 'dashicons-admin-page',
                'title'       => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($edit_link),
                    esc_html($page->post_title ?: __('(No title)', 'dailybuddy'))
                ),
                'time_ago'    => $page->post_modified ? human_time_diff(strtotime($page->post_modified), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy') : __('Unknown', 'dailybuddy'),
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
        // Check if user can moderate comments
        if (!current_user_can('moderate_comments')) {
            return array();
        }

        $activities = array();
        $comments = get_comments(array(
            'number' => 5,
            'status' => 'all',
            'orderby' => 'comment_date',
            'order' => 'DESC',
        ));

        foreach ($comments as $comment) {
            $post = get_post($comment->comment_post_ID);

            // Skip if post doesn't exist
            if (!$post) {
                continue;
            }

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
                    esc_html($post->post_title ?: __('(unknown post)', 'dailybuddy'))
                ),
                'time_ago'    => $comment->comment_date ? human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy') : __('Unknown', 'dailybuddy'),
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
        // Check if user can upload files
        if (!current_user_can('upload_files')) {
            return array();
        }

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

            // Skip if no edit link available
            if (!$edit_link) {
                continue;
            }

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
                'time_ago'    => $item->post_date ? human_time_diff(strtotime($item->post_date), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy') : __('Unknown', 'dailybuddy'),
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
        // Check if user can list users
        if (!current_user_can('list_users')) {
            return array();
        }

        $activities = array();
        $users = get_users(array(
            'number'  => 3,
            'orderby' => 'registered',
            'order'   => 'DESC',
        ));

        foreach ($users as $user) {
            $edit_link = get_edit_user_link($user->ID);

            // Skip if no edit link available
            if (!$edit_link) {
                continue;
            }

            $activities[] = array(
                'type'        => 'user',
                'icon'        => 'dashicons-admin-users',
                'title'       => sprintf(
                    // translators: 1: user edit URL, 2: user display name
                    __('New user registered: <a href="%1$s">%2$s</a>', 'dailybuddy'),
                    esc_url($edit_link),
                    esc_html($user->display_name)
                ),
                'time_ago'    => $user->user_registered ? human_time_diff(strtotime($user->user_registered), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy') : __('Unknown', 'dailybuddy'),
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
new Dailybuddy_Recent_Activity_Widget();

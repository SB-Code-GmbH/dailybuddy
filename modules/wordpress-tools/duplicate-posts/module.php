<?php

/**
 * Module: Duplicate Posts
 * 
 * Adds functionality to duplicate posts and pages
 */

if (! defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Duplicate_Posts
{

    public function __construct()
    {
        add_filter('post_row_actions', array($this, 'add_duplicate_link'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_duplicate_link'), 10, 2);
        add_action('admin_action_duplicate_post', array($this, 'duplicate_post'));
    }

    /**
     * Add duplicate link
     */
    public function add_duplicate_link($actions, $post)
    {
        if (current_user_can('edit_posts')) {
            $url = wp_nonce_url(
                add_query_arg(
                    array(
                        'action' => 'duplicate_post',
                        'post' => $post->ID,
                    ),
                    admin_url('admin.php')
                ),
                'duplicate_post_' . $post->ID
            );

            $actions['duplicate'] = '<a href="' . esc_url($url) . '">' . __('Duplicate', 'dailybuddy') . '</a>';
        }

        return $actions;
    }

    /**
     * Duplicate a post
     */
    public function duplicate_post()
    {
        if (! isset($_GET['post'])) {
            wp_die(esc_html__('No post to duplicate specified.', 'dailybuddy'));
        }

        $post_id = absint($_GET['post']);

        check_admin_referer('duplicate_post_' . $post_id);

        $post = get_post($post_id);

        if (! $post) {
            wp_die(esc_html__('Post not found.', 'dailybuddy'));
        }

        // Create the duplicate
        $new_post = array(
            'post_title'     => $post->post_title . ' (Copy)',
            'post_content'   => $post->post_content,
            'post_status'    => 'draft',
            'post_type'      => $post->post_type,
            'post_author'    => get_current_user_id(),
            'post_excerpt'   => $post->post_excerpt,
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
        );

        $new_post_id = wp_insert_post($new_post);

        if ($new_post_id) {
            // Copy post meta
            $post_meta = get_post_meta($post_id);
            foreach ($post_meta as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }

            // Copy taxonomies
            $taxonomies = get_object_taxonomies($post->post_type);
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $terms, $taxonomy);
            }

            // Redirect to new post
            wp_safe_redirect(
                admin_url('post.php?action=edit&post=' . absint($new_post_id))
            );
            exit;
        } else {
            wp_die(esc_html__('Error duplicating post.', 'dailybuddy'));
        }
    }
}

// Initialize module
new WP_Dailybuddy_Duplicate_Posts();

<?php

/**
 * Module: Content Folders v4.2
 * 
 * COMPLETE REWRITE with all fixes:
 * - Proper column registration for drag handle
 * - Proper column for folder display
 * - Works on Posts, Pages AND Media
 * - Settings page
 * - No "undefined" folders
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Content_Folders
{
    private $active_post_types = array();

    public function __construct()
    {
        // Load settings
        $settings = get_option('dailybuddy_content_folders_settings', array(
            'enable_posts' => true,
            'enable_pages' => true,
            'enable_media' => true,
        ));

        // Build active post types from settings
        $this->active_post_types = array();
        if (!empty($settings['enable_posts'])) {
            $this->active_post_types[] = 'post';
        }
        if (!empty($settings['enable_pages'])) {
            $this->active_post_types[] = 'page';
        }
        if (!empty($settings['enable_media'])) {
            $this->active_post_types[] = 'attachment';
        }

        // Register taxonomies
        add_action('init', array($this, 'register_folder_taxonomies'), 15);

        // Admin columns
        add_action('admin_init', array($this, 'setup_admin_columns'));

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Render sidebar
        add_action('admin_footer', array($this, 'render_folder_sidebar'));

        // AJAX handlers
        add_action('wp_ajax_dailybuddy_get_folder_tree', array($this, 'ajax_get_folder_tree'));
        add_action('wp_ajax_dailybuddy_create_folder', array($this, 'ajax_create_folder'));
        add_action('wp_ajax_dailybuddy_assign_to_folder', array($this, 'ajax_assign_to_folder'));
        add_action('wp_ajax_dailybuddy_delete_folder', array($this, 'ajax_delete_folder'));
        add_action('wp_ajax_dailybuddy_rename_folder', array($this, 'ajax_rename_folder'));
        add_action('wp_ajax_dailybuddy_get_post_folder', array($this, 'ajax_get_post_folder'));
        add_action('wp_ajax_dailybuddy_move_folder', array($this, 'ajax_move_folder'));

        add_action('admin_menu', array($this, 'maybe_add_tools_menu'));

        add_action(
            'load-tools_page_dailybuddy-content-folders',
            array($this, 'redirect_to_cf_settings')
        );
    }

    /**
     * Add "Content Folders" link under Tools when module is active
     */
    public function maybe_add_tools_menu()
    {
        // Nur im Admin und nur für Admins
        if (! is_admin() || ! current_user_can('manage_options')) {
            return;
        }

        // Optional: nur anzeigen, wenn Modulsystem das Modul als aktiv kennt
        if (class_exists('Dailybuddy_Settings')) {
            $modules   = Dailybuddy_Settings::get_modules();
            $module_id = 'wordpress-tools/content-folders'; // <-- ggf. anpassen

            // Standard: wenn kein Eintrag vorhanden ist → aktiv
            $is_module_active = true;
            if (isset($modules[$module_id])) {
                $is_module_active = (bool) $modules[$module_id];
            }

            if (! $is_module_active) {
                return;
            }
        }

        add_submenu_page(
            'tools.php',
            __('Content Folders', 'dailybuddy'),          // Seitentitel
            __('Content Folders', 'dailybuddy'),          // Menü-Text
            'manage_options',
            'dailybuddy-content-folders',                 // Slug
            array($this, 'redirect_to_cf_settings')       // Callback (macht nur Redirect)
        );
    }

    /**
     * Redirect Tools submenu to the Content Folders module settings page
     */
    public function redirect_to_cf_settings()
    {
        $url = add_query_arg(
            array(
                'page'   => 'dailybuddy',
                'view'   => 'settings',
                'module' => 'wordpress-tools/content-folders', // <-- selbe ID wie oben
            ),
            admin_url('admin.php')
        );

        wp_safe_redirect($url);
        exit;
    }

    /**
     * Register taxonomies
     */
    public function register_folder_taxonomies()
    {
        foreach ($this->active_post_types as $post_type) {
            $taxonomy_name = $this->get_taxonomy_name($post_type);

            $labels = array(
                'name'              => __('Folders', 'dailybuddy'),
                'singular_name'     => __('Folder', 'dailybuddy'),
                'menu_name'         => __('Folders', 'dailybuddy'),
            );

            $args = array(
                'labels'            => $labels,
                'hierarchical'      => true,
                'public'            => false,
                'show_ui'           => false,
                'show_admin_column' => false,
                'show_in_menu'      => false,
                'show_in_rest'      => true,
                'query_var'         => false,
                'rewrite'           => false,
            );

            register_taxonomy($taxonomy_name, $post_type, $args);
        }
    }

    /**
     * Get taxonomy name for post type
     */
    private function get_taxonomy_name($post_type)
    {
        $map = array(
            'post'       => 'dailybuddy_post_folder',
            'page'       => 'dailybuddy_page_folder',
            'attachment' => 'dailybuddy_media_folder',
        );

        return isset($map[$post_type]) ? $map[$post_type] : 'dailybuddy_' . $post_type . '_folder';
    }

    /**
     * Setup admin columns
     */
    public function setup_admin_columns()
    {
        foreach ($this->active_post_types as $post_type) {
            // Register columns
            if ($post_type === 'attachment') {
                add_filter('manage_media_columns', array($this, 'add_columns'));
                add_action('manage_media_custom_column', array($this, 'render_column_content'), 10, 2);
            } else {
                add_filter('manage_' . $post_type . '_posts_columns', array($this, 'add_columns'));
                add_action('manage_' . $post_type . '_posts_custom_column', array($this, 'render_column_content'), 10, 2);
            }
        }
    }

    /**
     * Add columns
     */
    public function add_columns($columns)
    {
        $new_columns = array();

        foreach ($columns as $key => $title) {
            // After checkbox, add drag handle
            if ($key === 'cb') {
                $new_columns[$key] = $title;
                $new_columns['dailybuddy_drag'] = '<span class="dashicons dashicons-menu"></span>';
            } else {
                $new_columns[$key] = $title;
            }
        }

        // Add folder column before author
        $position = array_search('author', array_keys($new_columns));
        if ($position !== false) {
            $new_columns = array_slice($new_columns, 0, $position, true) +
                array('dailybuddy_folder' => __('Folder', 'dailybuddy')) +
                array_slice($new_columns, $position, null, true);
        } else {
            $new_columns['dailybuddy_folder'] = __('Folder', 'dailybuddy');
        }

        return $new_columns;
    }

    /**
     * Render column content
     */
    public function render_column_content($column_name, $post_id)
    {
        global $post;

        if ($column_name === 'dailybuddy_drag') {
            echo '<span class="dailybuddy-drag-handle" title="' . esc_attr__('Drag to folder', 'dailybuddy') . '">';
            echo '<span class="dashicons dashicons-menu"></span>';
            echo '</span>';
        }

        if ($column_name === 'dailybuddy_folder') {
            $post_type = $post->post_type;
            $taxonomy = $this->get_taxonomy_name($post_type);
            $terms = wp_get_post_terms($post_id, $taxonomy);

            if (!empty($terms) && !is_wp_error($terms)) {
                $term = $terms[0];
                echo '<a href="#" class="dailybuddy-folder-badge" data-folder-id="' . esc_attr($term->term_id) . '" data-post-id="' . esc_attr($post_id) . '">';
                echo '<span class="dashicons dashicons-category"></span>';
                echo '<span>' . esc_html($term->name) . '</span>';
                echo '</a>';
            } else {
                echo '<span class="dailybuddy-folder-badge unassigned" data-post-id="' . esc_attr($post_id) . '">—</span>';
            }
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        $allowed_hooks = array('edit.php', 'upload.php', 'post.php', 'post-new.php');

        if (!in_array($hook, $allowed_hooks)) {
            return;
        }

        // Get current screen
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, $this->active_post_types)) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'dailybuddy-folders',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.css',
            array(),
            '4.2.0'
        );

        // Add inline CSS for custom colors and settings
        $settings = get_option('dailybuddy_content_folders_settings', array(
            'primary_color' => '#91CE00',
            'accent_color'  => '#478d63',
            'show_counts'   => true,
            'show_icons'    => true,
        ));

        $custom_css = ':root {';
        $custom_css .= '--folder-primary-color: ' . esc_attr($settings['primary_color']) . ';';
        $custom_css .= '--folder-accent-color: ' . esc_attr($settings['accent_color']) . ';';
        $custom_css .= '}';

        // Hide counts if disabled
        if (empty($settings['show_counts'])) {
            $custom_css .= '.folder-count, .total-count { display: none !important; }';
        }

        // Hide icons if disabled
        if (empty($settings['show_icons'])) {
            $custom_css .= '.folder-item .dashicons, .folder-tree-item > span.dashicons { display: none !important; }';
        }

        wp_add_inline_style('dailybuddy-folders', $custom_css);

        wp_register_script(
            'dailybuddy-folders',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wp-i18n'),
            '4.2.0',
            true
        );

        wp_set_script_translations(
            'dailybuddy-folders',
            'dailybuddy',
            DAILYBUDDY_PATH . 'languages'
        );

        wp_enqueue_script('dailybuddy-folders');

        // Localize script
        wp_localize_script('dailybuddy-folders', 'sbToolboxFolders', array(
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dailybuddy_folders'),
            'taxonomy' => $this->get_taxonomy_name($screen->post_type),
            'postType' => $screen->post_type,
            'strings'  => array(
                'newFolder'     => __('New Folder', 'dailybuddy'),
                'allFiles'      => __('All Files', 'dailybuddy'),
                'unassigned'    => __('Unassigned', 'dailybuddy'),
                'createFolder'  => __('Create', 'dailybuddy'),
                'cancel'        => __('Cancel', 'dailybuddy'),
                'folderName'    => __('Folder name...', 'dailybuddy'),
                'confirmDelete' => __('Delete this folder?', 'dailybuddy'),
                'emptyTitle'     => __('This folder is empty', 'dailybuddy'),
                /* translators: %s: folder name */
                'emptyTemplate'  => __('Drag items here to organize them into "%s"', 'dailybuddy'),
                'rootDrop' => __('Drag here to move the folder to the root level', 'dailybuddy'),
            ),
        ));
    }

    /**
     * Render folder sidebar
     */
    public function render_folder_sidebar()
    {
        $screen = get_current_screen();

        // Only on list pages
        if (!$screen || $screen->base !== 'edit' && $screen->base !== 'upload') {
            return;
        }

        if (!in_array($screen->post_type, $this->active_post_types)) {
            return;
        }

        include dirname(__FILE__) . '/templates/sidebar.php';
    }

    /**
     * AJAX: Get folder tree
     */
    public function ajax_get_folder_tree()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid taxonomy', 'dailybuddy')));
        }

        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
            'pad_counts' => false,
        ));

        if (is_wp_error($terms)) {
            wp_send_json_error(array('message' => $terms->get_error_message()));
        }

        $counts = $this->get_folder_counts($terms, $taxonomy);
        $tree   = $this->build_folder_tree($terms, $counts);
        $post_type = $this->get_post_type_from_taxonomy($taxonomy);

        // Count ALL posts including drafts, pending, etc.
        $all_posts_args = array(
            'post_type' => $post_type,
            'numberposts' => -1,
            'fields' => 'ids',
            'post_status' => 'any', // ← WICHTIG: Alle Status!
        );

        // For attachments, use get_posts with specific status
        if ($post_type === 'attachment') {
            $all_posts_args['post_status'] = 'inherit'; // Attachments use 'inherit'
        }

        $all_posts = get_posts($all_posts_args);

        $unassigned_count = 0;
        foreach ($all_posts as $post_id) {
            $post_terms = wp_get_post_terms($post_id, $taxonomy);
            if (empty($post_terms) || is_wp_error($post_terms)) {
                $unassigned_count++;
            }
        }

        wp_send_json_success(array(
            'tree' => $tree,
            'counts' => $counts,
            'total' => count($all_posts),
            'unassigned' => $unassigned_count,
        ));
    }

    /**
     * Get post type from taxonomy
     */
    private function get_post_type_from_taxonomy($taxonomy)
    {
        $map = array(
            'dailybuddy_post_folder' => 'post',
            'dailybuddy_page_folder' => 'page',
            'dailybuddy_media_folder' => 'attachment',
        );

        return isset($map[$taxonomy]) ? $map[$taxonomy] : 'post';
    }

    /**
     * Build folder tree
     */
    private function build_folder_tree($terms, $counts, $parent = 0)
    {
        $branch = array();

        foreach ($terms as $term) {
            if ((int) $term->parent === (int) $parent) {
                $children = $this->build_folder_tree($terms, $counts, $term->term_id);

                $branch[] = array(
                    'id'       => $term->term_id,
                    'name'     => $term->name,
                    'count'    => isset($counts[$term->term_id]) ? (int) $counts[$term->term_id] : 0,
                    'children' => $children,
                );
            }
        }

        return $branch;
    }


    /**
     * Get folder counts
     */
    private function get_folder_counts($terms, $taxonomy)
    {
        $counts    = array();
        $post_type = $this->get_post_type_from_taxonomy($taxonomy);

        foreach ($terms as $term) {
            $args = array(
                'post_type'              => $post_type,
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'post_status'            => ($post_type === 'attachment') ? 'inherit' : 'any',
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,

                // This tax_query is necessary to determine the exact number of posts per folder.
                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                'tax_query'              => array(
                    array(
                        'taxonomy'         => $taxonomy,
                        'field'            => 'term_id',
                        'terms'            => $term->term_id,
                        'include_children' => false,
                    ),
                ),
            );

            $posts = get_posts($args);
            $counts[$term->term_id] = count($posts);
        }

        return $counts;
    }

    /**
     * AJAX: Create folder
     */
    public function ajax_create_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('manage_categories')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $name = isset($_POST['name'])
            ? sanitize_text_field(wp_unslash($_POST['name']))
            : '';

        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        $parent = isset($_POST['parent']) ? intval($_POST['parent']) : 0;

        if (empty($name) || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Missing parameters', 'dailybuddy')));
        }

        $result = wp_insert_term($name, $taxonomy, array(
            'parent' => $parent,
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        $term = get_term($result['term_id'], $taxonomy);

        wp_send_json_success(array(
            'folder' => array(
                'id' => $term->term_id,
                'name' => $term->name,
                'count' => 0,
            ),
        ));
    }

    /**
     * AJAX: Assign to folder
     */
    public function ajax_assign_to_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';


        if (!$post_id || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        if ($folder_id > 0) {
            $result = wp_set_object_terms($post_id, $folder_id, $taxonomy);
            if (is_wp_error($result)) {
                wp_send_json_error(array('message' => $result->get_error_message()));
            }

            $term = get_term($folder_id, $taxonomy);
            $folder_name = $term->name;
        } else {
            wp_delete_object_term_relationships($post_id, $taxonomy);
            $folder_name = '';
        }

        wp_send_json_success(array(
            'folder_name' => $folder_name,
            'folder_id' => $folder_id,
        ));
    }

    /**
     * AJAX: Delete folder
     */
    public function ajax_delete_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('manage_categories')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (!$folder_id || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        $result = wp_delete_term($folder_id, $taxonomy);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('message' => __('Folder deleted', 'dailybuddy')));
    }

    /**
     * AJAX: Rename folder
     */
    public function ajax_rename_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('manage_categories')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $new_name = isset($_POST['name'])
            ? sanitize_text_field(wp_unslash($_POST['name']))
            : '';

        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (!$folder_id || empty($new_name) || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        $result = wp_update_term($folder_id, $taxonomy, array(
            'name' => $new_name,
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'folder_id' => $folder_id,
            'name' => $new_name,
        ));
    }

    /**
     * AJAX: Get post folder (for grid view)
     */
    public function ajax_get_post_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (!$post_id || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        // Get folder terms for this post
        $terms = wp_get_post_terms($post_id, $taxonomy);

        if (is_wp_error($terms)) {
            wp_send_json_error(array('message' => $terms->get_error_message()));
        }

        if (empty($terms)) {
            wp_send_json_success(array(
                'folder_id' => null,
                'folder_name' => null,
            ));
        } else {
            $term = $terms[0]; // Use first term
            wp_send_json_success(array(
                'folder_id' => $term->term_id,
                'folder_name' => $term->name,
            ));
        }
    }

    /**
     * AJAX: Move folder (change parent)
     */
    public function ajax_move_folder()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('manage_categories')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $new_parent_id = isset($_POST['new_parent_id']) ? intval($_POST['new_parent_id']) : 0;
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (!$folder_id || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        // Check if folder would become its own parent (prevent circular reference)
        if ($folder_id === $new_parent_id) {
            wp_send_json_error(array('message' => __('Cannot move folder into itself', 'dailybuddy')));
        }

        // Check if new parent is a child of the folder (prevent circular reference)
        $parent_check = $new_parent_id;
        while ($parent_check > 0) {
            $parent_term = get_term($parent_check, $taxonomy);
            if (is_wp_error($parent_term)) {
                break;
            }
            if ($parent_term->term_id == $folder_id) {
                wp_send_json_error(array('message' => __('Cannot create circular reference', 'dailybuddy')));
            }
            $parent_check = $parent_term->parent;
        }

        // Update folder parent
        $result = wp_update_term($folder_id, $taxonomy, array(
            'parent' => $new_parent_id,
        ));

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('Folder moved successfully', 'dailybuddy'),
            'folder_id' => $folder_id,
            'new_parent_id' => $new_parent_id,
        ));
    }
}

// Initialize
new Dailybuddy_Content_Folders();

/**
 * Settings page render callback for Content Folders
 */
/**
 * Render settings page
 */
function dailybuddy_render_content_folders_settings($module_data)
{
    // Enqueue styles
    if (defined('DAILYBUDDY_URL') && defined('DAILYBUDDY_VERSION')) {
        wp_enqueue_style(
            'dailybuddy-uc',
            DAILYBUDDY_URL . 'assets/css/modul-settings.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }

    // Include template (relative to this module file)
    include __DIR__ . '/templates/settings-page.php';
}

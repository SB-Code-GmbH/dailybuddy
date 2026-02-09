<?php

/**
 * Module: Content Folders v5.0
 * 
 * PERFORMANCE OPTIMIZED VERSION:
 * - URL parameter support (?dailybuddy_folder=123) - without page reload
 * - Batch API for grid labels (one request for all images)
 * - Optimized counts with single GROUP BY query
 * - Transient caching for folder tree
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

        // Enqueue media modal assets on all admin pages
        add_action('admin_enqueue_scripts', array($this, 'enqueue_media_modal_assets'), 20);

        // Enqueue for Elementor Editor
        add_action('elementor/editor/before_enqueue_scripts', array($this, 'enqueue_elementor_media_modal_assets'));

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

        // ★ NEW: Batch API for grid labels
        add_action('wp_ajax_dailybuddy_get_posts_folders_batch', array($this, 'ajax_get_posts_folders_batch'));

        // ★ NEW: Batch assign multiple posts to a folder
        add_action('wp_ajax_dailybuddy_assign_to_folder_batch', array($this, 'ajax_assign_to_folder_batch'));

        // ★ NEW: Auto-assign uploads to folder based on referer URL
        add_action('add_attachment', array($this, 'auto_assign_upload_to_folder'));

        // ★ Server-side folder filtering for list views (posts, pages, media list)
        add_action('pre_get_posts', array($this, 'filter_by_folder_query'));

        // ★ Server-side folder filtering for media grid (AJAX)
        add_filter('ajax_query_attachments_args', array($this, 'filter_media_grid_by_folder'));

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
                // Count ALL post statuses (including 'inherit' for attachments, 'draft', etc.)
                'update_count_callback' => '_update_generic_term_count',
            );

            register_taxonomy($taxonomy_name, $post_type, $args);
        }

        // One-time recount: update_count_callback changed to _update_generic_term_count
        if ( ! get_option( 'dailybuddy_cf_recount_v2' ) ) {
            foreach ( $this->active_post_types as $pt ) {
                $tax = $this->get_taxonomy_name( $pt );
                $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false, 'fields' => 'ids' ) );
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    wp_update_term_count_now( $terms, $tax );
                }
            }
            update_option( 'dailybuddy_cf_recount_v2', true, false );
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
     * ★ Server-side folder filtering for admin list views.
     *
     * Reads ?dailybuddy_folder= from URL and adds a tax_query so
     * WordPress returns only posts in the selected folder.
     * Pagination works automatically because WP_Query handles it.
     */
    public function filter_by_folder_query($query)
    {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $folder_id = isset( $_GET['dailybuddy_folder'] ) ? sanitize_text_field( wp_unslash( $_GET['dailybuddy_folder'] ) ) : '';

        if ( empty( $folder_id ) || 'all' === $folder_id ) {
            return;
        }

        // Determine post type from query
        $post_type = $query->get( 'post_type' );
        if ( empty( $post_type ) ) {
            $post_type = 'post';
        }

        // Skip if post_type is an array (e.g. search across types)
        if ( is_array( $post_type ) ) {
            return;
        }

        // Only filter our active post types
        if ( ! in_array( $post_type, $this->active_post_types, true ) ) {
            return;
        }

        $taxonomy = $this->get_taxonomy_name( $post_type );
        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }

        if ( 'unassigned' === $folder_id ) {
            // Show posts NOT in any folder
            $tax_query = array(
                array(
                    'taxonomy' => $taxonomy,
                    'operator' => 'NOT EXISTS',
                ),
            );
        } else {
            $term_id = absint( $folder_id );
            $term    = get_term( $term_id, $taxonomy );

            if ( ! $term || is_wp_error( $term ) ) {
                return;
            }

            // Include child folder terms for hierarchical filtering
            $term_ids   = array( $term_id );
            $child_terms = get_term_children( $term_id, $taxonomy );
            if ( ! is_wp_error( $child_terms ) && ! empty( $child_terms ) ) {
                $term_ids = array_merge( $term_ids, $child_terms );
            }

            $tax_query = array(
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                ),
            );
        }

        // Merge with existing tax_query if present
        $existing = $query->get( 'tax_query' );
        if ( ! empty( $existing ) ) {
            $tax_query = array_merge( $existing, $tax_query );
        }

        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        $query->set( 'tax_query', $tax_query );
    }

    /**
     * ★ Server-side folder filtering for the media grid (AJAX).
     *
     * Hooks into wp_ajax_query-attachments to filter the backbone
     * media library grid by folder.
     *
     * @param array $query The attachment query args.
     * @return array Modified query args.
     */
    public function filter_media_grid_by_folder( $query )
    {
        // The media grid sends custom props as $_REQUEST['query'][...]
        $folder_id = isset( $_REQUEST['query']['dailybuddy_folder'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ? sanitize_text_field( wp_unslash( $_REQUEST['query']['dailybuddy_folder'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            : '';

        if ( empty( $folder_id ) || 'all' === $folder_id ) {
            return $query;
        }

        $taxonomy = $this->get_taxonomy_name( 'attachment' );

        if ( 'unassigned' === $folder_id ) {
            $query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => $taxonomy,
                    'operator' => 'NOT EXISTS',
                ),
            );
        } else {
            $term_id = absint( $folder_id );
            $term    = get_term( $term_id, $taxonomy );

            if ( ! $term || is_wp_error( $term ) ) {
                return $query;
            }

            $term_ids   = array( $term_id );
            $child_terms = get_term_children( $term_id, $taxonomy );
            if ( ! is_wp_error( $child_terms ) && ! empty( $child_terms ) ) {
                $term_ids = array_merge( $term_ids, $child_terms );
            }

            $query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                ),
            );
        }

        return $query;
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
        $allowed_hooks = array('edit.php', 'upload.php');

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
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $current_folder = isset( $_GET['dailybuddy_folder'] ) ? sanitize_text_field( wp_unslash( $_GET['dailybuddy_folder'] ) ) : 'all';
        $is_media_grid  = ( 'upload.php' === $hook && ( ! isset( $_GET['mode'] ) || 'grid' === $_GET['mode'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        wp_localize_script('dailybuddy-folders', 'sbToolboxFolders', array(
            'ajaxurl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('dailybuddy_folders'),
            'taxonomy'      => $this->get_taxonomy_name($screen->post_type),
            'postType'      => $screen->post_type,
            'currentFolder' => $current_folder,
            'isMediaGrid'   => $is_media_grid,
            'strings'       => array(
                'newFolder'       => __('New Folder', 'dailybuddy'),
                'allFiles'        => __('All Files', 'dailybuddy'),
                'unassigned'      => __('Unassigned', 'dailybuddy'),
                'unassignedFiles' => __('Unassigned Files', 'dailybuddy'),
                'createFolder'    => __('Create', 'dailybuddy'),
                'cancel'          => __('Cancel', 'dailybuddy'),
                'folderName'      => __('Folder name...', 'dailybuddy'),
                'confirmDelete'   => __('Delete this folder?', 'dailybuddy'),
                /* translators: %s: folder name */
                'confirmDeleteNamed' => __('Delete folder "%s"? Items will be unassigned.', 'dailybuddy'),
                'emptyTitle'      => __('This folder is empty', 'dailybuddy'),
                /* translators: %s: folder name */
                'emptyTemplate'   => __('Drag items here to organize them into "%s"', 'dailybuddy'),
                'rootDrop'        => __('Drag here to move the folder to the root level', 'dailybuddy'),
                'enterFolderName' => __('Please enter a folder name.', 'dailybuddy'),
                'errorMoving'     => __('Error moving folder', 'dailybuddy'),
                'errorAssigning'  => __('Error assigning to folder', 'dailybuddy'),
                'errorRenaming'   => __('Error renaming folder', 'dailybuddy'),
                'errorDeleting'   => __('Error deleting folder', 'dailybuddy'),
                'errorCreating'   => __('Error creating folder.', 'dailybuddy'),
                /* translators: %d: number of selected items */
                'itemsCount'      => __('%d items', 'dailybuddy'),
                'oneItem'         => __('1 item', 'dailybuddy'),
                'rename'          => __('Rename', 'dailybuddy'),
                'delete'          => __('Delete', 'dailybuddy'),
                // Media Modal Strings
                'uploadToFolder'  => __('Upload to folder:', 'dailybuddy'),
                'selectFolder'    => __('Select folder...', 'dailybuddy'),
                'noFolder'        => __('No folder', 'dailybuddy'),
                'loading'         => __('Loading...', 'dailybuddy'),
            ),
        ));
    }

    /**
     * Enqueue assets for Media Modal on pages that use it
     */
    public function enqueue_media_modal_assets($hook)
    {
        // Check if media folders are enabled
        $settings = get_option('dailybuddy_content_folders_settings', array(
            'enable_media' => true,
        ));

        if (empty($settings['enable_media'])) {
            return;
        }

        // Skip if main folders script already loaded (on media library page)
        if (wp_script_is('dailybuddy-folders', 'enqueued')) {
            return;
        }

        // Skip if modal script already loaded
        if (wp_script_is('dailybuddy-folders-modal', 'enqueued')) {
            return;
        }

        // Load on ALL admin pages - media modal can be opened anywhere
        // We use a lightweight approach that only activates when modal opens

        // Enqueue CSS
        wp_enqueue_style(
            'dailybuddy-folders-modal',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.css',
            array(),
            '4.3.0'
        );

        // Add custom colors
        $custom_css = ':root {';
        $custom_css .= '--folder-primary-color: ' . esc_attr($settings['primary_color'] ?? '#91CE00') . ';';
        $custom_css .= '--folder-accent-color: ' . esc_attr($settings['accent_color'] ?? '#478d63') . ';';
        $custom_css .= '}';
        wp_add_inline_style('dailybuddy-folders-modal', $custom_css);

        // Enqueue JS
        wp_enqueue_script(
            'dailybuddy-folders-modal',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.js',
            array('jquery'),
            '4.3.0',
            true
        );

        // Localize
        wp_localize_script('dailybuddy-folders-modal', 'sbToolboxFolders', array(
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dailybuddy_folders'),
            'taxonomy' => 'dailybuddy_media_folder',
            'postType' => 'attachment',
            'isMediaModal' => true,
            'strings'  => array(
                'uploadToFolder'  => __('Upload to folder:', 'dailybuddy'),
                'selectFolder'    => __('Select folder...', 'dailybuddy'),
                'noFolder'        => __('No folder', 'dailybuddy'),
                'loading'         => __('Loading...', 'dailybuddy'),
            ),
        ));
    }

    /**
     * Enqueue assets for Elementor Editor Media Modal
     */
    public function enqueue_elementor_media_modal_assets()
    {
        // Check if media folders are enabled
        $settings = get_option('dailybuddy_content_folders_settings', array(
            'enable_media' => true,
        ));

        if (empty($settings['enable_media'])) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'dailybuddy-folders-modal',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.css',
            array(),
            '4.3.1'
        );

        // Add custom colors
        $custom_css = ':root {';
        $custom_css .= '--folder-primary-color: ' . esc_attr($settings['primary_color'] ?? '#91CE00') . ';';
        $custom_css .= '--folder-accent-color: ' . esc_attr($settings['accent_color'] ?? '#478d63') . ';';
        $custom_css .= '}';
        wp_add_inline_style('dailybuddy-folders-modal', $custom_css);

        // Enqueue JS
        wp_enqueue_script(
            'dailybuddy-folders-modal',
            DAILYBUDDY_URL . 'modules/wordpress-tools/content-folders/assets/folders.js',
            array('jquery'),
            '4.3.1',
            true
        );

        // Localize
        wp_localize_script('dailybuddy-folders-modal', 'sbToolboxFolders', array(
            'ajaxurl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dailybuddy_folders'),
            'taxonomy' => 'dailybuddy_media_folder',
            'postType' => 'attachment',
            'isMediaModal' => true,
            'strings'  => array(
                'uploadToFolder'  => __('Upload to folder:', 'dailybuddy'),
                'selectFolder'    => __('Select folder...', 'dailybuddy'),
                'noFolder'        => __('No folder', 'dailybuddy'),
                'loading'         => __('Loading...', 'dailybuddy'),
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

        // Total post count via WordPress API
        $count_obj = wp_count_posts($post_type);
        if ($post_type === 'attachment') {
            $total_count = (int) $count_obj->inherit;
        } else {
            $total_count = 0;
            foreach (get_post_stati(array('show_in_admin_all_list' => true)) as $status) {
                if (isset($count_obj->$status)) {
                    $total_count += (int) $count_obj->$status;
                }
            }
        }

        // Assigned count = sum of all folder counts (posts in at least one folder)
        // Since a post can be in multiple folders, we use WP_Query with tax_query EXISTS
        $assigned_query = new WP_Query(array(
            'post_type'      => $post_type,
            'post_status'    => ($post_type === 'attachment') ? 'inherit' : 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => $taxonomy,
                    'operator' => 'EXISTS',
                ),
            ),
        ));
        $assigned_count   = $assigned_query->found_posts;
        $unassigned_count = max(0, $total_count - $assigned_count);

        wp_send_json_success(array(
            'tree' => $tree,
            'counts' => $counts,
            'total' => $total_count,
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
     * Get folder counts using WordPress term count API
     */
    private function get_folder_counts($terms, $taxonomy)
    {
        $counts = array();

        // WordPress maintains term counts automatically via $term->count
        // Since each post type has its own taxonomy, these counts are accurate
        foreach ($terms as $term) {
            $counts[$term->term_id] = (int) $term->count;
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
     * AJAX: Batch assign multiple posts to a folder
     */
    public function ajax_assign_to_folder_batch()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'dailybuddy')));
        }

        $post_ids = isset($_POST['post_ids']) ? array_map('intval', (array) $_POST['post_ids']) : array();
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (empty($post_ids) || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        // Limit to prevent abuse
        $post_ids = array_slice($post_ids, 0, 200);

        $folder_name = '';

        foreach ($post_ids as $post_id) {
            if ($folder_id > 0) {
                wp_set_object_terms($post_id, $folder_id, $taxonomy);
            } else {
                wp_delete_object_term_relationships($post_id, $taxonomy);
            }
        }

        if ($folder_id > 0) {
            $term = get_term($folder_id, $taxonomy);
            if ($term && !is_wp_error($term)) {
                $folder_name = $term->name;
            }
        }

        wp_send_json_success(array(
            'folder_name' => $folder_name,
            'folder_id' => $folder_id,
            'post_ids' => $post_ids,
            'count' => count($post_ids),
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
     * ★ NEW: AJAX Batch API - Get folders for multiple posts at once
     * This dramatically improves performance for grid view (1 request instead of N)
     */
    public function ajax_get_posts_folders_batch()
    {
        check_ajax_referer('dailybuddy_folders', 'nonce');

        $post_ids = isset($_POST['post_ids']) ? array_map('intval', (array) $_POST['post_ids']) : array();
        $taxonomy = isset($_POST['taxonomy'])
            ? sanitize_text_field(wp_unslash($_POST['taxonomy']))
            : '';

        if (empty($post_ids) || empty($taxonomy)) {
            wp_send_json_error(array('message' => __('Invalid parameters', 'dailybuddy')));
        }

        // Limit to prevent abuse
        $post_ids = array_slice($post_ids, 0, 200);

        // Prime the term cache for all post IDs at once (single query internally)
        update_object_term_cache($post_ids, get_post_type($post_ids[0]));

        // Build response map using WordPress API
        $folders_map = array();
        foreach ($post_ids as $pid) {
            $terms = wp_get_object_terms($pid, $taxonomy, array(
                'fields'  => 'all',
                'number'  => 1,
                'orderby' => 'term_id',
            ));

            if (!empty($terms) && !is_wp_error($terms)) {
                $folders_map[$pid] = array(
                    'folder_id'   => (int) $terms[0]->term_id,
                    'folder_name' => $terms[0]->name,
                );
            } else {
                $folders_map[$pid] = array(
                    'folder_id'   => null,
                    'folder_name' => null,
                );
            }
        }

        wp_send_json_success(array('folders' => $folders_map));
    }

    /**
     * Auto-assign uploaded media to folder based on HTTP referer
     * This is called via add_attachment hook when any media is uploaded
     */
    public function auto_assign_upload_to_folder($attachment_id)
    {
        // Only for media uploads
        $post = get_post($attachment_id);
        if (!$post || $post->post_type !== 'attachment') {
            return;
        }

        // Check HTTP referer for folder parameter
        $referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
        
        if (empty($referer)) {
            return;
        }

        // Parse referer URL for dailybuddy_folder parameter
        $parsed = wp_parse_url($referer);
        if (empty($parsed['query'])) {
            return;
        }

        parse_str($parsed['query'], $query_params);
        
        if (empty($query_params['dailybuddy_folder'])) {
            return;
        }

        $folder_id = $query_params['dailybuddy_folder'];
        
        // Skip 'all' and 'unassigned'
        if ($folder_id === 'all' || $folder_id === 'unassigned') {
            return;
        }

        $folder_id = intval($folder_id);
        if ($folder_id <= 0) {
            return;
        }

        // Verify folder exists
        $term = get_term($folder_id, 'dailybuddy_media_folder');
        if (!$term || is_wp_error($term)) {
            return;
        }

        // Assign to folder
        wp_set_object_terms($attachment_id, $folder_id, 'dailybuddy_media_folder');
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

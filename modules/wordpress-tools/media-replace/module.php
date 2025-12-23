<?php

/**
 * Module: Media Replace
 * 
 * Allows users to replace media files in the Media Library
 * without changing URLs or breaking existing links
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Media_Replace
{
    public function __construct()
    {
        // Add replace button to media row actions
        add_filter('media_row_actions', array($this, 'add_replace_link'), 10, 2);

        // Add replace button in attachment details modal
        add_filter('attachment_fields_to_edit', array($this, 'add_replace_button_to_modal'), 10, 2);

        // Register admin page
        add_action('admin_menu', array($this, 'add_replace_page'), 5);

        // Handle form submission
        add_action('admin_init', array($this, 'handle_form_submission'));

        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Add AJAX handler for file upload
        add_action('wp_ajax_dailybuddy_replace_media', array($this, 'ajax_replace_media'));
    }

    /**
     * Add "Replace" link to media library row actions
     */
    public function add_replace_link($actions, $post)
    {
        if ($post->post_type === 'attachment') {
            $replace_url = admin_url('upload.php?page=dailybuddy-replace-media&attachment_id=' . $post->ID);
            $actions['dailybuddy_replace'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url($replace_url),
                esc_html__('Replace', 'dailybuddy')
            );
        }
        return $actions;
    }

    /**
     * Add replace button to attachment edit modal
     */
    public function add_replace_button_to_modal($fields, $post)
    {
        if ($post->post_type === 'attachment') {
            $replace_url = admin_url('upload.php?page=dailybuddy-replace-media&attachment_id=' . $post->ID);

            $fields['dailybuddy_replace'] = array(
                'label' => __('Replace Media', 'dailybuddy'),
                'input' => 'html',
                'html' => sprintf(
                    '<a href="%s" class="button button-primary">%s</a>',
                    esc_url($replace_url),
                    esc_html__('Replace File', 'dailybuddy')
                ),
                'show_in_edit' => true,
                'show_in_modal' => true,
            );
        }
        return $fields;
    }

    /**
     * Enqueue admin scripts and styles
     * Moved inline script from templates/replace-form.php for WordPress.org compliance
     */
    public function enqueue_admin_assets($hook)
    {
        // Load on our custom page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin routing only
        if (isset($_GET['page']) && $_GET['page'] === 'dailybuddy-replace-media') {
            wp_enqueue_style(
                'dailybuddy-media-replace',
                DAILYBUDDY_URL . 'modules/wordpress-tools/media-replace/assets/style.css',
                array(),
                DAILYBUDDY_VERSION
            );

            wp_enqueue_script(
                'dailybuddy-media-replace',
                DAILYBUDDY_URL . 'modules/wordpress-tools/media-replace/assets/replace-form.js',
                array('jquery'),
                DAILYBUDDY_VERSION,
                true
            );

            // Localize script for translations
            wp_localize_script(
                'dailybuddy-media-replace',
                'dailybuddyMediaReplace',
                array(
                    'noFileChosen' => esc_html__('No file chosen', 'dailybuddy'),
                    'pleaseSelectFile' => esc_html__('Please select a file.', 'dailybuddy'),
                    'confirmReplace' => esc_html__('Are you sure you want to replace this file? This action cannot be undone.', 'dailybuddy'),
                )
            );

            return;
        }

        // Also load on media pages for row actions
        if (!in_array($hook, array('upload.php', 'post.php'))) {
            return;
        }
    }

    /**
     * Handle form submission
     */
    public function handle_form_submission()
    {
        // Check if this is a form submission for our page
        if (! isset($_SERVER['REQUEST_METHOD']) || 'POST' !== $_SERVER['REQUEST_METHOD']) {
            return;
        }

        if (!isset($_POST['dailybuddy_replace_submit'])) {
            return;
        }

        check_admin_referer('dailybuddy_replace_media');

        if (!isset($_POST['attachment_id']) || !isset($_FILES['replace_file'])) {
            wp_die(esc_html__('Missing required data.', 'dailybuddy'));
        }

        $attachment_id = absint($_POST['attachment_id']);

        // Validate and sanitize uploaded file
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated by validate_uploaded_file()
        $validated_file = $this->validate_uploaded_file($_FILES['replace_file']);

        if (is_wp_error($validated_file)) {
            wp_die(esc_html($validated_file->get_error_message()));
        }

        $this->process_file_replacement($attachment_id, $validated_file);
    }

    /**
     * Add hidden admin page for file replacement
     */
    public function add_replace_page()
    {
        $hook = add_submenu_page(
            'upload.php', // Parent: Media menu (for permissions)
            __('Replace Media', 'dailybuddy'),
            __('Replace Media', 'dailybuddy'),
            'upload_files',
            'dailybuddy-replace-media',
            array($this, 'render_replace_page')
        );

        // Set proper page title on this screen
        add_action('load-' . $hook, array($this, 'set_screen_title'));

        // Remove from menu to hide it
        remove_submenu_page('upload.php', 'dailybuddy-replace-media');
    }

    /**
     * Set screen title to avoid strip_tags error
     */
    public function set_screen_title()
    {
        global $title;
        $title = __('Replace Media', 'dailybuddy');
    }

    /**
     * Render the replace page
     */
    public function render_replace_page()
    {
        // Set page title early to avoid strip_tags error
        global $title;
        $title = __('Replace Media', 'dailybuddy');

        // Read-only GET parameter: attachment existence check.
        // Safe without nonce because no data is being changed.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['attachment_id'])) {
            wp_die(esc_html__('No attachment specified.', 'dailybuddy'));
        }

        // Read-only GET parameter: attachment ID for display only.
        // Safe without nonce because no data is being changed.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $attachment_id = isset($_GET['attachment_id'])
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            ? absint(wp_unslash($_GET['attachment_id']))
            : 0;

        $attachment = get_post($attachment_id);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_die(esc_html__('Invalid attachment.', 'dailybuddy'));
        }

        $file_path = get_attached_file($attachment_id);
        $file_url = wp_get_attachment_url($attachment_id);
        $file_type = get_post_mime_type($attachment_id);

        // Safe file size check
        $file_size = '0 Bytes';
        if ($file_path && file_exists($file_path)) {
            $file_size = size_format(filesize($file_path));
        }

        // Safe title
        $attachment_title = $attachment->post_title ? $attachment->post_title : __('Untitled', 'dailybuddy');

        include DAILYBUDDY_PATH . 'modules/wordpress-tools/media-replace/templates/replace-form.php';
    }

    /**
     * Process file replacement
     * 
     * @param int   $attachment_id The attachment ID to replace
     * @param array $uploaded_file Already validated file array from validate_uploaded_file()
     */
    private function process_file_replacement($attachment_id, $uploaded_file)
    {
        // Verify permissions
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to upload files.', 'dailybuddy'));
        }

        // Get current file info
        $old_file_path = get_attached_file($attachment_id);
        $old_file_dir = dirname($old_file_path);
        $old_file_name = basename($old_file_path);

        // Note: Upload error check removed - already validated by validate_uploaded_file()

        // Get file extension
        $new_file_ext = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
        $old_file_ext = strtolower(pathinfo($old_file_name, PATHINFO_EXTENSION));

        // Determine new filename
        $new_file_name = $old_file_name;

        // If extensions differ, update filename
        if ($new_file_ext !== $old_file_ext) {
            $base_name = pathinfo($old_file_name, PATHINFO_FILENAME);
            $new_file_name = $base_name . '.' . $new_file_ext;
        }

        $new_file_path = $old_file_dir . '/' . $new_file_name;

        // Delete old file and thumbnails
        $this->delete_attachment_files($attachment_id);

        // Move uploaded file to correct location
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $upload_overrides = array(
            'test_form' => false,
        );

        // This uses WordPress' secure upload handling instead of move_uploaded_file().
        $movefile = wp_handle_sideload($uploaded_file, $upload_overrides);

        if (! $movefile || isset($movefile['error'])) {
            wp_die(
                esc_html__(
                    'Error moving uploaded file.',
                    'dailybuddy'
                )
            );
        }

        $new_file_path = $movefile['file'];

        // Update attachment metadata
        update_attached_file($attachment_id, $new_file_path);

        // Update mime type using WordPress function
        $filetype = wp_check_filetype($new_file_path);
        $new_mime_type = $filetype['type'];

        wp_update_post(array(
            'ID' => $attachment_id,
            'post_mime_type' => $new_mime_type,
        ));

        // Regenerate thumbnails for images
        if (wp_attachment_is_image($attachment_id)) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $metadata = wp_generate_attachment_metadata($attachment_id, $new_file_path);
            wp_update_attachment_metadata($attachment_id, $metadata);
        }

        // Clear all caches
        clean_post_cache($attachment_id);

        // Clear object cache
        wp_cache_delete($attachment_id, 'post_meta');

        // Force browser cache refresh by updating post modified date
        wp_update_post(array(
            'ID' => $attachment_id,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1)
        ));

        // Redirect back to media library with success message
        wp_safe_redirect(
            add_query_arg(
                array(
                    'dailybuddy_replaced' => '1',
                    'attachment_id' => $attachment_id
                ),
                admin_url('upload.php')
            )
        );
        exit;
    }

    /**
     * Delete attachment file and all its sizes
     */
    private function delete_attachment_files($attachment_id)
    {
        $file_path = get_attached_file($attachment_id);
        $metadata = wp_get_attachment_metadata($attachment_id);

        // Delete main file
        if (file_exists($file_path)) {
            wp_delete_file($file_path);
        }

        // Delete thumbnails
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $upload_dir = wp_upload_dir();
            $base_dir = dirname($file_path);

            foreach ($metadata['sizes'] as $size => $size_data) {
                $thumbnail_path = $base_dir . '/' . $size_data['file'];
                if (file_exists($thumbnail_path)) {
                    wp_delete_file($thumbnail_path);
                }
            }
        }

        // Delete backup sizes (if they exist)
        $backup_sizes = get_post_meta($attachment_id, '_wp_attachment_backup_sizes', true);
        if (is_array($backup_sizes)) {
            $base_dir = dirname($file_path);
            foreach ($backup_sizes as $size_data) {
                if (isset($size_data['file'])) {
                    $backup_path = $base_dir . '/' . $size_data['file'];
                    if (file_exists($backup_path)) {
                        wp_delete_file($backup_path);
                    }
                }
            }
        }
    }

    /**
     * Validate uploaded file from $_FILES
     * 
     * Performs comprehensive security validation on uploaded files:
     * - Validates array structure
     * - Checks for upload errors
     * - Validates file size against WordPress limits
     * - Validates MIME type and file extension
     * - Verifies it's an actual uploaded file
     * 
     * @param array $file File array from $_FILES
     * @return array|WP_Error Sanitized file array or WP_Error on failure
     */
    private function validate_uploaded_file($file)
    {
        // Validate array structure - all required keys must exist
        $required_keys = array('name', 'type', 'tmp_name', 'error', 'size');
        foreach ($required_keys as $key) {
            if (!isset($file[$key])) {
                return new WP_Error(
                    'invalid_file_structure',
                    __('Invalid file upload structure.', 'dailybuddy')
                );
            }
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = array(
                UPLOAD_ERR_INI_SIZE   => __('File exceeds maximum upload size.', 'dailybuddy'),
                UPLOAD_ERR_FORM_SIZE  => __('File exceeds form maximum size.', 'dailybuddy'),
                UPLOAD_ERR_PARTIAL    => __('File was only partially uploaded.', 'dailybuddy'),
                UPLOAD_ERR_NO_FILE    => __('No file was uploaded.', 'dailybuddy'),
                UPLOAD_ERR_NO_TMP_DIR => __('Missing temporary upload directory.', 'dailybuddy'),
                UPLOAD_ERR_CANT_WRITE => __('Failed to write file to disk.', 'dailybuddy'),
                UPLOAD_ERR_EXTENSION  => __('File upload stopped by PHP extension.', 'dailybuddy'),
            );

            $message = isset($error_messages[$file['error']]) 
                ? $error_messages[$file['error']] 
                : __('Unknown upload error.', 'dailybuddy');

            return new WP_Error('upload_error', $message);
        }

        // Validate file size
        $max_size = wp_max_upload_size();
        if ($file['size'] > $max_size) {
            return new WP_Error(
                'file_too_large',
                sprintf(
                    /* translators: %s: Maximum upload size */
                    __('File size exceeds maximum allowed size of %s.', 'dailybuddy'),
                    size_format($max_size)
                )
            );
        }

        // Verify this is an actual uploaded file (security check)
        if (!is_uploaded_file($file['tmp_name'])) {
            return new WP_Error(
                'not_uploaded_file',
                __('Security error: File was not properly uploaded.', 'dailybuddy')
            );
        }

        // Validate MIME type and extension using WordPress's security function
        $filetype = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);

        if (!$filetype['type'] || !$filetype['ext']) {
            return new WP_Error(
                'invalid_file_type',
                __('Sorry, this file type is not permitted for security reasons.', 'dailybuddy')
            );
        }

        // Return sanitized file array with validated MIME type
        return array(
            'name'     => sanitize_file_name($file['name']),
            'type'     => $filetype['type'],
            'tmp_name' => $file['tmp_name'],
            'error'    => $file['error'],
            'size'     => absint($file['size']),
        );
    }

    /**
     * AJAX handler for media replacement
     */
    public function ajax_replace_media()
    {
        check_ajax_referer('dailybuddy_media_replace', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'dailybuddy')));
        }

        if (!isset($_POST['attachment_id']) || !isset($_FILES['file'])) {
            wp_send_json_error(array('message' => __('Missing required data.', 'dailybuddy')));
        }

        $attachment_id = absint($_POST['attachment_id']);

        // Validate and sanitize uploaded file
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated by validate_uploaded_file()
        $validated_file = $this->validate_uploaded_file($_FILES['file']);

        if (is_wp_error($validated_file)) {
            wp_send_json_error(array('message' => $validated_file->get_error_message()));
            return;
        }

        try {
            $this->process_file_replacement($attachment_id, $validated_file);
            wp_send_json_success(array(
                'message' => __('File replaced successfully!', 'dailybuddy'),
                'attachment_id' => $attachment_id,
                'url' => wp_get_attachment_url($attachment_id)
            ));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
}

// Initialize module
new Dailybuddy_Media_Replace();

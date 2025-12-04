<?php

/**
 * Module: Quick Notes Dashboard Widget
 * 
 * Personal notes and todo list for each user
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Quick_Notes_Widget
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'), 10);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // AJAX handlers
        add_action('wp_ajax_dailybuddy_save_note', array($this, 'ajax_save_note'));
        add_action('wp_ajax_dailybuddy_delete_note', array($this, 'ajax_delete_note'));
        add_action('wp_ajax_dailybuddy_toggle_todo', array($this, 'ajax_toggle_todo'));
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_quick_notes',
            __('Quick Notes', 'dailybuddy'),
            array($this, 'render_widget'),
            null,
            null,
            'normal',
            'high'
        );
    }

    /**
     * Enqueue widget assets
     */
    public function enqueue_assets($hook)
    {
        if ($hook !== 'index.php') {
            return;
        }

        $css = "

            #dailybuddy_quick_notes .hndle::before {
                content: '\\f464';
                font-family: dashicons;
                margin-right: 12px;
                font-size: 20px;
                color: #000;
            }

            #dailybuddy_quick_notes .inside {
                margin: 0;
                padding: 0;
            }

            #dailybuddy_quick_notes .notes-container {
                margin: 0;
                max-height: 600px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
            }

            #dailybuddy_quick_notes .note-form {
                position: sticky;
                top: 0;
                background: #f8f9fa;
                padding: 12px;
                border-bottom: 1px solid #dcdcde;
                z-index: 10;
                margin: 0;
            }

            #dailybuddy_quick_notes .notes-list {
                padding: 12px;
                flex: 1;
            }

            #dailybuddy_quick_notes .note-input-group {
                display: flex;
                gap: 8px;
                margin-bottom: 8px;
            }

            #dailybuddy_quick_notes .note-input {
                flex: 1;
                padding: 6px 10px;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                font-size: 13px;
                margin-bottom: 8px;
            }

            #dailybuddy_quick_notes .note-input:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 0 1px #2271b1;
            }

            #dailybuddy_quick_notes .note-textarea {
                width: 100%;
                min-height: 50px;
                max-height: 100px;
                padding: 6px 10px;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                font-size: 13px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
                resize: vertical;
                margin-bottom: 8px;
            }

            #dailybuddy_quick_notes .note-textarea:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 0 1px #2271b1;
            }

            #dailybuddy_quick_notes .note-actions {
                display: flex;
                gap: 8px;
                align-items: center;
                justify-content: space-between;
            }

            #dailybuddy_quick_notes .note-type-toggle {
                display: flex;
                gap: 5px;
                align-items: center;
            }

            #dailybuddy_quick_notes .note-type-toggle label {
                display: flex;
                align-items: center;
                gap: 5px;
                font-size: 12px;
                cursor: pointer;
                margin: 0;
            }

            #dailybuddy_quick_notes .add-note-btn {
                padding: 5px 12px;
                background: #2271b1;
                color: #fff;
                border: none;
                border-radius: 4px;
                font-size: 13px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 5px;
                transition: background 0.2s ease;
                white-space: nowrap;
            }

            #dailybuddy_quick_notes .add-note-btn:hover {
                background: #135e96;
            }

            #dailybuddy_quick_notes .add-note-btn:disabled {
                background: #dcdcde;
                cursor: not-allowed;
            }

            #dailybuddy_quick_notes .notes-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            #dailybuddy_quick_notes .note-item {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 6px;
                padding: 12px;
                transition: all 0.2s ease;
            }

            #dailybuddy_quick_notes .note-item:hover {
                border-color: #2271b1;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }

            #dailybuddy_quick_notes .note-item.todo {
                border-left: 4px solid #f0b849;
            }

            #dailybuddy_quick_notes .note-item.todo.completed {
                opacity: 0.6;
                border-left-color: #00a32a;
            }

            #dailybuddy_quick_notes .note-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }

            #dailybuddy_quick_notes .note-title-row {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }

            #dailybuddy_quick_notes .todo-checkbox::before {
                margin: -0.1rem 0 0 -0.1rem!important;
            }

            #dailybuddy_quick_notes .todo-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }

            #dailybuddy_quick_notes .note-title {
                font-weight: 600;
                font-size: 14px;
                color: #1d2327;
                margin: 0;
            }

            #dailybuddy_quick_notes .note-item.completed .note-title {
                text-decoration: line-through;
                color: #646970;
            }

            #dailybuddy_quick_notes .note-badge {
                padding: 2px 8px;
                background: #f0b849;
                color: #fff;
                border-radius: 3px;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
            }

            #dailybuddy_quick_notes .note-content {
                color: #646970;
                font-size: 13px;
                line-height: 1.5;
                margin: 8px 0;
                white-space: pre-wrap;
                word-wrap: break-word;
            }

            #dailybuddy_quick_notes .note-item.completed .note-content {
                text-decoration: line-through;
            }

            #dailybuddy_quick_notes .note-meta {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 11px;
                color: #8c8f94;
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px solid #f0f0f1;
            }

            #dailybuddy_quick_notes .note-date {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            #dailybuddy_quick_notes .note-delete {
                color: #d63638;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 4px 8px;
                border-radius: 3px;
                transition: background 0.2s ease;
            }

            #dailybuddy_quick_notes .note-delete:hover {
                background: #ffe7e7;
            }

            #dailybuddy_quick_notes .no-notes {
                text-align: center;
                padding: 40px 20px;
                color: #646970;
            }

            #dailybuddy_quick_notes .no-notes .dashicons {
                font-size: 48px;
                width: 48px;
                height: 48px;
                opacity: 0.3;
                margin-bottom: 10px;
            }

            #dailybuddy_quick_notes .loading {
                opacity: 0.5;
                pointer-events: none;
            }

            /* Custom scrollbar */
            #dailybuddy_quick_notes .notes-container::-webkit-scrollbar {
                width: 8px;
            }

            #dailybuddy_quick_notes .notes-container::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            #dailybuddy_quick_notes .notes-container::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 4px;
            }

            #dailybuddy_quick_notes .notes-container::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            #dailybuddy_quick_notes .note-item {
                animation: slideIn 0.3s ease;
            }
            ";

        wp_add_inline_style('dashboard', $css);

        // Enqueue scripts
        wp_enqueue_script(
            'dailybuddy-quick-notes',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/quick-notes/assets/script.js',
            array('jquery'),
            DAILYBUDDY_VERSION,
            true
        );

        wp_localize_script(
            'dailybuddy-quick-notes',
            'wpToolboxNotes',
            array(
                'nonce'   => wp_create_nonce('dailybuddy_notes_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
            )
        );
    }

    /**
     * Render the widget content
     */
    public function render_widget()
    {
        $notes = $this->get_user_notes();
?>
        <div class="notes-container">

            <!-- Add Note Form -->
            <div class="note-form">
                <input
                    type="text"
                    id="note-title"
                    class="note-input"
                    placeholder="<?php esc_attr_e('Note title...', 'dailybuddy'); ?>">

                <textarea
                    id="note-content"
                    class="note-textarea"
                    placeholder="<?php esc_attr_e('Note content (optional)...', 'dailybuddy'); ?>"></textarea>

                <div class="note-actions">
                    <div class="note-type-toggle">
                        <label>
                            <input type="checkbox" id="note-is-todo">
                            <span><?php esc_html_e('Make it a ToDo', 'dailybuddy'); ?></span>
                        </label>
                    </div>
                    <button type="button" class="add-note-btn" id="add-note-btn">
                        <span class="dashicons dashicons-plus"></span>
                        <span><?php esc_html_e('Add Note', 'dailybuddy'); ?></span>
                    </button>
                </div>
            </div>

            <!-- Notes List -->
            <div class="notes-list" id="notes-list">
                <?php if (empty($notes)) : ?>
                    <div class="no-notes">
                        <span class="dashicons dashicons-edit"></span>
                        <p><?php esc_html_e('No notes yet. Add your first note above!', 'dailybuddy'); ?></p>
                    </div>
                <?php else : ?>
                    <?php foreach ($notes as $note) : ?>
                        <?php $this->render_note_item($note); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    <?php
    }

    /**
     * Render a single note item
     */
    private function render_note_item($note)
    {
        $is_todo = !empty($note['is_todo']);
        $is_completed = !empty($note['completed']);
        $classes = array('note-item');

        if ($is_todo) {
            $classes[] = 'todo';
        }
        if ($is_completed) {
            $classes[] = 'completed';
        }
    ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>" data-note-id="<?php echo esc_attr($note['id']); ?>">
            <div class="note-header">
                <div class="note-title-row">
                    <?php if ($is_todo) : ?>
                        <input
                            type="checkbox"
                            class="todo-checkbox"
                            <?php checked($is_completed); ?>
                            data-note-id="<?php echo esc_attr($note['id']); ?>">
                    <?php endif; ?>
                    <h4 class="note-title"><?php echo esc_html($note['title']); ?></h4>
                </div>
                <?php if ($is_todo) : ?>
                    <span class="note-badge"><?php esc_html_e('Todo', 'dailybuddy'); ?></span>
                <?php endif; ?>
            </div>

            <?php if (!empty($note['content'])) : ?>
                <div class="note-content"><?php echo esc_html($note['content']); ?></div>
            <?php endif; ?>

            <div class="note-meta">
                <span class="note-date">
                    <span class="dashicons dashicons-clock"></span>
                    <?php echo esc_html(human_time_diff(strtotime($note['created']), current_time('timestamp')) . ' ' . __('ago', 'dailybuddy')); ?>
                </span>
                <span class="note-delete" data-note-id="<?php echo esc_attr($note['id']); ?>">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Delete', 'dailybuddy'); ?>
                </span>
            </div>
        </div>
<?php
    }

    /**
     * Get user notes from database
     */
    private function get_user_notes()
    {
        $user_id = get_current_user_id();
        $notes = get_user_meta($user_id, 'dailybuddy_notes', true);

        if (!is_array($notes)) {
            return array();
        }

        // Sort by created date (newest first)
        usort($notes, function ($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return $notes;
    }

    /**
     * AJAX: Save new note
     */
    public function ajax_save_note()
    {
        check_ajax_referer('dailybuddy_notes_nonce', 'nonce');

        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));

        $content = sanitize_textarea_field(wp_unslash($_POST['content'] ?? ''));
        $is_todo = !empty($_POST['is_todo']);

        if (empty($title)) {
            wp_send_json_error(array('message' => __('Title is required', 'dailybuddy')));
        }

        $user_id = get_current_user_id();
        $notes = get_user_meta($user_id, 'dailybuddy_notes', true);

        if (!is_array($notes)) {
            $notes = array();
        }

        $note = array(
            'id'        => uniqid('note_'),
            'title'     => $title,
            'content'   => $content,
            'is_todo'   => $is_todo,
            'completed' => false,
            'created'   => current_time('mysql'),
        );

        $notes[] = $note;
        update_user_meta($user_id, 'dailybuddy_notes', $notes);

        ob_start();
        $this->render_note_item($note);
        $html = ob_get_clean();

        wp_send_json_success(array(
            'message' => __('Note saved!', 'dailybuddy'),
            'html'    => $html,
        ));
    }

    /**
     * AJAX: Delete note
     */
    public function ajax_delete_note()
    {
        check_ajax_referer('dailybuddy_notes_nonce', 'nonce');

        $note_id = sanitize_text_field(wp_unslash($_POST['note_id'] ?? ''));

        if (empty($note_id)) {
            wp_send_json_error(array('message' => __('Invalid note ID', 'dailybuddy')));
        }

        $user_id = get_current_user_id();
        $notes = get_user_meta($user_id, 'dailybuddy_notes', true);

        if (!is_array($notes)) {
            wp_send_json_error(array('message' => __('No notes found', 'dailybuddy')));
        }

        $notes = array_filter($notes, function ($note) use ($note_id) {
            return $note['id'] !== $note_id;
        });

        update_user_meta($user_id, 'dailybuddy_notes', array_values($notes));

        wp_send_json_success(array('message' => __('Note deleted!', 'dailybuddy')));
    }

    /**
     * AJAX: Toggle todo completion
     */
    public function ajax_toggle_todo()
    {
        check_ajax_referer('dailybuddy_notes_nonce', 'nonce');

        $note_id = sanitize_text_field(wp_unslash($_POST['note_id'] ?? ''));
        $completed = !empty($_POST['completed']);

        if (empty($note_id)) {
            wp_send_json_error(array('message' => __('Invalid note ID', 'dailybuddy')));
        }

        $user_id = get_current_user_id();
        $notes = get_user_meta($user_id, 'dailybuddy_notes', true);

        if (!is_array($notes)) {
            wp_send_json_error(array('message' => __('No notes found', 'dailybuddy')));
        }

        foreach ($notes as &$note) {
            if ($note['id'] === $note_id) {
                $note['completed'] = $completed;
                break;
            }
        }

        update_user_meta($user_id, 'dailybuddy_notes', $notes);

        wp_send_json_success(array('message' => __('Todo updated!', 'dailybuddy')));
    }
}

// Initialize module
new WP_Dailybuddy_Quick_Notes_Widget();

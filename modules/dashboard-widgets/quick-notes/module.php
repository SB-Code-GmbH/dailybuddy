<?php

/**
 * Module: Quick Notes Dashboard Widget
 * 
 * Personal notes and todo list for each user
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Quick_Notes_Widget
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

        wp_enqueue_style(
            'quick-notes',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/quick-notes/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );

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
new Dailybuddy_Quick_Notes_Widget();

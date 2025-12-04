/**
 * dailybuddy Quick Notes Widget Scripts
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        const $notesList = $('#notes-list');
        const $noteTitle = $('#note-title');
        const $noteContent = $('#note-content');
        const $noteIsTodo = $('#note-is-todo');
        const $addNoteBtn = $('#add-note-btn');

        /**
         * Add new note
         */
        $addNoteBtn.on('click', function (e) {
            e.preventDefault();

            const title = $noteTitle.val().trim();
            const content = $noteContent.val().trim();
            const isTodo = $noteIsTodo.is(':checked');

            if (!title) {
                alert('Please enter a note title.');
                $noteTitle.focus();
                return;
            }

            // Disable button during request
            $addNoteBtn.prop('disabled', true);

            $.ajax({
                url: wpToolboxNotes.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_save_note',
                    nonce: wpToolboxNotes.nonce,
                    title: title,
                    content: content,
                    is_todo: isTodo ? 1 : 0
                },
                success: function (response) {
                    if (response.success) {
                        // Remove "no notes" message if exists
                        $notesList.find('.no-notes').remove();

                        // Add new note to the top of the list
                        $notesList.prepend(response.data.html);

                        // Clear form
                        $noteTitle.val('');
                        $noteContent.val('');
                        $noteIsTodo.prop('checked', false);

                        // Show success feedback
                        showNotification('Note added successfully!', 'success');
                    } else {
                        showNotification(response.data.message || 'Error saving note', 'error');
                    }
                },
                error: function () {
                    showNotification('Error saving note. Please try again.', 'error');
                },
                complete: function () {
                    $addNoteBtn.prop('disabled', false);
                }
            });
        });

        /**
         * Delete note
         */
        $(document).on('click', '.note-delete', function (e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to delete this note?')) {
                return;
            }

            const $noteItem = $(this).closest('.note-item');
            const noteId = $(this).data('note-id');

            $noteItem.addClass('loading');

            $.ajax({
                url: wpToolboxNotes.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_delete_note',
                    nonce: wpToolboxNotes.nonce,
                    note_id: noteId
                },
                success: function (response) {
                    if (response.success) {
                        $noteItem.fadeOut(300, function () {
                            $(this).remove();

                            // Show "no notes" message if list is empty
                            if ($notesList.children('.note-item').length === 0) {
                                $notesList.html(
                                    '<div class="no-notes">' +
                                    '<span class="dashicons dashicons-edit"></span>' +
                                    '<p>No notes yet. Add your first note above!</p>' +
                                    '</div>'
                                );
                            }
                        });

                        showNotification('Note deleted!', 'success');
                    } else {
                        $noteItem.removeClass('loading');
                        showNotification(response.data.message || 'Error deleting note', 'error');
                    }
                },
                error: function () {
                    $noteItem.removeClass('loading');
                    showNotification('Error deleting note. Please try again.', 'error');
                }
            });
        });

        /**
         * Toggle todo completion
         */
        $(document).on('change', '.todo-checkbox', function () {
            const $checkbox = $(this);
            const $noteItem = $checkbox.closest('.note-item');
            const noteId = $checkbox.data('note-id');
            const isCompleted = $checkbox.is(':checked');

            $.ajax({
                url: wpToolboxNotes.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_toggle_todo',
                    nonce: wpToolboxNotes.nonce,
                    note_id: noteId,
                    completed: isCompleted ? 1 : 0
                },
                success: function (response) {
                    if (response.success) {
                        if (isCompleted) {
                            $noteItem.addClass('completed');
                        } else {
                            $noteItem.removeClass('completed');
                        }
                    } else {
                        // Revert checkbox on error
                        $checkbox.prop('checked', !isCompleted);
                        showNotification(response.data.message || 'Error updating todo', 'error');
                    }
                },
                error: function () {
                    // Revert checkbox on error
                    $checkbox.prop('checked', !isCompleted);
                    showNotification('Error updating todo. Please try again.', 'error');
                }
            });
        });

        /**
         * Allow Enter key to add note (Ctrl+Enter for textarea)
         */
        $noteTitle.on('keypress', function (e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $addNoteBtn.trigger('click');
            }
        });

        $noteContent.on('keypress', function (e) {
            if (e.which === 13 && e.ctrlKey) { // Ctrl+Enter
                e.preventDefault();
                $addNoteBtn.trigger('click');
            }
        });

        /**
         * Show notification (using WordPress admin notices style)
         */
        function showNotification(message, type) {
            const $notice = $('<div>', {
                class: 'notice notice-' + type + ' is-dismissible',
                html: '<p>' + message + '</p>'
            });

            // Insert after the main heading
            $('.wrap h1').first().after($notice);

            // Auto-dismiss after 3 seconds
            setTimeout(function () {
                $notice.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }

    });

})(jQuery);

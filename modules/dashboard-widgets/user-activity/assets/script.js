/**
 * dailybuddy User Activity Widget Scripts
 */

(function ($) {
    'use strict';

    $(document).ready(function () {

        /**
         * Tab Switching
         */
        $('.tab-btn').on('click', function () {
            const $btn = $(this);
            const tab = $btn.data('tab');

            // Update active button
            $('.tab-btn').removeClass('active');
            $btn.addClass('active');

            // Update active content
            $('.tab-content').removeClass('active');
            $('[data-tab-content="' + tab + '"]').addClass('active');
        });

        /**
         * Refresh Users
         */
        $('#refresh-users-btn').on('click', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const $list = $('#online-users-list');

            // Disable button and show loading
            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: wpToolboxUserActivity.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_refresh_users',
                    nonce: wpToolboxUserActivity.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Update list
                        $list.html(response.data.html);

                        // Update stats
                        if (response.data.stats) {
                            $('.stat-box.online .stat-value').text(response.data.stats.online);
                            $('.stat-box.idle .stat-value').text(response.data.stats.idle);
                            $('.tab-btn[data-tab="online"]').text('Online (' + response.data.stats.online + ')');
                        }

                        // Show success feedback
                        showNotification('User list refreshed!', 'success');
                    } else {
                        showNotification('Error refreshing users.', 'error');
                    }
                },
                error: function () {
                    showNotification('Error refreshing users. Please try again.', 'error');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        /**
         * Auto-refresh every 30 seconds
         */
        setInterval(function () {
            $('#refresh-users-btn').trigger('click');
        }, 30000);

        /**
         * Show notification
         */
        function showNotification(message, type) {
            const $notice = $('<div>', {
                class: 'notice notice-' + type + ' is-dismissible',
                html: '<p>' + message + '</p>'
            });

            // Insert at top of widget
            $('#dailybuddy_user_activity .inside').prepend($notice);

            // Auto-dismiss after 2 seconds
            setTimeout(function () {
                $notice.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 2000);
        }

    });

})(jQuery);

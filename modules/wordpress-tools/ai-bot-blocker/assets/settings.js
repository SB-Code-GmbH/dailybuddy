/**
 * AI Bot Blocker - Settings Page Script
 * 
 * Handles bot selection, card styling, and copy functionality
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Select all bots
         */
        $('#select-all-bots').on('click', function() {
            $('.bot-card input[type="checkbox"]').prop('checked', true);
            $('.bot-card').addClass('selected');
        });

        /**
         * Deselect all bots
         */
        $('#deselect-all-bots').on('click', function() {
            $('.bot-card input[type="checkbox"]').prop('checked', false);
            $('.bot-card').removeClass('selected');
        });

        /**
         * Update card style on checkbox change
         */
        $('.bot-card input[type="checkbox"]').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.bot-card').addClass('selected');
            } else {
                $(this).closest('.bot-card').removeClass('selected');
            }
        });

        /**
         * Copy legal text to clipboard
         */
        $('.copy-legal-btn').on('click', function() {
            const target = $(this).data('target');
            const textarea = $('#' + target);

            textarea.select();
            document.execCommand('copy');

            const btn = $(this);
            const originalText = btn.html();
            
            // Show success feedback
            btn.html('<span class="dashicons dashicons-yes"></span> ' + dailybuddyAiBotBlocker.copiedText);
            btn.css('color', '#00a32a');

            // Reset after 2 seconds
            setTimeout(function() {
                btn.html(originalText);
                btn.css('color', '');
            }, 2000);
        });
        
    });
    
})(jQuery);

/**
 * Custom Login URL - Settings Page Script
 * 
 * Handles URL copying and form validation
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Copy URL to clipboard
         */
        $('.copy-url-btn').on('click', function() {
            const url = $(this).data('url');
            const btn = $(this);

            // Create temporary input element
            const temp = $('<input>');
            $('body').append(temp);
            temp.val(url).select();
            document.execCommand('copy');
            temp.remove();

            const originalText = btn.html();
            
            // Show success feedback
            btn.html('<span class="dashicons dashicons-yes"></span> ' + dailybuddyCustomLoginUrl.copiedText);
            btn.css('color', '#00a32a');

            // Reset after 2 seconds
            setTimeout(function() {
                btn.html(originalText);
                btn.css('color', '');
            }, 2000);
        });

        /**
         * Validate that login and redirect slugs are different
         */
        $('form.dailybuddy-settings-form').on('submit', function(e) {
            const loginSlug = $('#login_slug').val().trim();
            const redirectSlug = $('#redirect_slug').val().trim();

            // Check if slugs are the same
            if (loginSlug === redirectSlug) {
                e.preventDefault();
                alert(dailybuddyCustomLoginUrl.errorSameSlugs);
                $('#login_slug').focus();
                return false;
            }

            // Check if slugs are empty
            if (loginSlug === '' || redirectSlug === '') {
                e.preventDefault();
                alert(dailybuddyCustomLoginUrl.errorRequiredSlugs);
                return false;
            }
        });
        
    });
    
})(jQuery);

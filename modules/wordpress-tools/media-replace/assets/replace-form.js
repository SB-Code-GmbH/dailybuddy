/**
 * Media Replace - Replace Form Script
 * 
 * Handles file selection display and form validation
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Show selected filename
         */
        $('#replace_file').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $('#file-chosen').text(fileName || dailybuddyMediaReplace.noFileChosen);
        });
        
        /**
         * Form validation
         */
        $('#dailybuddy-replace-form').on('submit', function(e) {
            const fileInput = $('#replace_file')[0];
            
            // Check if file is selected
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert(dailybuddyMediaReplace.pleaseSelectFile);
                return false;
            }
            
            // Confirmation dialog
            if (!confirm(dailybuddyMediaReplace.confirmReplace)) {
                e.preventDefault();
                return false;
            }
            
            // Show progress indicator
            $('#upload-progress').show();
            $('.submit-actions').hide();
        });
        
    });
    
})(jQuery);

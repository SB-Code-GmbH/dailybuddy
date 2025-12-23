/**
 * Under Construction - Admin Bar Toggle Script
 * 
 * Handles the maintenance mode toggle switch in the admin bar
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        var $toggle = $('#dailybuddy-uc-toggle-switch');
        var $statusText = $('#dailybuddy-uc-status');

        if (!$toggle.length) {
            return;
        }

        /**
         * Update switch visual appearance based on checkbox state
         */
        function updateSwitchVisual() {
            var label = $toggle.parent()[0];
            if (!label) return;

            var spans = label.getElementsByTagName('span');
            if (spans.length < 2) return;

            var track = spans[0]; // Background
            var knob = spans[1]; // Round "ball"

            if ($toggle.is(':checked')) {
                track.style.backgroundColor = '#00a32a';
                knob.style.left = '22px';
            } else {
                track.style.backgroundColor = '#8c8f94';
                knob.style.left = '2px';
            }
        }

        /**
         * Update status text
         */
        function updateStatusText(isActive) {
            if (isActive) {
                $statusText
                    .css('color', '#00a32a')
                    .html('✓ ' + dailybuddyUcToggle.activeText);
            } else {
                $statusText
                    .css('color', '#646970')
                    .html('○ ' + dailybuddyUcToggle.inactiveText);
            }
        }

        // Initial state on page load
        updateSwitchVisual();

        /**
         * Handle toggle change
         */
        $toggle.on('change', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var isChecked = $toggle.is(':checked');

            // Optimistic UI - update status text immediately
            updateStatusText(isChecked);

            // Update switch visual
            updateSwitchVisual();

            // AJAX request to save setting
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_toggle_maintenance',
                    enabled: isChecked ? 1 : 0,
                    nonce: dailybuddyUcToggle.nonce
                },
                success: function(response) {
                    if (!response || !response.success) {
                        // Revert on error
                        $toggle.prop('checked', !isChecked);
                        updateSwitchVisual();
                        updateStatusText(!isChecked);
                    }
                },
                error: function() {
                    // Revert on error
                    $toggle.prop('checked', !isChecked);
                    updateSwitchVisual();
                    updateStatusText(!isChecked);
                }
            });
        });

        /**
         * Prevent dropdown from closing when clicking the switch
         */
        $('#dailybuddy-uc-toggle').on('click', function(e) {
            e.stopPropagation();
        });
        
    });
    
})(jQuery);

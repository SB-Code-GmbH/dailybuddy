/**
 * Under Construction - Settings Page Script
 * 
 * Handles tab switching, layout selection, and admin bar updates
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        /**
         * Tab Switching
         */
        $('.dailybuddy-uc-tab').on('click', function() {
            var tab = $(this).data('tab');

            $('#current_tab').val(tab);

            $('.dailybuddy-uc-tab').removeClass('active');
            $(this).addClass('active');

            $('.dailybuddy-uc-tab-content').removeClass('active');
            $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');

            // WICHTIG: CodeMirror refreshen, wenn Design-Tab sichtbar wird
            if (tab === 'design' && window.dailybuddyUcCssEditor && window.dailybuddyUcCssEditor.codemirror) {
                setTimeout(function() {
                    window.dailybuddyUcCssEditor.codemirror.refresh();
                }, 20);
            }
        });

        /**
         * Falls Seite bereits mit aktivem Design-Tab geladen wird (z.B. nach Save)
         */
        if ($('#current_tab').val() === 'design' && window.dailybuddyUcCssEditor && window.dailybuddyUcCssEditor.codemirror) {
            setTimeout(function() {
                window.dailybuddyUcCssEditor.codemirror.refresh();
            }, 20);
        }

        /**
         * Layout Selection
         */
        $('.layout-preview').on('click', function() {
            $('.layout-preview').removeClass('selected');
            $(this).addClass('selected');
        });

        /**
         * Update admin bar on page load if settings were saved
         */
        if (dailybuddyUnderConstruction.settingsSaved) {
            if (window.parent && window.parent.jQuery) {
                var isActive = dailybuddyUnderConstruction.maintenanceActive;
                var $statusText = window.parent.jQuery('#dailybuddy-uc-status');
                var $toggle = window.parent.jQuery('#dailybuddy-uc-toggle-switch');

                if (isActive) {
                    $statusText.css('color', '#00a32a').html('✓ ' + dailybuddyUnderConstruction.activeText);
                    $toggle.prop('checked', true);
                } else {
                    $statusText.css('color', '#646970').html('○ ' + dailybuddyUnderConstruction.inactiveText);
                    $toggle.prop('checked', false);
                }
            }
        }
        
    });
    
})(jQuery);

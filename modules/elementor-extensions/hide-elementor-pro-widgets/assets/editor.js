/**
 * Hide Elementor Pro Widgets - Editor Script
 * 
 * Removes promotion widgets and locked widgets from Elementor panel
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    /**
     * Remove promotion widgets from Elementor panel
     */
    function removePromotionWidgets() {
        // Remove all promotion widgets
        const promotionWidgets = document.querySelectorAll('.elementor-element-wrapper.elementor-element--promotion');
        promotionWidgets.forEach(widget => {
            widget.remove();
        });

        // Remove widgets with lock icons
        const lockedWidgets = document.querySelectorAll('.elementor-element-wrapper:has(.eicon-lock)');
        lockedWidgets.forEach(widget => {
            widget.remove();
        });
    }

    // Run immediately
    removePromotionWidgets();

    // Run again after short delays (for dynamically loaded content)
    setTimeout(removePromotionWidgets, 1000);
    setTimeout(removePromotionWidgets, 3000);

    // Observer for dynamically added content
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                removePromotionWidgets();
            }
        });
    });

    // Observe changes in the widget panel
    const widgetPanel = document.querySelector('#elementor-panel-elements');
    if (widgetPanel) {
        observer.observe(widgetPanel, {
            childList: true,
            subtree: true
        });
    }
});

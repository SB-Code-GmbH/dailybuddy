/**
 * Advanced Tabs Widget Script
 * dailybuddy Plugin
 */

(function ($) {
    'use strict';

    var DailybuddyAdvancedTabs = function ($scope, $) {
        var $tabsWrapper = $scope.find('.dailybuddy-advance-tabs');
        
        if (!$tabsWrapper.length) {
            return;
        }

        var $tabNav = $tabsWrapper.find('.dailybuddy-tabs-nav ul li');
        var $tabContent = $tabsWrapper.find('.dailybuddy-tabs-content > div');
        var tabId = $tabsWrapper.data('tabid');
        var isToggle = $tabsWrapper.data('toggle') === 'yes';
        var defaultActive = $tabsWrapper.data('default-active') === 'yes';
        var scrollOnClick = $tabsWrapper.data('scroll-on-click') === 'yes';
        var scrollSpeed = $tabsWrapper.data('scroll-speed') || 300;
        var customIdOffset = $tabsWrapper.data('custom-id-offset') || 0;

        // Initialize tabs
        function initTabs() {
            // If no active tab and default active is enabled, activate first tab
            if (defaultActive && !$tabNav.hasClass('active') && !$tabNav.hasClass('active-default')) {
                $tabNav.first().addClass('active');
                $tabContent.first().addClass('active');
            }

            // Check for hash in URL
            var hash = window.location.hash;
            if (hash) {
                var $targetTab = $tabNav.filter('[id="' + hash.substring(1) + '"]');
                if ($targetTab.length) {
                    activateTab($targetTab);
                }
            }
        }

        // Activate a tab
        function activateTab($tab) {
            var tabIndex = $tab.data('tab');
            
            // Remove active class from all tabs
            $tabNav.removeClass('active active-default');
            $tabContent.removeClass('active active-default');
            
            // Add active class to clicked tab
            $tab.addClass('active');
            $tab.attr('aria-selected', 'true');
            
            // Show corresponding content
            var $content = $tabContent.eq(tabIndex - 1);
            $content.addClass('active');
            
            // Trigger custom event
            $tabsWrapper.trigger('dailybuddy-advance-tabs:changed', [$tab, $content]);
            
            // Update URL hash
            var tabId = $tab.attr('id');
            if (tabId) {
                if (history.pushState) {
                    history.pushState(null, null, '#' + tabId);
                } else {
                    window.location.hash = tabId;
                }
            }

            // Scroll to tab if enabled
            if (scrollOnClick) {
                $('html, body').animate({
                    scrollTop: $tabsWrapper.offset().top - customIdOffset
                }, scrollSpeed);
            }
        }

        // Tab click handler
        $tabNav.on('click', function (e) {
            e.preventDefault();
            var $this = $(this);
            
            // If toggle is enabled and tab is already active, deactivate it
            if (isToggle && $this.hasClass('active')) {
                $this.removeClass('active');
                $this.attr('aria-selected', 'false');
                var tabIndex = $this.data('tab');
                $tabContent.eq(tabIndex - 1).removeClass('active');
            } else {
                activateTab($this);
            }
        });

        // Keyboard navigation
        $tabNav.on('keydown', function (e) {
            var $this = $(this);
            var $prev = $this.prev();
            var $next = $this.next();
            
            // Arrow Left
            if (e.keyCode === 37 && $prev.length) {
                e.preventDefault();
                $prev.trigger('click').focus();
            }
            
            // Arrow Right
            if (e.keyCode === 39 && $next.length) {
                e.preventDefault();
                $next.trigger('click').focus();
            }
            
            // Enter or Space
            if (e.keyCode === 13 || e.keyCode === 32) {
                e.preventDefault();
                $this.trigger('click');
            }
        });

        // Handle hash change
        $(window).on('hashchange', function () {
            var hash = window.location.hash;
            if (hash) {
                var $targetTab = $tabNav.filter('[id="' + hash.substring(1) + '"]');
                if ($targetTab.length) {
                    activateTab($targetTab);
                }
            }
        });

        // Initialize
        initTabs();
    };

    // Run on Elementor Frontend
    $(window).on('elementor/frontend/init', function () {
        if (elementorFrontend && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/dailybuddy-advanced-tabs.default',
                DailybuddyAdvancedTabs
            );
        }
    });

})(jQuery);

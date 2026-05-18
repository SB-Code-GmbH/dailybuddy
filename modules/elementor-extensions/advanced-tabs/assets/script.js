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

        // ========== HORIZONTAL SCROLL (Pfeile + Shadow-Toggle) ==========
        function initHorizontalScroll() {
            if (!$tabsWrapper.hasClass('dailybuddy-tabs-scrollable')) return;

            var $nav = $tabsWrapper.find('.dailybuddy-tabs-nav').first();
            var $ul = $nav.find('> ul').first();
            var $btnPrev = $nav.find('.dailybuddy-scroll-btn-prev').first();
            var $btnNext = $nav.find('.dailybuddy-scroll-btn-next').first();
            if (!$ul.length) return;

            var ul = $ul[0];

            function updateState() {
                var maxScroll = ul.scrollWidth - ul.clientWidth;
                var hasOverflow = maxScroll > 1;
                var atStart = ul.scrollLeft <= 0;
                var atEnd = ul.scrollLeft >= maxScroll - 1;

                $nav.toggleClass('has-scroll-prev', hasOverflow && !atStart);
                $nav.toggleClass('has-scroll-next', hasOverflow && !atEnd);

                if (!hasOverflow) {
                    if ($btnPrev.length) $btnPrev.prop('hidden', true);
                    if ($btnNext.length) $btnNext.prop('hidden', true);
                    return;
                }

                if ($btnPrev.length) $btnPrev.prop('hidden', atStart);
                if ($btnNext.length) $btnNext.prop('hidden', atEnd);
            }

            function scrollByAmount(direction) {
                var amount = Math.max(120, ul.clientWidth * 0.8);
                ul.scrollBy({ left: direction * amount, behavior: 'smooth' });
            }

            if ($btnPrev.length) {
                $btnPrev.on('click', function (e) {
                    e.preventDefault();
                    scrollByAmount(-1);
                });
            }
            if ($btnNext.length) {
                $btnNext.on('click', function (e) {
                    e.preventDefault();
                    scrollByAmount(1);
                });
            }

            $ul.on('scroll', updateState);
            $(window).on('resize', updateState);

            // Initialer Status, kurze Verzögerung damit Layouts gesetzt sind
            updateState();
            setTimeout(updateState, 200);
        }

        // Initialize
        initTabs();
        initHorizontalScroll();
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

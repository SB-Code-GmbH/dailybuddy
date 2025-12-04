/**
 * dailybuddy Advanced Accordion Widget Script
 */

(function ($) {
    'use strict';

    /**
     * Initialize Advanced Accordion Widget
     */
    var AdvancedAccordion = function ($scope, $) {
        var $accordion = $scope.find('.dailybuddy-accordion');
        
        if (!$accordion.length) {
            return;
        }

        var type = $accordion.data('type');
        var speed = parseInt($accordion.data('speed')) || 300;
        var scrollEnabled = $accordion.data('scroll');
        var scrollOffset = parseInt($accordion.data('offset')) || 0;

        // Handle accordion click
        $accordion.find('.dailybuddy-accordion-title').on('click', function(e) {
            e.preventDefault();
            
            var $item = $(this).closest('.dailybuddy-accordion-item');
            var $content = $item.find('.dailybuddy-accordion-content');
            var isActive = $item.hasClass('active');

            if (type === 'accordion') {
                // Close all other items
                $accordion.find('.dailybuddy-accordion-item.active').not($item).each(function() {
                    var $otherItem = $(this);
                    var $otherContent = $otherItem.find('.dailybuddy-accordion-content');
                    
                    $otherItem.removeClass('active');
                    $otherItem.find('.dailybuddy-accordion-title').attr('aria-expanded', 'false');
                    $otherContent.stop(true, true).slideUp(speed);
                });
            }

            // Toggle current item
            if (isActive) {
                $item.removeClass('active');
                $(this).attr('aria-expanded', 'false');
                $content.stop(true, true).slideUp(speed);
            } else {
                $item.addClass('active');
                $(this).attr('aria-expanded', 'true');
                $content.stop(true, true).slideDown(speed, function() {
                    // Scroll to active item if enabled
                    if (scrollEnabled === 'yes' || scrollEnabled === true) {
                        scrollToItem($item, scrollOffset);
                    }
                });
            }
        });

        // Handle deep linking
        handleDeepLinking($accordion, speed);

        // Handle URL hash changes
        $(window).on('hashchange', function() {
            handleDeepLinking($accordion, speed);
        });
    };

    /**
     * Scroll to active item
     */
    function scrollToItem($item, offset) {
        var itemTop = $item.offset().top - offset;
        
        $('html, body').animate({
            scrollTop: itemTop
        }, 400);
    }

    /**
     * Handle deep linking via URL hash
     */
    function handleDeepLinking($accordion, speed) {
        var hash = window.location.hash;
        
        if (!hash) {
            return;
        }

        var $targetItem = $accordion.find(hash);
        
        if ($targetItem.length && $targetItem.hasClass('dailybuddy-accordion-item')) {
            // Close all items first (if accordion type)
            if ($accordion.data('type') === 'accordion') {
                $accordion.find('.dailybuddy-accordion-item.active').each(function() {
                    $(this).removeClass('active');
                    $(this).find('.dailybuddy-accordion-title').attr('aria-expanded', 'false');
                    $(this).find('.dailybuddy-accordion-content').hide();
                });
            }

            // Open target item
            setTimeout(function() {
                $targetItem.addClass('active highlight');
                $targetItem.find('.dailybuddy-accordion-title').attr('aria-expanded', 'true');
                $targetItem.find('.dailybuddy-accordion-content').stop(true, true).slideDown(speed, function() {
                    // Scroll to item
                    scrollToItem($targetItem, $accordion.data('offset') || 0);
                    
                    // Remove highlight after animation
                    setTimeout(function() {
                        $targetItem.removeClass('highlight');
                    }, 1000);
                });
            }, 100);
        }
    }

    /**
     * Keyboard Accessibility
     */
    function initKeyboardNavigation($scope) {
        $scope.find('.dailybuddy-accordion-title').on('keydown', function(e) {
            // Enter or Space key
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
            
            // Arrow keys navigation
            var $current = $(this).closest('.dailybuddy-accordion-item');
            var $items = $current.siblings('.dailybuddy-accordion-item').addBack();
            var currentIndex = $items.index($current);
            var $target;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $target = $items.eq(currentIndex + 1);
                if ($target.length) {
                    $target.find('.dailybuddy-accordion-title').focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                $target = $items.eq(currentIndex - 1);
                if ($target.length) {
                    $target.find('.dailybuddy-accordion-title').focus();
                }
            } else if (e.key === 'Home') {
                e.preventDefault();
                $items.first().find('.dailybuddy-accordion-title').focus();
            } else if (e.key === 'End') {
                e.preventDefault();
                $items.last().find('.dailybuddy-accordion-title').focus();
            }
        });
    }

    /**
     * Initialize Widget with all features
     */
    var initWidget = function($scope, $) {
        AdvancedAccordion($scope, $);
        initKeyboardNavigation($scope);
    };

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/dailybuddy-advanced-accordion.default',
            initWidget
        );
    });

    // Fallback for non-Elementor pages
    $(document).ready(function() {
        if (!window.elementorFrontend) {
            $('.dailybuddy-accordion').each(function() {
                initWidget($(this).closest('.elementor-widget-dailybuddy-advanced-accordion'), $);
            });
        }
    });

})(jQuery);

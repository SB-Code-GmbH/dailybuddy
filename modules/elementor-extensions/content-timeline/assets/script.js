/**
 * dailybuddy Content Timeline Scripts
 */

(function ($) {
    'use strict';

    var DailybuddyTimeline = {
        init: function () {
            this.adjustTimelineLine();
            this.adjustHorizontalTimelineLine();
            this.handleHorizontalScroll();
            this.handleIntersectionObserver();
        },

        /**
         * Adjust timeline line to start at first bulletpoint and end at last bulletpoint
         * This fine-tunes the CSS defaults based on actual marker positions
         */
        adjustTimelineLine: function () {
            $('.dailybuddy-timeline-vertical .dailybuddy-timeline').each(function () {
                var $timeline = $(this);
                var $line = $timeline.find('.dailybuddy-timeline-line');
                var $markers = $timeline.find('.dailybuddy-timeline-marker');

                if ($markers.length < 2) return; // Need at least 2 markers

                var $firstMarker = $markers.first();
                var $lastMarker = $markers.last();

                if ($firstMarker.length && $lastMarker.length) {
                    var timelineOffset = $timeline.offset().top;
                    var firstMarkerOffset = $firstMarker.offset().top + ($firstMarker.height() / 2);
                    var lastMarkerOffset = $lastMarker.offset().top + ($lastMarker.height() / 2);

                    var topPosition = firstMarkerOffset - timelineOffset;
                    var bottomPosition = $timeline.height() - (lastMarkerOffset - timelineOffset - 120);

                    $line.css({
                        'top': topPosition + 'px',
                        'bottom': bottomPosition + 'px'
                    });
                }
            });
        },

        /**
         * Adjust horizontal timeline line to connect bulletpoints
         */
        adjustHorizontalTimelineLine: function () {
            $('.dailybuddy-timeline-horizontal .dailybuddy-timeline').each(function () {
                var $timeline = $(this);
                var $line = $timeline.find('.dailybuddy-timeline-line');
                var $markers = $timeline.find('.dailybuddy-timeline-marker');

                if ($markers.length < 2) return;

                var $firstMarker = $markers.first();
                var $lastMarker = $markers.last();

                if ($firstMarker.length && $lastMarker.length) {
                    // Berechne die Position der Marker relativ zum ersten Item
                    var firstItemLeft = $firstMarker.closest('.dailybuddy-timeline-item').position().left;
                    var lastItemLeft = $lastMarker.closest('.dailybuddy-timeline-item').position().left;

                    // Marker ist in der Mitte des Items (50%)
                    var firstMarkerCenter = firstItemLeft + ($firstMarker.closest('.dailybuddy-timeline-item').width() / 2);
                    var lastMarkerCenter = lastItemLeft + ($lastMarker.closest('.dailybuddy-timeline-item').width() / 2);

                    var lineWidth = lastMarkerCenter - firstMarkerCenter;
                    var lineTop = "80";

                    $line.css({
                        'left': firstMarkerCenter + 'px',
                        'width': lineWidth + 'px',
                        'top': lineTop + 'px'
                    });
                }
            });
        },

        /**
         * Handle horizontal timeline scroll
         */
        handleHorizontalScroll: function () {
            $('.dailybuddy-timeline-horizontal .dailybuddy-timeline').each(function () {
                var $timeline = $(this);
                var isDragging = false;
                var startX;
                var scrollLeft;

                // Mouse events
                $timeline.on('mousedown', function (e) {
                    isDragging = true;
                    startX = e.pageX - $timeline.offset().left;
                    scrollLeft = $timeline.scrollLeft();
                    $timeline.css('cursor', 'grabbing');
                });

                $(document).on('mouseup', function () {
                    isDragging = false;
                    $timeline.css('cursor', 'grab');
                });

                $timeline.on('mousemove', function (e) {
                    if (!isDragging) return;
                    e.preventDefault();
                    var x = e.pageX - $timeline.offset().left;
                    var walk = (x - startX) * 2;
                    $timeline.scrollLeft(scrollLeft - walk);
                });

                // Touch events for mobile
                var touchStartX;
                var touchScrollLeft;

                $timeline.on('touchstart', function (e) {
                    touchStartX = e.touches[0].pageX - $timeline.offset().left;
                    touchScrollLeft = $timeline.scrollLeft();
                });

                $timeline.on('touchmove', function (e) {
                    var x = e.touches[0].pageX - $timeline.offset().left;
                    var walk = (x - touchStartX) * 2;
                    $timeline.scrollLeft(touchScrollLeft - walk);
                });
            });
        },

        /**
         * Handle Intersection Observer for scroll animations
         */
        handleIntersectionObserver: function () {
            if ('IntersectionObserver' in window) {
                var observerOptions = {
                    threshold: 0.2,
                    rootMargin: '0px 0px -100px 0px'
                };

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            $(entry.target).addClass('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, observerOptions);

                $('.dailybuddy-timeline-item').each(function () {
                    observer.observe(this);
                });
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        DailybuddyTimeline.init();
    });

    // Reinitialize on window resize
    $(window).on('resize', function () {
        DailybuddyTimeline.adjustTimelineLine();
        DailybuddyTimeline.adjustHorizontalTimelineLine();
    });

    // Reinitialize on Elementor frontend init
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/dailybuddy-content-timeline.default', function ($scope) {
            DailybuddyTimeline.init();
        });
    });

})(jQuery);

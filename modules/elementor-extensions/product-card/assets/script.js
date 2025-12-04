/**
 * dailybuddy Product Card Widget Script
 */

(function ($) {
    'use strict';

    /**
     * Initialize Product Card Widget
     */
    var ProductCard = function ($scope, $) {
        var $card = $scope.find('.dailybuddy-product-card');

        if (!$card.length) {
            return;
        }

        // Initialize Countdown
        initCountdown($scope);

        // Initialize Quick View
        initQuickView($scope);
    };

    /**
     * Initialize Countdown Timer
     */
    function initCountdown($scope) {
        var $countdown = $scope.find('.dailybuddy-countdown');

        if (!$countdown.length) {
            return;
        }

        var endDate = $countdown.data('date');

        if (!endDate) {
            return;
        }

        // Convert to timestamp
        var countDownDate = new Date(endDate).getTime();

        // Update countdown every second
        var countdownInterval = setInterval(function () {
            var now = new Date().getTime();
            var distance = countDownDate - now;

            // Time calculations
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result
            $countdown.find('.dailybuddy-countdown-days .number').text(days);
            $countdown.find('.dailybuddy-countdown-hours .number').text(hours);
            $countdown.find('.dailybuddy-countdown-minutes .number').text(minutes);
            $countdown.find('.dailybuddy-countdown-seconds .number').text(seconds);

            // Add urgent class when less than 24 hours left
            if (days === 0 && hours < 24) {
                $countdown.addClass('urgent');
            }

            // If countdown is finished
            if (distance < 0) {
                clearInterval(countdownInterval);
                $countdown.find('.dailybuddy-countdown-timer').html('<span>Expired</span>');
                $countdown.removeClass('urgent');
            }
        }, 1000);
    }

    /**
     * Initialize Quick View Modal
     */
    function initQuickView($scope) {
        var $quickViewBtn = $scope.find('.dailybuddy-quick-view-btn');

        if (!$quickViewBtn.length) {
            return;
        }

        // Open modal
        $quickViewBtn.on('click', function (e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var $modal = $('#quick-view-' + productId);

            if ($modal.length) {
                $modal.addClass('active');
                $('body').css('overflow', 'hidden');
            }
        });

        // Close modal - close button
        $scope.find('.dailybuddy-quick-view-close').on('click', function () {
            closeQuickView($(this).closest('.dailybuddy-quick-view-modal'));
        });

        // Close modal - overlay click
        $scope.find('.dailybuddy-quick-view-overlay').on('click', function () {
            closeQuickView($(this).closest('.dailybuddy-quick-view-modal'));
        });

        // Close modal - ESC key
        $(document).on('keyup', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                var $activeModal = $('.dailybuddy-quick-view-modal.active');
                if ($activeModal.length) {
                    closeQuickView($activeModal);
                }
            }
        });
    }

    /**
     * Close Quick View Modal
     */
    function closeQuickView($modal) {
        $modal.removeClass('active');
        $('body').css('overflow', '');
    }

    /**
     * Add to Cart Handler (Optional - for demo purposes)
     */
    function initAddToCart($scope) {
        $scope.find('.cart-button').on('click', function (e) {
            e.preventDefault();
            var $button = $(this);
            var originalText = $button.find('span').text();

            // Add loading state
            $button.addClass('loading').prop('disabled', true);
            $button.find('span').text('Adding...');

            // Simulate adding to cart (replace with actual cart functionality)
            setTimeout(function () {
                $button.removeClass('loading');
                $button.find('span').text('Added!');

                // Reset button after 2 seconds
                setTimeout(function () {
                    $button.prop('disabled', false);
                    $button.find('span').text(originalText);
                }, 2000);
            }, 1000);
        });
    }

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/dailybuddy-product-card.default',
            ProductCard
        );
    });

    // Fallback for non-Elementor pages
    $(document).ready(function () {
        if (!window.elementorFrontend) {
            $('.dailybuddy-product-card').each(function () {
                ProductCard($(this).closest('.elementor-widget-dailybuddy-product-card'), $);
            });
        }
    });

})(jQuery);

/**
 * dailybuddy Logo Carousel Widget Script
 */

(function ($) {
    'use strict';

    /**
     * Initialize Logo Carousel
     */
    var LogoCarousel = function ($scope, $) {
        var $carousel = $scope.find('.dailybuddy-logo-carousel');
        
        if (!$carousel.length) {
            return;
        }

        var $carouselContainer = $carousel;
        var $settings = $carousel.data('settings');
        var carouselId = $carousel.data('id');

        if (!$settings) {
            return;
        }

        // Swiper configuration
        var swiperConfig = {
            // Slides per view
            slidesPerView: $settings.items_mobile || 2,
            spaceBetween: $settings.margin_mobile || 10,
            
            // Effect
            effect: $settings.effect || 'slide',
            
            // Speed
            speed: $settings.speed || 400,
            
            // Loop
            loop: $settings.loop || false,
            
            // Autoplay
            autoplay: false,
            
            // Grab cursor
            grabCursor: $settings.grab_cursor || false,
            
            // Breakpoints
            breakpoints: {
                768: {
                    slidesPerView: $settings.items_tablet || 3,
                    spaceBetween: $settings.margin_tablet || 20,
                },
                1024: {
                    slidesPerView: $settings.items_desktop || 4,
                    spaceBetween: $settings.margin_desktop || 30,
                }
            },
            
            // Navigation arrows
            navigation: false,
            
            // Pagination
            pagination: false,
        };

        // Add autoplay if enabled
        if ($settings.autoplay) {
            swiperConfig.autoplay = {
                delay: $settings.autoplay_speed || 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: $settings.pause_on_hover || false,
            };
        }

        // Add navigation if enabled
        if ($settings.arrows) {
            swiperConfig.navigation = {
                nextEl: '.swiper-button-next-' + carouselId,
                prevEl: '.swiper-button-prev-' + carouselId,
            };
        }

        // Add pagination if enabled
        if ($settings.dots) {
            swiperConfig.pagination = {
                el: '.swiper-pagination-' + carouselId,
                clickable: true,
                dynamicBullets: false,
            };
        }

        // Effect-specific settings
        if ($settings.effect === 'fade') {
            swiperConfig.fadeEffect = {
                crossFade: true
            };
            swiperConfig.slidesPerView = 1;
            delete swiperConfig.breakpoints;
        }

        if ($settings.effect === 'cube') {
            swiperConfig.cubeEffect = {
                slideShadows: true,
                shadow: true,
                shadowOffset: 20,
                shadowScale: 0.94
            };
            swiperConfig.slidesPerView = 1;
            delete swiperConfig.breakpoints;
        }

        if ($settings.effect === 'coverflow') {
            swiperConfig.coverflowEffect = {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true
            };
            swiperConfig.centeredSlides = true;
        }

        if ($settings.effect === 'flip') {
            swiperConfig.flipEffect = {
                slideShadows: true,
                limitRotation: true
            };
            swiperConfig.slidesPerView = 1;
            delete swiperConfig.breakpoints;
        }

        // Initialize Swiper
        try {
            var swiper = new Swiper($carouselContainer[0], swiperConfig);

            // Handle resize events
            $(window).on('resize', function() {
                if (swiper && swiper.update) {
                    swiper.update();
                }
            });

            // Handle Elementor preview mode
            if (window.elementorFrontend && window.elementorFrontend.isEditMode()) {
                setTimeout(function() {
                    if (swiper && swiper.update) {
                        swiper.update();
                    }
                }, 300);
            }

        } catch (error) {
            console.error('dailybuddy Logo Carousel: Error initializing Swiper', error);
        }
    };

    // Make sure Swiper is available
    var checkSwiperAndInit = function($scope, $) {
        if (typeof Swiper !== 'undefined') {
            LogoCarousel($scope, $);
        } else {
            // Wait for Swiper to load
            var checkInterval = setInterval(function() {
                if (typeof Swiper !== 'undefined') {
                    clearInterval(checkInterval);
                    LogoCarousel($scope, $);
                }
            }, 100);
            
            // Timeout after 5 seconds
            setTimeout(function() {
                clearInterval(checkInterval);
            }, 5000);
        }
    };

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/dailybuddy-logo-carousel.default',
            checkSwiperAndInit
        );
    });

    // Fallback for non-Elementor pages
    $(document).ready(function() {
        if (!window.elementorFrontend) {
            $('.dailybuddy-logo-carousel').each(function() {
                checkSwiperAndInit($(this).closest('.elementor-widget-dailybuddy-logo-carousel'), $);
            });
        }
    });

})(jQuery);

/**
 * dailybuddy Toggle Widget Script
 */

(function ($) {
    'use strict';

    /**
     * Initialize Toggle Widget
     */
    var ToggleWidget = function ($scope, $) {
        var $toggle = $scope.find('.dailybuddy-toggle-wrapper');
        
        if (!$toggle.length) {
            return;
        }

        var toggleStyle = 'default';
        if ($toggle.hasClass('toggle-style-glossy')) {
            toggleStyle = 'glossy';
        } else if ($toggle.hasClass('toggle-style-grasshopper')) {
            toggleStyle = 'grasshopper';
        }

        // Initialize based on style
        if (toggleStyle === 'default') {
            initDefaultToggle($scope);
        } else if (toggleStyle === 'glossy') {
            initGlossyToggle($scope);
        } else if (toggleStyle === 'grasshopper') {
            initGrasshopperToggle($scope);
        }
    };

    /**
     * Default Toggle Style
     */
    function initDefaultToggle($scope) {
        var $input = $scope.find('.dailybuddy-toggle-input');
        var $contentWrap = $scope.find('.dailybuddy-toggle-content-wrap');
        var $primaryContent = $contentWrap.find('.dailybuddy-toggle-primary-wrap');
        var $secondaryContent = $contentWrap.find('.dailybuddy-toggle-secondary-wrap');

        $input.on('change', function() {
            if ($(this).is(':checked')) {
                // Show secondary content
                $primaryContent.removeClass('dailybuddy-toggle-active');
                $secondaryContent.addClass('dailybuddy-toggle-active');
            } else {
                // Show primary content
                $secondaryContent.removeClass('dailybuddy-toggle-active');
                $primaryContent.addClass('dailybuddy-toggle-active');
            }
        });
    }

    /**
     * Glossy Toggle Style
     */
    function initGlossyToggle($scope) {
        var $inputs = $scope.find('.dailybuddy-glossy-input');
        var $contentWrap = $scope.find('.dailybuddy-toggle-content-wrap');
        var $primaryContent = $contentWrap.find('.dailybuddy-toggle-primary-wrap');
        var $secondaryContent = $contentWrap.find('.dailybuddy-toggle-secondary-wrap');

        $inputs.on('change', function() {
            var option = $(this).attr('data-option');
            
            if (option === '1') {
                // Show primary content
                $secondaryContent.removeClass('dailybuddy-toggle-active');
                $primaryContent.addClass('dailybuddy-toggle-active');
            } else {
                // Show secondary content
                $primaryContent.removeClass('dailybuddy-toggle-active');
                $secondaryContent.addClass('dailybuddy-toggle-active');
            }
        });
    }

    /**
     * Grasshopper Toggle Style
     */
    function initGrasshopperToggle($scope) {
        var $inputs = $scope.find('.dailybuddy-grasshopper-input');
        var $contentWrap = $scope.find('.dailybuddy-toggle-content-wrap');
        var $primaryContent = $contentWrap.find('.dailybuddy-toggle-primary-wrap');
        var $secondaryContent = $contentWrap.find('.dailybuddy-toggle-secondary-wrap');

        $inputs.on('change', function() {
            var option = $(this).attr('data-option');
            
            if (option === '1') {
                // Show primary content
                $secondaryContent.removeClass('dailybuddy-toggle-active');
                $primaryContent.addClass('dailybuddy-toggle-active');
            } else {
                // Show secondary content
                $primaryContent.removeClass('dailybuddy-toggle-active');
                $secondaryContent.addClass('dailybuddy-toggle-active');
            }
        });
    }

    // Initialize on Elementor frontend
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/dailybuddy-toggle.default',
            ToggleWidget
        );
    });

    // Fallback for non-Elementor pages
    $(document).ready(function() {
        if (!window.elementorFrontend) {
            $('.dailybuddy-toggle-wrapper').each(function() {
                ToggleWidget($(this).closest('.elementor-widget-dailybuddy-toggle'), $);
            });
        }
    });

})(jQuery);

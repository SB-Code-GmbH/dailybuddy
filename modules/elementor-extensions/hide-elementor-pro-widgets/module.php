<?php

/**
 * Module: Hide Elementor Pro Widgets
 * 
 * Properly unregisters Elementor Pro widgets and hides promotion widgets.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Elementor_Hide_Pro_Widgets
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('elementor/widgets/register', array($this, 'unregister_pro_widgets'), 15);
        add_action('elementor/widgets/widgets_registered', array($this, 'unregister_pro_widgets_legacy'), 15);

        // CSS to hide remaining Pro elements and promotion widgets
        add_action('elementor/editor/after_enqueue_styles', array($this, 'add_editor_css'));

        // JavaScript to remove promotion widgets after they're loaded
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'add_editor_js'));
    }

    /**
     * Unregister Pro widgets (Elementor 3.5+)
     */
    public function unregister_pro_widgets($widgets_manager)
    {
        $pro_widgets = array(
            'theme-archive-title',
            'theme-post-title',
            'theme-post-excerpt',
            'theme-post-content',
            'theme-post-featured-image',
            'theme-post-navigation',
            'theme-site-logo',
            'theme-site-title',
            'theme-page-title',
            'woocommerce-menu-cart',
            'woocommerce-product-title',
            'woocommerce-product-images',
            'woocommerce-product-price',
            'woocommerce-product-add-to-cart',
            'woocommerce-product-rating',
            'woocommerce-product-stock',
            'woocommerce-product-meta',
            'woocommerce-product-short-description',
            'woocommerce-product-content',
            'woocommerce-products',
            'woocommerce-categories',
            'woocommerce-breadcrumb',
            'posts',
            'portfolio',
            'gallery',
            'form',
            'login',
            'slides',
            'nav-menu',
            'animated-headline',
            'hotspot',
            'price-list',
            'price-table',
            'flip-box',
            'call-to-action',
            'media-carousel',
            'testimonial-carousel',
            'reviews',
            'table-of-contents',
            'countdown',
            'share-buttons',
            'facebook-button',
            'facebook-comments',
            'facebook-embed',
            'facebook-page',
            'blockquote',
            'library',
            'template',
            'facebook-widget',
            'popup'
        );

        foreach ($pro_widgets as $widget_name) {
            $widgets_manager->unregister($widget_name);
        }
    }

    /**
     * Unregister Pro widgets (Legacy method)
     */
    public function unregister_pro_widgets_legacy($widgets_manager)
    {
        $pro_widgets = array(
            'theme-archive-title',
            'theme-post-title',
            'theme-post-excerpt',
            'theme-post-content',
            'theme-post-featured-image',
            'theme-post-navigation',
            'theme-site-logo',
            'theme-site-title',
            'theme-page-title',
            'woocommerce-menu-cart',
            'woocommerce-product-title',
            'woocommerce-product-images',
            'woocommerce-product-price',
            'woocommerce-product-add-to-cart',
            'woocommerce-product-rating',
            'woocommerce-product-stock',
            'woocommerce-product-meta',
            'woocommerce-product-short-description',
            'woocommerce-product-content',
            'woocommerce-products',
            'woocommerce-categories',
            'woocommerce-breadcrumb',
            'posts',
            'portfolio',
            'gallery',
            'form',
            'login',
            'slides',
            'nav-menu',
            'animated-headline',
            'hotspot',
            'price-list',
            'price-table',
            'flip-box',
            'call-to-action',
            'media-carousel',
            'testimonial-carousel',
            'reviews',
            'table-of-contents',
            'countdown',
            'share-buttons',
            'facebook-button',
            'facebook-comments',
            'facebook-embed',
            'facebook-page',
            'blockquote',
            'library',
            'template',
            'facebook-widget',
            'popup'
        );

        foreach ($pro_widgets as $widget_name) {
            $widgets_manager->unregister_widget_type($widget_name);
        }
    }

    /**
     * Add CSS to hide Pro elements and promotion widgets
     */
    public function add_editor_css()
    {
        $css = '
            /* Hide ALL promotion widgets (locked widgets with lock icon) */
            .elementor-element-wrapper.elementor-element--promotion,

            /* Hide Pro categories */
            #elementor-panel-category-pro-elements,
            #elementor-panel-category-theme-elements,
            #elementor-panel-category-theme-elements-single,
            #elementor-panel-category-woocommerce-elements,

            /* Hide "Get Pro" sections */
            #elementor-panel-get-pro-elements,
            #elementor-panel-get-pro-elements-sticky,

            /* Hide the Global Widgets tab (Pro feature) */
            .elementor-panel-navigation-tab[data-tab=global],

            /* Hide Pro promotions */
            #elementor-navigator__footer__promotion,
            .e-notice-bar,

            /* Hide dynamic tag controls */
            .elementor-control-dynamic-switcher,

            /* Hide locked Pro controls */
            .elementor-control:has([class*="promotion__lock-wrapper"]),
            .elementor-control-section_custom_css_pro,
            .elementor-control-section_custom_attributes_pro,

            /* Hide any widget with lock icon */
            .elementor-element .eicon-lock,
            .elementor-element-wrapper:has(.eicon-lock)
            {
                display: none !important;
            }

            /* Fix spacing after removing promotion widgets */
            .elementor-panel-category-items {
                gap: 0;
            }
        ';

        wp_add_inline_style('elementor-editor', $css);
    }

    /**
     * Add JavaScript to remove promotion widgets after page load
     */
    public function add_editor_js()
    {
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Function to remove promotion widgets
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

                // Run again after a short delay (for dynamically loaded content)
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
        </script>
<?php
    }
}

WP_Dailybuddy_Elementor_Hide_Pro_Widgets::get_instance();

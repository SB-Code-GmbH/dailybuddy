<?php

/**
 * Module: Elementor Filterable Gallery Widget
 * 
 * Register dailybuddy category and Filterable Gallery widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Elementor_Filterable_Gallery
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
        // Register Elementor hooks
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'register_category'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_widget_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_widget_scripts'));
        add_action('elementor/editor/after_enqueue_styles', array($this, 'enqueue_editor_styles'));
    }

    /**
     * Register widget category
     */
    public function register_category($elements_manager)
    {
        $elements_manager->add_category(
            'dailybuddy',
            array(
                'title' => __('dailybuddy', 'dailybuddy'),
                'icon'  => 'fa fa-toolbox',
            )
        );
    }

    /**
     * Register widgets
     */
    public function register_widgets($widgets_manager)
    {
        // Include widget file
        require_once DAILYBUDDY_PATH . 'modules/elementor-extensions/filterable-gallery/widget.php';

        // Register widget
        $widgets_manager->register(new \WP_Dailybuddy_Elementor_Filterable_Gallery_Widget());
    }

    /**
     * Enqueue widget styles
     */
    public function enqueue_widget_styles()
    {
        // Enqueue Magnific Popup CSS
        wp_enqueue_style(
            'magnific-popup',
            DAILYBUDDY_URL . 'vendor/magnific-popup/magnific-popup.min.css',
            array(),
            '1.1.0'
        );

        wp_enqueue_style(
            'dailybuddy-filterable-gallery',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }

    /**
     * Enqueue widget scripts
     */
    public function enqueue_widget_scripts()
    {
        // Enqueue Isotope
        wp_enqueue_script(
            'isotope',
            DAILYBUDDY_URL . 'vendor/isotope/isotope.pkgd.min.js',
            array('jquery'),
            '3.0.6',
            true
        );

        // Enqueue imagesLoaded
        wp_enqueue_script(
            'imagesloaded',
            DAILYBUDDY_URL . 'vendor/imagesloaded/imagesloaded.pkgd.min.js',
            array('jquery'),
            '5.0.0',
            true
        );

        // Enqueue Magnific Popup
        wp_enqueue_script(
            'magnific-popup',
            DAILYBUDDY_URL . 'vendor/magnific-popup/magnific-popup.min.js',
            array('jquery'),
            '1.1.0',
            true
        );

        // Enqueue DOMPurify for security
        wp_enqueue_script(
            'dompurify',
            DAILYBUDDY_URL . 'vendor/dompurify/purify.min.js',
            array(),
            '3.0.6',
            true
        );

        // Enqueue main script with dailybuddy hooks
        wp_enqueue_script(
            'dailybuddy-filterable-gallery',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/script.js',
            array('jquery', 'isotope', 'imagesloaded', 'magnific-popup', 'dompurify'),
            DAILYBUDDY_VERSION,
            true
        );
    }

    /**
     * Enqueue editor styles
     */
    public function enqueue_editor_styles()
    {
        wp_enqueue_style(
            'dailybuddy-editor',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/editor.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }
}

// Initialize module
WP_Dailybuddy_Elementor_Filterable_Gallery::get_instance();

<?php

/**
 * Module: Elementor Filterable Gallery Widget
 * 
 * Register dailybuddy category and Filterable Gallery widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Elementor_Filterable_Gallery
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
        add_action('elementor/editor/after_enqueue_scripts', array($this, 'enqueue_editor_scripts'));
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
        $widgets_manager->register(new \Dailybuddy_Elementor_Filterable_Gallery_Widget());
    }

    /**
     * Enqueue widget styles
     */
    public function enqueue_widget_styles()
    {
        wp_enqueue_style(
            'dailybuddy-filterable-gallery',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
        
        // Enqueue responsive CSS (uses CSS Custom Properties for dynamic columns)
        wp_enqueue_style(
            'dailybuddy-filterable-gallery-responsive',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/responsive.css',
            array('dailybuddy-filterable-gallery'),
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

        // Enqueue DOMPurify for security
        wp_enqueue_script(
            'dompurify',
            DAILYBUDDY_URL . 'vendor/dompurify/purify.min.js',
            array(),
            '3.0.6',
            true
        );

        // Enqueue main script - now using Elementor's native lightbox
        wp_enqueue_script(
            'dailybuddy-filterable-gallery',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/script.js',
            array('jquery', 'isotope', 'imagesloaded', 'dompurify'),
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

    /**
     * Enqueue editor scripts
     * Moved from inline <script> tag in widget.php for WordPress.org compliance
     */
    public function enqueue_editor_scripts()
    {
        // Register dependencies that are normally only available on the frontend
        if (!wp_script_is('isotope', 'registered')) {
            wp_register_script(
                'isotope',
                DAILYBUDDY_URL . 'vendor/isotope/isotope.pkgd.min.js',
                array('jquery'),
                '3.0.6',
                true
            );
        }

        if (!wp_script_is('imagesloaded', 'registered')) {
            wp_register_script(
                'imagesloaded',
                DAILYBUDDY_URL . 'vendor/imagesloaded/imagesloaded.pkgd.min.js',
                array('jquery'),
                '5.0.0',
                true
            );
        }

        // Enqueue the editor script (isotope initialization for editor preview)
        wp_enqueue_script(
            'dailybuddy-filterable-gallery-editor',
            DAILYBUDDY_URL . 'modules/elementor-extensions/filterable-gallery/assets/editor.js',
            array('jquery', 'isotope', 'imagesloaded'),
            DAILYBUDDY_VERSION,
            true
        );
    }
}

// Initialize module
Dailybuddy_Elementor_Filterable_Gallery::get_instance();

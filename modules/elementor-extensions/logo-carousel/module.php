<?php

/**
 * Module: Elementor Logo Carousel Widget
 * 
 * Register dailybuddy category and Logo Carousel widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Elementor_Logo_Carousel
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
        require_once DAILYBUDDY_PATH . 'modules/elementor-extensions/logo-carousel/widget.php';

        // Register widget
        $widgets_manager->register(new \WP_Dailybuddy_Elementor_Logo_Carousel_Widget());
    }

    /**
     * Enqueue widget styles
     */
    public function enqueue_widget_styles()
    {
        wp_enqueue_style(
            'dailybuddy-logo-carousel',
            DAILYBUDDY_URL . 'modules/elementor-extensions/logo-carousel/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }

    /**
     * Enqueue widget scripts
     */
    public function enqueue_widget_scripts()
    {
        // Swiper is already included by Elementor
        wp_enqueue_script(
            'dailybuddy-logo-carousel',
            DAILYBUDDY_URL . 'modules/elementor-extensions/logo-carousel/assets/script.js',
            array('jquery', 'swiper'),
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
            DAILYBUDDY_URL . 'modules/elementor-extensions/logo-carousel/assets/editor.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }
}

// Initialize module
WP_Dailybuddy_Elementor_Logo_Carousel::get_instance();

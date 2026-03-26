<?php

/**
 * Module: Elementor Category Post List Widget
 *
 * Register dailybuddy category and Category Post List widget.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Elementor_Category_Post_List
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
        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'register_category'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_widget_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_widget_scripts'));
    }

    /**
     * Register widget category.
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
     * Register widgets.
     */
    public function register_widgets($widgets_manager)
    {
        require_once DAILYBUDDY_PATH . 'modules/elementor-extensions/category-post-list/widget.php';
        $widgets_manager->register(new \Dailybuddy_Elementor_Category_Post_List_Widget());
    }

    /**
     * Enqueue widget styles.
     */
    public function enqueue_widget_styles()
    {
        wp_enqueue_style(
            'dailybuddy-category-post-list',
            DAILYBUDDY_URL . 'modules/elementor-extensions/category-post-list/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }
    /**
     * Enqueue widget scripts.
     */
    public function enqueue_widget_scripts()
    {
        wp_enqueue_script(
            'dailybuddy-category-post-list',
            DAILYBUDDY_URL . 'modules/elementor-extensions/category-post-list/assets/script.js',
            array(),
            DAILYBUDDY_VERSION,
            true
        );
    }
}

// Initialize module.
Dailybuddy_Elementor_Category_Post_List::get_instance();

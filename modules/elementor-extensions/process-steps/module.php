<?php

/**
 * Module: Elementor Process Steps Widget
 *
 * Register dailybuddy category and Process Steps widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Elementor_Process_Steps
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
        require_once DAILYBUDDY_PATH . 'modules/elementor-extensions/process-steps/widget.php';
        $widgets_manager->register(new \Dailybuddy_Elementor_Process_Steps_Widget());
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_widget_styles()
    {
        wp_enqueue_style(
            'dailybuddy-process-steps',
            DAILYBUDDY_URL . 'modules/elementor-extensions/process-steps/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_widget_scripts()
    {
        wp_enqueue_script(
            'dailybuddy-process-steps',
            DAILYBUDDY_URL . 'modules/elementor-extensions/process-steps/assets/script.js',
            array(),
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
            'dailybuddy-process-steps-editor',
            DAILYBUDDY_URL . 'modules/elementor-extensions/process-steps/assets/editor.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }
}

Dailybuddy_Elementor_Process_Steps::get_instance();

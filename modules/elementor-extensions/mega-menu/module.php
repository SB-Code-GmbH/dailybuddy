<?php
/**
 * Dailybuddy Mega Menu Module
 * 
 * IMPORTANT: The editor script must be loaded AFTER Elementor's nested-elements script
 * and must listen for 'elementor/nested-element-type-loaded' event
 */

namespace Dailybuddy\Modules\MegaMenu;

if (!defined('ABSPATH')) {
    exit;
}

class Module {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if (!$this->is_nested_elements_active()) {
            return;
        }

        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/frontend/after_register_styles', array($this, 'register_styles'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_scripts'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // CRITICAL: Load editor script with correct dependencies
        add_action('elementor/editor/before_enqueue_scripts', array($this, 'editor_scripts'));
        
        // Editor CSS - in the editor frame
        add_action('elementor/editor/after_enqueue_styles', array($this, 'editor_styles'));
        
        // CRITICAL: Also load editor CSS in the PREVIEW iframe!
        add_action('elementor/preview/enqueue_styles', array($this, 'preview_styles'));
    }
    
    /**
     * Enqueue editor scripts
     * Must be loaded AFTER nested-elements script
     */
    public function editor_scripts() {
        wp_enqueue_script(
            'dailybuddy-mega-menu-editor',
            plugins_url('assets/editor.js', __FILE__),
            array(
                'elementor-editor',
                'elementor-common',
                'jquery',
                'underscore',
                'backbone'
            ),
            filemtime(__DIR__ . '/assets/editor.js'),
            true
        );
    }
    
    /**
     * Enqueue editor styles
     */
    public function editor_styles() {
        wp_enqueue_style(
            'dailybuddy-mega-menu-editor',
            plugins_url('assets/editor.css', __FILE__),
            array('elementor-editor'),
            filemtime(__DIR__ . '/assets/editor.css')
        );
    }
    
    /**
     * Enqueue styles in the preview iframe (where the widget is rendered)
     */
    public function preview_styles() {
        wp_enqueue_style(
            'dailybuddy-mega-menu-editor-preview',
            plugins_url('assets/editor.css', __FILE__),
            array(),
            filemtime(__DIR__ . '/assets/editor.css')
        );
    }

    private function is_nested_elements_active() {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        $experiments = \Elementor\Plugin::$instance->experiments;
        if (!$experiments) {
            return false;
        }
        return $experiments->is_feature_active('nested-elements');
    }

    public function register_widgets($widgets_manager) {
        require_once __DIR__ . '/widget.php';
        $widgets_manager->register(new \Dailybuddy_Mega_Menu_Widget());
    }

    public function register_styles() {
        wp_register_style(
            'dailybuddy-mega-menu-style',
            plugins_url('assets/style.css', __FILE__),
            array('elementor-frontend', 'elementor-icons'),
            filemtime(__DIR__ . '/assets/style.css')
        );
    }

    public function register_scripts() {
        wp_register_script(
            'dailybuddy-mega-menu-script',
            plugins_url('assets/script.js', __FILE__),
            array(), // No dependencies - vanilla JS
            filemtime(__DIR__ . '/assets/script.js'),
            true
        );
    }

    public function enqueue_styles() {
        wp_enqueue_style('dailybuddy-mega-menu-style');
    }

    public function enqueue_scripts() {
        wp_enqueue_script('dailybuddy-mega-menu-script');
    }
}

Module::instance();

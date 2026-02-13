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
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            return;
        }

        // Check required Elementor features and show admin notice if missing
        $missing = $this->get_missing_features();
        if ( ! empty( $missing ) ) {
            add_action( 'admin_notices', function () use ( $missing ) {
                $this->render_missing_features_notice( $missing );
            });
            return;
        }

        add_action('elementor/widgets/register', array($this, 'register_widgets'));
        add_action('elementor/frontend/after_register_styles', array($this, 'register_styles'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'register_scripts'));
        
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
        // Ensure nested-elements JS module is loaded
        // Without Elementor Pro, this script may not be enqueued automatically
        $this->ensure_nested_elements_script();

        $deps = array(
            'elementor-editor',
            'elementor-common',
            'jquery',
            'underscore',
            'backbone',
        );

        // Add nested-elements script as dependency if available
        $nested_handles = array(
            'nested-elements',
            'elementor-nested-elements',
            'elementor-packages-editor-nested-elements',
        );

        foreach ( $nested_handles as $handle ) {
            if ( wp_script_is( $handle, 'registered' ) || wp_script_is( $handle, 'enqueued' ) ) {
                $deps[] = $handle;
                break;
            }
        }

        wp_enqueue_script(
            'dailybuddy-mega-menu-editor',
            plugins_url('assets/editor.js', __FILE__),
            $deps,
            filemtime(__DIR__ . '/assets/editor.js'),
            true
        );
    }

    /**
     * Ensure the nested-elements JS module is registered and enqueued.
     * In Elementor Free (without Pro), this script is not loaded automatically
     * because no built-in widget requires it.
     */
    private function ensure_nested_elements_script() {
        $handle = 'nested-elements';

        // Already enqueued or registered — nothing to do
        if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
            wp_enqueue_script( $handle );
            return;
        }

        // Check alternative handles (varies between Elementor versions)
        $alt_handles = array(
            'elementor-nested-elements',
            'elementor-packages-editor-nested-elements',
        );

        foreach ( $alt_handles as $alt ) {
            if ( wp_script_is( $alt, 'enqueued' ) || wp_script_is( $alt, 'registered' ) ) {
                wp_enqueue_script( $alt );
                return;
            }
        }

        // Not registered at all — register and enqueue it manually
        if ( defined( 'ELEMENTOR_URL' ) && defined( 'ELEMENTOR_VERSION' ) ) {
            $script_url = ELEMENTOR_URL . 'assets/js/nested-elements.min.js';
            $script_path = ELEMENTOR_PATH . 'assets/js/nested-elements.min.js';

            // Only register if the file actually exists in this Elementor version
            if ( file_exists( $script_path ) ) {
                wp_register_script(
                    $handle,
                    $script_url,
                    array( 'elementor-editor' ),
                    ELEMENTOR_VERSION,
                    true
                );
                wp_enqueue_script( $handle );
            }
        }
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

    /**
     * Check which required Elementor features are missing.
     * Returns an array of missing feature names, or empty if all OK.
     */
    private function get_missing_features() {
        $missing = array();
        $experiments = \Elementor\Plugin::$instance->experiments;

        if ( ! $experiments ) {
            return $missing;
        }

        // Check Container feature
        if ( ! $experiments->is_feature_active( 'container' ) ) {
            $missing[] = __( 'Container', 'dailybuddy' );
        }

        // Check Nested Elements feature
        if ( ! $experiments->is_feature_active( 'nested-elements' ) ) {
            $missing[] = __( 'Nested Elements', 'dailybuddy' );
        }

        return $missing;
    }

    /**
     * Render admin notice when required Elementor features are not active.
     */
    private function render_missing_features_notice( $missing ) {
        $settings_url = admin_url( 'admin.php?page=elementor-settings#tab-experiments' );
        $features_list = '<strong>' . implode( '</strong>, <strong>', array_map( 'esc_html', $missing ) ) . '</strong>';

        $message = sprintf(
            /* translators: %s: list of missing feature names */
            __( 'DailyBuddy Mega Menu requires the following Elementor features to be enabled: %s. Please activate them in the Elementor settings.', 'dailybuddy' ),
            $features_list
        );

        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p><p><a href="%s" class="button button-secondary">%s</a></p></div>',
            wp_kses( $message, array( 'strong' => array() ) ),
            esc_url( $settings_url ),
            esc_html__( 'Open Elementor Settings', 'dailybuddy' )
        );
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

}

Module::instance();

<?php

/**
 * Module: Classic Editor
 *
 * Disables the Gutenberg block editor and restores the classic TinyMCE editor
 * for all post types.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dailybuddy_Classic_Editor {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Disable block editor for all post types
        add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

        // Disable block editor for all posts
        add_filter( 'use_block_editor_for_post', '__return_false', 100 );

        // Re-register Classic Editor meta box styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_classic_styles' ) );
    }

    /**
     * Ensure classic editor styles are loaded
     */
    public function enqueue_classic_styles() {
        $screen = get_current_screen();

        if ( $screen && 'post' === $screen->base ) {
            wp_enqueue_script( 'editor-expand' );
        }
    }
}

// Initialize module
Dailybuddy_Classic_Editor::get_instance();

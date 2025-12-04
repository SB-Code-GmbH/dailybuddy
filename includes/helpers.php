<?php

/**
 * Helper Functions
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Get category config from modules/<category>/config.php
 */
function dailybuddy_get_category_config($category)
{
    static $cache = array();

    if (isset($cache[$category])) {
        return $cache[$category];
    }

    $config_file = DAILYBUDDY_PATH . 'modules/' . $category . '/config.php';

    if (file_exists($config_file)) {
        $config = include $config_file;
        if (is_array($config)) {
            $cache[$category] = $config;
            return $config;
        }
    }

    // Fallback: leeres Array
    $cache[$category] = array();
    return $cache[$category];
}

function dailybuddy_format_category_name($category)
{
    $config = dailybuddy_get_category_config($category);

    if (! empty($config['name'])) {
        return $config['name'];
    }

    // Fallback, falls keine Config vorhanden ist
    return ucwords(str_replace(array('-', '_'), ' ', $category));
}

function dailybuddy_get_category_icon($category)
{
    $config = dailybuddy_get_category_config($category);

    if (! empty($config['icon'])) {
        return $config['icon'];
    }

    // Fallback-Emoji, wenn keine Config existiert
    return '📦';
}

function dailybuddy_get_category_description($category)
{
    $config = dailybuddy_get_category_config($category);

    if (! empty($config['description'])) {
        return $config['description'];
    }

    return '';
}

function dailybuddy_register_translated_script($handle, $relative_path, $deps = array('jquery', 'wp-i18n'))
{
    wp_register_script(
        $handle,
        DAILYBUDDY_URL . ltrim($relative_path, '/'),
        $deps,
        DAILYBUDDY_VERSION,
        true
    );

    wp_set_script_translations(
        $handle,
        'dailybuddy',                // deine Textdomain
        DAILYBUDDY_PATH . 'languages'
    );
}

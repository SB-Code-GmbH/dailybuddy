<?php

/**
 * Module Configuration: Under Construction
 *
 * Configuration Structure Overview:
 *
 *  • name (string)
 *      Display name of the module (used in the admin interface).
 *
 *  • description (string)
 *      Standard description of the module. Explains the purpose.
 *
 *  • important_description (string)
 *      Highlighted message shown in red to emphasize critical info
 *      (e.g., module limitations or important usage notes).
 *
 *  • version (string)
 *      Module version. Recommended format: Semantic Versioning (e.g., "1.0.0").
 *
 *  • icon (string)
 *      Icon class from Font Awesome or Dashicons (e.g., "fa-solid fa-person-digging" or "dashicons-admin-tools").
 *
 *  • is_premium (bool)
 *      Set to true if this module is premium-only (will display a crown badge).
 *
 *  • requires (array)
 *      List of required plugins or dependencies (e.g., ['Elementor']).
 *
 *  • tags (array)
 *      Keywords defining the module’s functionality (shown as visual tags).
 *
 *  • has_settings (bool)
 *      Defines whether the module has a settings page.
 *
 *  • settings_callback (string)
 *      Name of the function that renders the module’s settings page.
 */

return array(
    'name'        => __('Under Construction Mode', 'dailybuddy'),
    'description' => __('Shows an "Under Construction" page for non-logged-in users.', 'dailybuddy'),
    'important_description' => __('Maintenance mode is controlled via settings, not when activating the module.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-person-digging',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('maintenance', 'dailybuddy'),
        __('frontend', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_under_construction_settings',
);

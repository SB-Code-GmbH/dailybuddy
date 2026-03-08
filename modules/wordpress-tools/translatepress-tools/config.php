<?php

/**
 * Module Configuration: TranslatePress Tools
 */

return array(
    'name'              => __('TranslatePress Tools', 'dailybuddy'),
    'description'       => __('Enhance TranslatePress with automatic language detection and SEO optimization. Includes hreflang tags, OG locale, canonical URLs, and multilingual sitemap support.', 'dailybuddy'),
    'version'           => '1.0.1',
    'icon'              => 'fa-solid fa-language',
    'is_premium'        => false,
    'requires'          => array('translatepress'),
    'tags'              => array(
        __('translation', 'dailybuddy'),
        __('language', 'dailybuddy'),
        __('multilingual', 'dailybuddy'),
        __('redirect', 'dailybuddy'),
        __('TranslatePress', 'dailybuddy'),
    ),
    'has_settings'      => true,
    'settings_callback' => 'dailybuddy_render_translatepress_tools_settings',
);

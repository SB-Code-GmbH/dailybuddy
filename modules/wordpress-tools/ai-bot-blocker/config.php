<?php

/**
 * Module Configuration: AI Bot Blocker
 */

return array(
    'name'        => __('AI Bot Blocker', 'dailybuddy'),
    'description' => __('Block AI bots and web crawlers from training on your content. Control which AI services can access your website.', 'dailybuddy'),
    'version'     => '1.0.0',
    'icon'        => 'fa-solid fa-robot',
    'is_premium'  => false,
    'requires'    => array(),
    'tags'        => array(
        __('security', 'dailybuddy'),
        __('ai', 'dailybuddy'),
        __('privacy', 'dailybuddy'),
        __('bots', 'dailybuddy')
    ),
    'has_settings' => true,
    'settings_callback' => 'dailybuddy_render_ai_bot_blocker_settings',
);

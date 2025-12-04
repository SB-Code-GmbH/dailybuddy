<?php
// modules/wordpress-tools/config.php

if (! defined('ABSPATH')) {
    exit;
}

return array(
    'id'          => 'wordpress-tools',
    'name'        => __('WordPress Tools', 'dailybuddy'),
    'description' => __('Essential WordPress utilities and tools', 'dailybuddy'),
    'icon'        => 'fa-brands fa-wordpress',
    'order'       => 10,
);

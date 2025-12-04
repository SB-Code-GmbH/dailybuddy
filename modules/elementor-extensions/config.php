<?php
// modules/elementor-extensions/config.php

if (! defined('ABSPATH')) {
    exit;
}

return array(
    'id'          => 'elementor-extensions',
    'name'        => __('Elementor Extensions', 'dailybuddy'),
    'description' => __('Additional widgets and features for Elementor', 'dailybuddy'),
    'icon'        => 'fa-brands fa-elementor',
    'order'       => 30,
);

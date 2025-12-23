<?php

/**
 * Layout: Modern
 * Preview: preview-modern.png
 *
 * Modernes, zentriertes Layout mit Glass-Effekt
 */
if (! defined('ABSPATH')) exit;
?>

<div class="layout-modern">
    <div class="uc-box">
        <h1 class="dailybuddy-under-construction-title"><?php echo esc_html($settings['title']); ?></h1>
        <p class="dailybuddy-under-construction-message"><?php echo esc_html($settings['message']); ?></p>
        <?php echo wp_kses_post($dailybuddy_social_html); ?>
    </div>
</div>
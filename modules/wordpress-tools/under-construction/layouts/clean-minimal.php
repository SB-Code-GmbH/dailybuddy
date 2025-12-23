<?php

/**
 * Layout: Clean Minimal
 * Preview: preview-clean-minimal.png
 *
 * Minimalist design with centered countdown and clean typography
 */
if (! defined('ABSPATH')) exit;

// Calculate countdown target if auto-end is enabled
$dailybuddy_show_countdown = false;
$dailybuddy_countdown_timestamp = 0;

if (!empty($settings['auto_end_enabled']) && !empty($settings['auto_end_datetime'])) {
    $dailybuddy_show_countdown = true;
    $dailybuddy_countdown_timestamp = strtotime($settings['auto_end_datetime']);
}
?>

<div class="layout-clean-minimal">
    <div class="main-container">
        <h1 class="site-title dailybuddy-under-construction-title">
            <?php echo esc_html($settings['title']); ?>
        </h1>

        <p class="subtitle dailybuddy-under-construction-message">
            <?php echo esc_html($settings['message']); ?>
        </p>

        <?php if ($dailybuddy_show_countdown) : ?>
            <!-- Countdown Timer -->
            <div class="countdown-wrapper" data-target-time="<?php echo absint($dailybuddy_countdown_timestamp); ?>">
                <div class="countdown-box">
                    <span class="countdown-number" id="cm-days">00</span>
                    <span class="countdown-label"><?php esc_html_e('Days', 'dailybuddy'); ?></span>
                </div>

                <div class="countdown-box">
                    <span class="countdown-number" id="cm-hours">00</span>
                    <span class="countdown-label"><?php esc_html_e('Hours', 'dailybuddy'); ?></span>
                </div>

                <div class="countdown-box">
                    <span class="countdown-number" id="cm-minutes">00</span>
                    <span class="countdown-label"><?php esc_html_e('Minutes', 'dailybuddy'); ?></span>
                </div>

                <div class="countdown-box">
                    <span class="countdown-number" id="cm-seconds">00</span>
                    <span class="countdown-label"><?php esc_html_e('Seconds', 'dailybuddy'); ?></span>
                </div>
            </div>

        <?php else : ?>
            <!-- No Countdown Message -->
            <div class="no-countdown">
                <p><?php esc_html_e('We are working hard to bring you something amazing!', 'dailybuddy'); ?></p>
            </div>
        <?php endif; ?>

        <?php echo wp_kses_post($dailybuddy_social_html); ?>
    </div>
</div>
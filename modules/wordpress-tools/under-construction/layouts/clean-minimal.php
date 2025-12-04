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

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .layout-clean-minimal {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, #e8e9ed 0%, #f5f5f7 100%);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .layout-clean-minimal .main-container {
        width: 100%;
        max-width: 900px;
        background: #ffffff;
        border-radius: 32px;
        padding: 80px 60px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        position: relative;
    }

    /* Decorative leaf element */
    .layout-clean-minimal .main-container::before {
        content: '🌿';
        position: absolute;
        bottom: 20px;
        left: 20px;
        font-size: 80px;
        opacity: 0.3;
        transform: rotate(-15deg);
    }

    .layout-clean-minimal .site-title {
        font-size: 3.2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
        letter-spacing: -1px;
        position: relative;
        display: inline-block;
    }

    .layout-clean-minimal .site-title::after {
        content: '☀️';
        position: absolute;
        top: -5px;
        right: -40px;
        font-size: 1.8rem;
        animation: rotate 8s linear infinite;
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .layout-clean-minimal .subtitle {
        font-size: 1.35rem;
        color: #a0aec0;
        margin-bottom: 60px;
        font-weight: 400;
        letter-spacing: 0.5px;
    }

    /* Countdown Timer */
    .layout-clean-minimal .countdown-wrapper {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 60px 0 80px;
        flex-wrap: wrap;
    }

    .layout-clean-minimal .countdown-box {
        background: #2d3748;
        border-radius: 16px;
        padding: 30px 25px;
        min-width: 130px;
        box-shadow: 0 8px 20px rgba(45, 55, 72, 0.2);
        transition: transform 0.3s ease;
    }

    .layout-clean-minimal .countdown-box:hover {
        transform: translateY(-5px);
    }

    .layout-clean-minimal .countdown-number {
        display: block;
        font-size: 3.5rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1;
        margin-bottom: 12px;
    }

    .layout-clean-minimal .countdown-label {
        display: block;
        font-size: 0.95rem;
        color: #cbd5e0;
        text-transform: capitalize;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* No countdown message */
    .layout-clean-minimal .no-countdown {
        margin: 60px 0;
        padding: 40px;
        background: #f7fafc;
        border-radius: 16px;
        color: #718096;
        font-size: 1.1rem;
    }

    /* Social Links */
    .layout-clean-minimal .social-links {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 60px;
    }

    .layout-clean-minimal .social-links a {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f7fafc;
        border-radius: 50%;
        color: #4a5568;
        font-size: 1.3rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .layout-clean-minimal .social-links a:hover {
        background: #667eea;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .layout-clean-minimal .main-container {
            padding: 60px 40px;
        }

        .layout-clean-minimal .site-title {
            font-size: 2.5rem;
        }

        .layout-clean-minimal .site-title::after {
            font-size: 1.5rem;
            right: -35px;
        }

        .layout-clean-minimal .subtitle {
            font-size: 1.15rem;
            margin-bottom: 50px;
        }

        .layout-clean-minimal .countdown-wrapper {
            gap: 15px;
            margin: 50px 0 60px;
        }

        .layout-clean-minimal .countdown-box {
            min-width: 110px;
            padding: 25px 20px;
        }

        .layout-clean-minimal .countdown-number {
            font-size: 2.8rem;
        }

        .layout-clean-minimal .countdown-label {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .layout-clean-minimal .main-container {
            padding: 50px 30px;
            border-radius: 24px;
        }

        .layout-clean-minimal .main-container::before {
            font-size: 60px;
            bottom: 15px;
            left: 15px;
        }

        .layout-clean-minimal .site-title {
            font-size: 2rem;
        }

        .layout-clean-minimal .subtitle {
            font-size: 1rem;
        }

        .layout-clean-minimal .countdown-wrapper {
            gap: 12px;
        }

        .layout-clean-minimal .countdown-box {
            min-width: 80px;
            padding: 20px 15px;
        }

        .layout-clean-minimal .countdown-number {
            font-size: 2.2rem;
        }

        .layout-clean-minimal .countdown-label {
            font-size: 0.75rem;
        }
    }

    /* Custom CSS aus Settings */
    <?php
    if (! empty($settings['custom_css'])) {
        echo esc_html($settings['custom_css']);
    }
    ?>
</style>

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
            <div class="countdown-wrapper">
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

            <script>
                (function() {
                    var targetTime = <?php echo absint($dailybuddy_countdown_timestamp); ?> * 1000;

                    function updateCountdown() {
                        var now = new Date().getTime();
                        var distance = targetTime - now;

                        if (distance < 0) {
                            document.getElementById('cm-days').textContent = '00';
                            document.getElementById('cm-hours').textContent = '00';
                            document.getElementById('cm-minutes').textContent = '00';
                            document.getElementById('cm-seconds').textContent = '00';
                            return;
                        }

                        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                        document.getElementById('cm-days').textContent = String(days).padStart(2, '0');
                        document.getElementById('cm-hours').textContent = String(hours).padStart(2, '0');
                        document.getElementById('cm-minutes').textContent = String(minutes).padStart(2, '0');
                        document.getElementById('cm-seconds').textContent = String(seconds).padStart(2, '0');
                    }

                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                })();
            </script>
        <?php else : ?>
            <!-- No Countdown Message -->
            <div class="no-countdown">
                <p><?php esc_html_e('We are working hard to bring you something amazing!', 'dailybuddy'); ?></p>
            </div>
        <?php endif; ?>

        <?php echo wp_kses_post($dailybuddy_social_html); ?>
    </div>
</div>
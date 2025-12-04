<?php

/**
 * Layout: Modern
 * Preview: preview-modern.png
 *
 * Modernes, zentriertes Layout mit Glass-Effekt
 */
if (! defined('ABSPATH')) exit;
?>

<style>
    /* dein richtiges Layout-CSS wie gehabt … */
    .layout-modern {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        text-align: center;
        padding: 30px;
        background: linear-gradient(135deg, #00c853, #007c91);
        color: #ffffff;
        font-family: inherit;
    }

    .layout-modern .uc-box {
        max-width: 520px;
        padding: 40px 35px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }

    .layout-modern h1 {
        font-size: 2.2rem;
        margin-bottom: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .layout-modern p {
        font-size: 1.05rem;
        opacity: 0.9;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .layout-modern .social-links {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-top: 20px;
    }

    .layout-modern .social-links a {
        font-size: 1.4rem;
        color: #ffffff;
        opacity: 0.8;
        transition: all 0.3s;
    }

    .layout-modern .social-links a:hover {
        opacity: 1;
        transform: translateY(-3px);
    }

    /* optional: mobile Anpassungen */
    @media (max-width: 768px) {
        .layout-modern h1 {
            font-size: 1.7rem;
        }

        .layout-modern .uc-box {
            padding: 30px 25px;
        }
    }

    <?php
    if (! empty($settings['custom_css'])) {
        echo esc_html($settings['custom_css']);
    }
    ?>
</style>

<div class="layout-modern">
    <div class="uc-box">
        <h1 class="dailybuddy-under-construction-title"><?php echo esc_html($settings['title']); ?></h1>
        <p class="dailybuddy-under-construction-message"><?php echo esc_html($settings['message']); ?></p>
        <?php echo wp_kses_post($social_html); ?>
    </div>
</div>
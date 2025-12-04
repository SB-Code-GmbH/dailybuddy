<?php

/**
 * General Settings Page
 */

if (! defined('ABSPATH')) {
    exit;
}

// Später kannst du hier wieder Settings speichern, aktuell ist es nur eine Info-Seite.
// if (isset($_POST['dailybuddy_save_general_settings'])) { ... }

?>

<div class="wrap">

    <!-- Breadcrumbs -->
    <div class="dailybuddy-breadcrumbs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy')); ?>">
            <?php esc_html_e('DailyBuddy', 'dailybuddy'); ?>
        </a>
        <span class="separator">›</span>
        <strong><?php esc_html_e('General Settings', 'dailybuddy'); ?></strong>
    </div>

    <!-- Header im selben Layout wie Modul-Settings -->
    <h1 class="dailybuddy-settings-header">
        <span class="dailybuddy-settings-icon dashicons dashicons-admin-generic"></span>
        <span class="dailybuddy-settings-title">
            <?php esc_html_e('General Settings', 'dailybuddy'); ?>
        </span>
    </h1>

    <!-- Gleicher Container wie bei Modul-Settings -->
    <div class="dailybuddy-settings-container">

        <?php
        // Optional: Später, wenn wieder was gespeichert wird:
        // if ( isset($_GET['settings-updated']) ) { ... }
        ?>

        <!-- About / Plugin Description -->
        <h2><?php esc_html_e('About DailyBuddy', 'dailybuddy'); ?></h2>

        <p>
            <?php esc_html_e(
                'DailyBuddy is a modular collection of helpful enhancements for your WordPress site. It bundles several small tools into one plugin, so you can enable only the features you actually need.',
                'dailybuddy'
            ); ?>
        </p>

        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php esc_html_e('Enable handy WordPress utilities such as maintenance mode and content tools.', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Add custom widgets and extensions to your dashboard or page builders.', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Keep everything organized in one central modules overview.', 'dailybuddy'); ?></li>
        </ul>

        <p>
            <?php esc_html_e(
                'You can manage all individual modules, activate or deactivate them, and access their settings from the main DailyBuddy screen.',
                'dailybuddy'
            ); ?>
        </p>

        <hr />

        <!-- Feedback / Support (Link folgt später) -->
        <h2><?php esc_html_e('Feedback & Support', 'dailybuddy'); ?></h2>

        <p>
            <?php esc_html_e(
                'If you have questions, feature requests, or found a bug, we would love to hear from you.',
                'dailybuddy'
            ); ?>
        </p>

        <?php
        // TODO: Hier später den echten WordPress.org Plugin- oder Support-URL einsetzen.
        // Beispiel: $support_url = 'https://wordpress.org/support/plugin/dein-plugin-slug/';
        $dailybuddy_support_url = '#';
        ?>

        <p>
            <a href="<?php echo esc_url($dailybuddy_support_url); ?>" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                <?php esc_html_e('Open support & feedback page', 'dailybuddy'); ?>
            </a>
        </p>

        <p class="description">
            <?php esc_html_e(
                'The support link will point to the official WordPress.org plugin page once it is available.',
                'dailybuddy'
            ); ?>
        </p>

        <?php
        // Lizenzbereich & Speichern-Button vorerst entfernt / auskommentiert.
        // Später kannst du diesen Teil wieder aktivieren, wenn du eine echte Lizenzverwaltung einbauen möchtest.

        /*
        <h2><?php esc_html_e('License', 'dailybuddy'); ?></h2>
        ...
        */

        /*
        <p class="submit">
            <button type="submit" name="dailybuddy_save_general_settings" class="button button-primary">
                <?php esc_html_e('Save Settings', 'dailybuddy'); ?>
            </button>
        </p>
        */
        ?>

    </div>
</div>
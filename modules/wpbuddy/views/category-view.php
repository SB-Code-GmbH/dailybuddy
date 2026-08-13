<?php

/**
 * WPBuddy Category View
 *
 * Rendered by admin/views/main-page.php when the "WPBuddy" tab is open,
 * in place of the default module-card grid.
 *
 * Available in scope:
 *   $dailybuddy_category          — 'wpbuddy'
 *   $dailybuddy_category_modules  — array of module data keyed by folder
 */

if (! defined('ABSPATH')) {
    exit;
}

// The category currently has exactly one module: 'connector'. If more
// come later (e.g. an updates module), decide per-module what to render.
$dailybuddy_wpbuddy_connector = isset($dailybuddy_category_modules['connector'])
    ? $dailybuddy_category_modules['connector']
    : null;

$dailybuddy_wpbuddy_active = $dailybuddy_wpbuddy_connector
    && ! empty($dailybuddy_wpbuddy_connector['active']);
?>

<div class="dailybuddy-wpbuddy-view" data-wpbuddy-reload-on-toggle="1">

    <div class="dailybuddy-wpbuddy-loader" aria-hidden="true">
        <span class="dailybuddy-wpbuddy-loader__spinner"></span>
        <span class="dailybuddy-wpbuddy-loader__text">
            <?php esc_html_e('Working…', 'dailybuddy'); ?>
        </span>
    </div>

    <?php if (! $dailybuddy_wpbuddy_connector) : ?>

        <div class="notice notice-error inline">
            <p>
                <?php esc_html_e('The Connector module is missing from modules/wpbuddy/connector/. Reinstall the plugin to restore it.', 'dailybuddy'); ?>
            </p>
        </div>

    <?php elseif (! $dailybuddy_wpbuddy_active) : ?>

        <div class="dailybuddy-wpbuddy-cta">
            <div class="dailybuddy-wpbuddy-cta__icon">
                <span class="fa-solid fa-satellite-dish"></span>
            </div>

            <h3 class="dailybuddy-wpbuddy-cta__title">
                <?php esc_html_e('Connect this site to a WPBuddy platform', 'dailybuddy'); ?>
            </h3>

            <p class="dailybuddy-wpbuddy-cta__lead">
                <?php esc_html_e('Enable the Connector to link this WordPress installation to a WPBuddy-compatible platform. Once active, you get a one-time pairing token to paste into your platform — nothing leaves your site until the token is used.', 'dailybuddy'); ?>
            </p>

            <div class="dailybuddy-wpbuddy-cta__action">
                <label class="dailybuddy-switch dailybuddy-switch--lg">
                    <input
                        type="checkbox"
                        class="dailybuddy-module-toggle"
                        id="module_<?php echo esc_attr($dailybuddy_wpbuddy_connector['id']); ?>"
                        data-module-id="<?php echo esc_attr($dailybuddy_wpbuddy_connector['id']); ?>"
                        data-module-name="<?php echo esc_attr($dailybuddy_wpbuddy_connector['config']['name']); ?>">
                    <span class="dailybuddy-slider"></span>
                </label>
                <span class="dailybuddy-wpbuddy-cta__action-label">
                    <?php esc_html_e('Activate Connector', 'dailybuddy'); ?>
                </span>
            </div>

            <p class="dailybuddy-wpbuddy-cta__hint">
                <?php esc_html_e('Your pairing token appears here as soon as the Connector is active.', 'dailybuddy'); ?>
            </p>
        </div>

    <?php else : ?>

        <?php
        $dailybuddy_wpbuddy_connection = new Dailybuddy_Platform_Connector_Connection();
        $dailybuddy_wpbuddy_token      = $dailybuddy_wpbuddy_connection->ensure_pairing_token();
        $dailybuddy_wpbuddy_expires_at = $dailybuddy_wpbuddy_connection->get_pairing_token_expires_at();
        $dailybuddy_wpbuddy_expires_in = max(0, $dailybuddy_wpbuddy_expires_at - time());
        $dailybuddy_wpbuddy_expires_min = (int) ceil($dailybuddy_wpbuddy_expires_in / 60);
        ?>

        <div class="dailybuddy-wpbuddy-panel">

            <h3><?php esc_html_e('Pairing token', 'dailybuddy'); ?></h3>
            <p>
                <?php esc_html_e('Copy this token and paste it into your WPBuddy platform to link this site. The token is single-use and expires shortly.', 'dailybuddy'); ?>
            </p>

            <div class="dailybuddy-wpbuddy-token">
                <input
                    type="text"
                    readonly
                    class="dailybuddy-wpbuddy-token__input"
                    id="dailybuddy-wpbuddy-token-input"
                    value="<?php echo esc_attr($dailybuddy_wpbuddy_token); ?>">
                <button
                    type="button"
                    class="button dailybuddy-wpbuddy-btn-brand dailybuddy-wpbuddy-token__copy"
                    data-target="dailybuddy-wpbuddy-token-input">
                    <span class="fa-solid fa-copy"></span>
                    <?php esc_html_e('Copy', 'dailybuddy'); ?>
                </button>
            </div>

            <p class="dailybuddy-wpbuddy-token__meta">
                <?php
                printf(
                    /* translators: %d: minutes remaining */
                    esc_html(_n('Expires in ~%d minute.', 'Expires in ~%d minutes.', $dailybuddy_wpbuddy_expires_min, 'dailybuddy')),
                    (int) $dailybuddy_wpbuddy_expires_min
                );
                ?>
            </p>

            <form
                method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                class="dailybuddy-wpbuddy-token__regen"
                data-confirm="<?php echo esc_attr__('Generate a new token? The current token will stop working immediately, and any pairing already in progress with it will fail.', 'dailybuddy'); ?>">
                <input type="hidden" name="action" value="<?php echo esc_attr(Dailybuddy_Platform_Connector_Connection::ACTION_REGENERATE); ?>">
                <?php wp_nonce_field(Dailybuddy_Platform_Connector_Connection::ACTION_REGENERATE); ?>
                <button type="submit" class="button dailybuddy-wpbuddy-btn-brand">
                    <span class="fa-solid fa-rotate"></span>
                    <?php esc_html_e('Regenerate token', 'dailybuddy'); ?>
                </button>
            </form>

            <div class="dailybuddy-wpbuddy-panel__toggle">
                <label class="dailybuddy-switch">
                    <input
                        type="checkbox"
                        class="dailybuddy-module-toggle"
                        id="module_<?php echo esc_attr($dailybuddy_wpbuddy_connector['id']); ?>"
                        data-module-id="<?php echo esc_attr($dailybuddy_wpbuddy_connector['id']); ?>"
                        data-module-name="<?php echo esc_attr($dailybuddy_wpbuddy_connector['config']['name']); ?>"
                        checked>
                    <span class="dailybuddy-slider"></span>
                </label>
                <span class="dailybuddy-wpbuddy-panel__toggle-label">
                    <?php esc_html_e('Deactivate Connector', 'dailybuddy'); ?>
                </span>
            </div>
        </div>

    <?php endif; ?>

</div>

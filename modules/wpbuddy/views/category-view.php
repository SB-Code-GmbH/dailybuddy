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

    <div class="notice notice-info inline dailybuddy-wpbuddy-beta">
        <p>
            <strong><?php esc_html_e('Beta', 'dailybuddy'); ?>:</strong>
            <?php esc_html_e('The WPBuddy Connector is still under active development. Core features (pairing, snapshots, comment moderation, SSO, optimization) work — but the platform side is evolving fast, so expect the occasional rough edge. Feedback welcome.', 'dailybuddy'); ?>
        </p>
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
        $dailybuddy_wpbuddy_pairings   = $dailybuddy_wpbuddy_connection->get_pairings();
        $dailybuddy_wpbuddy_date_fmt   = get_option('date_format') . ' ' . get_option('time_format');
        ?>

        <?php if (! empty($dailybuddy_wpbuddy_pairings)) : ?>
            <div class="dailybuddy-wpbuddy-pairings">
                <div class="dailybuddy-wpbuddy-pairings__header">
                    <span class="fa-solid fa-satellite-dish"></span>
                    <h3>
                        <?php
                        printf(
                            esc_html(
                                /* translators: %d: number of platforms this site is connected to */
                                _n(
                                    'Connected with %d platform',
                                    'Connected with %d platforms',
                                    count($dailybuddy_wpbuddy_pairings),
                                    'dailybuddy'
                                )
                            ),
                            count($dailybuddy_wpbuddy_pairings)
                        );
                        ?>
                    </h3>
                </div>

                <ul class="dailybuddy-wpbuddy-pairing-list">
                    <?php foreach ($dailybuddy_wpbuddy_pairings as $dailybuddy_wpbuddy_pairing) :
                        $dailybuddy_wpbuddy_p_url   = (string) ($dailybuddy_wpbuddy_pairing['platform_url'] ?? '');
                        $dailybuddy_wpbuddy_p_name  = (string) ($dailybuddy_wpbuddy_pairing['platform_name'] ?? '');
                        $dailybuddy_wpbuddy_p_uuid  = (string) ($dailybuddy_wpbuddy_pairing['site_uuid'] ?? '');
                        $dailybuddy_wpbuddy_p_since = (int)    ($dailybuddy_wpbuddy_pairing['connected_at'] ?? 0);
                        $dailybuddy_wpbuddy_p_since_fmt = $dailybuddy_wpbuddy_p_since
                            ? wp_date($dailybuddy_wpbuddy_date_fmt, $dailybuddy_wpbuddy_p_since)
                            : '—';
                    ?>
                        <li class="dailybuddy-wpbuddy-pairing">
                            <div class="dailybuddy-wpbuddy-pairing__body">
                                <div class="dailybuddy-wpbuddy-pairing__title">
                                    <a href="<?php echo esc_url($dailybuddy_wpbuddy_p_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($dailybuddy_wpbuddy_p_name ?: $dailybuddy_wpbuddy_p_url); ?>
                                    </a>
                                </div>
                                <div class="dailybuddy-wpbuddy-pairing__meta">
                                    <?php esc_html_e('Since', 'dailybuddy'); ?> <?php echo esc_html($dailybuddy_wpbuddy_p_since_fmt); ?>
                                    · <?php esc_html_e('UUID', 'dailybuddy'); ?> <code><?php echo esc_html($dailybuddy_wpbuddy_p_uuid); ?></code>
                                </div>
                            </div>
                            <form
                                method="post"
                                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                class="dailybuddy-wpbuddy-disconnect"
                                <?php /* translators: %s: platform name or URL the site is currently paired with */ ?>
                                data-confirm="<?php echo esc_attr(sprintf(__('Disconnect from %s? That platform will stop receiving updates.', 'dailybuddy'), $dailybuddy_wpbuddy_p_name ?: $dailybuddy_wpbuddy_p_url)); ?>">
                                <input type="hidden" name="action" value="<?php echo esc_attr(Dailybuddy_Platform_Connector_Connection::ACTION_DISCONNECT); ?>">
                                <input type="hidden" name="site_uuid" value="<?php echo esc_attr($dailybuddy_wpbuddy_p_uuid); ?>">
                                <?php wp_nonce_field(Dailybuddy_Platform_Connector_Connection::ACTION_DISCONNECT); ?>
                                <button type="submit" class="button dailybuddy-wpbuddy-btn-danger">
                                    <span class="fa-solid fa-link-slash"></span>
                                    <?php esc_html_e('Disconnect', 'dailybuddy'); ?>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php
        $dailybuddy_wpbuddy_token      = $dailybuddy_wpbuddy_connection->ensure_pairing_token();
        $dailybuddy_wpbuddy_expires_at = $dailybuddy_wpbuddy_connection->get_pairing_token_expires_at();
        $dailybuddy_wpbuddy_has_pairs  = ! empty($dailybuddy_wpbuddy_pairings);
        ?>

        <div class="dailybuddy-wpbuddy-panel">

            <h3>
                <?php echo $dailybuddy_wpbuddy_has_pairs
                    ? esc_html__('Pair with another platform', 'dailybuddy')
                    : esc_html__('Pairing token', 'dailybuddy'); ?>
            </h3>
            <p>
                <?php esc_html_e('Copy this token and paste it into your WPBuddy platform to link this site. The token is single-use.', 'dailybuddy'); ?>
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

            <p class="dailybuddy-wpbuddy-token__meta"
               id="dailybuddy-wpbuddy-token-meta"
               data-expires-at="<?php echo esc_attr($dailybuddy_wpbuddy_expires_at); ?>">
                <?php esc_html_e('Expires in', 'dailybuddy'); ?>
                <span id="dailybuddy-wpbuddy-token-countdown">…</span>
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

        <script>
        (function () {
            var meta = document.getElementById('dailybuddy-wpbuddy-token-meta');
            var out  = document.getElementById('dailybuddy-wpbuddy-token-countdown');
            if (!meta || !out) return;
            var expiresAt = parseInt(meta.getAttribute('data-expires-at'), 10);
            if (!expiresAt) return;

            function tick() {
                var left = expiresAt - Math.floor(Date.now() / 1000);
                if (left <= 0) {
                    meta.innerHTML = '<strong style="color:#d63638;"><?php echo esc_js(__('Token expired — regenerate to continue.', 'dailybuddy')); ?></strong>';
                    var input = document.getElementById('dailybuddy-wpbuddy-token-input');
                    if (input) input.disabled = true;
                    return;
                }
                var m = Math.floor(left / 60);
                var s = left % 60;
                out.textContent = m + ':' + (s < 10 ? '0' + s : s);
                setTimeout(tick, 1000);
            }
            tick();
        })();
        </script>

    <?php endif; ?>

</div>

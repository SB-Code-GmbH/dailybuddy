<?php

/**
 * Connection state manager.
 *
 * Owns the "am I connected?" question and the connect/disconnect
 * transitions. Talks to the platform's /api/wpbuddy/register endpoint
 * during the token handshake.
 *
 * Filled in as Tasks #22 (REST endpoint) and its counterpart on the
 * platform side (#21) land — this stub keeps the module loadable
 * during scaffolding.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector_Connection
{
    // Pairing token: 128-bit random, one-time, 15 min TTL. Stored as a
    // transient so it auto-expires. The user displays this in WP admin
    // and pastes it into a WPBuddy platform to trigger the handshake.
    const TRANSIENT_TOKEN     = 'dailybuddy_pc_pairing_token';
    const TRANSIENT_TOKEN_EXP = 'dailybuddy_pc_pairing_token_expires_at';
    const TOKEN_TTL           = 900; // seconds (15 min)

    // admin-post.php action for regenerating a token via form submit.
    const ACTION_REGENERATE = 'dailybuddy_pc_regenerate_token';

    public function __construct()
    {
        add_action('admin_post_' . self::ACTION_REGENERATE, array($this, 'handle_regenerate'));
    }

    /**
     * Return the current pairing token, generating one if missing.
     * Format: base64url(site_url) . "." . random-hex   (URL embedded so
     * the platform doesn't need a second input field). WP stores only
     * the random part in the transient.
     */
    public function ensure_pairing_token()
    {
        $secret = get_transient(self::TRANSIENT_TOKEN);
        if (! $secret) {
            $secret = $this->generate_pairing_token(); // returns raw secret
        }
        return $this->encode_display_token($secret);
    }

    /**
     * Force-generate a fresh secret, replacing any existing one.
     * Returns the raw random secret (32 hex chars) — callers who need
     * the full displayable token should use ensure_pairing_token().
     */
    public function generate_pairing_token()
    {
        // Make sure the keypair is ready — otherwise the handshake would
        // fail later. Do it now so the user sees any error early.
        $keypair = new Dailybuddy_Platform_Connector_Keypair();
        $keypair->ensure_keypair();

        $secret  = bin2hex(random_bytes(16)); // 32 hex chars, 128 bit entropy
        $expires = time() + self::TOKEN_TTL;

        set_transient(self::TRANSIENT_TOKEN, $secret, self::TOKEN_TTL);
        update_option(self::TRANSIENT_TOKEN_EXP, $expires, false);

        return $secret;
    }

    /**
     * Returns the raw secret currently stored (without the URL prefix).
     * Used by the REST /connect endpoint to validate an incoming token.
     */
    public function get_pairing_secret()
    {
        return get_transient(self::TRANSIENT_TOKEN);
    }

    /**
     * Wrap a raw secret as the user-facing token (URL embedded).
     */
    private function encode_display_token($secret)
    {
        $url_b64 = rtrim(strtr(base64_encode(home_url('/')), '+/', '-_'), '=');
        return $url_b64 . '.' . $secret;
    }

    public function get_pairing_token_expires_at()
    {
        return (int) get_option(self::TRANSIENT_TOKEN_EXP, 0);
    }

    public function consume_pairing_token()
    {
        delete_transient(self::TRANSIENT_TOKEN);
        delete_option(self::TRANSIENT_TOKEN_EXP);
    }

    /**
     * admin-post handler for the "Regenerate Token" form.
     */
    public function handle_regenerate()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No permission.', 'dailybuddy'));
        }
        check_admin_referer(self::ACTION_REGENERATE);

        $this->generate_pairing_token();

        wp_safe_redirect(add_query_arg(
            array('page' => 'dailybuddy', 'focus_category' => 'wpbuddy', 'token' => 'regenerated'),
            admin_url('admin.php')
        ));
        exit;
    }

    /**
     * Wipe local connection state. Doesn't notify the platform — the
     * platform will notice via missing heartbeats and mark stale/error.
     */
    public function disconnect()
    {
        delete_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_URL);
        delete_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_PUB_KEY);
        delete_option(Dailybuddy_Platform_Connector::OPT_SITE_UUID);
        delete_option(Dailybuddy_Platform_Connector::OPT_CONNECTED_AT);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_HEARTBEAT_AT);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_ERROR);
        $this->consume_pairing_token();
    }
}

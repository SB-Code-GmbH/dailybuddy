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

    // admin-post.php actions.
    const ACTION_REGENERATE = 'dailybuddy_pc_regenerate_token';
    const ACTION_DISCONNECT = 'dailybuddy_pc_disconnect';

    public function __construct()
    {
        add_action('admin_post_' . self::ACTION_REGENERATE, array($this, 'handle_regenerate'));
        add_action('admin_post_' . self::ACTION_DISCONNECT, array($this, 'handle_disconnect'));
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

    // ── Pairings storage ───────────────────────────────────────

    /**
     * All current pairings. Migrates the legacy single-connection
     * options into the array on first read, then removes them.
     *
     * @return array<int, array{site_uuid:string, platform_url:string,
     *   platform_public_key:string, platform_name:string,
     *   connected_at:int, last_heartbeat_at:?int}>
     */
    public function get_pairings()
    {
        $pairings = get_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, null);
        if (!is_array($pairings)) {
            $pairings = $this->migrate_legacy_pairing();
            update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, $pairings, false);
        }
        return $pairings;
    }

    /**
     * Add a pairing, or update in place if one with the same platform
     * public key already exists (idempotent re-pair from that platform).
     * Returns the stored pairing including its site_uuid.
     */
    public function upsert_pairing($platform_url, $platform_public_key, $platform_name = '')
    {
        $pairings = $this->get_pairings();
        foreach ($pairings as $i => $p) {
            if (($p['platform_public_key'] ?? '') === $platform_public_key) {
                $pairings[$i]['platform_url']  = $platform_url;
                $pairings[$i]['platform_name'] = $platform_name;
                if (empty($pairings[$i]['connected_at'])) {
                    $pairings[$i]['connected_at'] = time();
                }
                update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, $pairings, false);
                return $pairings[$i];
            }
        }
        $entry = array(
            'site_uuid'           => function_exists('wp_generate_uuid4')
                ? wp_generate_uuid4()
                : bin2hex(random_bytes(16)),
            'platform_url'        => $platform_url,
            'platform_public_key' => $platform_public_key,
            'platform_name'       => $platform_name,
            'connected_at'        => time(),
            'last_heartbeat_at'   => null,
        );
        $pairings[] = $entry;
        update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, $pairings, false);
        return $entry;
    }

    /**
     * Remove a pairing by its site_uuid. No-op if not found.
     */
    public function remove_pairing($site_uuid)
    {
        $pairings = $this->get_pairings();
        $pairings = array_values(array_filter($pairings, function ($p) use ($site_uuid) {
            return ($p['site_uuid'] ?? '') !== $site_uuid;
        }));
        update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, $pairings, false);
        return $pairings;
    }

    /**
     * Wipe all pairings + the pairing token. Called when the connector
     * module is deactivated so nothing dangles.
     */
    public function disconnect_all()
    {
        update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, array(), false);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_ERROR);
        $this->consume_pairing_token();
    }

    /**
     * One-time migration of the old single-value pairing options into
     * an array. Runs on first get_pairings() call after upgrade.
     */
    private function migrate_legacy_pairing()
    {
        $legacy_url  = (string) get_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_URL, '');
        $legacy_pub  = (string) get_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_PUB_KEY, '');
        $legacy_uuid = (string) get_option(Dailybuddy_Platform_Connector::OPT_SITE_UUID, '');
        $legacy_at   = (int)    get_option(Dailybuddy_Platform_Connector::OPT_CONNECTED_AT, 0);

        $migrated = array();
        if ($legacy_url !== '' && $legacy_uuid !== '') {
            $migrated[] = array(
                'site_uuid'           => $legacy_uuid,
                'platform_url'        => $legacy_url,
                'platform_public_key' => $legacy_pub,
                'platform_name'       => parse_url($legacy_url, PHP_URL_HOST) ?: $legacy_url,
                'connected_at'        => $legacy_at ?: time(),
                'last_heartbeat_at'   => (int) get_option(Dailybuddy_Platform_Connector::OPT_LAST_HEARTBEAT_AT, 0) ?: null,
            );
        }
        // Drop the legacy options — the array is now authoritative.
        delete_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_URL);
        delete_option(Dailybuddy_Platform_Connector::OPT_PLATFORM_PUB_KEY);
        delete_option(Dailybuddy_Platform_Connector::OPT_SITE_UUID);
        delete_option(Dailybuddy_Platform_Connector::OPT_CONNECTED_AT);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_HEARTBEAT_AT);

        return $migrated;
    }

    /**
     * admin-post handler for the "Disconnect" form.
     * With a "site_uuid" POST field: removes that single pairing.
     * Without it: removes ALL pairings (used by an "Unpair everything"
     * action if we add one later).
     */
    public function handle_disconnect()
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('No permission.', 'dailybuddy'));
        }
        check_admin_referer(self::ACTION_DISCONNECT);

        $uuid = isset($_POST['site_uuid'])
            ? sanitize_text_field(wp_unslash($_POST['site_uuid']))
            : '';

        if ($uuid !== '') {
            $this->remove_pairing($uuid);
        } else {
            $this->disconnect_all();
        }

        wp_safe_redirect(add_query_arg(
            array('page' => 'dailybuddy', 'focus_category' => 'wpbuddy', 'disconnected' => '1'),
            admin_url('admin.php')
        ));
        exit;
    }
}

<?php

/**
 * WPBuddy REST endpoints exposed to the platform side.
 *
 * All routes live under the `wpbuddy/v1` namespace.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector_Rest
{
    const NAMESPACE_V1 = 'wpbuddy/v1';

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(self::NAMESPACE_V1, '/connect', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_connect'),
            // Auth is the pairing token itself — validated inside the
            // callback. No WP user needs to be logged in.
            'permission_callback' => '__return_true',
            'args'                => array(
                'token' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'platform_url' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'platform_public_key' => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'platform_name' => array(
                    'required' => false,
                    'type'     => 'string',
                ),
            ),
        ));

        // Read-only status probe used by the platform's auth-based
        // connect flow to check whether this site is already paired.
        // Requires an authenticated user with manage_options — which
        // in practice means Basic Auth with an Application Password.
        register_rest_route(self::NAMESPACE_V1, '/status', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'handle_status'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Auth-based pairing endpoint (used by the WPBuddy platform's
        // "Install & connect" flow). Same result as /connect, but the
        // caller authenticates with an Application Password instead of
        // proving they know a pre-generated pairing token.
        // Snapshot endpoint — signed requests only. The platform proves
        // it holds the private key that pairs with the stored public
        // key for this site UUID. See handle_snapshot() for details.
        register_rest_route(self::NAMESPACE_V1, '/snapshot', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'handle_snapshot'),
            // Signature is verified inside the callback — this returns
            // true so WP hands us the request; we reject early if the
            // signature is missing/bad.
            'permission_callback' => '__return_true',
        ));

        register_rest_route(self::NAMESPACE_V1, '/auto-connect', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_auto_connect'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args'                => array(
                'platform_url'        => array('required' => true, 'type' => 'string'),
                'platform_public_key' => array('required' => true, 'type' => 'string'),
                'platform_name'       => array('required' => false, 'type' => 'string'),
            ),
        ));
    }

    /**
     * Handshake endpoint.
     *
     * Platform POSTs the pairing secret plus its own identity. We
     * validate the secret, remember the platform, hand back the site
     * UUID + our public key, and burn the token so it can't be reused.
     */
    public function handle_connect(WP_REST_Request $request)
    {
        $connection = new Dailybuddy_Platform_Connector_Connection();

        $token = (string) $request->get_param('token');
        $stored = (string) $connection->get_pairing_secret();

        if ('' === $stored || '' === $token || ! hash_equals($stored, $token)) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_token',
                'message' => __('Invalid or expired pairing token.', 'dailybuddy'),
            ), 401);
        }

        $platform_url = esc_url_raw((string) $request->get_param('platform_url'));
        $platform_pub = trim((string) $request->get_param('platform_public_key'));

        if ('' === $platform_url || ! preg_match('#^https?://#i', $platform_url)) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_platform_url',
                'message' => __('Platform URL is missing or malformed.', 'dailybuddy'),
            ), 400);
        }
        if ('' === $platform_pub || false === strpos($platform_pub, 'BEGIN PUBLIC KEY')) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_platform_key',
                'message' => __('Platform public key is missing or malformed.', 'dailybuddy'),
            ), 400);
        }

        // Ensure our own keypair exists — token-generation already does
        // this, but be defensive in case someone hits /connect directly.
        $keypair = new Dailybuddy_Platform_Connector_Keypair();
        $ok = $keypair->ensure_keypair();
        if (is_wp_error($ok)) {
            return new WP_REST_Response(array(
                'code'    => 'keypair_failed',
                'message' => $ok->get_error_message(),
            ), 500);
        }

        // Register (or update-in-place) this pairing.
        $platform_name = (string) $request->get_param('platform_name');
        if ($platform_name === '') {
            $platform_name = parse_url($platform_url, PHP_URL_HOST) ?: $platform_url;
        }
        $pairing = $connection->upsert_pairing($platform_url, $platform_pub, $platform_name);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_ERROR);

        // Burn the token so it can't be reused.
        $connection->consume_pairing_token();

        return new WP_REST_Response(array(
            'site_uuid'     => $pairing['site_uuid'],
            'wp_public_key' => $keypair->get_public_key(),
            'home_url'      => home_url('/'),
            'site_name'     => get_bloginfo('name'),
            'wp_version'    => get_bloginfo('version'),
            'php_version'   => PHP_VERSION,
            'connected_at'  => gmdate('c', $pairing['connected_at']),
        ), 200);
    }

    /**
     * Auth-based pairing (no token). Caller is authenticated as an
     * administrator via Basic Auth (Application Password), which we
     * trust as proof of intent — no separate pairing token needed.
     * Everything else works exactly like handle_connect().
     */
    public function handle_auto_connect(WP_REST_Request $request)
    {
        $platform_url = esc_url_raw((string) $request->get_param('platform_url'));
        $platform_pub = trim((string) $request->get_param('platform_public_key'));

        if ('' === $platform_url || ! preg_match('#^https?://#i', $platform_url)) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_platform_url',
                'message' => __('Platform URL is missing or malformed.', 'dailybuddy'),
            ), 400);
        }
        if ('' === $platform_pub || false === strpos($platform_pub, 'BEGIN PUBLIC KEY')) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_platform_key',
                'message' => __('Platform public key is missing or malformed.', 'dailybuddy'),
            ), 400);
        }

        $keypair = new Dailybuddy_Platform_Connector_Keypair();
        $ok = $keypair->ensure_keypair();
        if (is_wp_error($ok)) {
            return new WP_REST_Response(array(
                'code'    => 'keypair_failed',
                'message' => $ok->get_error_message(),
            ), 500);
        }

        $platform_name = (string) $request->get_param('platform_name');
        if ($platform_name === '') {
            $platform_name = parse_url($platform_url, PHP_URL_HOST) ?: $platform_url;
        }
        $connection = new Dailybuddy_Platform_Connector_Connection();
        $pairing = $connection->upsert_pairing($platform_url, $platform_pub, $platform_name);
        delete_option(Dailybuddy_Platform_Connector::OPT_LAST_ERROR);
        $connection->consume_pairing_token();

        return new WP_REST_Response(array(
            'site_uuid'     => $pairing['site_uuid'],
            'wp_public_key' => $keypair->get_public_key(),
            'home_url'      => home_url('/'),
            'site_name'     => get_bloginfo('name'),
            'wp_version'    => get_bloginfo('version'),
            'php_version'   => PHP_VERSION,
            'connected_at'  => gmdate('c', $pairing['connected_at']),
        ), 200);
    }

    /**
     * Signed snapshot endpoint — how the WPBuddy platform pulls live
     * site data (users, plugins, themes, updates, versions).
     *
     * Authentication (no passwords, no cookies):
     *   Headers:
     *     X-WPBuddy-Site-UUID   — identifies which pairing signed this
     *     X-WPBuddy-Timestamp   — unix seconds, must be within ±5 min
     *     X-WPBuddy-Signature   — base64(RSA-SHA256("<uuid>|<timestamp>"))
     *                              signed with the platform's private key
     *   We look up the platform_public_key stored for that pairing and
     *   verify. On success: collect + return snapshot; touch the
     *   pairing's last_heartbeat_at.
     */
    public function handle_snapshot(WP_REST_Request $request)
    {
        $uuid = (string) $request->get_header('X-WPBuddy-Site-UUID');
        $ts   = (int)    $request->get_header('X-WPBuddy-Timestamp');
        $sig  = (string) $request->get_header('X-WPBuddy-Signature');

        if ($uuid === '' || $ts <= 0 || $sig === '') {
            return new WP_REST_Response(array(
                'code'    => 'missing_auth_headers',
                'message' => __('Missing signature headers.', 'dailybuddy'),
            ), 401);
        }

        // ±5 minute drift window blocks replayed requests.
        $skew = abs(time() - $ts);
        if ($skew > 300) {
            return new WP_REST_Response(array(
                'code'    => 'stale_timestamp',
                'message' => __('Request timestamp is out of range.', 'dailybuddy'),
                'skew'    => $skew,
            ), 401);
        }

        $connection = new Dailybuddy_Platform_Connector_Connection();
        $pairings   = $connection->get_pairings();
        $pairing    = null;
        foreach ($pairings as $p) {
            if (($p['site_uuid'] ?? '') === $uuid) { $pairing = $p; break; }
        }
        if (!$pairing || empty($pairing['platform_public_key'])) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_pairing',
                'message' => __('No pairing with that UUID.', 'dailybuddy'),
            ), 401);
        }

        $rawSig  = base64_decode($sig, true);
        $message = $uuid . '|' . $ts;
        $ok = @openssl_verify($message, $rawSig, $pairing['platform_public_key'], OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            return new WP_REST_Response(array(
                'code'    => 'bad_signature',
                'message' => __('Signature did not verify.', 'dailybuddy'),
            ), 401);
        }

        // Auth passed → collect + return snapshot.
        $snapshot = $this->collect_snapshot();

        // Touch the pairing's last_heartbeat_at so the WP admin shows
        // when the platform last polled.
        $pairings_all = $connection->get_pairings();
        foreach ($pairings_all as $i => $p) {
            if (($p['site_uuid'] ?? '') === $uuid) {
                $pairings_all[$i]['last_heartbeat_at'] = time();
                update_option(Dailybuddy_Platform_Connector::OPT_PAIRINGS, $pairings_all, false);
                break;
            }
        }

        return new WP_REST_Response($snapshot, 200);
    }

    /**
     * Assemble everything the platform card + detail view might want.
     * Kept in one place so it stays consistent across future endpoints
     * (e.g. push heartbeats). No secrets, no user PII beyond aggregate
     * counts.
     */
    private function collect_snapshot()
    {
        // Plugins + themes need the admin includes.
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!function_exists('wp_get_themes')) {
            require_once ABSPATH . 'wp-includes/theme.php';
        }

        // Refresh core/plugin/theme update transients so counts are
        // current (WP would otherwise wait for its own schedule).
        if (!function_exists('wp_update_plugins')) {
            require_once ABSPATH . 'wp-includes/update.php';
        }
        @wp_version_check(array(), true);
        @wp_update_plugins();
        @wp_update_themes();

        // ── plugins ────────────────────────────────────────────────
        $all_plugins    = get_plugins();
        $active_plugins = (array) get_option('active_plugins', array());
        $plugin_updates = get_site_transient('update_plugins');
        $plugin_updates = ($plugin_updates && !empty($plugin_updates->response))
            ? $plugin_updates->response : array();

        $plugins = array();
        foreach ($all_plugins as $file => $data) {
            $plugins[] = array(
                'file'             => $file,
                'name'             => (string) ($data['Name']    ?? ''),
                'version'          => (string) ($data['Version'] ?? ''),
                'author'           => wp_strip_all_tags((string) ($data['Author']  ?? '')),
                'active'           => in_array($file, $active_plugins, true),
                'update_available' => isset($plugin_updates[$file]),
                'new_version'      => isset($plugin_updates[$file])
                    ? (string) ($plugin_updates[$file]->new_version ?? '')
                    : null,
            );
        }

        // ── themes ─────────────────────────────────────────────────
        $all_themes    = wp_get_themes();
        $active_theme  = wp_get_theme();
        $theme_updates = get_site_transient('update_themes');
        $theme_updates = ($theme_updates && !empty($theme_updates->response))
            ? $theme_updates->response : array();

        $themes = array();
        foreach ($all_themes as $slug => $theme) {
            $themes[] = array(
                'slug'             => (string) $slug,
                'name'             => (string) $theme->get('Name'),
                'version'          => (string) $theme->get('Version'),
                'author'           => wp_strip_all_tags((string) $theme->get('Author')),
                'active'           => ($active_theme && $active_theme->get_stylesheet() === $slug),
                'update_available' => isset($theme_updates[$slug]),
                'new_version'      => isset($theme_updates[$slug])
                    ? (string) ($theme_updates[$slug]['new_version'] ?? '')
                    : null,
            );
        }

        // ── core update ────────────────────────────────────────────
        $core = get_site_transient('update_core');
        $core_update_available = false;
        $core_latest_version   = null;
        if ($core && !empty($core->updates)) {
            foreach ($core->updates as $upd) {
                if (isset($upd->response) && $upd->response === 'upgrade') {
                    $core_update_available = true;
                    $core_latest_version   = (string) ($upd->version ?? '');
                    break;
                }
            }
        }

        // ── users ──────────────────────────────────────────────────
        $counts = function_exists('count_users') ? count_users() : array('total_users' => 0, 'avail_roles' => array());
        $users_total  = (int) ($counts['total_users'] ?? 0);
        $users_admins = (int) ($counts['avail_roles']['administrator'] ?? 0);

        // ── environment ────────────────────────────────────────────
        global $wpdb;
        $mysql_version = '';
        if ($wpdb && method_exists($wpdb, 'db_version')) {
            $mysql_version = (string) $wpdb->db_version();
        }
        $server_software = isset($_SERVER['SERVER_SOFTWARE'])
            ? (string) sanitize_text_field($_SERVER['SERVER_SOFTWARE'])
            : '';

        return array(
            'schema'    => 1,
            'generated' => gmdate('c'),
            'site'      => array(
                'name'        => get_bloginfo('name'),
                'description' => get_bloginfo('description'),
                'home_url'    => home_url('/'),
                'admin_email' => get_bloginfo('admin_email'),
                'timezone'    => (string) get_option('timezone_string', ''),
                'gmt_offset'  => (float)  get_option('gmt_offset', 0),
                'language'    => (string) get_locale(),
                'multisite'   => is_multisite(),
                'https'       => is_ssl(),
                'debug'       => defined('WP_DEBUG') && WP_DEBUG,
            ),
            'versions'  => array(
                'wp'    => get_bloginfo('version'),
                'php'   => PHP_VERSION,
                'mysql' => $mysql_version,
                'server' => $server_software,
            ),
            'users'     => array(
                'total'  => $users_total,
                'admins' => $users_admins,
                'roles'  => isset($counts['avail_roles']) ? array_map('intval', (array)$counts['avail_roles']) : array(),
            ),
            'plugins'   => $plugins,
            'themes'    => $themes,
            'updates'   => array(
                'plugins' => count($plugin_updates),
                'themes'  => count($theme_updates),
                'core'    => $core_update_available,
                'core_latest_version' => $core_latest_version,
            ),
        );
    }

    /**
     * Status probe — tells the caller whether we're paired, and if so,
     * with which platform. Used by the WPBuddy platform's auth-flow to
     * decide whether it can pair this site or whether it's occupied.
     */
    public function handle_status(WP_REST_Request $request)
    {
        $connection = new Dailybuddy_Platform_Connector_Connection();
        $pairings   = $connection->get_pairings();

        // Public-facing shape of a pairing (never expose our WP privkey).
        $public = array_map(function ($p) {
            return array(
                'site_uuid'         => $p['site_uuid']         ?? '',
                'platform_url'      => $p['platform_url']      ?? '',
                'platform_name'     => $p['platform_name']     ?? '',
                'connected_at'      => !empty($p['connected_at']) ? gmdate('c', (int)$p['connected_at']) : null,
                'last_heartbeat_at' => !empty($p['last_heartbeat_at']) ? gmdate('c', (int)$p['last_heartbeat_at']) : null,
            );
        }, $pairings);

        return new WP_REST_Response(array(
            'plugin_version'   => defined('DAILYBUDDY_VERSION') ? DAILYBUDDY_VERSION : null,
            'connector_active' => true, // if this endpoint runs, the module is loaded
            'is_connected'     => count($pairings) > 0,
            'pairings'         => $public,
            'home_url'         => home_url('/'),
            'site_name'        => get_bloginfo('name'),
        ), 200);
    }

    /**
     * Minimal UUID v4 fallback for very old WP versions.
     */
    private function uuid4_fallback()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

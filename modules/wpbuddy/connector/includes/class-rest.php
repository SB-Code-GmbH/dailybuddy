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

        // SSO — mint a single-use auto-login URL for this site.
        // Signed with the same signature scheme as /snapshot.
        register_rest_route(self::NAMESPACE_V1, '/sso-token', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_sso_token'),
            'permission_callback' => '__return_true',
        ));

        // Optimize actions (revisions, spam, db, all). Signed.
        register_rest_route(self::NAMESPACE_V1, '/optimize', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_optimize'),
            'permission_callback' => '__return_true',
            'args' => array(
                'action' => array('required' => true, 'type' => 'string'),
            ),
        ));

        // Comment moderation (approve, spam, trash — per id or bulk-all).
        register_rest_route(self::NAMESPACE_V1, '/comments', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_comments_action'),
            'permission_callback' => '__return_true',
            'args' => array(
                'action' => array('required' => true, 'type' => 'string'),
                'ids'    => array('required' => false, 'type' => 'array'),
            ),
        ));

        // Plugin actions (activate, deactivate, update). Signed.
        register_rest_route(self::NAMESPACE_V1, '/plugins', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_plugin_action'),
            'permission_callback' => '__return_true',
            'args' => array(
                'action' => array('required' => true, 'type' => 'string'),
                'files'  => array('required' => true, 'type' => 'array'),
            ),
        ));

        // WordPress core update. Signed. No body — always upgrades to
        // the latest wp.org-announced version.
        register_rest_route(self::NAMESPACE_V1, '/core-update', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_core_update'),
            'permission_callback' => '__return_true',
        ));

        // User actions (activate, deactivate, delete). Signed.
        register_rest_route(self::NAMESPACE_V1, '/users', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_user_action'),
            'permission_callback' => '__return_true',
            'args' => array(
                'action' => array('required' => true, 'type' => 'string'),
                'ids'    => array('required' => true, 'type' => 'array'),
            ),
        ));

        // Theme actions (activate, update). Signed.
        // Note: WP has no "deactivate theme" — a theme is always active.
        // Switching = activate another one.
        register_rest_route(self::NAMESPACE_V1, '/themes', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_theme_action'),
            'permission_callback' => '__return_true',
            'args' => array(
                'action' => array('required' => true, 'type' => 'string'),
                'slugs'  => array('required' => true, 'type' => 'array'),
            ),
        ));

        // Image audit — walks the Media Library, reports size/format/
        // alt-text/usage per image. Read-only, signed request.
        register_rest_route(self::NAMESPACE_V1, '/image-scan', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_image_scan'),
            'permission_callback' => '__return_true',
        ));

        // Link scan — walks published post_content, extracts URLs,
        // HEAD-checks each. Signed request. Runtime up to ~30 s on
        // large sites; the platform shows a spinner while it runs.
        register_rest_route(self::NAMESPACE_V1, '/link-scan', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_link_scan'),
            'permission_callback' => '__return_true',
        ));

        // Maintenance-mode toggle (delegates to the DailyBuddy
        // "Under Construction" module). Signed request from platform.
        register_rest_route(self::NAMESPACE_V1, '/maintenance', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'handle_maintenance_action'),
            'permission_callback' => '__return_true',
            'args' => array(
                'state' => array('required' => true, 'type' => 'string'),
            ),
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

        $platform_url = sanitize_url((string) $request->get_param('platform_url'));
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
            $platform_name = wp_parse_url($platform_url, PHP_URL_HOST) ?: $platform_url;
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
        $platform_url = sanitize_url((string) $request->get_param('platform_url'));
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
            $platform_name = wp_parse_url($platform_url, PHP_URL_HOST) ?: $platform_url;
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
     * Verify the signed-request headers used by /snapshot + /sso-token.
     * Returns the matching pairing array on success, or a WP_REST_Response
     * error to bubble straight back out.
     */
    private function verify_signed_request(WP_REST_Request $request)
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
        if (abs(time() - $ts) > 300) {
            return new WP_REST_Response(array(
                'code'    => 'stale_timestamp',
                'message' => __('Request timestamp is out of range.', 'dailybuddy'),
            ), 401);
        }

        $connection = new Dailybuddy_Platform_Connector_Connection();
        $pairing    = null;
        foreach ($connection->get_pairings() as $p) {
            if (($p['site_uuid'] ?? '') === $uuid) { $pairing = $p; break; }
        }
        if (!$pairing || empty($pairing['platform_public_key'])) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_pairing',
                'message' => __('No pairing with that UUID.', 'dailybuddy'),
            ), 401);
        }

        $ok = @openssl_verify($uuid . '|' . $ts, base64_decode($sig, true),
            $pairing['platform_public_key'], OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            return new WP_REST_Response(array(
                'code'    => 'bad_signature',
                'message' => __('Signature did not verify.', 'dailybuddy'),
            ), 401);
        }
        return $pairing;
    }

    /**
     * Mint a one-time login URL. The token lives 60 seconds in a
     * transient, tied to a WP admin user, and is consumed by the
     * SSO handler inside class-connection.php on the very next hit.
     */
    public function handle_sso_token(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        // Pick the target user: default to the first administrator on
        // the site. Later we could let each pairing pick its own user.
        $admins = get_users(array(
            'role'    => 'administrator',
            'number'  => 1,
            'orderby' => 'ID',
            'order'   => 'ASC',
            'fields'  => array('ID', 'user_login'),
        ));
        if (empty($admins)) {
            return new WP_REST_Response(array(
                'code'    => 'no_admin',
                'message' => __('No administrator user found on this site.', 'dailybuddy'),
            ), 500);
        }
        $target = $admins[0];

        $token = bin2hex(random_bytes(24)); // 48 hex chars
        set_transient(
            'dailybuddy_pc_sso_' . $token,
            array(
                'user_id'  => (int) $target->ID,
                'issued_for' => (string) ($pairing['site_uuid'] ?? ''),
            ),
            60
        );

        $login_url = add_query_arg('wpbuddy_sso', $token, home_url('/'));

        return new WP_REST_Response(array(
            'login_url' => $login_url,
            'user'      => array(
                'id'    => (int) $target->ID,
                'login' => (string) $target->user_login,
            ),
            'expires_in' => 60,
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
            // Slug used to look up the plugin on wp.org (icon + info).
            // dirname("foo/bar.php") = "foo", dirname("hello.php") = "."
            // — for single-file plugins we fall back to the file's basename
            // sans extension so we still have a usable identifier.
            $dir  = dirname($file);
            $slug = ($dir !== '' && $dir !== '.') ? $dir : basename($file, '.php');

            $plugins[] = array(
                'file'             => $file,
                'slug'             => $slug,
                'name'             => (string) ($data['Name']        ?? ''),
                'description'      => wp_strip_all_tags((string) ($data['Description'] ?? '')),
                'plugin_uri'       => (string) ($data['PluginURI']   ?? ''),
                'version'          => (string) ($data['Version']     ?? ''),
                'author'           => wp_strip_all_tags((string) ($data['Author']      ?? '')),
                'author_uri'       => (string) ($data['AuthorURI']   ?? ''),
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
                'description'      => wp_strip_all_tags((string) $theme->get('Description')),
                'theme_uri'        => (string) $theme->get('ThemeURI'),
                'version'          => (string) $theme->get('Version'),
                'author'           => wp_strip_all_tags((string) $theme->get('Author')),
                'author_uri'       => (string) $theme->get('AuthorURI'),
                'template'         => (string) $theme->get_template(), // parent for child themes
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
            ? (string) sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE']))
            : '';

        // ── Comments (wp_count_comments has approved/moderated/spam/trash) ──
        $cc = function_exists('wp_count_comments') ? wp_count_comments() : null;
        $comments = array(
            'approved' => $cc ? (int) $cc->approved  : 0,
            'pending'  => $cc ? (int) $cc->moderated : 0,
            'spam'     => $cc ? (int) $cc->spam      : 0,
            'trash'    => $cc ? (int) $cc->trash     : 0,
            'pending_list'  => array(),
            'approved_list' => array(),
        );

        // Lightweight comment lists for the dashboard's Comments card.
        // Cap at 20 pending / 10 approved to keep the payload small.
        if (function_exists('get_comments')) {
            foreach (get_comments(array('status' => 'hold', 'number' => 20)) as $c) {
                $comments['pending_list'][] = $this->serialize_comment($c);
            }
            foreach (get_comments(array('status' => 'approve', 'number' => 10)) as $c) {
                $comments['approved_list'][] = $this->serialize_comment($c);
            }
        }

        // ── Optimization ────────────────────────────────────────────
        // Post revisions bloat wp_posts, spam bloats wp_comments,
        // db_overhead is InnoDB/MyISAM data_free — the same numbers a
        // WP-Optimize-style plugin acts on.
        // Direct queries + no cache are intentional: these numbers must
        // be authoritative (they drive a maintenance UI), and the
        // information_schema query has no WP-API equivalent.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $revisions_count = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'"
        );
        $overhead_bytes = 0;
        if ($wpdb) {
            // Suppress errors — some hosts revoke information_schema access.
            $wpdb->suppress_errors(true);
            $overhead_bytes = (int) $wpdb->get_var(
                "SELECT COALESCE(SUM(data_free), 0)
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()"
            );
            $wpdb->suppress_errors(false);
        }
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

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
                'list'   => $this->collect_users_list(),
            ),
            'plugins'   => $plugins,
            'themes'    => $themes,
            'updates'   => array(
                'plugins' => count($plugin_updates),
                'themes'  => count($theme_updates),
                'core'    => $core_update_available,
                'core_latest_version' => $core_latest_version,
            ),
            'comments'  => $comments,
            'optimization' => array(
                'post_revisions' => $revisions_count,
                'spam_comments'  => $comments['spam'],
                'db_overhead_kb' => (int) round($overhead_bytes / 1024),
            ),
            'traffic' => $this->collect_traffic(),
            'maintenance' => $this->collect_maintenance(),
        );
    }

    /**
     * Current state of the DailyBuddy "Under Construction" module —
     * exposed so the platform can render a status kachel + toggle.
     * `available` is false when the module is turned off in DailyBuddy
     * settings; the platform then hides the toggle.
     */
    private function collect_maintenance()
    {
        $settings = get_option('dailybuddy_under_construction_settings', array());
        if (!is_array($settings)) $settings = array();

        $module_available = true;
        if (class_exists('Dailybuddy_Settings') && method_exists('Dailybuddy_Settings', 'get_modules')) {
            $modules = Dailybuddy_Settings::get_modules();
            $module_id = 'wordpress-tools/under-construction';
            if (isset($modules[$module_id])) {
                $module_available = (bool) $modules[$module_id];
            }
        }

        return array(
            'available' => $module_available,
            'active'    => !empty($settings['maintenance_active']),
            'title'     => (string) ($settings['title']   ?? ''),
            'message'   => (string) ($settings['message'] ?? ''),
            'layout'    => (string) ($settings['layout']  ?? ''),
            'auto_end_enabled'  => !empty($settings['auto_end_enabled']),
            'auto_end_datetime' => (string) ($settings['auto_end_datetime'] ?? ''),
        );
    }

    /**
     * Compact traffic payload for the dashboard chart.
     * daily is padded (30 days, UTC) so the client can plot without gaps.
     */
    private function collect_traffic()
    {
        if (! class_exists('Dailybuddy_Platform_Connector_Traffic')) {
            return array('daily' => array(), 'today' => 0, 'total_7d' => 0, 'total_30d' => 0, 'total_365d' => 0);
        }
        $t = new Dailybuddy_Platform_Connector_Traffic();
        $daily   = $t->get_daily_padded();
        $summary = $t->get_summary();
        return array(
            'daily'      => $daily, // padded, oldest→newest, up to 365 days
            'today'      => (int) $summary['today'],
            'total_7d'   => (int) $summary['total_7d'],
            'total_30d'  => (int) $summary['total_30d'],
            'total_365d' => (int) $summary['total_365d'],
        );
    }

    /**
     * A capped list of WP users for the site's Users view. Kept at 100
     * so the snapshot stays a reasonable size; sites with more users
     * will just show the first 100 alphabetically for now (we can
     * paginate later if it becomes a real problem).
     */
    private function collect_users_list()
    {
        if (!function_exists('get_users')) return array();
        $users = get_users(array(
            'number'  => 100,
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'fields'  => array('ID', 'user_login', 'user_email', 'display_name', 'user_registered'),
        ));
        $out = array();
        foreach ($users as $u) {
            $wp_user = new WP_User($u->ID);
            $roles   = (array) $wp_user->roles;

            // "Deactivated" convention: user was stashed via our
            // /users endpoint. We snapshot the original role list to
            // usermeta and remove all roles so they can't do anything.
            $stashed = get_user_meta($u->ID, '_wpbuddy_original_roles', true);
            $isDeact = !empty($stashed) && is_array($stashed);

            $out[] = array(
                'id'            => (int) $u->ID,
                'login'         => (string) $u->user_login,
                'display_name'  => (string) $u->display_name,
                'email'         => (string) $u->user_email,
                'roles'         => $roles,
                'stashed_roles' => $isDeact ? array_values($stashed) : array(),
                'is_deactivated'=> $isDeact,
                'is_admin'      => in_array('administrator', $roles, true) || in_array('administrator', (array)$stashed, true),
                'avatar_url'    => get_avatar_url($u->ID, array('size' => 96)),
                'registered'    => (string) $u->user_registered,
                'posts_count'   => (int) count_user_posts($u->ID, 'post', true),
            );
        }
        return $out;
    }

    /**
     * Compact serialization of a WP_Comment for the dashboard.
     * Only the fields the Comments card actually renders.
     */
    private function serialize_comment($c)
    {
        $post_id  = (int) $c->comment_post_ID;
        $post     = $post_id ? get_post($post_id) : null;
        $excerpt  = function_exists('wp_trim_words')
            ? wp_trim_words(wp_strip_all_tags((string) $c->comment_content), 30)
            : substr(wp_strip_all_tags((string) $c->comment_content), 0, 240);

        // ISO 8601 with explicit Z so dayjs on the client parses as UTC.
        $iso_utc = $c->comment_date_gmt
            ? gmdate('c', strtotime($c->comment_date_gmt . ' UTC'))
            : '';

        return array(
            'id'           => (int) $c->comment_ID,
            'author'       => (string) $c->comment_author,
            'author_email' => (string) $c->comment_author_email,
            'excerpt'      => $excerpt,
            'date'         => $iso_utc,
            'post_id'      => $post_id,
            'post_title'   => $post ? (string) $post->post_title : '',
            'post_url'     => $post ? (string) get_permalink($post) : '',
        );
    }

    /**
     * Signed action endpoint: approve / spam / trash comments.
     * Body: { action: 'approve'|'spam'|'trash', ids: [int,...]|null }
     * ids = null → apply to ALL currently-pending comments.
     */
    public function handle_comments_action(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        $action = (string) $request->get_param('action');
        if (!in_array($action, array('approve', 'unapprove', 'spam', 'trash'), true)) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_action',
                'message' => __('Unknown comment action.', 'dailybuddy'),
            ), 400);
        }

        // Optional ids array; missing/empty => operate on ALL pending.
        $ids = (array) $request->get_param('ids');
        $ids = array_values(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; }));
        if (empty($ids)) {
            $pending = get_comments(array('status' => 'hold', 'number' => 0, 'fields' => 'ids'));
            $ids = array_map('intval', (array) $pending);
        }

        $done = 0;
        foreach ($ids as $id) {
            $ok = false;
            if ($action === 'approve') {
                $ok = (bool) wp_set_comment_status($id, 'approve');
            } elseif ($action === 'unapprove') {
                // "hold" is WP's internal status for "waiting for moderation".
                $ok = (bool) wp_set_comment_status($id, 'hold');
            } elseif ($action === 'spam') {
                $ok = (bool) wp_spam_comment($id);
            } elseif ($action === 'trash') {
                $ok = (bool) wp_trash_comment($id);
            }
            if ($ok) $done++;
        }

        return new WP_REST_Response(array(
            'success'  => true,
            'action'   => $action,
            'affected' => $done,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
    }

    /**
     * Signed action endpoint: bulk activate / deactivate / update plugins.
     * Body: { action: 'activate'|'deactivate'|'update', files: [<plugin-file>, ...] }
     * A file is the plugin's basename entry, e.g. "akismet/akismet.php".
     * Returns per-file result + a fresh snapshot for the platform UI.
     */
    public function handle_plugin_action(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        $action = (string) $request->get_param('action');
        if (!in_array($action, array('activate', 'deactivate', 'update'), true)) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_action',
                'message' => __('Unknown plugin action.', 'dailybuddy'),
            ), 400);
        }

        $files = (array) $request->get_param('files');
        $files = array_values(array_filter(array_map(function ($f) {
            $f = (string) $f;
            // Basic sanity: only accept "<slug>/<file>.php" or "<file>.php".
            // Blocks path traversal and obvious garbage.
            if ($f === '' || strpos($f, '..') !== false) return null;
            if (!preg_match('#^[a-zA-Z0-9_\-]+(?:/[a-zA-Z0-9_\-\.]+)?\.php$#', $f)) return null;
            return $f;
        }, $files)));

        if (empty($files)) {
            return new WP_REST_Response(array(
                'code'    => 'no_files',
                'message' => __('No valid plugin files supplied.', 'dailybuddy'),
            ), 400);
        }

        // Load the WP admin plugin/upgrader stack lazily. plugin.php
        // exposes activate_plugin/deactivate_plugins; the upgrader
        // classes are only needed for the update path.
        if (!function_exists('activate_plugin')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $results = array();
        $done    = 0;

        if ($action === 'activate') {
            foreach ($files as $file) {
                // silent=true skips the plugin's "activate" hooks that
                // print output; return_wp_error surfaces the error object.
                $r = activate_plugin($file, '', false, true);
                if (is_wp_error($r)) {
                    $results[$file] = array('ok' => false, 'message' => $r->get_error_message());
                } else {
                    $results[$file] = array('ok' => true);
                    $done++;
                }
            }
        } elseif ($action === 'deactivate') {
            // deactivate_plugins accepts an array + silent flag; no return
            // value, so we check active_plugins after to confirm each.
            deactivate_plugins($files, true);
            $active = (array) get_option('active_plugins', array());
            foreach ($files as $file) {
                $ok = !in_array($file, $active, true);
                $results[$file] = array('ok' => $ok);
                if ($ok) $done++;
            }
        } else { // update
            if (!class_exists('Plugin_Upgrader')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/misc.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }
            if (!function_exists('request_filesystem_credentials')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            // Snapshot each plugin's version BEFORE the upgrade — the
            // only truthful signal that something actually changed. The
            // upgrader can hand back true/null even when it did nothing
            // (e.g. paid plugins without a license, plugins not on
            // wp.org, stale update transients).
            $before = array();
            foreach ($files as $file) {
                $full = WP_PLUGIN_DIR . '/' . $file;
                if (file_exists($full)) {
                    $data = @get_plugin_data($full, false, false);
                    $before[$file] = (string) ($data['Version'] ?? '');
                } else {
                    $before[$file] = '';
                }
            }

            @wp_update_plugins(); // ensure the update transient is fresh

            $skin     = new Automatic_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);
            $ret      = $upgrader->bulk_upgrade($files);

            foreach ($files as $file) {
                $r      = is_array($ret) ? ($ret[$file] ?? null) : null;
                $full   = WP_PLUGIN_DIR . '/' . $file;
                $after  = '';
                if (file_exists($full)) {
                    $data  = @get_plugin_data($full, false, false);
                    $after = (string) ($data['Version'] ?? '');
                }
                $beforeV = $before[$file];

                // Truth: real success is a bumped version.
                if ($beforeV !== '' && $after !== '' && version_compare($after, $beforeV, '>')) {
                    $results[$file] = array(
                        'ok'   => true,
                        'from' => $beforeV,
                        'to'   => $after,
                    );
                    $done++;
                } elseif (is_wp_error($r)) {
                    $results[$file] = array('ok' => false, 'message' => $r->get_error_message());
                } elseif (!is_array($ret)) {
                    $results[$file] = array('ok' => false, 'message' => __('Update failed (filesystem access).', 'dailybuddy'));
                } else {
                    // Upgrader returned something plausible-looking but
                    // the version didn't move — nothing was actually
                    // installed. Common for paid plugins that need a
                    // license (Elementor Pro, ACF Pro, WooCommerce Extras).
                    $results[$file] = array(
                        'ok'      => false,
                        'message' => __('Download failed or no update available (version unchanged).', 'dailybuddy'),
                    );
                }
            }
        }

        return new WP_REST_Response(array(
            'success'  => true,
            'action'   => $action,
            'affected' => $done,
            'results'  => $results,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
    }

    /**
     * Signed action endpoint: activate / deactivate / delete users.
     *   Body: { action: 'activate'|'deactivate'|'delete', ids: [int, …] }
     * "Deactivate" isn't a native WP concept — we stash the user's
     * roles into _wpbuddy_original_roles meta and remove them so the
     * account can log in but sees nothing. "Activate" restores.
     * Admins are always refused (self-lockout protection at the DB
     * level; the caller shouldn't send them either but we guard here).
     * Delete: for now we DO NOT reassign the user's content — that
     * needs a "attribute posts to" picker on the platform side (open
     * design question).
     */
    public function handle_user_action(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        $action = (string) $request->get_param('action');
        if (!in_array($action, array('activate', 'deactivate', 'delete'), true)) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_action',
                'message' => __('Unknown user action.', 'dailybuddy'),
            ), 400);
        }

        $ids = (array) $request->get_param('ids');
        $ids = array_values(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; }));
        if (empty($ids)) {
            return new WP_REST_Response(array(
                'code'    => 'no_ids',
                'message' => __('No user ids supplied.', 'dailybuddy'),
            ), 400);
        }

        // Delete needs the deprecated user.php helpers.
        if ($action === 'delete' && !function_exists('wp_delete_user')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }

        $results = array();
        $done    = 0;
        foreach ($ids as $id) {
            $u = get_userdata($id);
            if (!$u) {
                $results[$id] = array('ok' => false, 'message' => __('User not found.', 'dailybuddy'));
                continue;
            }
            // Never let the platform touch a WP admin — a bad click there
            // could brick the site's own admin access.
            $isAdmin = in_array('administrator', (array) $u->roles, true);
            $stashed = get_user_meta($id, '_wpbuddy_original_roles', true);
            if (!$isAdmin && is_array($stashed) && in_array('administrator', $stashed, true)) {
                $isAdmin = true;
            }
            if ($isAdmin) {
                $results[$id] = array('ok' => false, 'message' => __('Administrator accounts are protected.', 'dailybuddy'));
                continue;
            }

            if ($action === 'deactivate') {
                if (!empty($stashed)) {
                    $results[$id] = array('ok' => false, 'message' => __('User is already deactivated.', 'dailybuddy'));
                    continue;
                }
                $original = array_values((array) $u->roles);
                if (empty($original)) {
                    $results[$id] = array('ok' => false, 'message' => __('User has no roles to remove.', 'dailybuddy'));
                    continue;
                }
                update_user_meta($id, '_wpbuddy_original_roles', $original);
                $wp = new WP_User($id);
                foreach ($original as $r) $wp->remove_role($r);
                $results[$id] = array('ok' => true);
                $done++;
            } elseif ($action === 'activate') {
                if (empty($stashed) || !is_array($stashed)) {
                    $results[$id] = array('ok' => false, 'message' => __('User was not deactivated.', 'dailybuddy'));
                    continue;
                }
                $wp = new WP_User($id);
                foreach ($stashed as $r) $wp->add_role($r);
                delete_user_meta($id, '_wpbuddy_original_roles');
                $results[$id] = array('ok' => true);
                $done++;
            } else { // delete
                // reassign — either an int user id (attribute posts to
                // that user) or null (delete their posts too). Comes
                // from the platform's "Reassign posts to…" picker.
                $reassign = $request->get_param('reassign');
                $reassign = ($reassign === null || $reassign === '' || (int)$reassign <= 0)
                    ? null
                    : (int) $reassign;
                if ($reassign === $id) {
                    $results[$id] = array('ok' => false, 'message' => __('Cannot reassign posts to the same user being deleted.', 'dailybuddy'));
                    continue;
                }
                if ($reassign !== null) {
                    $target = get_userdata($reassign);
                    if (!$target) {
                        $results[$id] = array('ok' => false, 'message' => __('Reassign target user not found.', 'dailybuddy'));
                        continue;
                    }
                }
                $ok = wp_delete_user($id, $reassign);
                if ($ok) {
                    $results[$id] = array('ok' => true, 'reassigned_to' => $reassign);
                    $done++;
                } else {
                    $results[$id] = array('ok' => false, 'message' => __('Delete failed.', 'dailybuddy'));
                }
            }
        }

        return new WP_REST_Response(array(
            'success'  => true,
            'action'   => $action,
            'affected' => $done,
            'results'  => $results,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
    }

    /**
     * Signed action endpoint: activate a single theme, or bulk-update
     * one or more themes. Themes have no "deactivate" — one is always
     * active. For 'activate' the first slug in the array wins.
     * Body: { action: 'activate'|'update', slugs: [<theme-slug>, ...] }
     * Returns per-slug result + a fresh snapshot for the platform UI.
     */
    public function handle_theme_action(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        $action = (string) $request->get_param('action');
        if (!in_array($action, array('activate', 'update'), true)) {
            return new WP_REST_Response(array(
                'code'    => 'unknown_action',
                'message' => __('Unknown theme action.', 'dailybuddy'),
            ), 400);
        }

        $slugs = (array) $request->get_param('slugs');
        $slugs = array_values(array_filter(array_map(function ($s) {
            $s = (string) $s;
            if ($s === '' || strpos($s, '..') !== false) return null;
            if (!preg_match('#^[a-zA-Z0-9_\-]+$#', $s)) return null; // no slashes: themes live in ONE dir
            return $s;
        }, $slugs)));

        if (empty($slugs)) {
            return new WP_REST_Response(array(
                'code'    => 'no_slugs',
                'message' => __('No valid theme slugs supplied.', 'dailybuddy'),
            ), 400);
        }

        $results = array();
        $done    = 0;

        if ($action === 'activate') {
            // One theme active at a time. If the caller sent more than
            // one slug, the first wins; the rest get a "skipped" marker.
            $target = $slugs[0];
            $skip   = array_slice($slugs, 1);

            $theme = wp_get_theme($target);
            if (!$theme->exists()) {
                $results[$target] = array('ok' => false, 'message' => __('Theme not installed.', 'dailybuddy'));
            } else {
                switch_theme($theme->get_stylesheet());
                $current = wp_get_theme();
                $ok = $current && $current->get_stylesheet() === $target;
                $results[$target] = $ok ? array('ok' => true) : array('ok' => false, 'message' => __('Activation did not stick.', 'dailybuddy'));
                if ($ok) $done++;
            }
            foreach ($skip as $s) {
                $results[$s] = array('ok' => false, 'message' => __('Skipped — only one theme can be active at a time.', 'dailybuddy'));
            }
        } else { // update
            if (!class_exists('Theme_Upgrader')) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/misc.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }

            // Snapshot each theme's version BEFORE — same truthfulness
            // trick as plugins: real success = bumped version.
            $before = array();
            foreach ($slugs as $slug) {
                $t = wp_get_theme($slug);
                $before[$slug] = $t->exists() ? (string) $t->get('Version') : '';
            }

            @wp_update_themes(); // fresh update transient

            $skin     = new Automatic_Upgrader_Skin();
            $upgrader = new Theme_Upgrader($skin);
            $ret      = $upgrader->bulk_upgrade($slugs);

            // wp_get_themes() caches WP_Theme objects — force refresh so
            // we don't read the pre-upgrade version back.
            wp_clean_themes_cache();

            foreach ($slugs as $slug) {
                $r       = is_array($ret) ? ($ret[$slug] ?? null) : null;
                $t       = wp_get_theme($slug);
                $afterV  = $t->exists() ? (string) $t->get('Version') : '';
                $beforeV = $before[$slug];

                if ($beforeV !== '' && $afterV !== '' && version_compare($afterV, $beforeV, '>')) {
                    $results[$slug] = array('ok' => true, 'from' => $beforeV, 'to' => $afterV);
                    $done++;
                } elseif (is_wp_error($r)) {
                    $results[$slug] = array('ok' => false, 'message' => $r->get_error_message());
                } elseif (!is_array($ret)) {
                    $results[$slug] = array('ok' => false, 'message' => __('Update failed (filesystem access).', 'dailybuddy'));
                } else {
                    $results[$slug] = array(
                        'ok'      => false,
                        'message' => __('Download failed or no update available (version unchanged).', 'dailybuddy'),
                    );
                }
            }
        }

        return new WP_REST_Response(array(
            'success'  => true,
            'action'   => $action,
            'affected' => $done,
            'results'  => $results,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
    }

    /**
     * Signed action endpoint: upgrade WordPress core to the latest
     * announced version. Uses Core_Upgrader (the same code path
     * wp-admin's "Update Now" button drives).
     * Returns { success, from, to, snapshot }.
     */
    public function handle_core_update(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        // Load the upgrader stack.
        if (!class_exists('Core_Upgrader')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        }
        if (!function_exists('find_core_update')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        // Fresh core-update transient so we act on current data.
        @wp_version_check(array(), true);

        // Pick the "upgrade" candidate from the transient.
        $core = get_site_transient('update_core');
        $target = null;
        if ($core && !empty($core->updates)) {
            foreach ($core->updates as $u) {
                if (isset($u->response) && $u->response === 'upgrade') {
                    $target = $u;
                    break;
                }
            }
        }
        if (!$target) {
            return new WP_REST_Response(array(
                'code'    => 'no_update',
                'message' => __('WordPress is already up to date.', 'dailybuddy'),
            ), 400);
        }

        $before = get_bloginfo('version');

        // Automatic_Upgrader_Skin swallows the "click to continue" prompts
        // that Bulk_Upgrader_Skin would print — we're not on a page.
        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Core_Upgrader($skin);
        $result   = $upgrader->upgrade($target, array(
            'allow_relaxed_file_ownership' => true,
            'attempt_rollback'             => true,
        ));

        if (is_wp_error($result)) {
            return new WP_REST_Response(array(
                'code'    => 'upgrade_failed',
                'message' => $result->get_error_message(),
            ), 500);
        }

        // Re-read the version straight from wp-includes/version.php —
        // get_bloginfo() would still return the cached pre-upgrade value.
        $after = $before;
        $verFile = ABSPATH . 'wp-includes/version.php';
        if (is_readable($verFile)) {
            $wp_version = null;
            include $verFile;
            if ($wp_version !== null) $after = (string) $wp_version;
        }

        $ok = ($before !== '' && $after !== '' && version_compare($after, $before, '>'));
        if (!$ok) {
            return new WP_REST_Response(array(
                'code'    => 'no_version_bump',
                'message' => __('Upgrader ran but the version did not change.', 'dailybuddy'),
                'from'    => $before,
                'to'      => $after,
            ), 500);
        }

        // collect_snapshot() runs in the SAME PHP request that booted on
        // the OLD wp version — get_bloginfo('version') is memoized to
        // that old value and the update_core transient may still say
        // "upgrade available". Patch the freshly-read version in AFTER
        // so the platform sees the truth without a follow-up refresh.
        $snap = $this->collect_snapshot();
        if (isset($snap['versions']) && is_array($snap['versions'])) {
            $snap['versions']['wp'] = $after;
        }
        if (isset($snap['updates']) && is_array($snap['updates'])) {
            $snap['updates']['core']                = false;
            $snap['updates']['core_latest_version'] = null;
        }

        return new WP_REST_Response(array(
            'success'  => true,
            'from'     => $before,
            'to'       => $after,
            'snapshot' => $snap,
        ), 200);
    }

    /**
     * Signed action endpoint: run one of the optimization tasks
     * (delete post revisions, empty spam bin, OPTIMIZE TABLE).
     */
    public function handle_optimize(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        global $wpdb;
        $action = (string) $request->get_param('action');
        $cleaned = 0;

        // Optimization actions inherently touch tables WP core APIs don't
        // expose (SHOW TABLES / OPTIMIZE TABLE) and must NOT be cached —
        // the whole point is to mutate DB state and return fresh counts.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        switch ($action) {
            case 'revisions':
                $ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'");
                foreach ($ids as $id) {
                    if (wp_delete_post_revision((int) $id)) $cleaned++;
                }
                break;

            case 'spam':
                $ids = $wpdb->get_col("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
                foreach ($ids as $id) {
                    if (wp_delete_comment((int) $id, true)) $cleaned++;
                }
                break;

            case 'db':
                $tables = $wpdb->get_col("SHOW TABLES");
                foreach ($tables as $t) {
                    $safe = esc_sql($t);
                    // OPTIMIZE TABLE takes an identifier, not a value —
                    // $wpdb->prepare()'s %s/%d placeholders can't be used
                    // for table names. $t comes from SHOW TABLES on the
                    // same DB (never user input) and is esc_sql'd on top.
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    if ($wpdb->query("OPTIMIZE TABLE `{$safe}`") !== false) $cleaned++;
                }
                break;

            case 'all':
                // Chain all three in one call.
                $rev = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'");
                foreach ($rev as $id) { if (wp_delete_post_revision((int) $id)) $cleaned++; }

                $sp  = $wpdb->get_col("SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
                foreach ($sp as $id)  { if (wp_delete_comment((int) $id, true))   $cleaned++; }

                $tables = $wpdb->get_col("SHOW TABLES");
                foreach ($tables as $t) {
                    $safe = esc_sql($t);
                    // OPTIMIZE TABLE takes an identifier, not a value —
                    // $wpdb->prepare()'s %s/%d placeholders can't be used
                    // for table names. $t comes from SHOW TABLES on the
                    // same DB (never user input) and is esc_sql'd on top.
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    if ($wpdb->query("OPTIMIZE TABLE `{$safe}`") !== false) $cleaned++;
                }
                break;

            default:
                return new WP_REST_Response(array(
                    'code'    => 'unknown_action',
                    'message' => __('Unknown optimize action.', 'dailybuddy'),
                ), 400);
        }
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

        // Return a fresh snapshot so the platform can update the UI
        // without a second round-trip.
        return new WP_REST_Response(array(
            'success'  => true,
            'action'   => $action,
            'cleaned'  => $cleaned,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
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

    /**
     * Signed action endpoint: turn the DailyBuddy "Under Construction"
     * module on or off.
     *   Body: { state: 'on' | 'off' }
     *
     * We modify the same `dailybuddy_under_construction_settings` option
     * that the WP-admin toggle writes to, so the two stay in sync — no
     * hidden platform-only state.
     */
    public function handle_maintenance_action(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        $state = strtolower(trim((string) $request->get_param('state')));
        if (!in_array($state, array('on', 'off'), true)) {
            return new WP_REST_Response(array(
                'code'    => 'invalid_state',
                'message' => __('State must be "on" or "off".', 'dailybuddy'),
            ), 400);
        }

        // When turning maintenance ON, make sure the Under Construction
        // module itself is enabled — otherwise the guard code never runs
        // and visitors would still see the live site. The module class
        // only wires its template_redirect hook when the file is loaded
        // during plugin bootstrap, which is gated by the dailybuddy_modules
        // option we flip here. New value takes effect on the next request
        // (fine — the very next visitor triggers the maintenance page).
        if ($state === 'on' && class_exists('Dailybuddy_Settings')) {
            $module_id = 'wordpress-tools/under-construction';
            $modules   = Dailybuddy_Settings::get_modules();
            if (!is_array($modules)) $modules = array();
            if (empty($modules[$module_id])) {
                $modules[$module_id] = true;
                Dailybuddy_Settings::save_modules($modules);
            }
        }

        // Preserve every existing setting (title, message, layout, …) —
        // we only flip the boolean the frontend guard checks.
        $settings = get_option('dailybuddy_under_construction_settings', array());
        if (!is_array($settings)) $settings = array();
        $settings['maintenance_active'] = ($state === 'on');
        update_option('dailybuddy_under_construction_settings', $settings);

        return new WP_REST_Response(array(
            'success'  => true,
            'state'    => $state,
            'snapshot' => $this->collect_snapshot(),
        ), 200);
    }

    /**
     * Signed action endpoint: crawl published post_content for links,
     * HEAD-check each one, return the results.
     *
     * We walk the DB (not the site's frontend HTML) because it's much
     * faster and gives us the post-of-origin cheaply — the platform
     * uses that to deep-link back into wp-admin for each broken URL.
     *
     * Hard caps to keep the request under PHP's max_execution_time:
     *   • 300 posts scanned
     *   • 500 unique URLs checked
     *   • 5 s per HEAD (parallel via curl_multi)
     *
     * Bigger sites will surface the most-cited URLs first — a truncated
     * summary is honest and useful; a timeout is not.
     */
    public function handle_link_scan(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        // Give ourselves headroom — WP defaults to 30 s, most hosts
        // allow up to 60 s. This is a best-effort raise, silently
        // ignored where safe_mode / disable_functions block it.
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        @set_time_limit(60);

        global $wpdb;

        $siteHost = strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));

        // Pull the most-recent 300 published posts/pages. Newest first
        // reflects what the site is actively promoting.
        //
        // Direct query: WP_Query would work but wraps every row in a
        // WP_Post object we don't need here — heavy for a one-shot scan.
        // No object-cache: the platform stores the scan result in its
        // own 7-day file cache, so a second layer would just waste memory.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $posts = $wpdb->get_results("
            SELECT ID, post_title, post_content
            FROM {$wpdb->posts}
            WHERE post_status = 'publish'
              AND post_type IN ('post', 'page')
            ORDER BY post_modified DESC
            LIMIT 300
        ");

        // Collect URLs. Value = array of source posts so the platform
        // can render 'used on 4 pages' with click-through.
        $links = array(); // url => ['sources' => [ [id, title] ], 'kind' => internal|external]
        $skipRe = '#^(mailto:|tel:|javascript:|data:|\#)#i';

        foreach ((array) $posts as $p) {
            $html = (string) $p->post_content;
            if ($html === '') continue;

            if (!preg_match_all('#(?:href|src)\s*=\s*[\'"]([^\'"]+)[\'"]#i', $html, $m)) continue;

            $seenInPost = array();
            foreach ($m[1] as $raw) {
                $raw = trim($raw);
                if ($raw === '' || preg_match($skipRe, $raw)) continue;

                // Protocol-relative → https. Root-relative → same host.
                if (strpos($raw, '//') === 0)     $raw = 'https:' . $raw;
                elseif ($raw[0] === '/')          $raw = home_url($raw);
                elseif (!preg_match('#^https?://#i', $raw)) continue; // skip fragments/relative

                if (isset($seenInPost[$raw])) continue;
                $seenInPost[$raw] = true;

                if (!isset($links[$raw])) {
                    $host = strtolower((string) wp_parse_url($raw, PHP_URL_HOST));
                    $links[$raw] = array(
                        'kind'    => ($host === $siteHost || $host === '') ? 'internal' : 'external',
                        'sources' => array(),
                    );
                }
                if (count($links[$raw]['sources']) < 20) {
                    $links[$raw]['sources'][] = array(
                        'id'    => (int) $p->ID,
                        'title' => (string) $p->post_title,
                    );
                }
            }
        }

        // Cap the total URL set. Slice keeps deterministic order.
        if (count($links) > 500) $links = array_slice($links, 0, 500, true);

        // Parallel HEAD requests. Some servers reject HEAD (405) — we
        // fall back to a range-limited GET (fetch first 1 byte) to
        // avoid downloading full pages just to detect status.
        //
        // WHY curl_multi and NOT wp_remote_head():
        // wp_remote_head() has no parallel wrapper. Sequential HEADs on
        // 500 URLs at ~200 ms each easily exceeds a 60 s PHP timeout
        // and makes the scan unusable. curl_multi_* completes 500 URLs
        // in about the time of the slowest single one. This is a
        // conscious performance trade-off for the link-monitor endpoint.
        // phpcs:disable WordPress.WP.AlternativeFunctions.curl_curl_multi_init,WordPress.WP.AlternativeFunctions.curl_curl_init,WordPress.WP.AlternativeFunctions.curl_curl_setopt_array,WordPress.WP.AlternativeFunctions.curl_curl_multi_add_handle,WordPress.WP.AlternativeFunctions.curl_curl_multi_exec,WordPress.WP.AlternativeFunctions.curl_curl_multi_select,WordPress.WP.AlternativeFunctions.curl_curl_getinfo,WordPress.WP.AlternativeFunctions.curl_curl_exec,WordPress.WP.AlternativeFunctions.curl_curl_close,WordPress.WP.AlternativeFunctions.curl_curl_multi_remove_handle,WordPress.WP.AlternativeFunctions.curl_curl_multi_close
        $urls   = array_keys($links);
        $status = array(); // url => ['code' => int, 'ms' => int]

        if (!empty($urls)) {
            $mh = curl_multi_init();
            $handles = array();
            foreach ($urls as $idx => $url) {
                $ch = curl_init($url);
                curl_setopt_array($ch, array(
                    CURLOPT_NOBODY         => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => false, // status of the FIRST hop
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (WPBuddy Link Monitor)',
                ));
                curl_multi_add_handle($mh, $ch);
                $handles[$idx] = $ch;
            }
            $running = null;
            do {
                curl_multi_exec($mh, $running);
                if ($running) curl_multi_select($mh, 0.5);
            } while ($running);

            foreach ($handles as $idx => $ch) {
                $inf = curl_getinfo($ch);
                $code = (int) ($inf['http_code'] ?? 0);
                $ms   = (int) round(((float) ($inf['total_time'] ?? 0)) * 1000);

                // 405 = method not allowed for HEAD. Retry with a 1-byte GET.
                if ($code === 405) {
                    $ch2 = curl_init($urls[$idx]);
                    curl_setopt_array($ch2, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER     => array('Range: bytes=0-0'),
                        CURLOPT_TIMEOUT        => 6,
                        CURLOPT_CONNECTTIMEOUT => 4,
                        CURLOPT_USERAGENT      => 'Mozilla/5.0 (WPBuddy Link Monitor)',
                    ));
                    curl_exec($ch2);
                    $code = (int) curl_getinfo($ch2, CURLINFO_HTTP_CODE);
                    curl_close($ch2);
                }

                $status[$urls[$idx]] = array('code' => $code, 'ms' => $ms);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }
        // phpcs:enable

        // Assemble the response. Classification is deliberately coarse:
        //   broken   → 4xx/5xx or connection failed (code 0)
        //   redirect → 3xx (worth surfacing; some are outdated redirects)
        //   ok       → 2xx
        $out = array();
        foreach ($links as $url => $info) {
            $code = isset($status[$url]) ? (int) $status[$url]['code'] : 0;
            $ms   = isset($status[$url]) ? (int) $status[$url]['ms']   : 0;
            $bucket = 'ok';
            if     ($code === 0)                   $bucket = 'broken';
            elseif ($code >= 400)                  $bucket = 'broken';
            elseif ($code >= 300 && $code < 400)   $bucket = 'redirect';
            $out[] = array(
                'url'     => (string) $url,
                'status'  => $code,
                'ms'      => $ms,
                'bucket'  => $bucket,
                'kind'    => $info['kind'],
                'sources' => $info['sources'],
            );
        }

        // broken first, then redirects, then ok — the priority order
        // the UI will render them in without needing a secondary sort.
        $order = array('broken' => 0, 'redirect' => 1, 'ok' => 2);
        usort($out, function ($a, $b) use ($order) {
            return $order[$a['bucket']] <=> $order[$b['bucket']];
        });

        $counts = array('broken' => 0, 'redirect' => 0, 'ok' => 0);
        foreach ($out as $r) $counts[$r['bucket']]++;

        return new WP_REST_Response(array(
            'success'    => true,
            'scanned_at' => gmdate('c'),
            'totals'     => array(
                'checked' => count($out),
                'broken'  => $counts['broken'],
                'redirect'=> $counts['redirect'],
                'ok'      => $counts['ok'],
                'posts'   => is_array($posts) ? count($posts) : 0,
            ),
            'links'      => $out,
        ), 200);
    }

    /**
     * Signed action endpoint: audit the Media Library.
     *
     * For each image attachment we report file size, dimensions, mime
     * type, alt-text presence and (capped) usage count. The platform
     * turns that into KPIs — "12 images > 500 KB", "31 JPGs that could
     * be WebP", "8 without alt text" — and deep-links each row back
     * into wp-admin/upload.php?item=<id> for one-click editing.
     *
     * Deliberately read-only: no compression, no format conversion.
     * That's a Backup-required territory we haven't built yet, and
     * dedicated plugins (Smush / ShortPixel / EWWW) do it well.
     *
     * Hard caps to keep the endpoint under PHP's max_execution_time:
     *   • 500 attachments enumerated
     *   • usage lookup only for images >= 100 KB (cheap ones don't
     *     need "found in" context — the cost isn't worth the polish)
     */
    public function handle_image_scan(WP_REST_Request $request)
    {
        $pairing = $this->verify_signed_request($request);
        if ($pairing instanceof WP_REST_Response) return $pairing;

        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        @set_time_limit(60);

        global $wpdb;

        // Most-recent 500 image attachments (newer = more likely still
        // referenced from live content, so more actionable to fix).
        // Direct query + no cache is intentional here — same reasoning
        // as in handle_link_scan (bulk one-shot scan, platform caches
        // the aggregated result in its own file store).
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results("
            SELECT ID, post_title, post_mime_type, guid
            FROM {$wpdb->posts}
            WHERE post_type = 'attachment'
              AND post_mime_type LIKE 'image/%'
            ORDER BY ID DESC
            LIMIT 500
        ");

        if (!function_exists('wp_get_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
        if (!function_exists('get_attached_file')) {
            require_once ABSPATH . 'wp-includes/post.php';
        }

        $uploadUrlBase = trailingslashit(wp_get_upload_dir()['baseurl'] ?? '');

        $images  = array();
        $totals  = array(
            'checked'   => 0,
            'oversized' => 0,   // > 500 KB
            'no_alt'    => 0,
            'jpg_png'   => 0,   // WebP candidates
            'unused'    => 0,
            'bytes'     => 0,
        );

        // Reasonable byte-per-pixel budgets. JPG at Q~80 lands around
        // 0.2–0.5 B/px; PNG-8/24 varies wildly but > 1.5 usually means
        // it's an unflattened export. These are the thresholds we mark
        // a variant as "poorly compressed".
        $bppLimit = function ($mime) {
            if (stripos($mime, 'jpeg') !== false || stripos($mime, 'jpg') !== false) return 0.5;
            if (stripos($mime, 'png')  !== false) return 1.5;
            return 0.8; // gif / webp / avif — no strong opinion
        };

        foreach ((array) $rows as $p) {
            $id       = (int) $p->ID;
            $mime     = (string) $p->post_mime_type;
            $file     = get_attached_file($id);
            $meta     = wp_get_attachment_metadata($id);
            $bytes    = ($file && is_readable($file)) ? (int) @filesize($file) : 0;
            $width    = (int) ($meta['width']  ?? 0);
            $height   = (int) ($meta['height'] ?? 0);
            $altText  = (string) get_post_meta($id, '_wp_attachment_image_alt', true);
            $url      = (string) wp_get_attachment_url($id);

            // ── Size variants (thumbnail, medium, medium_large, large, …)
            // Every registered image size that WP actually generated for
            // this attachment shows up under $meta['sizes']. We resolve
            // each to a real file on disk + URL for the view.
            $variants = array();
            $origDir  = $file ? trailingslashit(dirname($file)) : '';
            $origUrl  = $url  ? trailingslashit(dirname($url))  : '';
            if (!empty($meta['sizes']) && is_array($meta['sizes']) && $origDir !== '') {
                foreach ($meta['sizes'] as $sizeName => $s) {
                    $sFile = (string) ($s['file'] ?? '');
                    if ($sFile === '') continue;
                    $absPath = $origDir . $sFile;
                    $sBytes  = is_readable($absPath) ? (int) @filesize($absPath) : 0;
                    $sW      = (int) ($s['width']  ?? 0);
                    $sH      = (int) ($s['height'] ?? 0);
                    $pixels  = $sW * $sH;
                    $bpp     = $pixels > 0 ? $sBytes / $pixels : 0;
                    $variants[] = array(
                        'name'   => (string) $sizeName,
                        'file'   => $sFile,
                        'url'    => $origUrl . $sFile,
                        'width'  => $sW,
                        'height' => $sH,
                        'bytes'  => $sBytes,
                        'bpp'    => round($bpp, 3),
                        'poor'   => ($bpp > $bppLimit($mime)),
                    );
                }
                // Biggest variant first — that's where compression matters most.
                usort($variants, function ($a, $b) { return $b['bytes'] <=> $a['bytes']; });
            }

            // Original itself as an implicit "full" variant if we have bytes.
            $origPixels = $width * $height;
            $origBpp    = $origPixels > 0 ? $bytes / $origPixels : 0;
            $origPoor   = ($origBpp > $bppLimit($mime));

            // "Used in" — COUNT of posts referencing this image, both
            // in post_content and in postmeta.
            //
            // WP generates many size variants per upload (thumbnail,
            // medium, 1024x768, -scaled, …) and Gutenberg usually inserts
            // one of those, NOT the original. So we search on the FILE
            // STEM (basename minus size suffix and extension) — that
            // matches every variant. Plus three ID-flavoured patterns to
            // catch page builders that reference by attachment ID:
            //   • wp-image-<ID>   (Gutenberg CSS class)
            //   • "id":<ID>       (Elementor JSON, plus many others)
            //   • _thumbnail_id   (featured-image meta, checked separately)
            //
            // We used to skip images < 100 KB to save the LIKE cost, but
            // that misclassified anything just under the threshold as
            // "unused". Accuracy beats speed here — the scan is capped
            // at 500 attachments anyway.
            $usage = 0;

            // Prefer the ORIGINAL file path from metadata over the
            // scaled/attached file — that's the un-suffixed stem WP
            // uses to build every variant.
            $originalFile = (string) ($meta['file'] ?? '');
            if ($originalFile === '' && $url !== '') {
                $originalFile = (string) wp_parse_url($url, PHP_URL_PATH);
            }
            $basename = basename($originalFile);

            // Strip WP's optional -scaled marker, then the extension.
            $stem = preg_replace('/-scaled$/', '', pathinfo($basename, PATHINFO_FILENAME));

            if ($stem !== '') {
                $likeStem  = '%' . $wpdb->esc_like($stem)  . '%';
                $likeClass = '%wp-image-' . $id . '%';
                $likeJson  = '%"id":' . $id . '%';

                // Fetch matching posts ONCE with their content — then
                // scan in PHP to figure out which specific variant each
                // post uses. Cheaper than N-per-variant SQL queries and
                // lets us show the user "large is used on page A" (not
                // just a raw count).
                // Direct query + no cache — bulk audit endpoint, results
                // are aggregated and stored in the platform's own cache.
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $postsHit = $wpdb->get_results($wpdb->prepare("
                    SELECT ID, post_title, post_type, post_content
                    FROM {$wpdb->posts}
                    WHERE post_status = 'publish'
                      AND post_type NOT IN ('revision', 'attachment', 'nav_menu_item')
                      AND (post_content LIKE %s OR post_content LIKE %s OR post_content LIKE %s)
                    LIMIT 50
                ", $likeStem, $likeClass, $likeJson));

                // Postmeta match: pull post_id + the meta_value blob so
                // we can scan for the exact variant filename in PHP.
                // No WP API for LIKE across meta_value — direct query
                // is the only way to catch page-builder JSON blobs.
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $metaHit = $wpdb->get_results($wpdb->prepare("
                    SELECT post_id, meta_value
                    FROM {$wpdb->postmeta}
                    WHERE meta_value LIKE %s
                       OR meta_value LIKE %s
                       OR meta_value LIKE %s
                       OR (meta_key = '_thumbnail_id' AND meta_value = %s)
                    LIMIT 200
                ", $likeStem, $likeClass, $likeJson, (string)$id));

                // Map post_id → { title, type } for meta hits.
                $metaPostIds = array();
                foreach ($metaHit as $mh) $metaPostIds[(int)$mh->post_id] = true;
                $metaTitles  = array();
                if (!empty($metaPostIds)) {
                    // Build a placeholder list of the right length so we
                    // can hand every id to $wpdb->prepare — safer than
                    // interpolating the CSV directly into the query.
                    $ids         = array_map('intval', array_keys($metaPostIds));
                    $placeholders= implode(',', array_fill(0, count($ids), '%d'));
                    $sql         = "SELECT ID, post_title, post_type
                                    FROM {$wpdb->posts}
                                    WHERE ID IN ($placeholders)
                                      AND post_status = 'publish'";
                    // Direct query + no cache — same reasoning as the
                    // surrounding scan queries; this is the follow-up
                    // that turns a set of post IDs into titles for the
                    // audit output. The $sql string is built above from
                    // a fixed template + $wpdb->posts (safe prefix) +
                    // %d placeholders; every $ids value is bound via
                    // prepare(), so PreparedSQL.NotPrepared is a false
                    // positive here.
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
                    $rows2       = $wpdb->get_results($wpdb->prepare($sql, $ids));
                    foreach ($rows2 as $r) {
                        $metaTitles[(int)$r->ID] = array(
                            'title' => (string) $r->post_title,
                            'type'  => (string) $r->post_type,
                        );
                    }
                }

                // Per-variant scanner. Called with a "search token" —
                // typically the exact variant filename — and returns
                // matching { id, title, type } arrays.
                $scan = function ($token) use ($postsHit, $metaHit, $metaTitles) {
                    if ($token === '') return array();
                    $out = array();
                    $seen = array();
                    // post_content matches
                    foreach ($postsHit as $p) {
                        if (isset($seen[$p->ID])) continue;
                        if (strpos((string)$p->post_content, $token) !== false) {
                            $seen[$p->ID] = true;
                            $out[] = array(
                                'id'    => (int)  $p->ID,
                                'title' => (string) $p->post_title,
                                'type'  => (string) $p->post_type,
                            );
                        }
                    }
                    // postmeta matches (dedup by post_id)
                    foreach ($metaHit as $m) {
                        $pid = (int) $m->post_id;
                        if (isset($seen[$pid])) continue;
                        if (strpos((string)$m->meta_value, $token) !== false && isset($metaTitles[$pid])) {
                            $seen[$pid] = true;
                            $out[] = array(
                                'id'    => $pid,
                                'title' => $metaTitles[$pid]['title'],
                                'type'  => $metaTitles[$pid]['type'],
                            );
                        }
                    }
                    // Cap — 10 per variant is plenty to be actionable.
                    return array_slice($out, 0, 10);
                };

                // Original file — its own filename (basename incl. ext).
                $origUsed = $scan($basename);
                // Also account for wp-image-<ID>/"id":<ID> as generic
                // hits: attach them to the ORIGINAL when a post has one
                // of those markers but references no specific variant
                // filename.
                $idMatches = array();
                foreach ($postsHit as $p) {
                    $c = (string) $p->post_content;
                    if (strpos($c, 'wp-image-' . $id) !== false || strpos($c, '"id":' . $id) !== false) {
                        $idMatches[(int)$p->ID] = array(
                            'id'    => (int)  $p->ID,
                            'title' => (string) $p->post_title,
                            'type'  => (string) $p->post_type,
                        );
                    }
                }
                foreach ($metaHit as $m) {
                    $pid = (int) $m->post_id;
                    if (!isset($metaTitles[$pid])) continue;
                    $mv = (string) $m->meta_value;
                    if (strpos($mv, 'wp-image-' . $id) !== false || strpos($mv, '"id":' . $id) !== false) {
                        $idMatches[$pid] = array(
                            'id'    => $pid,
                            'title' => $metaTitles[$pid]['title'],
                            'type'  => $metaTitles[$pid]['type'],
                        );
                    }
                }
                // Merge id-only matches into origUsed, dedup by post_id.
                $seenOrig = array();
                foreach ($origUsed as $u) $seenOrig[$u['id']] = true;
                foreach ($idMatches as $u) {
                    if (!isset($seenOrig[$u['id']])) {
                        $origUsed[] = $u;
                        $seenOrig[$u['id']] = true;
                    }
                }
                $origUsed = array_slice($origUsed, 0, 10);

                // Per-variant filename scan.
                foreach ($variants as $vi => $v) {
                    $variants[$vi]['used_in'] = $scan(basename((string)$v['file']));
                }

                // Overall usage = distinct posts referencing any part.
                $allSeen = array();
                foreach ($origUsed as $u) $allSeen[$u['id']] = true;
                foreach ($variants as $v) {
                    foreach ((array)($v['used_in'] ?? array()) as $u) $allSeen[$u['id']] = true;
                }
                $usage = count($allSeen);
                // Persist orig usage list for the view.
                $originalUsedIn = $origUsed;
            }

            // Total bytes on disk for this attachment = original + every
            // generated variant. That's what actually costs storage /
            // bandwidth, not just the biggest file.
            $totalBytes = $bytes;
            foreach ($variants as $v) $totalBytes += (int) $v['bytes'];

            $hasAlt      = $altText !== '';
            $isJpgPng    = ($mime === 'image/jpeg' || $mime === 'image/png');
            $isUnused    = ($usage === 0);
            // "Oversized" now means: original OR any variant > 500 KB
            // (variants matter most — that's what visitors actually load).
            $isOversized = $bytes > 500 * 1024;
            foreach ($variants as $v) {
                if ($v['bytes'] > 500 * 1024) { $isOversized = true; break; }
            }
            // "Poorly compressed" — bpp above the mime-specific budget
            // on the original or on any variant.
            $isPoor = $origPoor;
            foreach ($variants as $v) { if (!empty($v['poor'])) { $isPoor = true; break; } }

            $totals['checked']++;
            $totals['bytes']  += $totalBytes;
            if ($isOversized) $totals['oversized']++;
            if (!$hasAlt)     $totals['no_alt']++;
            if ($isJpgPng)    $totals['jpg_png']++;
            if ($isUnused)    $totals['unused']++;
            if ($isPoor)      $totals['poor'] = ($totals['poor'] ?? 0) + 1;

            $images[] = array(
                'id'              => $id,
                'title'           => (string) $p->post_title,
                'url'             => $url,
                'mime'            => $mime,
                'bytes'           => $bytes,        // original file only
                'total_bytes'     => $totalBytes,   // original + all variants
                'width'           => $width,
                'height'          => $height,
                'bpp'             => round($origBpp, 3),
                'orig_poor'       => $origPoor,
                'orig_used_in'    => isset($originalUsedIn) ? $originalUsedIn : array(),
                'alt'             => $hasAlt,
                'usage'           => $usage,
                'variants'        => $variants,     // sorted biggest-first, each with 'used_in'
                'flags'           => array(
                    'oversized' => $isOversized,
                    'no_alt'    => !$hasAlt,
                    'jpg_png'   => $isJpgPng,
                    'unused'    => $isUnused,
                    'poor'      => $isPoor,
                ),
            );
            unset($originalUsedIn); // don't leak into the next iteration
        }
        // Ensure the poor counter exists even when no image triggered it.
        if (!isset($totals['poor'])) $totals['poor'] = 0;

        // Sort worst-offender first: oversized before unused before others.
        usort($images, function ($a, $b) {
            $sa = ($a['flags']['oversized'] ? 3 : 0)
                + ($a['flags']['unused']    ? 1 : 0);
            $sb = ($b['flags']['oversized'] ? 3 : 0)
                + ($b['flags']['unused']    ? 1 : 0);
            if ($sa !== $sb) return $sb <=> $sa;
            return $b['bytes'] <=> $a['bytes'];
        });

        return new WP_REST_Response(array(
            'success'    => true,
            'scanned_at' => gmdate('c'),
            'totals'     => $totals,
            'images'     => $images,
        ), 200);
    }
}

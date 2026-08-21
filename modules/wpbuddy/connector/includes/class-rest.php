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
}

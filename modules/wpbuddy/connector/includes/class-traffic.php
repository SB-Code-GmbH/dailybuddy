<?php

/**
 * Lightweight traffic counter for the WPBuddy Platform Connector.
 *
 * Modeled on the ManageWP worker's HitCounter/SetHitCounter pair:
 *   - Hook `wp` (fires once WP has resolved the frontend request)
 *   - Skip admin, AJAX, cron, REST, feeds, robots.txt, favicon
 *   - Skip common bots by user-agent
 *   - Respect the DNT header
 *   - Store daily totals in a single wp_option array
 *     ('YYYY-MM-DD' => hits), pruned to the last 30 days
 *
 * No custom table — one option write per unique visit-day is cheap
 * and easy to migrate/remove. Storage grows by ~10 bytes / day.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Platform_Connector_Traffic
{
    /** Option storing the daily hit array. */
    const OPT_HITS = 'dailybuddy_pc_traffic_hits';

    /** Days to retain in the per-site counter. 365 fits comfortably in a
     *  single wp_option row (~4KB) and covers the year-view on the platform. */
    const RETAIN_DAYS = 365;

    /** Bot UA fragments (lowercased). Cheap regex-free contains-check. */
    private static $bot_needles = array(
        'bot', 'crawler', 'spider', 'slurp', 'facebookexternalhit', 'facebot',
        'ia_archiver', 'yandex', 'baiduspider', 'sogou', 'exabot', 'mj12',
        'ahrefsbot', 'semrushbot', 'dotbot', 'petalbot', 'bingpreview',
        'headlesschrome', 'phantomjs', 'python-requests', 'curl/', 'wget/',
        'httpclient', 'pingdom', 'uptimerobot', 'gtmetrix', 'lighthouse',
        'chrome-lighthouse', 'w3c_validator', 'monitis', 'newrelicpinger',
    );

    public function __construct()
    {
        add_action('wp', array($this, 'maybe_count_hit'));
    }

    public function maybe_count_hit()
    {
        // Only count real frontend page views — the same guard the
        // ManageWP worker uses. WP_USE_THEMES is defined only in
        // index.php's bootstrap, so cron/rest/xmlrpc never set it.
        if (! defined('WP_USE_THEMES') || ! WP_USE_THEMES) {
            return;
        }
        if (is_admin()
            || (defined('DOING_AJAX')     && DOING_AJAX)
            || (defined('DOING_CRON')     && DOING_CRON)
            || (defined('REST_REQUEST')   && REST_REQUEST)
            || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
            return;
        }
        if (function_exists('is_feed') && is_feed()) {
            return;
        }
        if (function_exists('is_robots') && is_robots()) {
            return;
        }
        if (function_exists('is_favicon') && is_favicon()) {
            return;
        }

        // Respect Do-Not-Track.
        if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1') {
            return;
        }

        // Simple bot filter.
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string) $_SERVER['HTTP_USER_AGENT']) : '';
        if ($ua === '') {
            // No UA at all is nearly always a bot / probe — skip.
            return;
        }
        foreach (self::$bot_needles as $needle) {
            if (strpos($ua, $needle) !== false) {
                return;
            }
        }

        // Site owner viewing their own pages shouldn't inflate counts —
        // but only admins get filtered, so editor/author clicks still count.
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }

        $this->increment();
    }

    /**
     * Increment today's counter, prune anything older than RETAIN_DAYS.
     * Kept public so a WP-CLI seeder or unit test can call it directly.
     */
    public function increment($count = 1)
    {
        $today = gmdate('Y-m-d');
        $hits  = get_option(self::OPT_HITS, array());
        if (! is_array($hits)) {
            $hits = array();
        }

        if (! isset($hits[$today])) {
            $hits[$today] = 0;

            // Prune older keys on a fresh-day boundary — the only time
            // pruning matters, and cheaper than doing it on every hit.
            ksort($hits);
            $cutoff = gmdate('Y-m-d', time() - (self::RETAIN_DAYS * DAY_IN_SECONDS));
            foreach ($hits as $date => $_v) {
                // Also drop malformed keys defensively.
                if ($date < $cutoff || strlen($date) !== 10) {
                    unset($hits[$date]);
                }
            }
        }

        $hits[$today] += (int) $count;

        // autoload=no — a single-row option that changes on every hit
        // shouldn't sit in the alloptions cache and get busted constantly.
        update_option(self::OPT_HITS, $hits, false);
    }

    /**
     * Return the daily hit map, padded so every day in the last
     * RETAIN_DAYS window is present (missing days => 0). This keeps
     * the client chart from having to fill blanks.
     *
     * @return array<string,int> ['YYYY-MM-DD' => hits, ...] oldest→newest
     */
    public function get_daily_padded()
    {
        $raw = get_option(self::OPT_HITS, array());
        if (! is_array($raw)) {
            $raw = array();
        }

        $out = array();
        $day = time() - ((self::RETAIN_DAYS - 1) * DAY_IN_SECONDS);
        for ($i = 0; $i < self::RETAIN_DAYS; $i++) {
            $key = gmdate('Y-m-d', $day + ($i * DAY_IN_SECONDS));
            $out[$key] = isset($raw[$key]) ? (int) $raw[$key] : 0;
        }
        return $out;
    }

    /**
     * Sum + today's count for the snapshot summary. Callers use this
     * to render the "KPI" number without walking the daily array.
     */
    public function get_summary()
    {
        $daily = $this->get_daily_padded();
        $today = gmdate('Y-m-d');
        return array(
            'today'     => isset($daily[$today]) ? (int) $daily[$today] : 0,
            'total_7d'  => array_sum(array_slice($daily, -7, 7, true)),
            'total_30d' => array_sum(array_slice($daily, -30, 30, true)),
            'total_365d' => array_sum($daily),
        );
    }
}

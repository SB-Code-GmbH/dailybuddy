<?php

/**
 * Module: Pageviews Dashboard Widget
 *
 * Renders a WP-Admin dashboard widget with a 30-day line chart and
 * today/7d/30d totals. Reads directly from the option the WPBuddy
 * connector's traffic counter writes to (`dailybuddy_pc_traffic_hits`).
 * No new HTTP calls, no external analytics.
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_Pageviews_Widget
{
    /** Same option the connector's traffic counter writes to. */
    const OPT_HITS = 'dailybuddy_pc_traffic_hits';

    /** Days retained by the counter — same as the connector's traffic class. */
    const RETAIN_DAYS = 365;

    public function __construct()
    {
        add_action('wp_dashboard_setup',   array($this, 'add_dashboard_widget'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }

    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dailybuddy_pageviews',
            __('Pageviews', 'dailybuddy'),
            array($this, 'render_widget')
        );
    }

    public function enqueue_styles($hook)
    {
        if ($hook !== 'index.php') {
            return;
        }
        wp_enqueue_style(
            'dailybuddy-pageviews',
            DAILYBUDDY_URL . 'modules/dashboard-widgets/pageviews/assets/style.css',
            array(),
            DAILYBUDDY_VERSION
        );
    }

    public function render_widget()
    {
        // Full padded history for the JS chart (up to 365 days).
        $daily365 = $this->get_daily_padded(self::RETAIN_DAYS);
        $today    = gmdate('Y-m-d');
        $todayN   = isset($daily365[$today]) ? (int) $daily365[$today] : 0;
        $sum7     = array_sum(array_slice($daily365, -7,  7,  true));
        $sum30    = array_sum(array_slice($daily365, -30, 30, true));
        $hasData  = array_sum($daily365) > 0;

        // JS-friendly triples [date, hits, localizedDate].
        $jsDaily = array();
        $dateFmt = get_option('date_format', 'M j');
        foreach ($daily365 as $d => $h) {
            $jsDaily[] = array(
                (string) $d,
                (int) $h,
                (string) date_i18n($dateFmt, strtotime($d . ' UTC')),
            );
        }
        ?>
        <div class="dailybuddy-pv-grid">

            <div class="pv-stat-card today">
                <div class="pv-stat-header">
                    <span class="pv-stat-icon dashicons dashicons-visibility"></span>
                    <div class="pv-stat-title"><?php esc_html_e('Today', 'dailybuddy'); ?></div>
                </div>
                <div class="pv-stat-value"><?php echo esc_html(number_format_i18n($todayN)); ?></div>
                <div class="pv-stat-label"><?php esc_html_e('pageviews', 'dailybuddy'); ?></div>
            </div>

            <div class="pv-stat-card week">
                <div class="pv-stat-header">
                    <span class="pv-stat-icon dashicons dashicons-calendar-alt"></span>
                    <div class="pv-stat-title"><?php esc_html_e('Last 7 days', 'dailybuddy'); ?></div>
                </div>
                <div class="pv-stat-value"><?php echo esc_html(number_format_i18n($sum7)); ?></div>
                <div class="pv-stat-label"><?php esc_html_e('pageviews', 'dailybuddy'); ?></div>
            </div>

            <div class="pv-stat-card month">
                <div class="pv-stat-header">
                    <span class="pv-stat-icon dashicons dashicons-chart-line"></span>
                    <div class="pv-stat-title"><?php esc_html_e('Last 30 days', 'dailybuddy'); ?></div>
                </div>
                <div class="pv-stat-value"><?php echo esc_html(number_format_i18n($sum30)); ?></div>
                <div class="pv-stat-label"><?php esc_html_e('pageviews', 'dailybuddy'); ?></div>
            </div>

        </div>

        <div class="dailybuddy-pv-chart">
            <div class="pv-chart-header">
                <span class="pv-chart-title" data-pv-range-label><?php esc_html_e('Last 30 days', 'dailybuddy'); ?></span>
                <?php if ($hasData) : ?>
                    <div class="pv-chart-switch" role="tablist">
                        <button type="button" data-pv-range="7"><?php esc_html_e('Week', 'dailybuddy'); ?></button>
                        <button type="button" data-pv-range="30" class="active"><?php esc_html_e('Month', 'dailybuddy'); ?></button>
                        <button type="button" data-pv-range="365"><?php esc_html_e('Year', 'dailybuddy'); ?></button>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($hasData) : ?>
                <div class="pv-chart-wrap">
                    <svg viewBox="0 0 520 90"
                         preserveAspectRatio="none"
                         class="pv-chart-svg"
                         id="dailybuddyPvChart"
                         aria-hidden="true">
                        <polygon points="" class="pv-chart-area"/>
                        <polyline points="" class="pv-chart-line"/>
                    </svg>
                    <div class="pv-tooltip" hidden></div>
                </div>
                <div class="pv-chart-legend">
                    <span data-pv-legend-first></span>
                    <span data-pv-legend-last></span>
                </div>
            <?php else : ?>
                <div class="pv-empty">
                    <span class="dashicons dashicons-chart-line"></span>
                    <p><?php esc_html_e('No pageviews recorded yet.', 'dailybuddy'); ?></p>
                    <p class="pv-empty-hint">
                        <?php esc_html_e('The counter starts as soon as your first visitor arrives. If you connected this site to a WPBuddy platform, tracking is already on.', 'dailybuddy'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($hasData) : ?>
        <script>
        (function () {
            var daily = <?php echo wp_json_encode($jsDaily); ?>;
            var i18n = {
                week:  <?php echo wp_json_encode(__('Last 7 days',   'dailybuddy')); ?>,
                month: <?php echo wp_json_encode(__('Last 30 days',  'dailybuddy')); ?>,
                year:  <?php echo wp_json_encode(__('Last 12 months','dailybuddy')); ?>,
                views: <?php echo wp_json_encode(__('pageviews',     'dailybuddy')); ?>
            };
            var monthNames = <?php echo wp_json_encode(array(
                __('Jan','dailybuddy'), __('Feb','dailybuddy'), __('Mar','dailybuddy'),
                __('Apr','dailybuddy'), __('May','dailybuddy'), __('Jun','dailybuddy'),
                __('Jul','dailybuddy'), __('Aug','dailybuddy'), __('Sep','dailybuddy'),
                __('Oct','dailybuddy'), __('Nov','dailybuddy'), __('Dec','dailybuddy')
            )); ?>;

            var svg     = document.getElementById('dailybuddyPvChart');
            if (!svg) return;
            var wrap    = svg.parentNode;
            var area    = svg.querySelector('.pv-chart-area');
            var line    = svg.querySelector('.pv-chart-line');
            var tip     = wrap.querySelector('.pv-tooltip');
            var lblEl   = document.querySelector('#dailybuddy_pageviews [data-pv-range-label]');
            var firstEl = document.querySelector('#dailybuddy_pageviews [data-pv-legend-first]');
            var lastEl  = document.querySelector('#dailybuddy_pageviews [data-pv-legend-last]');

            var W = 520, H = 90, PAD = 4;
            var current = []; // parallel data for the current render

            function aggregateMonths(days) {
                var buckets = {}, order = [];
                days.forEach(function (row) {
                    var key = row[0].substring(0, 7);
                    if (!(key in buckets)) { buckets[key] = { hits: 0, first: row[0] }; order.push(key); }
                    buckets[key].hits += row[1];
                });
                return order.map(function (k) {
                    var y = k.substring(0, 4), m = parseInt(k.substring(5, 7), 10) - 1;
                    return [ k, buckets[k].hits, monthNames[m] + " '" + y.substring(2) ];
                });
            }

            function render(range) {
                var data;
                if (range >= 365) {
                    data = aggregateMonths(daily.slice(Math.max(0, daily.length - 365)));
                } else {
                    data = daily.slice(Math.max(0, daily.length - range));
                }
                current = data;

                var vals = data.map(function (r) { return r[1]; });
                var max  = Math.max(1, Math.max.apply(null, vals.length ? vals : [0]));
                var n    = data.length;
                var step = n > 1 ? (W - 2 * PAD) / (n - 1) : 0;

                var pts = [];
                for (var i = 0; i < n; i++) {
                    var x = (PAD + i * step).toFixed(2);
                    var y = (H - PAD - (vals[i] / max) * (H - 2 * PAD)).toFixed(2);
                    pts.push(x + ',' + y);
                }
                line.setAttribute('points', pts.join(' '));
                area.setAttribute('points',
                    pts.join(' ') + ' ' + (W - PAD) + ',' + (H - PAD) + ' ' + PAD + ',' + (H - PAD)
                );

                if (lblEl)   lblEl.textContent = (range >= 365 ? i18n.year : (range === 7 ? i18n.week : i18n.month));
                if (firstEl) firstEl.textContent = data.length ? data[0][2] : '';
                if (lastEl)  lastEl.textContent  = data.length ? data[data.length - 1][2] : '';
            }

            document.querySelectorAll('#dailybuddy_pageviews [data-pv-range]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('#dailybuddy_pageviews [data-pv-range]').forEach(function (b) { b.classList.remove('active'); });
                    btn.classList.add('active');
                    render(parseInt(btn.getAttribute('data-pv-range'), 10) || 30);
                });
            });

            // Hover tooltip — resolves the hovered x-pixel to the closest data point.
            svg.addEventListener('mousemove', function (e) {
                if (!current.length) return;
                var rect = svg.getBoundingClientRect();
                var relX = (e.clientX - rect.left) / rect.width;
                var idx  = Math.max(0, Math.min(current.length - 1, Math.round(relX * (current.length - 1))));
                var row  = current[idx];
                tip.hidden = false;
                tip.innerHTML = '<div class="pv-tooltip-label">' + row[2] + '</div>' +
                                '<div class="pv-tooltip-value"><strong>' + row[1] + '</strong> ' + i18n.views + '</div>';
                var tw = tip.offsetWidth;
                var left = e.clientX - rect.left - tw / 2;
                if (left < 0) left = 0;
                if (left + tw > rect.width) left = rect.width - tw;
                tip.style.left = left + 'px';
                tip.style.top  = '-6px';
            });
            svg.addEventListener('mouseleave', function () { tip.hidden = true; });

            render(30);
        })();
        </script>
        <?php endif; ?>
        <?php
    }

    /**
     * Read + pad the daily hit array so every day in the last N days is
     * present (missing days = 0). Independent of the connector class so
     * this widget works even if the connector module is off (data just
     * won't refresh until the connector — or another counter — is on).
     */
    private function get_daily_padded($days)
    {
        $raw = get_option(self::OPT_HITS, array());
        if (! is_array($raw)) $raw = array();

        $out = array();
        $start = time() - (($days - 1) * DAY_IN_SECONDS);
        for ($i = 0; $i < $days; $i++) {
            $key = gmdate('Y-m-d', $start + ($i * DAY_IN_SECONDS));
            $out[$key] = isset($raw[$key]) ? (int) $raw[$key] : 0;
        }
        return $out;
    }
}

new Dailybuddy_Pageviews_Widget();

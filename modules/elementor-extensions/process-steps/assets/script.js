/**
 * dailybuddy Process Steps – Snake Row Grouping
 *
 * Column count is determined entirely by CSS Container Queries
 * (--ps-auto-columns) and the user's max setting (--ps-max-columns).
 * This script only reads the CSS values and groups steps into
 * snake-flow rows (LTR → RTL → LTR …).
 */
(function () {
    'use strict';

    function initProcessSteps(container) {
        if (!container) return;
        if (container._psCleanup) container._psCleanup();

        var allSteps = [];
        var tpl      = container.querySelector('.dailybuddy-row-connector-template');
        var loopConn = container.querySelector('.dailybuddy-loop-connector');
        var noConn   = container.classList.contains('dailybuddy-connector-style-none');
        var prevCols = -1;

        container.querySelectorAll('.dailybuddy-process-step').forEach(function (s) {
            allSteps.push(s);
        });
        if (!allSteps.length) return;

        /** Read column count from CSS Container Queries + user max */
        function getColumns() {
            var style = getComputedStyle(container);
            var auto  = parseInt(style.getPropertyValue('--ps-auto-columns'), 10) || 4;
            var max   = parseInt(style.getPropertyValue('--ps-max-columns'), 10) || 4;
            return Math.max(1, Math.min(auto, max));
        }

        /** Build snake rows or vertical stack */
        function layout() {
            var cols = getColumns();
            if (cols === prevCols) return;
            prevCols = cols;

            // 1 — Move steps back to container
            allSteps.forEach(function (s) { container.appendChild(s); });

            // 2 — Remove old rows & row connectors
            container.querySelectorAll('.dailybuddy-ps-row, .dailybuddy-ps-row-connector').forEach(function (el) {
                el.remove();
            });

            // 3 — Set effective columns for CSS flex layout
            container.style.setProperty('--ps-columns', cols);

            if (cols <= 1) {
                /* ── Vertical ── */
                container.classList.remove('dailybuddy-ps-snake');
                container.classList.add('dailybuddy-ps-vertical');

                allSteps.forEach(function (step, i) {
                    var c = step.querySelector('.dailybuddy-step-connector');
                    if (c) c.style.display = (i < allSteps.length - 1) ? '' : 'none';
                });

            } else {
                /* ── Snake ── */
                container.classList.add('dailybuddy-ps-snake');
                container.classList.remove('dailybuddy-ps-vertical');

                var rows = [], i;
                for (i = 0; i < allSteps.length; i += cols) {
                    rows.push(allSteps.slice(i, i + cols));
                }

                var frag = document.createDocumentFragment();

                rows.forEach(function (rowSteps, ri) {
                    var rtl = ri % 2 === 1;
                    var row = document.createElement('div');
                    row.className = 'dailybuddy-ps-row';
                    row.setAttribute('data-direction', rtl ? 'rtl' : 'ltr');

                    rowSteps.forEach(function (step, si) {
                        var c = step.querySelector('.dailybuddy-step-connector');
                        if (c) c.style.display = (si < rowSteps.length - 1) ? '' : 'none';
                        row.appendChild(step);
                    });

                    frag.appendChild(row);

                    if (!noConn && tpl && ri < rows.length - 1) {
                        var src = tpl.content
                            ? tpl.content.querySelector('.dailybuddy-ps-row-connector')
                            : tpl.querySelector('.dailybuddy-ps-row-connector');
                        if (src) {
                            var rc = src.cloneNode(true);
                            rc.classList.add('dailybuddy-ps-row-connector-' + (rtl ? 'left' : 'right'));
                            frag.appendChild(rc);
                        }
                    }
                });

                var ref = tpl || loopConn || null;
                if (ref) container.insertBefore(frag, ref);
                else     container.appendChild(frag);
            }

            if (tpl)      container.appendChild(tpl);
            if (loopConn) container.appendChild(loopConn);
        }

        layout();

        var timer;
        var ro = new ResizeObserver(function () {
            clearTimeout(timer);
            timer = setTimeout(layout, 100);
        });
        ro.observe(container);

        container._psCleanup = function () {
            ro.disconnect();
            clearTimeout(timer);
            prevCols = -1;
        };
    }

    function initAll() {
        document.querySelectorAll('.dailybuddy-process-steps').forEach(initProcessSteps);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    if (window.jQuery) {
        jQuery(window).on('elementor/frontend/init', function () {
            if (window.elementorFrontend) {
                elementorFrontend.hooks.addAction(
                    'frontend/element_ready/dailybuddy-process-steps.default',
                    function ($scope) {
                        var el = $scope[0].querySelector('.dailybuddy-process-steps');
                        if (el) initProcessSteps(el);
                    }
                );
            }
        });
    }
})();

/**
 * dailybuddy Category Post List
 *
 * Live search, excerpt tooltip, scroll-to-active, mobile select.
 */
(function () {
    'use strict';

    function initCPL(container) {
        if (!container) return;

        var searchInput   = container.querySelector('.dailybuddy-cpl-search');
        var list          = container.querySelector('.dailybuddy-cpl-list');
        var noResults     = container.querySelector('.dailybuddy-cpl-no-results');
        var items         = list ? list.querySelectorAll('.dailybuddy-cpl-item') : [];
        var mobileWrap    = container.querySelector('.dailybuddy-cpl-mobile');
        var dropToggle    = container.querySelector('.dailybuddy-cpl-dropdown-toggle');
        var dropPanel     = container.querySelector('.dailybuddy-cpl-dropdown-panel');
        var dropSearch    = container.querySelector('.dailybuddy-cpl-dropdown-search');
        var dropList      = container.querySelector('.dailybuddy-cpl-dropdown-list');
        var dropEmpty     = container.querySelector('.dailybuddy-cpl-dropdown-empty');

        // ── Live Search (Desktop) ──
        if (searchInput && items.length) {
            searchInput.addEventListener('input', function () {
                filterList(this.value, items, noResults);
            });
        }

        // ── Searchable Dropdown (Mobile) ──
        // Open/close is handled natively by <details>/<summary>.
        // JS only handles: search filter, focus, close on outside click.
        if (mobileWrap && mobileWrap.tagName === 'DETAILS') {
            // Focus search when opened
            mobileWrap.addEventListener('toggle', function () {
                if (mobileWrap.open && dropSearch) {
                    dropSearch.value = '';
                    filterDropdown('');
                    setTimeout(function () { dropSearch.focus(); }, 50);
                }
            });

            // Close on outside click
            document.addEventListener('click', function (e) {
                if (mobileWrap.open && !mobileWrap.contains(e.target)) {
                    mobileWrap.open = false;
                }
            });

            // Search inside dropdown
            if (dropSearch) {
                dropSearch.addEventListener('input', function () {
                    filterDropdown(this.value);
                });
            }

            function filterDropdown(value) {
                if (!dropList) return;
                var query   = value.toLowerCase().trim();
                var dItems  = dropList.querySelectorAll('.dailybuddy-cpl-dropdown-item');
                var visible = 0;

                for (var i = 0; i < dItems.length; i++) {
                    var link = dItems[i].querySelector('.dailybuddy-cpl-dropdown-link');
                    var text = link ? link.textContent.toLowerCase() : '';
                    var match = !query || text.indexOf(query) !== -1;
                    dItems[i].style.display = match ? '' : 'none';
                    if (match) visible++;
                }

                if (dropEmpty) {
                    dropEmpty.style.display = visible === 0 ? '' : 'none';
                }
            }
        }

        // ── Mobile breakpoint toggle ──
        if (container.hasAttribute('data-mobile-select') && mobileWrap) {
            var bp = parseInt(container.getAttribute('data-mobile-bp'), 10) || 767;

            function checkBreakpoint() {
                if (window.innerWidth <= bp) {
                    container.classList.add('dailybuddy-cpl-is-mobile');
                } else {
                    container.classList.remove('dailybuddy-cpl-is-mobile');
                }
            }

            checkBreakpoint();
            window.addEventListener('resize', debounce(checkBreakpoint, 150));
        }

        // ── Excerpt Tooltip ──
        if (container.hasAttribute('data-show-excerpt')) {
            var tooltip = document.createElement('div');
            tooltip.className = 'dailybuddy-cpl-tooltip';
            container.appendChild(tooltip);

            for (var j = 0; j < items.length; j++) {
                bindTooltip(items[j], tooltip, container);
            }
        }

        // ── Scroll to Active ──
        if (container.hasAttribute('data-scroll-active') && list) {
            var activeItem = list.querySelector('.dailybuddy-cpl-active');
            if (activeItem) {
                requestAnimationFrame(function () {
                    if (list.scrollHeight > list.clientHeight) {
                        var listRect   = list.getBoundingClientRect();
                        var activeRect = activeItem.getBoundingClientRect();
                        var offset     = activeRect.top - listRect.top - (listRect.height / 2) + (activeRect.height / 2);
                        list.scrollTo({ top: offset, behavior: 'smooth' });
                    }
                });
            }
        }
    }

    // ── Helpers ──

    function filterList(value, items, noResults) {
        var query   = value.toLowerCase().trim();
        var visible = 0;

        for (var i = 0; i < items.length; i++) {
            var link = items[i].querySelector('.dailybuddy-cpl-link');
            var text = link ? link.textContent.toLowerCase() : '';
            var match = !query || text.indexOf(query) !== -1;

            items[i].style.display = match ? '' : 'none';
            if (match) visible++;
        }

        if (noResults) {
            noResults.style.display = visible === 0 ? '' : 'none';
        }
    }

    function bindTooltip(item, tooltip, container) {
        var excerpt = item.getAttribute('data-excerpt');
        if (!excerpt) return;

        item.addEventListener('mouseenter', function () {
            tooltip.textContent = excerpt;
            tooltip.classList.add('visible');
            positionTooltip(item, tooltip, container);
        });

        item.addEventListener('mouseleave', function () {
            tooltip.classList.remove('visible');
        });
    }

    function positionTooltip(item, tooltip, container) {
        var rect     = container.getBoundingClientRect();
        var itemRect = item.getBoundingClientRect();

        var left = itemRect.right - rect.left + 8;
        var top  = itemRect.top - rect.top;

        var tooltipWidth = tooltip.offsetWidth || 280;
        if (itemRect.right + tooltipWidth + 16 > window.innerWidth) {
            left = itemRect.left - rect.left - tooltipWidth - 8;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top  = top + 'px';
    }

    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    // ── Init ──

    function initAll() {
        document.querySelectorAll('.dailybuddy-cpl-wrap').forEach(initCPL);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Elementor editor re-init.
    if (window.jQuery) {
        jQuery(window).on('elementor/frontend/init', function () {
            if (window.elementorFrontend) {
                elementorFrontend.hooks.addAction(
                    'frontend/element_ready/dailybuddy-category-post-list.default',
                    function ($scope) {
                        var el = $scope[0].querySelector('.dailybuddy-cpl-wrap');
                        if (el) initCPL(el);
                    }
                );
            }
        });
    }
})();

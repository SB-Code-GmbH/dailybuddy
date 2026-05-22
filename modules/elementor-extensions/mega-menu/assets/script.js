/**
 * Dailybuddy Mega Menu - Frontend Script
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Check if we are inside the Elementor editor
     */
    function isEditorMode() {
        return window.elementor ||
            (window.elementorFrontend && elementorFrontend.isEditMode && elementorFrontend.isEditMode()) ||
            document.body.classList.contains('elementor-editor-active') ||
            document.body.classList.contains('elementor-editor-preview');
    }

    /**
     * Close all dropdown items within a specific menu.
     * Only touches items with data-has-dropdown="true" — non-dropdown items
     * may have e-active for a different reason (current page or scrollspy).
     */
    function closeItemsInMenu(menu, except) {
        menu.querySelectorAll('.db-mega-menu-item[data-has-dropdown="true"]').forEach(function (el) {
            if (el !== except) {
                el.classList.remove('e-active');
                var icon = el.querySelector('.db-mega-menu-dropdown-icon');
                if (icon) icon.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /**
     * Toggle a dropdown item's active state + aria-expanded
     */
    function toggleItem(item, forceState) {
        var isActive = typeof forceState === 'boolean' ? forceState : !item.classList.contains('e-active');
        item.classList.toggle('e-active', isActive);

        var icon = item.querySelector('.db-mega-menu-dropdown-icon');
        if (icon) icon.setAttribute('aria-expanded', isActive ? 'true' : 'false');

        if (isActive) {
            applyViewportStretch(item);
        }
    }

    /**
     * For data-dropdown-width="viewport" items, set --stretch-* on the
     * dropdown content so it spans the full viewport width regardless of
     * where the menu is positioned in the page.
     */
    function applyViewportStretch(item) {
        if (!item || item.getAttribute('data-dropdown-width') !== 'viewport') return;
        var heading = item.closest('.db-mega-menu-heading');
        var content = item.querySelector('.db-mega-menu-content');
        if (!heading || !content) return;

        var rect = heading.getBoundingClientRect();
        content.style.setProperty('--stretch-left', (-rect.left) + 'px');
        content.style.setProperty('--stretch-right', 'auto');
        content.style.setProperty('--stretch-width', '100vw');
    }

    // Recompute viewport stretch on resize so the dropdown stays full-width.
    window.addEventListener('resize', function () {
        document.querySelectorAll('.db-mega-menu-item.e-active[data-dropdown-width="viewport"]').forEach(applyViewportStretch);
    });

    /**
     * Close a mobile menu
     */
    function closeMobileMenu(menu) {
        var wrapper = menu.querySelector('.db-mega-menu-wrapper');
        var toggle = menu.querySelector('.db-mega-menu-toggle');

        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('e-open');
        if (wrapper) wrapper.classList.remove('e-open');

        // Return focus to toggle for accessibility
        if (toggle) toggle.focus();
    }

    // ========== PAGE-MATCH ACTIVE STATE ==========
    // Marks the menu item whose link points at the current URL with .e-active.
    // Runs client-side so it survives cached headers/footers (Elementor Pro templates,
    // page caches, etc.) where the server-rendered class may be stale.

    (function applyPageActiveState() {
        if (isEditorMode()) return;

        function normalizePath(p) {
            p = '/' + String(p || '').replace(/^\/+/, '').replace(/\/+$/, '');
            return p === '/' ? '/' : p;
        }

        var currentPath = normalizePath(window.location.pathname);

        document.querySelectorAll('.db-mega-menu-item').forEach(function (item) {
            // Dropdown items reserve .e-active for open/close state — skip them.
            if (item.getAttribute('data-has-dropdown') === 'true') return;

            var link = item.querySelector('a.db-mega-menu-title-container');
            if (!link) {
                item.classList.remove('e-active');
                return;
            }

            var href = link.getAttribute('href') || '';

            // Skip URLs that scrollspy or non-navigational schemes handle.
            if (!href || href === '#' || href.charAt(0) === '#') return;
            if (href.indexOf('#') !== -1) return; // anchor links → scrollspy owns this
            if (/^(javascript:|mailto:|tel:|sms:)/i.test(href)) return;

            var linkPath;
            try {
                linkPath = new URL(href, window.location.href).pathname;
            } catch (e) {
                linkPath = href;
            }

            if (normalizePath(linkPath) === currentPath) {
                item.classList.add('e-active');
            } else {
                item.classList.remove('e-active');
            }
        });
    })();

    // ========== DROPDOWN ITEMS ==========

    var titles = document.querySelectorAll('.db-mega-menu-item[data-has-dropdown="true"] > .db-mega-menu-title');

    titles.forEach(function (title) {
        var menu = title.closest('.db-mega-menu');
        var openOn = menu ? menu.dataset.openOn : 'click';

        // Click handler (always needed for accessibility)
        title.addEventListener('click', function (e) {
            if (openOn === 'hover' && window.matchMedia('(hover: hover)').matches) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            var item = title.closest('.db-mega-menu-item');

            // Close other items within THIS menu only
            closeItemsInMenu(menu, item);

            // Toggle this item
            toggleItem(item);
        });

        // Hover handlers (only if hover mode)
        if (openOn === 'hover') {
            var item = title.closest('.db-mega-menu-item');

            item.addEventListener('mouseenter', function () {
                if (!window.matchMedia('(hover: hover)').matches) return;
                closeItemsInMenu(menu, item);
                toggleItem(item, true);
            });

            item.addEventListener('mouseleave', function () {
                if (!window.matchMedia('(hover: hover)').matches) return;
                toggleItem(item, false);
            });
        }
    });

    // Prevent clicks inside dropdown content from closing it
    document.querySelectorAll('.db-mega-menu-content').forEach(function (content) {
        content.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });

    // ========== MOBILE MENU TOGGLE ==========

    document.querySelectorAll('.db-mega-menu-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var menu = toggle.closest('.db-mega-menu');
            var wrapper = menu.querySelector('.db-mega-menu-wrapper');
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';

            if (isExpanded) {
                closeMobileMenu(menu);
            } else {
                toggle.setAttribute('aria-expanded', 'true');
                menu.classList.add('e-open');
                if (wrapper) wrapper.classList.add('e-open');
            }
        });
    });

    // ========== CLOSE BUTTON (inside wrapper) ==========

    document.querySelectorAll('.db-mega-menu-close').forEach(function (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var menu = closeBtn.closest('.db-mega-menu');
            closeMobileMenu(menu);
        });
    });

    // ========== CLICK OUTSIDE TO CLOSE ==========

    document.addEventListener('click', function (e) {
        if (isEditorMode()) return;

        // Close dropdown items - scoped per menu
        if (!e.target.closest('.db-mega-menu')) {
            document.querySelectorAll('.db-mega-menu').forEach(function (menu) {
                closeItemsInMenu(menu);
            });
        }

        // Close open mobile menus
        if (!e.target.closest('.db-mega-menu-wrapper') &&
            !e.target.closest('.db-mega-menu-toggle') &&
            !e.target.closest('.db-mega-menu-content')) {
            document.querySelectorAll('.db-mega-menu.e-open').forEach(function (menu) {
                closeMobileMenu(menu);
            });
        }
    });

    // ========== SCROLLSPY FOR ANCHOR LINKS ==========
    // Markiert das Menü-Item, dessen #anchor-Ziel gerade im Viewport ist, mit .e-active.
    // Items mit Dropdown bleiben unangetastet (deren e-active steuert das Auf/Zu).

    (function initScrollSpy() {
        var entries = [];
        var currentPath = window.location.pathname;

        document.querySelectorAll('.db-mega-menu-item a.db-mega-menu-title-container').forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var hashIndex = href.indexOf('#');
            if (hashIndex === -1) return;

            var pathPart = href.substring(0, hashIndex);
            var anchorId = href.substring(hashIndex + 1);
            if (!anchorId) return;

            // Akzeptiere nur Anker auf der aktuellen Seite (oder relative #...)
            if (pathPart !== '' && pathPart !== currentPath && pathPart !== currentPath + '/') {
                try {
                    var u = new URL(href, window.location.href);
                    if (u.pathname !== currentPath) return;
                } catch (e) {
                    return;
                }
            }

            var target = document.getElementById(anchorId);
            if (!target) return;

            var menuItem = link.closest('.db-mega-menu-item');
            if (!menuItem) return;

            // Items mit Dropdown vom Scrollspy ausnehmen, sonst Konflikt mit Open-State.
            if (menuItem.getAttribute('data-has-dropdown') === 'true') return;

            entries.push({ menuItem: menuItem, target: target });
        });

        if (entries.length === 0) return;

        function updateActive() {
            var scrollPos = window.scrollY + (window.innerHeight * 0.3);
            var current = null;

            entries.forEach(function (entry) {
                var rect = entry.target.getBoundingClientRect();
                var top = rect.top + window.scrollY;
                if (top <= scrollPos) {
                    current = entry;
                }
            });

            entries.forEach(function (entry) {
                if (entry === current) {
                    entry.menuItem.classList.add('e-active');
                } else {
                    entry.menuItem.classList.remove('e-active');
                }
            });
        }

        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateActive();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        window.addEventListener('resize', updateActive);
        window.addEventListener('hashchange', updateActive);

        updateActive();
    })();

    // ========== ESC KEY TO CLOSE ==========

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            // Close dropdown items in the currently focused menu
            var focusedMenu = document.activeElement ? document.activeElement.closest('.db-mega-menu') : null;

            if (focusedMenu) {
                // Close items in this menu only
                closeItemsInMenu(focusedMenu);

                // Close mobile menu if open
                if (focusedMenu.classList.contains('e-open')) {
                    closeMobileMenu(focusedMenu);
                }
            } else {
                // No focused menu — close all open mobile menus
                document.querySelectorAll('.db-mega-menu.e-open').forEach(function (menu) {
                    closeMobileMenu(menu);
                });
            }
        }
    });
});

/**
 * Dailybuddy Mega Menu - Frontend Script
 */
document.addEventListener('DOMContentLoaded', function () {

    // Track original body overflow to restore correctly
    var savedBodyOverflow = null;

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
     * Close all dropdown items within a specific menu
     */
    function closeItemsInMenu(menu, except) {
        menu.querySelectorAll('.db-mega-menu-item').forEach(function (el) {
            if (el !== except) {
                el.classList.remove('e-active');
                // Update aria-expanded on dropdown icon
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
    }

    /**
     * Close a mobile menu and restore body scroll
     */
    function closeMobileMenu(menu) {
        var wrapper = menu.querySelector('.db-mega-menu-wrapper');
        var toggle = menu.querySelector('.db-mega-menu-toggle');

        if (toggle) toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('e-open');
        if (wrapper) wrapper.classList.remove('e-open');

        restoreBodyOverflow();

        // Return focus to toggle for accessibility
        if (toggle) toggle.focus();
    }

    /**
     * Lock body scroll (save original value first)
     */
    function lockBodyOverflow() {
        if (savedBodyOverflow === null) {
            savedBodyOverflow = document.body.style.overflow || '';
        }
        document.body.style.overflow = 'hidden';
    }

    /**
     * Restore body scroll to its original value
     */
    function restoreBodyOverflow() {
        if (savedBodyOverflow !== null) {
            document.body.style.overflow = savedBodyOverflow;
            savedBodyOverflow = null;
        }
    }

    /**
     * Check if a menu layout needs body scroll lock
     */
    function needsScrollLock(menu) {
        return menu.classList.contains('mobile-layout-slide-left') ||
            menu.classList.contains('mobile-layout-slide-right') ||
            menu.classList.contains('mobile-layout-full-screen');
    }

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
                if (needsScrollLock(menu)) lockBodyOverflow();
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

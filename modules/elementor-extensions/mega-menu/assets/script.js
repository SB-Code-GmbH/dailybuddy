/**
 * Dailybuddy Mega Menu - Simple Toggle
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all menu TITLES with dropdown (click on title only, not content)
    var titles = document.querySelectorAll('.db-mega-menu-item[data-has-dropdown="true"] > .db-mega-menu-title');
    
    titles.forEach(function(title) {
        var menu = title.closest('.db-mega-menu');
        var openOn = menu ? menu.dataset.openOn : 'click';
        
        // Click handler (always needed for accessibility)
        title.addEventListener('click', function(e) {
            // If hover mode, only handle click on mobile or as fallback
            if (openOn === 'hover' && window.matchMedia('(hover: hover)').matches) {
                return;
            }
            
            e.preventDefault();
            e.stopPropagation();
            
            var item = title.closest('.db-mega-menu-item');
            var isActive = item.classList.contains('e-active');
            
            // Close all other items first
            document.querySelectorAll('.db-mega-menu-item').forEach(function(el) {
                if (el !== item) {
                    el.classList.remove('e-active');
                }
            });
            
            // Toggle this one
            if (isActive) {
                item.classList.remove('e-active');
            } else {
                item.classList.add('e-active');
            }
        });
        
        // Hover handlers (only if hover mode)
        if (openOn === 'hover') {
            var item = title.closest('.db-mega-menu-item');
            
            item.addEventListener('mouseenter', function() {
                // Only on devices that support hover
                if (!window.matchMedia('(hover: hover)').matches) {
                    return;
                }
                
                // Close all other items first
                document.querySelectorAll('.db-mega-menu-item').forEach(function(el) {
                    if (el !== item) {
                        el.classList.remove('e-active');
                    }
                });
                
                item.classList.add('e-active');
            });
            
            item.addEventListener('mouseleave', function() {
                if (!window.matchMedia('(hover: hover)').matches) {
                    return;
                }
                item.classList.remove('e-active');
            });
        }
    });
    
    // Prevent clicks inside dropdown content from closing it
    var contents = document.querySelectorAll('.db-mega-menu-content');
    contents.forEach(function(content) {
        content.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Click outside to close dropdowns
    document.addEventListener('click', function(e) {
        // Don't handle click-outside in Elementor Editor
        if (window.elementor || window.elementorFrontend?.isEditMode?.() || 
            document.body.classList.contains('elementor-editor-active') ||
            document.body.classList.contains('elementor-editor-preview')) {
            return;
        }
        
        if (!e.target.closest('.db-mega-menu')) {
            document.querySelectorAll('.db-mega-menu-item').forEach(function(el) {
                el.classList.remove('e-active');
            });
        }
    });
    
    // ========== MOBILE MENU TOGGLE ==========
    var toggles = document.querySelectorAll('.db-mega-menu-toggle');
    
    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var menu = toggle.closest('.db-mega-menu');
            var wrapper = menu.querySelector('.db-mega-menu-wrapper');
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            
            // Check if layout needs body scroll lock (only for overlay layouts)
            var needsScrollLock = menu.classList.contains('mobile-layout-slide-left') ||
                                  menu.classList.contains('mobile-layout-slide-right') ||
                                  menu.classList.contains('mobile-layout-full-screen');
            
            if (isExpanded) {
                // Close
                toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('e-open');
                if (wrapper) wrapper.classList.remove('e-open');
                if (needsScrollLock) {
                    document.body.style.overflow = '';
                }
            } else {
                // Open
                toggle.setAttribute('aria-expanded', 'true');
                menu.classList.add('e-open');
                if (wrapper) wrapper.classList.add('e-open');
                if (needsScrollLock) {
                    document.body.style.overflow = 'hidden';
                }
            }
        });
    });
    
    // ========== CLOSE BUTTON (inside wrapper) ==========
    var closeButtons = document.querySelectorAll('.db-mega-menu-close');
    
    closeButtons.forEach(function(closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var menu = closeBtn.closest('.db-mega-menu');
            var wrapper = menu.querySelector('.db-mega-menu-wrapper');
            var toggle = menu.querySelector('.db-mega-menu-toggle');
            
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
            menu.classList.remove('e-open');
            if (wrapper) wrapper.classList.remove('e-open');
            document.body.style.overflow = '';
        });
    });
    
    // ========== CLICK OUTSIDE TO CLOSE ==========
    document.addEventListener('click', function(e) {
        // Don't handle click-outside in Elementor Editor
        if (window.elementor || window.elementorFrontend?.isEditMode?.() || 
            document.body.classList.contains('elementor-editor-active') ||
            document.body.classList.contains('elementor-editor-preview')) {
            return;
        }
        
        var openMenus = document.querySelectorAll('.db-mega-menu.e-open');
        openMenus.forEach(function(menu) {
            // Don't close if clicking inside wrapper, toggle, or any content area
            if (!e.target.closest('.db-mega-menu-wrapper') && 
                !e.target.closest('.db-mega-menu-toggle') &&
                !e.target.closest('.db-mega-menu-content')) {
                var toggle = menu.querySelector('.db-mega-menu-toggle');
                var wrapper = menu.querySelector('.db-mega-menu-wrapper');
                
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('e-open');
                if (wrapper) wrapper.classList.remove('e-open');
                document.body.style.overflow = '';
            }
        });
    });
    
    // ========== ESC KEY TO CLOSE ==========
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            // Close all dropdowns
            document.querySelectorAll('.db-mega-menu-item').forEach(function(el) {
                el.classList.remove('e-active');
            });
            
            // Close mobile menu
            document.querySelectorAll('.db-mega-menu-toggle').forEach(function(toggle) {
                var menu = toggle.closest('.db-mega-menu');
                var wrapper = menu.querySelector('.db-mega-menu-wrapper');
                toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('e-open');
                if (wrapper) wrapper.classList.remove('e-open');
            });
            document.body.style.overflow = '';
        }
    });
});

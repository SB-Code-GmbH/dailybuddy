/**
 * Dailybuddy Mega Menu - Editor Module
 * 
 * This module registers the nested element type for the Mega Menu widget.
 * It follows the exact same pattern as Elementor Pro's mega-menu-editor.
 */

(function() {
    'use strict';

    // Wait for Elementor to be ready
    if (typeof elementorCommon === 'undefined') {
        return;
    }

    // Listen for the nested element type loaded event
    // This is the SAME event that Elementor Pro uses
    elementorCommon.elements.$window.on('elementor/nested-element-type-loaded', function() {
        initDailybuddyMegaMenuModule();
    });

    function initDailybuddyMegaMenuModule() {
        // Verify NestedElementBase is available
        if (!elementor.modules?.elements?.types?.NestedElementBase) {
            return;
        }

        // Get NestedView from nested-elements component
        var nestedElementsComponent = $e.components.get('nested-elements');
        if (!nestedElementsComponent || !nestedElementsComponent.exports || !nestedElementsComponent.exports.NestedView) {
            return;
        }

        var NestedView = nestedElementsComponent.exports.NestedView;
        var NestedElementBase = elementor.modules.elements.types.NestedElementBase;

        /**
         * Custom View for the Mega Menu
         * Extends NestedView to handle interlaced container rendering
         */
        class DailybuddyMegaMenuView extends NestedView {
            constructor() {
                super(...arguments);
                this.isRendering = false;
                this.itemTitle = 'item_title';
                this.itemLink = 'item_link';
            }

            filter(child, index) {
                child.attributes.dataIndex = index + 1;
                child.attributes.widgetId = child.id;
                return true;
            }

            onAddChild(childView) {
                var widgetNumber = childView._parent.$el.find('.db-mega-menu')[0]?.dataset.widgetNumber || childView.model.attributes.widgetId;
                var index = childView.model.attributes.dataIndex;
                var tabId = childView._parent.$el.find('.db-mega-menu-title[data-tab-index="' + index + '"]')?.attr('id') || childView.model.attributes.widgetId + ' ' + index;
                
                childView.$el.attr({
                    id: 'db-mega-menu-content-' + widgetNumber + '' + index,
                    role: 'menu',
                    'aria-labelledby': tabId,
                    'data-tab-index': index
                });
            }

            getChildViewContainer(containerView, childView) {
                var _this$model$config$de = this.model.config.defaults;
                var customSelector = _this$model$config$de.elements_placeholder_selector;
                var childContainerSelector = _this$model$config$de.child_container_placeholder_selector;
                
                if (childView !== undefined && childView._index !== undefined && childContainerSelector) {
                    return containerView.$el.find(childContainerSelector)[childView._index];
                }
                
                if (customSelector) {
                    return containerView.$el.find(this.model.config.defaults.elements_placeholder_selector);
                }
                
                return NestedView.prototype.getChildViewContainer.call(this, containerView, childView);
            }

            attachBuffer(compositeView, buffer) {
                var $container = this.getChildViewContainer(compositeView);
                
                if (this.model?.config?.support_improved_repeaters && this.model?.config?.is_interlaced) {
                    var childContainerSelector = this.model?.config?.defaults?.child_container_placeholder_selector || '';
                    var childContainerClass = childContainerSelector.replace('.', '');
                    this._updateChildContainers($container[0], childContainerClass, buffer);
                } else {
                    $container.append(buffer);
                }
            }

            _updateChildContainers(wrapper, childContainerClass, buffer) {
                var _this = this;
                
                _.each(wrapper.children, function(childContainer) {
                    if (!childContainer.classList?.contains(childContainerClass)) {
                        _this._updateChildContainers(childContainer, childContainerClass, buffer);
                        return;
                    }
                    
                    var numberOfItems = buffer.childNodes.length;
                    if (0 === numberOfItems) {
                        return;
                    }
                    
                    childContainer.appendChild(buffer.childNodes[0]);
                    buffer.appendChild(childContainer);
                    wrapper.append(buffer.childNodes[numberOfItems - 1]);
                });
            }
        }

        /**
         * NestedModule class - the element type definition
         * CRITICAL: getType() must return the exact widget name from get_name()
         */
        class DailybuddyMegaMenuNestedModule extends NestedElementBase {
            getType() {
                // MUST match the widget's get_name() return value
                return 'dailybuddy-mega-menu';
            }

            getView() {
                return DailybuddyMegaMenuView;
            }
        }

        // Register the element type
        elementor.elementsManager.registerElementType(new DailybuddyMegaMenuNestedModule());
        
        
        // Setup click handlers for editor
        setupEditorClickHandlers();
    }

    /**
     * Setup click handlers to toggle .e-active class in editor
     */
    function setupEditorClickHandlers() {
        
        // GLOBAL state storage - survives DOM re-renders
        window.dbMegaMenuActiveState = window.dbMegaMenuActiveState || {};
        
        // Wait for preview to be ready
        elementor.on('preview:loaded', function() {
            var previewIframe = elementor.$preview[0];
            if (!previewIframe) return;
            
            var previewDocument = previewIframe.contentDocument || previewIframe.contentWindow.document;
            if (!previewDocument) return;
            
            
            // Click handler using native JS for better capture
            previewDocument.addEventListener('click', function(e) {
                var target = e.target;
                
                // Check if clicked on menu toggle (hamburger button)
                var menuToggle = target.closest('.db-mega-menu-toggle');
                if (menuToggle) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var menu = menuToggle.closest('.db-mega-menu');
                    var wrapper = menu ? menu.querySelector('.db-mega-menu-wrapper') : null;
                    
                    if (menu && wrapper) {
                        var isOpen = menu.classList.contains('e-open');
                        
                        if (isOpen) {
                            menu.classList.remove('e-open');
                            wrapper.classList.remove('e-open');
                        } else {
                            menu.classList.add('e-open');
                            wrapper.classList.add('e-open');
                        }
                    }
                    return;
                }
                
                // Check if clicked on mobile menu close button
                var closeButton = target.closest('.db-mega-menu-close');
                if (closeButton) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var menu = closeButton.closest('.db-mega-menu');
                    var wrapper = menu ? menu.querySelector('.db-mega-menu-wrapper') : null;
                    
                    if (menu && wrapper) {
                        menu.classList.remove('e-open');
                        wrapper.classList.remove('e-open');
                    }
                    return;
                }
                
                // Check if clicked on menu title or dropdown icon
                var menuTitle = target.closest('.db-mega-menu-title');
                var dropdownIcon = target.closest('.db-mega-menu-dropdown-icon');
                
                if (!menuTitle && !dropdownIcon) {
                    return; // Not our element
                }
                
                e.preventDefault();
                e.stopPropagation();
                
                var clickedEl = menuTitle || dropdownIcon;
                var menuItem = clickedEl.closest('.db-mega-menu-item');
                var menu = clickedEl.closest('.db-mega-menu');
                var widget = clickedEl.closest('.elementor-widget');
                
                if (!menuItem || !menu || !widget) return;
                
                // CRITICAL: Check data-has-dropdown attribute first
                var hasDropdown = menuItem.getAttribute('data-has-dropdown');
                if (hasDropdown !== 'true') {
                    return;
                }
                
                // Also check if content element exists
                var content = menuItem.querySelector('.db-mega-menu-content');
                if (!content) {
                    return;
                }
                
                var widgetId = widget.getAttribute('data-id');
                var isActive = menuItem.classList.contains('e-active');
                
                // Get index of this menu item
                var allItems = menu.querySelectorAll('.db-mega-menu-item');
                var menuItemIndex = Array.from(allItems).indexOf(menuItem);
                
                // Close all dropdowns first
                allItems.forEach(function(item) {
                    item.classList.remove('e-active');
                    var itemContent = item.querySelector('.db-mega-menu-content');
                    if (itemContent) itemContent.classList.remove('e-active');
                    var icon = item.querySelector('.db-mega-menu-dropdown-icon');
                    if (icon) icon.setAttribute('aria-expanded', 'false');
                });
                
                // Toggle this one
                if (!isActive) {
                    menuItem.classList.add('e-active');
                    content.classList.add('e-active');
                    var icon = menuItem.querySelector('.db-mega-menu-dropdown-icon');
                    if (icon) icon.setAttribute('aria-expanded', 'true');
                    
                    // Store state
                    window.dbMegaMenuActiveState[widgetId] = menuItemIndex;
                } else {
                    delete window.dbMegaMenuActiveState[widgetId];
                }
            }, true); // Capture phase
            
        });
        
        // LIVE UPDATE: Listen for setting changes in Elementor
        setupLiveSettingUpdates();
        
        // Restore function - checks if content exists before restoring
        function restoreActiveState() {
            var state = window.dbMegaMenuActiveState;
            if (!state || Object.keys(state).length === 0) {
                return;
            }
            
            var previewIframe = elementor.$preview?.[0];
            if (!previewIframe) return;
            
            var previewDocument = previewIframe.contentDocument || previewIframe.contentWindow?.document;
            if (!previewDocument) return;
            
            Object.keys(state).forEach(function(widgetId) {
                var menuItemIndex = state[widgetId];
                var widget = previewDocument.querySelector('.elementor-widget[data-id="' + widgetId + '"]');
                
                if (!widget) return;
                
                var menu = widget.querySelector('.db-mega-menu');
                if (!menu) return;
                
                var allItems = menu.querySelectorAll('.db-mega-menu-item');
                var menuItem = allItems[menuItemIndex];
                
                if (!menuItem) return;
                
                // CRITICAL: Check data-has-dropdown attribute
                var hasDropdown = menuItem.getAttribute('data-has-dropdown');
                if (hasDropdown !== 'true') {
                    delete window.dbMegaMenuActiveState[widgetId];
                    return;
                }
                
                // Also check if content element exists
                var content = menuItem.querySelector('.db-mega-menu-content');
                if (!content) {
                    // Dropdown was disabled - remove from state
                    delete window.dbMegaMenuActiveState[widgetId];
                    return;
                }
                
                if (menuItem.classList.contains('e-active')) {
                    return; // Already active
                }
                
                // Clear all first
                allItems.forEach(function(item) {
                    item.classList.remove('e-active');
                    var itemContent = item.querySelector('.db-mega-menu-content');
                    if (itemContent) itemContent.classList.remove('e-active');
                });
                
                // Set active
                menuItem.classList.add('e-active');
                content.classList.add('e-active');
                var icon = menuItem.querySelector('.db-mega-menu-dropdown-icon');
                if (icon) icon.setAttribute('aria-expanded', 'true');
                
            });
        }
        
        // Listen for element changes
        elementor.channels.data.on('element:after:add', function() {
            setTimeout(restoreActiveState, 100);
            setTimeout(restoreActiveState, 300);
        });
        
        // Interval to maintain state (checks before restoring)
        setInterval(restoreActiveState, 300);
    }
    
    /**
     * Setup live updates when dropdown switch is toggled
     */
    function setupLiveSettingUpdates() {
        
        // Listen to Elementor's data channel for setting changes
        elementor.channels.editor.on('change:item_dropdown_content', function(controlView) {
            updateDropdownStateFromModel();
        });
        
        // Listen for mobile_menu_only changes
        elementor.channels.editor.on('change:mobile_menu_only', function(controlView) {
            updateMobileMenuOnlyState();
        });
        
        // Also listen for any repeater item changes
        elementor.channels.editor.on('change', function(controlView) {
            if (!controlView || !controlView.model) return;
            
            var controlName = controlView.model.get('name');
            if (controlName === 'item_dropdown_content') {
                setTimeout(updateDropdownStateFromModel, 100);
            }
            if (controlName === 'mobile_menu_only') {
                setTimeout(updateMobileMenuOnlyState, 100);
            }
        });
        
        // Hook into container changes
        if (elementor.hooks) {
            elementor.hooks.addAction('panel/open_editor/widget/dailybuddy-mega-menu', function(panel, model, view) {
                // Watch for changes on this model
                model.on('change:settings', function() {
                    setTimeout(updateDropdownStateFromModel, 100);
                    setTimeout(updateMobileMenuOnlyState, 100);
                });
            });
        }
        
        // Use MutationObserver on the panel to detect switch changes
        observePanelForSwitchChanges();
    }
    
    /**
     * Observe the Elementor panel for switch toggle clicks
     */
    function observePanelForSwitchChanges() {
        var panelObserver = new MutationObserver(function(mutations) {
            // Debounce
            clearTimeout(window.dbMegaMenuPanelDebounce);
            window.dbMegaMenuPanelDebounce = setTimeout(function() {
                updateDropdownStateFromModel();
            }, 200);
        });
        
        // Start observing when panel is ready
        var checkPanel = setInterval(function() {
            var panel = document.getElementById('elementor-panel');
            if (panel) {
                clearInterval(checkPanel);
                
                // Listen for clicks on switcher controls
                panel.addEventListener('click', function(e) {
                    var switcher = e.target.closest('.elementor-control-type-switcher');
                    if (switcher) {
                        // Check by control name (data attribute) or by label text
                        var controlName = switcher.dataset.setting || '';
                        var label = switcher.querySelector('.elementor-control-title');
                        var labelText = label ? label.textContent.trim().toLowerCase() : '';
                        
                        // Dropdown Content switch
                        if (controlName === 'item_dropdown_content' || labelText.includes('dropdown content')) {
                            setTimeout(updateDropdownStateFromModel, 100);
                            setTimeout(updateDropdownStateFromModel, 300);
                            setTimeout(updateDropdownStateFromModel, 500);
                        }
                        
                        // Mobile Menu Only switch
                        if (controlName === 'mobile_menu_only' || labelText.includes('mobile menu only')) {
                            setTimeout(updateMobileMenuOnlyState, 100);
                            setTimeout(updateMobileMenuOnlyState, 300);
                        }
                        
                        // Fallback: check if any mega menu widget control was clicked
                        var control = switcher.closest('.elementor-control');
                        if (control) {
                            var controlId = control.dataset.setting || '';
                            if (controlId === 'item_dropdown_content') {
                                setTimeout(updateDropdownStateFromModel, 100);
                            }
                            if (controlId === 'mobile_menu_only') {
                                setTimeout(updateMobileMenuOnlyState, 100);
                            }
                        }
                    }
                    
                    // Listen for icon picker clicks
                    var iconPicker = e.target.closest('.elementor-control-type-icons');
                    if (iconPicker) {
                        setTimeout(updateDropdownStateFromModel, 300);
                        setTimeout(updateDropdownStateFromModel, 600);
                    }
                }, true);
                
                // Listen for input changes (title, etc.)
                panel.addEventListener('input', function(e) {
                    var input = e.target.closest('.elementor-control-type-text input');
                    if (input) {
                        var controlWrapper = input.closest('.elementor-control');
                        var label = controlWrapper?.querySelector('.elementor-control-title');
                        if (label && (label.textContent.trim() === 'Titel' || label.textContent.trim() === 'Title')) {
                            // Debounce title updates
                            clearTimeout(window.dbMegaMenuTitleDebounce);
                            window.dbMegaMenuTitleDebounce = setTimeout(updateDropdownStateFromModel, 200);
                        }
                    }
                }, true);
                
                // Also observe the panel for DOM changes (icon picker selections)
                panelObserver.observe(panel, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                
            }
        }, 500);
    }
    
    /**
     * Update Mobile Menu Only state in preview
     */
    function updateMobileMenuOnlyState() {
        try {
            var previewIframe = elementor.$preview?.[0];
            if (!previewIframe) return;
            
            var previewDocument = previewIframe.contentDocument || previewIframe.contentWindow?.document;
            if (!previewDocument) return;
            
            // Get currently edited widget
            var editedElement = elementor.getPanelView()?.getCurrentPageView?.()?.getOption?.('editedElementView');
            if (!editedElement || !editedElement.model) return;
            
            var model = editedElement.model;
            var widgetType = model.get('widgetType');
            
            if (widgetType !== 'dailybuddy-mega-menu') return;
            
            var widgetId = model.get('id');
            var settings = model.get('settings');
            var mobileMenuOnly = settings?.get('mobile_menu_only') === 'yes';
            
            // Find widget in preview
            var widget = previewDocument.querySelector('.elementor-widget[data-id="' + widgetId + '"]');
            if (!widget) return;
            
            var menu = widget.querySelector('.db-mega-menu');
            if (!menu) return;
            
            // Update class
            if (mobileMenuOnly) {
                menu.classList.add('mobile-menu-only');
            } else {
                menu.classList.remove('mobile-menu-only');
            }
            
            
        } catch (e) {
        }
    }
    
    /**
     * Update the DOM based on current model settings
     */
    function updateDropdownStateFromModel() {
        try {
            var previewIframe = elementor.$preview?.[0];
            if (!previewIframe) return;
            
            var previewDocument = previewIframe.contentDocument || previewIframe.contentWindow?.document;
            if (!previewDocument) return;
            
            // Get currently edited widget
            var editedElement = elementor.getPanelView()?.getCurrentPageView?.()?.getOption?.('editedElementView');
            if (!editedElement) {
                // Try alternative method
                var currentElement = $e?.routes?.current?.component?.currentView?.options?.container;
                if (currentElement && currentElement.model) {
                    editedElement = { model: currentElement.model };
                }
            }
            
            if (!editedElement || !editedElement.model) {
                return;
            }
            
            var model = editedElement.model;
            var widgetType = model.get('widgetType');
            
            if (widgetType !== 'dailybuddy-mega-menu') {
                return;
            }
            
            var widgetId = model.get('id');
            var settings = model.get('settings');
            var menuItems = settings?.get('menu_items') || [];
            
            
            // Find widget in preview
            var widget = previewDocument.querySelector('.elementor-widget[data-id="' + widgetId + '"]');
            if (!widget) {
                return;
            }
            
            var menu = widget.querySelector('.db-mega-menu');
            if (!menu) return;
            
            var domItems = menu.querySelectorAll('.db-mega-menu-item');
            
            menuItems.forEach(function(item, index) {
                var domItem = domItems[index];
                if (!domItem) return;
                
                // Get values from model
                var hasDropdown = item.get ? item.get('item_dropdown_content') === 'yes' : item.item_dropdown_content === 'yes';
                var itemTitle = item.get ? item.get('item_title') : item.item_title;
                var itemIcon = item.get ? item.get('item_icon') : item.item_icon;
                
                var currentAttr = domItem.getAttribute('data-has-dropdown');
                
                
                // Update data attribute
                domItem.setAttribute('data-has-dropdown', hasDropdown ? 'true' : 'false');
                
                // Update title
                var titleSpan = domItem.querySelector('.db-mega-menu-title-text');
                if (titleSpan && itemTitle) {
                    titleSpan.textContent = itemTitle;
                }
                
                // Update icon
                var iconSpan = domItem.querySelector('.db-mega-menu-icon');
                var titleContainer = domItem.querySelector('.db-mega-menu-title-container');
                
                if (itemIcon && itemIcon.value) {
                    // Should have icon
                    var iconHtml = '<i class="' + itemIcon.value + '" aria-hidden="true"></i>';
                    if (iconSpan) {
                        iconSpan.innerHTML = iconHtml;
                    } else if (titleContainer) {
                        // Create icon span
                        var newIconSpan = previewDocument.createElement('span');
                        newIconSpan.className = 'db-mega-menu-icon';
                        newIconSpan.innerHTML = iconHtml;
                        titleContainer.insertBefore(newIconSpan, titleContainer.firstChild);
                    }
                } else {
                    // Should not have icon
                    if (iconSpan) {
                        iconSpan.remove();
                    }
                }
                
                // Get or create dropdown icon
                var titleDiv = domItem.querySelector('.db-mega-menu-title');
                var dropdownIcon = domItem.querySelector('.db-mega-menu-dropdown-icon');
                
                if (hasDropdown) {
                    // Should have dropdown icon
                    if (!dropdownIcon && titleDiv) {
                        // Create dropdown icon
                        var iconHtml = createDropdownIconHtml(widgetId, index + 1);
                        titleDiv.insertAdjacentHTML('beforeend', iconHtml);
                    }
                    
                    // Update title class
                    if (titleDiv) {
                        titleDiv.classList.remove('link-only');
                        titleDiv.classList.add('e-click');
                    }
                } else {
                    // Should NOT have dropdown icon
                    if (dropdownIcon) {
                        dropdownIcon.remove();
                    }
                    
                    // Update title class
                    if (titleDiv) {
                        titleDiv.classList.add('link-only');
                        titleDiv.classList.remove('e-click');
                    }
                    
                    // Close if currently active
                    if (domItem.classList.contains('e-active')) {
                        domItem.classList.remove('e-active');
                        var content = domItem.querySelector('.db-mega-menu-content');
                        if (content) content.classList.remove('e-active');
                        
                        // Clear from state
                        delete window.dbMegaMenuActiveState[widgetId];
                    }
                }
            });
            
        } catch (e) {
        }
    }
    
    /**
     * Create HTML for dropdown icon
     */
    function createDropdownIconHtml(widgetId, index) {
        var iconId = 'db-mega-menu-dropdown-icon-' + widgetId + index;
        var contentId = 'db-mega-menu-content-' + widgetId + index;
        
        return '<button id="' + iconId + '" class="db-mega-menu-dropdown-icon e-focus" ' +
            'data-tab-index="' + index + '" ' +
            'aria-haspopup="true" aria-expanded="false" ' +
            'aria-controls="' + contentId + '">' +
            '<span class="db-mega-menu-dropdown-icon-opened">' +
            '<i aria-hidden="true" class="eicon-caret-up"></i>' +
            '</span>' +
            '<span class="db-mega-menu-dropdown-icon-closed">' +
            '<i aria-hidden="true" class="eicon-caret-down"></i>' +
            '</span>' +
            '</button>';
    }

})();

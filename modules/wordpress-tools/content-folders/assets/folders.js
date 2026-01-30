/**
 * dailybuddy Content Folders v5.0 - PERFORMANCE OPTIMIZED
 * 
 * Improvements:
 * - URL parameter support (no page reload, uses History API)
 * - Batch API for grid labels (100 images = 1 request instead of 100)
 * - Better performance and caching
 */

(function ($) {
    'use strict';

    var dailybuddyFolders = {
        sidebarWidth: 275,
        minWidth: 200,
        maxWidth: 500,
        isResizing: false,
        currentFolderId: null,
        expandedFolders: [], // Track expanded folders

        init: function () {
            this.cacheDom();
            this.expandedFolders = this.loadExpandedFolders();
            this.detectWPAdminWidth();
            this.bindEvents();
            this.loadFolders();
            this.initDragAndDrop();
            this.checkSidebarState();
            this.initResizer();
            this.watchWPAdminMenu();
            this.initFolderHierarchy();

            // Erst wenn alles gesetzt ist, Animationen aktivieren
            var self = this;
            window.requestAnimationFrame(function () {
                $('body').addClass('sb-folders-ready');
            });

            // ★ NEW: Check URL parameter for initial folder
            var initialFolder = (typeof sbToolboxFolders !== 'undefined' && sbToolboxFolders.currentFolder)
                ? sbToolboxFolders.currentFolder
                : 'all';

            this.currentFolderId = initialFolder;

            // Apply initial filter after folders are loaded
            setTimeout(function () {
                if (initialFolder !== 'all') {
                    self.filterByFolder(initialFolder);
                } else {
                    $('.folder-item[data-folder-id="all"]').addClass('active');
                }
            }, 300);
        },

        cacheDom: function () {
            this.$sidebar = $('#dailybuddy-folders-sidebar');
            this.$toggle = $('#dailybuddy-folders-toggle');
            this.$toggleIcon = this.$toggle.find('.dashicons');

            this.$newFolderBtn = $('#dailybuddy-new-folder-btn');
            this.$newFolderForm = $('#dailybuddy-new-folder-form');
            this.$folderNameInput = $('#dailybuddy-folder-name-input');
            this.$createFolderBtn = $('#dailybuddy-create-folder-btn');
            this.$cancelFolderBtn = $('#dailybuddy-cancel-folder-btn');

            // Rename
            this.$renameFolderForm = $('#dailybuddy-rename-folder-form');
            this.$renameFolderInput = $('#dailybuddy-rename-folder-input');
            this.$renameFolderSaveBtn = $('#dailybuddy-rename-folder-save-btn');
            this.$renameFolderCancelBtn = $('#dailybuddy-rename-folder-cancel-btn');

            this.$folderTree = $('#folders-tree');
            this.$folderSearch = $('#dailybuddy-folder-search');
            this.$wpList = $('.wp-list-table tbody');
            this.$foldersTreeContainer = $('.folders-tree-container');

            // Flag for current rename folder
            this.renameFolderId = null;
            this.$renameFolderItem = null;
        },

        startSidebarLoading: function () {
            if (this.$foldersTreeContainer) {
                this.$foldersTreeContainer.addClass('loading');
            }
        },

        stopSidebarLoading: function () {
            if (this.$foldersTreeContainer) {
                this.$foldersTreeContainer.removeClass('loading');
            }
        },

        detectWPAdminWidth: function () {
            var $body = $('body');
            var windowWidth = $(window).width();

            // WordPress responsive logic:
            // - folded: always 36px
            // - auto-fold + <960px: 36px (WordPress collapses menu automatically)
            // - auto-fold + >960px: 160px (menu still open)
            // - <782px: 0 (menu hidden on mobile)

            var isFolded = $body.hasClass('folded');
            var isAutoFoldAndNarrow = $body.hasClass('auto-fold') && windowWidth <= 960;
            var isMobile = windowWidth <= 782;

            var adminWidth;
            if (isMobile) {
                adminWidth = 0;
            } else if (isFolded || isAutoFoldAndNarrow) {
                adminWidth = 36;
            } else {
                adminWidth = 160;
            }

            // Update CSS variables
            document.documentElement.style.setProperty('--wp-menu-width', adminWidth + 'px');
            document.documentElement.style.setProperty('--sidebar-width', this.sidebarWidth + 'px');

        },

        watchWPAdminMenu: function () {
            var self = this;

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.attributeName === 'class') {
                        self.detectWPAdminWidth();
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });

            $(window).on('resize', function () {
                self.detectWPAdminWidth();
            });
        },

        initFolderHierarchy: function () {
            var self = this;

            // Expand/Collapse Toggle
            $(document).on('click', '.folder-toggle', function (e) {
                e.stopPropagation();
                var $toggle = $(this);
                var $item = $toggle.closest('.folder-tree-item');
                var $children = $item.siblings('.folder-children');
                var folderId = $item.data('folder-id');

                if ($children.is(':visible')) {
                    // Collapse
                    $children.slideUp(200);
                    $toggle.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-right-alt2');
                    $item.removeClass('expanded');
                    // Remove from expandedFolders
                    var index = self.expandedFolders.indexOf(folderId);
                    if (index > -1) {
                        self.expandedFolders.splice(index, 1);
                        self.saveExpandedFolders();
                    }
                } else {
                    // Expand
                    $children.slideDown(200);
                    $toggle.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
                    $item.addClass('expanded');
                    // Add to expandedFolders
                    if (self.expandedFolders.indexOf(folderId) === -1) {
                        self.expandedFolders.push(folderId);
                        self.saveExpandedFolders();
                    }
                }
            });

            // Folder Drag & Drop MIT VERZÖGERUNG
            var dragging = null;
            var $ghost = null;
            var dragStartTimer = null;
            var isDragging = false;
            var dragDelay = 180; // ms

            $(document).on('mousedown', '.folder-tree-item', function (e) {
                // Ignore clicks auf Actions, Toggle oder Count
                if ($(e.target).closest('.folder-actions-toggle, .folder-actions, .folder-toggle, .folder-count').length) {
                    return;
                }

                var $item = $(this);
                var startEvent = e;

                dragging = $item.data('folder-id');
                isDragging = false;

                // Funktion, die den Drag WIRKLICH startet
                function startFolderDrag(ev) {
                    if (isDragging) return;
                    isDragging = true;

                    var $li = $item.closest('.folder-li');
                    var isNested = $li.parents('.folder-children').length > 0;

                    // Root-Dropzone nur für verschachtelte Ordner anzeigen
                    if (isNested) {
                        $('#folder-root-dropzone').addClass('visible');
                    }

                    // Ghost erstellen
                    $ghost = $item.clone().addClass('folder-drag-ghost');
                    $ghost.css({
                        position: 'fixed',
                        zIndex: 10000,
                        pointerEvents: 'none',
                        opacity: 0.7,
                        left: ev.pageX + 10,
                        top: ev.pageY + 10
                    });
                    $('body').append($ghost);

                    $item.addClass('dragging');
                    $('.folder-tree-item').not($item).addClass('droppable');

                    // Ghost bewegen + Hover-States
                    $(document).on('mousemove.folderDrag', function (moveEv) {
                        $ghost.css({
                            left: moveEv.pageX + 10,
                            top: moveEv.pageY + 10
                        });

                        var $folderItem = $(moveEv.target).closest('.folder-tree-item, .folder-item');
                        $('.folder-tree-item, .folder-item').removeClass('drop-hover');
                        if ($folderItem.length) {
                            $folderItem.addClass('drop-hover');
                        }
                    });
                }

                // Verzögerung: erst nach dragDelay ms starten
                dragStartTimer = setTimeout(function () {
                    startFolderDrag(startEvent);
                }, dragDelay);

                // Mouseup: entweder Klick (kein Drag) oder Drop (Drag aktiv)
                $(document).on('mouseup.folderDrag', function (upEv) {
                    clearTimeout(dragStartTimer);
                    $(document).off('mouseup.folderDrag mousemove.folderDrag');

                    // WENN der Drag nie gestartet wurde → normaler Klick, nichts tun
                    if (!isDragging) {
                        dragging = null;
                        return;
                    }

                    // Ab hier: echter Drop
                    if ($ghost) {
                        $ghost.remove();
                    }
                    $('.folder-tree-item').removeClass('dragging droppable drop-hover');
                    $('#folder-root-dropzone').removeClass('visible');

                    if (window.getSelection) {
                        window.getSelection().removeAllRanges();
                    }

                    var $target = $(upEv.target).closest('.folder-tree-item');
                    var $rootTarget = $(upEv.target).closest('#folder-root-dropzone');

                    if ($rootTarget.length) {
                        // Root-Dropzone → nach Root verschieben
                        self.moveFolderToParent(dragging, 0);

                    } else if ($target.length && $target.data('folder-id') != dragging) {
                        // Auf anderen Ordner gedroppt
                        self.moveFolderToParent(dragging, $target.data('folder-id'));

                    } else if ($(upEv.target).closest('#folders-tree').length && !$target.length) {
                        // Hintergrund im Baum → ebenfalls Root
                        self.moveFolderToParent(dragging, 0);
                    }

                    dragging = null;
                    $ghost = null;
                });
            });

            // Hover effect while dragging bleibt wie gehabt
            $(document).on('mouseenter', '.folder-tree-item.droppable', function () {
                if (dragging && isDragging) {
                    $(this).addClass('drop-hover');
                }
            }).on('mouseleave', '.folder-tree-item.droppable', function () {
                $(this).removeClass('drop-hover');
            });

        },

        moveFolderToParent: function (folderId, newParentId) {
            var self = this;

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_move_folder',
                    nonce: sbToolboxFolders.nonce,
                    folder_id: folderId,
                    new_parent_id: newParentId,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success) {
                        // Reload folder tree
                        self.loadFolders();
                    } else {
                        alert(response.data.message || 'Error moving folder');
                    }
                },
                error: function () {
                    alert('Error moving folder');
                }
            });
        },

        initResizer: function () {
            var self = this;

            if (!this.$sidebar.find('.dailybuddy-folders-resizer').length) {
                this.$sidebar.append('<div class="dailybuddy-folders-resizer"></div>');
            }

            var $resizer = this.$sidebar.find('.dailybuddy-folders-resizer');
            var startX, startWidth;

            $resizer.on('mousedown', function (e) {
                self.isResizing = true;
                startX = e.pageX;
                startWidth = self.$sidebar.width();

                $resizer.addClass('resizing');
                $('body').addClass('dailybuddy-resizing');

                $('body').css({
                    'user-select': 'none',
                    'cursor': 'ew-resize'
                });

                e.preventDefault();
            });

            $(document).on('mousemove', function (e) {
                if (!self.isResizing) return;

                var delta = e.pageX - startX;
                var newWidth = Math.max(self.minWidth, Math.min(self.maxWidth, startWidth + delta));

                self.sidebarWidth = newWidth;
                self.$sidebar.css('width', newWidth + 'px');
                document.documentElement.style.setProperty('--sidebar-width', newWidth + 'px');

                e.preventDefault();
            });

            $(document).on('mouseup', function () {
                if (self.isResizing) {
                    self.isResizing = false;
                    $resizer.removeClass('resizing');
                    $('body').removeClass('dailybuddy-resizing');
                    $('body').css({
                        'user-select': '',
                        'cursor': ''
                    });

                    localStorage.setItem('sbToolboxFoldersSidebarWidth', self.sidebarWidth);
                }
            });

            var savedWidth = localStorage.getItem('sbToolboxFoldersSidebarWidth');
            if (savedWidth) {
                savedWidth = parseInt(savedWidth, 10);
                if (savedWidth >= self.minWidth && savedWidth <= self.maxWidth) {
                    self.sidebarWidth = savedWidth;
                    self.$sidebar.css('width', savedWidth + 'px');
                    document.documentElement.style.setProperty('--sidebar-width', savedWidth + 'px');
                }
            }
        },

        bindEvents: function () {
            var self = this;

            this.$toggle.on('click', function () {
                self.toggleSidebar();
            });

            this.$newFolderBtn.on('click', function () {
                self.showNewFolderForm();
            });

            this.$createFolderBtn.on('click', function () {
                self.createFolder();
            });

            this.$cancelFolderBtn.on('click', function () {
                self.hideNewFolderForm();
            });

            this.$folderNameInput.on('keypress', function (e) {
                if (e.which === 13) {
                    self.createFolder();
                }
            });

            // Rename-Formular speichern
            this.$renameFolderSaveBtn.on('click', function () {
                self.performRenameFolder();
            });

            // Rename-Formular abbrechen
            this.$renameFolderCancelBtn.on('click', function () {
                self.hideRenameFolderForm();
            });

            // Enter im Rename-Input
            this.$renameFolderInput.on('keypress', function (e) {
                if (e.which === 13) {
                    self.performRenameFolder();
                }
            });

            // Special folder clicks
            $(document).on('click', '.folder-item', function (e) {
                e.preventDefault();
                self.selectSpecialFolder($(this));
            });

            // Folder tree item clicks
            $(document).on('click', '.folder-tree-item', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.selectFolderItem($(this));
            });

            // Context menu toggle
            $(document).on('click', '.folder-actions-toggle', function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggleFolderActions($(this));
            });

            // Context menu actions
            $(document).on('click', '.folder-action-rename', function (e) {
                e.preventDefault();
                self.renameFolder($(this).closest('.folder-tree-item'));
            });

            $(document).on('click', '.folder-action-delete', function (e) {
                e.preventDefault();
                self.deleteFolder($(this).closest('.folder-tree-item'));
            });

            $(document).on('click', '.folder-action-color', function (e) {
                e.preventDefault();
                self.changeFolderColor($(this).closest('.folder-tree-item'));
            });

            // Click on folder badge to filter
            $(document).on('click', '.dailybuddy-folder-badge', function (e) {
                e.preventDefault();
                var folderId = $(this).data('folder-id');
                if (folderId) {
                    self.expandAndSelectFolder(folderId);
                }
            });

            this.$folderSearch.on('keyup', function () {
                var query = $(this).val().toLowerCase().trim();

                var $treeLis = $('.folders-tree .folder-li');

                // Alte Hervorhebungen entfernen
                $('.folder-name').each(function () {
                    var original = $(this).text();
                    $(this).text(original);
                });

                if (query === '') {
                    $treeLis.show();
                    $('.folder-children').show();
                    $('.folder-tree-item.has-children').addClass('expanded');
                    return;
                }

                // Alles ausblenden
                $treeLis.hide();
                $('.folder-children').hide();
                $('.folder-tree-item.has-children').removeClass('expanded');

                // Treffer suchen
                $('.folder-name').each(function () {
                    var $name = $(this);
                    var text = $name.text();
                    var lower = text.toLowerCase();

                    var idx = lower.indexOf(query);

                    if (idx !== -1) {

                        // ----- 1️⃣ HIGHLIGHT -----
                        var before = text.substring(0, idx);
                        var match = text.substring(idx, idx + query.length);
                        var after = text.substring(idx + query.length);

                        $name.html(
                            before +
                            '<span class="search-highlight">' + match + '</span>' +
                            after
                        );

                        // ----- 2️⃣ Sichtbarkeit herstellen -----
                        var $li = $name.closest('.folder-li');
                        $li.show();
                        $li.parents('.folder-li').show();
                        $li.parents('.folder-children').show();

                        $li.parents('.folder-children')
                            .prev('.folder-tree-item.has-children')
                            .addClass('expanded');
                    }
                });
            });

            // Close context menus when clicking outside
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.folder-actions').length && !$(e.target).closest('.folder-actions-toggle').length) {
                    $('.folder-actions').removeClass('active');
                }
            });
        },

        toggleSidebar: function () {
            this.$sidebar.toggleClass('active');
            $('body').toggleClass('dailybuddy-folders-active');

            var active = this.$sidebar.hasClass('active');
            localStorage.setItem('sbToolboxFoldersSidebarActive', active ? '1' : '0');

            // Icon anpassen
            this.updateToggleIcon();
        },

        updateToggleIcon: function () {
            if (!this.$toggleIcon || !this.$toggleIcon.length) {
                return;
            }

            // Sidebar AKTIV → Pfeil nach links
            if (this.$sidebar.hasClass('active')) {
                this.$toggleIcon
                    .removeClass('dashicons-arrow-right-alt2')
                    .addClass('dashicons-arrow-left-alt2');
            } else {
                // Sidebar NICHT aktiv → Pfeil nach rechts
                this.$toggleIcon
                    .removeClass('dashicons-arrow-left-alt2')
                    .addClass('dashicons-arrow-right-alt2');
            }
        },

        checkSidebarState: function () {
            var active = localStorage.getItem('sbToolboxFoldersSidebarActive');
            if (active === '1') {
                this.$sidebar.addClass('active');
                $('body').addClass('dailybuddy-folders-active');
            } else {
                this.$sidebar.removeClass('active');
                $('body').removeClass('dailybuddy-folders-active');
            }

            this.updateToggleIcon();
        },

        loadFolders: function () {
            var self = this;

            // Loader AN
            self.startSidebarLoading();

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_get_folder_tree',
                    nonce: sbToolboxFolders.nonce,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success && response.data.tree) {
                        self.renderFolders(response.data.tree);
                        self.updateCounts(response.data);
                        self.restoreActiveFolder();
                    }
                },
                complete: function () {
                    self.stopSidebarLoading();
                }
            });
        },

        restoreActiveFolder: function () {
            if (!this.currentFolderId) {
                this.currentFolderId = 'all';
            }

            // Only update if not already active (avoid flicker)
            var $targetFolder;
            if (this.currentFolderId === 'all') {
                $targetFolder = $('.folder-item[data-folder-id="all"]');
            } else if (this.currentFolderId === 'unassigned') {
                $targetFolder = $('.folder-item[data-folder-id="unassigned"]');
            } else {
                $targetFolder = $('.folder-tree-item[data-folder-id="' + this.currentFolderId + '"]');
            }

            // Only update if not already active
            if ($targetFolder.length && !$targetFolder.hasClass('active')) {
                $('.folder-item, .folder-tree-item').removeClass('active');
                $targetFolder.addClass('active');
            }
        },

        renderFolders: function (tree) {
            var html = '';

            html += '<div id="folder-root-dropzone" class="folder-root-dropzone">';
            html += sbToolboxFolders.strings.rootDrop;
            html += '</div>';

            html += this.buildFolderTree(tree);

            this.$folderTree.html(html);
            this.restoreExpandedFolders();
        },

        buildFolderTree: function (items, level) {
            level = level || 0;
            var self = this;
            var html = '<ul class="folder-level-' + level + '">';

            $.each(items, function (i, item) {
                var hasChildren = item.children && item.children.length > 0;
                var indent = level * 1; // 20px per level

                html += '<li class="folder-li" data-folder-id="' + item.id + '">';
                html += '<div class="folder-tree-item' + (hasChildren ? ' has-children' : '') + '" data-folder-id="' + item.id + '" style="padding-left: ' + indent + 'px;">';

                // Expand/Collapse Icon
                if (hasChildren) {
                    html += '<span class="folder-toggle dashicons dashicons-arrow-right-alt2"></span>';
                } else {
                    html += '<span class="folder-spacer"></span>';
                }

                html += '<span class="dashicons dashicons-category"></span>';
                html += '<span class="folder-name">' + item.name + '</span>';
                html += '<span class="folder-count">' + (item.count || 0) + '</span>';

                // Actions toggle (shows on hover)
                html += '<span class="folder-actions-toggle">';
                html += '<span class="dashicons dashicons-ellipsis"></span>';
                html += '</span>';

                // Context menu
                html += '<div class="folder-actions">';
                html += '<a href="#" class="folder-action folder-action-rename" title="Rename">';
                html += '<span class="context-menu-icon dashicons dashicons-edit"></span> Rename';
                html += '</a>';
                html += '<a href="#" class="folder-action folder-action-delete" title="Delete">';
                html += '<span class="context-menu-icon dashicons dashicons-trash"></span> Delete';
                html += '</a>';
                html += '</div>';

                html += '</div>';

                // Children container
                if (hasChildren) {
                    html += '<div class="folder-children" style="display: none;">';
                    html += self.buildFolderTree(item.children, level + 1);
                    html += '</div>';
                }

                html += '</li>';
            });

            html += '</ul>';
            return html;
        },

        updateCounts: function (data) {
            // Update special folders
            $('#folder-count-all').text(data.total || 0);
            $('#folder-count-unassigned').text(data.unassigned || 0);

            // Update regular folders
            if (data.counts) {
                $.each(data.counts, function (folderId, count) {
                    $('.folder-tree-item[data-folder-id="' + folderId + '"] .folder-count').text(count);
                });
            }
        },

        selectSpecialFolder: function ($folder) {
            $('.folder-item, .folder-tree-item').removeClass('active');
            $folder.addClass('active');

            var folderId = $folder.data('folder-id');
            this.filterByFolder(folderId);
        },

        selectFolderItem: function ($item) {
            // Don't set active here - let filterByFolder handle it to avoid flicker
            var folderId = $item.data('folder-id');
            this.currentFolderId = folderId;
            this.filterByFolder(folderId);
        },

        filterByFolder: function (folderId) {
            var self = this;

            // Store current folder
            this.currentFolderId = folderId;

            // Update active state in sidebar (avoid flicker)
            var $targetFolder;
            if (folderId === 'all') {
                $targetFolder = $('.folder-item[data-folder-id="all"]');
            } else if (folderId === 'unassigned') {
                $targetFolder = $('.folder-item[data-folder-id="unassigned"]');
            } else {
                $targetFolder = $('.folder-tree-item[data-folder-id="' + folderId + '"]');
            }

            // Only update if not already active
            if ($targetFolder.length && !$targetFolder.hasClass('active')) {
                $('.folder-item, .folder-tree-item').removeClass('active');
                $targetFolder.addClass('active');
            }

            var visibleCount = 0;

            // Filter LIST VIEW (table rows)
            if (folderId === 'all') {
                // Show all
                this.$wpList.find('tr').show();
                visibleCount = this.$wpList.find('tr').length;
            } else if (folderId === 'unassigned') {
                // Show only unassigned
                this.$wpList.find('tr').each(function () {
                    var $row = $(this);
                    var hasFolder = $row.find('.dailybuddy-folder-badge:not(.unassigned)').length > 0;
                    if (!hasFolder) {
                        $row.show();
                        visibleCount++;
                    } else {
                        $row.hide();
                    }
                });
            } else {
                // Show only items from this folder
                this.$wpList.find('tr').each(function () {
                    var $row = $(this);
                    var rowFolderId = $row.find('.dailybuddy-folder-badge').data('folder-id');
                    if (rowFolderId == folderId) {
                        $row.show();
                        visibleCount++;
                    } else {
                        $row.hide();
                    }
                });
            }

            // Filter GRID VIEW (attachment items)
            if ($('.attachments-browser').length) {
                if (folderId === 'all') {
                    // Show all grid items
                    $('.attachment').show();
                } else if (folderId === 'unassigned') {
                    // Show only unassigned
                    $('.attachment').each(function () {
                        var $attachment = $(this);
                        var $label = $attachment.find('.sb-folder-label');
                        var labelFolderId = $label.data('folder-id');

                        if (!labelFolderId || labelFolderId === 'unassigned') {
                            $attachment.show();
                        } else {
                            $attachment.hide();
                        }
                    });

                } else {
                    // Show only items from this folder
                    $('.attachment').each(function () {
                        var $attachment = $(this);
                        var $label = $attachment.find('.sb-folder-label');
                        var labelFolderId = $label.data('folder-id');

                        if (labelFolderId == folderId) {
                            $attachment.show();
                        } else {
                            $attachment.hide();
                        }
                    });

                }
            }

            // Show empty message if no items
            this.showEmptyMessage(visibleCount, folderId);

            // ★ NEW: Update URL without page reload (History API)
            this.updateUrlParameter(folderId);

            // Update counts in badges if items were moved
            this.loadFolders();
        },

        /**
         * ★ NEW: Update URL parameter without page reload
         */
        updateUrlParameter: function (folderId) {
            if (typeof history === 'undefined' || !history.replaceState) {
                return; // Browser doesn't support History API
            }

            try {
                var url = new URL(window.location.href);

                if (folderId === 'all') {
                    // Remove parameter for "all"
                    url.searchParams.delete('dailybuddy_folder');
                } else {
                    // Set parameter for specific folder
                    url.searchParams.set('dailybuddy_folder', folderId);
                }

                // Update URL without reload
                history.replaceState(null, '', url.toString());
            } catch (e) {
                // Fallback for older browsers
            }
        },

        showEmptyMessage: function (visibleCount, folderId) {
            // Remove existing empty message
            $('.dailybuddy-empty-message').remove();

            if (visibleCount === 0 && folderId !== 'all') {
                var folderName = '';
                if (folderId === 'unassigned') {
                    folderName = 'Unassigned Files';
                } else {
                    folderName = $('.folder-tree-item[data-folder-id="' + folderId + '"] .folder-name').text();
                }

                // Count visible columns dynamically
                var $headerRow = $('.wp-list-table thead tr, .wp-list-table .widefat thead tr').first();
                var colspan = ($headerRow.find('th:visible').length + 1) || 20;

                var title = sbToolboxFolders.strings.emptyTitle;
                var descriptionTemplate = sbToolboxFolders.strings.emptyTemplate || '';
                var description = descriptionTemplate.replace('%s', folderName);

                var message = '<tr class="dailybuddy-empty-message no-items">' +
                    '<td colspan="' + colspan + '" style="text-align: center; padding: 40px 20px; color: #666;">' +
                    '<p style="font-size: 16px; margin: 0 0 8px 0;"><strong>' + title + '</strong></p>' +
                    '<p style="font-size: 14px; margin: 0; opacity: 0.7;">' + description + '</p>' +
                    '</td>' +
                    '</tr>';

                this.$wpList.prepend(message);
            }
        },

        showNewFolderForm: function () {
            this.$newFolderForm.slideDown(200);
            this.$folderNameInput.focus();
        },

        hideNewFolderForm: function () {
            this.$newFolderForm.slideUp(200);
            this.$folderNameInput.val('');
        },

        showRenameFolderForm: function ($item) {
            // evtl. New-Folder-Form verstecken
            this.hideNewFolderForm();

            this.renameFolderId = $item.data('folder-id');
            this.$renameFolderItem = $item;

            var currentName = $item.find('.folder-name').text().trim();

            this.$renameFolderInput.val(currentName);
            this.$renameFolderForm.slideDown(200);
            this.$renameFolderInput.focus().select();
        },

        hideRenameFolderForm: function () {
            this.$renameFolderForm.slideUp(200);
            this.$renameFolderInput.val('');
            this.renameFolderId = null;
            this.$renameFolderItem = null;
        },

        performRenameFolder: function () {
            var self = this;

            if (!this.renameFolderId || !this.$renameFolderItem) {
                this.hideRenameFolderForm();
                return;
            }

            var folderId = this.renameFolderId;
            var currentName = this.$renameFolderItem.find('.folder-name').text().trim();
            var newName = this.$renameFolderInput.val().trim();

            if (!newName || newName === currentName) {
                this.hideRenameFolderForm();
                return;
            }

            // Loader AN
            self.startSidebarLoading();

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_rename_folder',
                    nonce: sbToolboxFolders.nonce,
                    folder_id: folderId,
                    name: newName,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success) {

                        self.$renameFolderItem.find('.folder-name').text(newName);

                        $('.dailybuddy-folder-badge[data-folder-id="' + folderId + '"] span:last-child')
                            .text(newName);

                        $('.sb-folder-label[data-folder-id="' + folderId + '"] span:last-child')
                            .text(newName);

                        self.loadFolders();

                        self.hideRenameFolderForm();
                    } else {
                        alert(response.data && response.data.message ? response.data.message : 'Error renaming folder');
                    }
                },
                complete: function () {
                    // Loader AUS
                    self.stopSidebarLoading();
                }
            });
        },

        createFolder: function () {
            var self = this;
            var folderName = this.$folderNameInput.val().trim();

            if (!folderName) {
                alert('Please enter a folder name.');
                return;
            }

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_create_folder',
                    nonce: sbToolboxFolders.nonce,
                    name: folderName,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success) {
                        self.hideNewFolderForm();
                        self.loadFolders(); // Reload tree
                    } else {
                        alert(response.data.message || 'Error creating folder.');
                    }
                }
            });
        },

        // ========== DRAG & DROP ==========
        initDragAndDrop: function () {
            var self = this;

            // Make table rows draggable by drag handle
            this.$wpList.find('tr').each(function () {
                var $row = $(this);
                var $handle = $row.find('.dailybuddy-drag-handle');

                if ($handle.length) {
                    $handle.on('mousedown', function (e) {
                        e.preventDefault();
                        self.startDrag($row, e);
                    });
                }
            });

            // NEW: Watch for dynamically added rows
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        self.initDragAndDrop();
                    }
                });
            });

            if (this.$wpList.length) {
                observer.observe(this.$wpList[0], {
                    childList: true
                });
            }
        },

        startDrag: function ($row, startEvent) {
            var self = this;

            // 1. Standard: Posts/Seiten
            var pageTitle = $row.find('.row-title').text().trim();

            // 2. Mediathek (Listenansicht): Dateiname
            if (!pageTitle) {
                pageTitle = $row.find('.filename strong').text().trim();
            }

            // 3. Fallback: erster Link/strong in Titelspalte
            if (!pageTitle) {
                pageTitle = $row.find('td.column-title a, td.column-title strong')
                    .first()
                    .text()
                    .trim();
            }

            var pageIcon = $row.find('.dailybuddy-drag-handle').html() || '';

            var $dragHelper = $('<div class="dailybuddy-drag-helper-minimal">' +
                '<span class="drag-icon">' + pageIcon + '</span>' +
                '<span class="drag-title">' + pageTitle + '</span>' +
                '</div>');

            var postId = $row.attr('id') ? $row.attr('id').replace(/^(post|media)-/, '') : null;

            if (!postId) return;

            $dragHelper.addClass('dailybuddy-drag-helper');
            $dragHelper.css({
                position: 'fixed',
                left: startEvent.pageX + 10,
                top: startEvent.pageY + 10,
                width: 'auto',
                opacity: 0.8,
                zIndex: 10000,
                pointerEvents: 'none'
            });
            $('body').append($dragHelper);

            $row.addClass('dailybuddy-dragging');

            $(document).on('mousemove.drag', function (e) {
                $dragHelper.css({
                    left: e.pageX + 10,
                    top: e.pageY + 10
                });

                // Check if over folder
                var $folderItem = $(e.target).closest('.folder-tree-item, .folder-item');
                $('.folder-tree-item, .folder-item').removeClass('drop-hover');
                if ($folderItem.length) {
                    $folderItem.addClass('drop-hover');
                }
            });

            $(document).on('mouseup.drag', function (e) {
                $(document).off('mousemove.drag mouseup.drag');
                $dragHelper.remove();
                $row.removeClass('dailybuddy-dragging');

                // Check if dropped on folder
                var $folderItem = $(e.target).closest('.folder-tree-item, .folder-item');
                $('.folder-tree-item, .folder-item').removeClass('drop-hover');

                if ($folderItem.length) {
                    var folderId = $folderItem.data('folder-id');
                    if (folderId && folderId !== 'all' && folderId !== 'unassigned') {
                        self.assignToFolder(postId, folderId, $row);
                    } else if (folderId === 'unassigned') {
                        self.assignToFolder(postId, 0, $row); // Remove from folder
                    }
                }
            });
        },

        assignToFolder: function (postId, folderId, $row, $attachment) {
            var self = this;

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_assign_to_folder',
                    nonce: sbToolboxFolders.nonce,
                    post_id: postId,
                    folder_id: folderId,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (!response.success) {
                        alert(response.data && response.data.message ? response.data.message : 'Error assigning to folder');
                        return;
                    }

                    var folderName = response.data.folder_name;
                    var newFolderId = response.data.folder_id;

                    /* 1. LISTENANSICHT (Posts/Seiten/Media-Liste) */
                    if ($row && $row.length) {
                        self.updateFolderBadge($row, newFolderId, folderName);
                    }

                    /* 2. GRIDANSICHT (Media-Grid) */
                    if ($attachment && $attachment.length) {
                        // altes Label entfernen
                        $attachment.find('.sb-folder-label').remove();

                        var $label;
                        if (folderName && newFolderId) {
                            $label = $('<div class="sb-folder-label" data-folder-id="' + newFolderId + '">' +
                                '<span class="dashicons dashicons-category"></span>' +
                                '<span>' + folderName + '</span>' +
                                '</div>');
                        } else {
                            $label = $('<div class="sb-folder-label unassigned" data-folder-id="unassigned">' +
                                '<span class="dashicons dashicons-category"></span>' +
                                '<span>Unassigned</span>' +
                                '</div>');
                        }

                        $attachment.find('.attachment-preview').append($label);

                        // Klick zum Filtern
                        $label.on('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();

                            if (newFolderId && folderName) {
                                self.filterByFolder(newFolderId);
                            } else {
                                self.filterByFolder('unassigned');
                            }
                        });
                    }

                    if (self.currentFolderId) {
                        self.filterByFolder(self.currentFolderId);
                    } else {
                        self.loadFolders();
                    }
                }
            });
        },

        updateFolderBadge: function ($row, folderId, folderName) {
            var $folderCell = $row.find('.column-dailybuddy_folder');

            if (folderId && folderId !== 0) {
                var html = '<a href="#" class="dailybuddy-folder-badge" data-folder-id="' + folderId + '">';
                html += '<span class="dashicons dashicons-category"></span>';
                html += '<span>' + folderName + '</span>';
                html += '</a>';
                $folderCell.html(html);
            } else {
                $folderCell.html('<span class="dailybuddy-folder-badge unassigned">—</span>');
            }
        },

        // ========== CONTEXT MENU ==========
        toggleFolderActions: function ($toggle) {
            var $actions = $toggle.siblings('.folder-actions');
            var isActive = $actions.hasClass('active');

            // Close all other menus
            $('.folder-actions').removeClass('active');

            // Toggle this one
            if (!isActive) {
                $actions.addClass('active');
            }
        },

        renameFolder: function ($item) {
            this.showRenameFolderForm($item);
        },

        deleteFolder: function ($item) {
            var self = this;
            var folderId = $item.data('folder-id');
            var folderName = $item.find('.folder-name').text();

            if (!confirm('Delete folder "' + folderName + '"? Items will be unassigned.')) {
                return;
            }

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_delete_folder',
                    nonce: sbToolboxFolders.nonce,
                    folder_id: folderId,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success) {
                        // Remove LIVE
                        $item.closest('li').fadeOut(300, function () {
                            $(this).remove();
                        });

                        // Update badges in table
                        $('.dailybuddy-folder-badge[data-folder-id="' + folderId + '"]').replaceWith(
                            '<span class="dailybuddy-folder-badge unassigned">—</span>'
                        );

                        // Reload counts
                        self.loadFolders();
                    } else {
                        alert(response.data.message || 'Error deleting folder');
                    }
                }
            });
        },

        changeFolderColor: function ($item) {
            // TODO: Implement color picker
            alert('Color picker coming in v4.4');
        },

        // ========== GRID VIEW SUPPORT ==========
        initGridView: function () {
            var self = this;

            // Check if we're on media library
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                return;
            }

            // Wait for grid to load
            var checkInterval = setInterval(function () {
                if ($('.attachment').length > 0) {
                    clearInterval(checkInterval);
                    self.addFolderLabelsToGridBatch(function () {
                        self.initGridDragDrop();
                        // ★ Apply initial filter after labels are loaded
                        if (self.currentFolderId && self.currentFolderId !== 'all') {
                            self.filterByFolder(self.currentFolderId);
                        }
                    });
                }
            }, 500);

            // Watch for grid changes (pagination, search, etc.) with debounce
            var debounceTimer = null;
            var gridObserver = new MutationObserver(function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    self.addFolderLabelsToGridBatch();
                }, 300);
            });

            setTimeout(function () {
                var $grid = $('.attachments-browser .attachments');
                if ($grid.length) {
                    gridObserver.observe($grid[0], {
                        childList: true,
                        subtree: true
                    });
                }
            }, 2000);
        },

        /**
         * Refresh page after upload when folder filter is active
         */
        initUploadRefresh: function () {
            var self = this;
            var refreshScheduled = false;

            // Only listen for async-upload.php responses (the actual file upload)
            $(document).ajaxSuccess(function (event, xhr, settings) {
                // Only react to actual file uploads
                if (!settings.url || settings.url.indexOf('async-upload.php') === -1) {
                    return;
                }

                // Schedule ONE refresh after uploads complete
                if (!refreshScheduled) {
                    refreshScheduled = true;

                    // Wait 2 seconds for multiple uploads to finish
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }
            });
        },

        /**
         * ★ NEW: Batch API - Load all folder labels in ONE request
         * This replaces the old method that made 1 request per image
         */
        addFolderLabelsToGridBatch: function (callback) {
            var self = this;

            // Collect all attachment IDs that need labels
            var attachmentIds = [];
            var attachmentMap = {};

            $('.attachment').each(function () {
                var $attachment = $(this);

                // Skip if already has label
                if ($attachment.find('.sb-folder-label').length) {
                    return;
                }

                var attachmentId = $attachment.data('id');
                if (attachmentId) {
                    attachmentIds.push(attachmentId);
                    attachmentMap[attachmentId] = $attachment;
                }
            });

            if (attachmentIds.length === 0) {
                if (callback) callback();
                return;
            }

            // ★ ONE request for ALL attachments instead of N requests!
            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_get_posts_folders_batch',
                    nonce: sbToolboxFolders.nonce,
                    post_ids: attachmentIds,
                    taxonomy: sbToolboxFolders.taxonomy
                },
                success: function (response) {
                    if (response.success && response.data.folders) {
                        $.each(response.data.folders, function (postId, folderInfo) {
                            var $attachment = attachmentMap[postId];
                            if (!$attachment) return;

                            var folderName = folderInfo.folder_name;
                            var folderId = folderInfo.folder_id;

                            var $label;
                            if (folderName && folderId) {
                                $label = $('<div class="sb-folder-label" data-folder-id="' + folderId + '">' +
                                    '<span class="dashicons dashicons-category"></span>' +
                                    '<span>' + folderName + '</span>' +
                                    '</div>');
                            } else {
                                $label = $('<div class="sb-folder-label unassigned" data-folder-id="unassigned">' +
                                    '<span class="dashicons dashicons-category"></span>' +
                                    '<span>Unassigned</span>' +
                                    '</div>');
                            }

                            $attachment.find('.attachment-preview').append($label);

                            // Click handler for filtering
                            (function (id) {
                                $label.on('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    self.filterByFolder(id || 'unassigned');
                                });
                            })(folderId);
                        });
                    }

                    if (callback) callback();
                },
                error: function () {
                    if (callback) callback();
                }
            });
        },

        // Keep old method for backwards compatibility
        addFolderLabelsToGrid: function (callback) {
            this.addFolderLabelsToGridBatch(callback);
        },

        initGridDragDrop: function () {
            var self = this;

            $('.attachment').each(function () {
                var $attachment = $(this);

                $attachment.on('mousedown', function (e) {
                    // ★ Don't start drag on interactive elements (checkbox, buttons)
                    if ($(e.target).closest('button, input, .check').length) {
                        return;
                    }

                    // ★ Allow drag from anywhere on the attachment
                    e.preventDefault();
                    var attachmentId = $attachment.data('id');
                    var startX = e.pageX;
                    var startY = e.pageY;
                    var isDragging = false;
                    var $helper = null;

                    $(document).on('mousemove.griddrag', function (e) {
                        // ★ Only start drag after moving 5+ pixels (to allow normal clicks)
                        var distance = Math.sqrt(Math.pow(e.pageX - startX, 2) + Math.pow(e.pageY - startY, 2));

                        if (!isDragging && distance > 5) {
                            isDragging = true;

                            // Create small drag helper
                            $helper = $attachment.clone();
                            $helper.addClass('sb-grid-drag-helper');
                            $helper.css({
                                position: 'fixed',
                                left: e.pageX + 10,
                                top: e.pageY + 10,
                                width: '100px',
                                opacity: 0.8,
                                zIndex: 10000,
                                pointerEvents: 'none',
                                transform: 'scale(0.5)'
                            });
                            $('body').append($helper);

                            // Add dragging class to original
                            $attachment.addClass('sb-dragging');
                        }

                        if (isDragging && $helper) {
                            $helper.css({
                                left: e.pageX + 10,
                                top: e.pageY + 10
                            });

                            // Highlight folder on hover
                            var $folderItem = $(e.target).closest('.folder-tree-item, .folder-item');
                            $('.folder-tree-item, .folder-item').removeClass('drop-hover');
                            if ($folderItem.length) {
                                $folderItem.addClass('drop-hover');
                            }
                        }
                    });

                    $(document).on('mouseup.griddrag', function (e) {
                        if (isDragging) {
                            var $folderItem = $(e.target).closest('.folder-tree-item, .folder-item');
                            if ($folderItem.length) {
                                var folderId = $folderItem.data('folder-id');
                                if (folderId && folderId !== 'all') {
                                    if (folderId === 'unassigned') {
                                        folderId = 0;
                                    }

                                    self.assignToFolder(attachmentId, folderId, null, $attachment);
                                }
                            }

                            if ($helper) {
                                $helper.remove();
                            }
                            $attachment.removeClass('sb-dragging');
                        }

                        $(document).off('mousemove.griddrag mouseup.griddrag');
                        $('.folder-tree-item, .folder-item').removeClass('drop-hover');
                    });

                });
            });
        }
        ,

        expandAndSelectFolder: function (folderId) {
            var self = this;

            // First, find and expand all parent folders
            var $targetItem = $('.folder-tree-item[data-folder-id="' + folderId + '"]');
            if (!$targetItem.length) {
                // Folder not found, just filter
                self.filterByFolder(folderId);
                return;
            }

            // Find all parent folders by traversing up
            var $parents = $targetItem.parents('.folder-li');

            $parents.each(function () {
                var $parentLi = $(this);
                var $parentItem = $parentLi.children('.folder-tree-item');
                var parentFolderId = $parentItem.data('folder-id');
                var $parentChildren = $parentLi.children('.folder-children');
                var $parentToggle = $parentItem.find('.folder-toggle');

                if ($parentChildren.length && !$parentChildren.is(':visible')) {
                    // Expand parent WITHOUT animation (instant)
                    $parentChildren.show();
                    $parentToggle.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
                    $parentItem.addClass('expanded');

                    // Add to expandedFolders
                    if (self.expandedFolders.indexOf(parentFolderId) === -1) {
                        self.expandedFolders.push(parentFolderId);
                    }
                }
            });

            // Save expanded state
            self.saveExpandedFolders();

            // Now filter by the target folder
            self.filterByFolder(folderId);

            // Scroll to the folder in sidebar
            setTimeout(function () {
                if ($targetItem.length) {
                    var sidebarOffset = $('.folders-tree-container').offset().top;
                    var itemOffset = $targetItem.offset().top;
                    var scrollTop = $('.folders-tree-container').scrollTop();
                    var relativeTop = itemOffset - sidebarOffset + scrollTop - 100;

                    $('.folders-tree-container').animate({
                        scrollTop: relativeTop
                    }, 300);
                }
            }, 100);
        },

        loadExpandedFolders: function () {
            var saved = localStorage.getItem('sbToolboxExpandedFolders');
            if (saved) {
                try {
                    return JSON.parse(saved);
                } catch (e) {
                    return [];
                }
            }
            return [];
        },

        saveExpandedFolders: function () {
            localStorage.setItem('sbToolboxExpandedFolders', JSON.stringify(this.expandedFolders));
        },

        restoreExpandedFolders: function () {
            var self = this;
            setTimeout(function () {
                $.each(self.expandedFolders, function (i, folderId) {
                    var $item = $('.folder-tree-item[data-folder-id="' + folderId + '"]');
                    if ($item.length) {
                        var $toggle = $item.find('.folder-toggle');
                        var $children = $item.siblings('.folder-children');
                        if ($toggle.length && $children.length) {
                            $children.show();
                            $toggle.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-down-alt2');
                            $item.addClass('expanded');
                        }
                    }
                });
            }, 50);
        }

    };

    // ==========================================
    // MEDIA MODAL FOLDER DROPDOWN (MINIMAL)
    // Only for modals (Elementor, Gutenberg, etc.)
    // Media library uploads use PHP hook instead
    // ==========================================

    var MediaModalFolders = {
        folders: null,
        selectedFolderId: 0,

        init: function () {
            // Only initialize for modals, not on media library page
            if (window.location.href.indexOf('upload.php') === -1) {
                this.observeMediaModal();
                this.hookUploader();
            }
        },

        observeMediaModal: function () {
            var self = this;

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        mutation.addedNodes.forEach(function (node) {
                            if (node.nodeType === 1) {
                                var $node = $(node);
                                // Check if modal opened or upload UI appeared
                                if ($node.hasClass('media-modal') ||
                                    $node.find('.media-modal').length ||
                                    $node.hasClass('upload-ui') ||
                                    $node.find('.upload-ui').length) {
                                    setTimeout(function () {
                                        self.injectDropdown();
                                    }, 300);
                                }
                            }
                        });
                    }
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });

            // ★ Also periodically check for new upload UIs (backup for dynamic content)
            setInterval(function () {
                if ($('.media-modal:visible').length) {
                    self.injectDropdown();
                }
            }, 1000);
        },

        injectDropdown: function () {
            var self = this;

            // ★ Check all upload-ui elements (in modal and standalone)
            $('.media-modal .upload-ui, .media-frame .upload-ui').each(function () {
                var $uploadUI = $(this);

                // Skip if already has dropdown
                if ($uploadUI.find('.dailybuddy-folder-dropdown').length) return;

                // Skip if not visible
                if (!$uploadUI.is(':visible')) return;

                var html = '<div class="dailybuddy-folder-dropdown">' +
                    '<label class="folder-dropdown-label">' +
                    '<span class="dashicons dashicons-category"></span>' +
                    '<span>' + (sbToolboxFolders.strings.uploadToFolder || 'Upload to folder:') + '</span>' +
                    '</label>' +
                    '<select class="folder-dropdown-select">' +
                    '<option value="0">' + (sbToolboxFolders.strings.noFolder || 'No folder') + '</option>' +
                    '</select>' +
                    '</div>';

                var $maxSize = $uploadUI.find('.max-upload-size');
                if ($maxSize.length) {
                    $maxSize.after(html);
                } else {
                    $uploadUI.append(html);
                }

            });

            // Load folders for all dropdowns
            this.loadFolders();

            // ★ Use event delegation for change events
            $(document).off('change.dailybuddyModal', '.folder-dropdown-select');
            $(document).on('change.dailybuddyModal', '.folder-dropdown-select', function () {
                self.selectedFolderId = parseInt($(this).val()) || 0;

                // ★ Store in sessionStorage for persistence
                try {
                    sessionStorage.setItem('dailybuddy_modal_folder', self.selectedFolderId);
                } catch (e) { }
            });

            // ★ Restore from sessionStorage
            try {
                var storedFolder = sessionStorage.getItem('dailybuddy_modal_folder');
                if (storedFolder) {
                    self.selectedFolderId = parseInt(storedFolder) || 0;
                }
            } catch (e) { }
        },

        loadFolders: function () {
            var self = this;
            var $selects = $('.folder-dropdown-select');
            if (!$selects.length) return;

            // ★ Try to restore from sessionStorage first
            try {
                var storedFolder = sessionStorage.getItem('dailybuddy_modal_folder');
                if (storedFolder) {
                    self.selectedFolderId = parseInt(storedFolder) || 0;
                }
            } catch (e) { }

            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_get_folder_tree',
                    nonce: sbToolboxFolders.nonce,
                    taxonomy: 'dailybuddy_media_folder'
                },
                success: function (response) {
                    if (response.success && response.data.tree) {
                        // ★ Update all dropdowns
                        $selects.each(function () {
                            var $select = $(this);
                            // Keep first option, remove others
                            $select.find('option:not(:first)').remove();
                            self.renderFolderOptions(response.data.tree, $select, 0);

                            // ★ Restore selected value if any
                            if (self.selectedFolderId > 0) {
                                $select.val(self.selectedFolderId);
                            }
                        });
                    }
                }
            });
        },

        renderFolderOptions: function (folders, $select, level) {
            var self = this;
            var prefix = '';
            for (var i = 0; i < level; i++) {
                prefix += '— ';
            }

            folders.forEach(function (folder) {
                $select.append($('<option>', {
                    value: folder.id,
                    text: prefix + folder.name + ' (' + folder.count + ')'
                }));

                if (folder.children && folder.children.length) {
                    self.renderFolderOptions(folder.children, $select, level + 1);
                }
            });
        },

        hookUploader: function () {
            var self = this;

            if (window._modalFolderHooked) return;
            window._modalFolderHooked = true;

            if (typeof wp !== 'undefined' && wp.Uploader) {
                var origSuccess = wp.Uploader.prototype.success;
                wp.Uploader.prototype.success = function (attachment) {
                    if (origSuccess) {
                        origSuccess.apply(this, arguments);
                    }

                    // ★ Get the currently selected folder from ANY visible dropdown
                    var $visibleDropdown = $('.dailybuddy-folder-dropdown:visible .folder-dropdown-select');
                    var folderId = $visibleDropdown.length ? parseInt($visibleDropdown.val()) : self.selectedFolderId;


                    if (folderId > 0) {
                        self.assignToFolder(attachment.id, folderId);
                    }
                };
            }
        },

        assignToFolder: function (attachmentId, folderId) {
            if (!attachmentId || !folderId) return;


            $.ajax({
                url: sbToolboxFolders.ajaxurl,
                type: 'POST',
                data: {
                    action: 'dailybuddy_assign_to_folder',
                    nonce: sbToolboxFolders.nonce,
                    post_id: attachmentId,
                    folder_id: folderId,
                    taxonomy: 'dailybuddy_media_folder'
                },
                success: function (response) {
                },
                error: function (xhr, status, error) {
                }
            });
        }
    };

    // Initialize
    $(document).ready(function () {
        if (typeof sbToolboxFolders !== 'undefined') {
            if (sbToolboxFolders.isMediaModal) {
                MediaModalFolders.init();
            } else {
                dailybuddyFolders.init();

                if (sbToolboxFolders.postType === 'attachment') {
                    dailybuddyFolders.initGridView();

                    // Refresh page after upload if folder filter is active
                    if (sbToolboxFolders.currentFolder &&
                        sbToolboxFolders.currentFolder !== 'all') {
                        dailybuddyFolders.initUploadRefresh();
                    }
                }

                MediaModalFolders.init();
            }
        }
    });

})(jQuery);

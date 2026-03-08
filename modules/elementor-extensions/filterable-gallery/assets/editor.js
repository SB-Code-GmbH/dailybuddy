/**
 * Filterable Gallery - Elementor Editor Script
 * 
 * Handles gallery initialization and interactions in Elementor editor preview
 * ES5 Compatible - No Build Tools Required
 */

(function($) {
    'use strict';
    
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Initialize filterable gallery in editor
     */
    function initFilterableGallery($scope) {
        $('.dailybuddy-filter-gallery-container', $scope).each(function() {
            var $gallery = $(this),
                $settings = $gallery.data('settings'),
                fg_items = JSON.parse(atob($gallery.data("gallery-items"))),
                $layout_mode = ($settings.grid_style == 'masonry' ? 'masonry' : 'fitRows'),
                $gallery_enabled = ($settings.gallery_enabled == 'yes' ? true : false),
                input = $scope.find('#fg-search-box-input'),
                searchRegex, buttonFilter, timer;
            var $init_show_setting = $gallery.data("init-show");

            fg_items.splice(0, $init_show_setting);
            var filterControls = $scope.find(".fg-layout-3-filter-controls").eq(0);

            if ($gallery.closest($scope).length < 1) {
                return;
            }

            // init isotope
            var layoutMode = $('.dailybuddy-filter-gallery-wrapper').data('layout-mode');

            var $galleryWrap = $(".dailybuddy-filter-gallery-wrapper", $scope);
            var custom_default_control = $galleryWrap.data('custom_default_control');
            var default_control_key = $galleryWrap.data('default_control_key');
            custom_default_control = typeof(custom_default_control) !== 'undefined' ? parseInt(custom_default_control) : 0;
            default_control_key = typeof(default_control_key) !== 'undefined' ? parseInt(default_control_key) : 0;

            var $isotope_gallery = $gallery.isotope({
                itemSelector: '.dailybuddy-filterable-gallery-item-wrap',
                layoutMode: $layout_mode,
                percentPosition: true,
                filter: function() {
                    var $this = $(this);
                    var $result = searchRegex ? $this.text().match(searchRegex) : true;

                    if (buttonFilter == undefined) {
                        if (layoutMode != 'layout_3') {
                            buttonFilter = $scope.find('.dailybuddy-filter-gallery-control ul li').first().data('filter');
                        } else {
                            buttonFilter = $scope.find('.fg-layout-3-filter-controls li').first().data('filter');
                        }
                    }

                    var buttonResult = buttonFilter ? $this.is(buttonFilter) : true;
                    return $result && buttonResult;
                }
            });

            // Layout after images loaded (CRITICAL for Masonry!)
            $isotope_gallery.imagesLoaded().progress(function() {
                $isotope_gallery.isotope('layout');
            });

            // Extra layout for Masonry (sometimes needs multiple attempts)
            if ($layout_mode === 'masonry') {
                setTimeout(function() {
                    $isotope_gallery.isotope('layout');
                }, 100);
                setTimeout(function() {
                    $isotope_gallery.isotope('layout');
                }, 500);
            }

            // filter
            $scope.on("click", ".control", function() {
                var $this = $(this);
                var firstInit = parseInt($this.data('first-init'));
                buttonFilter = $(this).attr('data-filter');

                if ($scope.find('#fg-filter-trigger > span')) {
                    $scope.find('#fg-filter-trigger > span').text($this.text());
                }

                if (!firstInit) {
                    $this.data('first-init', 1);
                    var item_found = 0;
                    var index_list = [];
                    var $items = [];
                    for (var i = 0; i < fg_items.length; i++) {
                        var item = fg_items[i];
                        if (buttonFilter !== '' && buttonFilter !== '*') {
                            var element = $($(item)[0]);
                            if (element.is(buttonFilter)) {
                                ++item_found;
                                $items.push($(item)[0]);
                                index_list.push(i);
                            }
                        }

                        if (item_found === $init_show_setting) {
                            break;
                        }
                    }

                    if (index_list.length > 0) {
                        fg_items = fg_items.filter(function(item, index) {
                            return index_list.indexOf(index) === -1;
                        });
                    }
                }

                $this.siblings().removeClass("active");
                $this.addClass("active");
                if (!firstInit && $items.length > 0) {
                    $gallery.append($items);
                    $isotope_gallery.isotope('appended', $items);
                    $isotope_gallery.isotope();
                    $isotope_gallery.imagesLoaded().progress(function() {
                        $isotope_gallery.isotope("layout");
                    });
                } else {
                    $isotope_gallery.isotope();
                }

                if ($this.hasClass('all-control')) {
                    $('.dailybuddy-filterable-gallery-item-wrap .dailybuddy-magnific-link-clone').removeClass('active').addClass('active');
                } else {
                    $('.dailybuddy-filterable-gallery-item-wrap .dailybuddy-magnific-link-clone').removeClass('active');
                    $(buttonFilter + ' .dailybuddy-magnific-link-clone').addClass('active');
                }
            });

            // quick search
            input.on('input', function() {
                var $this = $(this);
                clearTimeout(timer);
                timer = setTimeout(function() {
                    searchRegex = new RegExp(escapeRegExp($this.val()), 'gi');
                    $isotope_gallery.isotope();
                }, 600);
            });

            // not necessary, just in case
            $isotope_gallery.imagesLoaded().progress(function() {
                $isotope_gallery.isotope('layout');
            });

            $(window).on("load", function() {
                $isotope_gallery.isotope("layout");
            });

            // layout gal, on click tabs
            $isotope_gallery.on("arrangeComplete", function() {
                $isotope_gallery.isotope("layout");
                var notFoundDiv = $('#dailybuddy-fg-no-items-found', $scope),
                    minHeight = notFoundDiv.css('font-size');

                $('.dailybuddy-filter-gallery-container', $scope).css('min-height', parseInt(minHeight) * 2 + 'px');

                if (!$isotope_gallery.data('isotope').filteredItems.length) {
                    $('#dailybuddy-fg-no-items-found').show();
                } else {
                    $('#dailybuddy-fg-no-items-found').hide();
                }
            });

            // resize
            $('.dailybuddy-filterable-gallery-item-wrap', $gallery).resize(function() {
                $isotope_gallery.isotope('layout');
            });

            // Load more button
            $scope.on("click", ".dailybuddy-gallery-load-more", function(e) {
                e.preventDefault();
                var $this = $(this),
                    $images_per_page = $gallery.data("images-per-page"),
                    $nomore_text = $gallery.data("nomore-item-text"),
                    filter_enable = $(".dailybuddy-filter-gallery-control", $scope).length,
                    $items = [];
                var filter_name = $(".dailybuddy-filter-gallery-control li.active", $scope).data('filter');
                if (filterControls.length > 0) {
                    filter_name = $(".fg-layout-3-filter-controls li.active", $scope).data('filter');
                }

                var item_found = 0;
                var index_list = [];
                for (var i = 0; i < fg_items.length; i++) {
                    var item = fg_items[i];
                    if (filter_name !== '' && filter_name !== '*' && filter_enable) {
                        var element = $($(item)[0]);
                        if (element.is(filter_name)) {
                            ++item_found;
                            $items.push($(item)[0]);
                            index_list.push(i);
                        }
                        if ((fg_items.length - 1) === i) {
                            $(".dailybuddy-filter-gallery-control li.active", $scope).data('load-more-status', 1);
                            $this.hide();
                        }
                    } else {
                        ++item_found;
                        $items.push($(item)[0]);
                        index_list.push(i);
                    }

                    if (item_found === $images_per_page) {
                        break;
                    }
                }

                if (index_list.length > 0) {
                    fg_items = fg_items.filter(function(item, index) {
                        return index_list.indexOf(index) === -1;
                    });
                }

                if (fg_items.length < 1) {
                    $this.html('<div class="no-more-items-text"></div>');
                    $this.children('.no-more-items-text').text($nomore_text);
                    setTimeout(function() {
                        $this.fadeOut("slow");
                    }, 600);
                }

                // append items
                $gallery.append($items);
                $isotope_gallery.isotope("appended", $items);
                $isotope_gallery.imagesLoaded().progress(function() {
                    $isotope_gallery.isotope("layout");
                });

                if (custom_default_control) {
                    var increment = $settings.control_all_text ? 2 : 1;
                    default_control_key = default_control_key + increment;
                    jQuery('.dailybuddy-filter-gallery-control li:nth-child(' + default_control_key + ')').trigger('click');
                }
            });
        });
    }
    
    // Run on document ready
    $(document).ready(function() {
        $('.elementor-widget-dailybuddy-filterable-gallery').each(function() {
            initFilterableGallery($(this));
        });
    });
    
})(jQuery);


/**
 * Tagify Enhancement for Gallery Filter Title
 *
 * Replaces the plain text input for "Gallery Filter Title" in each
 * gallery item with a Tagify-powered tag input. The dropdown shows
 * all filter titles defined in the Filter Controls repeater.
 *
 * Stored value format stays as comma-separated string for backward
 * compatibility (e.g. "Portfolio, Web Design").
 */
(function () {
    'use strict';

    if (typeof elementor === 'undefined' || typeof Tagify === 'undefined') {
        return;
    }

    var CONTROL_NAME = 'dailybuddy_fg_gallery_control_name';
    var FILTER_REPEATER = 'dailybuddy_fg_controls';
    var FILTER_FIELD = 'dailybuddy_fg_control';
    var debounceTimer = null;

    /**
     * Read the current filter titles from the Filter Controls repeater
     */
    function getFilterWhitelist(model) {
        var settings = model.get('settings');
        var controls = settings.get(FILTER_REPEATER);
        var whitelist = [];

        if (controls && controls.models) {
            controls.models.forEach(function (m) {
                var title = m.get(FILTER_FIELD);
                if (title && title.trim()) {
                    whitelist.push(title.trim());
                }
            });
        }

        return whitelist;
    }

    /**
     * Initialize Tagify on a single input element
     */
    function initTagifyOnInput(input, whitelist) {
        // Already initialized — just update whitelist
        if (input.__tagify) {
            input.__tagify.whitelist = whitelist;
            return;
        }

        var tagify = new Tagify(input, {
            whitelist: whitelist,
            enforceWhitelist: false,       // allow old/unknown tags (shown as invalid)
            keepInvalidTags: true,         // don't auto-remove invalid tags
            editTags: false,
            dropdown: {
                maxItems: 20,
                enabled: 0,               // show suggestions on focus
                closeOnSelect: false       // keep open for multi-select
            },
            delimiters: ',',
            originalInputValueFormat: function (valuesArr) {
                return valuesArr.map(function (item) {
                    return item.value;
                }).join(', ');
            }
        });

        // Sync value back to Elementor's data model
        tagify.on('change', function () {
            // Dispatch native events so Elementor picks up the change
            var evt;
            evt = new Event('input', { bubbles: true });
            input.dispatchEvent(evt);
            evt = new Event('change', { bubbles: true });
            input.dispatchEvent(evt);
        });

        // Store reference for later whitelist updates
        input.__tagify = tagify;
    }

    /**
     * Find all Gallery Filter Title inputs in the panel and init Tagify
     */
    function enhanceFilterInputs(panelEl, model) {
        var inputs = panelEl.querySelectorAll(
            '.elementor-control-' + CONTROL_NAME + ' input[data-setting="' + CONTROL_NAME + '"]'
        );

        if (!inputs.length) return;

        var whitelist = getFilterWhitelist(model);

        for (var i = 0; i < inputs.length; i++) {
            initTagifyOnInput(inputs[i], whitelist);
        }
    }

    /**
     * Update whitelist on all existing Tagify instances in the panel
     */
    function updateWhitelists(panelEl, model) {
        var whitelist = getFilterWhitelist(model);
        var inputs = panelEl.querySelectorAll(
            '.elementor-control-' + CONTROL_NAME + ' input[data-setting="' + CONTROL_NAME + '"]'
        );

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].__tagify) {
                inputs[i].__tagify.whitelist = whitelist;
            }
        }
    }

    /**
     * Set up a MutationObserver to catch lazy-rendered repeater items
     */
    function setupPanelObserver(panel, model) {
        var panelEl = panel.el || (panel.$el && panel.$el[0]);
        if (!panelEl) return;

        // Initial enhancement (with slight delay for DOM readiness)
        setTimeout(function () {
            enhanceFilterInputs(panelEl, model);
        }, 200);

        // Watch for new DOM nodes (repeater items being expanded)
        var observer = new MutationObserver(function () {
            if (debounceTimer) clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                enhanceFilterInputs(panelEl, model);
            }, 100);
        });

        observer.observe(panelEl, {
            childList: true,
            subtree: true
        });

        // Listen for filter controls repeater changes to update whitelist
        var settings = model.get('settings');
        if (settings) {
            settings.on('change:' + FILTER_REPEATER, function () {
                updateWhitelists(panelEl, model);
            });
        }

        // Clean up observer when panel changes to a different widget
        elementor.hooks.addAction(
            'panel/open_editor/widget',
            function cleanupOnce() {
                observer.disconnect();
                elementor.hooks.removeAction('panel/open_editor/widget', cleanupOnce);
            }
        );
    }

    // Hook into the panel opening for this specific widget
    elementor.hooks.addAction(
        'panel/open_editor/widget/dailybuddy-filterable-gallery',
        function (panel, model) {
            setupPanelObserver(panel, model);
        }
    );

})();

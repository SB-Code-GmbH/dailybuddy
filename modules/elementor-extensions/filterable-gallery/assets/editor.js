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

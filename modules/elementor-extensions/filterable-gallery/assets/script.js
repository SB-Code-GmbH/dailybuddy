/**
 * dailybuddy Filterable Gallery - Frontend Scripts
 * ES5 Compatible - No Build Tools Required
 */

// Initialize dailybuddy hooks object if not exists
if (typeof dailybuddy === 'undefined') {
    var dailybuddy = {
        hooks: {
            actions: {},
            addAction: function(hookName, namespace, callback, priority) {
                priority = priority || 10;
                var key = hookName + '.' + namespace;
                if (!this.actions[hookName]) {
                    this.actions[hookName] = [];
                }
                this.actions[hookName].push({
                    namespace: namespace,
                    callback: callback,
                    priority: priority
                });
            },
            doAction: function(hookName) {
                if (!this.actions[hookName]) return;
                var args = Array.prototype.slice.call(arguments, 1);
                this.actions[hookName].forEach(function(action) {
                    action.callback.apply(null, args);
                });
            }
        },
        elementStatusCheck: function(element) {
            return false;
        }
    };
}

jQuery(window).on("elementor/frontend/init", function () {
  function getVideoId(url) {
    var vimeoMatch = url.match(/\/\/(?:player\.)?vimeo.com\/(?:video\/)?([0-9]+)/);
    if (vimeoMatch) {
      return vimeoMatch[1];
    }
    var youtubeMatch = url.match(/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (youtubeMatch) {
      return youtubeMatch[1];
    }
    return null;
  }
  
  function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }
  
  function shuffleGalleryItems(items) {
    for (var i = 0; i < items.length - 1; i++) {
      var j = i + Math.floor(Math.random() * (items.length - i));
      var temp = items[j];
      items[j] = items[i];
      items[i] = temp;
    }
    return items;
  }
  
  function manageNotFoundDiv($isotope_gallery, $scope, $) {
    var notFoundDiv = $("#dailybuddy-fg-no-items-found", $scope);
    var minHeight = notFoundDiv.css("font-size");
    $(".dailybuddy-filter-gallery-container", $scope).css("min-height", parseInt(minHeight) * 2 + "px");
    if (!$isotope_gallery.data("isotope").filteredItems.length) {
      $("#dailybuddy-fg-no-items-found", $scope).show();
    } else {
      $("#dailybuddy-fg-no-items-found", $scope).hide();
    }
  }
  
  var filterableGalleryHandler = function filterableGalleryHandler($scope, $) {
    var localize = typeof localize !== 'undefined' ? localize : {};
    var filterControls = $scope.find(".fg-layout-3-filter-controls").eq(0);
    var filterTrigger = $scope.find("#fg-filter-trigger");
    var form = $scope.find(".fg-layout-3-search-box");
    var input = $scope.find("#fg-search-box-input");
    var searchRegex;
    var buttonFilter;
    var timer;
    
    var fg_mfp_counter_text = localize && localize.dailybuddy_translate_text ? localize.dailybuddy_translate_text.fg_mfp_counter_text : null;
    fg_mfp_counter_text = fg_mfp_counter_text ? "%curr% " + fg_mfp_counter_text + " %total%" : "%curr% of %total%";
    
    var $galleryWrap = $(".dailybuddy-filter-gallery-wrapper", $scope);
    var custom_default_control = $galleryWrap.data("custom_default_control");
    var default_control_key = $galleryWrap.data("default_control_key");
    custom_default_control = typeof custom_default_control !== "undefined" ? parseInt(custom_default_control) : 0;
    default_control_key = typeof default_control_key !== "undefined" ? parseInt(default_control_key) : 0;
    
    if (form.length) {
      form.on("submit", function (e) {
        e.preventDefault();
      });
    }
    
    filterTrigger.on("click", function () {
      filterControls.toggleClass("open-filters");
    });
    
    filterTrigger.on("blur", function () {
      filterControls.removeClass("open-filters");
    });
    
    $(".video-popup.dailybuddy-magnific-video-link.playout-vertical", $scope).on("click", function () {
      setTimeout(function () {
        $(".mfp-iframe-holder").addClass("dailybuddy-gf-vertical-video-popup");
      }, 1);
    });
    
    $(".dailybuddy-magnific-link", $scope).on("click", function () {
      setTimeout(function () {
        $(".mfp-wrap").addClass("dailybuddy-gf-mfp-popup");
      }, 1);
    });
    
    $(document).on("click", ".mfp-arrow.mfp-arrow-left, .mfp-arrow.mfp-arrow-right", function () {
      setTimeout(function () {
        var container = $(".dailybuddy-gf-mfp-popup .mfp-container");
        if (container.hasClass("mfp-iframe-holder")) {
          var src = $("iframe", container).attr("src");
          var plyBtn = $('a[href="' + src + '"]');
          if (plyBtn.length < 1) {
            var videoId = getVideoId(src);
            plyBtn = $('a[href*="' + videoId + '"]');
          }
          if (plyBtn.hasClass("playout-vertical")) {
            container.addClass("dailybuddy-gf-vertical-video-popup");
          } else {
            container.removeClass("dailybuddy-gf-vertical-video-popup");
          }
        }
      }, 1);
    });
    
    if (!isEditMode) {
      var $gallery = $(".dailybuddy-filter-gallery-container", $scope);
      var $settings = $gallery.data("settings");
      var fg_items = JSON.parse(atob($gallery.data("gallery-items")));
      var $layout_mode = $settings.grid_style === "masonry" ? "masonry" : "fitRows";
      var $gallery_enabled = $settings.gallery_enabled === "yes";
      var $images_per_page = $gallery.data("images-per-page");
      var $init_show_setting = $gallery.data("init-show");
      var $is_randomize = $gallery.data("is-randomize");
      
      var isRTL = $("body").hasClass("rtl");
      
      fg_items = fg_items.map(function (item) {
        return DOMPurify.sanitize(item);
      });
      
      if ("yes" === $is_randomize) {
        fg_items = shuffleGalleryItems(fg_items);
        $gallery.empty();
        for (var i = 0; i < $init_show_setting; i++) {
          $gallery.append(fg_items[i]);
        }
      }
      fg_items.splice(0, $init_show_setting);

      // init isotope
      var gwrap = $(".dailybuddy-filter-gallery-wrapper");
      var layoutMode = gwrap.data("layout-mode");
      var $isotope_gallery = $gallery.isotope({
        itemSelector: ".dailybuddy-filterable-gallery-item-wrap",
        layoutMode: $layout_mode,
        percentPosition: true,
        stagger: 30,
        transitionDuration: $settings.duration + "ms",
        isOriginLeft: !isRTL,
        filter: function filter() {
          var $this = $(this);
          var $result = searchRegex ? $this.text().match(searchRegex) : true;
          if (buttonFilter === undefined) {
            if (layoutMode !== "layout_3") {
              buttonFilter = $scope.find(".dailybuddy-filter-gallery-control ul li").first().data("filter");
            } else {
              buttonFilter = $scope.find(".fg-layout-3-filter-controls li").first().data("filter");
            }
          }
          var buttonResult = buttonFilter ? $this.is(buttonFilter) : true;
          return $result && buttonResult;
        }
      });

      // Note: Using Elementor's native lightbox via data-elementor-open-lightbox attribute

      // filter
      $scope.on("click", ".control", function () {
        var $this = $(this);
        buttonFilter = $(this).attr("data-filter");
        var initData = $(".dailybuddy-filter-gallery-container .dailybuddy-filterable-gallery-item-wrap" + buttonFilter, $scope).length;
        var $tspan = $scope.find("#fg-filter-trigger > span");
        if ($tspan.length) {
          $tspan.text($this.text());
        }
        var firstInit = parseInt($this.data("first-init"));
        if (!firstInit) {
          $this.data("first-init", 1);
          var item_found = initData;
          var index_list = [];
          var $items = [];
          if (typeof $images_per_page === "string") {
            $images_per_page = $init_show_setting;
          }
          if (item_found < $images_per_page) {
            // Convert for...of loop to traditional for loop
            for (var i = 0; i < fg_items.length; i++) {
              var item = fg_items[i];
              var index = i;
              
              if (buttonFilter !== "" && buttonFilter !== "*") {
                var element = $($(item)[0]);
                if (element.is(buttonFilter)) {
                  ++item_found;
                  $items.push($(item)[0]);
                  index_list.push(index);
                }
              }
              if (item_found >= $images_per_page) {
                break;
              }
            }
          }
          if ($items.length > 0) {
            $items = $items.filter(function (item) {
              return typeof item !== "number";
            });
          }
          if (index_list.length > 0) {
            fg_items = fg_items.filter(function (item, index) {
              return index_list.indexOf(index) === -1;
            });
          }
        }
        var LoadMoreShow = $(this).data("load-more-status");
        var loadMore = $(".dailybuddy-gallery-load-more", $scope);

        //hide load more button if selected control have no item to show
        var replaceWithDot = buttonFilter.replace(".", "");
        var restOfItem = fg_items.filter(function (galleryItem) {
          return galleryItem.includes(replaceWithDot);
        }).length;
        if (restOfItem < 1 && $this.data("filter") === "*") {
          var renderdItmes = $(".dailybuddy-filter-gallery-container .dailybuddy-filterable-gallery-item-wrap", $scope).length;
          var totalItems = $gallery.data("total-gallery-items");
          restOfItem = Number(totalItems) - Number(renderdItmes);
        }
        if (LoadMoreShow || restOfItem < 1) {
          loadMore.hide();
        } else {
          loadMore.show();
        }
        $this.siblings().removeClass("active");
        $this.addClass("active");
        if (!firstInit && $items.length > 0) {
          $isotope_gallery.isotope();
          $gallery.append($items);
          $isotope_gallery.isotope("appended", $items);
          $isotope_gallery.imagesLoaded().progress(function () {
            $isotope_gallery.isotope("layout");
          });

          //Listen for custom event when new items are loaded Grid Flow/Harmonic Layout
          jQuery(document).trigger("dailybuddy:filterable-gallery:items-loaded", [$scope.data("id")]);
        } else {
          $isotope_gallery.isotope();
        }
        if ($this.hasClass("all-control")) {
          //All items are active
          if (LoadMoreShow || fg_items.length <= 1) {
            loadMore.hide();
          } else {
            loadMore.show();
          }
        } else {
          $(buttonFilter + " .dailybuddy-magnific-link").addClass("active");
        }
        manageNotFoundDiv($isotope_gallery, $scope, $);
      });

      //key board accesibilty
      $(".dailybuddy-filter-gallery-control li.control", $scope).keydown(function (e) {
        if (e.key === "ArrowRight" || e.key === "ArrowLeft") {
          var tabs = $(".dailybuddy-filter-gallery-control li.control", $scope);
          var currentIndex = $(".dailybuddy-filter-gallery-control li.control.active", $scope);
          var index = currentIndex < 0 ? tabs.index(this) : tabs.index(currentIndex);
          if (e.key === "ArrowRight") index = (index + 1) % tabs.length;
          if (e.key === "ArrowLeft") index = (index - 1 + tabs.length) % tabs.length;
          $(tabs[index]).focus().click();
        }
      });
      $(".dailybuddy-filter-gallery-control li.control", $scope).attr("tabindex", "-1");
      $(".dailybuddy-filter-gallery-control li.control.active", $scope).attr("tabindex", "0");

      //quick search
      var loaded_on_search = false;
      input.on("input", function () {
        var $this = $(this);
        var $items = [];
        if (!loaded_on_search && $gallery.data("search-all") === "yes") {
          // Convert for...of loop to traditional for loop
          for (var i = 0; i < fg_items.length; i++) {
            var item = fg_items[i];
            $items.push($(item)[0]);
          }
          
          $isotope_gallery.isotope();
          $gallery.append($items);
          $isotope_gallery.isotope("appended", $items);
          $isotope_gallery.imagesLoaded().progress(function () {
            $isotope_gallery.isotope("layout");
          });
          $(".dailybuddy-gallery-load-more", $scope).hide();
          loaded_on_search = true;
        }
        clearTimeout(timer);
        timer = setTimeout(function () {
          searchRegex = new RegExp(escapeRegExp($this.val()), "gi");
          $isotope_gallery.isotope();
        }, 600);
      });

      // layout gal, while images are loading
      $isotope_gallery.imagesLoaded().progress(function () {
        $isotope_gallery.isotope("layout");
      });

      // layout gal, on click tabs
      $isotope_gallery.on("arrangeComplete", function () {
        manageNotFoundDiv($isotope_gallery, $scope, $);
      });

      // layout gal, after window loaded
      $(window).on("load", function () {
        $isotope_gallery.isotope("layout");
      });

      // Load more button
      $scope.on("click", ".dailybuddy-gallery-load-more", function (e) {
        e.preventDefault();
        var $this = $(this);
        var $nomore_text = $gallery.data("nomore-item-text");
        var filter_enable = $(".dailybuddy-filter-gallery-control", $scope).length;
        var $items = [];
        var filter_name = $(".dailybuddy-filter-gallery-control li.active", $scope).data("filter");
        if (filterControls.length > 0) {
          filter_name = $(".fg-layout-3-filter-controls li.active", $scope).data("filter");
        }
        if (filter_name === undefined) {
          filter_name = "*";
        }
        var item_found = 0;
        var index_list = [];
        
        // Convert for...of loop to traditional for loop
        for (var i = 0; i < fg_items.length; i++) {
          var item = fg_items[i];
          var index = i;
          
          var element = $($(item)[0]);
          if (element.is(filter_name)) {
            ++item_found;
            $items.push($(item)[0]);
            index_list.push(index);
          }
          if (filter_name !== "" && filter_name !== "*" && fg_items.length - 1 === index) {
            $(".dailybuddy-filter-gallery-control li.active", $scope).data("load-more-status", 1);
            $this.hide();
          }
          if (item_found === $images_per_page) {
            break;
          }
        }
        
        if (index_list.length > 0) {
          fg_items = fg_items.filter(function (item, index) {
            return index_list.indexOf(index) === -1;
          });
        }
        if (fg_items.length < 1) {
          $this.html('<div class="no-more-items-text"></div>');
          $this.children(".no-more-items-text").text($nomore_text);
          setTimeout(function () {
            $this.fadeOut("slow");
          }, 600);
        }

        // append items
        $gallery.append($items);
        $isotope_gallery.isotope("appended", $items);
        $isotope_gallery.imagesLoaded().progress(function () {
          $isotope_gallery.isotope("layout");
        });

        //Listen for custom event when new items are loaded Grid Flow/Harmonic Layout
        jQuery(document).trigger("dailybuddy:filterable-gallery:items-loaded", [$scope.data("id")]);
        manageNotFoundDiv($isotope_gallery, $scope, $);
      });

      // Fix issue on Safari: hide filter menu
      $(document).on("mouseup", function (e) {
        if (!filterTrigger.is(e.target) && filterTrigger.has(e.target).length === 0) {
          filterControls.removeClass("open-filters");
        }
      });
      
      $(document).ready(function () {
        if (window.location.hash) {
          jQuery("#" + window.location.hash.substring(1)).trigger("click");
        } else if (custom_default_control) {
          var increment = $settings.control_all_text ? 2 : 1;
          default_control_key = default_control_key + increment;
          jQuery(".dailybuddy-filter-gallery-control li:nth-child(" + default_control_key + ")").trigger("click");
        }
      });
      
      var FilterableGallery = function FilterableGallery(element) {
        $isotope_gallery.imagesLoaded().progress(function () {
          $isotope_gallery.isotope("layout");
        });
      };
      
      dailybuddy.hooks.addAction("ea-toggle-triggered", "ea", FilterableGallery);
      dailybuddy.hooks.addAction("ea-lightbox-triggered", "ea", FilterableGallery);
      dailybuddy.hooks.addAction("ea-advanced-tabs-triggered", "ea", FilterableGallery);
      dailybuddy.hooks.addAction("ea-advanced-accordion-triggered", "ea", FilterableGallery);
    }
  };
  
  if (dailybuddy.elementStatusCheck("dailybuddyFilterableGallery")) {
    return false;
  }
  
  elementorFrontend.hooks.addAction("frontend/element_ready/dailybuddy-filterable-gallery.default", filterableGalleryHandler);
});

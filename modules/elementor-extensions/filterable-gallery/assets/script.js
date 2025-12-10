/**
 * dailybuddy Filterable Gallery - Frontend Scripts
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

function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
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
    var notFoundDiv = $("#dailybuddy-fg-no-items-found", $scope),
      minHeight = notFoundDiv.css("font-size");
    $(".dailybuddy-filter-gallery-container", $scope).css("min-height", parseInt(minHeight) * 2 + "px");
    if (!$isotope_gallery.data("isotope").filteredItems.length) {
      $("#dailybuddy-fg-no-items-found", $scope).show();
    } else {
      $("#dailybuddy-fg-no-items-found", $scope).hide();
    }
  }
  var filterableGalleryHandler = function filterableGalleryHandler($scope, $) {
    var _localize;
    var filterControls = $scope.find(".fg-layout-3-filter-controls").eq(0),
      filterTrigger = $scope.find("#fg-filter-trigger"),
      form = $scope.find(".fg-layout-3-search-box"),
      input = $scope.find("#fg-search-box-input"),
      searchRegex,
      buttonFilter,
      timer,
      fg_mfp_counter_text = (_localize = localize) === null || _localize === void 0 || (_localize = _localize.dailybuddy_translate_text) === null || _localize === void 0 ? void 0 : _localize.fg_mfp_counter_text;
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
          var src = $("iframe", container).attr("src"),
            plyBtn = $('a[href="' + src + '"]');
          if (plyBtn.length < 1) {
            var videoId = getVideoId(src),
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
      var $gallery = $(".dailybuddy-filter-gallery-container", $scope),
        $settings = $gallery.data("settings"),
        fg_items = JSON.parse(atob($gallery.data("gallery-items"))),
        $layout_mode = $settings.grid_style === "masonry" ? "masonry" : "fitRows",
        $gallery_enabled = $settings.gallery_enabled === "yes",
        $images_per_page = $gallery.data("images-per-page"),
        $init_show_setting = $gallery.data("init-show"),
        $is_randomize = $gallery.data("is-randomize");
      isRTL = $("body").hasClass("rtl");
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

      // Popup
      $($scope).magnificPopup({
        delegate: ".dailybuddy-filterable-gallery-item-wrap:not([style*='display: none']) .dailybuddy-magnific-link.active",
        type: "image",
        gallery: {
          enabled: $gallery_enabled,
          tCounter: fg_mfp_counter_text
        },
        iframe: {
          markup: "<div class=\"mfp-iframe-scaler\">\n\t\t\t\t\t\t\t\t<div class=\"mfp-close\"></div>\n\t\t\t\t\t\t\t\t<iframe class=\"mfp-iframe dailybuddy-video-gallery-on\" frameborder=\"0\" allowfullscreen></iframe>\n\t\t\t\t\t\t\t\t<div class=\"dailybuddy-privacy-message\"></div>\n\t\t\t\t\t\t\t\t<div class=\"mfp-bottom-bar\">\n\t\t\t\t\t\t\t\t\t<div class=\"mfp-title\"></div>\n\t\t\t\t\t\t\t\t\t<div class=\"mfp-counter\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>"
        },
        callbacks: {
          markupParse: function markupParse(template, values, item) {
            if (item.el.attr("title") !== "") {
              values.title = item.el.attr("title");
            }
            if (item.el.hasClass('video-popup')) {
              var privacyNotice = $scope.find('.dailybuddy-filter-gallery-container').attr('data-privacy-notice');
              if (privacyNotice) {
                setTimeout(function () {
                  $('.dailybuddy-privacy-message').text(privacyNotice);
                }, 100);
              }
            }
          },
          open: function open() {
            var privacyNotice = $scope.find('.dailybuddy-filter-gallery-container').attr('data-privacy-notice');
            if (privacyNotice) {
              $('.dailybuddy-privacy-message').text(privacyNotice);
            }
            setTimeout(function () {
              $(".dailybuddy-privacy-message").remove();
            }, 5000);
            setTimeout(function () {
              var el_lightbox = $(".dialog-type-lightbox.elementor-lightbox");
              if (el_lightbox.length > 0) {
                el_lightbox.remove();
              }

              //Fix Safari pop video width issue.
              $(".e--ua-safari .dailybuddy-gf-mfp-popup iframe.mfp-iframe").on("load", function () {
                // Access the iframe's document
                var iframeDoc = this.contentDocument || this.contentWindow.document;
                var $video = $(iframeDoc).find("video");
                $video.removeClass("mac");
              });
            }, 100);
          }
        }
      });

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
          var index_list = $items = [];
          if (typeof $images_per_page === "string") {
            $images_per_page = $init_show_setting;
          }
          if (item_found < $images_per_page) {
            var _iterator = _createForOfIteratorHelper(fg_items.entries()),
              _step;
            try {
              for (_iterator.s(); !(_step = _iterator.n()).done;) {
                var _step$value = _slicedToArray(_step.value, 2),
                  index = _step$value[0],
                  item = _step$value[1];
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
            } catch (err) {
              _iterator.e(err);
            } finally {
              _iterator.f();
            }
          }
          if ($items.length > 0) {
            $items = $items.filter(function (item) {
              return typeof item !== "number";
            });
          }
          if (index_list.length > 0) {
            fg_items = fg_items.filter(function (item, index) {
              return !index_list.includes(index);
            });
          }
        }
        var LoadMoreShow = $(this).data("load-more-status"),
          loadMore = $(".dailybuddy-gallery-load-more", $scope);

        //hide load more button if selected control have no item to show
        var replaceWithDot = buttonFilter.replace(".", "");
        var restOfItem = fg_items.filter(function (galleryItem) {
          return galleryItem.includes(replaceWithDot);
        }).length;
        if (restOfItem < 1 && $this.data("filter") === "*") {
          var renderdItmes = $(".dailybuddy-filter-gallery-container .dailybuddy-filterable-gallery-item-wrap", $scope).length,
            totalItems = $gallery.data("total-gallery-items");
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

          // $('.dailybuddy-filterable-gallery-item-wrap .dailybuddy-magnific-link-clone').removeClass('active').addClass('active');
        } else {
          // $('.dailybuddy-filterable-gallery-item-wrap .dailybuddy-magnific-link-clone').removeClass('active');
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
        var $this = $(this),
          $items = [];
        if (!loaded_on_search && $gallery.data("search-all") === "yes") {
          var _iterator2 = _createForOfIteratorHelper(fg_items.entries()),
            _step2;
          try {
            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
              var _step2$value = _slicedToArray(_step2.value, 2),
                index = _step2$value[0],
                item = _step2$value[1];
              $items.push($(item)[0]);
            }
          } catch (err) {
            _iterator2.e(err);
          } finally {
            _iterator2.f();
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
          searchRegex = new RegExp($this.val(), "gi");
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
        var $this = $(this),
          // $init_show       = $(".dailybuddy-filter-gallery-container", $scope).children(".dailybuddy-filterable-gallery-item-wrap").length,
          // $total_items     = $gallery.data("total-gallery-items"),
          $nomore_text = $gallery.data("nomore-item-text"),
          filter_enable = $(".dailybuddy-filter-gallery-control", $scope).length,
          $items = [];
        var filter_name = $(".dailybuddy-filter-gallery-control li.active", $scope).data("filter");
        if (filterControls.length > 0) {
          filter_name = $(".fg-layout-3-filter-controls li.active", $scope).data("filter");
        }
        if (filter_name === undefined) {
          filter_name = "*";
        }
        var item_found = 0;
        var index_list = [];
        var _iterator3 = _createForOfIteratorHelper(fg_items.entries()),
          _step3;
        try {
          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
            var _step3$value = _slicedToArray(_step3.value, 2),
              index = _step3$value[0],
              item = _step3$value[1];
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
        } catch (err) {
          _iterator3.e(err);
        } finally {
          _iterator3.f();
        }
        if (index_list.length > 0) {
          fg_items = fg_items.filter(function (item, index) {
            return !index_list.includes(index);
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
          jQuery(".dailybuddy-filter-gallery-control li:nth-child(".concat(default_control_key, ")")).trigger("click");
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


/**
 * dailybuddy Admin Scripts with Auto-Save and Snackbar (mit i18n)
 */

jQuery(document).ready(function ($) {

    // WordPress i18n
    const { __, sprintf } = wp.i18n;

    // URL-Parameter lesen
    const params = new URLSearchParams(window.location.search);
    const focusCategory = params.get('focus_category');
    const focusModule = params.get('focus_module');

    if (focusCategory || focusModule) {
        // Kategorie-Tab aktivieren, falls vorhanden
        if (focusCategory) {
            const $navItem = $('.dailybuddy-nav-item[data-category="' + focusCategory + '"]');
            const $categoryDiv = $('.dailybuddy-category[data-category="' + focusCategory + '"]');

            if ($navItem.length && $categoryDiv.length) {
                $('.dailybuddy-nav-item').removeClass('active');
                $('.dailybuddy-category').removeClass('active');

                $navItem.addClass('active');
                $categoryDiv.addClass('active');
            }
        }

        // Konkretes Modul hervorheben & hinscrollen
        if (focusModule) {
            const $targetCard = $('.dailybuddy-module-card[data-module-id="' + focusModule + '"]');

            if ($targetCard.length) {
                const $container = $('.dailybuddy-content');

                // Scrollposition innerhalb des Containers
                const offsetTop = $targetCard.position().top + $container.scrollTop() - 20;

                $container.animate({ scrollTop: offsetTop }, 400);

                // Highlight-Klasse hinzufügen
                $targetCard.addClass('dailybuddy-module-highlight');

                // Nach ein paar Sekunden wieder entfernen
                setTimeout(function () {
                    $targetCard.removeClass('dailybuddy-module-highlight');
                }, 4000);
            }
        }
    }

    // Create snackbar container
    if ($('.dailybuddy-snackbar').length === 0) {
        $('body').append('<div class="dailybuddy-snackbar"></div>');
    }

    // Sidebar Navigation
    $('.dailybuddy-nav-item').on('click', function (e) {
        e.preventDefault();

        var targetCategory = $(this).data('category');

        // Remove active class from all nav items and categories
        $('.dailybuddy-nav-item').removeClass('active');
        $('.dailybuddy-category').removeClass('active');

        // Add active class to clicked nav item
        $(this).addClass('active');

        // Show target category
        $('.dailybuddy-category[data-category="' + targetCategory + '"]').addClass('active');

        // Scroll content to top
        $('.dailybuddy-content').scrollTop(0);
    });

    // Auto-Save on Toggle
    $('.dailybuddy-module-toggle').on('change', function () {
        var $checkbox = $(this);
        var $card = $checkbox.closest('.dailybuddy-module-card');
        var $reloadContainer = $checkbox.closest('[data-wpbuddy-reload-on-toggle="1"]');
        var moduleId = $checkbox.data('module-id');
        var moduleName = $checkbox.data('module-name');
        var isActive = $checkbox.is(':checked');

        // Show loading state
        $checkbox.prop('disabled', true);
        $card.css('opacity', '0.6');
        if ($reloadContainer.length) {
            $reloadContainer.addClass('is-loading');
        }

        // AJAX Save
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dailybuddy_toggle_module',
                nonce: wpToolboxData.nonce,
                module_id: moduleId,
                is_active: isActive
            },
            success: function (response) {
                $checkbox.prop('disabled', false);
                $card.css('opacity', '1');

                if (response.success) {
                    // Update visual state
                    if (isActive) {
                        $card.addClass('is-active');
                    } else {
                        $card.removeClass('is-active');
                    }

                    // Update counters
                    updateCounters();

                    // Show Snackbar
                    showSnackbar(moduleName, isActive);

                    // Container asks for a reload after toggle (e.g. WPBuddy
                    // CTA that reveals the pairing token). Reload with
                    // focus_category so we land on the same tab.
                    if ($reloadContainer.length) {
                        var category = $checkbox.data('module-id').split('/')[0];
                        var url = new URL(window.location.href);
                        url.searchParams.set('page', 'dailybuddy');
                        url.searchParams.set('focus_category', category);
                        setTimeout(function () { window.location.href = url.toString(); }, 500);
                        return; // keep loader visible until navigation
                    }

                } else {
                    // Revert checkbox on error
                    $checkbox.prop('checked', !isActive);
                    if ($reloadContainer.length) {
                        $reloadContainer.removeClass('is-loading');
                    }
                    showSnackbar(response.data.message || __('Error!', 'dailybuddy'), false, 'error');
                }
            },
            error: function () {
                $checkbox.prop('disabled', false);
                $card.css('opacity', '1');
                $checkbox.prop('checked', !isActive);
                if ($reloadContainer.length) {
                    $reloadContainer.removeClass('is-loading');
                }
                showSnackbar(__('Connection error. Please try again.', 'dailybuddy'), false, 'error');
            }
        });
    });

    // WPBuddy: confirm + show loader when regenerate form is submitted
    $(document).on('submit', '.dailybuddy-wpbuddy-token__regen', function (e) {
        var msg = $(this).data('confirm');
        if (msg && !window.confirm(msg)) {
            e.preventDefault();
            return false;
        }
        $(this).closest('[data-wpbuddy-reload-on-toggle="1"]').addClass('is-loading');
    });

    // Snackbar Function (mit gettext)
    function showSnackbar(moduleName, isActive, type) {
        const { __, sprintf } = wp.i18n;

        let message;
        let snackbarType = type || 'success';

        if (typeof isActive === 'boolean') {
            if (isActive) {
                var tmpl = dailybuddyAdmin.strings.moduleActivated || '%s activated!';
                message = tmpl.replace('%s', moduleName);
            } else {
                var tmpl = dailybuddyAdmin.strings.moduleDeactivated || '%s deactivated';
                message = tmpl.replace('%s', moduleName);
            }
        } else {
            // Direkt übergebene Nachricht (z.B. Fehler)
            message = moduleName;
        }

        var $snackbar = $('.dailybuddy-snackbar');
        $snackbar.text(message);
        $snackbar.removeClass('success error info warning').addClass(snackbarType);
        $snackbar.addClass('show');

        setTimeout(function () {
            $snackbar.removeClass('show');
        }, 3000);
    }

    // Update counters
    function updateCounters() {
        $('.dailybuddy-nav-item').each(function () {
            var category = $(this).data('category');
            var $categoryDiv = $('.dailybuddy-category[data-category="' + category + '"]');

            var total = $categoryDiv.find('.dailybuddy-module-card').length;
            var active = $categoryDiv.find('.dailybuddy-module-toggle:checked').length;

            var $counter = $(this).find('.dailybuddy-nav-counter');
            $counter.text(active + '/' + total);

            if (active > 0) {
                $counter.addClass('has-active');
            } else {
                $counter.removeClass('has-active');
            }
        });
    }

    // WPBuddy: copy pairing token to clipboard
    $(document).on('click', '.dailybuddy-wpbuddy-token__copy', function () {
        const $btn = $(this);
        const targetId = $btn.data('target');
        const input = document.getElementById(targetId);
        if (!input) return;

        const done = function () {
            const original = $btn.html();
            $btn.html('<span class="fa-solid fa-check"></span> ' + __('Copied', 'dailybuddy'));
            setTimeout(function () { $btn.html(original); }, 1500);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(done);
        } else {
            input.select();
            document.execCommand('copy');
            done();
        }
    });

});

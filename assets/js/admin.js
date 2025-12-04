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
        var moduleId = $checkbox.data('module-id');
        var moduleName = $checkbox.data('module-name');
        var isActive = $checkbox.is(':checked');

        // Show loading state
        $checkbox.prop('disabled', true);
        $card.css('opacity', '0.6');

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

                } else {
                    // Revert checkbox on error
                    $checkbox.prop('checked', !isActive);
                    showSnackbar(response.data.message || __('Error!', 'dailybuddy'), false, 'error');
                }
            },
            error: function () {
                $checkbox.prop('disabled', false);
                $card.css('opacity', '1');
                $checkbox.prop('checked', !isActive);
                showSnackbar(__('Connection error. Please try again.', 'dailybuddy'), false, 'error');
            }
        });
    });

    // Snackbar Function (mit gettext)
    function showSnackbar(moduleName, isActive, type) {
        const { __, sprintf } = wp.i18n;

        let message;
        let snackbarType = type || 'success';

        if (typeof isActive === 'boolean') {
            if (isActive) {
                message = sprintf(
                    __('%s activated!', 'dailybuddy'),
                    moduleName
                );
            } else {
                message = sprintf(
                    __('%s deactivated', 'dailybuddy'),
                    moduleName
                );
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

});

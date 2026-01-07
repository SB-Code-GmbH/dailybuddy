jQuery(document).ready(function ($) {
    // Tab Switching
    $('.dailybuddy-uc-tab').on('click', function () {
        var tab = $(this).data('tab');

        // Update tabs
        $('.dailybuddy-uc-tab').removeClass('active');
        $(this).addClass('active');

        // Update content
        $('.dailybuddy-uc-tab-content').removeClass('active');
        $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');

        // Update URL without page reload
        if (history.pushState) {
            var newUrl = dailybuddyDev.developmentUrl + '&tab=' + tab;
            history.pushState(null, '', newUrl);
        }
    });

    // Load empty translations count on page load
    if ($('.empty-translations-cell').length > 0) {
        loadEmptyTranslations();
    }

    // Load empty translations count
    function loadEmptyTranslations() {
        $.ajax({
            url: dailybuddyDev.ajaxurl,
            type: 'POST',
            data: {
                action: 'dailybuddy_get_empty_translations',
                nonce: dailybuddyDev.nonce
            },
            success: function (response) {
                if (response.success && response.data.empty_counts) {
                    $.each(response.data.empty_counts, function (locale, count) {
                        var $cell = $('.empty-translations-cell[data-locale="' + locale + '"]');
                        $cell.find('.spinner').hide();

                        var $countSpan = $cell.find('.empty-count');
                        if (count > 0) {
                            $countSpan.html('<span style="color: #d63638; font-weight: 600;">' + count + '</span> ' +
                                dailybuddyDev.i18n.untranslated);
                        } else {
                            $countSpan.html('<span style="color: #46b450;">✓ ' + dailybuddyDev.i18n.complete + '</span>');
                        }
                        $countSpan.show();
                    });
                }
            },
            error: function () {
                $('.empty-translations-cell .spinner').hide();
                $('.empty-translations-cell .empty-count').html('—').show();
            }
        });
    }

    // Create New Language
    $('#dailybuddy-create-language').on('click', function () {
        var $btn = $(this);
        var $input = $('#new-locale-input');
        var $status = $('#create-language-status');
        var locale = $input.val().trim();

        // Validate input
        if (!locale) {
            alert(dailybuddyDev.i18n.pleaseEnterLocale);
            $input.focus();
            return;
        }

        // Validate format (e.g., de_DE)
        var localePattern = /^[a-z]{2}_[A-Z]{2}$/;
        if (!localePattern.test(locale)) {
            alert(dailybuddyDev.i18n.invalidLocaleFormat);
            $input.focus();
            return;
        }

        $btn.prop('disabled', true);
        $status.show().find('.status-text').text(dailybuddyDev.i18n.creatingLanguageFiles);

        $.ajax({
            url: dailybuddyDev.ajaxurl,
            type: 'POST',
            data: {
                action: 'dailybuddy_create_language',
                nonce: dailybuddyDev.nonce,
                locale: locale
            },
            success: function (response) {
                if (response.success) {
                    alert(response.data.message);
                    // Reload page to show new language
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                    $btn.prop('disabled', false);
                    $status.hide();
                }
            },
            error: function () {
                alert(dailybuddyDev.i18n.errorOccurred);
                $btn.prop('disabled', false);
                $status.hide();
            }
        });
    });

    // Allow Enter key in locale input
    $('#new-locale-input').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#dailybuddy-create-language').click();
        }
    });

    // Translation Scanner
    var scanResults = null;

    // Scan Button
    $('#dailybuddy-scan-translations').on('click', function () {
        var $btn = $(this);
        var $status = $('#dailybuddy-scanner-status');
        var $results = $('#dailybuddy-scanner-results');

        $btn.prop('disabled', true);
        $status.show();
        $results.hide();

        $.ajax({
            url: dailybuddyDev.ajaxurl,
            type: 'POST',
            data: {
                action: 'dailybuddy_scan_translations',
                nonce: dailybuddyDev.nonce
            },
            success: function (response) {
                if (response.success) {
                    scanResults = response.data;
                    displayScanResults(response.data);
                    $results.fadeIn();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function () {
                alert(dailybuddyDev.i18n.errorOccurredScanning);
            },
            complete: function () {
                $btn.prop('disabled', false);
                $status.hide();
            }
        });
    });

    // Display Scan Results
    function displayScanResults(data) {
        var totalMissing = data.missing_strings.length;
        var locales = Object.keys(data.available_locales);

        // Summary
        var summaryText = dailybuddyDev.i18n.found + ' <strong>' + data.total_strings + '</strong> ' +
            dailybuddyDev.i18n.translatableStringsIn + ' <strong>' + data.files_scanned + '</strong> ' +
            dailybuddyDev.i18n.files + '<br>';

        if (totalMissing > 0) {
            summaryText += '<strong>' + totalMissing + '</strong> ' +
                dailybuddyDev.i18n.stringsAreMissing;
        } else {
            summaryText += '<span style="color: #46b450;">✓ ' + dailybuddyDev.i18n.allStringsTranslated + '</span>';
        }

        $('#summary-text').html(summaryText);

        // Missing Strings Table
        if (totalMissing > 0) {
            var tableHtml = '<table class="widefat striped">' +
                '<thead><tr>' +
                '<th style="width: 40px;"><input type="checkbox" id="select-all-strings"></th>' +
                '<th>' + dailybuddyDev.i18n.string + '</th>' +
                '<th>' + dailybuddyDev.i18n.context + '</th>' +
                '<th>' + dailybuddyDev.i18n.missingIn + '</th>' +
                '</tr></thead><tbody>';

            $.each(data.missing_strings, function (index, item) {
                var missingLocales = item.missing_in.join(', ');
                tableHtml += '<tr>' +
                    '<td><input type="checkbox" class="string-checkbox" data-string-id="' + index + '" checked></td>' +
                    '<td><code>' + escapeHtml(item.string) + '</code></td>' +
                    '<td style="font-size: 11px; color: #646970;">' + escapeHtml(item.context) + '</td>' +
                    '<td><span class="locale-badges">' + missingLocales + '</span></td>' +
                    '</tr>';
            });

            tableHtml += '</tbody></table>';
            $('#missing-strings-content').html(tableHtml);

            // Select All Checkbox
            $('#select-all-strings').on('change', function () {
                $('.string-checkbox').prop('checked', $(this).is(':checked'));
            });

            // Locale Buttons
            var buttonsHtml = '';
            $.each(locales, function (index, locale) {
                buttonsHtml += '<button type="button" class="button button-secondary add-to-locale" data-locale="' + locale + '" style="margin-right: 10px; margin-bottom: 10px;">' +
                    '<span class="dashicons dashicons-plus" style="margin-top: 3px;"></span> ' +
                    dailybuddyDev.i18n.addTo + ' ' + locale + '.po' +
                    '</button>';
            });
            $('#locale-buttons').html(buttonsHtml);

            $('#dailybuddy-scanner-actions').show();
        } else {
            $('#missing-strings-content').html('<p style="color: #46b450;">✓ ' + dailybuddyDev.i18n.noMissingTranslations + '</p>');
            $('#dailybuddy-scanner-actions').hide();
        }
    }

    // Add to specific locale
    $(document).on('click', '.add-to-locale', function () {
        var locale = $(this).data('locale');
        var selectedStrings = getSelectedStrings();

        if (selectedStrings.length === 0) {
            alert(dailybuddyDev.i18n.selectAtLeastOne);
            return;
        }

        addStringsToLocale([locale], selectedStrings, $(this));
    });

    // Add to all locales
    $('#dailybuddy-add-to-all').on('click', function () {
        var selectedStrings = getSelectedStrings();

        if (selectedStrings.length === 0) {
            alert(dailybuddyDev.i18n.selectAtLeastOne);
            return;
        }

        var locales = Object.keys(scanResults.available_locales);
        addStringsToLocale(locales, selectedStrings, $(this));
    });

    // Get Selected Strings
    function getSelectedStrings() {
        var selected = [];
        $('.string-checkbox:checked').each(function () {
            var index = $(this).data('string-id');
            selected.push(scanResults.missing_strings[index]);
        });
        return selected;
    }

    // Add Strings to Locale(s)
    function addStringsToLocale(locales, strings, $btn) {
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner" style="float:none;visibility:visible;margin:0;"></span> ' + dailybuddyDev.i18n.adding);

        // Debug output
        console.log('Locales:', locales);
        console.log('Strings:', strings);

        $.ajax({
            url: dailybuddyDev.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'dailybuddy_add_translations',
                nonce: dailybuddyDev.nonce,
                locales: JSON.stringify(locales),
                strings: JSON.stringify(strings)
            },
            success: function (response) {
                console.log('Response:', response);
                if (response.success) {
                    alert(response.data.message);
                    // Re-scan to update results
                    $('#dailybuddy-scan-translations').click();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                alert(dailybuddyDev.i18n.errorOccurred);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    }

    // Clear Results
    $('#dailybuddy-clear-results').on('click', function () {
        $('#dailybuddy-scanner-results').fadeOut();
        scanResults = null;
    });

    // Helper function
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }
});
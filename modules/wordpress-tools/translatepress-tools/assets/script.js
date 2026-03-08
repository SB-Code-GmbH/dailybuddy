/**
 * DailyBuddy - TranslatePress Auto Language Detection
 *
 * Detects browser language and shows popup/bar or redirects.
 */
(function () {
    'use strict';

    var config = window.dailybuddyTpTools;
    if (!config || !config.languages || !config.languages.length) {
        return;
    }

    // Fix basePath: TranslatePress filters home_url() on translated pages,
    // appending the current language slug (e.g. "/wp/de/" instead of "/wp/").
    // Strip any trailing language slug so all URL logic uses the real base path.
    (function () {
        var bp = config.basePath || '/';
        for (var i = 0; i < config.languages.length; i++) {
            var suffix = config.languages[i].slug + '/';
            if (bp.length > suffix.length && bp.slice(-suffix.length) === suffix) {
                config.basePath = bp.slice(0, -suffix.length);
                return;
            }
        }
    })();

    var STORAGE_KEY = 'dailybuddy_tp_lang_detected';

    /**
     * Get stored value from localStorage (persists across tabs and sessions).
     */
    function getStorage(key) {
        try {
            return localStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    /**
     * Set value in localStorage.
     */
    function setStorage(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            // localStorage not available, silently fail.
        }
    }

    /**
     * Determine the current language slug from the actual browser URL.
     * This is more reliable than the server-side computed value because
     * TranslatePress may translate the config output itself.
     *
     * @return string The current language slug.
     */
    function getCurrentSlugFromUrl() {
        var path     = window.location.pathname;
        var basePath = config.basePath || '/';

        // Strip the base path prefix (e.g. "/wp/de/contact/" → "de/contact/").
        var relativePath = path;
        if (basePath !== '/' && path.indexOf(basePath) === 0) {
            relativePath = path.substring(basePath.length);
        } else if (path.charAt(0) === '/') {
            relativePath = path.substring(1);
        }

        var firstSegment = relativePath.split('/')[0] || '';

        // Check if the first URL segment matches a known language slug.
        for (var i = 0; i < config.languages.length; i++) {
            if (config.languages[i].slug === firstSegment) {
                return firstSegment;
            }
        }

        // No language slug in URL → user is on the default language.
        return config.defaultSlug;
    }

    /**
     * Get language display name from locale code.
     */
    function getLanguageName(code) {
        // Normalize TranslatePress code (e.g. "en_US" → "en-US") for Intl API.
        var normalized = code.replace('_', '-');
        var langPrefix = code.substring(0, 2);

        // Find the current page language code from our language list.
        var currentLocale = 'en';
        for (var i = 0; i < config.languages.length; i++) {
            if (config.languages[i].slug === config.currentSlug) {
                currentLocale = config.languages[i].code.replace('_', '-');
                break;
            }
        }
        // Also try document lang attribute as fallback.
        if (currentLocale === 'en' && document.documentElement.lang) {
            currentLocale = document.documentElement.lang;
        }

        try {
            // Show language name in the current page language so the user understands it.
            // e.g. on a German page, "en" → "Englisch"; on an English page, "de" → "German".
            var display = new Intl.DisplayNames([currentLocale], { type: 'language' });
            var name = display.of(langPrefix);
            return name.charAt(0).toUpperCase() + name.slice(1);
        } catch (e) {
            try {
                // Fallback: show name in the target language itself.
                var fallback = new Intl.DisplayNames([normalized], { type: 'language' });
                var fbName = fallback.of(langPrefix);
                return fbName.charAt(0).toUpperCase() + fbName.slice(1);
            } catch (e2) {
                return langPrefix.toUpperCase();
            }
        }
    }

    /**
     * Build the URL for a given language slug, keeping the current page path.
     * E.g. /en/about-us/ → /de/about-us/
     */
    function buildLanguageUrl(targetSlug) {
        var path = window.location.pathname;
        var basePath = config.basePath || '/'; // e.g. "/wp/" or "/"

        var allSlugs = [];
        for (var i = 0; i < config.languages.length; i++) {
            if (config.languages[i].slug) {
                allSlugs.push(config.languages[i].slug);
            }
        }

        // Strip the base path prefix to get the relative path.
        // e.g. "/wp/en/about-us/" → "en/about-us/"
        var relativePath = path;
        if (basePath !== '/' && path.indexOf(basePath) === 0) {
            relativePath = path.substring(basePath.length);
        } else if (path.indexOf('/') === 0) {
            relativePath = path.substring(1);
        }

        // Remove current language slug from relative path if present.
        // e.g. "en/about-us/" → "about-us/"
        var slugPattern = new RegExp('^(' + allSlugs.join('|') + ')(/|$)');
        var strippedPath = relativePath.replace(slugPattern, '');

        // For default language, no slug prefix needed.
        if (targetSlug === config.defaultSlug) {
            return basePath + strippedPath + window.location.search;
        }

        // Prepend the target slug after the base path.
        // e.g. "/wp/" + "en/" + "about-us/"
        return basePath + targetSlug + '/' + strippedPath + window.location.search;
    }

    /**
     * Match browser languages against available TranslatePress languages.
     * Returns the best matching language object or null.
     */
    function detectLanguage() {
        var browserLangs = navigator.languages || [navigator.language || navigator.userLanguage];
        if (!browserLangs || !browserLangs.length) {
            return null;
        }

        for (var i = 0; i < browserLangs.length; i++) {
            var browserLang = browserLangs[i].toLowerCase();
            var prefix = browserLang.split('-')[0];

            // Exact match first (e.g. "de-de" matches "de_DE").
            for (var j = 0; j < config.languages.length; j++) {
                var lang = config.languages[j];
                var langNorm = lang.code.toLowerCase().replace('_', '-');
                if (browserLang === langNorm) {
                    return lang;
                }
            }

            // Prefix match (e.g. "de" matches "de_DE").
            for (var k = 0; k < config.languages.length; k++) {
                if (config.languages[k].prefix === prefix) {
                    return config.languages[k];
                }
            }
        }

        return null;
    }

    /**
     * Check if the detected language differs from current page language.
     */
    function shouldPrompt(detectedLang) {
        if (!detectedLang) {
            return false;
        }
        // If detected language slug matches current slug, no need to prompt.
        if (detectedLang.slug === config.currentSlug) {
            return false;
        }
        // Also compare by language code prefix: if both resolve to the same
        // language (e.g. browser "de" and current page "de_DE"), don't prompt.
        var detectedPrefix = detectedLang.code.substring(0, 2).toLowerCase();
        var currentPrefix = '';
        for (var i = 0; i < config.languages.length; i++) {
            if (config.languages[i].slug === config.currentSlug) {
                currentPrefix = config.languages[i].code.substring(0, 2).toLowerCase();
                break;
            }
        }
        if (detectedPrefix && currentPrefix && detectedPrefix === currentPrefix) {
            return false;
        }
        return true;
    }

    /**
     * Show popup notification.
     */
    function showPopup(detectedLang) {
        var langName = getLanguageName(detectedLang.code);
        var text = config.popupText.replace('{language}', langName);

        var overlay = document.createElement('div');
        overlay.className = 'dailybuddy-tp-overlay';

        var popup = document.createElement('div');
        popup.className = 'dailybuddy-tp-popup';
        popup.innerHTML =
            '<div class="dailybuddy-tp-popup-icon"><span class="dashicons ' + escapeAttr(config.popupIcon) + '"></span></div>' +
            '<div class="dailybuddy-tp-popup-text">' + escapeHtml(text) + '</div>' +
            '<div class="dailybuddy-tp-popup-actions">' +
            '<a href="' + escapeAttr(buildLanguageUrl(detectedLang.slug)) + '" class="dailybuddy-tp-btn dailybuddy-tp-btn-primary">' + escapeHtml(config.buttonText) + '</a>' +
            '<button type="button" class="dailybuddy-tp-btn dailybuddy-tp-btn-secondary dailybuddy-tp-dismiss">' + escapeHtml(config.dismissText) + '</button>' +
            '</div>';

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Trigger animation.
        requestAnimationFrame(function () {
            overlay.classList.add('visible');
        });

        // Dismiss handler.
        overlay.querySelector('.dailybuddy-tp-dismiss').addEventListener('click', function () {
            setStorage(STORAGE_KEY, 'dismissed');
            overlay.classList.remove('visible');
            setTimeout(function () { overlay.remove(); }, 300);
        });

        // Clicking the switch button also sets storage.
        overlay.querySelector('.dailybuddy-tp-btn-primary').addEventListener('click', function () {
            setStorage(STORAGE_KEY, detectedLang.slug);
        });

        // Close on overlay click.
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                setStorage(STORAGE_KEY, 'dismissed');
                overlay.classList.remove('visible');
                setTimeout(function () { overlay.remove(); }, 300);
            }
        });
    }

    /**
     * Show hello bar notification.
     */
    function showBar(detectedLang) {
        var langName = getLanguageName(detectedLang.code);
        var text = config.barText.replace('{language}', langName);

        var bar = document.createElement('div');
        bar.className = 'dailybuddy-tp-bar dailybuddy-tp-bar-' + config.barPosition;
        bar.innerHTML =
            '<div class="dailybuddy-tp-bar-inner">' +
            '<span class="dailybuddy-tp-bar-text">' + escapeHtml(text) + '</span>' +
            '<a href="' + escapeAttr(buildLanguageUrl(detectedLang.slug)) + '" class="dailybuddy-tp-btn dailybuddy-tp-btn-primary dailybuddy-tp-btn-small">' + escapeHtml(config.buttonText) + '</a>' +
            '<button type="button" class="dailybuddy-tp-bar-close dailybuddy-tp-dismiss" aria-label="Close">&times;</button>' +
            '</div>';

        document.body.appendChild(bar);

        // Trigger animation.
        requestAnimationFrame(function () {
            bar.classList.add('visible');
        });

        // Dismiss handler.
        bar.querySelector('.dailybuddy-tp-dismiss').addEventListener('click', function () {
            setStorage(STORAGE_KEY, 'dismissed');
            bar.classList.remove('visible');
            setTimeout(function () { bar.remove(); }, 300);
        });

        // Switch button sets storage.
        bar.querySelector('.dailybuddy-tp-btn-primary').addEventListener('click', function () {
            setStorage(STORAGE_KEY, detectedLang.slug);
        });
    }

    /**
     * Direct redirect.
     */
    function doRedirect(detectedLang) {
        var targetUrl = buildLanguageUrl(detectedLang.slug);
        // Guard: only redirect if the URL actually changes (prevents loop).
        var current = (window.location.pathname + window.location.search).replace(/\/+$/, '') || '/';
        var target  = targetUrl.replace(/\/+$/, '') || '/';
        if (current !== target) {
            setStorage(STORAGE_KEY, detectedLang.slug);
            window.location.href = targetUrl;
        }
    }

    /**
     * Escape HTML for safe insertion.
     */
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * Escape for HTML attributes.
     */
    function escapeAttr(str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    /**
     * Watch for manual language changes via the TranslatePress switcher.
     * If the user explicitly picks a language themselves, store 'dismissed'
     * so auto-detection no longer overrides their choice.
     */
    function watchManualLanguageChange() {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('.trp-language-switcher a[href]');
            if (link) {
                setStorage(STORAGE_KEY, 'dismissed');
            }
        });
    }

    /**
     * Initialize detection.
     */
    function init() {
        // Always listen for manual language switches, regardless of stored state.
        watchManualLanguageChange();

        // Override the server-side currentSlug with a client-side computation
        // from the actual URL. TranslatePress may translate the wp_localize_script
        // output, making the PHP value unreliable on translated pages.
        config.currentSlug = getCurrentSlugFromUrl();

        var stored = getStorage(STORAGE_KEY);

        if (stored) {
            // User previously dismissed — do nothing.
            if (stored === 'dismissed') {
                return;
            }

            // User previously chose a language — redirect if not already on it.
            if (stored !== config.currentSlug) {
                // Verify the stored slug is still a valid language.
                for (var i = 0; i < config.languages.length; i++) {
                    if (config.languages[i].slug === stored) {
                        var targetUrl = buildLanguageUrl(stored);
                        // Guard: only redirect if the URL actually changes (prevents loop).
                        var current = (window.location.pathname + window.location.search).replace(/\/+$/, '') || '/';
                        var target  = targetUrl.replace(/\/+$/, '') || '/';
                        if (current !== target) {
                            window.location.href = targetUrl;
                        }
                        return;
                    }
                }
            }

            // Already on the chosen language, nothing to do.
            return;
        }

        // No stored preference — detect and prompt.
        var detectedLang = detectLanguage();
        if (!shouldPrompt(detectedLang)) {
            return;
        }

        switch (config.actionType) {
            case 'popup':
                showPopup(detectedLang);
                break;
            case 'bar':
                showBar(detectedLang);
                break;
            case 'redirect':
                doRedirect(detectedLang);
                break;
        }
    }

    // Run after DOM is ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

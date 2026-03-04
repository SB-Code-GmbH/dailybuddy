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

    var STORAGE_KEY = 'dailybuddy_tp_lang_detected';

    /**
     * Get stored value from sessionStorage.
     */
    function getStorage(key) {
        try {
            return sessionStorage.getItem(key);
        } catch (e) {
            return null;
        }
    }

    /**
     * Set value in sessionStorage.
     */
    function setStorage(key, value) {
        try {
            sessionStorage.setItem(key, value);
        } catch (e) {
            // sessionStorage not available, silently fail.
        }
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
        return detectedLang.slug !== config.currentSlug;
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
            '<div class="dailybuddy-tp-popup-icon">&#127760;</div>' +
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

        // Clicking the switch button also sets cookie.
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

        // Switch button sets cookie.
        bar.querySelector('.dailybuddy-tp-btn-primary').addEventListener('click', function () {
            setStorage(STORAGE_KEY, detectedLang.slug);
        });
    }

    /**
     * Direct redirect.
     */
    function doRedirect(detectedLang) {
        setStorage(STORAGE_KEY, detectedLang.slug);
        window.location.href = buildLanguageUrl(detectedLang.slug);
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
     * Initialize detection.
     */
    function init() {
        // Check if already decided.
        if (getStorage(STORAGE_KEY)) {
            return;
        }

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

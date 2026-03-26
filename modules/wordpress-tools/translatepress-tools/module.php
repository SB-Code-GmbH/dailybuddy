<?php

/**
 * Module: TranslatePress Tools
 *
 * Automatic User Language Detection for TranslatePress.
 * Detects browser language and redirects or notifies visitors.
 */

if (! defined('ABSPATH')) {
    exit;
}

class Dailybuddy_TranslatePress_Tools
{
    /**
     * Module settings.
     *
     * @var array
     */
    private $settings;

    /**
     * TranslatePress settings.
     *
     * @var array
     */
    private $trp_settings;

    /**
     * Default settings.
     *
     * @var array
     */
    private static $defaults = array(
        'enabled'              => true,
        'action_type'          => 'popup',       // popup, bar
        'cookie_days'          => 30,
        'bar_position'         => 'top',         // top, bottom
        'popup_text'           => '',
        'bar_text'             => '',
        'button_text'          => '',
        'dismiss_text'         => '',
        'exclude_pages'        => '',
        // Appearance.
        'popup_icon'           => 'dashicons-translation',
        'popup_icon_color'     => '',
        'popup_bg_color'       => '#ffffff',
        'popup_text_color'     => '#333333',
        'popup_border_radius'  => 12,
        'popup_overlay_color'  => '#000000',
        'popup_overlay_opacity' => 50,
        'bar_bg_color'         => '#2271b1',
        'bar_text_color'       => '#ffffff',
        'btn_bg_color'         => '#2271b1',
        'btn_text_color'       => '#ffffff',
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->settings     = get_option('dailybuddy_tp_tools_settings', self::$defaults);
        $this->settings     = wp_parse_args($this->settings, self::$defaults);
        $this->trp_settings = get_option('trp_settings', array());

        // Migrate: redirect action type was removed due to SEO issues.
        if ($this->settings['action_type'] === 'redirect') {
            $this->settings['action_type'] = 'popup';
            update_option('dailybuddy_tp_tools_settings', $this->settings);
        }

        if (! empty($this->settings['enabled']) && ! is_admin() && ! $this->is_rest_request()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
            add_action('wp_head', array($this, 'output_custom_styles'));
        }

    }

    /**
     * Check if this is a REST request.
     *
     * @return bool
     */
    private function is_rest_request()
    {
        return defined('REST_REQUEST') && REST_REQUEST;
    }

    /**
     * Get available TranslatePress languages.
     *
     * @return array Associative array of language_code => url_slug.
     */
    public function get_available_languages()
    {
        if (empty($this->trp_settings['translation-languages'])) {
            return array();
        }

        $languages = $this->trp_settings['translation-languages'];
        $url_slugs = isset($this->trp_settings['url-slugs']) ? $this->trp_settings['url-slugs'] : array();
        $default   = isset($this->trp_settings['default-language']) ? $this->trp_settings['default-language'] : '';

        $result = array();
        foreach ($languages as $lang) {
            $slug = isset($url_slugs[$lang]) ? $url_slugs[$lang] : '';
            $result[] = array(
                'code'       => $lang,
                'slug'       => $slug,
                'is_default' => ($lang === $default),
            );
        }

        return $result;
    }

    /**
     * Get the current TranslatePress language from URL.
     *
     * @return string Current language slug or empty string.
     */
    private function get_current_language_slug()
    {
        $url_slugs = isset($this->trp_settings['url-slugs']) ? $this->trp_settings['url-slugs'] : array();
        $default   = isset($this->trp_settings['default-language']) ? $this->trp_settings['default-language'] : '';

        // Strip the base path to get the relative URI.
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        $home_path   = wp_parse_url(home_url(), PHP_URL_PATH);
        if ($home_path && strpos($request_uri, $home_path) === 0) {
            $request_uri = substr($request_uri, strlen($home_path));
        }

        $path_parts = explode('/', trim($request_uri, '/'));
        $first_part = ! empty($path_parts[0]) ? $path_parts[0] : '';

        foreach ($url_slugs as $lang_code => $slug) {
            if ($slug === $first_part && $lang_code !== $default) {
                return $slug;
            }
        }

        // Return default language slug.
        return isset($url_slugs[$default]) ? $url_slugs[$default] : '';
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets()
    {
        // Don't load if no TranslatePress languages configured.
        $languages = $this->get_available_languages();
        if (empty($languages)) {
            return;
        }

        // Check excluded pages.
        if (! empty($this->settings['exclude_pages'])) {
            $excluded = array_map('trim', explode("\n", $this->settings['exclude_pages']));
            $current  = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
            foreach ($excluded as $pattern) {
                if (! empty($pattern) && strpos($current, $pattern) !== false) {
                    return;
                }
            }
        }

        $module_url = plugin_dir_url(__FILE__);

        // Dashicons for the popup icon on the frontend.
        wp_enqueue_style('dashicons');

        wp_enqueue_style(
            'dailybuddy-tp-tools',
            $module_url . 'assets/style.css',
            array('dashicons'),
            '1.0.0'
        );

        wp_enqueue_script(
            'dailybuddy-tp-tools',
            $module_url . 'assets/script.js',
            array(),
            '1.0.0',
            true
        );

        // Prepare language map: browser locale prefix => url slug.
        $lang_map = array();
        foreach ($languages as $lang) {
            // TranslatePress uses codes like "de_DE", "en_US", "fr_FR".
            // Browser sends "de", "de-DE", "en", "en-US".
            $code   = $lang['code'];
            $slug   = $lang['slug'];
            $prefix = strtolower(substr($code, 0, 2)); // "de", "en", "fr"

            $lang_map[] = array(
                'code'       => $code,
                'prefix'     => $prefix,
                'slug'       => $slug,
                'is_default' => $lang['is_default'],
            );
        }

        $default_texts = $this->get_default_texts();

        // Determine the default language slug.
        $default_lang = isset($this->trp_settings['default-language']) ? $this->trp_settings['default-language'] : '';
        $url_slugs    = isset($this->trp_settings['url-slugs']) ? $this->trp_settings['url-slugs'] : array();
        $default_slug = isset($url_slugs[$default_lang]) ? $url_slugs[$default_lang] : '';

        // Get the base path from home_url (e.g. "/wp" or "").
        $home_path = wp_parse_url(home_url(), PHP_URL_PATH);
        $home_path = $home_path ? trailingslashit($home_path) : '/';

        wp_localize_script('dailybuddy-tp-tools', 'dailybuddyTpTools', array(
            'languages'    => $lang_map,
            'currentSlug'  => $this->get_current_language_slug(),
            'defaultSlug'  => $default_slug,
            'basePath'     => $home_path,
            'actionType'   => $this->settings['action_type'],
            'popupIcon'    => ! empty($this->settings['popup_icon']) ? $this->settings['popup_icon'] : 'dashicons-translation',
            'cookieDays'   => absint($this->settings['cookie_days']),
            'barPosition'  => $this->settings['bar_position'],
            'popupText'    => ! empty($this->settings['popup_text']) ? $this->settings['popup_text'] : $default_texts['popup_text'],
            'barText'      => ! empty($this->settings['bar_text']) ? $this->settings['bar_text'] : $default_texts['bar_text'],
            'buttonText'   => ! empty($this->settings['button_text']) ? $this->settings['button_text'] : $default_texts['button_text'],
            'dismissText'  => ! empty($this->settings['dismiss_text']) ? $this->settings['dismiss_text'] : $default_texts['dismiss_text'],
        ));
    }

    /**
     * Output custom CSS variables based on settings.
     */
    public function output_custom_styles()
    {
        $s = $this->settings;
        $overlay_opacity = max(0, min(100, absint($s['popup_overlay_opacity']))) / 100;

        // Convert hex overlay color to rgba.
        $hex = ltrim($s['popup_overlay_color'], '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $icon_color = ! empty($s['popup_icon_color']) ? esc_attr($s['popup_icon_color']) : 'inherit';

        printf(
            '<style id="dailybuddy-tp-tools-vars">
:root {
    --db-tp-popup-bg: %s;
    --db-tp-popup-text: %s;
    --db-tp-popup-radius: %dpx;
    --db-tp-popup-icon-color: %s;
    --db-tp-overlay-bg: rgba(%d, %d, %d, %s);
    --db-tp-bar-bg: %s;
    --db-tp-bar-text: %s;
    --db-tp-btn-bg: %s;
    --db-tp-btn-text: %s;
}
</style>' . "\n",
            esc_attr($s['popup_bg_color']),
            esc_attr($s['popup_text_color']),
            absint($s['popup_border_radius']),
            esc_attr($icon_color),
            absint($r),
            absint($g),
            absint($b),
            esc_attr($overlay_opacity),
            esc_attr($s['bar_bg_color']),
            esc_attr($s['bar_text_color']),
            esc_attr($s['btn_bg_color']),
            esc_attr($s['btn_text_color'])
        );
    }




    /**
     * Get default translatable texts.
     *
     * @return array
     */
    private function get_default_texts()
    {
        return array(
            'popup_text'   => __('We detected that your browser language is {language}. Would you like to switch?', 'dailybuddy'),
            'bar_text'     => __('This page is also available in {language}.', 'dailybuddy'),
            'button_text'  => __('Switch Language', 'dailybuddy'),
            'dismiss_text' => __('No, thanks', 'dailybuddy'),
        );
    }

    /**
     * Get settings.
     *
     * @return array
     */
    public function get_settings()
    {
        return $this->settings;
    }

    /**
     * Get defaults.
     *
     * @return array
     */
    public static function get_defaults()
    {
        return self::$defaults;
    }
}


/**
 * Render settings page.
 */
function dailybuddy_render_translatepress_tools_settings()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'dailybuddy'));
    }

    // Enqueue shared module settings styles (tabs, etc.).
    if (defined('DAILYBUDDY_URL') && defined('DAILYBUDDY_VERSION')) {
        wp_enqueue_style('dailybuddy-uc', DAILYBUDDY_URL . 'assets/css/modul-settings.css', array(), DAILYBUDDY_VERSION);
    }

    $instance       = new Dailybuddy_TranslatePress_Tools();
    $settings       = $instance->get_settings();
    $defaults       = Dailybuddy_TranslatePress_Tools::get_defaults();
    $trp_settings   = get_option('trp_settings', array());
    $trp_languages  = isset($trp_settings['translation-languages']) ? $trp_settings['translation-languages'] : array();
    $default_texts  = array(
        'popup_text'   => __('We detected that your browser language is {language}. Would you like to switch?', 'dailybuddy'),
        'bar_text'     => __('This page is also available in {language}.', 'dailybuddy'),
        'button_text'  => __('Switch Language', 'dailybuddy'),
        'dismiss_text' => __('No, thanks', 'dailybuddy'),
    );

    // Handle form submission.
    if (isset($_POST['dailybuddy_tp_tools_submit'])) {
        check_admin_referer('dailybuddy_tp_tools_settings');

        $new_settings = array(
            'enabled'       => isset($_POST['enabled']) ? true : false,
            'action_type'   => isset($_POST['action_type']) ? sanitize_text_field(wp_unslash($_POST['action_type'])) : 'popup',
            'cookie_days'   => isset($_POST['cookie_days']) ? absint($_POST['cookie_days']) : 30,
            'bar_position'  => isset($_POST['bar_position']) ? sanitize_text_field(wp_unslash($_POST['bar_position'])) : 'top',
            'popup_text'    => isset($_POST['popup_text']) ? sanitize_text_field(wp_unslash($_POST['popup_text'])) : '',
            'bar_text'      => isset($_POST['bar_text']) ? sanitize_text_field(wp_unslash($_POST['bar_text'])) : '',
            'button_text'   => isset($_POST['button_text']) ? sanitize_text_field(wp_unslash($_POST['button_text'])) : '',
            'dismiss_text'  => isset($_POST['dismiss_text']) ? sanitize_text_field(wp_unslash($_POST['dismiss_text'])) : '',
            'exclude_pages'        => isset($_POST['exclude_pages']) ? sanitize_textarea_field(wp_unslash($_POST['exclude_pages'])) : '',
            // Appearance.
            'popup_icon'           => isset($_POST['popup_icon']) ? sanitize_text_field(wp_unslash($_POST['popup_icon'])) : 'dashicons-translation',
            'popup_icon_color'     => isset($_POST['popup_icon_color']) && ! empty($_POST['popup_icon_color']) ? sanitize_hex_color(wp_unslash($_POST['popup_icon_color'])) : '',
            'popup_bg_color'       => isset($_POST['popup_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['popup_bg_color'])) : '#ffffff',
            'popup_text_color'     => isset($_POST['popup_text_color']) ? sanitize_hex_color(wp_unslash($_POST['popup_text_color'])) : '#333333',
            'popup_border_radius'  => isset($_POST['popup_border_radius']) ? absint($_POST['popup_border_radius']) : 12,
            'popup_overlay_color'  => isset($_POST['popup_overlay_color']) ? sanitize_hex_color(wp_unslash($_POST['popup_overlay_color'])) : '#000000',
            'popup_overlay_opacity' => isset($_POST['popup_overlay_opacity']) ? absint($_POST['popup_overlay_opacity']) : 50,
            'bar_bg_color'         => isset($_POST['bar_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['bar_bg_color'])) : '#2271b1',
            'bar_text_color'       => isset($_POST['bar_text_color']) ? sanitize_hex_color(wp_unslash($_POST['bar_text_color'])) : '#ffffff',
            'btn_bg_color'         => isset($_POST['btn_bg_color']) ? sanitize_hex_color(wp_unslash($_POST['btn_bg_color'])) : '#2271b1',
            'btn_text_color'       => isset($_POST['btn_text_color']) ? sanitize_hex_color(wp_unslash($_POST['btn_text_color'])) : '#ffffff',
        );

        // Validate action_type.
        if (! in_array($new_settings['action_type'], array('popup', 'bar'), true)) {
            $new_settings['action_type'] = 'popup';
        }

        // Validate bar_position.
        if (! in_array($new_settings['bar_position'], array('top', 'bottom'), true)) {
            $new_settings['bar_position'] = 'top';
        }

        update_option('dailybuddy_tp_tools_settings', $new_settings);
        $settings = $new_settings;

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'dailybuddy') . '</p></div>';
    }

    // Include template.
    include __DIR__ . '/templates/settings-page.php';
}

// Initialize module.
new Dailybuddy_TranslatePress_Tools();

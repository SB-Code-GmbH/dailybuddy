<?php

/**
 * Module: Custom Login URL
 *
 * Works exactly like WPS Hide Login - executes immediately on construction
 *
 * @package DailyBuddy
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class WP_Dailybuddy_Custom_Login_URL
 */
class WP_Dailybuddy_Custom_Login_URL
{

    /**
     * Whether wp-login.php was accessed
     *
     * @var bool
     */
    private $wp_login_php;

    /**
     * Constructor
     */
    public function __construct()
    {
        // CRITICAL: Execute check_login_request IMMEDIATELY!
        // Don't wait for hook, as module is loaded too late.
        $this->check_login_request();

        add_action('wp_loaded', array($this, 'wp_loaded'));
        add_action('setup_theme', array($this, 'setup_theme'), 1);

        add_filter('site_url', array($this, 'site_url'), 10, 4);
        add_filter('network_site_url', array($this, 'network_site_url'), 10, 3);
        add_filter('wp_redirect', array($this, 'wp_redirect'), 10, 2);
        add_filter('login_url', array($this, 'login_url'), 10, 3);

        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);

        add_action('admin_init', array($this, 'register_settings'));

        // Add "Custom Login URL" link under Tools when module is active.
        add_action('admin_menu', array($this, 'maybe_add_tools_menu'));
        
        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook)
    {
        // Only load on DailyBuddy settings page
        if (strpos($hook, 'dailybuddy') === false) {
            return;
        }

        if (defined('DAILYBUDDY_URL') && defined('DAILYBUDDY_VERSION')) {
            wp_enqueue_style(
                'dailybuddy-uc',
                DAILYBUDDY_URL . 'assets/css/modul-settings.css',
                array(),
                DAILYBUDDY_VERSION
            );
        }
    }

    /**
     * Add "Custom Login URL" link under Tools when module is active
     */
    public function maybe_add_tools_menu()
    {
        // Only in admin and only for admins.
        if (! is_admin() || ! current_user_can('manage_options')) {
            return;
        }

        // Optional: only show if module system knows the module as active.
        if (class_exists('WP_Dailybuddy_Settings')) {
            $modules   = WP_Dailybuddy_Settings::get_modules();
            $module_id = 'wordpress-tools/custom-login-url';

            // Default: if no entry exists → active.
            $is_module_active = true;
            if (isset($modules[$module_id])) {
                $is_module_active = (bool) $modules[$module_id];
            }

            if (! $is_module_active) {
                return;
            }
        }

        // Use direct URL instead of callback to avoid rendering issues.
        $settings_url = admin_url('admin.php?page=dailybuddy&view=settings&module=wordpress-tools%2Fcustom-login-url');

        add_submenu_page(
            'tools.php',
            __('Custom Login URL', 'dailybuddy'),
            __('Custom Login URL', 'dailybuddy'),
            'manage_options',
            $settings_url,
            ''
        );
    }

    /**
     * Check immediately if login URL is being accessed
     */
    private function check_login_request()
    {
        global $pagenow;

        if (! isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $request     = wp_parse_url(rawurldecode($request_uri));

        // Block wp-login.php.
        if ((strpos(rawurldecode($request_uri), 'wp-login.php') !== false
                || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-login', 'relative')))
            && ! is_admin()
        ) {

            $this->wp_login_php       = true;
            $_SERVER['REQUEST_URI'] = $this->user_trailingslashit('/' . str_repeat('-/', 10));
            $pagenow                  = 'index.php';
        } elseif (
            (isset($request['path']) && untrailingslashit($request['path']) === home_url($this->new_login_slug(), 'relative'))
            ||
            (! get_option('permalink_structure') && $this->is_login_query_string())
        ) {

            $_SERVER['SCRIPT_NAME'] = $this->new_login_slug();
            $pagenow                  = 'wp-login.php';
        } elseif ((strpos(rawurldecode($request_uri), 'wp-register.php') !== false
                || (isset($request['path']) && untrailingslashit($request['path']) === site_url('wp-register', 'relative')))
            && ! is_admin()
        ) {

            $this->wp_login_php       = true;
            $_SERVER['REQUEST_URI'] = $this->user_trailingslashit('/' . str_repeat('-/', 10));
            $pagenow                  = 'index.php';
        }
    }

    /**
     * Check if query string matches the login URL
     *
     * @return bool
     */
    private function is_login_query_string()
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $request     = wp_parse_url(rawurldecode($request_uri));

        if (! isset($request['query'])) {
            return false;
        }

        parse_str($request['query'], $query_vars);
        $slug = $this->new_login_slug();

        return isset($query_vars[$slug]) && empty($query_vars[$slug]);
    }

    /**
     * Get new login slug from options
     *
     * @return string
     */
    private function new_login_slug()
    {
        $slug = get_option('dailybuddy_login_slug');
        if ($slug) {
            return $slug;
        }
        return 'login';
    }

    /**
     * Get new redirect slug from options
     *
     * @return string
     */
    private function new_redirect_slug()
    {
        $slug = get_option('dailybuddy_redirect_slug');
        if ($slug) {
            return $slug;
        }
        return '404';
    }

    /**
     * Check if using trailing slashes
     *
     * @return bool
     */
    private function use_trailing_slashes()
    {
        return ('/' === substr(get_option('permalink_structure'), -1, 1));
    }

    /**
     * Add/remove trailing slash based on permalink settings
     *
     * @param string $string String to modify.
     * @return string
     */
    private function user_trailingslashit($string)
    {
        return $this->use_trailing_slashes() ? trailingslashit($string) : untrailingslashit($string);
    }

    /**
     * Get new login URL
     *
     * @param string|null $scheme URL scheme.
     * @return string
     */
    public function new_login_url($scheme = null)
    {
        if (get_option('permalink_structure')) {
            return $this->user_trailingslashit(home_url('/', $scheme) . $this->new_login_slug());
        } else {
            return home_url('/', $scheme) . '?' . $this->new_login_slug();
        }
    }

    /**
     * Get new redirect URL
     *
     * @param string|null $scheme URL scheme.
     * @return string
     */
    public function new_redirect_url($scheme = null)
    {
        if (get_option('permalink_structure')) {
            return $this->user_trailingslashit(home_url('/', $scheme) . $this->new_redirect_slug());
        } else {
            return home_url('/', $scheme) . '?' . $this->new_redirect_slug();
        }
    }

    /**
     * Get forbidden slugs (reserved WordPress query vars)
     *
     * @return array
     */
    public function forbidden_slugs()
    {
        $wp = new WP();
        return array_merge($wp->public_query_vars, $wp->private_query_vars);
    }

    /**
     * Setup theme hook - block customizer access when not logged in
     */
    public function setup_theme()
    {
        global $pagenow;

        if (! is_user_logged_in() && 'customize.php' === $pagenow) {
            wp_die(esc_html__('This has been disabled', 'dailybuddy'), 403);
        }
    }

    /**
     * WordPress loaded hook - handle redirects and login page
     */
    public function wp_loaded()
    {
        global $pagenow;

        if (! isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        $request     = wp_parse_url(rawurldecode($request_uri));

        // Allow postpass action (password protected posts).
        if (isset($_GET['action']) && 'postpass' === $_GET['action'] && isset($_POST['post_password'])) {
            return;
        }

        // Redirect non-logged-in admin access to redirect URL.
        if (
            is_admin()
            && ! is_user_logged_in()
            && ! defined('WP_CLI')
            && ! defined('DOING_AJAX')
            && ! defined('DOING_CRON')
            && 'admin-post.php' !== $pagenow
            && (! isset($request['path']) || '/wp-admin/options.php' !== $request['path'])
        ) {

            wp_safe_redirect($this->new_redirect_url());
            die();
        }

        // WooCommerce ajax check.
        if (! is_user_logged_in() && isset($_GET['wc-ajax']) && 'profile.php' === $pagenow) {
            wp_safe_redirect($this->new_redirect_url());
            die();
        }

        // Options.php check.
        if (! is_user_logged_in() && isset($request['path']) && '/wp-admin/options.php' === $request['path']) {
            header('Location: ' . $this->new_redirect_url());
            die;
        }

        // Handle wp-login.php access.
        if ('wp-login.php' === $pagenow) {

            // Trailing slash redirect.
            if (
                isset($request['path'])
                && $request['path'] !== $this->user_trailingslashit($request['path'])
                && get_option('permalink_structure')
            ) {

                $query_string = isset($_SERVER['QUERY_STRING']) ? sanitize_text_field(wp_unslash($_SERVER['QUERY_STRING'])) : '';
                wp_safe_redirect(
                    $this->user_trailingslashit($this->new_login_url())
                        . (! empty($query_string) ? '?' . $query_string : '')
                );
                die();
            } elseif ($this->wp_login_php) {
                // Show 404 for direct wp-login.php access.
                $this->wp_template_loader();
            } else {
                // Load real wp-login.php for custom URL.
                global $error, $interim_login, $action, $user_login;

                $redirect_to           = admin_url();
                $requested_redirect_to = isset($_REQUEST['redirect_to']) ? sanitize_text_field(wp_unslash($_REQUEST['redirect_to'])) : '';

                if (is_user_logged_in()) {
                    $user = wp_get_current_user();
                    if (! isset($_REQUEST['action'])) {
                        wp_safe_redirect($redirect_to);
                        die();
                    }
                }

                require_once ABSPATH . 'wp-login.php';
                die();
            }
        }
    }

    /**
     * Load 404 template
     */
    private function wp_template_loader()
    {
        global $pagenow;

        $pagenow = 'index.php';

        if (! defined('DAILYBUDDY_USE_THEMES')) {
            define('DAILYBUDDY_USE_THEMES', true);
        }

        wp();

        require_once ABSPATH . WPINC . '/template-loader.php';

        die;
    }

    /**
     * Filter site URL
     *
     * @param string      $url     The complete site URL.
     * @param string      $path    Path relative to the site URL.
     * @param string|null $scheme  Scheme to give the site URL context.
     * @param int         $blog_id Blog ID.
     * @return string
     */
    public function site_url($url, $path, $scheme, $blog_id)
    {
        return $this->filter_wp_login_php($url, $scheme);
    }

    /**
     * Filter network site URL
     *
     * @param string      $url    The complete network site URL.
     * @param string      $path   Path relative to the network site URL.
     * @param string|null $scheme Scheme to give the URL context.
     * @return string
     */
    public function network_site_url($url, $path, $scheme)
    {
        return $this->filter_wp_login_php($url, $scheme);
    }

    /**
     * Filter wp redirect
     *
     * @param string $location The path or URL to redirect to.
     * @param int    $status   The HTTP response status code.
     * @return string
     */
    public function wp_redirect($location, $status)
    {
        return $this->filter_wp_login_php($location);
    }

    /**
     * Filter login URL
     *
     * @param string $login_url    The login URL.
     * @param string $redirect     The path to redirect to on login.
     * @param bool   $force_reauth Whether to force reauthorization.
     * @return string
     */
    public function login_url($login_url, $redirect, $force_reauth)
    {
        if (is_404()) {
            return '#';
        }

        if (false === $force_reauth) {
            return $login_url;
        }

        if (empty($redirect)) {
            return $this->filter_wp_login_php($login_url);
        }

        $redirect = explode('?', $redirect);

        // Special case for options.php.
        if ($redirect[0] === admin_url('options.php')) {
            return admin_url();
        }

        return $this->filter_wp_login_php($login_url);
    }

    /**
     * Replace wp-login.php with custom slug in URLs
     *
     * @param string      $url    The URL to filter.
     * @param string|null $scheme URL scheme.
     * @return string
     */
    private function filter_wp_login_php($url, $scheme = null)
    {
        // Don't replace postpass action.
        if (strpos($url, 'wp-login.php?action=postpass') !== false) {
            return $url;
        }

        if (strpos($url, 'wp-login.php') !== false && strpos(wp_get_referer(), 'wp-login.php') === false) {

            if (is_ssl()) {
                $scheme = 'https';
            }

            $args = explode('?', $url);

            if (isset($args[1])) {
                parse_str($args[1], $args);

                if (isset($args['login'])) {
                    $args['login'] = rawurlencode($args['login']);
                }

                $url = add_query_arg($args, $this->new_login_url($scheme));
            } else {
                $url = $this->new_login_url($scheme);
            }
        }

        return $url;
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting(
            'dailybuddy_custom_login_url_settings',
            'dailybuddy_login_slug',
            array($this, 'sanitize_login_slug')
        );

        register_setting(
            'dailybuddy_custom_login_url_settings',
            'dailybuddy_redirect_slug',
            array($this, 'sanitize_redirect_slug')
        );
    }

    /**
     * Sanitize login slug
     *
     * @param string $value The value to sanitize.
     * @return string
     */
    public function sanitize_login_slug($value)
    {
        $value = sanitize_title_with_dashes($value);

        if (empty($value) || in_array($value, $this->forbidden_slugs(), true)) {
            add_settings_error(
                'dailybuddy_login_slug',
                'invalid_slug',
                __('Invalid login slug. Please choose a different one.', 'dailybuddy'),
                'error'
            );
            return get_option('dailybuddy_login_slug', 'login');
        }

        return $value;
    }

    /**
     * Sanitize redirect slug
     *
     * @param string $value The value to sanitize.
     * @return string
     */
    public function sanitize_redirect_slug($value)
    {
        $value = sanitize_title_with_dashes($value);

        if (empty($value)) {
            return '404';
        }

        return $value;
    }
}

/**
 * Render settings page
 */
function dailybuddy_render_custom_login_url_settings()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'dailybuddy'));
    }

    $login_slug    = get_option('dailybuddy_login_slug', 'login');
    $redirect_slug = get_option('dailybuddy_redirect_slug', '404');

    if (isset($_POST['dailybuddy_custom_login_url_submit'])) {
        check_admin_referer('dailybuddy_custom_login_url_settings');

        $new_login_slug    = isset($_POST['login_slug']) ? sanitize_title_with_dashes(wp_unslash($_POST['login_slug'])) : 'login';
        $new_redirect_slug = isset($_POST['redirect_slug']) ? sanitize_title_with_dashes(wp_unslash($_POST['redirect_slug'])) : '404';

        $instance = new WP_Dailybuddy_Custom_Login_URL();

        $has_errors = false;

        // Validate login slug.
        if (empty($new_login_slug) || in_array($new_login_slug, $instance->forbidden_slugs(), true)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Invalid login slug. Please choose a different one.', 'dailybuddy') . '</p></div>';
            $has_errors = true;
        }

        // Check if slugs are the same.
        if ($new_login_slug === $new_redirect_slug) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Login slug and redirect slug cannot be the same!', 'dailybuddy') . '</p></div>';
            $has_errors = true;
        }

        if (! $has_errors) {
            update_option('dailybuddy_login_slug', $new_login_slug);
            update_option('dailybuddy_redirect_slug', $new_redirect_slug);
            $login_slug    = $new_login_slug;
            $redirect_slug = $new_redirect_slug;

            echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__('Settings saved!', 'dailybuddy') . '</strong><br>';
            echo sprintf(
                // translators: %s: Login URL.
                esc_html__('Login URL: %s', 'dailybuddy'),
                '<a href="' . esc_url(home_url('/' . $login_slug)) . '" target="_blank"><strong>' . esc_html(home_url('/' . $login_slug)) . '</strong></a>'
            );
            echo '<br>';
            echo sprintf(
                // translators: %s: Redirect URL.
                esc_html__('Redirect URL: %s', 'dailybuddy'),
                '<strong>' . esc_html(home_url('/' . $redirect_slug)) . '</strong>'
            );
            echo '<br><strong>' . esc_html__('BOOKMARK your login URL now!', 'dailybuddy') . '</strong></p></div>';
        }
    }

    include DAILYBUDDY_PATH . 'modules/wordpress-tools/custom-login-url/templates/settings-page.php';
}

// Initialize module.
new WP_Dailybuddy_Custom_Login_URL();

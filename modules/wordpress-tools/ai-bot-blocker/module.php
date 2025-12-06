<?php

/**
 * Module: AI Bot Blocker
 * 
 * Block AI bots and crawlers from accessing your content
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_AI_Bot_Blocker
{
    private $settings;

    /**
     * Known AI Bots Database
     */
    private $ai_bots = array(
        'openai' => array(
            'name' => 'OpenAI (GPTBot)',
            'user_agent' => 'GPTBot',
            'company' => 'OpenAI',
            'description' => 'Used to train ChatGPT and other OpenAI models',
            'respects_robots' => true,
        ),
        'google-extended' => array(
            'name' => 'Google Gemini',
            'user_agent' => 'Google-Extended',
            'company' => 'Google',
            'description' => 'Used for Google Bard/Gemini AI training',
            'respects_robots' => true,
        ),
        'ccbot' => array(
            'name' => 'Common Crawl',
            'user_agent' => 'CCBot',
            'company' => 'Common Crawl',
            'description' => 'Used by many AI companies for training data',
            'respects_robots' => true,
        ),
        'claude' => array(
            'name' => 'Claude (Anthropic)',
            'user_agent' => 'ClaudeBot',
            'company' => 'Anthropic',
            'description' => 'Used to train Claude AI models',
            'respects_robots' => true,
        ),
        'anthropic' => array(
            'name' => 'Anthropic AI',
            'user_agent' => 'anthropic-ai',
            'company' => 'Anthropic',
            'description' => 'Alternative Anthropic crawler',
            'respects_robots' => true,
        ),
        'perplexity' => array(
            'name' => 'Perplexity AI',
            'user_agent' => 'PerplexityBot',
            'company' => 'Perplexity',
            'description' => 'Used for Perplexity AI search and answers',
            'respects_robots' => true,
        ),
        'apple' => array(
            'name' => 'Apple Intelligence',
            'user_agent' => 'Applebot-Extended',
            'company' => 'Apple',
            'description' => 'Used for Apple AI features',
            'respects_robots' => true,
        ),
        'facebook' => array(
            'name' => 'Meta AI',
            'user_agent' => 'FacebookBot',
            'company' => 'Meta',
            'description' => 'Used for Meta AI training',
            'respects_robots' => true,
        ),
        'bytedance' => array(
            'name' => 'ByteDance (TikTok)',
            'user_agent' => 'Bytespider',
            'company' => 'ByteDance',
            'description' => 'Used for TikTok and ByteDance AI',
            'respects_robots' => false,
        ),
        'amazon' => array(
            'name' => 'Amazon Bot',
            'user_agent' => 'Amazonbot',
            'company' => 'Amazon',
            'description' => 'Used for Alexa and Amazon AI',
            'respects_robots' => true,
        ),
        'cohere' => array(
            'name' => 'Cohere AI',
            'user_agent' => 'cohere-ai',
            'company' => 'Cohere',
            'description' => 'Used for Cohere AI models',
            'respects_robots' => true,
        ),
    );

    public function __construct()
    {
        // Load settings
        $this->settings = get_option('dailybuddy_ai_bot_blocker_settings', array(
            'blocked_bots' => array(),
            'use_meta_tags' => true,
            'use_robots_txt' => true,
            'use_htaccess' => false,
        ));

        // Add meta tags - only if blocking many bots (80%+)
        $blocked_count = isset($this->settings['blocked_bots']) ? count($this->settings['blocked_bots']) : 0;
        $total_bots = count($this->ai_bots);
        $should_use_meta = $blocked_count >= ($total_bots * 0.8); // 80% threshold

        if (!empty($this->settings['use_meta_tags']) && $should_use_meta) {
            add_action('wp_head', array($this, 'add_meta_tags'), 1);
        }

        // Manage physical robots.txt file
        if (!empty($this->settings['use_robots_txt'])) {
            add_action('init', function () {
                WP_Dailybuddy_AI_Bot_Blocker::manage_robots_txt_file();
            });
            // Also use WordPress filter as fallback
            add_filter('robots_txt', array($this, 'modify_robots_txt'), 10, 2);
        }

        // Add HTTP headers
        if (!empty($this->settings['use_htaccess'])) {
            add_action('send_headers', array($this, 'add_http_headers'));
        }

        // Settings
        add_action('admin_init', array($this, 'register_settings'));

        // Enqueue admin styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));

        // Clean up on deactivation
        register_deactivation_hook(__FILE__, array('WP_Dailybuddy_AI_Bot_Blocker', 'cleanup_robots_txt'));
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
     * Get AI bots list
     */
    public function get_ai_bots()
    {
        return $this->ai_bots;
    }

    /**
     * Add meta tags to block AI
     */
    public function add_meta_tags()
    {
        echo '<!-- AI Bot Blocker by DailyBuddy -->' . "\n";
        echo '<meta name="robots" content="noai, noimageai">' . "\n";
        echo '<meta name="googlebot" content="noai, noimageai">' . "\n";
    }

    /**
     * Modify robots.txt (WordPress filter fallback)
     */
    public function modify_robots_txt($output, $public)
    {
        $blocked_bots = isset($this->settings['blocked_bots']) ? $this->settings['blocked_bots'] : array();

        if (empty($blocked_bots)) {
            return $output;
        }

        $ai_rules = "\n# AI Bot Blocker by DailyBuddy\n";
        $ai_rules .= "# Block AI crawlers from training on content\n\n";

        foreach ($blocked_bots as $bot_key) {
            if (isset($this->ai_bots[$bot_key])) {
                $bot = $this->ai_bots[$bot_key];
                $ai_rules .= "User-agent: {$bot['user_agent']}\n";
                $ai_rules .= "Disallow: /\n\n";
            }
        }

        return $output . $ai_rules;
    }

    /**
     * Manage physical robots.txt file
     */
    public static function manage_robots_txt_file($settings = null)
    {
        if ($settings === null) {
            $settings = get_option('dailybuddy_ai_bot_blocker_settings', array());
        }

        $robots_file = ABSPATH . 'robots.txt';
        $blocked_bots = isset($settings['blocked_bots']) ? $settings['blocked_bots'] : array();
        $use_robots_txt = !empty($settings['use_robots_txt']);

        // Get AI bots list
        $instance = new self();
        $ai_bots = $instance->ai_bots;

        // If checkbox disabled OR no bots selected, clean up and return
        if (!$use_robots_txt || empty($blocked_bots)) {
            self::cleanup_robots_txt();
            return;
        }

        // Generate our rules
        $our_rules = self::generate_robots_rules($blocked_bots, $ai_bots);

        // Read existing file if it exists
        $existing_content = '';
        if (file_exists($robots_file)) {
            $existing_content = file_get_contents($robots_file);

            // Remove our old rules if they exist
            $existing_content = self::remove_our_rules($existing_content);
        }

        // Combine existing content with our rules
        $new_content = trim($existing_content) . "\n\n" . $our_rules;

        require_once ABSPATH . 'wp-admin/includes/file.php';

        global $wp_filesystem;

        if (! $wp_filesystem) {
            WP_Filesystem();
        }

        // Check if path is writable using WordPress helpers.
        if (wp_is_writable(ABSPATH) || wp_is_writable($robots_file)) {
            // Use WP_Filesystem instead of direct PHP file operations.
            $wp_filesystem->put_contents($robots_file, $new_content, FS_CHMOD_FILE);
        }
    }

    /**
     * Generate our robots.txt rules
     */
    private static function generate_robots_rules($blocked_bots, $ai_bots)
    {
        $rules = "# BEGIN AI Bot Blocker by DailyBuddy\n";
        $rules .= "# Block AI crawlers from training on content\n";
        $rules .= "# Generated: " . gmdate('Y-m-d H:i:s') . "\n\n";

        foreach ($blocked_bots as $bot_key) {
            if (isset($ai_bots[$bot_key])) {
                $bot = $ai_bots[$bot_key];
                $rules .= "User-agent: {$bot['user_agent']}\n";
                $rules .= "Disallow: /\n\n";
            }
        }

        $rules .= "# END AI Bot Blocker by DailyBuddy\n";

        return $rules;
    }

    /**
     * Remove our rules from content
     */
    private static function remove_our_rules($content)
    {
        // Remove everything between our markers
        $pattern = '/# BEGIN AI Bot Blocker by DailyBuddy.*?# END AI Bot Blocker by DailyBuddy\n?/s';
        $content = preg_replace($pattern, '', $content);

        // Clean up extra newlines
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return trim($content);
    }

    /**
     * Clean up robots.txt on deactivation
     */
    public static function cleanup_robots_txt()
    {
        $robots_file = ABSPATH . 'robots.txt';

        if (!file_exists($robots_file)) {
            return;
        }

        $content = file_get_contents($robots_file);
        $cleaned = self::remove_our_rules($content);

        // If file is now empty, delete it
        if (empty(trim($cleaned))) {
            wp_delete_file($robots_file);
        } else {
            // Otherwise update with cleaned content
            file_put_contents($robots_file, $cleaned);
        }
    }

    /**
     * Add HTTP headers
     */
    public function add_http_headers()
    {
        header('X-Robots-Tag: noai, noimageai', false);
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting(
            'dailybuddy_ai_bot_blocker_settings',
            'dailybuddy_ai_bot_blocker_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings'),
            )
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        // Sanitize blocked bots
        if (isset($input['blocked_bots']) && is_array($input['blocked_bots'])) {
            $sanitized['blocked_bots'] = array_map('sanitize_text_field', $input['blocked_bots']);
        } else {
            $sanitized['blocked_bots'] = array();
        }

        // Sanitize method options
        $sanitized['use_meta_tags'] = isset($input['use_meta_tags']) ? (bool)$input['use_meta_tags'] : false;
        $sanitized['use_robots_txt'] = isset($input['use_robots_txt']) ? (bool)$input['use_robots_txt'] : false;
        $sanitized['use_htaccess'] = isset($input['use_htaccess']) ? (bool)$input['use_htaccess'] : false;

        // Update robots.txt file after saving settings
        $this->settings = $sanitized;
        $this->manage_robots_txt_file();

        return $sanitized;
    }
}

/**
 * Render settings page
 */
function dailybuddy_render_ai_bot_blocker_settings()
{
    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'dailybuddy'));
    }

    $dailybuddy_instance = new WP_Dailybuddy_AI_Bot_Blocker();
    $dailybuddy_ai_bots = $dailybuddy_instance->get_ai_bots();

    // Get current settings
    $dailybuddy_settings = get_option('dailybuddy_ai_bot_blocker_settings', array(
        'blocked_bots' => array(),
        'use_meta_tags' => true,
        'use_robots_txt' => true,
        'use_htaccess' => false,
    ));

    // Handle form submission
    if (isset($_POST['dailybuddy_ai_bot_blocker_submit'])) {
        check_admin_referer('dailybuddy_ai_bot_blocker_settings');

        $dailybuddy_new_settings = array(
            'blocked_bots'  => isset($_POST['blocked_bots'])
                ? array_map('sanitize_text_field', wp_unslash((array) $_POST['blocked_bots']))
                : array(),
            'use_meta_tags'  => isset($_POST['use_meta_tags']) ? true : false,
            'use_robots_txt' => isset($_POST['use_robots_txt']) ? true : false,
            'use_htaccess'   => isset($_POST['use_htaccess']) ? true : false,
        );

        update_option('dailybuddy_ai_bot_blocker_settings', $dailybuddy_new_settings);
        $dailybuddy_settings = $dailybuddy_new_settings;

        // Update robots.txt file immediately after saving
        WP_Dailybuddy_AI_Bot_Blocker::manage_robots_txt_file($dailybuddy_new_settings);

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!', 'dailybuddy') . '</p></div>';
    }

    // Include template
    include __DIR__ . '/templates/settings-page.php';
}

// Initialize module
new WP_Dailybuddy_AI_Bot_Blocker();

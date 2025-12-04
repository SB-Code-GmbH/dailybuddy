<?php

/**
 * dailybuddy Translation Scanner
 * 
 * Scans plugin files for translatable strings and compares with PO files
 */

if (! defined('ABSPATH')) {
    exit;
}

class WP_Dailybuddy_Translation_Scanner
{

    private $plugin_path;
    private $languages_dir;
    private $text_domain = 'dailybuddy';

    // WordPress i18n functions to scan for
    private $i18n_functions = array(
        '__',
        '_e',
        '_x',
        '_ex',
        '_n',
        '_nx',
        '_n_noop',
        '_nx_noop',
        'esc_html__',
        'esc_html_e',
        'esc_html_x',
        'esc_attr__',
        'esc_attr_e',
        'esc_attr_x',
    );

    public function __construct()
    {
        $this->plugin_path = DAILYBUDDY_PATH;
        $this->languages_dir = DAILYBUDDY_PATH . 'languages/';

        // Register AJAX handlers
        add_action('wp_ajax_dailybuddy_scan_translations', array($this, 'ajax_scan_translations'));
        add_action('wp_ajax_dailybuddy_add_translations', array($this, 'ajax_add_translations'));
        add_action('wp_ajax_dailybuddy_get_empty_translations', array($this, 'ajax_get_empty_translations'));
        add_action('wp_ajax_dailybuddy_create_language', array($this, 'ajax_create_language'));
    }

    /**
     * AJAX: Scan for missing translations
     */
    public function ajax_scan_translations()
    {

        check_ajax_referer('dailybuddy_translation_scanner', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(
                array(
                    'message' => __('Insufficient permissions', 'dailybuddy'),
                )
            );
            return;
        }

        // Scan plugin files for strings.
        $found_strings = $this->scan_plugin_files();

        // Get available locales.
        $available_locales = $this->get_available_locales();

        // Compare with existing PO files.
        $missing_strings = $this->find_missing_strings($found_strings, $available_locales);

        wp_send_json_success(
            array(
                'total_strings'    => count($found_strings),
                'files_scanned'    => $this->count_php_files(),
                'missing_strings'  => $missing_strings,
                'available_locales' => $available_locales,
            )
        );
    }

    /**
     * AJAX: Add translations to PO files
     */
    public function ajax_add_translations()
    {
        check_ajax_referer('dailybuddy_translation_scanner', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'dailybuddy')));
            return;
        }

        // Get raw POST data
        $locales_json = isset($_POST['locales']) ? wp_unslash($_POST['locales']) : '';
        $strings_json = isset($_POST['strings']) ? wp_unslash($_POST['strings']) : '';

        // Decode JSON strings
        $dailybuddy_locales = json_decode($locales_json, true);
        $strings = json_decode($strings_json, true);

        // Ensure arrays
        if (!is_array($dailybuddy_locales)) {
            $dailybuddy_locales = array();
        }
        if (!is_array($strings)) {
            $strings = array();
        }

        // Sanitize locale values
        $dailybuddy_locales = array_map('sanitize_text_field', $dailybuddy_locales);

        // Validate data
        if (empty($dailybuddy_locales) || empty($strings)) {
            wp_send_json_error(array(
                'message' => __('Erforderliche Daten fehlen', 'dailybuddy'),
                'debug' => array(
                    'locales_received' => !empty($locales_json),
                    'strings_received' => !empty($strings_json),
                    'locales_decoded' => $dailybuddy_locales,
                    'strings_count' => count($strings),
                    'json_error' => json_last_error_msg()
                )
            ));
            return;
        }

        $added_count   = 0;
        $updated_files = array();

        // ALWAYS update POT file first.
        $pot_updated = $this->update_pot_file($strings);

        foreach ($dailybuddy_locales as $locale) {
            $po_file = $this->languages_dir . 'dailybuddy-' . $locale . '.po';

            if (! file_exists($po_file)) {
                continue;
            }

            $result = $this->add_strings_to_po_file($po_file, $strings);

            if ($result) {
                $added_count++;
                $updated_files[] = $locale;
            }
        }

        if ($added_count > 0) {
            $message = sprintf(
                // translators: 1: number of strings found, 2: number of translation files updated.
                _n(
                    'Successfully added %1$d strings to %2$d translation file.',
                    'Successfully added %1$d strings to %2$d translation files.',
                    $added_count,
                    'dailybuddy'
                ),
                count($strings),
                $added_count
            );


            // Add POT update info.
            if ($pot_updated) {
                $message .= ' ' . __('POT template file was also updated.', 'dailybuddy');
            }

            wp_send_json_success(
                array(
                    'message'       => $message,
                    'updated_files' => $updated_files,
                    'pot_updated'   => $pot_updated,
                )
            );
            return;
        }

        wp_send_json_error(array('message' => __('Failed to update translation files', 'dailybuddy')));
    }

    /**
     * Scan all plugin PHP files for translatable strings
     */
    private function scan_plugin_files()
    {
        $strings = array();
        $files = $this->get_php_files($this->plugin_path);

        foreach ($files as $file) {
            $file_strings = $this->extract_strings_from_file($file);
            $strings = array_merge($strings, $file_strings);
        }

        // Remove duplicates
        return array_unique($strings, SORT_REGULAR);
    }

    /**
     * Get all PHP files in plugin directory
     */
    private function get_php_files($dir)
    {
        $files = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            // Skip non-PHP files
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Skip vendor/node_modules directories
            $path = $file->getPathname();
            if (strpos($path, '/vendor/') !== false || strpos($path, '/node_modules/') !== false) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }

    /**
     * Count PHP files for stats
     */
    private function count_php_files()
    {
        return count($this->get_php_files($this->plugin_path));
    }

    /**
     * Extract translatable strings from a PHP file
     */
    private function extract_strings_from_file($file)
    {
        $strings = array();
        $content = file_get_contents($file);
        $relative_path = str_replace($this->plugin_path, '', $file);

        // Handle _n() and _nx() separately (they have 2 strings: singular and plural)
        $plural_functions = array('_n', '_nx', '_n_noop', '_nx_noop');
        $plural_pattern = '/(?:_n|_nx|_n_noop|_nx_noop)\s*\(\s*(["\'])(.+?)\1\s*,\s*(["\'])(.+?)\3\s*,.*?["\']' . preg_quote($this->text_domain) . '["\'].*?\)/s';

        if (preg_match_all($plural_pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // Singular form
                $singular = stripslashes($match[2]);
                if (!empty(trim($singular))) {
                    $strings[] = array(
                        'string' => $singular,
                        'file' => $relative_path,
                        'context' => $this->get_string_context($content, $singular)
                    );
                }

                // Plural form
                $plural = stripslashes($match[4]);
                if (!empty(trim($plural))) {
                    $strings[] = array(
                        'string' => $plural,
                        'file' => $relative_path,
                        'context' => $this->get_string_context($content, $plural)
                    );
                }
            }
        }

        // Handle regular functions (single string)
        $regular_functions = array(
            '__',
            '_e',
            '_x',
            '_ex',
            'esc_html__',
            'esc_html_e',
            'esc_html_x',
            'esc_attr__',
            'esc_attr_e',
            'esc_attr_x'
        );

        $functions = implode('|', array_map('preg_quote', $regular_functions));
        $pattern = '/(?:' . $functions . ')\s*\(\s*(["\'])(.+?)\1\s*,\s*["\']' . preg_quote($this->text_domain) . '["\'].*?\)/s';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $string = $match[2];

                // Skip empty strings
                if (empty(trim($string))) {
                    continue;
                }

                // Clean up string
                $string = stripslashes($string);

                $strings[] = array(
                    'string' => $string,
                    'file' => $relative_path,
                    'context' => $this->get_string_context($content, $string)
                );
            }
        }

        return $strings;
    }

    /**
     * Get context information for a string (e.g., which function it's in)
     */
    private function get_string_context($content, $string)
    {
        // Find the line where the string appears
        $lines = explode("\n", $content);
        $line_number = 0;

        foreach ($lines as $num => $line) {
            if (strpos($line, $string) !== false) {
                $line_number = $num + 1;
                break;
            }
        }

        return 'Line ' . $line_number;
    }

    /**
     * Get available translation locales
     */
    private function get_available_locales()
    {
        $locales = array();

        if (! is_dir($this->languages_dir)) {
            return $locales;
        }

        $po_files = glob($this->languages_dir . 'dailybuddy-*.po');

        foreach ($po_files as $po_file) {
            if (preg_match('/dailybuddy-([a-z]{2}_[A-Z]{2})\.po/', basename($po_file), $matches)) {
                $locale = $matches[1];
                $locales[$locale] = $po_file;
            }
        }

        return $locales;
    }

    /**
     * Find missing strings by comparing with PO files
     */
    private function find_missing_strings($found_strings, $available_locales)
    {
        $missing = array();

        foreach ($found_strings as $string_data) {
            $string = $string_data['string'];
            $missing_in = array();

            foreach ($available_locales as $locale => $po_file) {
                if (! $this->string_exists_in_po($po_file, $string)) {
                    $missing_in[] = $locale;
                }
            }

            if (! empty($missing_in)) {
                $missing[] = array(
                    'string' => $string,
                    'context' => $string_data['file'] . ' (' . $string_data['context'] . ')',
                    'missing_in' => $missing_in
                );
            }
        }

        return $missing;
    }

    /**
     * Check if a string exists in a PO file
     */
    private function string_exists_in_po($po_file, $string)
    {
        if (! file_exists($po_file)) {
            return false;
        }

        $content = file_get_contents($po_file);

        // Escape quotes in string for matching
        $escaped_string = str_replace('"', '\\"', $string);

        // Look for msgid "string"
        $pattern = '/msgid\s+"' . preg_quote($escaped_string, '/') . '"/';

        return preg_match($pattern, $content) === 1;
    }

    /**
     * Add strings to a PO file
     */
    private function add_strings_to_po_file($po_file, $strings)
    {
        if (! file_exists($po_file)) {
            return false;
        }

        $content = file_get_contents($po_file);
        $new_entries = '';

        foreach ($strings as $string_data) {
            $string = $string_data['string'];

            // Skip if already exists
            if ($this->string_exists_in_po($po_file, $string)) {
                continue;
            }

            // Escape quotes
            $escaped_string = str_replace('"', '\\"', $string);

            // Create PO entry
            $new_entries .= "\n";
            $new_entries .= '#: ' . $string_data['context'] . "\n";
            $new_entries .= 'msgid "' . $escaped_string . '"' . "\n";
            $new_entries .= 'msgstr ""' . "\n";
        }

        if (empty($new_entries)) {
            return true; // Nothing to add
        }

        // Backup original file
        copy($po_file, $po_file . '.backup');

        // Append new entries to file
        $result = file_put_contents($po_file, $content . $new_entries);

        if ($result === false) {
            // Restore backup if write failed
            copy($po_file . '.backup', $po_file);
            wp_delete_file($po_file . '.backup');
            return false;
        }

        // Keep backup for safety
        return true;
    }

    /**
     * Update POT file with new strings
     */
    private function update_pot_file($strings)
    {
        $pot_file = $this->languages_dir . 'dailybuddy.pot';

        if (! file_exists($pot_file)) {
            return false;
        }

        $content = file_get_contents($pot_file);
        $new_entries = '';

        foreach ($strings as $string_data) {
            $string = $string_data['string'];

            // Skip if already exists
            if ($this->string_exists_in_po($pot_file, $string)) {
                continue;
            }

            // Escape quotes
            $escaped_string = str_replace('"', '\\"', $string);

            // Create POT entry
            $new_entries .= "\n";
            $new_entries .= '#: ' . $string_data['context'] . "\n";
            $new_entries .= 'msgid "' . $escaped_string . '"' . "\n";
            $new_entries .= 'msgstr ""' . "\n";
        }

        if (empty($new_entries)) {
            return true; // Nothing to add
        }

        // Backup original file
        copy($pot_file, $pot_file . '.backup');

        // Append new entries to file
        $result = file_put_contents($pot_file, $content . $new_entries);

        if ($result === false) {
            // Restore backup if write failed
            copy($pot_file . '.backup', $pot_file);
            wp_delete_file($pot_file . '.backup');
            return false;
        }

        return true;
    }

    /**
     * Count empty translations in a PO file
     */
    private function count_empty_translations($po_file)
    {
        if (! file_exists($po_file)) {
            return 0;
        }

        $content = file_get_contents($po_file);

        // Pattern to find msgid followed by empty msgstr
        // Matches: msgid "something" \n msgstr ""
        $pattern = '/msgid\s+"(.+?)"\s*\nmsgstr\s+""\s*\n/s';

        preg_match_all($pattern, $content, $matches);

        // Subtract 1 because the header usually has an empty msgstr
        $count = count($matches[0]);
        return max(0, $count - 1);
    }

    /**
     * AJAX: Get empty translations count for all locales
     */
    public function ajax_get_empty_translations()
    {
        check_ajax_referer('dailybuddy_translation_scanner', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'dailybuddy')));
            return;
        }

        $available_locales = $this->get_available_locales();
        $empty_counts = array();

        foreach ($available_locales as $locale => $po_file) {
            $empty_counts[$locale] = $this->count_empty_translations($po_file);
        }

        wp_send_json_success(array('empty_counts' => $empty_counts));
        return;
    }

    /**
     * AJAX: Create new language from POT file
     */
    public function ajax_create_language()
    {

        check_ajax_referer('dailybuddy_translation_scanner', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'dailybuddy')));
            return;
        }

        $locale = isset($_POST['locale'])
            ? sanitize_text_field(wp_unslash($_POST['locale']))
            : '';


        if (empty($locale)) {
            wp_send_json_error(array('message' => __('Locale is required', 'dailybuddy')));
            return;
        }

        // Validate locale format (e.g., de_DE, fr_FR)
        if (! preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
            wp_send_json_error(array('message' => __('Invalid locale format. Use format like: de_DE, fr_FR, en_US', 'dailybuddy')));
            return;
        }

        $pot_file = $this->languages_dir . 'dailybuddy.pot';
        $new_po_file = $this->languages_dir . 'dailybuddy-' . $locale . '.po';
        $new_mo_file = $this->languages_dir . 'dailybuddy-' . $locale . '.mo';

        // Check if POT file exists
        if (! file_exists($pot_file)) {
            wp_send_json_error(array('message' => __('POT template file not found', 'dailybuddy')));
            return;
        }

        // Check if PO file already exists
        if (file_exists($new_po_file)) {
            // translators: %s is the locale code (e.g., de_DE)
            wp_send_json_error(array('message' => sprintf(__('Language file for %s already exists', 'dailybuddy'), $locale)));
            return;
        }

        // Copy POT to new PO file
        if (! copy($pot_file, $new_po_file)) {
            wp_send_json_error(array('message' => __('Failed to create PO file', 'dailybuddy')));
            return;
        }

        // Update PO file headers
        $content = file_get_contents($new_po_file);
        $content = str_replace('LANGUAGE <LL@li.org>', $locale . ' <admin@example.com>', $content);
        $content = str_replace('Language: ', 'Language: ' . $locale, $content);
        file_put_contents($new_po_file, $content);

        // Create empty MO file
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_touch -- Creating empty MO file
        touch($new_mo_file);

        wp_send_json_success(array(
            // translators: %s is the locale code (e.g., de_DE)
            'message' => sprintf(__('Successfully created language files for %s', 'dailybuddy'), $locale),
            'locale' => $locale,
            'po_file' => basename($new_po_file),
            'mo_file' => basename($new_mo_file)
        ));
        return;
    }
}

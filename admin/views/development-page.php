<?php

/**
 * Development Page - Only visible when DAILYBUDDY_DEV_MODE is enabled
 */

if (! defined('ABSPATH')) {
    exit;
}

// Security check - only accessible in DEV_MODE
if (! defined('DAILYBUDDY_DEV_MODE') || ! DAILYBUDDY_DEV_MODE) {
    wp_die(esc_html__('Access denied. Development mode is not enabled.', 'dailybuddy'));
}

// Enqueue necessary styles
wp_enqueue_style('dailybuddy-admin', DAILYBUDDY_URL . 'assets/css/admin.css', array(), DAILYBUDDY_VERSION);
wp_enqueue_style('dailybuddy-dev-mode', DAILYBUDDY_URL . 'assets/css/modul-settings.css', array(), DAILYBUDDY_VERSION);
wp_enqueue_style('dailybuddy-scanner', DAILYBUDDY_URL . 'assets/css/translation-scanner.css', array(), DAILYBUDDY_VERSION);

// Zeile 17, nach den Style-Enqueues:
wp_enqueue_script(
    'dailybuddy-development-page',
    DAILYBUDDY_URL . 'assets/js/development-page.js',
    array('jquery'),
    DAILYBUDDY_VERSION,
    true
);

// AJAX-Daten für das Script bereitstellen
wp_localize_script(
    'dailybuddy-development-page',
    'dailybuddyDev',
    array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dailybuddy_translation_scanner'),
        'i18n' => array(
            'pleaseEnterLocale' => __('Please enter a locale code.', 'dailybuddy'),
            'invalidLocaleFormat' => __('Invalid locale format. Use format like: de_DE, fr_FR, en_US', 'dailybuddy'),
            'creatingLanguageFiles' => __('Creating language files...', 'dailybuddy'),
            'errorOccurred' => __('An error occurred. Please try again.', 'dailybuddy'),
            'found' => __('Found', 'dailybuddy'),
            'translatableStringsIn' => __('translatable strings in', 'dailybuddy'),
            'files' => __('files.', 'dailybuddy'),
            'stringsAreMissing' => __('strings are missing from at least one translation file.', 'dailybuddy'),
            'allStringsTranslated' => __('All strings are translated!', 'dailybuddy'),
            'selectAtLeastOne' => __('Please select at least one string to add.', 'dailybuddy'),
            'adding' => __('Adding...', 'dailybuddy'),
            'addTo' => __('Add to', 'dailybuddy'),
            'untranslated' => __('untranslated', 'dailybuddy'),
            'complete' => __('Complete', 'dailybuddy'),
        )
    )
);

// Read-only GET parameter: switches visible tab.
// Safe without nonce because no data is being changed.
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$dailybuddy_current_tab = isset($_GET['tab'])
    ? sanitize_key(wp_unslash($_GET['tab']))
    : 'general';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

?>

<div class="wrap">

    <!-- Breadcrumbs -->
    <div class="dailybuddy-breadcrumbs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy')); ?>">
            <?php esc_html_e('DailyBuddy', 'dailybuddy'); ?>
        </a>
        <span class="separator">›</span>
        <strong><?php esc_html_e('Development', 'dailybuddy'); ?></strong>
    </div>

    <!-- Header im selben Layout wie andere Settings -->
    <h1 class="dailybuddy-settings-header">
        <span class="dailybuddy-settings-icon dashicons dashicons-admin-tools"></span>
        <span class="dailybuddy-settings-title">
            <?php esc_html_e('Development', 'dailybuddy'); ?>
            <small><?php esc_html_e('Settings', 'dailybuddy'); ?></small>
        </span>
    </h1>

    <!-- Warning Notice -->
    <div class="notice notice-warning inline" style="margin: 20px 0;">
        <p>
            <strong><?php esc_html_e('Development Mode Active', 'dailybuddy'); ?></strong><br>
            <?php esc_html_e('This page is only visible when DAILYBUDDY_DEV_MODE is set to true in dailybuddy.php.', 'dailybuddy'); ?>
        </p>
    </div>

    <div class="dailybuddy-settings-container">

        <!-- Tabs Navigation -->
        <div class="dailybuddy-uc-tabs">
            <button type="button" class="dailybuddy-uc-tab <?php echo ('general' === $dailybuddy_current_tab) ? 'active' : ''; ?>" data-tab="general">
                <span class="dashicons dashicons-admin-generic" style="margin-right: 5px;"></span>
                <?php esc_html_e('General', 'dailybuddy'); ?>
            </button>
            <button type="button" class="dailybuddy-uc-tab <?php echo ('translation' === $dailybuddy_current_tab) ? 'active' : ''; ?>" data-tab="translation">
                <span class="dashicons dashicons-translation" style="margin-right: 5px;"></span>
                <?php esc_html_e('Translation', 'dailybuddy'); ?>
            </button>
        </div>

        <!-- General Tab Content -->
        <div class="dailybuddy-uc-tab-content <?php echo ('general' === $dailybuddy_current_tab) ? 'active' : ''; ?>" data-tab="general">

            <!-- Plugin Information -->
            <h2><?php esc_html_e('Plugin Information', 'dailybuddy'); ?></h2>

            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th style="width: 250px;"><?php esc_html_e('Plugin Version', 'dailybuddy'); ?></th>
                        <td><?php echo esc_html(DAILYBUDDY_VERSION); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Plugin Path', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(DAILYBUDDY_PATH); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Plugin URL', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(DAILYBUDDY_URL); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Plugin Basename', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(DAILYBUDDY_BASENAME); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Development Mode', 'dailybuddy'); ?></th>
                        <td>
                            <span style="color: #d63638; font-weight: bold;">
                                ✓ <?php esc_html_e('ENABLED', 'dailybuddy'); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr style="margin: 30px 0;" />

            <!-- WordPress Environment -->
            <h2><?php esc_html_e('WordPress Environment', 'dailybuddy'); ?></h2>

            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th style="width: 250px;"><?php esc_html_e('WordPress Version', 'dailybuddy'); ?></th>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('PHP Version', 'dailybuddy'); ?></th>
                        <td><?php echo esc_html(phpversion()); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Site URL', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(get_site_url()); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Home URL', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(get_home_url()); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Active Theme', 'dailybuddy'); ?></th>
                        <td>
                            <?php
                            $dailybuddy_theme = wp_get_theme();
                            echo esc_html($dailybuddy_theme->get('Name') . ' ' . $dailybuddy_theme->get('Version'));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('WP Debug', 'dailybuddy'); ?></th>
                        <td>
                            <?php
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                echo '<span style="color: #d63638;">✓ ' . esc_html__('Enabled', 'dailybuddy') . '</span>';
                            } else {
                                echo '<span style="color: #46b450;">✗ ' . esc_html__('Disabled', 'dailybuddy') . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr style="margin: 30px 0;" />

            <!-- Active Modules -->
            <h2><?php esc_html_e('Active Modules', 'dailybuddy'); ?></h2>

            <?php
            $dailybuddy_active_modules  = Dailybuddy_Settings::get_modules();
            $dailybuddy_active_count   = count(array_filter($dailybuddy_active_modules));
            ?>

            <p>
                <?php
                $dailybuddy_message = sprintf(
                    /* translators: %s: Number of active modules (wrapped in strong tags). */
                    _n(
                        'Currently %s module is active.',
                        'Currently %s modules are active.',
                        $dailybuddy_active_count,
                        'dailybuddy'
                    ),
                    '<strong>' . (int) $dailybuddy_active_count . '</strong>'
                );
                echo wp_kses_post($dailybuddy_message);
                ?>
            </p>

            <?php if ($dailybuddy_active_count > 0) : ?>
                <ul style="list-style: disc; margin-left: 20px;">
                    <?php foreach ($dailybuddy_active_modules as $dailybuddy_module_id => $dailybuddy_is_active) : ?>
                        <?php if ($dailybuddy_is_active) : ?>
                            <li><code><?php echo esc_html($dailybuddy_module_id); ?></code></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="description">
                    <?php esc_html_e('No modules are currently active.', 'dailybuddy'); ?>
                </p>
            <?php endif; ?>

            <hr style="margin: 30px 0;" />

            <!-- Debug Tools -->
            <h2><?php esc_html_e('Debug Tools', 'dailybuddy'); ?></h2>

            <p>
                <?php esc_html_e('Additional debug tools and utilities for development.', 'dailybuddy'); ?>
            </p>

            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy')); ?>" class="button button-secondary">
                    <?php esc_html_e('Back to Modules', 'dailybuddy'); ?>
                </a>
            </p>

            <p class="description">
                <?php esc_html_e('To disable this page, set DAILYBUDDY_DEV_MODE to false in dailybuddy.php.', 'dailybuddy'); ?>
            </p>

        </div>

        <!-- Translation Tab Content -->
        <div class="dailybuddy-uc-tab-content <?php echo ('translation' === $dailybuddy_current_tab) ? 'active' : ''; ?>" data-tab="translation">

            <h2><?php esc_html_e('Translation Information', 'dailybuddy'); ?></h2>

            <?php
            // Get available translations.
            $dailybuddy_translations  = array();
            $dailybuddy_languages_dir = DAILYBUDDY_PATH . 'languages/';

            if (is_dir($dailybuddy_languages_dir)) {
                $dailybuddy_po_files = glob($dailybuddy_languages_dir . '*.po');
                if ($dailybuddy_po_files) {
                    foreach ($dailybuddy_po_files as $dailybuddy_po_file) {
                        $dailybuddy_filename = basename($dailybuddy_po_file);
                        // Extract locale from filename (e.g., dailybuddy-de_DE.po -> de_DE).
                        if (preg_match('/dailybuddy-([a-z]{2}_[A-Z]{2})\.po/', $dailybuddy_filename, $matches)) {
                            $locale  = $matches[1];
                            $dailybuddy_mo_file = str_replace('.po', '.mo', $dailybuddy_po_file);

                            $dailybuddy_translations[$locale] = array(
                                'po_file'     => $dailybuddy_po_file,
                                'mo_file'     => $dailybuddy_mo_file,
                                'po_exists'   => file_exists($dailybuddy_po_file),
                                'mo_exists'   => file_exists($dailybuddy_mo_file),
                                'po_size'     => file_exists($dailybuddy_po_file) ? filesize($dailybuddy_po_file) : 0,
                                'mo_size'     => file_exists($dailybuddy_mo_file) ? filesize($dailybuddy_mo_file) : 0,
                                'po_modified' => file_exists($dailybuddy_po_file) ? filemtime($dailybuddy_po_file) : 0,
                                'mo_modified' => file_exists($dailybuddy_mo_file) ? filemtime($dailybuddy_mo_file) : 0,
                            );
                        }
                    }
                }
            }

            // Get current WordPress locale.
            $dailybuddy_current_locale = get_locale();
            $dailybuddy_translations_count = count($dailybuddy_translations);
            ?>

            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th style="width: 250px;"><?php esc_html_e('Text Domain', 'dailybuddy'); ?></th>
                        <td><code>dailybuddy</code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Languages Directory', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html($dailybuddy_languages_dir); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Current WordPress Locale', 'dailybuddy'); ?></th>
                        <td>
                            <strong><?php echo esc_html($dailybuddy_current_locale); ?></strong>
                            <?php if (isset($dailybuddy_translations[$dailybuddy_current_locale])) : ?>
                                <span style="color: #46b450;">✓ <?php esc_html_e('Translation available', 'dailybuddy'); ?></span>
                            <?php else : ?>
                                <span style="color: #dba617;">○ <?php esc_html_e('No translation available', 'dailybuddy'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Available Translations', 'dailybuddy'); ?></th>
                        <td>
                            <?php if (! empty($dailybuddy_translations)) : ?>
                                <strong><?php echo (int) $dailybuddy_translations_count; ?></strong>
                                <?php
                                echo esc_html(
                                    _n(
                                        'translation',
                                        'translations',
                                        $dailybuddy_translations_count,
                                        'dailybuddy'
                                    )
                                );
                                ?>
                            <?php else : ?>
                                <span style="color: #dba617;"><?php esc_html_e('No translations found', 'dailybuddy'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if (! empty($dailybuddy_translations)) : ?>

                <hr style="margin: 30px 0;" />

                <h2><?php esc_html_e('Translation Files', 'dailybuddy'); ?></h2>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Locale', 'dailybuddy'); ?></th>
                            <th><?php esc_html_e('PO File', 'dailybuddy'); ?></th>
                            <th><?php esc_html_e('MO File', 'dailybuddy'); ?></th>
                            <th><?php esc_html_e('Empty Translations', 'dailybuddy'); ?></th>
                            <th><?php esc_html_e('Last Modified', 'dailybuddy'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailybuddy_translations as $locale => $dailybuddy_data) : ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($locale); ?></strong>
                                    <?php if ($locale === $dailybuddy_current_locale) : ?>
                                        <span style="color: #46b450; font-size: 11px;">(<?php esc_html_e('active', 'dailybuddy'); ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($dailybuddy_data['po_exists']) : ?>
                                        <span style="color: #46b450;">✓</span>
                                        <code style="font-size: 11px;"><?php echo esc_html(basename($dailybuddy_data['po_file'])); ?></code>
                                        <span style="color: #646970; font-size: 11px;">
                                            (<?php echo esc_html(size_format($dailybuddy_data['po_size'])); ?>)
                                        </span>
                                    <?php else : ?>
                                        <span style="color: #d63638;">✗ <?php esc_html_e('Missing', 'dailybuddy'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($dailybuddy_data['mo_exists']) : ?>
                                        <span style="color: #46b450;">✓</span>
                                        <code style="font-size: 11px;"><?php echo esc_html(basename($dailybuddy_data['mo_file'])); ?></code>
                                        <span style="color: #646970; font-size: 11px;">
                                            (<?php echo esc_html(size_format($dailybuddy_data['mo_size'])); ?>)
                                        </span>
                                    <?php else : ?>
                                        <span style="color: #d63638;">✗ <?php esc_html_e('Missing', 'dailybuddy'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="empty-translations-cell" data-locale="<?php echo esc_attr($locale); ?>">
                                    <span class="spinner" style="float: none; visibility: visible; margin: 0;"></span>
                                    <span class="empty-count" style="display: none;"></span>
                                </td>
                                <td style="font-size: 11px; color: #646970;">
                                    <?php
                                    $dailybuddy_latest_modified = max($dailybuddy_data['po_modified'], $dailybuddy_data['mo_modified']);
                                    if ($dailybuddy_latest_modified > 0) {
                                        echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), $dailybuddy_latest_modified));
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 20px; padding: 15px; background: #f6f7f7; border-left: 4px solid #2271b1;">
                    <h4 style="margin-top: 0;"><?php esc_html_e('Add New Language', 'dailybuddy'); ?></h4>
                    <p class="description">
                        <?php esc_html_e('Create a new translation file based on the POT template. Enter a locale code (e.g., de_DE, fr_FR, es_ES).', 'dailybuddy'); ?>
                    </p>

                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">
                        <input type="text" id="new-locale-input" placeholder="<?php echo esc_attr__('e.g., de_DE, fr_FR, es_ES', 'dailybuddy'); ?>"
                            style="width: 200px;" pattern="[a-z]{2}_[A-Z]{2}" maxlength="5">

                        <button type="button" id="dailybuddy-create-language" class="button button-secondary">
                            <span class="dashicons dashicons-plus" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Create Language File', 'dailybuddy'); ?>
                        </button>

                        <span id="create-language-status" style="display: none;">
                            <span class="spinner" style="float: none; visibility: visible; margin: 0;"></span>
                            <span class="status-text"></span>
                        </span>
                    </div>

                    <p class="description" style="margin-top: 10px;">
                        <strong><?php esc_html_e('Common locale codes:', 'dailybuddy'); ?></strong>
                        de_DE (German), fr_FR (French), es_ES (Spanish), it_IT (Italian), nl_NL (Dutch),
                        pt_BR (Portuguese Brazil), pl_PL (Polish), ru_RU (Russian), ja (Japanese), zh_CN (Chinese)
                    </p>
                </div>

            <?php endif; ?>

            <hr style="margin: 30px 0;" />

            <!-- Translation String Scanner & Manager -->
            <h2><?php esc_html_e('Translation String Scanner', 'dailybuddy'); ?></h2>

            <p>
                <?php esc_html_e('Scan your plugin files for translatable strings and compare them with your existing translation files.', 'dailybuddy'); ?>
            </p>

            <div id="dailybuddy-scanner-controls" style="margin: 20px 0;">
                <button type="button" id="dailybuddy-scan-translations" class="button button-primary">
                    <span class="dashicons dashicons-search" style="margin-top: 3px;"></span>
                    <?php esc_html_e('Scan for Missing Translations', 'dailybuddy'); ?>
                </button>

                <span id="dailybuddy-scanner-status" style="margin-left: 15px; display: none;">
                    <span class="spinner" style="float: none; visibility: visible; margin: 0;"></span>
                    <span class="status-text"><?php esc_html_e('Scanning...', 'dailybuddy'); ?></span>
                </span>
            </div>

            <!-- Scanner Results -->
            <div id="dailybuddy-scanner-results" style="display: none; margin-top: 30px;">

                <!-- Summary -->
                <div id="dailybuddy-scanner-summary" class="notice notice-info inline" style="padding: 15px;">
                    <p style="margin: 0; font-size: 14px;">
                        <strong><?php esc_html_e('Scan Complete', 'dailybuddy'); ?></strong><br>
                        <span id="summary-text"></span>
                    </p>
                </div>

                <!-- Missing Strings Table -->
                <div id="dailybuddy-missing-strings" style="margin-top: 20px;">
                    <h3><?php esc_html_e('Missing Translations', 'dailybuddy'); ?></h3>
                    <div id="missing-strings-content"></div>
                </div>

                <!-- Action Buttons -->
                <div id="dailybuddy-scanner-actions" style="margin-top: 20px; padding: 15px; background: #f6f7f7; border-left: 4px solid #91CE00;">
                    <h4 style="margin-top: 0;"><?php esc_html_e('Add Missing Strings', 'dailybuddy'); ?></h4>
                    <p class="description">
                        <?php esc_html_e('Select which translation files you want to update with the missing strings.', 'dailybuddy'); ?>
                    </p>

                    <div id="locale-buttons" style="margin-top: 15px;">
                        <!-- Dynamisch generiert via JS -->
                    </div>

                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dcdcde;">
                        <button type="button" id="dailybuddy-add-to-all" class="button button-primary" style="margin-right: 10px;">
                            <span class="dashicons dashicons-yes-alt" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Add to All Translation Files', 'dailybuddy'); ?>
                        </button>

                        <button type="button" id="dailybuddy-clear-results" class="button button-secondary">
                            <?php esc_html_e('Clear Results', 'dailybuddy'); ?>
                        </button>
                    </div>
                </div>

            </div>

            <hr style="margin: 30px 0;" />

            <h2><?php esc_html_e('Translation Tools', 'dailybuddy'); ?></h2>

            <p>
                <?php esc_html_e('You can create or update translations for DailyBuddy using tools like Poedit or Loco Translate.', 'dailybuddy'); ?>
            </p>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('POT Template File', 'dailybuddy'); ?></th>
                        <td>
                            <?php
                            $dailybuddy_pot_file = $dailybuddy_languages_dir . 'dailybuddy.pot';
                            if (file_exists($dailybuddy_pot_file)) :
                            ?>
                                <span style="color: #46b450;">✓</span>
                                <code><?php echo esc_html(basename($dailybuddy_pot_file)); ?></code>
                                <span style="color: #646970; font-size: 12px;">
                                    (<?php echo esc_html(size_format(filesize($dailybuddy_pot_file))); ?>)
                                </span>
                                <br>
                                <p class="description">
                                    <?php esc_html_e('Use this file as a template to create new translations.', 'dailybuddy'); ?>
                                </p>
                            <?php else : ?>
                                <span style="color: #d63638;">✗ <?php esc_html_e('POT file not found', 'dailybuddy'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Recommended Tools', 'dailybuddy'); ?></th>
                        <td>
                            <ul style="list-style: disc; margin-left: 20px;">
                                <li>
                                    <strong>Poedit</strong> –
                                    <a href="https://poedit.net/" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e('Download Poedit', 'dailybuddy'); ?>
                                    </a>
                                </li>
                                <li>
                                    <strong>Loco Translate</strong> –
                                    <?php
                                    if (is_plugin_active('loco-translate/loco.php')) {
                                        echo '<span style="color: #46b450;">✓ ' . esc_html__('Installed', 'dailybuddy') . '</span> – ';
                                    }
                                    ?>
                                    <a href="https://wordpress.org/plugins/loco-translate/" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e('View Plugin', 'dailybuddy'); ?>
                                    </a>
                                </li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

    </div>

</div>
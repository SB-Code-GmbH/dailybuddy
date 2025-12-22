<?php

/**
 * General Settings Page with Tabs
 */

if (! defined('ABSPATH')) {
    exit;
}

if (defined('DAILYBUDDY_URL') && defined('DAILYBUDDY_VERSION')) {
    wp_enqueue_style(
        'dailybuddy-uc',
        DAILYBUDDY_URL . 'assets/css/modul-settings.css',
        array(),
        DAILYBUDDY_VERSION
    );
}

// Read-only GET parameter: switches visible tab.
// Safe without nonce because no data is being changed.
// phpcs:disable WordPress.Security.NonceVerification.Missing
$dailybuddy_current_tab = isset($_POST['current_tab'])
    ? sanitize_text_field(wp_unslash($_POST['current_tab']))
    : 'general';
// phpcs:disable WordPress.Security.NonceVerification.Missing

// Allow only defined tabs
$dailybuddy_allowed_tabs = array('general', 'about');
if (! in_array($dailybuddy_current_tab, $dailybuddy_allowed_tabs, true)) {
    $dailybuddy_current_tab = 'general';
}

// Load module list if not already available
if (! isset($dailybuddy_modules) || ! is_array($dailybuddy_modules)) {
    if (class_exists('Dailybuddy_Settings')) {
        $dailybuddy_modules = Dailybuddy_Settings::get_modules();
    } else {
        $dailybuddy_modules = array();
    }
}

?>

<div class="wrap">

    <!-- Breadcrumbs -->
    <div class="dailybuddy-breadcrumbs">
        <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy')); ?>">
            <?php esc_html_e('DailyBuddy', 'dailybuddy'); ?>
        </a>
        <span class="separator">›</span>
        <strong><?php esc_html_e('General Settings', 'dailybuddy'); ?></strong>
    </div>

    <h1 class="dailybuddy-settings-header">
        <span class="dailybuddy-settings-icon dashicons dashicons-admin-generic"></span>
        <span class="dailybuddy-settings-title">
            <?php esc_html_e('General Settings', 'dailybuddy'); ?>
        </span>
    </h1>

    <div class="dailybuddy-settings-wrap">

        <form method="post" action="">
            <?php wp_nonce_field('dailybuddy_general_settings'); ?>

            <input type="hidden" name="current_tab" id="current_tab"
                value="<?php echo esc_attr($dailybuddy_current_tab); ?>">

            <div class="dailybuddy-settings-container">

                <!-- Tabs -->
                <div class="dailybuddy-uc-tabs">

                    <button type="button"
                        class="dailybuddy-uc-tab <?php echo ('general' === $dailybuddy_current_tab) ? 'active' : ''; ?>"
                        data-tab="general">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php esc_html_e('General Settings', 'dailybuddy'); ?>
                    </button>

                    <button type="button"
                        class="dailybuddy-uc-tab <?php echo ('about' === $dailybuddy_current_tab) ? 'active' : ''; ?>"
                        data-tab="about">
                        <span class="dashicons dashicons-info-outline"></span>
                        <?php esc_html_e('About DailyBuddy', 'dailybuddy'); ?>
                    </button>

                </div>

                <!-- Tab: General -->
                <div class="dailybuddy-uc-tab-content <?php echo ('general' === $dailybuddy_current_tab) ? 'active' : ''; ?>"
                    data-tab="general">

                    <h2><?php esc_html_e('General Settings', 'dailybuddy'); ?></h2>

                    <p class="description">
                        <?php esc_html_e(
                            'Overview of all modules that have their own settings screen.',
                            'dailybuddy'
                        ); ?>
                    </p>

                    <?php
                    $dailybuddy_modules_with_settings = array();

                    if (class_exists('Dailybuddy_Settings')) {
                        $dailybuddy_modules_state = Dailybuddy_Settings::get_modules();

                        if (is_array($dailybuddy_modules_state)) {
                            foreach ($dailybuddy_modules_state as $dailybuddy_module_id => $dailybuddy_is_active) {

                                $dailybuddy_config_file = DAILYBUDDY_PATH . 'modules/' . $dailybuddy_module_id . '/config.php';

                                if (! file_exists($dailybuddy_config_file)) {
                                    continue;
                                }

                                $dailybuddy_config = include $dailybuddy_config_file;

                                if (! is_array($dailybuddy_config)) {
                                    continue;
                                }

                                if (empty($dailybuddy_config['has_settings'])) {
                                    continue;
                                }

                                $dailybuddy_parts    = explode('/', $dailybuddy_module_id);
                                $dailybuddy_category = $dailybuddy_parts[0];

                                $dailybuddy_modules_with_settings[] = array(
                                    'id'       => $dailybuddy_module_id,
                                    'name'     => isset($dailybuddy_config['name']) ? $dailybuddy_config['name'] : $dailybuddy_module_id,
                                    'category' => $dailybuddy_category,
                                );
                            }
                        }
                    }
                    ?>

                    <?php if (! empty($dailybuddy_modules_with_settings)) : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Module', 'dailybuddy'); ?></th>
                                    <th><?php esc_html_e('Category', 'dailybuddy'); ?></th>
                                    <th><?php esc_html_e('Settings', 'dailybuddy'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dailybuddy_modules_with_settings as $dailybuddy_mod) : ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($dailybuddy_mod['name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo esc_html(dailybuddy_format_category_name($dailybuddy_mod['category'])); ?>
                                        </td>
                                        <td>
                                            <a href="<?php
                                                        echo esc_url(
                                                            admin_url(
                                                                'admin.php?page=dailybuddy&view=settings&module=' . urlencode($dailybuddy_mod['id'])
                                                            )
                                                        );
                                                        ?>"
                                                class="button button-primary button-large">
                                                <?php esc_html_e('Open settings', 'dailybuddy'); ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>
                            <?php esc_html_e(
                                'Currently, no modules provide their own settings page.',
                                'dailybuddy'
                            ); ?>
                        </p>
                    <?php endif; ?>

                </div>

                <!-- Tab: About -->
                <div class="dailybuddy-uc-tab-content <?php echo ('about' === $dailybuddy_current_tab) ? 'active' : ''; ?>"
                    data-tab="about">

                    <h2><?php esc_html_e('About DailyBuddy', 'dailybuddy'); ?></h2>

                    <p>
                        <?php esc_html_e(
                            'DailyBuddy is a modular collection of helpful enhancements for your WordPress site.',
                            'dailybuddy'
                        ); ?>
                    </p>

                    <ul style="list-style: disc; margin-left: 20px;">
                        <li><?php esc_html_e('Enable handy WordPress utilities such as maintenance mode and content tools.', 'dailybuddy'); ?></li>
                        <li><?php esc_html_e('Add custom widgets and extensions to your dashboard or page builders.', 'dailybuddy'); ?></li>
                        <li><?php esc_html_e('Keep everything organized in one central modules overview.', 'dailybuddy'); ?></li>
                    </ul>

                    <p>
                        <?php esc_html_e(
                            'You can manage all individual modules, activate or deactivate them, and access their settings from the main DailyBuddy screen.',
                            'dailybuddy'
                        ); ?>
                    </p>

                    <hr />

                    <h2><?php esc_html_e('Feedback & Support', 'dailybuddy'); ?></h2>

                    <p>
                        <?php esc_html_e(
                            'If you have questions, feature requests, or found a bug, we would love to hear from you.',
                            'dailybuddy'
                        ); ?>
                    </p>

                    <?php $dailybuddy_support_url = '#'; ?>

                    <p>
                        <a href="<?php echo esc_url($dailybuddy_support_url); ?>" target="_blank" rel="noopener noreferrer"
                            class="button button-secondary">
                            <?php esc_html_e('Open support & feedback page', 'dailybuddy'); ?>
                        </a>
                    </p>

                    <p class="description">
                        <?php esc_html_e(
                            'The support link will point to the official WordPress.org plugin page once it is available.',
                            'dailybuddy'
                        ); ?>
                    </p>

                </div>

            </div>

        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        $('.dailybuddy-uc-tab').on('click', function() {
            var tab = $(this).data('tab');

            $('#current_tab').val(tab);

            $('.dailybuddy-uc-tab').removeClass('active');
            $(this).addClass('active');

            $('.dailybuddy-uc-tab-content').removeClass('active');
            $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');
        });
    });
</script>
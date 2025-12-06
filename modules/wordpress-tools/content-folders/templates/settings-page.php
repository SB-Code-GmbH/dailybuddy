<?php

/**
 * Template: Content Folders Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$dailybuddy_settings = get_option('dailybuddy_content_folders_settings', array(
    'enable_posts'  => true,
    'enable_pages'  => true,
    'enable_media'  => true,
    // Design settings
    'primary_color' => '#91CE00',
    'accent_color'  => '#478d63',
    'show_counts'   => true,
    'show_icons'    => true,
));

// Aktiven Tab merken
$dailybuddy_current_tab = isset($_POST['current_tab'])
    ? sanitize_text_field(wp_unslash($_POST['current_tab']))
    : 'general';

// Handle form submission
if (isset($_POST['dailybuddy_save_folder_settings'])) {
    check_admin_referer('dailybuddy_folder_settings');

    $dailybuddy_new_settings = array(
        'enable_posts'  => !empty($_POST['enable_posts']),
        'enable_pages'  => !empty($_POST['enable_pages']),
        'enable_media'  => !empty($_POST['enable_media']),
        // Design settings
        'primary_color' => isset($_POST['primary_color']) ? sanitize_hex_color(wp_unslash($_POST['primary_color'])) : '#91CE00',
        'accent_color'  => isset($_POST['accent_color']) ? sanitize_hex_color(wp_unslash($_POST['accent_color'])) : '#478d63',
        'show_counts'   => !empty($_POST['show_counts']),
        'show_icons'    => !empty($_POST['show_icons']),
    );

    update_option('dailybuddy_content_folders_settings', $dailybuddy_new_settings);
    $dailybuddy_settings = $dailybuddy_new_settings;

    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved!', 'dailybuddy') . '</p></div>';
}
?>

<div class="wrap dailybuddy-content-folders-settings">
    <h1>
        <span class="dashicons dashicons-category" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('Content Folders', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Organize your posts, pages and media in folders with drag & drop.', 'dailybuddy'); ?>
    </p>

    <form method="post" action="" class="dailybuddy-settings-form">
        <?php wp_nonce_field('dailybuddy_folder_settings'); ?>
        <input type="hidden" name="current_tab" id="current_tab" value="<?php echo esc_attr($dailybuddy_current_tab); ?>">

        <!-- Tabs -->
        <div class="dailybuddy-uc-tabs">
            <button type="button"
                class="dailybuddy-uc-tab <?php echo $dailybuddy_current_tab === 'general' ? 'active' : ''; ?>"
                data-tab="general">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e('General', 'dailybuddy'); ?>
            </button>

            <button type="button"
                class="dailybuddy-uc-tab <?php echo $dailybuddy_current_tab === 'design' ? 'active' : ''; ?>"
                data-tab="design">
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e('Design', 'dailybuddy'); ?>
            </button>
        </div>

        <!-- General Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $dailybuddy_current_tab === 'general' ? 'active' : ''; ?>"
            data-tab="general">

            <h2><?php esc_html_e('Enable Folders For', 'dailybuddy'); ?></h2>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Posts', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Enable folder organization for posts', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="enable_posts" value="1"
                            <?php checked(!empty($dailybuddy_settings['enable_posts'])); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Pages', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Enable folder organization for pages', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="enable_pages" value="1"
                            <?php checked(!empty($dailybuddy_settings['enable_pages'])); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Media Library', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Enable folder organization for media library', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="enable_media" value="1"
                            <?php checked(!empty($dailybuddy_settings['enable_media'])); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Design Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $dailybuddy_current_tab === 'design' ? 'active' : ''; ?>"
            data-tab="design">

            <h2><?php esc_html_e('Design Settings', 'dailybuddy'); ?></h2>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Show Counts', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Display item count for each folder', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="show_counts" value="1"
                            <?php checked(!empty($dailybuddy_settings['show_counts'])); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Show Icons', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Display icons next to folder names', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="show_icons" value="1"
                            <?php checked(!empty($dailybuddy_settings['show_icons'])); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Primary Color', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Main color for active elements and buttons', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <div class="color-picker-group">
                        <input type="color"
                            id="primary_color"
                            name="primary_color"
                            value="<?php echo esc_attr($dailybuddy_settings['primary_color']); ?>">
                        <input type="text"
                            id="primary_color_text"
                            value="<?php echo esc_attr($dailybuddy_settings['primary_color']); ?>"
                            readonly
                            class="regular-text code color-text">
                    </div>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Accent Color', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Hover color for active elements', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <div class="color-picker-group">
                        <input type="color"
                            id="accent_color"
                            name="accent_color"
                            value="<?php echo esc_attr($dailybuddy_settings['accent_color']); ?>">
                        <input type="text"
                            id="accent_color_text"
                            value="<?php echo esc_attr($dailybuddy_settings['accent_color']); ?>"
                            readonly
                            class="regular-text code color-text">
                    </div>
                </div>
            </div>

        </div>

        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary large', 'dailybuddy_save_folder_settings'); ?>
    </form>

    <div class="info-box" style="margin-top: 30px;">
        <h3>
            <span class="dashicons dashicons-info"></span>
            <?php esc_html_e('How to Use', 'dailybuddy'); ?>
        </h3>
        <ol>
            <li><?php esc_html_e('Go to Posts, Pages, or Media Library', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Click the toggle button to show the folder sidebar', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Create folders with the "New Folder" button', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Drag items using the ☰ icon to assign them to folders', 'dailybuddy'); ?></li>
            <li><?php esc_html_e('Click on folder badges in the table to filter by folder', 'dailybuddy'); ?></li>
        </ol>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Tab switching
        $('.dailybuddy-uc-tab').on('click', function() {
            var tab = $(this).data('tab');

            $('#current_tab').val(tab);

            $('.dailybuddy-uc-tab').removeClass('active');
            $(this).addClass('active');

            $('.dailybuddy-uc-tab-content').removeClass('active');
            $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');
        });

        // Color picker text sync
        $('#primary_color').on('change input', function() {
            $('#primary_color_text').val($(this).val());
        });
        $('#accent_color').on('change input', function() {
            $('#accent_color_text').val($(this).val());
        });
    });
</script>
<?php

/**
 * Template: Under Construction Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$dailybuddy_settings = get_option('dailybuddy_under_construction_settings', array(
    // General
    'maintenance_active' => false,
    'title' => __('Website Under Construction', 'dailybuddy'),
    'message' => __('We are currently working on improvements. Please check back soon!', 'dailybuddy'),
    'show_login_button' => true,
    'auto_end_enabled' => false,
    'auto_end_datetime' => '',
    'admin_bar_notice' => true,

    // Design
    'layout' => 'centered',
    'custom_css' => '',

    // Social
    'social_enabled' => false,
    'social_facebook' => '',
    'social_twitter' => '',
    'social_instagram' => '',
    'social_linkedin' => '',
    'social_youtube' => '',
));

// Get current tab
$dailybuddy_current_tab = isset($_POST['current_tab'])
    ? sanitize_text_field(wp_unslash($_POST['current_tab']))
    : 'general';

// Handle form submission
if (isset($_POST['dailybuddy_save_uc_settings'])) {
    check_admin_referer('dailybuddy_uc_settings');

    // Helper: sichere bool-Werte (Checkboxen)
    $dailybuddy_maintenance_active   = !empty($_POST['uc_maintenance_active']);
    $dailybuddy_show_login_button    = !empty($_POST['uc_show_login']);
    $dailybuddy_auto_end_enabled     = !empty($_POST['uc_auto_end_enabled']);
    $dailybuddy_admin_bar_notice     = !empty($_POST['uc_admin_bar_notice']);
    $dailybuddy_social_enabled       = !empty($_POST['uc_social_enabled']);

    // Texte
    $dailybuddy_title   = isset($_POST['uc_title'])
        ? sanitize_text_field(wp_unslash($_POST['uc_title']))
        : '';

    $dailybuddy_message = isset($_POST['uc_message'])
        ? sanitize_textarea_field(wp_unslash($_POST['uc_message']))
        : '';

    $dailybuddy_auto_end_datetime = isset($_POST['uc_auto_end_datetime'])
        ? sanitize_text_field(wp_unslash($_POST['uc_auto_end_datetime']))
        : '';

    $dailybuddy_layout = isset($_POST['uc_layout'])
        ? sanitize_text_field(wp_unslash($_POST['uc_layout']))
        : '';

    $dailybuddy_custom_css = isset($_POST['uc_custom_css'])
        ? sanitize_textarea_field(wp_unslash($_POST['uc_custom_css']))
        : '';

    // Social URLs
    $dailybuddy_social_facebook = isset($_POST['uc_social_facebook'])
        ? esc_url_raw(wp_unslash($_POST['uc_social_facebook']))
        : '';

    $dailybuddy_social_twitter = isset($_POST['uc_social_twitter'])
        ? esc_url_raw(wp_unslash($_POST['uc_social_twitter']))
        : '';

    $dailybuddy_social_instagram = isset($_POST['uc_social_instagram'])
        ? esc_url_raw(wp_unslash($_POST['uc_social_instagram']))
        : '';

    $dailybuddy_social_linkedin = isset($_POST['uc_social_linkedin'])
        ? esc_url_raw(wp_unslash($_POST['uc_social_linkedin']))
        : '';

    $dailybuddy_social_youtube = isset($_POST['uc_social_youtube'])
        ? esc_url_raw(wp_unslash($_POST['uc_social_youtube']))
        : '';

    $dailybuddy_new_settings = array(
        // General
        'maintenance_active' => $dailybuddy_maintenance_active,
        'title'              => $dailybuddy_title,
        'message'            => $dailybuddy_message,
        'show_login_button'  => $dailybuddy_show_login_button,
        'auto_end_enabled'   => $dailybuddy_auto_end_enabled,
        'auto_end_datetime'  => $dailybuddy_auto_end_datetime,
        'admin_bar_notice'   => $dailybuddy_admin_bar_notice,

        // Design
        'layout'     => $dailybuddy_layout,
        'custom_css' => $dailybuddy_custom_css,

        // Social
        'social_enabled'  => $dailybuddy_social_enabled,
        'social_facebook' => $dailybuddy_social_facebook,
        'social_twitter'  => $dailybuddy_social_twitter,
        'social_instagram' => $dailybuddy_social_instagram,
        'social_linkedin' => $dailybuddy_social_linkedin,
        'social_youtube'  => $dailybuddy_social_youtube,
    );

    update_option('dailybuddy_under_construction_settings', $dailybuddy_new_settings);
    $dailybuddy_settings = $dailybuddy_new_settings;

    echo '<div class="notice notice-success is-dismissible"><p>' .
        esc_html__('Settings saved!', 'dailybuddy') .
        '</p></div>';
}

// Get available layouts
$dailybuddy_available_layouts = Dailybuddy_Under_Construction::get_available_layouts();
?>

<div class="wrap dailybuddy-under-construction-settings">
    <h1>
        <span class="dashicons dashicons-hammer" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('Under Construction', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Show a maintenance page to visitors while you work on your site.', 'dailybuddy'); ?>
    </p>

    <form method="post" action="" class="dailybuddy-settings-form">
        <?php wp_nonce_field('dailybuddy_uc_settings'); ?>
        <input type="hidden" name="current_tab" id="current_tab" value="<?php echo esc_attr($dailybuddy_current_tab); ?>">

        <!-- Tabs -->
        <div class="dailybuddy-uc-tabs">
            <button type="button" class="dailybuddy-uc-tab <?php echo $dailybuddy_current_tab === 'general' ? 'active' : ''; ?>" data-tab="general">
                <i class="fas fa-cog"></i> <?php esc_html_e('General', 'dailybuddy'); ?>
            </button>
            <button type="button" class="dailybuddy-uc-tab <?php echo $dailybuddy_current_tab === 'design' ? 'active' : ''; ?>" data-tab="design">
                <i class="fas fa-palette"></i> <?php esc_html_e('Design', 'dailybuddy'); ?>
            </button>
            <button type="button" class="dailybuddy-uc-tab <?php echo $dailybuddy_current_tab === 'social' ? 'active' : ''; ?>" data-tab="social">
                <i class="fas fa-share-alt"></i> <?php esc_html_e('Social Media', 'dailybuddy'); ?>
            </button>
        </div>

        <!-- General Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $dailybuddy_current_tab === 'general' ? 'active' : ''; ?>" data-tab="general">

            <!-- Maintenance Mode Toggle -->
            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Wartungsmodus', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Enable or disable the under construction page', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_maintenance_active" value="1" <?php checked($dailybuddy_settings['maintenance_active'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_title"><?php esc_html_e('Seitentitel', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="uc_title" name="uc_title"
                                value="<?php echo esc_attr($dailybuddy_settings['title']); ?>"
                                class="regular-text">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_message"><?php esc_html_e('Nachricht', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <textarea id="uc_message" name="uc_message" rows="4"
                                class="large-text"><?php echo esc_textarea($dailybuddy_settings['message']); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Show Login Button', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show admin login link in bottom right corner', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_show_login" value="1" <?php checked($dailybuddy_settings['show_login_button'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Admin Bar Notice', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show warning in WordPress admin bar when mode is active', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_admin_bar_notice" value="1" <?php checked($dailybuddy_settings['admin_bar_notice'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Auto-End Mode', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Automatically disable maintenance mode at specific date/time', 'dailybuddy'); ?></p>
                    <input type="datetime-local" name="uc_auto_end_datetime"
                        value="<?php echo esc_attr($dailybuddy_settings['auto_end_datetime']); ?>"
                        class="regular-text" style="margin-top: 10px;">
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_auto_end_enabled" value="1" <?php checked($dailybuddy_settings['auto_end_enabled'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

        </div>

        <!-- Design Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $dailybuddy_current_tab === 'design' ? 'active' : ''; ?>" data-tab="design">

            <h3><?php esc_html_e('Layout', 'dailybuddy'); ?></h3>
            <p class="description"><?php esc_html_e('Select a layout style for your maintenance page', 'dailybuddy'); ?></p>

            <div class="layout-preview-grid">
                <?php foreach ($dailybuddy_available_layouts as $dailybuddy_layout) : ?>
                    <label class="layout-preview <?php echo $dailybuddy_settings['layout'] === $dailybuddy_layout['id'] ? 'selected' : ''; ?>">
                        <input type="radio" name="uc_layout" value="<?php echo esc_attr($dailybuddy_layout['id']); ?>"
                            <?php checked($dailybuddy_settings['layout'], $dailybuddy_layout['id']); ?>>
                        <div class="layout-preview-inner">
                            <?php
                            /**
                             * Output layout preview HTML
                             * 
                             * get_layout_preview_html() returns self-generated HTML with
                             * proper escaping (esc_url, esc_attr). Using wp_kses_post()
                             * to allow safe HTML tags while filtering dangerous content.
                             */
                            echo wp_kses_post(Dailybuddy_Under_Construction::get_layout_preview_html($dailybuddy_layout['id']));
                            ?>
                        </div>
                        <div class="layout-preview-label"><?php echo esc_html($dailybuddy_layout['name']); ?></div>
                    </label>
                <?php endforeach; ?>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_custom_css"><?php esc_html_e('Custom CSS', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <textarea id="uc_custom_css" name="uc_custom_css" rows="10"
                                class="large-text code"><?php echo esc_textarea($dailybuddy_settings['custom_css']); ?></textarea>
                            <p class="description"><?php esc_html_e('Add custom CSS to further customize your maintenance page', 'dailybuddy'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <!-- Social Tab -->
        <div class="dailybuddy-uc-tab-content <?php echo $dailybuddy_current_tab === 'social' ? 'active' : ''; ?>" data-tab="social">

            <div class="dailybuddy-uc-setting-row">
                <div class="dailybuddy-uc-setting-info">
                    <h4><?php esc_html_e('Enable Social Links', 'dailybuddy'); ?></h4>
                    <p><?php esc_html_e('Show social media links on maintenance page', 'dailybuddy'); ?></p>
                </div>
                <div class="dailybuddy-uc-setting-control">
                    <label class="dailybuddy-uc-switch">
                        <input type="checkbox" name="uc_social_enabled" value="1" <?php checked($dailybuddy_settings['social_enabled'], true); ?>>
                        <span class="dailybuddy-uc-slider"></span>
                    </label>
                </div>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="uc_social_facebook">
                                <i class="fab fa-facebook dailybuddy-uc-social-icon"></i> Facebook
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_facebook" name="uc_social_facebook"
                                value="<?php echo esc_attr($dailybuddy_settings['social_facebook']); ?>"
                                class="regular-text" placeholder="https://facebook.com/yourpage">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_twitter">
                                <i class="fab fa-twitter dailybuddy-uc-social-icon"></i> Twitter/X
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_twitter" name="uc_social_twitter"
                                value="<?php echo esc_attr($dailybuddy_settings['social_twitter']); ?>"
                                class="regular-text" placeholder="https://twitter.com/yourhandle">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_instagram">
                                <i class="fab fa-instagram dailybuddy-uc-social-icon"></i> Instagram
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_instagram" name="uc_social_instagram"
                                value="<?php echo esc_attr($dailybuddy_settings['social_instagram']); ?>"
                                class="regular-text" placeholder="https://instagram.com/yourhandle">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_linkedin">
                                <i class="fab fa-linkedin dailybuddy-uc-social-icon"></i> LinkedIn
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_linkedin" name="uc_social_linkedin"
                                value="<?php echo esc_attr($dailybuddy_settings['social_linkedin']); ?>"
                                class="regular-text" placeholder="https://linkedin.com/company/yourcompany">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="uc_social_youtube">
                                <i class="fab fa-youtube dailybuddy-uc-social-icon"></i> YouTube
                            </label>
                        </th>
                        <td>
                            <input type="url" id="uc_social_youtube" name="uc_social_youtube"
                                value="<?php echo esc_attr($dailybuddy_settings['social_youtube']); ?>"
                                class="regular-text" placeholder="https://youtube.com/@yourchannel">
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary large', 'dailybuddy_save_uc_settings'); ?>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Tab Switching
        $('.dailybuddy-uc-tab').on('click', function() {
            var tab = $(this).data('tab');

            $('#current_tab').val(tab);

            $('.dailybuddy-uc-tab').removeClass('active');
            $(this).addClass('active');

            $('.dailybuddy-uc-tab-content').removeClass('active');
            $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');

            // 🔹 WICHTIG: CodeMirror refreshen, wenn Design-Tab sichtbar wird
            if (tab === 'design' && window.dailybuddyUcCssEditor && window.dailybuddyUcCssEditor.codemirror) {
                setTimeout(function() {
                    window.dailybuddyUcCssEditor.codemirror.refresh();
                }, 20);
            }
        });

        // Falls Seite bereits mit aktivem Design-Tab geladen wird (z.B. nach Save)
        if ($('#current_tab').val() === 'design' && window.dailybuddyUcCssEditor && window.dailybuddyUcCssEditor.codemirror) {
            setTimeout(function() {
                window.dailybuddyUcCssEditor.codemirror.refresh();
            }, 20);
        }

        // Layout Selection
        $('.layout-preview').on('click', function() {
            $('.layout-preview').removeClass('selected');
            $(this).addClass('selected');
        });

        // Update admin bar on page load if settings were saved
        <?php if (isset($_POST['dailybuddy_save_uc_settings'])) : ?>
            if (window.parent && window.parent.jQuery) {
                var isActive = <?php echo $dailybuddy_settings['maintenance_active'] ? 'true' : 'false'; ?>;
                var $statusText = window.parent.jQuery('#dailybuddy-uc-status');
                var $toggle = window.parent.jQuery('#dailybuddy-uc-toggle-switch');

                if (isActive) {
                    $statusText.css('color', '#00a32a').html('✓ <?php echo esc_js(__('Maintenance Mode Active', 'dailybuddy')); ?>');
                    $toggle.prop('checked', true);
                } else {
                    $statusText.css('color', '#646970').html('○ <?php echo esc_js(__('Maintenance Mode Inactive', 'dailybuddy')); ?>');
                    $toggle.prop('checked', false);
                }
            }
        <?php endif; ?>
    });
</script>
<?php

/**
 * Template: TranslatePress Tools Settings
 */

if (! defined('ABSPATH')) {
    exit;
}

$dailybuddy_trp_active    = is_plugin_active('translatepress-multilingual/index.php');
$dailybuddy_lang_count    = count($trp_languages);
$dailybuddy_action_types  = array(
    'popup'    => __('Popup Window', 'dailybuddy'),
    'bar'      => __('Hello Bar', 'dailybuddy'),
    'redirect' => __('Direct Redirect', 'dailybuddy'),
);
?>

<div class="wrap dailybuddy-tp-tools-settings">
    <h1>
        <span class="dashicons dashicons-translation" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('TranslatePress Tools', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Automatic language detection for TranslatePress.', 'dailybuddy'); ?>
    </p>

    <?php if (! $dailybuddy_trp_active) : ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e('TranslatePress is not active!', 'dailybuddy'); ?></strong>
                <?php esc_html_e('Please install and activate TranslatePress to use this module.', 'dailybuddy'); ?>
            </p>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <?php if ($dailybuddy_lang_count < 2) : ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('Not enough languages configured.', 'dailybuddy'); ?></strong>
                <?php esc_html_e('You need at least 2 languages in TranslatePress for auto-detection to work.', 'dailybuddy'); ?>
            </p>
        </div>
        <?php return; ?>
    <?php endif; ?>

    <form method="post" action="" class="dailybuddy-settings-form" id="dailybuddy-tp-form">
        <?php wp_nonce_field('dailybuddy_tp_tools_settings'); ?>

            <!-- Status Card -->
            <div class="status-card">
                <div class="status-item">
                    <span class="status-number"><?php echo esc_html($dailybuddy_lang_count); ?></span>
                    <span class="status-label"><?php esc_html_e('Languages', 'dailybuddy'); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-icon <?php echo ! empty($settings['enabled']) ? 'active' : 'inactive'; ?>">
                        <?php echo ! empty($settings['enabled']) ? '&#10003;' : '&#10007;'; ?>
                    </span>
                    <span class="status-label"><?php esc_html_e('Detection', 'dailybuddy'); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-icon active">&#10003;</span>
                    <span class="status-label"><?php echo esc_html($dailybuddy_action_types[$settings['action_type']]); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-icon active">&#10003;</span>
                    <span class="status-label"><?php esc_html_e('GDPR-Safe', 'dailybuddy'); ?></span>
                </div>
            </div>

        <!-- General Settings -->
        <div class="settings-section">
            <h2><?php esc_html_e('General Settings', 'dailybuddy'); ?></h2>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php esc_html_e('Enable Auto-Detection', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <label class="method-option">
                                <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked(! empty($settings['enabled'])); ?>>
                                <div class="method-info">
                                    <strong><?php esc_html_e('Detect browser language automatically', 'dailybuddy'); ?></strong>
                                    <p class="description">
                                        <?php esc_html_e('When enabled, visitors will be prompted or redirected based on their browser language.', 'dailybuddy'); ?>
                                    </p>
                                </div>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="action_type"><?php esc_html_e('Action Type', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <?php foreach ($dailybuddy_action_types as $dailybuddy_type_key => $dailybuddy_type_label) : ?>
                                <label style="display: block; margin-bottom: 10px;">
                                    <input type="radio" name="action_type" value="<?php echo esc_attr($dailybuddy_type_key); ?>" <?php checked($settings['action_type'], $dailybuddy_type_key); ?>>
                                    <strong><?php echo esc_html($dailybuddy_type_label); ?></strong>
                                    <?php if ('popup' === $dailybuddy_type_key) : ?>
                                        <span class="badge badge-green"><?php esc_html_e('Recommended', 'dailybuddy'); ?></span>
                                        <p class="description" style="margin-left: 24px;">
                                            <?php esc_html_e('Shows a small popup asking the visitor if they want to switch language.', 'dailybuddy'); ?>
                                        </p>
                                    <?php elseif ('bar' === $dailybuddy_type_key) : ?>
                                        <p class="description" style="margin-left: 24px;">
                                            <?php esc_html_e('Shows a notification bar at the top or bottom of the page.', 'dailybuddy'); ?>
                                        </p>
                                    <?php elseif ('redirect' === $dailybuddy_type_key) : ?>
                                        <span class="badge badge-red"><?php esc_html_e('SEO Risk', 'dailybuddy'); ?></span>
                                        <p class="description" style="margin-left: 24px;">
                                            <?php esc_html_e('Redirects visitors directly without asking. May cause issues with search engine indexing.', 'dailybuddy'); ?>
                                        </p>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Storage', 'dailybuddy'); ?>
                        </th>
                        <td>
                            <p class="description">
                                <span class="badge badge-green"><?php esc_html_e('GDPR-Safe', 'dailybuddy'); ?></span>
                                <?php esc_html_e('Uses sessionStorage (no cookies). The visitor\'s choice is remembered for the current browser session. A new session will show the prompt again.', 'dailybuddy'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Hello Bar Settings -->
        <div class="settings-section" id="bar-settings-section">
            <h2><?php esc_html_e('Hello Bar Settings', 'dailybuddy'); ?></h2>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="bar_position"><?php esc_html_e('Bar Position', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <select id="bar_position" name="bar_position">
                                <option value="top" <?php selected($settings['bar_position'], 'top'); ?>><?php esc_html_e('Top', 'dailybuddy'); ?></option>
                                <option value="bottom" <?php selected($settings['bar_position'], 'bottom'); ?>><?php esc_html_e('Bottom', 'dailybuddy'); ?></option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Appearance -->
        <div class="settings-section">
            <h2><?php esc_html_e('Appearance', 'dailybuddy'); ?></h2>
            <p class="description">
                <?php esc_html_e('Customize the look of the popup and hello bar.', 'dailybuddy'); ?>
            </p>

            <h3 style="margin-top: 20px;"><?php esc_html_e('Popup', 'dailybuddy'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="popup_bg_color"><?php esc_html_e('Background', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="popup_bg_color" name="popup_bg_color" value="<?php echo esc_attr($settings['popup_bg_color']); ?>" class="db-color-picker" data-default-color="#ffffff"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_text_color"><?php esc_html_e('Text Color', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="popup_text_color" name="popup_text_color" value="<?php echo esc_attr($settings['popup_text_color']); ?>" class="db-color-picker" data-default-color="#333333"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_border_radius"><?php esc_html_e('Border Radius (px)', 'dailybuddy'); ?></label></th>
                        <td><input type="number" id="popup_border_radius" name="popup_border_radius" value="<?php echo esc_attr(absint($settings['popup_border_radius'])); ?>" min="0" max="50" class="small-text"> px</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_overlay_color"><?php esc_html_e('Overlay Color', 'dailybuddy'); ?></label></th>
                        <td>
                            <input type="text" id="popup_overlay_color" name="popup_overlay_color" value="<?php echo esc_attr($settings['popup_overlay_color']); ?>" class="db-color-picker" data-default-color="#000000">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="popup_overlay_opacity"><?php esc_html_e('Overlay Opacity (%)', 'dailybuddy'); ?></label></th>
                        <td>
                            <input type="range" id="popup_overlay_opacity" name="popup_overlay_opacity" value="<?php echo esc_attr(absint($settings['popup_overlay_opacity'])); ?>" min="0" max="100" step="5" style="vertical-align: middle;">
                            <span id="opacity-value"><?php echo esc_html(absint($settings['popup_overlay_opacity'])); ?>%</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e('Hello Bar', 'dailybuddy'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="bar_bg_color"><?php esc_html_e('Background', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="bar_bg_color" name="bar_bg_color" value="<?php echo esc_attr($settings['bar_bg_color']); ?>" class="db-color-picker" data-default-color="#2271b1"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bar_text_color"><?php esc_html_e('Text Color', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="bar_text_color" name="bar_text_color" value="<?php echo esc_attr($settings['bar_text_color']); ?>" class="db-color-picker" data-default-color="#ffffff"></td>
                    </tr>
                </tbody>
            </table>

            <h3><?php esc_html_e('Button', 'dailybuddy'); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="btn_bg_color"><?php esc_html_e('Background', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="btn_bg_color" name="btn_bg_color" value="<?php echo esc_attr($settings['btn_bg_color']); ?>" class="db-color-picker" data-default-color="#2271b1"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="btn_text_color"><?php esc_html_e('Text Color', 'dailybuddy'); ?></label></th>
                        <td><input type="text" id="btn_text_color" name="btn_text_color" value="<?php echo esc_attr($settings['btn_text_color']); ?>" class="db-color-picker" data-default-color="#ffffff"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Text Customization -->
        <div class="settings-section">
            <h2><?php esc_html_e('Text Customization', 'dailybuddy'); ?></h2>
            <p class="description">
                <?php esc_html_e('Customize the texts shown to visitors. Use {language} as a placeholder for the detected language name. Leave empty to use defaults.', 'dailybuddy'); ?>
            </p>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="popup_text"><?php esc_html_e('Popup / Bar Text', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="popup_text" name="popup_text" value="<?php echo esc_attr($settings['popup_text']); ?>" class="large-text" placeholder="<?php echo esc_attr($default_texts['popup_text']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="button_text"><?php esc_html_e('Button Text', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="button_text" name="button_text" value="<?php echo esc_attr($settings['button_text']); ?>" class="regular-text" placeholder="<?php echo esc_attr($default_texts['button_text']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="dismiss_text"><?php esc_html_e('Dismiss Text', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="dismiss_text" name="dismiss_text" value="<?php echo esc_attr($settings['dismiss_text']); ?>" class="regular-text" placeholder="<?php echo esc_attr($default_texts['dismiss_text']); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Exclude Pages -->
        <div class="settings-section">
            <h2><?php esc_html_e('Exclude Pages', 'dailybuddy'); ?></h2>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="exclude_pages"><?php esc_html_e('Excluded URL Patterns', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <textarea id="exclude_pages" name="exclude_pages" rows="4" class="large-text" placeholder="/checkout&#10;/cart&#10;/my-account"><?php echo esc_textarea($settings['exclude_pages']); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('One URL pattern per line. Detection will be disabled on pages matching these patterns.', 'dailybuddy'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Info -->
        <div class="warning-box">
            <h3>
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e('How It Works', 'dailybuddy'); ?>
            </h3>
            <ul>
                <li><?php esc_html_e('The visitor\'s browser language is detected via JavaScript (navigator.languages).', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('It is matched against your configured TranslatePress languages.', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('If a match is found and differs from the current page language, the visitor is notified or redirected.', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('The choice is stored in sessionStorage (no cookies) and remembered for the current browser session.', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('This approach is cache-friendly because detection happens client-side.', 'dailybuddy'); ?></li>
            </ul>
        </div>


        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary large', 'dailybuddy_tp_tools_submit'); ?>
    </form>
</div>

<?php
wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');
?>
<script>
(function($) {
    $(function() {
        // Initialize color pickers.
        $('.db-color-picker').wpColorPicker();

        // Toggle bar settings section.
        var radios = document.querySelectorAll('input[name="action_type"]');
        var barSection = document.getElementById('bar-settings-section');
        function toggleBarSection() {
            var selected = document.querySelector('input[name="action_type"]:checked');
            barSection.style.display = (selected && selected.value === 'bar') ? '' : 'none';
        }
        radios.forEach(function(r) { r.addEventListener('change', toggleBarSection); });
        toggleBarSection();

        // Opacity slider value display.
        var opacitySlider = document.getElementById('popup_overlay_opacity');
        var opacityValue = document.getElementById('opacity-value');
        if (opacitySlider && opacityValue) {
            opacitySlider.addEventListener('input', function() {
                opacityValue.textContent = this.value + '%';
            });
        }
    });
})(jQuery);
</script>

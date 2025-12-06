<?php

/**
 * Template: AI Bot Blocker Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$dailybuddy_blocked_count = count($dailybuddy_settings['blocked_bots']);
$dailybuddy_robots_url = home_url('robots.txt');
?>

<div class="wrap dailybuddy-ai-bot-blocker-settings">
    <h1>
        <span class="dashicons dashicons-shield-alt" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('AI Bot Blocker', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Protect your content from being used to train AI models. Block crawlers from OpenAI, Google, Meta, and other AI companies.', 'dailybuddy'); ?>
    </p>

    <!-- Status Card -->
    <div class="status-card">
        <div class="status-item">
            <span class="status-number"><?php echo esc_html($dailybuddy_blocked_count); ?></span>
            <span class="status-label"><?php esc_html_e('Bots Blocked', 'dailybuddy'); ?></span>
        </div>
        <div class="status-item">
            <span class="status-icon <?php echo $dailybuddy_settings['use_meta_tags'] ? 'active' : 'inactive'; ?>">
                <?php echo $dailybuddy_settings['use_meta_tags'] ? '✓' : '✗'; ?>
            </span>
            <span class="status-label"><?php esc_html_e('Meta Tags', 'dailybuddy'); ?></span>
        </div>
        <div class="status-item">
            <span class="status-icon <?php echo $dailybuddy_settings['use_robots_txt'] ? 'active' : 'inactive'; ?>">
                <?php echo $dailybuddy_settings['use_robots_txt'] ? '✓' : '✗'; ?>
            </span>
            <span class="status-label"><?php esc_html_e('robots.txt', 'dailybuddy'); ?></span>
        </div>
        <div class="status-item">
            <span class="status-icon <?php echo $dailybuddy_settings['use_htaccess'] ? 'active' : 'inactive'; ?>">
                <?php echo $dailybuddy_settings['use_htaccess'] ? '✓' : '✗'; ?>
            </span>
            <span class="status-label"><?php esc_html_e('HTTP Headers', 'dailybuddy'); ?></span>
        </div>
    </div>

    <form method="post" action="" class="dailybuddy-settings-form">
        <?php wp_nonce_field('dailybuddy_ai_bot_blocker_settings'); ?>

        <!-- AI Bots Selection -->
        <div class="settings-section">
            <h2><?php esc_html_e('Select AI Bots to Block', 'dailybuddy'); ?></h2>
            <p class="description">
                <?php esc_html_e('Choose which AI crawlers should be blocked from accessing your content.', 'dailybuddy'); ?>
            </p>

            <div class="bots-grid">
                <?php foreach ($dailybuddy_ai_bots as $dailybuddy_bot_key => $dailybuddy_bot) : ?>
                    <label class="bot-card <?php echo in_array($dailybuddy_bot_key, $dailybuddy_settings['blocked_bots'], true) ? 'selected' : ''; ?>">
                        <input
                            type="checkbox"
                            name="blocked_bots[]"
                            value="<?php echo esc_attr($dailybuddy_bot_key); ?>"
                            <?php checked(in_array($dailybuddy_bot_key, $dailybuddy_settings['blocked_bots'], true)); ?>>
                        <div class="bot-info">
                            <div class="bot-header">
                                <strong><?php echo esc_html($dailybuddy_bot['name']); ?></strong>
                                <?php if ($dailybuddy_bot['respects_robots']) : ?>
                                    <span class="badge badge-green"><?php esc_html_e('Respects robots.txt', 'dailybuddy'); ?></span>
                                <?php else : ?>
                                    <span class="badge badge-red"><?php esc_html_e('May ignore', 'dailybuddy'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="bot-company"><?php echo esc_html($dailybuddy_bot['company']); ?></div>
                            <div class="bot-description"><?php echo esc_html($dailybuddy_bot['description']); ?></div>
                            <div class="bot-useragent">
                                <code><?php echo esc_html($dailybuddy_bot['user_agent']); ?></code>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="bulk-actions">
                <button type="button" class="button" id="select-all-bots">
                    <?php esc_html_e('Select All', 'dailybuddy'); ?>
                </button>
                <button type="button" class="button" id="deselect-all-bots">
                    <?php esc_html_e('Deselect All', 'dailybuddy'); ?>
                </button>
            </div>
        </div>

        <!-- Blocking Methods -->
        <div class="settings-section">
            <h2><?php esc_html_e('Blocking Methods', 'dailybuddy'); ?></h2>
            <p class="description">
                <?php esc_html_e('Choose how to block AI bots. Multiple methods provide better protection.', 'dailybuddy'); ?>
            </p>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="use_meta_tags"><?php esc_html_e('Meta Tags (Global)', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <?php
                            $dailybuddy_blocked_count = count($dailybuddy_settings['blocked_bots']);
                            $dailybuddy_total_bots = count($dailybuddy_ai_bots);
                            $dailybuddy_required_count = ceil($dailybuddy_total_bots * 0.8); // 80%
                            $dailybuddy_can_use_meta = $dailybuddy_blocked_count >= $dailybuddy_required_count;
                            ?>

                            <label class="method-option <?php echo !$dailybuddy_can_use_meta ? 'disabled' : ''; ?>">
                                <input
                                    type="checkbox"
                                    id="use_meta_tags"
                                    name="use_meta_tags"
                                    value="1"
                                    <?php checked($dailybuddy_settings['use_meta_tags']); ?>
                                    <?php disabled(!$dailybuddy_can_use_meta); ?>>
                                <div class="method-info">
                                    <strong><?php esc_html_e('Block ALL AI via meta tags (global)', 'dailybuddy'); ?></strong>
                                    <p class="description">
                                        <?php esc_html_e('Adds <meta name="robots" content="noai, noimageai"> to all pages.', 'dailybuddy'); ?>
                                    </p>

                                    <?php if ($dailybuddy_can_use_meta) : ?>
                                        <span class="badge badge-green"><?php esc_html_e('Available', 'dailybuddy'); ?></span>
                                        <span class="badge badge-blue"><?php esc_html_e('Safe', 'dailybuddy'); ?></span>
                                        <div class="meta-warning" style="margin-top: 10px; padding: 10px; background: #fcf9e8; border-left: 3px solid #dba617; border-radius: 3px;">
                                            <strong>⚠️ <?php esc_html_e('Important:', 'dailybuddy'); ?></strong>
                                            <?php esc_html_e('Meta tags block ALL AI bots globally, regardless of your selection above. Use this only if you want to block most/all AI crawlers.', 'dailybuddy'); ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="badge badge-red"><?php esc_html_e('Disabled', 'dailybuddy'); ?></span>
                                        <div class="meta-disabled-info" style="margin-top: 10px; padding: 10px; background: #fcf0f1; border-left: 3px solid #d63638; border-radius: 3px;">
                                            <strong>🚫 <?php esc_html_e('Cannot activate:', 'dailybuddy'); ?></strong>
                                            <?php
                                            echo esc_html(
                                                sprintf(
                                                    /* translators: 1: number of required blocked bots, 2: total number of bots, 3: percentage threshold */
                                                    __('Meta tags are global and block ALL AI bots. To activate this option, you must block at least %1$d out of %2$d bots (%3$d%% or more).', 'dailybuddy'),
                                                    $dailybuddy_required_count,
                                                    $dailybuddy_total_bots,
                                                    80
                                                )
                                            );
                                            ?>
                                            <br>
                                            <strong><?php esc_html_e('Currently blocking:', 'dailybuddy'); ?></strong>
                                            <?php echo esc_html($dailybuddy_blocked_count); ?>
                                            / <?php echo esc_html($dailybuddy_total_bots); ?>
                                            (<?php echo esc_html(round(($dailybuddy_blocked_count / $dailybuddy_total_bots) * 100)); ?>%)
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="use_robots_txt"><?php esc_html_e('robots.txt', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <label class="method-option">
                                <input
                                    type="checkbox"
                                    id="use_robots_txt"
                                    name="use_robots_txt"
                                    value="1"
                                    <?php checked($dailybuddy_settings['use_robots_txt']); ?>>
                                <div class="method-info">
                                    <strong><?php esc_html_e('Block bots via robots.txt', 'dailybuddy'); ?></strong>
                                    <p class="description">
                                        <?php esc_html_e('Adds User-agent rules to your robots.txt. Only works for bots that respect robots.txt.', 'dailybuddy'); ?>
                                    </p>
                                    <span class="badge badge-green"><?php esc_html_e('Recommended', 'dailybuddy'); ?></span>
                                    <a href="<?php echo esc_url($dailybuddy_robots_url); ?>" target="_blank" class="button button-small">
                                        <?php esc_html_e('View robots.txt', 'dailybuddy'); ?>
                                    </a>
                                </div>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="use_htaccess"><?php esc_html_e('HTTP Headers', 'dailybuddy'); ?></label>
                        </th>
                        <td>
                            <label class="method-option">
                                <input
                                    type="checkbox"
                                    id="use_htaccess"
                                    name="use_htaccess"
                                    value="1"
                                    <?php checked($dailybuddy_settings['use_htaccess']); ?>>
                                <div class="method-info">
                                    <strong><?php esc_html_e('Send X-Robots-Tag HTTP headers', 'dailybuddy'); ?></strong>
                                    <p class="description">
                                        <?php esc_html_e('Sends headers with every request. Works for all file types including PDFs and images.', 'dailybuddy'); ?>
                                    </p>
                                    <span class="badge badge-blue"><?php esc_html_e('Advanced', 'dailybuddy'); ?></span>
                                </div>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Legal Text Generator -->
        <div class="settings-section">
            <h2><?php esc_html_e('Legal Protection (Copy & Paste)', 'dailybuddy'); ?></h2>
            <p class="description">
                <?php esc_html_e('Add this text to your Imprint, Terms of Service, or Privacy Policy for legal protection.', 'dailybuddy'); ?>
            </p>

            <div class="legal-text-box">
                <h3><?php esc_html_e('Legal Notice Text', 'dailybuddy'); ?></h3>
                <textarea readonly id="legal-text-en" rows="5"><?php echo esc_html__(
                                                                    'The use, reproduction, or processing of content provided on this website (including text, images, videos, and other media) by automated systems, web crawlers, AI models, machine learning systems, or other forms of data extraction is prohibited without explicit written consent from the operator. This applies in particular to use for training artificial intelligence, creating datasets, or commercial exploitation.',
                                                                    'dailybuddy'
                                                                ); ?></textarea>

                <button type="button" class="button copy-legal-btn" data-target="legal-text-en">
                    <span class="dashicons dashicons-admin-page"></span>
                    <?php esc_html_e('Copy Text', 'dailybuddy'); ?>
                </button>
            </div>

            <div class="info-box">
                <p>
                    <strong><?php esc_html_e('Where to add this text:', 'dailybuddy'); ?></strong><br>
                    • <?php esc_html_e('Imprint / Impressum page', 'dailybuddy'); ?><br>
                    • <?php esc_html_e('Terms of Service / AGB', 'dailybuddy'); ?><br>
                    • <?php esc_html_e('Privacy Policy / Datenschutzerklärung', 'dailybuddy'); ?><br><br>
                    <strong><?php esc_html_e('Why this is important:', 'dailybuddy'); ?></strong><br>
                    <?php esc_html_e('This text provides legal grounds for claims under copyright law and GDPR. It makes your position clear in case of unauthorized AI training on your content.', 'dailybuddy'); ?>
                </p>
            </div>
        </div>

        <!-- Important Info -->
        <div class="warning-box">
            <h3>
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e('Important Information', 'dailybuddy'); ?>
            </h3>
            <ul>
                <li><?php esc_html_e('Blocking is NOT 100% effective - some bots may ignore these rules', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('Reputable companies like OpenAI, Google, and Anthropic respect robots.txt', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('This does NOT block regular search engines like Google Search', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('For maximum protection, use Cloudflare or similar services', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('Legal text is important for GDPR and copyright claims', 'dailybuddy'); ?></li>
            </ul>
        </div>

        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary', 'dailybuddy_ai_bot_blocker_submit'); ?>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Select all bots
        $('#select-all-bots').on('click', function() {
            $('.bot-card input[type="checkbox"]').prop('checked', true);
            $('.bot-card').addClass('selected');
        });

        // Deselect all bots
        $('#deselect-all-bots').on('click', function() {
            $('.bot-card input[type="checkbox"]').prop('checked', false);
            $('.bot-card').removeClass('selected');
        });

        // Update card style on checkbox change
        $('.bot-card input[type="checkbox"]').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.bot-card').addClass('selected');
            } else {
                $(this).closest('.bot-card').removeClass('selected');
            }
        });

        // Copy legal text
        $('.copy-legal-btn').on('click', function() {
            const target = $(this).data('target');
            const textarea = $('#' + target);

            textarea.select();
            document.execCommand('copy');

            const btn = $(this);
            const originalText = btn.html();
            btn.html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Copied!', 'dailybuddy'); ?>');
            btn.css('color', '#00a32a');

            setTimeout(function() {
                btn.html(originalText);
                btn.css('color', '');
            }, 2000);
        });
    });
</script>
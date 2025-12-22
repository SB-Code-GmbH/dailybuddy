<?php

/**
 * Template: Custom Login URL Settings
 */

if (! defined('ABSPATH')) {
    exit;
}

// Instance and current URLs for display.
$dailybuddy_instance           = new Dailybuddy_Custom_Login_URL();
$dailybuddy_current_login_url  = $dailybuddy_instance->new_login_url();
$dailybuddy_current_redirect_url = $dailybuddy_instance->new_redirect_url();
$dailybuddy_has_permalink      = get_option('permalink_structure');
?>

<div class="wrap dailybuddy-custom-login-url-settings">
    <h1>
        <span class="dashicons dashicons-lock" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('Custom Login URL', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e(
            'Hide your wp-login.php and wp-admin by changing the login URL. Works exactly like WPS Hide Login.',
            'dailybuddy'
        ); ?>
    </p>

    <form method="post" action="" class="dailybuddy-settings-form">
        <?php wp_nonce_field('dailybuddy_custom_login_url_settings'); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <!-- Login slug -->
                <tr>
                    <th scope="row">
                        <label for="login_slug"><?php esc_html_e('Login Slug', 'dailybuddy'); ?></label>
                    </th>
                    <td>
                        <?php if ($dailybuddy_has_permalink) : ?>
                            <code><?php echo esc_html(trailingslashit(home_url())); ?></code>
                            <input
                                type="text"
                                id="login_slug"
                                name="login_slug"
                                value="<?php echo esc_attr($login_slug); ?>"
                                class="regular-text"
                                required>
                            <?php if ('/' === substr($dailybuddy_has_permalink, -1)) : ?>
                                <code>/</code>
                            <?php endif; ?>
                        <?php else : ?>
                            <code><?php echo esc_html(trailingslashit(home_url())); ?>?</code>
                            <input
                                type="text"
                                id="login_slug"
                                name="login_slug"
                                value="<?php echo esc_attr($login_slug); ?>"
                                class="regular-text"
                                required>
                        <?php endif; ?>

                        <p class="description">
                            <?php esc_html_e(
                                'This is your new login URL. wp-login.php will be blocked.',
                                'dailybuddy'
                            ); ?>
                        </p>
                    </td>
                </tr>

                <!-- Redirect slug -->
                <tr>
                    <th scope="row">
                        <label for="redirect_slug"><?php esc_html_e('Redirect Slug', 'dailybuddy'); ?></label>
                    </th>
                    <td>
                        <?php if ($dailybuddy_has_permalink) : ?>
                            <code><?php echo esc_html(trailingslashit(home_url())); ?></code>
                            <input
                                type="text"
                                id="redirect_slug"
                                name="redirect_slug"
                                value="<?php echo esc_attr($redirect_slug); ?>"
                                class="regular-text"
                                required>
                            <?php if ('/' === substr($dailybuddy_has_permalink, -1)) : ?>
                                <code>/</code>
                            <?php endif; ?>
                        <?php else : ?>
                            <code><?php echo esc_html(trailingslashit(home_url())); ?>?</code>
                            <input
                                type="text"
                                id="redirect_slug"
                                name="redirect_slug"
                                value="<?php echo esc_attr($redirect_slug); ?>"
                                class="regular-text"
                                required>
                        <?php endif; ?>

                        <p class="description">
                            <?php esc_html_e(
                                'Redirect URL when someone tries to access wp-login.php or wp-admin while not logged in. Default: 404.',
                                'dailybuddy'
                            ); ?>
                        </p>
                    </td>
                </tr>

                <!-- Current login URL -->
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Your Login URL', 'dailybuddy'); ?>
                    </th>
                    <td>
                        <p>
                            <a href="<?php echo esc_url($dailybuddy_current_login_url); ?>" target="_blank" style="font-size: 16px; font-weight: 600; color: #2271b1;">
                                <?php echo esc_html($dailybuddy_current_login_url); ?>
                            </a>
                            <button
                                type="button"
                                class="button button-secondary copy-url-btn"
                                data-url="<?php echo esc_attr($dailybuddy_current_login_url); ?>"
                                style="margin-left: 10px;">
                                <span class="dashicons dashicons-admin-page" style="margin-top:6px;"></span>
                                <?php esc_html_e('Copy', 'dailybuddy'); ?>
                            </button>
                        </p>
                    </td>
                </tr>

                <!-- Current redirect URL -->
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Your Redirect URL', 'dailybuddy'); ?>
                    </th>
                    <td>
                        <p style="font-size: 16px; font-weight: 600; color: #d63638;">
                            <?php echo esc_html($dailybuddy_current_redirect_url); ?>
                        </p>
                        <p class="description">
                            <?php esc_html_e(
                                'This is where non-logged-in users will be redirected when accessing wp-admin or wp-login.php.',
                                'dailybuddy'
                            ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="warning-box">
            <h3>
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('Important', 'dailybuddy'); ?>
            </h3>
            <ul>
                <li><strong><?php esc_html_e('BOOKMARK your login URL immediately!', 'dailybuddy'); ?></strong></li>
                <li>
                    <?php esc_html_e('Your login page:', 'dailybuddy'); ?>
                    <code><?php echo esc_html($dailybuddy_current_login_url); ?></code>
                </li>
                <li>
                    <?php esc_html_e('wp-login.php will redirect to:', 'dailybuddy'); ?>
                    <code><?php echo esc_html($dailybuddy_current_redirect_url); ?></code>
                </li>
                <li>
                    <?php esc_html_e('wp-admin (not logged in) will redirect to:', 'dailybuddy'); ?>
                    <code><?php echo esc_html($dailybuddy_current_redirect_url); ?></code>
                </li>
                <li><?php esc_html_e('Password reset and logout will continue to work.', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('Login slug and redirect slug must be different.', 'dailybuddy'); ?></li>
            </ul>
        </div>

        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary', 'dailybuddy_custom_login_url_submit'); ?>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Copy URL to clipboard.
        $('.copy-url-btn').on('click', function() {
            const url = $(this).data('url');
            const btn = $(this);

            const temp = $('<input>');
            $('body').append(temp);
            temp.val(url).select();
            document.execCommand('copy');
            temp.remove();

            const originalText = btn.html();
            btn.html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e('Copied!', 'dailybuddy'); ?>');
            btn.css('color', '#00a32a');

            setTimeout(function() {
                btn.html(originalText);
                btn.css('color', '');
            }, 2000);
        });

        // Validate that login and redirect slugs are different.
        $('form.dailybuddy-settings-form').on('submit', function(e) {
            const loginSlug = $('#login_slug').val().trim();
            const redirectSlug = $('#redirect_slug').val().trim();

            if (loginSlug === redirectSlug) {
                e.preventDefault();
                alert('<?php echo esc_js(__('Error: Login slug and redirect slug cannot be the same!', 'dailybuddy')); ?>');
                $('#login_slug').focus();
                return false;
            }

            if (loginSlug === '' || redirectSlug === '') {
                e.preventDefault();
                alert('<?php echo esc_js(__('Error: Both slugs are required!', 'dailybuddy')); ?>');
                return false;
            }
        });
    });
</script>
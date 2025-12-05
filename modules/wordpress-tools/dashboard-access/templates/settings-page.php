<?php

/**
 * Template: Dashboard Access Control Settings
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap dailybuddy-dashboard-access-settings">
    <h1>
        <span class="dashicons dashicons-shield" style="font-size: 32px; width: 32px; height: 32px;"></span>
        <?php esc_html_e('Dashboard Access Control', 'dailybuddy'); ?>
    </h1>

    <p class="description">
        <?php esc_html_e('Control which user roles can access the WordPress dashboard. Users without access will be redirected to the specified URL.', 'dailybuddy'); ?>
    </p>

    <form method="post" action="" class="dailybuddy-settings-form">
        <?php wp_nonce_field('dailybuddy_dashboard_access_settings'); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <!-- Allowed Roles -->
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Allowed Roles', 'dailybuddy'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <span><?php esc_html_e('Allowed Roles', 'dailybuddy'); ?></span>
                            </legend>

                            <div class="roles-list">
                                <?php foreach ($all_roles as $dailybuddy_role_key => $dailybuddy_role_data) : ?>
                                    <?php
                                    $dailybuddy_is_checked = in_array($dailybuddy_role_key, $settings['allowed_roles'], true);
                                    $dailybuddy_is_admin   = ('administrator' === $dailybuddy_role_key);
                                    ?>
                                    <label class="role-checkbox <?php echo $dailybuddy_is_admin ? 'admin-role' : ''; ?>">
                                        <input
                                            type="checkbox"
                                            name="allowed_roles[]"
                                            value="<?php echo esc_attr($dailybuddy_role_key); ?>"
                                            <?php checked($dailybuddy_is_checked); ?>
                                            <?php disabled($dailybuddy_is_admin); ?>>
                                        <span class="role-name"><?php echo esc_html($dailybuddy_role_data['name']); ?></span>
                                        <?php if ($dailybuddy_is_admin) : ?>
                                            <span class="admin-badge">
                                                <?php esc_html_e('Always Allowed', 'dailybuddy'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <p class="description">
                                <?php esc_html_e('Select which user roles can access the WordPress dashboard. Administrators always have access.', 'dailybuddy'); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Info Box -->
        <div class="info-box">
            <h3>
                <span class="dashicons dashicons-info"></span>
                <?php esc_html_e('How it works', 'dailybuddy'); ?>
            </h3>
            <ul>
                <li><?php esc_html_e('Users with allowed roles can access the dashboard normally', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('Users without allowed roles will be redirected to their profile page when trying to access /wp-admin/', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('The admin bar in the frontend is automatically hidden for non-allowed users', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('Administrators always have full access (cannot be restricted)', 'dailybuddy'); ?></li>
                <li><?php esc_html_e('AJAX requests are not affected by this restriction', 'dailybuddy'); ?></li>
            </ul>
        </div>

        <!-- Warning Box -->
        <div class="warning-box">
            <h3>
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('Important', 'dailybuddy'); ?>
            </h3>
            <p>
                <?php esc_html_e('Be careful when restricting dashboard access. Make sure you understand which roles need access before making changes.', 'dailybuddy'); ?>
            </p>
        </div>

        <?php submit_button(__('Save Settings', 'dailybuddy'), 'primary', 'dailybuddy_dashboard_access_submit'); ?>
    </form>
</div>

<style>
    .dailybuddy-dashboard-access-settings h1 {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dailybuddy-dashboard-access-settings h1 .dashicons {
        color: #2271b1;
    }

    .dailybuddy-settings-form {
        background: #fff;
        padding: 20px;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        margin-top: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .roles-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 10px;
        margin: 10px 0;
    }

    .role-checkbox {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px;
        background: #f6f7f7;
        border: 1px solid #dcdcde;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .role-checkbox:hover {
        background: #fff;
        border-color: #2271b1;
    }

    .role-checkbox input[type="checkbox"] {
        margin: 0;
    }

    .role-checkbox input[type="checkbox"]:disabled {
        cursor: not-allowed;
    }

    .role-checkbox.admin-role {
        background: #e7f5fe;
        border-color: #2271b1;
    }

    .role-name {
        font-weight: 500;
        flex: 1;
    }

    .admin-badge {
        font-size: 11px;
        background: #2271b1;
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-weight: 600;
    }

    .info-box,
    .warning-box {
        margin: 20px 0;
        padding: 15px 20px;
        border-radius: 4px;
    }

    .info-box {
        background: #e7f5fe;
        border-left: 4px solid #2271b1;
    }

    .warning-box {
        background: #fcf9e8;
        border-left: 4px solid #dba617;
    }

    .info-box h3,
    .warning-box h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
    }

    .info-box .dashicons {
        color: #2271b1;
    }

    .warning-box .dashicons {
        color: #dba617;
    }

    .info-box ul {
        margin: 10px 0 0 0;
        padding-left: 20px;
    }

    .info-box li {
        margin: 5px 0;
    }

    .warning-box p {
        margin: 5px 0 0 0;
    }
</style>
<?php

/**
 * Module Settings Page Template
 */

if (! defined('ABSPATH')) {
    exit;
}

$dailybuddy_config        = $module_data['config'];
$dailybuddy_module_name   = $dailybuddy_config['name'];
$dailybuddy_module_id     = $module_data['id'];
$dailybuddy_category      = explode('/', $dailybuddy_module_id)[0];
$dailybuddy_category_name = dailybuddy_format_category_name($dailybuddy_category);

// Aktuellen Modul-Status laden.
$dailybuddy_modules_status = Dailybuddy_Settings::get_modules();
$dailybuddy_is_active      = ! empty($dailybuddy_modules_status[$dailybuddy_module_id]);

// Breadcrumb link → focus on category only.
$dailybuddy_breadcrumb_url = add_query_arg(
    array(
        'page'           => 'dailybuddy',
        'focus_category' => $dailybuddy_category,
    ),
    admin_url('admin.php')
);

// Activation link → Focus on category + module.
$dailybuddy_activate_url = add_query_arg(
    array(
        'page'           => 'dailybuddy',
        'focus_category' => $dailybuddy_category,
        'focus_module'   => $dailybuddy_module_id,
    ),
    admin_url('admin.php')
);

?>

<div class="wrap">

    <!-- Breadcrumbs -->
    <div class="dailybuddy-breadcrumbs">
        <a href="<?php echo esc_url($dailybuddy_breadcrumb_url); ?>">
            <?php esc_html_e('DailyBuddy', 'dailybuddy'); ?>
        </a>

        <span class="separator">›</span>

        <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy-settings')); ?>">
            <?php esc_html_e('General Settings', 'dailybuddy'); ?>
        </a>

        <span class="separator">›</span>

        <span><?php echo esc_html($dailybuddy_category_name); ?></span>

        <span class="separator">›</span>

        <strong><?php echo esc_html($dailybuddy_module_name); ?></strong>
    </div>

    <h1 class="dailybuddy-settings-header">
        <span class="dailybuddy-settings-icon dashicons <?php echo esc_attr(isset($dailybuddy_config['icon']) ? $dailybuddy_config['icon'] : 'dashicons-admin-generic'); ?>"></span>
        <span class="dailybuddy-settings-title">
            <?php echo esc_html($dailybuddy_module_name); ?>
            <small><?php esc_html_e('Settings', 'dailybuddy'); ?></small>
        </span>
    </h1>

    <?php
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only GET param used only for notice display
    if (isset($_GET['settings-updated'])) :
    ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved!', 'dailybuddy'); ?></p>
        </div>
    <?php
    endif;
    ?>

    <?php if (! $dailybuddy_is_active) : ?>

        <div class="notice notice-warning">
            <p>
                <?php
                echo wp_kses(
                    sprintf(
                        // translators: 1: module name, 2: activation link URL.
                        __(
                            'The module "%1$s" is currently <strong>deactivated</strong>. You can see and change its settings after you <a href="%2$s">activate the module</a>.',
                            'dailybuddy'
                        ),
                        esc_html($dailybuddy_module_name),
                        esc_url($dailybuddy_activate_url)
                    ),
                    array(
                        'strong' => array(),
                        'a'      => array(
                            'href' => array(),
                        ),
                    )
                );
                ?>
            </p>
        </div>

    <?php else : ?>

        <div class="dailybuddy-settings-container">

            <?php
            if ($settings_callback && function_exists($settings_callback)) {
                call_user_func($settings_callback, $module_data);
            } else {
                echo '<div class="notice notice-warning"><p>' .
                    esc_html__('Settings interface not implemented yet.', 'dailybuddy') .
                    '</p></div>';
            }
            ?>

        </div>

    <?php endif; ?>

</div>
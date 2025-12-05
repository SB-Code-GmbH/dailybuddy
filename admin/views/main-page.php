<?php

/**
 * Admin Page Template - Modern Sidebar Layout with Auto-Save
 */

if (! defined('ABSPATH')) {
    exit;
}

// Calculate module stats for each category.
$dailybuddy_category_stats = array();
foreach ($modules as $dailybuddy_category => $dailybuddy_category_modules) {
    $dailybuddy_active_count = 0;
    $dailybuddy_total_count  = count($dailybuddy_category_modules);

    foreach ($dailybuddy_category_modules as $dailybuddy_module_data) {
        if (! empty($dailybuddy_module_data['active'])) {
            $dailybuddy_active_count++;
        }
    }

    $dailybuddy_category_stats[$dailybuddy_category] = array(
        'active' => $dailybuddy_active_count,
        'total'  => $dailybuddy_total_count,
    );
}

wp_localize_script(
    'dailybuddy-admin',
    'dailybuddyAdmin',
    array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'strings' => array(
            /* translators: %s: module name */
            'moduleActivated'   => __('%s activated!', 'dailybuddy'),

            /* translators: %s: module name */
            'moduleDeactivated' => __('%s deactivated', 'dailybuddy'),
        ),
    )
);

?>

<div class="wrap">

    <h1>
        <div class="dailybuddy-header">
            <span class="dailybuddy-logo">
                <img src="<?php echo esc_url(plugin_dir_url(dirname(__DIR__, 1)) . 'assets/images/logo.png'); ?>" alt="dailybuddy Logo" class="dailybuddy-logo-img">
            </span>
            <h2 style="margin: 0px;"><?php esc_html_e('DailyBuddy', 'dailybuddy'); ?></h2>
        </div>
    </h1>

    <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading display state only 
    ?>
    <?php if (isset($_GET['settings-updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved!', 'dailybuddy'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (empty($modules)) : ?>

        <div class="notice notice-info">
            <p><?php esc_html_e('No modules available yet. Create your first module in the modules/ directory!', 'dailybuddy'); ?></p>
        </div>

    <?php else : ?>

        <div class="dailybuddy-container">

            <!-- Sidebar Navigation -->
            <div class="dailybuddy-sidebar">
                <nav class="dailybuddy-nav">
                    <?php $dailybuddy_first = true; ?>
                    <?php foreach ($modules as $dailybuddy_category => $dailybuddy_category_modules) : ?>
                        <?php
                        $dailybuddy_stats      = $dailybuddy_category_stats[$dailybuddy_category];
                        $dailybuddy_has_active = $dailybuddy_stats['active'] > 0;
                        ?>
                        <a href="#<?php echo esc_attr($dailybuddy_category); ?>"
                            class="dailybuddy-nav-item <?php echo $dailybuddy_first ? 'active' : ''; ?>"
                            data-category="<?php echo esc_attr($dailybuddy_category); ?>">
                            <span class="dailybuddy-nav-icon <?php echo esc_attr(dailybuddy_get_category_icon($dailybuddy_category)); ?>"></span>
                            <span class="dailybuddy-nav-text">
                                <?php echo esc_html(dailybuddy_format_category_name($dailybuddy_category)); ?>
                            </span>
                            <span class="dailybuddy-nav-counter <?php echo $dailybuddy_has_active ? 'has-active' : ''; ?>">
                                <?php echo esc_html($dailybuddy_stats['active'] . '/' . $dailybuddy_stats['total']); ?>
                            </span>
                        </a>
                        <?php $dailybuddy_first = false; ?>
                    <?php endforeach; ?>
                </nav>
            </div>

            <!-- Main Content Area -->
            <div class="dailybuddy-content">

                <?php $dailybuddy_first = true; ?>
                <?php foreach ($modules as $dailybuddy_category => $dailybuddy_category_modules) : ?>

                    <div class="dailybuddy-category <?php echo $dailybuddy_first ? 'active' : ''; ?>"
                        data-category="<?php echo esc_attr($dailybuddy_category); ?>">

                        <div class="dailybuddy-category-header">
                            <h2>
                                <span class="dailybuddy-nav-icon <?php echo esc_attr(dailybuddy_get_category_icon($dailybuddy_category)); ?>"></span>
                                <?php echo esc_html(dailybuddy_format_category_name($dailybuddy_category)); ?>
                            </h2>
                            <p class="dailybuddy-category-description">
                                <?php echo esc_html(dailybuddy_get_category_description($dailybuddy_category)); ?>
                            </p>
                        </div>

                        <div class="dailybuddy-modules">

                            <?php foreach ($dailybuddy_category_modules as $dailybuddy_module_name => $dailybuddy_module_data) : ?>
                                <?php
                                $dailybuddy_config          = $dailybuddy_module_data['config'];
                                $dailybuddy_is_premium      = isset($dailybuddy_config['is_premium']) && $dailybuddy_config['is_premium'];
                                $dailybuddy_has_requirements = isset($dailybuddy_config['requires']) && ! empty($dailybuddy_config['requires']);
                                $dailybuddy_has_settings    = isset($dailybuddy_config['has_settings']) && $dailybuddy_config['has_settings'];
                                $dailybuddy_icon            = isset($dailybuddy_config['icon']) ? $dailybuddy_config['icon'] : 'dashicons-admin-plugins';
                                $dailybuddy_tags            = isset($dailybuddy_config['tags']) ? $dailybuddy_config['tags'] : array();
                                $dailybuddy_version         = isset($dailybuddy_config['version']) ? $dailybuddy_config['version'] : '1.0.0';
                                ?>

                                <div
                                    class="dailybuddy-module-card <?php echo ! empty($dailybuddy_module_data['active']) ? 'is-active' : ''; ?>"
                                    id="dailybuddy-module-<?php echo esc_attr($dailybuddy_module_data['id']); ?>"
                                    data-module-id="<?php echo esc_attr($dailybuddy_module_data['id']); ?>"
                                    data-category="<?php echo esc_attr($dailybuddy_category); ?>">

                                    <div class="dailybuddy-module-header">
                                        <div class="dailybuddy-module-title">
                                            <span class="dailybuddy-module-icon <?php echo esc_attr($dailybuddy_icon); ?>"></span>
                                            <h3><?php echo esc_html($dailybuddy_config['name']); ?></h3>
                                            <?php if ($dailybuddy_is_premium) : ?>
                                                <span class="dailybuddy-premium-badge">👑 PREMIUM</span>
                                            <?php endif; ?>
                                        </div>

                                        <label class="dailybuddy-switch">
                                            <input
                                                type="checkbox"
                                                class="dailybuddy-module-toggle"
                                                id="module_<?php echo esc_attr($dailybuddy_module_data['id']); ?>"
                                                data-module-id="<?php echo esc_attr($dailybuddy_module_data['id']); ?>"
                                                data-module-name="<?php echo esc_attr($dailybuddy_config['name']); ?>"
                                                <?php checked($dailybuddy_module_data['active'], true); ?>>
                                            <span class="dailybuddy-slider"></span>
                                        </label>
                                    </div>

                                    <div class="dailybuddy-module-body">
                                        <p class="dailybuddy-module-description">
                                            <?php echo esc_html($dailybuddy_config['description']); ?>
                                        </p>
                                        <?php if (isset($dailybuddy_config['important_description']) && ! empty($dailybuddy_config['important_description'])) : ?>
                                            <p class="dailybuddy-module-description-important">
                                                <?php echo esc_html($dailybuddy_config['important_description']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (! empty($dailybuddy_tags)) : ?>
                                            <div class="dailybuddy-module-tags">
                                                <?php foreach ($dailybuddy_tags as $dailybuddy_tag) : ?>
                                                    <span class="dailybuddy-tag-badge"><?php echo esc_html($dailybuddy_tag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="dailybuddy-module-footer">
                                        <div class="dailybuddy-module-meta">
                                            <span class="dailybuddy-module-version">
                                                <span class="fa-solid fa-code-branch"></span>
                                                <?php echo esc_html($dailybuddy_version); ?>
                                            </span>

                                            <?php if ($dailybuddy_has_settings) : ?>
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=dailybuddy&view=settings&module=' . urlencode($dailybuddy_module_data['id']))); ?>"
                                                    class="dailybuddy-settings-link">
                                                    <span class="fa-solid fa-toolbox"></span>
                                                    <?php esc_html_e('Settings', 'dailybuddy'); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($dailybuddy_has_requirements) : ?>
                                            <div class="dailybuddy-module-requirements">
                                                <span class="fa-solid fa-circle-exclamation"></span>
                                                <?php esc_html_e('Requires:', 'dailybuddy'); ?>

                                                <?php foreach ($dailybuddy_config['requires'] as $dailybuddy_requirement) : ?>
                                                    <?php
                                                    if (is_array($dailybuddy_requirement)) {
                                                        $dailybuddy_name = $dailybuddy_requirement['name'];
                                                        $dailybuddy_url  = $dailybuddy_requirement['link'];
                                                    } else {
                                                        $dailybuddy_name = $dailybuddy_requirement;
                                                        $dailybuddy_url  = admin_url('plugin-install.php?tab=search&type=term&s=' . urlencode($dailybuddy_name));
                                                    }
                                                    ?>
                                                    <a href="<?php echo esc_url($dailybuddy_url); ?>" target="_blank" rel="noopener noreferrer" class="dailybuddy-requirement-link">
                                                        <?php echo esc_html($dailybuddy_name); ?>
                                                    </a><?php if (next($dailybuddy_config['requires'])) {
                                                            echo ', ';
                                                        } ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                    <?php $dailybuddy_first = false; ?>
                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>

</div>
<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="dailybuddy-folders-sidebar" class="dailybuddy-folders-sidebar">
    <div class="dailybuddy-folders-resizer"></div>

    <div class="folders-header">
        <button type="button" id="dailybuddy-new-folder-btn" class="button">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php esc_html_e('New Folder', 'dailybuddy'); ?>
        </button>
    </div>

    <div class="folders-search">
        <input type="text" id="dailybuddy-folder-search" placeholder="<?php esc_attr_e('Search folders...', 'dailybuddy'); ?>">
    </div>

    <div class="folders-tree-container">
        <div class="folders-special">
            <div class="folder-item active" data-folder-id="all">
                <span class="dashicons dashicons-category"></span>
                <span class="folder-name"><?php esc_html_e('All Files', 'dailybuddy'); ?></span>
                <span class="folder-count" id="folder-count-all">0</span>
            </div>
            <div class="folder-item" data-folder-id="unassigned">
                <span class="dashicons dashicons-category"></span>
                <span class="folder-name"><?php esc_html_e('Unassigned Files', 'dailybuddy'); ?></span>
                <span class="folder-count" id="folder-count-unassigned">0</span>
            </div>
        </div>

        <div id="folders-tree" class="folders-tree">
            <!-- Loaded via AJAX -->
        </div>
    </div>

    <div id="dailybuddy-new-folder-form" class="folders-form" style="display: none;">
        <input type="text" id="dailybuddy-folder-name-input" placeholder="<?php esc_attr_e('Folder name...', 'dailybuddy'); ?>">
        <div class="folders-form-actions">
            <button type="button" id="dailybuddy-create-folder-btn" class="button button-primary"><?php esc_html_e('Create', 'dailybuddy'); ?></button>
            <button type="button" id="dailybuddy-cancel-folder-btn" class="button"><?php esc_html_e('Cancel', 'dailybuddy'); ?></button>
        </div>
    </div>

    <!-- Rename-Formular -->
    <div id="dailybuddy-rename-folder-form" class="folders-form" style="display: none;">
        <input type="text" id="dailybuddy-rename-folder-input"
            placeholder="<?php esc_attr_e('New folder name...', 'dailybuddy'); ?>">
        <div class="folders-form-actions">
            <button type="button" id="dailybuddy-rename-folder-save-btn"
                class="button button-primary"><?php esc_html_e('Rename', 'dailybuddy'); ?></button>
            <button type="button" id="dailybuddy-rename-folder-cancel-btn"
                class="button"><?php esc_html_e('Cancel', 'dailybuddy'); ?></button>
        </div>
    </div>

</div>

<button type="button" id="dailybuddy-folders-toggle" class="dailybuddy-folders-toggle">
    <span class="dashicons dashicons-arrow-left-alt2"></span>
</button>

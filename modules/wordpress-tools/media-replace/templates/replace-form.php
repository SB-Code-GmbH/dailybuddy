<?php
/**
 * Template: Media Replace Form
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap dailybuddy-media-replace">
    <h1><?php esc_html_e('Replace Media File', 'dailybuddy'); ?></h1>

    <div class="dailybuddy-replace-container">
        <div class="dailybuddy-current-file">
            <h2><?php esc_html_e('Current File', 'dailybuddy'); ?></h2>
            
            <div class="file-preview">
                <?php if (wp_attachment_is_image($attachment_id)) : ?>
                    <img src="<?php echo esc_url($file_url); ?>" alt="<?php echo esc_attr($attachment_title); ?>" style="max-width: 300px; height: auto;">
                <?php else : ?>
                    <div class="file-icon">
                        <span class="dashicons dashicons-media-default"></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="file-details">
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Filename:', 'dailybuddy'); ?></th>
                        <td><code><?php echo esc_html(basename($file_path)); ?></code></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('File Type:', 'dailybuddy'); ?></th>
                        <td><?php echo esc_html($file_type); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('File Size:', 'dailybuddy'); ?></th>
                        <td><?php echo esc_html($file_size); ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('URL:', 'dailybuddy'); ?></th>
                        <td><a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_url($file_url); ?></a></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="dailybuddy-replace-form">
            <h2><?php esc_html_e('Upload New File', 'dailybuddy'); ?></h2>
            
            <form method="post" enctype="multipart/form-data" id="dailybuddy-replace-form">
                <?php wp_nonce_field('dailybuddy_replace_media'); ?>
                <input type="hidden" name="attachment_id" value="<?php echo esc_attr($attachment_id); ?>">
                
                <div class="upload-field">
                    <label for="replace_file" class="button button-large">
                        <span class="dashicons dashicons-upload"></span>
                        <?php esc_html_e('Choose File', 'dailybuddy'); ?>
                    </label>
                    <input type="file" name="replace_file" id="replace_file" style="display: none;">
                    <span id="file-chosen"><?php esc_html_e('No file chosen', 'dailybuddy'); ?></span>
                </div>

                <div class="replace-options">
                    <h3><?php esc_html_e('Replace Options', 'dailybuddy'); ?></h3>
                    
                    <p class="description">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('The new file will replace the existing file while keeping the same URL. All links and references will continue to work.', 'dailybuddy'); ?>
                    </p>

                    <p class="description">
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e('Important: This action cannot be undone. The old file will be permanently deleted.', 'dailybuddy'); ?>
                    </p>
                </div>

                <div class="submit-actions">
                    <button type="submit" name="dailybuddy_replace_submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Replace File', 'dailybuddy'); ?>
                    </button>
                    
                    <a href="<?php echo esc_url(admin_url('upload.php')); ?>" class="button button-large">
                        <?php esc_html_e('Cancel', 'dailybuddy'); ?>
                    </a>
                </div>

                <div id="upload-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="progress-text"><?php esc_html_e('Uploading...', 'dailybuddy'); ?></p>
                </div>
            </form>
        </div>
    </div>
</div>


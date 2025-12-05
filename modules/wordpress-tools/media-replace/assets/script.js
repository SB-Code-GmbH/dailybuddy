/**
 * Media Replace Module JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Show success message if file was replaced
        if (window.location.search.indexOf('dailybuddy_replaced=1') !== -1) {
            const urlParams = new URLSearchParams(window.location.search);
            const attachmentId = urlParams.get('attachment_id');
            
            showSuccessNotice(
                dailybuddyMediaReplace.strings.success,
                attachmentId
            );
            
            // Clean URL
            const cleanUrl = window.location.pathname + '?page=upload.php';
            window.history.replaceState({}, document.title, cleanUrl);
        }

        // File input change handler
        $('#replace_file').on('change', function() {
            const fileName = this.files[0] ? this.files[0].name : dailybuddyMediaReplace.strings.noFile;
            $('#file-chosen').text(fileName);
            
            // Show file size
            if (this.files[0]) {
                const fileSize = formatFileSize(this.files[0].size);
                $('#file-chosen').append(' <span class="file-size">(' + fileSize + ')</span>');
            }
        });

        // Drag and drop support
        const uploadField = $('.upload-field');
        
        uploadField.on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        });
        
        uploadField.on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        });
        
        uploadField.on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
            
            const files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $('#replace_file')[0].files = files;
                $('#replace_file').trigger('change');
            }
        });

        // Form validation
        $('#dailybuddy-replace-form').on('submit', function(e) {
            const fileInput = $('#replace_file')[0];
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert(dailybuddyMediaReplace.strings.selectFile || 'Bitte wählen Sie eine Datei aus.');
                return false;
            }

            // Confirm replacement
            if (!confirm(dailybuddyMediaReplace.strings.confirmReplace || 'Sind Sie sicher, dass Sie diese Datei ersetzen möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                e.preventDefault();
                return false;
            }

            // Show progress
            showProgress();
        });

        /**
         * Show upload progress
         */
        function showProgress() {
            $('#upload-progress').slideDown();
            $('.submit-actions').slideUp();
            $('.upload-field').css('opacity', '0.5');
        }

        /**
         * Show success notice
         */
        function showSuccessNotice(message, attachmentId) {
            const notice = $('<div>', {
                'class': 'notice notice-success is-dismissible dailybuddy-replaced',
                'html': '<p><span class="dashicons dashicons-yes-alt"></span> ' + message + '</p>'
            });

            $('.wrap').first().prepend(notice);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Format file size
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        /**
         * AJAX Upload (Alternative method - not used by default)
         */
        function ajaxUpload(attachmentId, file) {
            const formData = new FormData();
            formData.append('action', 'dailybuddy_replace_media');
            formData.append('nonce', dailybuddyMediaReplace.nonce);
            formData.append('attachment_id', attachmentId);
            formData.append('file', file);

            $.ajax({
                url: dailybuddyMediaReplace.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    
                    // Upload progress
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            updateProgressBar(percentComplete);
                        }
                    }, false);
                    
                    return xhr;
                },
                success: function(response) {
                    if (response.success) {
                        showSuccessNotice(response.data.message);
                        
                        // Refresh page after 2 seconds
                        setTimeout(function() {
                            window.location.href = dailybuddyMediaReplace.returnUrl || 'upload.php';
                        }, 2000);
                    } else {
                        alert(response.data.message || dailybuddyMediaReplace.strings.error);
                        hideProgress();
                    }
                },
                error: function(xhr, status, error) {
                    alert(dailybuddyMediaReplace.strings.error + ' ' + error);
                    hideProgress();
                }
            });
        }

        /**
         * Update progress bar
         */
        function updateProgressBar(percent) {
            $('.progress-fill').css('width', percent + '%');
            $('.progress-text').text(Math.round(percent) + '%');
        }

        /**
         * Hide progress
         */
        function hideProgress() {
            $('#upload-progress').slideUp();
            $('.submit-actions').slideDown();
            $('.upload-field').css('opacity', '1');
        }

    });

})(jQuery);

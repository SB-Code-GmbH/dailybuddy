jQuery(document).ready(function ($) {
    // Tab switching
    $('.dailybuddy-uc-tab').on('click', function () {
        var tab = $(this).data('tab');

        $('#current_tab').val(tab);

        $('.dailybuddy-uc-tab').removeClass('active');
        $(this).addClass('active');

        $('.dailybuddy-uc-tab-content').removeClass('active');
        $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');
    });

    // Color picker text sync
    $('#primary_color').on('change input', function () {
        $('#primary_color_text').val($(this).val());
    });
    $('#accent_color').on('change input', function () {
        $('#accent_color_text').val($(this).val());
    });
});
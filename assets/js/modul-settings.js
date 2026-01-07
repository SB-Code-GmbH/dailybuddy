jQuery(document).ready(function ($) {
    $('.dailybuddy-uc-tab').on('click', function () {
        var tab = $(this).data('tab');

        $('#current_tab').val(tab);

        $('.dailybuddy-uc-tab').removeClass('active');
        $(this).addClass('active');

        $('.dailybuddy-uc-tab-content').removeClass('active');
        $('.dailybuddy-uc-tab-content[data-tab="' + tab + '"]').addClass('active');
    });
});
(function ($) {
    $(document).on('click', 'div[darwin-data-dismissible] .notice-dismiss', function (e) {
        e.preventDefault();
        $.post(ajaxurl, {
            action: 'anyway_dismiss_notice_ajax',
            name: $(this).parent().attr('darwin-data-dismissible'),
            interval: $(this).parent().attr('darwin-data-interval')
        });
        return false;
    });
}(jQuery));
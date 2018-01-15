(function ($, document, window) {
    $(document).on('click', '[darwin-data-dismissible=archives-in-root] .delete', function () {
        $.post(ajaxurl, { action: 'anyway_delete_archives_in_root_ajax'}, function (response) {
            if (response && response.success) {
                $('[darwin-data-dismissible=archives-in-root]').hide();
            } else if (response && response.data) {
                $('[darwin-data-dismissible=archives-in-root]').replaceWith(response.data.message);
            } else {
                $('[darwin-data-dismissible=archives-in-root] p').text(
                    $('[darwin-data-dismissible=archives-in-root]').data('failed-text')
                );
            }
        });
        return false;
    });
}(jQuery, document, window));

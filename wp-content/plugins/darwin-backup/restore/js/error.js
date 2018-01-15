(function ($, window) {
    var button = '.anyway-error-to-clipboard',
        clipboard;

    $(document).on('click', '.anyway-notice .action a', function () {
        $(this).parents('.anyway-notice').find('.stack-trace').toggle();
        // <= IE8
        if (window.Clipboard && !clipboard) {
            clipboard = new Clipboard(button);
            clipboard.on('success', function(e) {
                $(button).html($(button).data('text-copied'));
            });
        }
        return false;
    });

}(jQuery, window));

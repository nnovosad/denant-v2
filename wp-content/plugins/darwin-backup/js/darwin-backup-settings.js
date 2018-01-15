(function ($, document) {

    $(app).bind('settings:email', function () {
        var emails = Array.prototype.slice.call(arguments, 1);
        $('[name=email]').val(emails.join(", "));
    });

    $(app).bind('settings:send-mail', function (e, value) {
        $('[name=send-mail]').attr("checked", value);
        $('[name=email]').attr('disabled', !value);
    });

    $(document).ready(function () {
        var app = window.app;

        app.page = "Settings";
        app.load_settings_request = function load_settings_request() {
            return {
                action: 'anyway_load_settings_ajax'
            };
        };
        app.init(window.ajaxurl);

        $('#unlimited').on('click', function () {
            $('[name=retain]').attr('disabled', $(this).is(':checked'));
        });

        $('[name=send-mail]').on('click', function () {
            $('[name=email]').attr('disabled', !$(this).is(':checked'));
        });

        if (($.widget || {}).bridge) {
            $.widget.bridge('uitooltip', $.ui.tooltip);
            $(document).uitooltip();
        }
    });

}(jQuery, document, window));


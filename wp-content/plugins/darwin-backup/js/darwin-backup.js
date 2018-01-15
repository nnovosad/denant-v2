(function ($, document, window) {

    $(document).ready(function () {
        var app = window.app,
            clipboard;

        var dialogMail = $("#dialog-mail").dialog({
            autoOpen: false,
            width: 700,
            modal: true
        });

        // <= IE8
        if (window.Clipboard)
            clipboard = new Clipboard('.anyway-button-clipboard');

        $(document).on('click', '.anyway-button-download', function () {
            app.track("Download");
        });

        $(document).on('click', '.anyway-button-restore', function () {
            app.track("Link to Restore");
        });

        $(document).on('click', '.anyway-button-delete', function () {
            app.track("Delete");
        });

        $(document).on('click', '.anyway-button-clipboard', function () {
            app.track("Copy to Clipboard");
            return false;
        });

        $(document).on('click', '.anyway-button-mail-dialog', function (e) {
            dialogMail.dialog("open");
            $('#anyway-button-mail').data("sid", $(this).data("sid"));
            $('[name=email]').focus();
            return false;
        });

        $('#anyway-button-mail').bind("click", function (e) {
            $('.error', dialogMail).remove();
            var self = e.target,
                request = {
                    action: 'anyway_mail_ajax',
                    sid: $(self).data('sid'),
                    email: $('[name=email]').val()
                };

            $(self).attr("disabled", true);
            $.post(ajaxurl, request, function (response) {
                if (response && response.success) {
                    app.track("Mail Ok");
                    dialogMail.dialog("close");
                } else if (response.data) {
                    app.track("Mail Failed");
                    dialogMail.prepend('<div class="error">' + response.data.warning + '</div>');
                } else {
                    app.track("Mail Failed");
                    dialogMail.prepend('<div class="error">' + $(self).data('text-error') + '</div>');
                }
                $(self).attr("disabled", false);
            });
            return false;
        });

        $(app).bind('settings:email', function () {
            var emails = Array.prototype.slice.call(arguments, 1);
            $('[name=email]').val(emails.join(", "));
        });

        app.page = "Listing";
        app.load_settings_request = function start_request() {
            return {
                action: 'anyway_load_settings_ajax'
            };
        };
        app.init(window.ajaxurl);

    });

})(jQuery, document, window);
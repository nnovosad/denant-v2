(function ($, document, window) {
    var app = window.app,
        progressbar, clipboard, stage;

    var form = {
        init: function () {

            var dialogMail = $("#dialog-mail").dialog({
                autoOpen: false,
                width: 700,
                modal: true
            });

            progressbar = $("#progressbar");
            progressbar.progressbar({value: 0});

            $('[name=include-uploads]').bind("change", function () {
                var self = this,
                    request = {
                        action: 'anyway_save_settings_ajax',
                        'include-uploads': $(this).is(':checked')
                    };
                $.post(window.ajaxurl, request, function (response) {
                    if (response && response.success) {
                        var checked = response.data['include-uploads'];
                        includeUploads = checked;
                        $(self).attr('checked', checked);
                    } else {
                        $(self).attr('disabled', true);
                    }
                });
            });

            $('#anyway-button-start').bind("click", function () {
                app.start();
                return false;
            });

            $('#anyway-button-cancel').bind("click", function () {
                app.stop();
                return false;
            });

            $('#anyway-button-mail-dialog').bind("click", function () {
                dialogMail.dialog("open");
                $('[name=email]').focus();
                return false;
            });

            $('#anyway-button-mail').bind("click", function () {
                $('.error', dialogMail).remove();
                var self = this,
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
                        dialogMail.prepend('<div class="error">' + response.data.message + '</div>');
                    } else {
                        app.track("Mail Failed");
                        dialogMail.prepend('<div class="error">' + $(self).data('text-error') + '</div>');
                    }
                    $(self).attr("disabled", false);
                });
                return false;
            });

            $('#anyway-button-download').bind("click", function () {
                window.location.href = $(this).data('url') + '&sid=' + $(this).data('sid');
            });

            // <= IE8
            if (window.Clipboard) {
                clipboard = new Clipboard('#anyway-button-clipboard');
                clipboard.on('success', function(e) {
                    app.track("Copy to Clipboard");
                    $('#anyway-button-clipboard').html($('#anyway-button-clipboard').data('text-copied'));
                });
            }
        }
    };

    $(app).bind("app:start", function (e, progress) {
        progressbar.progressbar("value", false);
        $('#include-uploads-checkbox').attr('disabled', true);
        $('#anyway-button-start').hide();
        $('#anyway-button-cancel').show();
    });

    $(app).bind("app:stop", function (e, progress) {
        $('#include-uploads-checkbox').attr('disabled', false);
        $('#anyway-button-start').show();
        $('#anyway-button-cancel').hide();
    });

    $(app).bind("progress", function (e, progress) {
        document.title = progress.toFixed(2) + "%";
        progressbar.progressbar("option", {value: progress});
        $(".progress-value").text(progress.toFixed(2) + "%");
    });

    $(app).bind("done", function (e, link) {
        document.title = "100%";
        progressbar.hide();
        $('#link input').val(link);
        $('#anyway-button-clipboard').attr('data-clipboard-text', link);
        $('#anyway-button-mail').attr('data-sid', app.sid);
        $('#anyway-button-download').attr('data-sid', app.sid);
        $('#link').show();
        $('#anyway-button-start').hide();
        $('#anyway-button-cancel').hide();
        $('#anyway-button-mail-dialog').show();
        $('#anyway-button-download').show();

        // <= IE8
        if (window.Clipboard)
            $('#anyway-button-clipboard').show();

        app.reset(); // to prevent next xhr request
    });

    $(app).bind("app:start", function () {
        progressbar.show();
        $(".progress-stage").text('');
    });

    $(app).bind("app:stop", function () {
        progressbar.progressbar("option", {value: 0});
        $(".progress-value").text("0.00%");
        $(".progress-stage").text('');
    });

    $(app).bind("estimate-fs:started", function () {
        $(".progress-stage").text($('.estimate-fs').text());
    });

    $(app).bind("estimate-db:started", function () {
        $(".progress-stage").text($('.estimate-db').text());
    });

    $(app).bind("estimate-bootstrap:started", function () {
        $(".progress-stage").text($('.estimate-bootstrap').text());
    });

    $(app).bind("bootstrap:started", function () {
        $(".progress-stage").text($('.bootstrap').text());
    });

    $(app).bind("compress:started", function () {
        $(".progress-stage").text($('.compress').text());
    });

    $(app).bind("mysqldump:started", function () {
        $(".progress-stage").text($('.mysqldump').text());
    });

    $(app).bind("decompress:started", function () {
        $(".progress-stage").text($('.decompress').text());
    });

    $(app).bind("mysqlrestore:started", function () {
        $(".progress-stage").text($('.mysqlrestore').text());
    });

    $(app).bind("rename:started", function () {
        $(".progress-stage").text($('.rename').text());
    });

    $(app).bind('settings:include-uploads', function (e, value) {
        $('[name=include-uploads]').attr('disabled', false);
        $('[name=include-uploads]').attr('checked', value);
    });

    $(app).bind('settings:email', function () {
        var emails = Array.prototype.slice.call(arguments, 1);
        $('[name=email]').val(emails.join(", "));
    });

    $(app).bind('error', function (e, message) {
        $('.wrap').parent().html(message);
    });

    $(document).ready(function () {

        app.track("Page - Add New");

        form.init();

        app.page = "Create";

        app.load_settings_request = function load_settings_request() {
            return {
                action: 'anyway_load_settings_ajax'
            };
        };
        app.start_request = function start_request() {
            return {
                action: 'anyway_start_ajax',
                'include-uploads': $('[name=include-uploads]').is(':checked')
            };
        };
        app.next_request = function start_request() {
            return {
                action: 'anyway_next_step_ajax',
                sid: this.sid
            };
        };
        app.stop_request = function start_request() {
            return {
                action: 'anyway_stop_ajax',
                sid: this.sid
            };
        };
        app.init(window.ajaxurl);

        if (($.widget || {}).bridge) {
            $.widget.bridge('uitooltip', $.ui.tooltip);
            $(document).uitooltip();
        }

    });

}(jQuery, document, window));



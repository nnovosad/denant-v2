(function ($, document) {
    var app = window.app,
        globalEmail = "";

    $(app).bind('settings:email', function (e, value) {
        globalEmail = value;
    });

    $(document).ready(function () {

        var dialog = $("#dialog").dialog({
            autoOpen: false,
            width: 700,
            modal: true
        });

        $('#unlimited').on('click', function () {
            $('[name=retain]').attr('disabled', $(this).is(':checked'));
        });

        $('[name=send-mail]').on('click', function () {
            $('[name=email]').attr('disabled', !$(this).is(':checked'));
        });

        $('.page-title-action').on('click', function () {
            var val = $("select[name=frequency] option:not([disabled]):first", dialog).val();
            $("select[name=frequency]", dialog).attr("disabled", false).val(val);
            $("input[name=frequency]", dialog).remove();

            $("[name=include_uploads]", dialog).attr("checked", false);
            $("[name=retain]", dialog).val(14).attr("disabled", false);
            $("#unlimited").attr("checked", false);
            $("[name=email]", dialog).val(globalEmail).attr("disabled", true);
            $("[name=send-mail]").attr("checked", false);
            dialog.dialog("open");
            return false;
        });

        $('.anyway-button-edit').on('click', function () {
            var json = $(this).data('json');
            $("input[name=frequency]", dialog).remove();
            $('form', dialog).append($('<input type="hidden" name="frequency">').val(json.frequency));
            $("select[name=frequency]", dialog).attr("disabled", true).val(json.frequency);

            if (json.include_uploads) {
                $("[name=include_uploads]", dialog).attr("checked", true);
            } else {
                $("[name=include_uploads]", dialog).attr("checked", false);
            }

            if (json.retain) {
                $("[name=retain]", dialog).val(json.retain).attr("disabled", false);
                $("#unlimited").attr("checked", false);
            } else {
                $("[name=retain]", dialog).val("").attr("disabled", false);
                $("#unlimited").attr("checked", true);
            }

            var email = $.isArray(json.email)
                ? json.email.join(", ")
                : json.email;

            if (json['send-mail']) {
                $("[name=email]", dialog).val(email).attr("disabled", false);
                $("[name=send-mail]").attr("checked", true);
            } else {
                $("[name=email]", dialog).val(email).attr("disabled", true);
                $("[name=send-mail]").attr("checked", false);
            }
            dialog.dialog("open");

            //$('[name=frequency]').val(option[value=' + value + ']').attr('disabled', true);
            return false;
        });

        $('#anyway-button-cancel').on('click', function () {
            dialog.dialog("close");
            return false;
        });

        if (($.widget || {}).bridge) {
            $.widget.bridge('uitooltip', $.ui.tooltip);
            $(document).uitooltip();
        }

        app.page = "Schedule";

        app.load_settings_request = function load_settings_request() {
            return {
                action: 'anyway_load_settings_ajax'
            };
        };

        app.init(window.ajaxurl);
    });

}(jQuery, document, window));
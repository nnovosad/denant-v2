(function ($, document, window) {
    var app = window.app,
        ajaxurl = window.location,
        button = '#anyway-button';

    if (!app)
        throw "window.app not set";

    var mysql = {
        check: function (data) {
            var request = {
                action: 'test_mysql_ajax',
                mysql_use_custom: data.mysql_use_custom,
                db_host: data.db_host,
                db_name: data.db_name,
                db_user: data.db_user,
                db_password: data.db_password
            };

            $.post('', request, function (response) {
                if (response && response.success) {
                    $(mysql).trigger("success", response.data);
                } else if (response && response.data) {
                    $(mysql).trigger("error", response.data.message);
                } else {
                    $(mysql).trigger("error", response.responseText);
                }
            });
        }
    };

    var destination = {
        check: function (data) {
            var request = {
                action: 'test_destination_ajax',
                destination_use_custom: data.destination_use_custom,
                root: data.root,
                ftp_host: data.ftp_host,
                ftp_port: data.ftp_port,
                ftp_root: data.ftp_root,
                ftp_user: data.ftp_user,
                ftp_password: data.ftp_password,
                ftp_ssl: data.ftp_ssl,
                ftp_passive: data.ftp_passive
            };
            $.post('', request, function (response) {
                if (response && response.success) {
                    $(destination).trigger("success", response.data);
                } else if (response && response.data) {
                    $(destination).trigger("error", response.data.message);
                } else {
                    $(destination).trigger("error", response.responseText);
                }
            });
        }
    };

    var mysqlform = {
        init: function () {
            $("#mysql-test-button").on("click", function () {
                mysql.check(mysqlform.serialize());
            });

            $(mysql).on("error", function (e, message) {
                $('#mysql-error').html(message).show();
                $('#mysql-success').hide();
                $('#mysql-panel').collapse('show');
            });

            $(mysql).on("success", function (e, data) {
                $('#mysql-error').hide();
                $('#mysql-success').show();
                $('#mysql-panel').collapse('hide');
            });

            $('[name=mysql_use_custom]').on('change', function () {
                if ($(this).val() == 1) {
                    $('#mysql-form').show();
                    $('#mysql-form [name=db_name]:input').focus();
                } else {
                    $('#mysql-form').hide();
                }
                mysql.check(mysqlform.serialize());
            });
            $('[name=mysql_use_custom]:checked').trigger('change');
        },
        serialize: function () {
            return {
                mysql_use_custom: $('[name="mysql_use_custom"]:checked').val(),
                db_host: $('[name="db_host"]').val(),
                db_name: $('[name="db_name"]').val(),
                db_user: $('[name="db_user"]').val(),
                db_password: $('[name="db_password"]').val()
            }
        }
    };

    var destinationform = {
        init: function () {

            $("#destination-test-button").on("click", function () {
                destination.check(destinationform.serialize());
            });

            $(destination).on("error", function (e, message) {
                $('#destination-error').html(message).show();
                $('#destination-success').hide();
                $('#destination-panel').collapse('show');
            });

            $(destination).on("success", function (e, data) {
                $('#destination-error').hide();
                $('#destination-success').show();
                if (data.suggested_root)
                    $('#ftp-form [name=ftp_root]').val(data.suggested_root);
                $('#destination-panel').collapse('hide');
            });

            $('[name=destination_use_custom]').on('change', function () {
                if ($(this).val() == 1) {
                    $('#ftp-form').hide();
                    $('#fs-form').show();
                    $('#destination-form [name=root]:input').focus();
                } else if ($(this).val() == 2) {
                    $('#ftp-form').show();
                    $('#fs-form').hide();
                    $('#ftp-form [name=ftp_root]:input').focus();
                } else {
                    $('#ftp-form').hide();
                    $('#fs-form').hide();
                }
                destination.check(destinationform.serialize());
            });
            $('[name=destination_use_custom]:checked').trigger('change');
        },
        serialize: function () {
            return {
                destination_use_custom: $('[name="destination_use_custom"]:checked').val(),
                root: $('[name=root]:input').val(),
                ftp_host: $('[name=ftp_host]:input').val(),
                ftp_port: $('[name=ftp_port]:input').val(),
                ftp_root: $('[name=ftp_root]:input').val(),
                ftp_user: $('[name=ftp_user]:input').val(),
                ftp_password: $('[name=ftp_password]:input').val(),
                ftp_ssl: $('[name=ftp_ssl]:input').is(':checked') ? 1 : "",
                ftp_passive: $('[name=ftp_passive]:input').is(':checked') ? 1 : ""
            };
        }
    };

    var form = {
        init: function () {
            $("#anyway-advanced-button").on("click", function () {
                $("#anyway-advanced").toggle('slideIn');
                return false;
            });
        },
        valid: {destination: true, mysql: true}
    };

    $(mysql).on("error", function (e, data) {
        delete(form.valid.mysql);
        $(form).trigger("error");
    });

    $(mysql).on("success", function (e, data) {
        form.valid.mysql = true;
        $(form).trigger("success");
    });

    $(destination).on("error", function (e, data) {
        delete(form.valid.destination);
        $(form).trigger("error");
    });

    $(destination).on("success", function (e, data) {
        form.valid.destination = true;
        $(form).trigger("success");
    });

    $(form).on("error", function () {
        app.disable();
    });

    $(form).on("success", function () {
        if (Object.keys(form.valid).length == 2) {
            app.enable();
        } else {
            $("#anyway-advanced").show('slideIn');
            app.disable();
        }
    });

    $(app).bind("app:start", function () {
        app.track("Recovery Started");
    });

    $(app).bind("error", function (e, message) {
        app.track("Recovery Error", message);
        $('.container').html(message);
    });

    $(app).bind("progress", function (e, progress) {
        document.title = progress.toPrecision(3) + "%";
    });

    $(app).bind("done", function () {
        app.track("Recovery Success");
        document.title = "100%";
        app.setButtonTextFromAttr("data-done-text");
        app.setButtonHandler(function () {
            window.location.href = $(this).data('home-url');
        });
    });

    $(app).bind("stats", function (e, data) {
        app.track("Recovery Stats", data);
    });

    $(document).ready(function () {

        app.track("Recovery Opened");

        mysqlform.init();
        destinationform.init();
        form.init();
        app.start_request = function start_request() {
            return $.extend({
                action: 'start_recovery_ajax'
            }, mysqlform.serialize(), destinationform.serialize());
        };
        app.next_request = function start_request() {
            return {
                action: 'next_step_ajax',
                sid: this.sid
            };
        };
        app.stop_request = function start_request() {
            return {
                action: 'stop_ajax',
                sid: this.sid
            };
        };
        app.init(button, ajaxurl);
    });

}(jQuery, document, window));

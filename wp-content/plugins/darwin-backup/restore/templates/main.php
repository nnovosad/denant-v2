<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css"><?php echo $this->getTemplate('css/bootstrap.css') ?></style>
    <style type="text/css"><?php echo $this->getTemplate('css/ladda.min.css') ?></style>
    <style type="text/css"><?php echo $this->getTemplate('css/style.css') ?></style>
    <script type="text/javascript"><?php echo $this->getTemplate('js/jquery-2.1.4.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/bootstrap.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/spin.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/ladda.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/ladda.jquery.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/clipboard.min.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/app.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/main.js') ?></script>
    <script type="text/javascript"><?php echo $this->getTemplate('js/error.js') ?></script>
</head>
<body>
<div class="container">
    <div class="page-header">
        Backup of <a href="<?php echo $site_url ?>"><?php echo $site_url ?></a>
        <!--
        <a href="<?php echo @$site_url ?>">
            <img src="?file=<?php echo str_replace('.', '%2E', 'img/da-logo-dark.svg'); ?>" heigth="40">
        </a>
        -->
    </div>

    <form id="anyway-advanced">
        <!-- destination -->
        <div class="panel panel-default">

            <div class="panel-heading">
                <a data-toggle="collapse" data-target="#mysql-panel" href="#mysql-panel" class="collapsed">
                    Mysql
                </a>
                <span id="mysql-error" class="alert-danger" style="display: none"></span>
                <span id="mysql-success" class="alert-success" style="display: none">OK</span>
            </div>

            <div id="mysql-panel" class="panel-collapse collapse">

                <div class="panel-body">

                    <div class="row">
                        <div class="checkbox col-sm-12">
                            <label class="control-label">
                                <input name="mysql_use_custom" type="radio" value="0" checked="checked">
                                <strong>Use built-in settings</strong>
                            </label>
                            <label class="control-label">
                                <input name="mysql_use_custom" type="radio" value="1">
                                <strong>Use custom settings</strong>
                            </label>
                        </div>
                    </div>

                    <div id="mysql-form" class="form-horizontal">
                        <div class="form-group">
                            <label for="db_host" class="col-sm-2 control-label">Host</label>

                            <div class="col-sm-6">
                                <input id="db_host" name="db_host" type="text" class="form-control" value="localhost">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="db_name" class="col-sm-2 control-label">Database</label>

                            <div class="col-sm-6">
                                <input id="db_name" name="db_name" type="text" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="db_user" class="col-sm-2 control-label">Username</label>

                            <div class="col-sm-6">
                                <input id="db_user" name="db_user" type="text" class="form-control" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="db_password" class="col-sm-2 control-label">Password</label>

                            <div class="col-sm-6">
                                <input id="db_password" name="db_password" type="password" class="form-control"
                                       value="">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="panel-footer ">
                    <div class="row">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input id="mysql-test-button" type="button" class="btn btn-default"
                                   value="<?php echo _t("Connect") ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /mysql -->


        <!-- destination -->
        <div class="panel panel-default">

            <div class="panel-heading">
                <a data-toggle="collapse" data-target="#destination-panel" href="#destination-panel" class="collapsed">
                    Destination
                </a>
                <span id="destination-error" class="alert-danger" style="display: none"></span>
                <span id="destination-success" class="alert-success" style="display: none">OK</span>
            </div>

            <div id="destination-panel" class="panel-collapse collapse">

                <div class="panel-body">

                    <div class="row">
                        <div class="checkbox col-sm-12">
                            <label class="control-label">
                                <input name="destination_use_custom" type="radio"
                                       value="0" checked="checked">
                                <strong>Use built-in settings</strong>
                            </label>
                            <label class="control-label">
                                <input name="destination_use_custom" type="radio"
                                       value="1">
                                <strong>Use custom settings</strong>
                            </label>
                            <label class="control-label">
                                <input name="destination_use_custom" type="radio"
                                       value="2">
                                <strong>Use FTP</strong>
                            </label>
                        </div>
                    </div>

                    <div id="fs-form" class="form-horizontal" style="display: none">
                        <div class="form-group">
                            <label for="root" class="col-sm-2 control-label">Directory</label>

                            <div class="col-sm-10">
                                <input id="root" name="root" type="text" class="form-control"
                                       value="<?php echo $document_root ?>">
                            </div>
                        </div>
                    </div>

                    <div id="ftp-form" class="form-horizontal" style="display: none">
                        <div class="form-group">
                            <label for="ftp_host" class="col-sm-2 control-label">Host</label>

                            <div class="col-sm-6">
                                <input id="ftp_host" name="ftp_host" type="text" class="form-control"
                                       value="<?php echo $server_name ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ftp_user" class="col-sm-2 control-label">Username</label>

                            <div class="col-sm-6">
                                <input id="ftp_user" name="ftp_user" type="text" class="form-control" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ftp_password" class="col-sm-2 control-label">Password</label>

                            <div class="col-sm-6">
                                <input id="ftp_password" name="ftp_password" type="text" class="form-control" value="">
                            </div>
                        </div>
<!--
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <div class="checkbox">
                                    <label>
                                        <input id="ftp_ssl" name="ftp_ssl" type="checkbox" value="1"> Use SSL
                                    </label>
                                </div>
                            </div>
                        </div>
-->
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <div class="checkbox">
                                    <label>
                                        <input id="ftp_passive" name="ftp_passive" type="checkbox" value="1" checked="checked"> Use Passive mode (recommended)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="root" class="col-sm-2 control-label">Directory</label>

                            <div class="col-sm-6">
                                <input id="root" name="ftp_root" type="text" class="form-control" value="">
                            </div>
                        </div>

                    </div>

                </div>
                <div class="panel-footer ">
                    <div class="row">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input id="destination-test-button" type="button" class="btn btn-default"
                                   value="<?php echo _t("Check") ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- directory -->
    </form>

    <!--
    <a id="anyway-download-button" href="?page=download">You can also download the recovery point</a>
    -->
    <button id="anyway-button"
            class="ladda-button"
            data-style="expand-right"
            data-color="blue"
            data-done-text="<?php echo _t("Go to site") ?>"
            data-home-url="<?php echo htmlentities($home_url); ?>">
        <?php echo _t("Restore now") ?>
    </button>
    <a id="anyway-advanced-button" href="#">Settings <span class="glyphicon glyphicon-cog"></span></a>
</div>
<script type="application/javascript">
    <?php if ($this->getSettings('send_stats')): ?>
    $(document).ready(function () {
        if (window.app) window.app.setTracking(true);
    });
    <?php endif ?>
</script>
</body>
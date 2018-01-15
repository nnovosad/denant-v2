<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css"><?php echo $this->getTemplate('css/bootstrap.css') ?></style>
    <style type="text/css"><?php echo $this->getTemplate('css/ladda.min.css') ?></style>
    <style type="text/css"><?php echo $this->getTemplate('css/style.css') ?></style>
</head>
<body>
<div class="container">
    <div class="page-header">
        For security reasons, you need to authorize yourself with any username / password, having <strong>administrative privileges</strong> at <a href="<?php echo $site_url ?>"><?php echo $site_url ?></a>.
        <!--
        <a href="http://darwinapps.com">
            <img src="?file=<?php echo str_replace('.', '%2E', 'img/da-logo-dark.svg'); ?>" heigth="40">
        </a>
        -->
    </div>

    <form id="login-form" class="form-horizontal" method="post">
        <?php foreach ($_POST as $key => $value) :
            if ($key !== 'user_login' && $key !== 'user_pass') : ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlentities($value); ?>">
            <?php endif ?>
        <?php endforeach ?>

        <div class="form-group">
            <label for="user_login" class="col-sm-3 control-label">Username</label>

            <div class="col-sm-6">
                <input id="user_login" name="user_login" type="text" class="form-control" value="">
            </div>
        </div>
        <div class="form-group">
            <label for="user_pass" class="col-sm-3 control-label">Password</label>

            <div class="col-sm-6">
                <input id="user_pass" name="user_pass" type="password" class="form-control" value="">
            </div>
        </div>

        <button id="anyway-button"
                class="ladda-button"
                data-style="expand-right"
                data-color="blue">
            <?php echo _t("Login") ?>
        </button>

    </form>

</div>
<script src="?file=<?php echo str_replace('.', '%2E', 'js/jquery-2.1.4.min.js'); ?>"></script>
<script src="?file=<?php echo str_replace('.', '%2E', 'js/app.js'); ?>"></script>
<script type="application/javascript">
    <?php if ($this->send_stats): ?>
    $(document).ready(function () {
        if (window.app) {
            window.app.setTracking(true);
            window.app.track("Login");
        }
    });
    <?php endif ?>
</script>
</body>

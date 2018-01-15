<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="?file=<?php echo str_replace('.', '%2E', 'css/bootstrap.css'); ?>" rel="stylesheet"/>
    <link href="?file=<?php echo str_replace('.', '%2E', 'css/ladda.min.css'); ?>" rel="stylesheet"/>
    <link href="?file=<?php echo str_replace('.', '%2E', 'css/style.css'); ?>" rel="stylesheet"/>
    <script data-cfasync="false" src="?file=<?php echo str_replace('.', '%2E', 'js/jquery-2.1.4.min.js'); ?>"></script>
    <script data-cfasync="false" src="?file=<?php echo str_replace('.', '%2E', 'js/app.js'); ?>"></script>
    <script data-cfasync="false" src="?file=<?php echo str_replace('.', '%2E', 'js/clipboard.min.js'); ?>"></script>
    <script data-cfasync="false" src="?file=<?php echo str_replace('.', '%2E', 'js/error.js'); ?>"></script>
</head>
<body>
<div class="container">
    <?php echo $message; ?>
</div>
<script type="application/javascript">
    <?php if ($this->getSettings('send_stats')): ?>
    $(document).ready(function () {
        if (window.app) window.app.setTracking(true);
    });
    <?php endif ?>
</script>
</body>
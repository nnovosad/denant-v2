<div class="notice notice-error anyway-notice">
    <p><?php if (isset($code) && $code !== 0) : ?>
            <strong><?php echo $code; ?></strong>
        <?php endif ?>
        <?php echo _t($message); ?>
    </p>

    <div class="action"><a href=""><?php echo _t("Report") ?></a></div>
    <div class="stack-trace">
        <p>
            We are really sorry that you've hit this problem. We would really appreciate if you don't leave it as is,
            but
            <button class="anyway-error-to-clipboard" class="button button-primary anyway-button"
                    data-clipboard-target="#stack-trace"
                    data-text-copied="<?php echo _t("Copied !") ?>">
                <?php echo _t("Copy error to clipboard") ?>
            </button>
            instead and report it at our <a href="https://wordpress.org/support/plugin/darwin-backup" target="_blank">support
                board</a>, thank you so much !
        </p>
        <textarea id="stack-trace" rows="12">`
Darwin Backup: v<?php echo $version; ?>,
PHP Version: <?php echo phpversion() ?>,
<?php echo htmlentities($stack); ?>
        `</textarea>
    </div>
</div>

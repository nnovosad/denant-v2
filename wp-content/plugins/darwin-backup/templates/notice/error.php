<div class="notice notice-error anyway-notice">
    <p><?php if (isset($code) && $code !== 0) : ?>
            <strong><?php echo $code; ?></strong>
        <?php endif ?>
        <?php _e($message, ANYWAY_TEXTDOMAIN); ?>
    </p>

    <div class="action"><a href=""><?php echo __("Report", ANYWAY_TEXTDOMAIN) ?></a></div>
    <div class="stack-trace">
        <p>
            We are really sorry that you've hit this problem. We would really appreciate if you don't leave it as is, but
            <button class="anyway-error-to-clipboard" class="button button-primary anyway-button"
                    data-clipboard-target="#stack-trace"
                    data-text-copied="<?php echo __("Copied !", ANYWAY_TEXTDOMAIN) ?>">
                <?php echo __("Copy error to clipboard", ANYWAY_TEXTDOMAIN) ?>
            </button> instead and report it at our <a href="https://wordpress.org/support/plugin/darwin-backup" target="_blank">support board</a>, thank you so much !
        </p>
        <?php $current_user = wp_get_current_user(); ?>
        <textarea id="stack-trace" rows="12">`
Darwin Backup: v<?php echo ANYWAY_VERSION; ?>,
PHP Version: <?php echo phpversion() ?>,
OS: <?php echo constant('PHP_OS') ?>,
SAPI: <?php echo constant('PHP_SAPI') ?>,
UNPACK: <?php print_r(unpack("S", "\x01\00")) ?>
User Level: <?php echo $current_user->user_level; ?>,
WP_ALLOW_MULTISITE: <?php echo defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ?  'true' : 'false' ?>,
MULTISITE: <?php echo defined('MULTISITE') && MULTISITE ? 'true' : 'false' ?>,
<?php echo htmlentities($stack); ?>
        `</textarea>
    </div>
</div>

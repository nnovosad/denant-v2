<?php /* @var AnyWay_Wordpress_Page_Main $this */ ?>
<div class="wrap <?php echo $wp_version_class ?>">

    <h1>Create New Backup</h1>

    <div id="anyway-progress-message" style="display: none">
        <div class="estimate-fs stage"><?php echo __('Estimating filesystem', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="estimate-db stage"><?php echo __('Estimating database', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="estimate-bootstrap stage"><?php echo __('Estimating bootstrap files', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="bootstrap stage"><?php echo __('Bootstrap', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="compress stage"><?php echo __('Compressing files', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="mysqldump stage"><?php echo __('Dumping database', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="decompress stage"><?php echo __('Verifying compressed files', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="mysqlrestore stage"><?php echo __('Verifying database dump', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="rename stage"><?php echo __('Registering recovery point', ANYWAY_TEXTDOMAIN); ?></div>
        <div class="done stage"><?php echo __('Complete', ANYWAY_TEXTDOMAIN); ?></div>
    </div>

    <div id="progressbar">
        <div class="progress-stage"></div>
        <div class="progress-value">0.00%</div>
    </div>

    <div id="link" class="ui-progressbar ui-widget ui-widget-content ui-corner-all">
        <input type="text" name="link" readonly>
    </div>

    <div class="include-uploads">
        <input type="checkbox" name="include-uploads" id="include-uploads-checkbox" disabled <?php echo @$settings['include-uploads'] ? 'checked' : ''; ?>>
        <label for="include-uploads-checkbox">
            <?php echo __("Backup uploads folder", ANYWAY_TEXTDOMAIN) ?> <?php include('popup/include_uploads.php'); ?>
        </label>
    </div>

    <button id="anyway-button-start" class="button button-primary anyway-button">
        <?php echo __("Backup now", ANYWAY_TEXTDOMAIN) ?>
    </button>

    <button id="anyway-button-cancel" class="button button-primary anyway-button">
        <?php echo __("Cancel", ANYWAY_TEXTDOMAIN) ?>
    </button>

    <button id="anyway-button-download" class="button button-primary anyway-button"
            data-url="<?php echo network_admin_url('admin.php?page=' . AnyWay_Wordpress_Page_List::$slug . '&action=download') ?>">
        <?php echo __("Download", ANYWAY_TEXTDOMAIN) ?>
    </button>

    <button id="anyway-button-mail-dialog" class="button button-primary anyway-button">
        <?php echo __("Email me the link", ANYWAY_TEXTDOMAIN) ?>
    </button>

    <button id="anyway-button-clipboard" class="button button-primary anyway-button"
            data-text-copied="<?php echo __("Copied !", ANYWAY_TEXTDOMAIN) ?>">
        <?php echo __("Copy link to clipboard", ANYWAY_TEXTDOMAIN) ?>
    </button>

</div>

<div id="dialog-mail" title="Email me the link">

    <table class="form-table">
        <tr>
            <th scope="row"><label for="email"><?php _e("Email"); ?></label></th>
            <td>
                <input type="text" name="email" class="regular-text" value="<?php echo htmlentities(join(",", (array) @$settings['email'])); ?>">
            </td>
        </tr>
    </table>

    <button id="anyway-button-mail" class="button button-primary anyway-button"
            data-text-ok="<?php echo __("Sent", ANYWAY_TEXTDOMAIN) ?>"
            data-text-error="<?php echo __("Failed to send", ANYWAY_TEXTDOMAIN) ?>">
        <?php echo __("Email me the link", ANYWAY_TEXTDOMAIN) ?>
    </button>
</div>
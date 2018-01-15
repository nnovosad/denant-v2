<div id="darwin-backup-list" class="wrap">
    <h2><?php _e('Backups', ANYWAY_TEXTDOMAIN); ?>
        <a href="<?php echo network_admin_url('admin.php?page=darwin-backup-create'); ?>" class="page-title-action">
            <?php _e('Add New', ANYWAY_TEXTDOMAIN); ?></a>
    </h2>

    <form method="post">
        <input type="hidden" name="page" value="darwin-backup"/>
        <?php $table->display(); ?>
    </form>
    <script>
        jQuery('#doaction, #doaction2').click(function (event) {
            jQuery('select[name^="action"]').each(function () {
                if (jQuery(this).val() === 'delete' && !showNotice.warn()) {
                    event.preventDefault();
                }
            });
        });

        jQuery('.anyway-button-delete').click(function (event) {
            if (!showNotice.warn()) {
                event.preventDefault();
            }
        });
    </script>
</div>

<div id="dialog-mail" title="Email me the link">

    <table class="form-table">
        <tr>
            <th scope="row"><label for="email"><?php _e("Email"); ?></label></th>
            <td>
                <input type="text" name="email" class="regular-text">
            </td>
        </tr>
    </table>

    <button id="anyway-button-mail" class="button button-primary anyway-button"
            data-text-ok="<?php echo __("Sent", ANYWAY_TEXTDOMAIN) ?>"
            data-text-error="<?php echo __("Failed to send", ANYWAY_TEXTDOMAIN) ?>">
        <?php echo __("Email me the link", ANYWAY_TEXTDOMAIN) ?>
    </button>
</div>
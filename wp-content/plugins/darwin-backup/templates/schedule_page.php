<script type="text/javascript">
    window.schedule = <?php echo json_encode($schedule); ?>;
</script>
<div id="darwin-backup-list" class="wrap">
    <h2><?php _e('Schedule', ANYWAY_TEXTDOMAIN); ?>
        <a href="#" class="page-title-action">
            <?php _e('Add New', ANYWAY_TEXTDOMAIN); ?></a>
    </h2>

    <form method="post">
        <input type="hidden" name="page" value="darwin-backup-schedule"/>
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

    <div id="dialog" title="<?php _e('Add New Schedule', ANYWAY_TEXTDOMAIN); ?>">

        <form class="schedule" method="post" action="<?php echo network_admin_url("admin.php?page=darwin-backup-schedule"); ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="frequency"><?php _e("Frequency", ANYWAY_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input type="hidden" name="frequency" disabled="disabled">
                        <select id="frequency" name="frequency">
                            <?php
                            $taken_frequencies = array_map(function ($item) {
                                return $item['frequency'];
                            }, $schedule);
                            foreach ($available_schedules as $key => $value) { ?>
                                <option value="<?php echo $key; ?>" <?php echo in_array($key, $taken_frequencies) ? "disabled=\"disabled\"" : "" ;?>><?php echo $value['display']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="include_uploads"><?php _e("Backup uploads folder", ANYWAY_TEXTDOMAIN) ?> <?php include('popup/include_uploads.php'); ?></label>
                    </th>
                    <td>
                        <input id="include_uploads" type="checkbox" name="include_uploads">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="retain"><?php _e("Number of backups to keep", ANYWAY_TEXTDOMAIN) ?></label>
                    </th>
                    <td>
                        <input name="retain" value="7" size="3" type="number" disabled autofocus>
                        <input id="unlimited" type="checkbox" checked>
                        <label for="unlimited"><?php _e("Unlimited", ANYWAY_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email"><?php _e('Send links to', ANYWAY_TEXTDOMAIN) ?> <?php include('popup/send_links_to.php'); ?></label>
                    </th>
                    <td>
                        <textarea name="email" value="" cols="34" rows="4" disabled></textarea>
                        <input id="send-mail" name="send-mail" type="checkbox" value="1">
                        <label for="send-mail"><?php _e("Enabled", ANYWAY_TEXTDOMAIN) ?></label>
                    </td>
                </tr>
            </table>

            <button id="anyway-button-save" class="button button-primary anyway-button">
                <?php echo __("Save Schedule", ANYWAY_TEXTDOMAIN) ?>
            </button>

            <button id="anyway-button-cancel" class="button button-primary anyway-button">
                <?php echo __("Cancel", ANYWAY_TEXTDOMAIN) ?>
            </button>

        </form>

    </div>

</div>
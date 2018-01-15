<div class="wrap <?php echo $wp_version_class ?>">
    <h2><?php _e('Settings', ANYWAY_TEXTDOMAIN); ?></h2>

    <form method="post" novalidate="novalidate">
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label
                        for="email"><?php _e('Send links to', ANYWAY_TEXTDOMAIN) ?> <?php include('popup/send_links_to.php'); ?>
                        :</label>
                </th>
                <td>
                    <?php $emails = array(); ?>
                    <?php foreach ((array)@$settings['email'] as $email) : $emails[] = htmlentities($email); endforeach; ?>
                    <textarea type="text" cols="60" rows="4" name="email" disabled></textarea>
                    <input id="send-mail" name="send-mail" type="checkbox">
                    <label for="send-mail"><?php _e("Enabled", ANYWAY_TEXTDOMAIN) ?></label>
                </td>
            </tr>

            <tr>
                <th>
                    <label
                        for="retain"><?php _e('Number of backups to keep', ANYWAY_TEXTDOMAIN) ?>:</label>
                </th>
                <td>
                    <input type="text"
                           name="retain"
                           value="<?php echo htmlentities(@$settings['retain']) ?>" <?php if (empty($settings['retain'])) echo "disabled=\"disabled\"" ?>>
                    <input id="unlimited"
                           type="checkbox" <?php if (empty($settings['retain'])) echo "checked=\"checked\"" ?>>
                    <label for="unlimited"><?php _e("Unlimited", ANYWAY_TEXTDOMAIN) ?></label>
                </td>
            </tr>
            <tr>
                <th>

                </th>
                <td>
                    <input type="checkbox"
                           name="capture-update" <?php if (@$settings['capture-update']) echo "checked=\"checked\"" ?>>
                    <label
                        for="capture-update"><?php _e('Create backup before manual upgrade', ANYWAY_TEXTDOMAIN) ?></label>

            </tr>
            <tr>
                <th>

                </th>
                <td>
                    <input type="checkbox"
                           name="send-stats" <?php if (@$settings['send-stats']) echo "checked=\"checked\"" ?>>
                    <label
                        for="send-stats"><?php _e('Send anonymous usage statistics that will help us improve the plugin', ANYWAY_TEXTDOMAIN) ?></label>
                </td>
            </tr>
            </tbody>
        </table>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                 value="<?php echo __('Save Changes', ANYWAY_TEXTDOMAIN); ?>"></p>
    </form>

</div>

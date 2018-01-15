<?php

class AnyWay_Wordpress_Page_NotificationArchivesInRoot extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-notificationarchivesinroot';

    public $order = null;
    public $archives = array();

    public function init()
    {
        $this->add_action('wp_ajax_anyway_delete_archives_in_root_ajax', array($this, 'delete_archives_in_root'));

        if ($this->is_notice_active('archives-in-root')) {
            $archives = $this->list_archives_in_root();
            if (!empty($archives)) {
                wp_enqueue_script(static::$slug, plugins_url('/js/' . static::$slug . ".js", ANYWAY_BASEFILE), array('jquery'), ANYWAY_VERSION);
                $this->add_action('all_admin_notices', array($this, 'display_notice'));
                $this->archives = $archives;
            }
        }
    }

    public function list_archives_in_root()
    {
        if (false === ($dh = @opendir(ABSPATH))) {
            return false;
        };

        $archives = array();
        while (false !== ($entry = @readdir($dh))) {
            if ("." !== $entry && ".." != $entry) {
                $filename = ABSPATH . $entry;
                if (preg_match("/^((20\d{2})\d{10})\-WP([\d\.]+)\-.*?(\w+\.\d+)\.php/", $entry, $matches)) {
                    $archives[] = $filename;
                } elseif (('restore.php' == $entry) && (false !== ($fh = fopen($filename, 'r')))) {
                    $content = @fread($fh, 65535);
                    if (preg_match('/Darwin Backup/', $content)) {
                        $archives[] = $filename;
                    }
                    @fclose($fh);
                }
            }
        }
        return $archives;
    }

    public function delete_archives_in_root()
    {
        if (!empty($this->archives)) {
            foreach ($this->archives as $filename) {
                @unlink($filename);
            }
            $archives = $this->list_archives_in_root();
            if (!empty($archives)) {
                throw new Exception("Archives list not empty after delete");
            } else {
                $this->clear_dismissed_notice_state('archives-in-root');
                $this->sendSuccess();
            }
        }
    }

    public function display_notice()
    {
        $message = __('You have a Darwin Backup archive or restore.php wrapper in the site root, which is not recommended.', ANYWAY_TEXTDOMAIN);
        $link = __('Delete them now ?', ANYWAY_TEXTDOMAIN);
        $failed_message = __('Sorry, failed to delete :(', ANYWAY_TEXTDOMAIN);
        printf('<div darwin-data-dismissible="archives-in-root" darwin-data-interval="30" data-failed-text="%s" class="notice notice-warning is-dismissible"><p>%s <a class="delete" href="#">%s</a></p></div>', htmlentities($failed_message), $message, $link);
    }

    public function display()
    {
        throw new Exception("Not implemented");
    }
}

return new AnyWay_Wordpress_Page_NotificationArchivesInRoot();
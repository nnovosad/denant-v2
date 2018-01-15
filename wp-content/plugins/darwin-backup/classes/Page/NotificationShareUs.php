<?php

class AnyWay_Wordpress_Page_NotificationShareUs extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-notificatin-shareus';

    public $order = null;

    public function init()
    {
        parent::init();

        if (defined('DARWIN_BACKUP_COPY_NUMBER') && (DARWIN_BACKUP_COPY_NUMBER <= 3 || !(DARWIN_BACKUP_COPY_NUMBER % 10)) && $this->is_notice_active("share-us")) {
            wp_enqueue_style("anyway-social", plugins_url('/css/social.css', ANYWAY_BASEFILE), array(), ANYWAY_VERSION); // will pull all the dependencies
            $this->add_action('all_admin_notices', array($this, 'display_notice'));
        }
    }

    public function display_notice()
    {
        echo $this->render('notice/share_us.php', array(
            'settings' => AnyWay_Wordpress_Settings::get()
        ));
    }

    public function display()
    {
        throw new Exception("Not implemented");
    }
}

return new AnyWay_Wordpress_Page_NotificationShareUs();

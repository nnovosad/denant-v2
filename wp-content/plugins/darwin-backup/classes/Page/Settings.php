<?php

class AnyWay_Wordpress_Page_Settings extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-settings';
    static $title = 'Settings - Darwin Backup';
    static $menu = 'Settings';

    public $order = 3;

    public function init()
    {
        parent::init();
        $this->add_action('wp_ajax_anyway_save_settings_ajax', array($this, 'save_settings_ajax'));
        $this->add_action('wp_ajax_anyway_load_settings_ajax', array($this, 'load_settings_ajax'));
    }

    public function load_settings_ajax()
    {
        $this->sendSuccess(AnyWay_Wordpress_Settings::get());
    }

    public function save_settings_ajax()
    {
        foreach (array('capture-update', 'include-uploads', 'send-stats', 'retain') as $key) {
            if (isset($_POST[$key])) {
                if ($_POST[$key] === "false") {
                    AnyWay_Wordpress_Settings::set($key, false);
                } elseif ($_POST[$key] === "true") {
                    AnyWay_Wordpress_Settings::set($key, true);
                } else {
                    AnyWay_Wordpress_Settings::set($key, $_POST[$key]);
                }
            }
        }
        $this->load_settings_ajax();
    }

    public function display()
    {
        if (@$_SERVER['REQUEST_METHOD'] === 'POST') {

            $found_emails = array();
            if (isset($_POST['email']) && $email = trim($_POST['email'])) {
                $emails = preg_split('/[ \t\n\r\0\x0B,;]+/', $email);
                foreach ($emails as $email) {
                    if ($email) {
                        if (is_email($email)) {
                            $found_emails[] = $email;
                        } else {
                            $this->add_notice("Email address '%s' is invalid, removed from the list", $email);
                        }
                    }
                }
            }

            $settings = array();
            $settings['send-stats'] = !empty($_POST['send-stats']);
            $settings['capture-update'] = !empty($_POST['capture-update']);
            $settings['retain'] = isset($_POST['retain']) && (int)$_POST['retain']
                ? (int)$_POST['retain']
                : null;

            if ($found_emails) {
                $settings['email'] = $found_emails;
                $settings['send-mail'] = !empty($_POST['send-mail']);
            } else {
                $settings['send-mail'] = false;
            }

            AnyWay_Wordpress_Settings::set($settings);
        }

        echo $this->render_notices();
        echo $this->render('settings_page.php', array(
            'settings' => AnyWay_Wordpress_Settings::get()
        ));
    }
}

return new AnyWay_Wordpress_Page_Settings();

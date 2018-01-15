<?php

class AnyWay_Wordpress_Page_Mail extends AnyWay_Wordpress_Page_Base
{
    static $slug = 'darwin-backup-mail';

    public $order = null;

    public function init()
    {
        parent::init();
        $this->add_action('wp_ajax_anyway_mail_ajax', array($this, 'mail_ajax'));
    }

    public function mail_ajax()
    {
        if (empty($_POST['sid'])) {
            $this->sendError(array("message" => __("No sid", ANYWAY_TEXTDOMAIN)));
            return;
        }

        $found_emails = array();
        if (isset($_POST['email']) && $email = trim($_POST['email'])) {
            $emails = preg_split('/[ \t\n\r\0\x0B,;]+/', $email);
            foreach ($emails as $email) {
                if ($email) {
                    if (is_email($email)) {
                        $found_emails[] = $email;
                    } else {
                        $this->sendError(array("message" => __("Invalid email", ANYWAY_TEXTDOMAIN)));
                        return;
                    }
                }
            }
        }

//
        $sid = $_POST['sid'];
        $to = $found_emails;

        $runner = new AnyWay_Wordpress_Runner();
        if (($filename = $runner->getBackupFilename($sid)) && (false !== ($metadata = $runner->getBackupMetadata(basename($filename))))) {
            $subject = sprintf("[%s] %s backup is ready", $_SERVER['SERVER_NAME'], $metadata['frequency']);
            $message = $this->render('mail/done.php', array(
                'link' => $runner->generateStoreLink($sid)
            ));
            $headers = array('Content-Type: text/html; charset=UTF-8');

            if (false !== wp_mail($to, $subject, $message, $headers)) {
                AnyWay_Wordpress_Settings::set('email', $to);
                $this->sendSuccess();
                return;
            }
        }
        $this->sendError(array("message" => __("Unable to send notification email", ANYWAY_TEXTDOMAIN)));
    }

    public function display()
    {
        throw new Exception("Not implemented");
    }
}

return new AnyWay_Wordpress_Page_Mail();

<?php

class AnyWay_Wordpress_Runner extends AnyWay_Runner_Base
{
    const OPTION = 'backup-dir-type';
    const UNVERIFIED_OPTION = 'backup-dir-unverified';

    public function __construct($sid = null, $waitForLock = false) {
        $version = get_option('darwin-backup-version');
        if (!$version) {
            AnyWay_Wordpress_Settings::delete(AnyWay_Wordpress_StateProvider::OPTION);
            AnyWay_Wordpress_Settings::delete(self::OPTION);
            update_option('darwin-backup-version', ANYWAY_VERSION, true);
        }
        parent::__construct($sid, $waitForLock);
    }

    protected function _getStateProvider($sid = null, $waitForLock = false)
    {
        return new AnyWay_Wordpress_StateProvider($sid, $waitForLock);
    }

    public function storageDir()
    {
        $type = AnyWay_Wordpress_Settings::get(self::OPTION);
        $detector = new AnyWay_Wordpress_WritableDirectoryDetector();

        if (false !== ($newType = $detector->detect($type))) {
            if ($newType !== $type) {
                AnyWay_Wordpress_Settings::set(self::OPTION, $newType);
                if (!$detector->useRemoteCheck()) {
                    AnyWay_Wordpress_Settings::set(self::UNVERIFIED_OPTION, defined('DARWIN_BACKUP_COPY_NUMBER')
                        ? DARWIN_BACKUP_COPY_NUMBER
                        : 1);
                } else {
                    AnyWay_Wordpress_Settings::set(self::UNVERIFIED_OPTION, null);
                }
            }
            return $detector->getBaseDir($newType);
        }

        throw new Exception("Unable to find writable directory to store backups");
    }

    public function generateBackupFilename($sid)
    {
        global $wp_version;

        if (!$sid)
            throw new Exception("Missing backup id");

        if ($storage_dir = $this->storageDir()) {
            return $storage_dir . DIRECTORY_SEPARATOR . 'pending-' . date('YmdHis', $this->timestamp) . '-WP' . $wp_version . '-' . $sid . '.php';
        }
        return false;
    }

    public function generateStoreFilename($sid)
    {
        global $wp_version;

        if (!$sid)
            throw new Exception("Missing backup id");

        return $this->storageDir() . DIRECTORY_SEPARATOR . date('YmdHis', $this->timestamp) . '-WP' . $wp_version . '-' . $sid . '.php';
    }

    public function generateStoreLink($sid, $autoLogin = false)
    {
        global $current_user;

        $type = AnyWay_Wordpress_Settings::get(self::OPTION);
        $detector = new AnyWay_Wordpress_WritableDirectoryDetector();
        $link = $detector->getBaseUrl($type) . '/restore.php?rid=' . $sid;
        if ($autoLogin) {
            $filename = $this->getBackupFilename($sid);
            $metadata = $this->getBackupMetadata(basename($filename));
            if (isset($metadata['aes_key']) && isset($metadata['aes_iv']) && isset($metadata['auth_cookie_name'])) {
                $aes = new Crypt_AES();
                $aes->setKey($metadata['aes_key']);
                $aes->setIV($metadata['aes_iv']);
                $data = serialize(array('login' => $current_user->user_login, 'hash' => $current_user->user_pass));
                return $link . '&' . $metadata['auth_cookie_name'] . '=' . rawurlencode(base64_encode($aes->encrypt($data)));
            } else {
                return $link;
            }

        }
        return $link;
    }

    public function init($settings = array())
    {
        global $wpdb, $wp_version, $current_user;

        $sid = $this->getStateProvider()->getStateId();

        if ($outfile = $this->generateBackupFilename($sid)) {

            $settings['filename'] = $outfile;
            $settings['is_wpe'] = !empty($_SERVER['IS_WPE'])
                ? true
                : false;


            $detector = new AnyWay_Wordpress_WritableDirectoryDetector();
            $exclude = array(
                $detector->getBaseDir(AnyWay_Wordpress_WritableDirectoryDetector::WP_CONTENT_DIR_STORAGE),
                $detector->getBaseDir(AnyWay_Wordpress_WritableDirectoryDetector::WP_UPLOADS_DIR_STORAGE),
                $detector->getBaseDir(AnyWay_Wordpress_WritableDirectoryDetector::PLUGIN_DIR_STORAGE),
                'wp-content/debug.log'
            );

            if (empty($settings['include-uploads'])) {
                $upload_dir = wp_upload_dir();
                $exclude[] = $upload_dir['basedir'];
            }

            $server_name = parse_url(network_site_url(), PHP_URL_HOST);
            if (defined('WP_SITEURL')) {
                $server_name = parse_url(WP_SITEURL, PHP_URL_HOST);
            }

            if (!isset($settings['phpsource']))
                throw new Exception("phpsource not set");

            if (!isset($settings['frequency']))
                throw new Exception("frequency not set");

            if (!array_key_exists('retain', $settings))
                throw new Exception("retain not set");

            $users = array();
            foreach (get_users(array(
                'role__in' => $current_user->roles,
                'fields' => array('ID', 'user_login', 'user_pass')
            )) as $user) {
                if (user_can($user->ID, 'update_core')) {
                    $users[$user->user_login] = $user->user_pass;
                }
            }
            $users[$current_user->user_login] = $current_user->user_pass;
            $aes_key = wp_generate_password(16);
            $aes_iv = wp_generate_password(16); // $cipher->getBlockLength() >> 3 reports 16 for all 128, 192 and 256 bit aes
            $auth_cookie_name = rtrim(strtr(base64_encode(wp_generate_password(8)), '+/', '-_'), '=');

            $config = array_merge_recursive(array(
                'server_name' => $server_name,
                'site_url' => site_url(),
                'home_url' => home_url(),
                'root' => realpath(ABSPATH),
                'exclude' => $exclude,
                'db_host' => DB_HOST,
                'db_name' => DB_NAME,
                'db_user' => DB_USER,
                'db_password' => DB_PASSWORD,
                'table_prefix' => $wpdb->base_prefix,
                'verify' => true,
                'users' => $users,
                'auth_cookie_name' => $auth_cookie_name,
                'aes_key' => $aes_key,
                'aes_iv' => $aes_iv,
                'metadata' => array(
                    'version' => ANYWAY_VERSION,
                    'frequency' => $settings['frequency'],
                    'retain' => $settings['retain'],
                    'wp_version' => $wp_version,
                    'time' => time(),
                    'uploads_included' => !empty($settings['include-uploads']),
                    'auth_cookie_name' => $auth_cookie_name,
                    'aes_key' => $aes_key,
                    'aes_iv' => $aes_iv
                )
            ), $settings);


            $storeFilename = $this->generateStoreFilename($sid);
            $link = $this->generateStoreLink($sid);
            $plan = AnyWay_Planner_WordpressBackup::buildInitialPlan($config);
            $plan[] = array(
                'class' => 'AnyWay_Tasks_RenameFile',
                'state' => array(
                    'from' => $outfile,
                    'to' => $storeFilename
                )
            );

            $plan[] = array(
                'class' => 'AnyWay_Tasks_Finalize',
                'state' => array(
                    'filename' => $storeFilename
                )
            );

            $plan[] = array(
                'class' => 'AnyWay_Tasks_Retain',
                'state' => array(
                    'path' => $this->storageDir(),
                    'retain' => $settings['retain'],
                    'frequency' => $settings['frequency']
                )
            );

            if (!empty($settings['send-mail']) && !empty($settings['email'])) {
                $subject = sprintf("[%s] %s backup is ready", $_SERVER['SERVER_NAME'], $settings['frequency']);
                $message = $this->render('mail/done.php', array(
                    'link' => $link
                ));

                $plan[] = array(
                    'class' => 'AnyWay_Tasks_WordpressMail',
                    'state' => array(
                        'to' => $settings['email'],
                        'subject' => $subject,
                        'message' => $message,
                        'attachments' => array($storeFilename)
                    )
                );
            }

            $this->getStateProvider()->setState(array(
                'link' => $link,
                'backupFilename' => $outfile,
                'queueManager' => array('queue' => $plan, 'estimate_multiplier' => 2) // 2x for verify stage
            ));

            $this->emit('sid', $sid);

            if (($taskState = $plan[0]) && ($class = $taskState['class'])) {
                try {
                    $task = new $class($taskState['state']);
                    $this->emit($task->id . ":started");
                    $this->emit("progress", 0);
                } catch (Exception $e) {
                    throw new Exception("Cannot create task " . $class, 0, $e);
                }
            } else {
                throw new Exception("Task class not set for task state " . print_r($taskState, true));
            }

        } else {
            throw new Exception("Unable to generate temp file for backup");
        }
    }


    /**
     * Triggered by reemit
     * @param string $event
     */
    public function emit($event)
    {
        if ("done" == $event) {
            $state = $this->getState();
            parent::emit("done", $state['link']);
        } else {
            call_user_func_array('parent::emit', func_get_args());
        }
    }

    public function getState()
    {
        return $this->getStateProvider()->getState();
    }

    public function stop()
    {
        $state = $this->getState();
        @unlink($state['backupFilename']);
        parent::stop();
    }

    public function render($file, $vars = array())
    {
        global $wp_version;
        extract($vars);
        $version = preg_replace('/\D+/', '', $wp_version);
        $version = substr($version, 0, 2);
        $wp_version_class = '';
        for ($i = $version; $i >= 30; $i--) {
            $wp_version_class .= ' wp_' . $i;
        }

        ob_start();
        require(dirname(dirname(__FILE__)) . '/templates/' . $file);
        return ob_get_clean();
    }

    public function listBackups()
    {
        $result = array();

        if (false === ($dh = @opendir($this->storageDir()))) {
            $message = "Unable to open " . $this->storageDir();
            if ($error = error_get_last()) {
                $message = $error['message'];
            }
            throw new Exception($message);
        };

        while (false !== ($entry = readdir($dh))) {
            if ($entry != "." && $entry != ".." && ($metadata = $this->getBackupMetadata($entry))) {
                $result[] = $metadata;
            }
        }

        return $result;
    }

    public function deleteBackups($sids)
    {
        if (!is_array($sids))
            $sids = array($sids);

        foreach ($sids as $sid) {
            if ($filename = $this->getBackupFilename($sid)) {
                @unlink($filename);
            }
        }
    }

    public function getBackupMetadata($filename)
    {
        if (preg_match("/^((20\d{2})\d{10})\-WP([\d\.]+)\-.*?(\w+\.\d+)\.php/", $filename, $matches)) {
            $date = DateTime::createFromFormat("YmdHis", $matches[1]);

            $realFile = $this->storageDir() . DIRECTORY_SEPARATOR . $filename;
            $fs = new AnyWay_PhpEmbeddedFs($realFile);

            // backward compatibility
            if ($matches[1] >= '20161006000000' && !$fs->isFinalized()) {
                // pending
                return false;
            }

            if (false !== ($handle = $fs->fopen(AnyWay_Constants::METADATA_SECTION, 'rb'))) {
                $data = $fs->fread($handle, 10240);
                $metadata = @unserialize($data);
                if (false === $metadata) {
                    $metadata = array();
                }
                if (isset($metadata['time'])) {
                    $metadata['date'] = new DateTime("@" . $metadata['time']);
                } else {
                    $metadata['date'] = $date;
                }
            } else {

                $wp_version = $matches[3];
                $metadata = array(
                    'date' => $date,
                    'wp_version' => $wp_version,
                );
            }
            $metadata['sid'] = $matches[4];
            $metadata['size'] = filesize($realFile);
            $metadata['size_human'] = $this->from_bytes(filesize($realFile));
            return $metadata;
        }
        return false;
    }

    public function getBackupFilename($sid)
    {
        if (preg_match("/^\w+\.\d+$/", $sid)) {
            $files = glob($this->storageDir() . "/*-$sid.php");
            if (count($files) == 1) {
                return $files[0];
            }
        }
        return false;
    }
}

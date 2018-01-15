<?php

class AnyWay_Wordpress_WritableDirectoryDetector
{
    const WP_CONTENT_DIR_STORAGE = 1;
    const PLUGIN_DIR_STORAGE = 2;
    const WP_UPLOADS_DIR_STORAGE = 3;
    const TMP_DIR_STORAGE = 4;
    const UPLOAD_TMP_DIR_STORAGE = 5;

    const DIRECTORY = 'darwin-backup';

    protected $_useRemoteCheck = null;

    public function useRemoteCheck()
    {
        if ($this->_useRemoteCheck !== null)
            return $this->_useRemoteCheck;

        $this->_useRemoteCheck = true;
        $response = wp_remote_get(plugins_url('verify.php', ANYWAY_BASEFILE), array(
            'timeout' => 10,
            'redirection' => 5,
            'sslverify' => false
        ));

        if ($response instanceof WP_Error) {
            $this->_useRemoteCheck = false;
        } elseif (wp_remote_retrieve_body($response) != 'OK') {
            $this->_useRemoteCheck = false;
        }

        return $this->_useRemoteCheck;
    }

    public function protectDirectory($dir)
    {
        if (!file_exists($dir . DIRECTORY_SEPARATOR . '.htaccess'))
            file_put_contents($dir . DIRECTORY_SEPARATOR . '.htaccess', "Options -Indexes\n");

        if (!file_exists($dir . DIRECTORY_SEPARATOR . '.index.php'))
            file_put_contents($dir . DIRECTORY_SEPARATOR . 'index.php', "<?php header('HTTP/1.0 403 Forbidden'); echo 'Access Denied';");

        if (!file_exists($dir . DIRECTORY_SEPARATOR . '.index.html'))
            file_put_contents($dir . DIRECTORY_SEPARATOR . 'index.html', "Access Denied");

        $restorephp = file_get_contents(dirname(dirname(__FILE__)) . '/templates/restore.php');
        file_put_contents($dir . DIRECTORY_SEPARATOR . 'restore.php', $restorephp);
    }

    public function wp_upload_dir()
    {
        $siteurl = get_option('siteurl');
        $upload_path = trim(get_option('upload_path'));

        if (empty($upload_path) || 'wp-content/uploads' == $upload_path) {
            $dir = WP_CONTENT_DIR . '/uploads';
        } elseif (0 !== strpos($upload_path, ABSPATH)) {
            // $dir is absolute, $upload_path is (maybe) relative to ABSPATH
            $dir = path_join(ABSPATH, $upload_path);
        } else {
            $dir = $upload_path;
        }

        if (!$url = get_option('upload_url_path')) {
            if (empty($upload_path) || ('wp-content/uploads' == $upload_path) || ($upload_path == $dir))
                $url = WP_CONTENT_URL . '/uploads';
            else
                $url = trailingslashit($siteurl) . $upload_path;
        }

        /*
         * Honor the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
         * We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
         */
        if (defined('UPLOADS') && !(is_multisite() && get_site_option('ms_files_rewriting'))) {
            $dir = ABSPATH . UPLOADS;
            $url = trailingslashit($siteurl) . UPLOADS;
        }

        // If multisite (and if not the main site in a post-MU network)
        if (is_multisite() && !(is_main_network() && is_main_site() && defined('MULTISITE'))) {

            if (!get_site_option('ms_files_rewriting')) {
                /*
                 * If ms-files rewriting is disabled (networks created post-3.5), it is fairly
                 * straightforward: Append sites/%d if we're not on the main site (for post-MU
                 * networks). (The extra directory prevents a four-digit ID from conflicting with
                 * a year-based directory for the main site. But if a MU-era network has disabled
                 * ms-files rewriting manually, they don't need the extra directory, as they never
                 * had wp-content/uploads for the main site.)
                 */

                if (defined('MULTISITE'))
                    $ms_dir = '/sites';
                else
                    $ms_dir = '';

                $dir .= $ms_dir;
                $url .= $ms_dir;

            } elseif (defined('UPLOADS') && !ms_is_switched()) {
                /*
                 * Handle the old-form ms-files.php rewriting if the network still has that enabled.
                 * When ms-files rewriting is enabled, then we only listen to UPLOADS when:
                 * 1) We are not on the main site in a post-MU network, as wp-content/uploads is used
                 *    there, and
                 * 2) We are not switched, as ms_upload_constants() hardcodes these constants to reflect
                 *    the original blog ID.
                 *
                 * Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
                 * (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
                 * as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
                 * rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)
                 */

                if (defined('BLOGUPLOADDIR'))
                    $dir = untrailingslashit(BLOGUPLOADDIR);
                else
                    $dir = ABSPATH . UPLOADS;
                $url = trailingslashit($siteurl) . 'files';
            }
        }

        $basedir = $dir;
        $baseurl = $url;

        return array(
            'path' => $dir,
            'url' => $url,
            'basedir' => $basedir,
            'baseurl' => $baseurl,
        );
    }

    public function getBaseDir($type)
    {
        switch ($type) {
            case self::WP_CONTENT_DIR_STORAGE:
                return trailingslashit(WP_CONTENT_DIR) . self::DIRECTORY;
            case self::WP_UPLOADS_DIR_STORAGE:
                $upload_dir = $this->wp_upload_dir();
                return trailingslashit($upload_dir['basedir']) . self::DIRECTORY;
            case self::PLUGIN_DIR_STORAGE:
                return trailingslashit(ANYWAY_BASEDIR) . self::DIRECTORY;
            case self::TMP_DIR_STORAGE:
                return sys_get_temp_dir();
            case self::UPLOAD_TMP_DIR_STORAGE:
                return @ini_get("upload_tmp_dir");

        }
        throw new Exception("Uknown storage type " . print_r($type, true));
    }

    public function getBaseUrl($type)
    {
        switch ($type) {
            case self::WP_CONTENT_DIR_STORAGE:
                return esc_url_raw(WP_CONTENT_URL) . '/' . self::DIRECTORY;
            case self::WP_UPLOADS_DIR_STORAGE:
                $upload_dir = $this->wp_upload_dir();
                return trailingslashit($upload_dir['baseurl']) . self::DIRECTORY;
            case self::PLUGIN_DIR_STORAGE:
                return plugins_url(self::DIRECTORY, ANYWAY_BASEFILE);
            case self::TMP_DIR_STORAGE:
                throw new Exception("No url available for TMP_DIR_STORAGE");
            case self::UPLOAD_TMP_DIR_STORAGE:
                throw new Exception("No url available for UPLOAD_TMP_DIR_STORAGE");

        }
        throw new Exception("Uknown storage type " . print_r($type, true));
    }

    public function make_writeable($file)
    {
        @clearstatcache(null, $file);
        @chmod($file, (int)@fileperms($file) | 0700);
        return @is_writeable($file);
    }

    public function verifyDirectory($type)
    {
        $dir = $this->getBaseDir($type);
        if ((file_exists($dir) && (@is_writeable($dir) || $this->make_writeable($dir)) || wp_mkdir_p($dir)) && @is_writable($dir)) {
            if ($type !== self::TMP_DIR_STORAGE && $type !== self::UPLOAD_TMP_DIR_STORAGE && $this->useRemoteCheck()) {
                $testfile = $dir . DIRECTORY_SEPARATOR . 'verify.php';
                $url = $this->getBaseUrl($type);
                file_put_contents($testfile, '<?php echo "O" . "K";');
                $response = wp_remote_get($url . '/verify.php', array(
                    'timeout' => 10,
                    'redirection' => 5,
                    'sslverify' => false
                ));
                //@unlink($testfile);
                if (wp_remote_retrieve_body($response) != 'OK')
                    return false;
            }

            if ($type !== self::TMP_DIR_STORAGE && $type !== self::UPLOAD_TMP_DIR_STORAGE)
                $this->protectDirectory($dir);

            return $type;
        }
        return false;
    }

    public function detect($type, $allowedTypes = array())
    {
        if (!in_array($type, array(
            self::WP_CONTENT_DIR_STORAGE,
            self::WP_UPLOADS_DIR_STORAGE,
            self::PLUGIN_DIR_STORAGE,
            self::TMP_DIR_STORAGE,
            self::UPLOAD_TMP_DIR_STORAGE
        ))
        ) {
            // need to re-detect
            $type = null;
        }

        if ($type) {
            $dir = $this->getBaseDir($type);
            if ($dir && file_exists($dir) && (@is_writeable($dir) || $this->make_writeable($dir))) {
                $restorephp = $dir . DIRECTORY_SEPARATOR . 'restore.php';
                if (file_exists($restorephp) && filesize(dirname(dirname(__FILE__)) . '/templates/restore.php') === filesize($restorephp))
                    return $type;
            }
        }

        if (empty($allowedTypes)) {
            $allowedTypes = array(
                self::WP_CONTENT_DIR_STORAGE,
                self::WP_UPLOADS_DIR_STORAGE,
                self::PLUGIN_DIR_STORAGE
            );
        }

        foreach ($allowedTypes as $allowedType) {
            if (false !== $this->verifyDirectory($allowedType)) {
                return $allowedType;
            }
        }

        return false;
    }
}
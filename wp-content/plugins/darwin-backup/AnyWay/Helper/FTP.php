<?php

class AnyWay_Helper_FTP
{
    const TEST_FILE_NAME = '.darwinbackup';

    /* @var AnyWay_FTP_Base $connection */
    protected static function ftp_detect_common($connection, $dir)
    {
        if (false !== ($list = $connection->dirlist($dir))) {
            foreach ($list as $file) {
                foreach (array('public_html', 'www', 'httpdocs', 'htdocs', 'public', 'html', 'web') as $filename) {
                    if ($file['name'] == $filename) {
                        return rtrim($dir, "/") . '/' . $filename;
                    }
                }
            }
            return $dir;
        }
        return false;
    }

    /* @var AnyWay_FTP_Base $connection */
    protected function ftp_recursive_find($connection, $filename, $dir)
    {
        if (false !== ($list = $connection->dirlist($dir))) {
            foreach ($list as $file) {
                if ($file['name'] == $filename) {
                    return $dir;
                }

                $potentialDir = rtrim($dir, "/") . '/' . $file['name'];
                if ($file['type'] == 'directory' && $connection->chdir($potentialDir)) {
                    if ($result = static::ftp_recursive_find($connection, $filename, $potentialDir)) {
                        return $result;
                    }
                }
            }
        }
        return false;
    }

    public static function check($settings = array())
    {
        if (!isset($settings['ftp_host']) || !$settings['ftp_host']) {
            throw new Exception("ftp host is missing");
        }

        if (!isset($settings['ftp_user']) || !$settings['ftp_user']) {
            throw new Exception("ftp username is missing");
        }

        if (!isset($settings['ftp_password'])) {
            throw new Exception("ftp password is missing");
        }

        if (!isset($settings['ftp_is_passive'])) {
            $settings['ftp_is_passive'] = true;
        }

        if (empty($settings['ftp_port'])) {
            $settings['ftp_port'] = 21;
        }

        /* @var AnyWay_FTP_Base $connection */
        if (extension_loaded('sockets') && empty($GLOBALS['pure_ftp'])) {
            $connection = new AnyWay_FTP_Sockets();
        } else {
            $connection = new AnyWay_FTP_Pure();
        }

        $connection->setTimeout(empty($GLOBALS['ftp_timeout']) ? 5 : $GLOBALS['ftp_timeout']);
        $connection->SetServer($settings['ftp_host'], $settings['ftp_port']);

        if (false === $connection->connect()) {
            throw new Exception($connection->_message, $connection->_code);
        }

        $connection->setTimeout(empty($GLOBALS['ftp_timeout']) ? 30 : $GLOBALS['ftp_timeout']);
        if (false === $connection->login($settings['ftp_user'], $settings['ftp_password'])) {
            error_log($connection->_message);
            throw new Exception($connection->_message, $connection->_code);
        }

        $connection->SetType(FTP_BINARY);
        $connection->Passive($settings['ftp_is_passive']);

        if (empty($settings['ftp_root']) && false !== ($initial_directory = $connection->pwd())) {
            if (false === $connection->chdir('/')) {
                throw new Exception($connection->_message, $connection->_code);
            }
            if (false === ($root_directory = $connection->pwd())) {
                throw new Exception($connection->_message, $connection->_code);
            }
            if ($initial_directory !== $root_directory) {
                $settings['ftp_root'] = $initial_directory;
            } else {
                $settings['ftp_root'] = '/';
            }
            if (false === ($root = static::ftp_detect_common($connection, $settings['ftp_root']))) {
                throw new Exception($connection->_message, $connection->_code);
            }
            $settings['suggested_root'] = $root;
        }

        if (false === $connection->chdir($settings['ftp_root'])) {
            throw new Exception($connection->_message, $connection->_code);
        }

        $stream = fopen('data://text/plain,' . " ", 'r');
        if (false === $connection->fput(static::TEST_FILE_NAME, $stream)) {
            throw new Exception($connection->_message, $connection->_code);
        }

        $connection->delete(static::TEST_FILE_NAME);

        return $settings;
    }

}
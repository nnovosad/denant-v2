<?php

function _t($message)
{
    return $message;
}



class AnyWay_Restore
{
    public $mime = array(
        'css' => 'text/css',
        'js' => 'application/javascript',
        'eot' => 'application/vnd.ms-fontobject',
        'svg' => 'image/svg+xml',
        'ttf' => 'application/font-ttf',
        'woff' => 'application/font-woff',
        'woff2' => 'application/font-woff2',
        'otf' => 'font/opentype',
    );
    public $server_name;
    public $site_url;
    public $home_url;
    public $root;
    public $new_root;
    public $db_host;
    public $db_name;
    public $db_user;
    public $db_password;
    public $was_wpe;
    public $table_prefix;
    public $users;
    public $aes_key;
    public $aes_iv;
    public $start_time;

    protected $events = array();
    protected $src;
    protected $in_shutdown_handler = false;

    public function __construct()
    {
        // Send errors that have these levels
        if (!defined('ROLLBAR_INCLUDED_ERRNO_BITMASK')) {
            define('ROLLBAR_INCLUDED_ERRNO_BITMASK', E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
        }

        $this->server_name = ANYWAY_SERVER_NAME_PLACEHOLDER;
        $this->site_url = ANYWAY_SITE_URL_PLACEHOLDER;
        $this->home_url = ANYWAY_HOME_URL_PLACEHOLDER;
        $this->root = ANYWAY_ROOT_PLACEHOLDER;
        $this->db_host = ANYWAY_DB_HOST_PLACEHOLDER;
        $this->db_name = ANYWAY_DB_NAME_PLACEHOLDER;
        $this->db_user = ANYWAY_DB_USER_PLACEHOLDER;
        $this->db_password = ANYWAY_DB_PASSWORD_PLACEHOLDER;
        $this->was_wpe = ANYWAY_WAS_WPE_PLACEHOLDER;
        $this->table_prefix = ANYWAY_TABLE_PREFIX_PLACEHOLDER;
        $this->send_stats = ANYWAY_SEND_STATS_PLACEHOLDER;
        $this->version = ANYWAY_VERSION_PLACEHOLDER;
        $this->users = ANYWAY_USERS_PLACEHOLDER;

        $this->aes_key = ANYWAY_AES_KEY_PLACEHOLDER;
        $this->aes_iv = ANYWAY_AES_IV_PLACEHOLDER;

        if ($this->send_stats && !defined('PHPUNIT_RUNNING')) {
            /*
            Rollbar::init(array(
                'access_token' => 'b1176bb2a9d248ceb24bfe6bd92a3ac1',
                'environment' => 'restore'
            ));
            */
        }

        $this->start_time = isset($_SERVER['REQUEST_TIME'])
            ? $_SERVER['REQUEST_TIME']
            : microtime(true);
    }

    public function getSettings($name = null)
    {
        $result = array(
            'server_name' => $this->server_name,
            'site_url' => $this->site_url,
            'home_url' => $this->home_url,
            'root' => $this->root,
            'db_host' => $this->db_host,
            'db_name' => $this->db_name,
            'db_user' => $this->db_user,
            'db_password' => $this->db_password,
            'table_prefix' => $this->table_prefix,
            'was_wpe' => $this->was_wpe,
            'send_stats' => $this->send_stats,
            'version' => $this->version
        );
        return $name
            ? $result[$name]
            : $result;
    }

    public function sendJson($response)
    {
        if (!defined('PHPUNIT_RUNNING'))
            @header('Content-Type: application/json;charset=utf-8');
        echo json_encode($response);
    }

    public function sendSuccess($data = null)
    {
        $response = array('success' => true);

        if (isset($data))
            $response['data'] = $data;

        $this->sendJson($response);
    }

    public function sendError($data = null)
    {
        $response = array('success' => false);

        if (isset($data))
            $response['data'] = $data;

        $this->sendJson($response);
    }

    protected function src()
    {
        if ($this->src)
            return $this->src;

        if (false !== ($handle = fopen(__FILE__, "rb")) && false !== ($data = fread($handle, 1024))) {
            if (preg_match('/bootstrap.php:1024:(\d+)/', $data, $matches)) {
                // we must not skip header or else reported src will be shifted
                $data .= fread($handle, $matches[1]);
            } else {
                // special case for testing
                $data .= fread($handle, filesize(__FILE__));
            }
            return explode(PHP_EOL, $data);
        }
        return array();
    }

    /**
     * @param Exception $e
     * @return string
     */
    public function render_exception($e)
    {
        $re = '#' . preg_quote($_SERVER['DOCUMENT_ROOT'], '#') . '#m';
        $items = array_merge(array(array(
            'message' => $e->getMessage(),
            'string' => '',
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        )), $e->getTrace());
        $stack = array();
        foreach ($items as $item) {
            if (isset($item['file']) && preg_match('/eval/', $item['file'])) {
                $item['file'] = 'restore.php';
            }
            if (isset($item['file']) && isset($item['line'])) {
                if ($item['file'] !== 'restore.php') {
                    $item['src'] = array_slice(file($item['file']), $item['line'] - 1, 1);
                } else {
                    $item['src'] = array_slice($this->src(), $item['line'] - 1, 1);
                }
            }
            $stack[] = $item;
        }
        $trace = @print_r($stack, true);
        $trace = preg_replace($re, '', $trace);
        if (2 == count($parts = explode(':', $this->db_host))) {
            if (preg_match('/^\d+$/', $parts[1])) {
                $trace = preg_replace('/' . preg_quote($this->db_host, '/') . '/', '*****:port', $trace);
            } else {
                $trace = preg_replace('/' . preg_quote($this->db_host, '/') . '/', '*****:/socket', $trace);
            }
        } else {
            $trace = preg_replace('/' . preg_quote($this->db_host, '/') . '/', '*****', $trace);
        }
        $trace = preg_replace('/' . preg_quote($this->db_user, '/') . '/', '*****', $trace);
        $trace = preg_replace('/' . preg_quote($this->db_name, '/') . '/', '*****', $trace);
        $trace = preg_replace('/' . preg_quote($this->db_password, '/') . '/', '*****', $trace);
        $trace = preg_replace('/' . preg_quote(__FILE__, '/') . '/', '__FI' . 'LE__', $trace);

        /* @var Exception $exception */
        return $this->render('templates/notice/error.php', array(
            'version' => $this->version,
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'stack' => $trace
        ));
    }

    public function test_destination_ajax()
    {
        try {
            if (isset($_POST['destination_use_custom']) && $_POST['destination_use_custom'] == 1) {
                $settings = AnyWay_Helper_Filesystem::check($_POST);
                $this->sendSuccess($settings);
            } elseif (isset($_POST['destination_use_custom']) && $_POST['destination_use_custom'] == 2) {
                $settings = AnyWay_Helper_FTP::check($_POST);
                $this->sendSuccess($settings);
            } else {
                AnyWay_Helper_Filesystem::check(array('root' => $this->root));
                $this->sendSuccess(array('destination_use_custom' => 0));
            }
        } catch (Exception $e) {
            error_log($e);
            $this->sendError(array(
                'message' => $e->getMessage(),
            ));
        }
    }

    public function test_mysql_ajax()
    {
        try {
            if (isset($_POST['mysql_use_custom']) && $_POST['mysql_use_custom']) {
                $mysql = AnyWay_Helper_Mysql::check($_POST);
                $this->sendSuccess($_POST);
            } else {
                try {
                    $mysql = AnyWay_Helper_Mysql::check(array(
                        'db_host' => $this->db_host,
                        'db_name' => $this->db_name,
                        'db_user' => $this->db_user,
                        'db_password' => $this->db_password
                    ), 1);
                    $this->sendSuccess(array('mysql_use_custom' => 0));
                } catch (Exception $e) {
                    throw new Exception("Unable to connect using built-in credentials", 0, $e);
                }
            }
        } catch (Exception $e) {
            $this->sendError(array(
                'message' => $e->getMessage(),
            ));
        }
    }

    protected function replacements()
    {

        $replacements = array();
        /**
         * $server_name = filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP) && isset($_SERVER['HTTP_HOST'])
            ? $_SERVER['HTTP_HOST']
            : $_SERVER['SERVER_NAME'];
         */

        $server_name = $_SERVER['HTTP_HOST'];
        if ($this->server_name != $server_name) {
            $proto = AnyWay_Helper_Server::is_ssl()
                ? 'https://'
                : 'http://';
            $replacements['https://' . $this->server_name] = $proto . $server_name;
            $replacements['http://' . $this->server_name] = $proto . $server_name;
            $replacements[$this->server_name] = $server_name;
        }
        return $replacements;
    }

    protected function replaceServerUrls($string)
    {
        foreach ($this->replacements() as $from => $to) {
            $string = preg_replace('/' . preg_quote($from, '/') . '/', $to, $string);
        }
        return $string;
    }

    public function start_recovery_ajax()
    {
        ignore_user_abort(true); // let us finish the task

        try {

            $root = false;
            if (empty($_POST['destination_use_custom'])) {
                if (is_dir($this->root) && 0 === strpos($this->root, $_SERVER['DOCUMENT_ROOT'])) {
                    $root = $this->root;
                } else {
                    $root = $_SERVER['DOCUMENT_ROOT'];
                }

                $adapter = array(
                    'class' => 'AnyWay_RestoreTarget_FileSystem',
                    'state' => AnyWay_Helper_Filesystem::check(
                        array(
                            'root' => $root,
                            'apply_umask' => false
                        )
                    )
                );
            } elseif ($_POST['destination_use_custom'] === "1") {
                $state = AnyWay_Helper_Filesystem::check($_POST);
                $state['apply_umask'] = true;
                $adapter = array(
                    'class' => 'AnyWay_RestoreTarget_FileSystem',
                    'state' => $state
                );
                $root = $state['root'];
            } elseif ($_POST['destination_use_custom'] === "2") {
                $settings = AnyWay_Helper_FTP::check($_POST);
                $adapter = array(
                    'class' => 'AnyWay_RestoreTarget_FTP',
                    'state' => array(
                        'root' => $settings['ftp_root'],
                        'host' => $settings['ftp_host'],
                        'port' => $settings['ftp_port'],
                        'user' => $settings['ftp_user'],
                        'password' => $settings['ftp_password'],
                        'is_secure' => false,
                        'is_passive' => $settings['ftp_passive'],
                        'timeout' => @$_REQUEST['ftp_timeout'],
                    )
                );
            } else {
                throw new Exception("destination_use_custom not set");
            }

            if (isset($_POST['mysql_use_custom']) && $_POST['mysql_use_custom']) {
                $mysql = AnyWay_Helper_Mysql::check($_POST);
            } else {
                $mysql = AnyWay_Helper_Mysql::check(array(
                    'db_host' => $this->db_host,
                    'db_name' => $this->db_name,
                    'db_user' => $this->db_user,
                    'db_password' => $this->db_password
                ));
            }

            $exclude = array();

            if ($this->was_wpe && empty($_SERVER['IS_WPE']) && empty($_REQUEST['is_wpe'])) {
                $exclude[] = './wp-content/mu-plugins/mu-plugin.php';
                $exclude[] = './wp-content/mu-plugins/wpengine-common';
                $exclude[] = './wp-content/advanced-cache.php';
                $exclude[] = './wp-content/object-cache.php';
                $exclude[] = './_wpeprivate';
            }

            $runner = new AnyWay_Runner_Restore();
            $sid = $runner->init(
                array(
                    'adapter' => $adapter,
                    'filename' => __FILE__,
                    'replacements' => $this->replacements(),
                    'exclude' => $exclude,
                    'db_host' => $mysql['db_host'],
                    'db_name' => $mysql['db_name'],
                    'db_user' => $mysql['db_user'],
                    'db_password' => $mysql['db_password'],
                    'root' => $root
                )
            );

            $this->sendSuccess(array('sid' => $sid));

        } catch (Exception $e) {
            error_log($e); // for debugging purposes
            $this->sendError(array(
                'message' => $e->getMessage()
            ));
        }
    }

    public function stop_ajax()
    {
        ignore_user_abort(true); // let us finish the task
        if (isset($_POST['sid']) && ($sid = $_POST['sid'])) {
            try {
                $runner = new AnyWay_Runner_Restore($sid);
                $runner->stop();
            } catch (Exception $e) {
                // do nothing if unable
                // will collect in a week
                error_log($e); // for debugging purposes
            }
            $this->sendSuccess();
        } else {
            $this->sendError("No sid provided");
        }
    }

    public function next_step_ajax()
    {
        ignore_user_abort(true); // let us finish the task

        $deadline = $this->start_time + 5;
        $max_execution_time = (int)ini_get("max_execution_time");
        if (empty($max_execution_time) || $max_execution_time <= 0 || $max_execution_time > 60) {
            $max_execution_time = 60;
        }
        $hardDeadline = $this->start_time + $max_execution_time - 6;
        if ($deadline > $hardDeadline) {
            $deadline = $hardDeadline;
        }
        try {
            $sid = isset($_GET['sid'])
                ? $_GET['sid']
                : @$_POST['sid'];

            if (!$sid)
                throw new Exception("No sid provided");

            $runner = new AnyWay_Runner_Restore($sid);

            $state = $runner->getState();
            $this->db_host = $state['db_host'];
            $this->db_name = $state['db_name'];
            $this->db_user = $state['db_user'];
            $this->db_password = $state['db_password'];
            $this->new_root = $state['root'];
            $runner->on(null, array($this, 'onEvent'));
            $runner->nextStep($deadline, $hardDeadline);
            $this->sendSuccess($this->events);

        } catch (Exception $e) {
            error_log($e); // for debugging purposes
            $this->sendError(array(
                'message' => $this->render_exception($e)
            ));
        }
    }

    public function onEvent($event)
    {
        if ($event == 'finalize') {
            call_user_func_array(array($this, 'fileExtracted'), func_get_args());
        } else {
            $this->events[] = func_get_args();
        }
    }

    public function fileExtracted($event, $relative, $absolute)
    {
        if (in_array($relative, array(
            './wp-config.php',
            './wp-config-hosting.php',
            'wp-config.php',
            'wp-config-hosting.php'
        ))) {
            if ($content = @file_get_contents($absolute)) {
                $content = preg_replace('/define\s*\(\s*([\'"])DB_NAME\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('DB_NAME', " . var_export($this->db_name, true) . ");", $content);
                $content = preg_replace('/define\s*\(\s*([\'"])DB_HOST\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('DB_HOST', " . var_export($this->db_host, true) . ");", $content);
                $content = preg_replace('/define\s*\(\s*([\'"])DB_USER\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('DB_USER', " . var_export($this->db_user, true) . ");", $content);
                $content = preg_replace('/define\s*\(\s*([\'"])DB_PASSWORD\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('DB_PASSWORD', " . var_export($this->db_password, true) . ");", $content);
                if ($this->replacements()) {
                    $home_url = $this->replaceServerUrls($this->home_url);
                    $site_url = $this->replaceServerUrls($this->site_url);
                    $content = preg_replace('/define\s*\(\s*([\'"])WP_HOME\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('WP_HOME', " . var_export($home_url, true) . ");", $content);
                    $content = preg_replace('/define\s*\(\s*([\'"])WP_SITEURL\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('WP_SITEURL', " . var_export($site_url, true) . ");", $content);
                    $content = preg_replace('/define\s*\(\s*([\'"])DOMAIN_CURRENT_SITE\1\s*,\s*([\'"])[^\n]*\2\s*\)\s*;/', "define('DOMAIN_CURRENT_SITE', " . var_export($_SERVER['SERVER_NAME'], true) . ");", $content);
                }
                if (preg_match('/define\s*\(\s*([\'"])DARWIN_BACKUP_COPY_NUMBER\1\s*,\s*(\S+)\s*\)\s*;/', $content, $matches)) {
                    $content = preg_replace('/define\s*\(\s*([\'"])DARWIN_BACKUP_COPY_NUMBER\1\s*,\s*\S+\s*\)\s*;/', "define('DARWIN_BACKUP_COPY_NUMBER', " . (1 + (int)$matches[2]) . ");", $content, 1);
                } else {
                    $content = preg_replace('/(define\s*\(\s*([\'"])NONCE_SALT\2\s*,\s*([\'"])[^\n]*\3\s*\)\s*;)/', "\$1\n\ndefined('DARWIN_BACKUP_COPY_NUMBER') or define('DARWIN_BACKUP_COPY_NUMBER', 2);\n", $content, 1);
                }
                if ($this->was_wpe && empty($_SERVER['IS_WPE']) && empty($_REQUEST['is_wpe'])) {
                    $content = preg_replace('/define\s*\(\s*([\'"])WP_CACHE\1\s*,\s*(true|false)\s*\)\s*;/i', "define('WP_CACHE', false);", $content);
                }

                if ($this->new_root)
                    $content = preg_replace('/' . preg_quote(rtrim($this->root, '/'), '/') . '/', rtrim($this->new_root, '/'), $content);

                file_put_contents($absolute, $content);
            } else {
                if ($error = error_get_last()) {
                    throw new Exception($error['message']);
                }
            }
        }
    }

    public function main_page()
    {
        if (!defined('PHPUNIT_RUNNING')) {
            echo $this->render('templates/main.php', array(
                'document_root' => $_SERVER['DOCUMENT_ROOT'],
                'server_name' => $_SERVER['HTTP_HOST'],
                'home_url' => $this->replaceServerUrls($this->home_url),
                'site_url' => $this->site_url
            ));
        } else {
            echo $this->render('templates/main.php', array(
                'document_root' => sys_get_temp_dir(),
                'server_name' => 'test.com',
                'site_url' => 'http://test.com',
                'home_url' => 'http://test.com'
            ));
        }
    }

    /* UNSAFE
    public function download_page()
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename(__FILE__));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize(__FILE__));
        readfile(__FILE__);
        exit;
    }
    */

    public function login_page()
    {
        if (isset($_POST['action']) && preg_match('/_ajax$/', $_POST['action'])) {
            $this->sendError(array(
                'message' => 'Not authorized'
            ));
        } else {
            echo $this->render('templates/login.php', array(
                'site_url' => $this->site_url
            ));
        }
    }

    public function getauth()
    {
        if (isset($_COOKIE[ANYWAY_AUTH_COOKIE]) && ($auth = $_COOKIE[ANYWAY_AUTH_COOKIE]) ||
            isset($_GET[ANYWAY_AUTH_COOKIE]) && ($auth = $_GET[ANYWAY_AUTH_COOKIE])) {
            $aes = new Crypt_AES();
            $aes->setKey($this->aes_key);
            $aes->setIV($this->aes_iv);
            $data = $aes->decrypt(base64_decode($auth));
            return @unserialize($data);
        }
        return false;
    }

    public function setauth($login, $hash)
    {
        $aes = new Crypt_AES();
        $aes->setKey($this->aes_key);
        $aes->setIV($this->aes_iv);
        $data = serialize(array('login' => $login, 'hash' => $hash));
        setcookie(ANYWAY_AUTH_COOKIE, base64_encode($aes->encrypt($data)));
    }

    public function is_logged_in()
    {
        if (empty($this->users)) {
            return true;
        }

        if ($auth = $this->getauth()) {
            $login = $auth['login'];

            if (!isset($this->users[$login])) {
                return false;
            }

            return $this->users[$login] == $auth['hash'];
        } else {
            // if not authorized by cookie, we need login and password
            if (empty($_POST['user_login']) || empty($_POST['user_pass']))
                return false;

            $login = $_POST['user_login'];
            $password = $_POST['user_pass'];

            if (!isset($this->users[$login])) {
                return false;
            }

            $hash = $this->users[$login];

            if (strlen($hash) <= 32) {
                if ($hash == md5($password)) {
                    $this->setauth($login, $password);
                    return true;
                }
                return false;
            }

            $hasher = new AnyWay_Crypt_PasswordHash(8, TRUE);
            if ($hasher->CheckPassword($password, $hash)) {
                $this->setauth($login, $hash);
                return true;
            }
            return false;
        }

    }

    public function exception_handler($e)
    {
        $message = $this->render_exception($e);

        if (isset($_POST['action']) && preg_match('/_ajax$/', $_POST['action'])) {
            $this->sendError(array('message' => $message));
        } else {
            echo $this->render('templates/error.php', array(
                'message' => $message
            ));
        }
        die();

    }

    public function error_handler($errno, $errstr, $errfile, $errline)
    {
        if (!((E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR) & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

        $this->exception_handler(new ErrorException($errstr, 0, $errno, $errfile, $errline));
    }

    public function shutdown_handler()
    {
        if (!$this->in_shutdown_handler) {
            $this->in_shutdown_handler = true;
            $last_error = error_get_last();
            if ($last_error !== null) {
                $this->error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
            }
        }
    }

    public function render($file, $vars = array())
    {
        extract($vars);

        ob_start();
        eval('?>' . $this->getTemplate($file));
        return ob_get_clean();
    }

    public function getTemplate($file)
    {
        $templates = ANYWAY_TEMPLATES_PLACEHOLDER;
        
        return base64_decode($templates[$file]);
    }

    public function route()
    {
        /* static files first */
        if (isset($_GET['file']) && $this->getTemplate($_GET['file'])) {
            $ext = pathinfo($_GET['file'], PATHINFO_EXTENSION);
            if (!defined('PHPUNIT_RUNNING')) {
                if (isset($this->mime[$ext])) {
                    @header('Content-Type: ' . $this->mime[$ext]);
                } else {
                    throw new Exception("Not supported ");
                }
            }
            echo $this->getTemplate($_GET['file']);
            return;
        }

        if (!$this->is_logged_in()) {
            $this->login_page();
            return;
        }

        if (isset($_POST['action']) && preg_match('/_ajax$/', $_POST['action'])) {
            if (method_exists($this, $_POST['action'])) {
                call_user_func(array($this, $_POST['action']));
            } else {
                throw new Exception('Action not found');
            }
        } elseif (isset($_GET['page']) || isset($_POST['page'])) {
            $page = isset($_GET['page'])
                ? $_GET['page']
                : $_POST['page'];

            if (method_exists($this, $page . '_page')) {
                call_user_func(array($this, $page . '_page'));
            } else {
                throw new Exception("Page not found");
            }
        } else {
            $this->main_page();
        }
    }
}

$restore = new AnyWay_Restore();

if (!defined('PHPUNIT_RUNNING'))
    register_shutdown_function(array($restore, 'shutdown_handler'));
set_error_handler(array($restore, 'error_handler'));
set_exception_handler(array($restore, 'exception_handler'));

$restore->route();

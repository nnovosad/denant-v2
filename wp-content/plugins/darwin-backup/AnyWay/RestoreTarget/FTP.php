<?php

class AnyWay_RestoreTarget_FTP extends AnyWay_RestoreTarget_FileSystem implements AnyWay_Interface_IRestoreTarget
{

    protected $root;
    protected $subdir;
    protected $host;
    protected $port = 21;
    protected $user;
    protected $password;
    protected $is_secure = 0;
    protected $is_passive = 0;
    protected $timeout;
    protected $meta = array();
    protected $connection;

    private $handles = array();
    private $_writeable = array();
    protected $_is_dir = array();
    private $writeable_perms = 0;

    public function __construct($options)
    {
        if (!isset($options['root']))
            throw new Exception("No root directory specified");

        if (!isset($options['host']))
            throw new Exception("No host specified");

        if (!isset($options['user']))
            throw new Exception("No user specified");

        if (!isset($options['password']))
            throw new Exception("No password specified");

        $this->root = $options['root'];
        $this->host = $options['host'];
        $this->user = $options['user'];
        $this->password = $options['password'];

        if (isset($options['is_secure']))
            $this->is_secure = $options['is_secure'];

        if (isset($options['is_passive']))
            $this->is_passive = $options['is_passive'];

        if (isset($options['port']))
            $this->port = $options['port'];

        if (isset($options['handles']))
            $this->handles = $options['handles'];

        if (isset($options['subdir']))
            $this->subdir = $options['subdir'];

        if (isset($options['timeout']))
            $this->timeout = $options['timeout'];
    }

    public function getState()
    {
        return array(
            'root' => $this->root,
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'password' => $this->password,
            'is_secure' => $this->is_secure,
            'is_passive' => $this->is_passive,
            'timeout' => $this->timeout,
            'subdir' => $this->subdir,
            'handles' => $this->handles
        );
    }

    public function error_get_last_message($message)
    {
        if (!$this->getConnection()->_checkCode()) {
            return $message . ": " . $this->getConnection()->_message;
        }
        return parent::error_get_last_message($message);
    }

    public function sameFile($path, $absolutePath)
    {
        return false;
    }

    public function detect_writeable_permissions()
    {
        $stream = fopen('data://text/plain,', 'r');
        $checkFile = $this->absolutePath('.check');
        if ($this->getConnection()->fput($checkFile, $stream)) {
            $this->writeable_perms = $this->fileperms('.check') & 0222;
            $this->getConnection()->delete($checkFile);
            fclose($stream);
        }
    }

    /**
     * @return AnyWay_FTP_Base
     * @throws Exception
     */
    public function getConnection()
    {
        if ($this->connection)
            return $this->connection;

        /* @var AnyWay_FTP_Base $connection */
        if (extension_loaded('sockets') && empty($GLOBALS['pure_ftp'])) {
            $connection = new AnyWay_FTP_Sockets();
        } else {
            $connection = new AnyWay_FTP_Pure();
        }

        $timeout = empty($this->timeout) ? 10 : $this->timeout;
        $connection->setTimeout($timeout);
        $connection->SetServer($this->host, $this->port);

        if (false === $connection->connect())
            throw new Exception("Unable to connect to " . $this->host);

        if (false === $connection->login($this->user, $this->password))
            throw new Exception("Invalid username / password");

        $connection->SetType(FTP_BINARY);
        if ($this->is_passive && (false === $connection->Passive(true))) {
            throw new Exception("Unable to enter passive mode");
        }
        if (empty($this->root) && false !== ($initial_directory = $connection->pwd())) {
            if (false === $connection->chdir('/')) {
                throw new Exception($connection->_message, $connection->_code);
            }
            if (false === ($root_directory = $connection->pwd())) {
                throw new Exception($connection->_message, $connection->_code);
            }
            if ($initial_directory !== $root_directory && $initial_directory !== '/') {
                $this->root = $initial_directory;
            } else {
                $this->root = '';
            }
        }

        $this->root = rtrim($this->root, '');

        $this->connection = $connection;
        $this->detect_writeable_permissions();

        if ($this->subdir) {
            $root = $this->absolutePath($this->subdir);
            if (false !== $this->mkdir($this->subdir)) {
                $this->subdir = null;
                $this->root = $root;
            } else {
                throw new Exception("Unable to initialize root directory " . $this->subdir);
            }
        }
        return $this->connection;
    }

    protected function getfileinfo($path)
    {
        $found = false;
        $remote = $this->absolutePath($path);
        if (false !== ($list = $this->getConnection()->dirlist($this->dirname($remote)))) {
            foreach ($list as $item) {
                if ($item['name'] == basename($path)) {
                    $found = $item;
                }
                $dir = $this->dirname($path);
                    $tpath = $dir
                        ? $dir . DIRECTORY_SEPARATOR . $item['name']
                        : $item['name'];
                $this->_is_dir[$tpath] = $item['isdir'];
            }
            return $found;
        }
        return false;
    }

    protected function _mkdir($path, $perms)
    {
        return $this->getConnection()->mkdir($this->absolutePath($path));
    }

    public function is_writeable($path)
    {
        if ('' === $path || '.' === $path || './' === $path)
            return true;

        if (isset($this->_made_writeable[$path]))
            return true;

        if (isset($this->_created[$path]))
            return true;

        if (isset($this->_writeable[$path]))
            return $this->_writeable[$path];

        if (false !== ($perms = $this->fileperms($path))) {
            if ($perms & $this->writeable_perms)
                return $this->_writeable[$path] = true;
        }
        return false;
    }


    public function file_exists($path)
    {
        return $this->getConnection()->file_exists($this->absolutePath($path));
    }

    public function symlink($target, $link)
    {
        return true; // FTP do not support symlinks by RFC
    }

    public function unlink($path)
    {
        return $this->getConnection()->delete($this->absolutePath($path));
    }

    public function touch($path, $time)
    {
        return true;
    }

    protected function _chmod($path, $mode)
    {
        return $this->getConnection()->chmod($this->absolutePath($path), $mode);
    }

    public function fopen($path, $mode)
    {
        $dir = $this->getTempDir();

        if (isset($this->handles[$path])) {
            $tempfile = $this->handles[$path];
        } else {
            //$tempfile = join(DIRECTORY_SEPARATOR, array($dir, basename($path)));
            $tempfile = tempnam($dir, '');
        }
        if (false !== ($handle = @fopen($tempfile, $mode))) {
            $this->handles[$path] = $tempfile;
            return $handle;
        }
        if ($error = error_get_last()) {
            $this->emit('error', $error['message']);
        }
        return false;
    }

    protected function clearstatcache($clear_realpath_cache = false, $path)
    {
        if (isset($this->handles[$path])) {
            $tempfile = $this->handles[$path];
        } else {
            $tempfile = join(DIRECTORY_SEPARATOR, array($this->getTempDir(), basename($path)));
        }
        @clearstatcache($clear_realpath_cache, $tempfile);
        return true;
    }

    protected function is_dir($path)
    {
        if ('' === $path || '.' === $path || './' === $path)
            return true;

        if (isset($this->_is_dir[$path]))
            return $this->_is_dir[$path];

        if (false !== ($fileinfo = $this->getfileinfo($path))) {
            return $fileinfo['isdir'];
        }

        return false;
    }

    protected function convert_permissions($permissions)
    {
        $mode = 0;

        if ($permissions[1] == 'r') $mode += 0400;
        if ($permissions[2] == 'w') $mode += 0200;
        if ($permissions[3] == 'x') $mode += 0100;
        else if ($permissions[3] == 's') $mode += 04100;
        else if ($permissions[3] == 'S') $mode += 04000;

        if ($permissions[4] == 'r') $mode += 040;
        if ($permissions[5] == 'w') $mode += 020;
        if ($permissions[6] == 'x') $mode += 010;
        else if ($permissions[6] == 's') $mode += 02010;
        else if ($permissions[6] == 'S') $mode += 02000;

        if ($permissions[7] == 'r') $mode += 04;
        if ($permissions[8] == 'w') $mode += 02;
        if ($permissions[9] == 'x') $mode += 01;
        else if ($permissions[9] == 't') $mode += 01001;
        else if ($permissions[9] == 'T') $mode += 01000;
        return $mode;
    }

    public function fileperms($path)
    {
        if (false !== ($fileinfo = $this->getfileinfo($path))) {
            return $this->convert_permissions($fileinfo['perms']);
        }
        return false;
    }

    public function finalize($path, $warnOnMissing = true)
    {
        if (isset($this->handles[$path])) {
            $this->emit("finalize", $path, $this->handles[$path], $this->root);

            $result = false;
            if (parent::make_writeable($path, false) || parent::make_writeable($this->dirname($path), $warnOnMissing)) {
                $result = $this->getConnection()->put($this->handles[$path], $this->absolutePath($path));
                if (false === $result) {
                    $this->emit("warning", $this->error_get_last_message("Unable to store file " . $this->absolutePath($path)));
                }
            } else {
                $this->emit('warning', "Unable to store $path");
            }
            unlink($this->handles[$path]);
            unset($this->handles[$path]);
            return $result;
        }
        throw new Exception("Trying to finalize non-existent path $path");
    }

    protected function getTempDir()
    {
        $dir = sys_get_temp_dir();
        if (@is_writable($dir))
            return $dir;

        $dir = realpath(ini_get("upload_tmp_dir"));
        if (@is_writable($dir))
            return $dir;

        throw new Exception("Cannot find a suitable temporary directory");
    }

    public function __destruct()
    {
        parent::__destruct();

        if ($this->connection)
            $this->connection->quit();
    }
}
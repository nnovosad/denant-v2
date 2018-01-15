<?php

class AnyWay_RestoreTarget_FileSystem extends AnyWay_EventEmitter implements AnyWay_Interface_IRestoreTarget
{

    protected $root;
    protected $apply_umask = false;

    protected $_made_writeable = array();
    protected $_created = array();

    public function __construct($options)
    {
        if (!isset($options['root']))
            throw new Exception("No root directory specified");

        $this->root = realpath($options['root']);
        if (false === $this->root)
            throw new Exception("Root directory does not exist");

        if (isset($options['apply_umask']))
            $this->apply_umask = $options['apply_umask'];

        if (isset($options['subdir']) && $options['subdir']) {
            $root = $this->absolutePath($options['subdir']);
            if (false !== @$this->mkdir($options['subdir'])) {
                $re = '~^' . preg_quote($options['subdir'], '~') . '$~';
                foreach ($this->_made_writeable as $key => $value) {
                    $newkey = preg_replace($re, '', $key);
                    $this->_made_writeable[$newkey] = $value;
                    unset($this->_made_writeable[$key]);
                }
                foreach ($this->_created as $key => $value) {
                    $newkey = preg_replace($re, '', $key);
                    $this->_created[$newkey] = $value;
                    unset($this->_created[$key]);
                }
                $this->root = $root;
            } else {
                throw new Exception("Unable to initialize root directory $root");
            }
        }
    }

    public function getState()
    {
        return array(
            'root' => $this->root,
            'apply_umask' => $this->apply_umask
        );
    }

    protected function error_get_last_message($message)
    {
        if ($error = error_get_last()) {
            return $message . ": " . $error['message'];
        }
        return $message;
    }

    public function sameFile($path, $absolutePath)
    {
        return $this->absolutePath($path) == $absolutePath;
    }

    /**
     * @param $path
     * @return string
     * @throws Exception
     */
    protected function absolutePath($path)
    {
        if ('' === $path || '.' === $path || './' === $path)
            return $this->root;

        if (strpos($path, '/') === 0)
            throw new Exception("Cannot convert absolute path to absolute path: $path");

        return strpos($path, './') === 0
            ? $this->root . DIRECTORY_SEPARATOR . substr($path, 2)
            : $this->root . DIRECTORY_SEPARATOR . $path;
    }

    protected function dirname($path)
    {
        $dirname = dirname($path);
        return '.' === $dirname
            ? ''
            : $dirname;
    }

    protected function _mkdir($path, $perms)
    {
        return @mkdir($this->absolutePath($path), $perms);
    }

    public function mkdir($path, $perms = 0777)
    {
        if ('' === $path || '.' === $path || './' === $path)
            return true;

        $stack = array($path);
        $tpath = $path;
        while ($tpath = $this->dirname($tpath)) {
            $stack[] = $tpath;
        }

        $umask = @umask(0);
        while ($stack) {
            $tpath = array_pop($stack);
            if (!$this->file_exists($tpath)) {
                if (false === $this->_mkdir($tpath, 0777)) {
                    $this->emit("warning", $this->error_get_last_message("Unable to create directory " . $this->absolutePath($tpath)));
                }
            } elseif (!$this->make_writeable($tpath)) {
                umask($umask);
                return false;
            } else {
            }
            $this->_created[$tpath] = $perms;
        }
        umask($umask);
        return true;
    }

    public function file_exists($path)
    {
        return file_exists($this->absolutePath($path));
    }

    public function symlink($target, $link)
    {
        return symlink($target, $this->absolutePath($link));
    }

    public function unlink($path)
    {
        return unlink($this->absolutePath($path));
    }

    public function touch($path, $time)
    {
        return touch($this->absolutePath($path), $time);
    }

    protected function _chmod($path, $mode)
    {
        return $this->apply_umask
            ? chmod($this->absolutePath($path), $mode & (0777 ^ umask()))
            : chmod($this->absolutePath($path), $mode);
    }

    public function chmod($path, $mode)
    {
        $this->_created[$path] = $mode;
    }

    public function make_writeable($path, $warnOnMissing = true)
    {
        if ('' === $path || '.' === $path || './' === $path)
            return true;

        if (isset($this->_made_writeable[$path]))
            return true;

        if ($this->is_writeable($path))
            return true;

        $stack = array($path);
        $tpath = $path;
        while ($tpath = $this->dirname($tpath)) {
            if ($this->is_writeable($tpath)) {
                break;
            }
            $stack[] = $tpath;
        }

        while ($stack) {
            $tpath = array_pop($stack);
            $perms = (int)@$this->fileperms($tpath);
            $failed = false;

            if ($this->is_dir($tpath)) {
                if (@$this->_chmod($tpath, $perms | 0700) || @$this->_chmod($tpath, $perms | 0070) || @$this->_chmod($tpath, $perms | 0007)) {
                    $this->_made_writeable[$tpath] = $perms;
                } else {
                    $failed = true;
                }
            } else {
                if (@$this->_chmod($tpath, $perms | 0600) || @$this->_chmod($tpath, $perms | 0060) || @$this->_chmod($tpath, $perms | 0006)) {
                    $this->_made_writeable[$tpath] = $perms;
                } else {
                    $failed = true;
                }
            }
            if ($failed && $warnOnMissing) {
                $this->emit("warning", $this->error_get_last_message("Cannot make " . $this->absolutePath($path) . " writeable"));
                return false;
            }
        }
        return $this->is_writeable($path);
    }

    public function fopen($path, $mode)
    {
        if (false === $this->make_writeable($this->dirname($path)))
            return false;

        if (preg_match("/[acw]/", $mode) && $this->file_exists($path) && false === $this->make_writeable($path))
            return false;

        if (false === ($result = @fopen($this->absolutePath($path), $mode))) {
            $this->emit("warning", $this->error_get_last_message("Unable to open " . $this->absolutePath($path)));
        }
        return $result;
    }

    public function ftruncate($handle, $size)
    {
        return ftruncate($handle, $size);
    }

    public function fwrite($handle, $data)
    {
        return ((func_num_args() == 3) && ($length = @func_get_arg(2)))
            ? fwrite($handle, $data, $length)
            : fwrite($handle, $data);
    }

    public function fflush($handle)
    {
        return fflush($handle);
    }

    public function fclose($handle)
    {
        return fclose($handle);
    }

    public function flock($handle, $mode)
    {
        return flock($handle, $mode);
    }

    public function fseek($handle, $offset, $whence = SEEK_SET)
    {
        return fseek($handle, $offset, $whence);
    }

    public function is_writeable($path)
    {
        $this->clearstatcache(true, $path);
        return is_writable($this->absolutePath($path));
    }

    protected function clearstatcache($clear_realpath_cache = false, $path)
    {
        clearstatcache($clear_realpath_cache, $this->absolutePath($path));
    }

    protected function is_dir($path)
    {
        return is_dir($this->absolutePath($path));
    }

    public function finalize($path, $warnOnMissing = true)
    {
        $this->emit("finalize", $path, $this->absolutePath($path), $this->root);
        return true; // not needed for filesystem
    }

    public function fileperms($path)
    {
        return fileperms($this->absolutePath($path));
    }

    public function restore_permissions()
    {
        uksort($this->_made_writeable, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($this->_made_writeable as $path => $mode) {
            @$this->_chmod($path, $mode);
        }
        $this->_made_writeable = array();
    }

    public function apply_permissions()
    {
        uksort($this->_created, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($this->_created as $path => $mode) {
            unset($this->_made_writeable[$path]);
            @$this->_chmod($path, $mode);
        }
        $this->_created = array();
    }

    public function __destruct()
    {
        if ($this->_created)
            $this->apply_permissions();

        if ($this->_made_writeable)
            $this->restore_permissions();
    }
}
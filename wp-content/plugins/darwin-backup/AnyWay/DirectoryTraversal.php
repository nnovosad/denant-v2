<?php

class AnyWay_DirectoryTraversal extends AnyWay_EventEmitter
{
    public $root;
    public $file;
    public $exclude = array();
    public $count = 0;
    public $symlink_stack = array();

    private $cache = array();
    private $changed = array();
    private $restore = array();

    public function __construct($options = array())
    {

        if (!isset($options['root']))
            throw new Exception("Directory to compress (root) not set");

        if ($options['root'] != realpath($options['root']))
            throw new Exception("No trailing slash allowed for compress root");

        $this->root = $options['root'];

        if (isset($options['file']) && $options['file'] !== null)
            $this->file = $options['file'];

        if (isset($options['count']) && $options['count'] !== null)
            $this->count = $options['count'];

        if (isset($options['symlink_stack']) && $options['symlink_stack'] !== null)
            $this->symlink_stack = $options['symlink_stack'];

        if (isset($options['exclude']) && $options['exclude'] !== null) {
            $this->exclude = array();
            foreach ((array)$options['exclude'] as $path) {
                if (preg_match('/^\//', $path)) {
                    $this->exclude[] = $path;
                } elseif (preg_match('/^\.\//', $path)) {
                    $this->exclude[] = $this->root . DIRECTORY_SEPARATOR . substr($path, 2);
                } else {
                    $this->exclude[] = $this->root . DIRECTORY_SEPARATOR . $path;
                }
            }
        }

        //parent::__construct($options);
    }

    public function getState()
    {
        return array(
            'root' => $this->root,
            'file' => $this->file,
            'exclude' => $this->exclude,
            'count' => $this->count,
            'symlink_stack' => $this->symlink_stack
        );
    }

    public function makeReadable($path)
    {
        if (is_link($path)) {
            $path = static::getAbsolutePath($path, @readlink($path));
        }

        if (is_readable($path))
            return true;

        $stack = array($path);
        $tpath = $path;
        while (0 === strpos($tpath, $this->root)) {
            $tpath = dirname($tpath);
            if (is_readable($tpath)) {
                break;
            }
            $stack[] = $tpath;
        }

        while ($stack) {
            $tpath = array_pop($stack);
            clearstatcache(null, $tpath);
            $perms = (int)@fileperms($tpath);
            if (is_dir($tpath) && (@chmod($tpath, $perms | 0700) || @chmod($tpath, $perms | 0070) || @chmod($tpath, $perms | 0007)) ||
                (@chmod($tpath, $perms | 0400) || @chmod($tpath, $perms | 0040) || @chmod($tpath, $perms | 0004))
            ) {
                if (!isset($this->changed[$tpath])) {
                    $this->restore[] = array($tpath, $perms);
                    $this->changed[$tpath] = $perms ? $perms : 0775;
                }
                clearstatcache(null, $tpath);
            } else {
                $this->emit("warning", "Cannot read " . str_replace($this->root, '', $tpath));
                return false;
            }
        }

        return is_readable($path);
    }

    public function restorePermissions()
    {
        if ($this->restore) {
            usort($this->restore, function ($a, $b) {
                if (strlen($a[0]) == strlen($b[0])) return 0;
                return (strlen($a[0]) > strlen($b[0]) ? -1 : 1);
            });
            foreach ($this->restore as $v) {
                list($path, $perms) = $v;
                @chmod($path, $perms);
            }
            $this->restore = array();
            $this->changed = array();
        }
    }

    public static function getAbsolutePath($realFile, $linkTo)
    {
        if (!preg_match('/^\//', $linkTo)) {
            $dir = dirname($realFile);
            while (preg_match('/^\.\.\//', $linkTo)) {
                $linkTo = preg_replace('/^\.\.\//', '', $linkTo);
                $dir = dirname($dir);
            }
            $linkTo = preg_replace('/[^\/+]\/\.\.\//', '', $linkTo);
            while (preg_match('/^\.\//', $linkTo)) {
                $linkTo = preg_replace('/^\.\//', '', $linkTo);
            }
            $linkTo = preg_replace('/\/\.\//', '/', $linkTo);
            $linkTo = $dir . DIRECTORY_SEPARATOR . $linkTo;
        }
        return $linkTo;
    }

    protected function getNextFromDirectoryCache($dir, $currentFile)
    {
        if (!isset($this->cache[$dir])) {

            if (!is_readable($dir) && !$this->makeReadable($dir)) {
                return false;
            }

            $this->cache[$dir] = array();
            if (false !== ($dh = @opendir($dir))) {

                while (false !== ($filename = readdir($dh))) {

                    if ($filename != '.' && $filename != '..' && strcmp($filename, $currentFile) > 0) {
                        $realFile = $dir . DIRECTORY_SEPARATOR . $filename;
                        if (is_readable($realFile) || $this->makeReadable($realFile)) {
                            $this->cache[$dir][] = $filename;
                        }
                    }
                }
                @closedir($dh);
                sort($this->cache[$dir]);
            } else {
                if ($error = error_get_last()) {
                    $this->emit("warning", $error['message']);
                }
            }
        }

        while (true) {
            $filename = count($this->cache[$dir])
                ? array_shift($this->cache[$dir])
                : false;

            if (!$filename)
                return false;

            $realFile = $dir . DIRECTORY_SEPARATOR . $filename;

            foreach ($this->exclude as $path) {
                if (strpos($realFile, $path) === 0)
                    continue 2;
            }

            if (is_link($realFile) && is_dir($realFile)) {
                if (count($this->symlink_stack) == 3) {
                    continue;
                } else {
                    $this->symlink_stack[] = $realFile;
                }
            }
            return $filename;
        }
    }

    protected function getSibling($directory)
    {

        $currentDir = dirname($directory);
        $currentFile = basename($directory);

        if (end($this->symlink_stack) == $directory)
            array_pop($this->symlink_stack);

        if ($next = $this->getNextFromDirectoryCache($currentDir, $currentFile)) {
            return substr_replace($currentDir . DIRECTORY_SEPARATOR . $next, '', 0, strlen($this->root) + 1);
        } else {
            return realpath($currentDir) == $this->root
                ? false
                : $this->getSibling($currentDir);
        }
    }

    public function getNextFile()
    {
        $currentFile = $this->root . DIRECTORY_SEPARATOR . $this->file;

        if (!is_readable($currentFile) && !$this->makeReadable($currentFile)) {
            throw new Exception("DirectoryTraversal: File $currentFile became unreadable between runs");
        }

        $currentDir = is_dir($currentFile) // && !is_link($currentFile)
            ? preg_replace('/\/$/', '', $currentFile)
            : dirname($currentFile);

        $currentFile = is_dir($currentFile) // && !is_link($currentFile)
            ? ''
            : basename($currentFile);

        if ($next = $this->getNextFromDirectoryCache($currentDir, $currentFile)) {
            $this->count++;
            return $this->file = substr_replace($currentDir . DIRECTORY_SEPARATOR . $next, '', 0, strlen($this->root) + 1);
        } else {
            if (realpath($currentDir) == $this->root)
                return false;

            if (false === ($currentFile = $this->getSibling($currentDir)))
                return false;
        }

        $this->count++;
        // returning next sibling from $this->getSibling();
        return $this->file = $currentFile;
    }

    public function realFile($filename)
    {
        return $this->root . DIRECTORY_SEPARATOR . $filename;
    }

    public function fileperms($filename)
    {
        return isset($this->changed[$filename])
            ? $this->changed[$filename]
            : fileperms($filename);
    }

    public function __destruct()
    {
        $this->restorePermissions();
    }

}
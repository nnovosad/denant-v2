<?php

class AnyWay_Tasks_Decompress extends AnyWay_PhpEmbeddedFsArchive implements AnyWay_Interface_ITask
{
    const REPLACEABLE_FILES_REGEXP = '/\.(php|txt|js|css|scss|sass)$/';

    public $id = 'decompress';

    protected $adapter;
    protected $currentBlock;
    protected $file;
    protected $section;
    protected $longLink;
    protected $longName;
    protected $position;
    protected $size;
    protected $perms;
    protected $mtime;
    protected $replacements = array();
    protected $exclude = array();
    protected $memoized_exclude = array();

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        if (!isset($options['section']))
            throw new Exception("section not set");

        parent::__construct($options['filename']);

        if (!isset($options['adapter']))
            throw new Exception("adapter not set");

        if (!is_array($options['adapter'])) {
            throw new Exception("Invalid adapter");
        }

        $this->section = $options['section'];
        $this->adapter = $options['adapter'];

        if (isset($options['currentBlock']) && $options['currentBlock'] !== null)
            $this->currentBlock = $options['currentBlock'];

        if (isset($options['file']) && $options['file'] !== null) {
            $this->file = $options['file'];
            $this->position = $options['position'];
            $this->size = $options['size'];
            $this->perms = $options['perms'];
            $this->mtime = $options['mtime'];
        }

        if (isset($options['longLink']))
            $this->longLink = $options['longLink'];

        if (isset($options['longName']))
            $this->longName = $options['longName'];

        if (isset($options['replacements']))
            $this->replacements = $options['replacements'];

        if (!empty($options['exclude'])) {
            if (!is_array($options['exclude']))
                throw new Exception('exclude option expects an array');
            foreach ($options['exclude'] as $exclude) {
                $this->exclude[] = strpos($exclude, './') === 0
                    ? $exclude
                    : "./" . $exclude;
            }
        }
    }

    public function getState()
    {
        return array(
            'adapter' => $this->adapter,
            'filename' => $this->filename,
            'section' => $this->section,
            'currentBlock' => $this->currentBlock,
            'file' => $this->file,
            'longLink' => $this->longLink,
            'longName' => $this->longName,
            'position' => $this->position,
            'size' => $this->size,
            'perms' => $this->perms,
            'mtime' => $this->mtime,
            'replacements' => $this->replacements,
            'exclude' => $this->exclude
        );
    }

    public function emit($event, $message = null)
    {
        if ($event == 'read') {
            parent::emit("processed", $message);
        } else {
            call_user_func_array('parent::emit', func_get_args());
        }
    }

    public function gzreadblock($handle)
    {
        //echo "-----\n";
        $safeToReplace = false;
        $data = '';
        while (!$safeToReplace) {
            $chunk = parent::gzreadblock($handle);
            if ($chunk !== false) {
                //echo "chunk:" . $chunk . "\n";
                $chunkLen = strlen($chunk);
                $newSafeToReplace = true;
                foreach ($this->replacements as $key => $replacement) {
                    $len = $keyLen = strlen($key) - 1;
                    if ($keyLen > $chunkLen)
                        $len = $chunkLen;
                    foreach (range(1, $len) as $i) {
                        //echo "search: " . substr($key, 0, $i) . "\n";
                        //echo "end: " . substr($chunk, ($chunkLen) - $i, $i) . ($chunkLen) . ":" . ($i) . "\n";
                        if (substr($key, 0, $i) == substr($chunk, ($chunkLen) - $i, $i)) {
                            //echo "unsafe !, reading more\n";
                            $newSafeToReplace = false;
                            break;
                        }
                    }
                    if (!$newSafeToReplace) {
                        break;
                    }
                }
                $safeToReplace = $newSafeToReplace;
                $data .= $chunk;
            } else {
                return $data ? $data : false;
            }
        }
        return $data;
    }

    public function replace($data)
    {
        if ($this->replacements) {
            foreach ($this->replacements as $search => $replacement) {
                $data = str_ireplace($search, $replacement, $data);
            }
        }
        return $data;
    }

    public function is_excluded($adapter, $file)
    {
        if (isset($this->memoized_exclude[$file]))
            return $this->memoized_exclude[$file];

        foreach ($this->exclude as $exclude) {
            if (strpos($file, $exclude) === 0) {
                return $this->memoized_exclude[$file] = true;
                break;
            }
        }

        if ($adapter->sameFile($file, $this->filename))
            return $this->memoized_exclude[$file] = true;

        return $this->memoized_exclude[$file] = false;
    }

    public function runPartial($deadline, $hardDeadline)
    {
        $adapterClass = $this->adapter['class'];

        /* @var AnyWay_RestoreTarget_FileSystem|AnyWay_RestoreTarget_FTP|AnyWay_RestoreTarget_Verify $adapter */
        $adapter = isset($this->adapter['state'])
            ? new $adapterClass($this->adapter['state'])
            : new $adapterClass(array());
        $adapter->reemit(null, $this);

        if (microtime(true) >= $deadline) {
            return $this->getState();
        }

        if (false === ($tarfh = $this->gzopen($this->section, 'rb'))) {
            throw new Exception($this->section . ' not found');
        }

        if ($this->currentBlock)
            $this->gzseek($tarfh, $this->currentBlock);

        $endMarker = pack("a512", "");

        $data = $this->gzreadblock($tarfh);

        while (false !== $data) {

            $excluded = false;

            while ($data && !$this->file) {

                // http://www.gnu.org/software/tar/manual/html_node/Standard.html
                //  At the end of the archive file there are two 512-byte blocks filled with binary zeros as an end-of-file marker.
                if (substr($data, 0, 512) == $endMarker) {
                    // ending
                    return null;
                }

                $handler = unpack("a100name/a8perms/a8uid/a8gid/a12size/a12time/a8checksum/a1flag/a100link/a6mgc/a2ver/a32un/a32gn/a8dvh/a8dvm/a155prefix/a12oth", substr($data, 0, 512));
                $data = substr($data, 512);

                if ($this->longLink) {
                    $file = $this->longLink;
                    $this->longLink = null;
                } else {
                    $prefix = rtrim($handler['prefix'], "\0");
                    $file = $prefix
                        ? $prefix . DIRECTORY_SEPARATOR . rtrim($handler['name'], "\0")
                        : rtrim($handler['name'], "\0");
                }

                if ($handler['flag'] == '5') {
                    if (!$this->is_excluded($adapter, $file))
                        $adapter->mkdir($file, octdec($handler['perms']));
                } elseif ($handler['flag'] == '2') {
                    if (!$this->is_excluded($adapter, $file) && $adapter->mkdir(dirname($file), 0777)) {
                        if ($adapter->file_exists($file))
                            @$adapter->unlink($file);
                        // symlink
                        if ($this->longName) {
                            @$adapter->symlink($this->longName, $file);
                            $this->longName = null;
                        } else {
                            @$adapter->symlink(rtrim($handler['link'], "\0"), $file);
                        }
                    }
                } elseif ($handler['flag'] == '0') {
                    $this->mtime = octdec($handler['time']);
                    $this->perms = octdec($handler['perms']);
                    $this->size = octdec($handler['size']);
                    $this->file = $file;
                    $this->position = 0;
                } elseif ($handler['flag'] == "L") {
                    $size = octdec($handler['size']);
                    while (ceil($size / 512) * 512 > strlen($data)) {
                        if (false === ($more = $this->gzreadblock($tarfh)))
                            throw new Exception("Unexpected end of file");
                        $data .= $more;
                    }
                    $this->longLink = substr($data, 0, $size - 1);
                    $data = substr($data, ceil($size / 512) * 512);
                } elseif ($handler['flag'] == "K") {
                    $size = octdec($handler['size']);
                    while (ceil($size / 512) * 512 > strlen($data)) {
                        if (false === ($more = $this->gzreadblock($tarfh)))
                            throw new Exception("Unexpected end of file");
                        $data .= $more;
                    }
                    $this->longName = substr($data, 0, $size - 1);
                    $data = substr($data, ceil($size / 512) * 512);
                } else {
                    throw new Exception("Uknown flag {$handler['flag']}");
                }
            }

            if ($data && $this->file) {

                // this is done now at adapter level during fopen
                //$adapter->make_writeable($this->file, $this->position); // may fail, that's ok
                $outfh = null;

                if (!$this->is_excluded($adapter, $this->file) && false !== ($outfh = @$adapter->fopen($this->file, "cb"))) {
                    if (false === $adapter->flock($outfh, LOCK_EX | LOCK_NB)) {
                        $adapter->fclose($outfh);
                        $outfh = null;
                    } else {
                        if ($this->position)
                            $adapter->fseek($outfh, $this->position, SEEK_SET);
                        else
                            $adapter->ftruncate($outfh, 0);
                    }
                }

                $todo = $this->size - $this->position;
                if ($todo <= strlen($data)) {
                    if ($outfh) {
                        if (preg_match(static::REPLACEABLE_FILES_REGEXP, $this->file)) {
                            $adapter->fwrite($outfh, $this->replace(substr($data, 0, $todo)));
                        } else {
                            $adapter->fwrite($outfh, substr($data, 0, $todo));
                        }
                        $adapter->flock($outfh, LOCK_UN);
                        $adapter->fclose($outfh);
                        if ($adapter->finalize($this->file)) {
                            @$adapter->chmod($this->file, $this->perms);
                            @$adapter->touch($this->file, $this->mtime);
                        }
                    }
                    $data = substr($data, ceil($todo / 512) * 512);
                    $this->file = $this->position = $this->size = $this->perms = null;
                } else {
                    if ($outfh) {
                        if (preg_match(static::REPLACEABLE_FILES_REGEXP, $this->file)) {
                            $replaced = $this->replace($data);
                            $this->size -= (strlen($data) - strlen($replaced));
                            $data = $replaced;
                        }
                        $adapter->fwrite($outfh, $data);
                        $adapter->flock($outfh, LOCK_UN);
                        $adapter->fclose($outfh);
                    }
                    $this->position += strlen($data);
                    $data = '';
                }
            }

            // check timed out
            // I've spent a few hours trying to pass unprocessedData back and forth
            // as I thought $data = '' happens infrequently
            // but this implementation is absolutely fine
            // as $data = '' after each block because tar block is 512 bytes long,
            // this implementation will check for timeout after each gz block !!!
            if (!$data) {
                if (microtime(true) >= $deadline) {
                    $currentBlock = $this->gztell($tarfh);
                    $this->gzclose($tarfh);
                    return array(
                        'adapter' => array(
                            'class' => $this->adapter['class'],
                            'state' => $adapter->getState()
                        ),
                        'filename' => $this->filename,
                        'section' => $this->section,
                        'currentBlock' => $currentBlock,
                        'file' => $this->file,
                        'longLink' => $this->longLink,
                        'longName' => $this->longName,
                        'position' => $this->position,
                        'size' => $this->size,
                        'perms' => $this->perms,
                        'mtime' => $this->mtime,
                        'replacements' => $this->replacements,
                        'exclude' => $this->exclude
                    );
                }
                $data = $this->gzreadblock($tarfh);
            }
        }

        $this->gzclose($tarfh);
        return null;
    }
}
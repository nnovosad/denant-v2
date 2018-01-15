<?php

class AnyWay_PhpEmbeddedFs extends AnyWay_EventEmitter
{
    const BUFFER_LENGTH = 65536;
    const FS_START = 1024; // 1KB for FAT
    const FINALIZE_MARKER = '/* phpefs:end */';
    const MIN_FREE_SPACE = 1024000; // 1MB of free space required

    protected $filename;

    protected $opened_for_write = false;
    protected $fat_read = false;
    protected $FAT = array();
    protected $FAT_fnlookup = array();
    protected $FAT_fhlookup = array();
    protected $EOF = array();

    protected $fh;
    protected $fh_appending;

    protected $encoding;
    protected $is_finalized = false;

    public function __construct($filename)
    {
        if (function_exists('mb_internal_encoding') && (@ini_get('mbstring.func_overload') & 2)) {
            $this->encoding = mb_internal_encoding();
            mb_internal_encoding('ISO-8859-1');
        }

        $this->filename = $filename;
        $this->_read_fat();
    }

    protected function open_for_write()
    {
        if (false === ($this->fh = @fopen($this->filename, 'c+b'))) {
            throw new Exception("Unable to open {$this->filename} for writing");
        }

        if (false == flock($this->fh, LOCK_EX | LOCK_NB)) {
            fclose($this->fh);
            throw new Exception("Unable to lock {$this->filename}");
        }

        $this->opened_for_write = true;
    }

    protected function _read_fat()
    {
        if ($this->fat_read)
            throw new Exception("FAT already read");

        // fat might be empty
        if (file_exists($this->filename) && false !== ($fh = fopen($this->filename, 'rb')) && false !== ($fat = fread($fh, self::FS_START))) {
            foreach (explode("\n", $fat) as $line) {
                if (preg_match('/\/\* (.*):(\d+):(\d+) \*\//', $line, $matches)) {

                    $l = count($this->FAT);

                    if ($this->FAT)
                        $this->FAT[$l - 1]['can_append'] = false;

                    $this->FAT_fnlookup[$l] = $matches[1];
                    $this->FAT[] = array(
                        'filename' => $matches[1],
                        'start' => (int)$matches[2],
                        'size' => (int)$matches[3],
                        'can_append' => true
                    );
                } elseif (preg_match('/' . preg_quote(self::FINALIZE_MARKER, '/') . '/', $line, $matches)) {
                    $this->is_finalized = true;
                }
            }
            $this->fat_read = true;
            fclose($fh);
        }
    }

    protected function _write_fat()
    {
        if (!$this->opened_for_write) {
            // no fopen before _writing_fat
            throw new Exception("Unable to write FAT - {$this->filename} not opened_for_write");
        }

        $header = "<?php \n";
        foreach ($this->FAT as $data) {
            $header .= sprintf("/* %s:%d:%d */\n", $data['filename'], $data['start'], $data['size']);
        }

        if ($this->is_finalized) {
            $header .= self::FINALIZE_MARKER . "\n";
        }

        fseek($this->fh, 0);
        fwrite($this->fh, pack("A" . self::FS_START, $header), self::FS_START);
    }

    public function fopen($filename, $mode)
    {

        if (false !== strpos($mode, 'w')) {
            // write
            throw new Exception("fopen mode w not supported");
        } elseif (false !== strpos($mode, 'a')) {

            if (!$this->opened_for_write)
                $this->open_for_write();

            if ($this->fh_appending)
                throw new Exception("Already appending, close previous file first");

            if ($this->is_finalized)
                throw new Exception("Already is finalized");

            if (false !== ($index = array_search($filename, $this->FAT_fnlookup))) {
                $FATRecord = $this->FAT[$index];
                if (!$FATRecord['can_append']) {
                    throw new Exception("Can't append to the file (" . $filename . ") that's already followed by a file");
                }
            } else {
                // adding new file
                $eof = self::FS_START;
                if ($index = count($this->FAT)) {
                    $this->FAT[$index - 1]['can_append'] = false;
                    $eof = $this->FAT[$index - 1]['start'] + $this->FAT[$index - 1]['size'];
                }
                // new fat record
                $FATRecord = array(
                    'filename' => $filename,
                    'start' => $eof,
                    'size' => 0,
                    'can_append' => true
                );
                $this->FAT_fnlookup[$index] = $filename;
                $this->FAT[] = &$FATRecord;
                $this->_write_fat();
            }
            if (false !== ($handle = @fopen($this->filename, $mode))) {
                if (-1 === fseek($handle, $FATRecord['start'] + $FATRecord['size'])) {
                    throw new Exception("Failed to seek");
                }
                $this->FAT_fhlookup[$index] = $handle;
                $this->fh_appending = $handle;
                return $handle;
            }
            throw new Exception("Unable to fopen $filename in '$mode' mode");
        } elseif (false !== strpos($mode, 'r')) {
            if (false !== ($index = array_search($filename, $this->FAT_fnlookup)) && ($FATRecord = &$this->FAT[$index])) {
                if (false !== ($handle = @fopen($this->filename, $mode))) {
                    $this->FAT_fhlookup[$index] = $handle;
                    fseek($handle, $FATRecord['start']);
                    return $handle;
                }
            }
            return false;
        }
    }

    public function fwrite($handle, $data, $length = null)
    {
        if (false !== ($index = array_search($handle, $this->FAT_fhlookup)) && ($FATRecord = &$this->FAT[$index])) {

            if ($this->fh_appending !== $handle)
                throw new Exception("Unable to write to specified handle");

            $bytesWritten = $length
                ? fwrite($handle, $data, $length)
                : fwrite($handle, $data);

            if (false === $bytesWritten)
                return false;

            $this->emit("write", $bytesWritten);
            $FATRecord['size'] += $bytesWritten;
            /*
            if (function_exists('disk_free_space') &&
                ($free_space = @disk_free_space(dirname($this->filename))) &&
                ($free_space < self::MIN_FREE_SPACE)
            ) {
                throw new Exception("Not enough disk space during write");
            }
            */
            return $bytesWritten;
        }
        throw new Exception("Unable to find FAT record for file handle");
    }

    public function fread($handle, $length)
    {
        if ($length === 0)
            throw new Exception("fread(): Length parameter must be greater than 0"); // default system behaviour

        if (false !== ($index = array_search($handle, $this->FAT_fhlookup))) {
            $position = ftell($handle) - $this->FAT[$index]['start'];
            $left = $this->FAT[$index]['size'] - $position;
            if ($length > $left) {
                $this->EOF[] = $handle;
                $result = @fread($handle, $left);
                if (false !== $result)
                    $this->emit("read", $left);
                return $result;
            } else {
                $result = @fread($handle, $length);
                if (false !== $result)
                    $this->emit("read", $length);
                return $result;
            }
        }
        throw new Exception("Handle not tracked");
    }

    public function fseek($handle, $offset, $mode = SEEK_SET)
    {
        if (false !== ($index = array_search($handle, $this->FAT_fhlookup))) {
            switch ($mode) {
                case SEEK_SET:
                    return fseek($handle, $this->FAT[$index]['start'] + $offset, SEEK_SET);
                case SEEK_CUR:
                    throw new Exception("SEEK_CUR not supported");
                case SEEK_END;
                    throw new Exception("SEEK_END not supported");
                default:
                    throw new Exception("Uknown $mode");
            }
        }
        throw new Exception("Handle not tracked");
    }

    public function ftell($handle)
    {
        if (false !== ($index = array_search($handle, $this->FAT_fhlookup))) {
            return ftell($handle) - $this->FAT[$index]['start'];
        }
        throw new Exception("Handle not tracked");
    }

    public function feof($handle)
    {
        if (false !== array_search($handle, $this->EOF)) {
            return true;
        }
        return false;
    }

    public function fclose($handle)
    {
        if (false !== ($index = array_search($handle, $this->FAT_fhlookup))) {

            if ($this->fh_appending === $handle) {
                $this->fh_appending = null;
                $this->_write_fat();
            }

            unset($this->FAT_fhlookup[$index]);
            if (false !== ($index = array_search($handle, $this->EOF))) {
                unset($this->EOF[$index]);
            }

            return fclose($handle);
        }
        throw new Exception("Handle not tracked");
    }

    public function close()
    {
        if ($this->opened_for_write) {
            while ($this->FAT_fhlookup) {
                $handle = reset($this->FAT_fhlookup);
                if (false === $this->fclose($handle))
                    throw new Exception("Unable to close handle");
            }
            $this->_write_fat();

            flock($this->fh, LOCK_UN);
            $this->opened_for_write = false;
            fclose($this->fh);
        }
    }

    public function isFinalized()
    {
        return $this->is_finalized;
    }

    public function finalize()
    {
        if (!$this->is_finalized) { // triggering _read_fat() through the getter

            if (empty($this->FAT)) {
                throw new Exception("Cannot finalize empty filesystem");
            }

            if (!$this->opened_for_write)
                $this->open_for_write();

            $this->is_finalized = true;
        }
    }

    public function __destruct()
    {
        $this->close();

        if ($this->encoding && function_exists('mb_internal_encoding'))
            @mb_internal_encoding($this->encoding);

        parent::__destruct();
    }

}
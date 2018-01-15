<?php

class AnyWay_Tasks_Compress extends AnyWay_PhpEmbeddedFsArchive implements AnyWay_Interface_ITask
{
    public $id = 'compress';

    protected $file;
    protected $size;
    protected $position;
    protected $dt;
    protected $section;

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        parent::__construct($options['filename']);

        if (!isset($options['directory']))
            throw new Exception("directory not set");

        if (!isset($options['section']))
            throw new Exception("section not set");

        $this->section = $options['section'];

        if (isset($options['file']))
            $this->file = $options['file'];

        if (isset($options['size']))
            $this->size = $options['size'];

        if (isset($options['position']))
            $this->position = $options['position'];

        if (isset($options['directory']['exclude'])) {
            if (!in_array($options['filename'], $options['directory']['exclude'])) {
                $options['directory']['exclude'][] = $options['filename'];
            }
        } else {
            $options['directory']['exclude'] = array($options['filename']);
        }

        $this->dt = new AnyWay_DirectoryTraversal($options['directory']);
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename,
            'file' => $this->file,
            'size' => $this->size,
            'position' => $this->position,
            'directory' => $this->dt->getState(),
            'section' => $this->section
        );
    }

    protected function buildLongLink($relativename, $flag = 'L')
    {
        $uid = sprintf("%07s", decoct(0));
        $gid = sprintf("%07s", decoct(0));
        $perms = sprintf("%07s", decoct(0));
        $mtime = sprintf("%011s", decoct(0));
        $namelen = strlen($relativename) + 1;

        $size = sprintf("%011s", decoct($namelen));
        $magic = sprintf("%5s ", "ustar");
        $uname = $gname = $devmajor = $devminor = $prefix = "";
        $version = " ";
        $binary_data_first = pack("a100a8a8a8a12a12", "././@LongLink", $perms, $uid, $gid, $size, $mtime);
        $binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $flag, "", $magic, $version, "root", "root", $devmajor, $devminor, $prefix, "");

        // checksum
        $checksum = 0;
        for ($i = 0; $i < 148; $i++) {
            $checksum += ord(substr($binary_data_first, $i, 1));
        }
        for ($i = 148; $i < 156; $i++) {
            $checksum += ord(' ');
        }
        for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
            $checksum += ord(substr($binary_data_last, $j, 1));
        }
        $checksum = sprintf("%06s", decoct($checksum));
        $binary_data = pack("a8", $checksum);

        $length = ceil($namelen / 512) * 512;
        return $binary_data_first . $binary_data . $binary_data_last . pack("a" . $length, $relativename);
    }

    protected function buildHeader($filename, $relativename)
    {
        $prepend = "";
        $linkname = "";
        $relativename = './' . $relativename;
//        if (is_link($filename)) {
//            $linkname = readlink($filename);
//            if (strlen($linkname) > 100) $prepend .= $this->buildLongLink($linkname, 'K');
//            if (strlen($relativename) > 100) $prepend .= $this->buildLongLink($relativename);
//            $typeflag = "2";
//            $size = 0;
//        } elseif (is_dir($filename)) {
        if (is_dir($filename)) {
            $relativename = $relativename . DIRECTORY_SEPARATOR;
            if (strlen($relativename) > 100) $prepend = $this->buildLongLink($relativename);
            $typeflag = "5";
            $size = 0;
        } elseif (is_file($filename)) {
            if (strlen($relativename) > 100) $prepend = $this->buildLongLink($relativename);
            $typeflag = "0";
            clearstatcache();
            $size = filesize($filename);
        } else {
            return false;
        }

        // converting file settings to binary format
        $info = stat($filename);
        $uid = sprintf("%07s", decoct($info[4]));
        $gid = sprintf("%07s", decoct($info[5]));
        $perms = sprintf("%07s", decoct($this->dt->fileperms($filename)));
        $mtime = sprintf("%011s", decoct(filemtime($filename)));
        $u = function_exists('posix_getpwuid')
            ? posix_getpwuid($info[4])
            : array('name' => $info[4]);
        $g = function_exists('posix_getgrgid')
            ? posix_getgrgid($info[5])
            : array('name' => $info[5]);

        $size = sprintf("%011s", decoct($size));
        $magic = sprintf("%5s ", "ustar");
        $version = " ";
        $uname = $gname = $devmajor = $devminor = $prefix = "";
        $binary_data_first = pack("a100a8a8a8a12a12", $relativename, $perms, $uid, $gid, $size, $mtime);
        $binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $typeflag, $linkname, $magic, $version, $u['name'], $g['name'], $devmajor, $devminor, $prefix, "");

        // checksum
        $checksum = 0;
        for ($i = 0; $i < 148; $i++) {
            $checksum += ord(substr($binary_data_first, $i, 1));
        }
        for ($i = 148; $i < 156; $i++) {
            $checksum += ord(' ');
        }
        for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
            $checksum += ord(substr($binary_data_last, $j, 1));
        }
        $checksum = sprintf("%06s", decoct($checksum));
        $binary_data = pack("a8", $checksum);

        return $prepend . $binary_data_first . $binary_data . $binary_data_last;
    }

    public function makeReadable($file)
    {
        return $this->dt->makeReadable($file);
    }

    public function runPartial($deadline, $hardDeadline)
    {
        if (microtime(true) >= $deadline) {
            return $this->getState();
        }

        if (false === ($tarfh = $this->gzopen($this->section, 'ab'))) {
            throw new Exception("Unable to open {$this->section} section");
        }

        if (!$this->file) {
            $file = $this->dt->getNextFile();
            $position = null;
            $size = null;
        } else {
            $file = $this->file;
            $size = $this->size;
            $position = $this->position;
            $realFile = $this->dt->realFile($file);
            if (!is_readable($realFile) && !$this->makeReadable($realFile)) {
                throw new Exception("Compress: File $file became unreadable between runs");
            }
        }

        while ($file) {
            if (microtime(true) >= $deadline) {
                $this->gzclose($tarfh);
                return array(
                    'filename' => $this->filename,
                    'file' => $file,
                    'size' => $size,
                    'position' => $position,
                    'directory' => $this->dt->getState(),
                    'section' => $this->section
                );
            }

            $realFile = $this->dt->realFile($file);

            $infh = null;
            if (!is_null($position)) {
                $infh = fopen($realFile, "rb");
                fseek($infh, $position);
            } else {
                // it is important to check we can read file before writing its header ! eg wp-content/mysql.sql
                if ((is_dir($realFile)) && false !== ($header = $this->buildHeader($realFile, $file))) {
                    $this->gzwrite($tarfh, $header);
                } elseif (is_file($realFile) && false !== ($infh = @fopen($realFile, "rb")) && false !== ($header = $this->buildHeader($realFile, $file))) {
                    $this->gzwrite($tarfh, $header);
                    $size = filesize($realFile);
                    $position = 0;
                } else {
                    // file of unsupported type
                    $file = null;
                    $size = null;
                    $position = null;
                }
            }

            if (isset($infh) && is_resource($infh)) {
                //echo "   writing $realFile, $position, $size\n";

                while ($position < $size) {

                    $length = $size - $position < self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE
                        ? $size - $position
                        : self::DEFAULT_UNCOMPRESSED_BLOCK_SIZE;

                    if (false !== ($data = fread($infh, $length))) {
                        $bytesRead = strlen($data);
                        if ($bytesRead < $length && feof($infh)) {
                            // the file shrunk since last read
                            $this->gzwrite($tarfh, $data . str_repeat("\x0", $length - $bytesRead));
                            $position += $length;
                            $this->emit("processed", $length);
                        } else {
                            $this->gzwrite($tarfh, $data);
                            $position += $bytesRead;
                            $this->emit("processed", $bytesRead);
                        }

                    } else {
                        throw new Exception("Unable to read from $file");
                    }

                    if ($position < $size && microtime(true) >= $hardDeadline) {
                        fclose($infh);
                        $this->gzclose($tarfh);
                        return array(
                            'filename' => $this->filename,
                            'file' => $file,
                            'size' => $size,
                            'position' => $position,
                            'directory' => $this->dt->getState(),
                            'section' => $this->section
                        );
                    }
                }

                fclose($infh);

                $left = $size > 512
                    ? 512 - fmod($size, 512)
                    : 512 - $size;

                if ((512 !== (int)$left) && (0 !== (int)$left)) {
                    $this->gzwrite($tarfh, pack("a" . (int)$left, ""));
                }
            }

            $file = $this->dt->getNextFile();
            $position = null;
        }

        $this->gzwrite($tarfh, pack("a1024", ""));
        $this->gzclose($tarfh, true);

        return null;
    }
}
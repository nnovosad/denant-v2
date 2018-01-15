<?php

interface AnyWay_Interface_IRestoreTarget
{
    public function __construct($root);

    public function mkdir($path, $perms = 0777);

    public function file_exists($path);

    public function symlink($target, $link);

    public function unlink($path);

    public function touch($path, $timestamp);

    public function chmod($path, $mode);

    public function make_writeable($path, $warnOnMissing = true);

    public function fopen($path, $mode);

    public function ftruncate($handle, $size);

    public function fwrite($handle, $data);

    public function fflush($handle);

    public function fclose($handle);

    public function flock($handle, $mode);

    public function fseek($handle, $offset, $whence = SEEK_SET);

    public function is_writeable($path);

    public function finalize($path);

    public function getState();

    public function sameFile($path, $absolutePath);
}
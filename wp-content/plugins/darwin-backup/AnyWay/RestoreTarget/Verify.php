<?php

class AnyWay_RestoreTarget_Verify extends AnyWay_EventEmitter implements AnyWay_Interface_IRestoreTarget
{
    public function __construct($options)
    {
    }

    public function mkdir($path, $perms = 0777)
    {
        return true;
    }

    public function file_exists($path)
    {
        return true;
    }

    public function symlink($target, $link)
    {
        return true;
    }

    public function unlink($path)
    {
        return true;
    }

    public function touch($path, $timestamp)
    {
        return true;
    }

    public function chmod($path, $mode)
    {
        return true;
    }

    public function make_writeable($path, $warnOnMissing = true)
    {
        return true;
    }

    public function fopen($path, $mode)
    {
        return true;
    }

    public function ftruncate($handle, $size)
    {
        return true;
    }

    public function fwrite($handle, $data)
    {
        return true;
    }

    public function fflush($handle)
    {
        return true;
    }

    public function fclose($handle)
    {
        return true;
    }

    public function flock($handle, $mode)
    {
        return true;
    }

    public function fseek($handle, $offset, $whence = SEEK_SET)
    {
        return true;
    }

    public function is_writeable($path)
    {
        return true;
    }

    public function finalize($path, $warnOnMissing = true)
    {
        return true;
    }

    public function getState()
    {
        return array();
    }

    public function sameFile($path, $absolutePath)
    {
        return false;
    }

}
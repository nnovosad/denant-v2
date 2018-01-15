<?php

class AnyWay_Tasks_Retain extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    public $id = 'retain';

    protected $path;
    protected $retain;
    protected $frequency;

    public function __construct($options = array())
    {
        if (!isset($options['path']))
            throw new Exception("path not set");

        if (false === @is_dir($options['path']))
            throw new Exception("path must be a valid directory");

        if (!isset($options['frequency']))
            throw new Exception("frequency not set");

        if (!array_key_exists('retain', $options))
            throw new Exception("retain not set");

        $this->path = $options['path'];
        $this->retain = $options['retain'];
        $this->frequency = $options['frequency'];
    }

    public function getState()
    {
        return array(
            'path' => $this->path,
            'retain' => $this->retain,
            'frequency' => $this->frequency
        );
    }

    public function runPartial($deadline, $hardDeadline)
    {
        if (empty($this->retain)) {
            return null;
        }

        $files = array();
        if (false !== ($dhandle = opendir($this->path))) {

            while (false !== ($file = readdir($dhandle))) {
                $filename = $this->path . DIRECTORY_SEPARATOR . $file;
                if (preg_match('/\.php$/', $file) && is_file($filename)) { // TODO: need a better detection of pending archives
                    try {
                        $fs = new AnyWay_PhpEmbeddedFs($filename);
                        if ($fs->isFinalized() && false !== ($handle = $fs->fopen(AnyWay_Constants::METADATA_SECTION, 'rb'))) {
                            $data = $fs->fread($handle, 10240);
                            if (false !== ($metadata = @unserialize($data)) && isset($metadata['frequency']) && $metadata['frequency'] == $this->frequency) {
                                $files[] = $filename;
                            }
                        }
                    } catch (Exception $e) {
                        error_log($e);
                    }
                }
            }

            if (count($files) > $this->retain) {
                sort($files);
                while (count($files) > $this->retain) {
                    $filename = array_shift($files);
                    @unlink($filename);
                }
            }

            closedir($dhandle);
        }
        return null;
    }
}

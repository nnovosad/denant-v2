<?php

class AnyWay_Tasks_EstimatePhpEmbeddedFs extends AnyWay_PhpEmbeddedFs implements AnyWay_Interface_ITask
{
    public $id = 'estimate-phpembeddedfs';

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        parent::__construct($options['filename']);
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename
        );
    }

    public function runPartial($deadline, $hardDeadline)
    {
        foreach ($this->FAT as $allocation) {
            if ($allocation['filename'] != AnyWay_Constants::PHP_SECTION)
                $this->emit("estimate", $allocation['size']);
        }
        return null;
    }
}

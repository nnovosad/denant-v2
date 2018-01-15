<?php

class AnyWay_Tasks_Finalize extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    public $id = 'finalize';

    protected $filename;

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        $this->filename = $options['filename'];
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename
        );
    }

    /**
     * @param $deadline
     * @return array|null
     */
    public function runPartial($deadline, $hardDeadline)
    {
        if (!file_exists($this->filename))
            throw new Exception("File does not exist");

        $fs = new AnyWay_PhpEmbeddedFs($this->filename);
        $fs->finalize();
        return null;
    }
}
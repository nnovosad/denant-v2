<?php

class AnyWay_Tasks_WordpressCleanup extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    public $id = 'cleanup';

    protected $adapter;

    public function __construct($options = array())
    {
        if (!isset($options['adapter']))
            throw new Exception("adapter not set");

        if (!is_array($options['adapter'])) {
            throw new Exception("Invalid adapter");
        }

        $this->adapter = $options['adapter'];
    }

    public function getState()
    {
        return array(
            'adapter' => $this->adapter
        );
    }

    /**
     * @param $deadline
     * @return array|null
     */
    public function runPartial($deadline, $hardDeadline)
    {
        $adapterClass = $this->adapter['class'];

        /* @var AnyWay_Interface_IRestoreTarget|AnyWay_Interface_IEventEmitter $adapter */
        $adapter = new $adapterClass($this->adapter['state']);

        @$adapter->unlink('.maintenance');
        return null;
    }

}

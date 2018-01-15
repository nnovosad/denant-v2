<?php

interface AnyWay_Interface_ITask extends AnyWay_Interface_IEventEmitter
{

    public function __construct($options = array());

    public function runPartial($deadline, $hardDeadline);

    public function getState();
}
<?php

interface AnyWay_Interface_IEventEmitter
{
    public function emit($event);

    public function on($event, $callback);
}

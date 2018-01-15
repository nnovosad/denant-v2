<?php

class AnyWay_EventEmitter implements AnyWay_Interface_IEventEmitter
{
    private $_events = array();

    /**
     * @param string $event
     * @return void
     */
    public function emit($event)
    {
        $args = func_get_args();
        if (isset($this->_events[$event])) {
            foreach ($this->_events[$event] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
        if (isset($this->_events['*'])) {
            foreach ($this->_events['*'] as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    /**
     * @param string $event event name
     * @param callable $callback function to call
     */
    public function on($event = null, $callback)
    {
        if (!is_callable($callback))
            throw new Exception("Callback $callback is not callable");

        if (!$event)
            $event = '*';

        if (!isset($this->_events[$event]))
            $this->_events[$event] = array();

        $this->_events[$event][] = $callback;
    }

    public function off($event = null, $callback = null)
    {
        $callbackName = null;
        if ($callback && !is_callable($callback)) {
            throw new Exception("Callback $callback is not callable");
        } elseif ($callback) {
            is_callable($callback, true, $callbackName);
        }

        $events = $event
            ? array($event)
            : array_keys($this->_events);

        foreach ($events as $event) {
            if (!empty($this->_events[$event])) {
                if ($callback) {
                    foreach ($this->_events[$event] as $i => $handler) {
                        if ($handler === $callback) {
                            unset($this->_events[$event][$i]);
                        }
                    }
                } else {
                    unset($this->_events[$event]);
                }
            }
        }
    }

    /**
     * @param null $event
     * @param AnyWay_Interface_IEventEmitter $to
     * @return void
     * @throws Exception
     */
    public function reemit($event = null, $to)
    {
        if (!($to instanceof AnyWay_Interface_IEventEmitter))
            throw new Exception("Cannot reemit to " . get_class($to));

        $this->on($event, array($to, 'emit'));
    }

    public function __destruct() {
        // doing nothing
        // echo static::class . " destroy";
    }
}
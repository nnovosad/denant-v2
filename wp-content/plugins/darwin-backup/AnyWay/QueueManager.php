<?php

class AnyWay_QueueManager extends AnyWay_EventEmitter
{

    protected $estimate = 0;
    protected $processed = 0;
    protected $written = 0;
    protected $read = 0;
    protected $logs = array();
    protected $queue = array();
    protected $currentTask;
    protected $memory_peak_usage;
    protected $estimate_multiplier = 1;

    /**
     * @param $queue
     * @throws \Exception
     */
    public function __construct($state = array())
    {
        if (isset($state['estimate']))
            $this->estimate = $state['estimate'];

        if (isset($state['processed']))
            $this->processed = $state['processed'];

        if (isset($state['written']))
            $this->written = $state['written'];

        if (isset($state['read']))
            $this->read = $state['read'];

        if (isset($state['memory_peak_usage']))
            $this->memory_peak_usage = $state['memory_peak_usage'];

        if (isset($state['estimate_multiplier']))
            $this->estimate_multiplier = $state['estimate_multiplier'];

        if (isset($state['logs']))
            $this->logs = $state['logs'];

        $this->queue = array();
        if (isset($state['queue'])) {
            foreach ($state['queue'] as $taskState) {
                if ($class = $taskState['class']) {
                    $this->queue[] = new $class($taskState['state']);
                } else {
                    throw new Exception("Task class not set for task state " . print_r($taskState, true));
                }
            }
        }
    }

    public function onLog()
    {
        // add backtrace to logs
        $this->logs[] = sprintf("[%s] %s", $this->currentTask, call_user_func_array("sprintf", func_get_args()));
    }

    public function emit($event, $value = null)
    {
        switch ($event) {
            case "estimate":
                $this->estimate += $value * $this->estimate_multiplier;
                break;
            case "processed":
                $this->processed += $value;
                break;
            case "read":
                $this->read += $value;
                break;
            case "write":
                $this->written += $value;
                break;
            default:
                call_user_func_array('parent::emit', func_get_args());
        }
    }

    /**
     * @param $queue
     * @return array|null $state
     * @throws \Exception
     */
    public function next($deadline, $hardDeadline)
    {
        if ($this->queue) {
            /* @var AnyWay_Interface_ITask|AnyWay_EventEmitter $task */
            $task = array_shift($this->queue);
            $task->reemit(null, $this);
            if ($state = $task->runPartial($deadline, $hardDeadline)) {
                $class = get_class($task);
                array_unshift($this->queue, new $class($state));
            } else {
                $this->emit($task->id . ":complete");
                if (count($this->queue)) {
                    $this->emit($this->queue[0]->id . ":started");
                }
            }
        }

        if (($memory_peak_usage = memory_get_peak_usage(false)) > $this->memory_peak_usage)
            $this->memory_peak_usage = $memory_peak_usage;

        if (100 == ($progress = $this->getProgress())) {
            $this->emit("stats", array(
                "estimate" => $this->estimate,
                "processed" => $this->processed,
                "read" => $this->read,
                "written" => $this->written,
                "memory_peak_usage" => $this->memory_peak_usage
            ));
            $this->emit("done");
        } else {
            $this->emit("progress", $progress);
        }
        return $this->getState();
    }

    /**
     * @return array
     */
    protected function getSerializedQueue()
    {
        $queue = array();
        foreach ($this->queue as $runner) {
            /* @var AnyWay_Interface_ITask $runner */
            $queue[] = array(
                'class' => get_class($runner),
                'state' => $runner->getState()
            );
        }
        return $queue;
    }

    /**
     * @return array|null
     */
    public function getState()
    {
        return $this->queue
            ? array(

                'queue' => $this->getSerializedQueue(),
                'estimate' => $this->estimate,
                'processed' => $this->processed,
                'read' => $this->read,
                'written' => $this->written,
                'memory_peak_usage' => $this->memory_peak_usage,
                'logs' => $this->logs
            )
            : null;
    }

    public function getEstimate()
    {
        return $this->estimate;
    }

    public function getProcessed()
    {
        return $this->processed;
    }

    public function getRead()
    {
        return $this->read;
    }

    public function getWritten()
    {
        return $this->written;
    }

    public function getProgress()
    {

        if (!$this->queue) {
            return 100;
        }

        $progress = $this->estimate
            ? ($this->processed / $this->estimate) * 100
            : 0;

        // a growing log (eg wp-content/debug.log) might result in > 100% completion
        // as well as innodb tables are estimated by mysql not exactly
        if ($progress > 99.99)
            $progress = 99.99;

        return $progress;
    }
}
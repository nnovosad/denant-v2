<?php

/**
 * Class AnyWay_Runner_Base
 */
// this is needed
abstract class AnyWay_Runner_Base extends AnyWay_EventEmitter
{
    protected $sid;
    protected $waitForLock = false;
    protected $timestamp;
    protected $error_level;

    /* @var AnyWay_Interface_IStateProvider $stateProvider */
    private $stateProvider;
    private $deleteStateOnDestruct = false;

    public function __construct($sid = null, $waitForLock = false)
    {
        $this->error_level = error_reporting(-1);
        $this->sid = $sid;
        $this->waitForLock = $waitForLock;
        $this->timestamp = time();
    }

    /* @return AnyWay_Interface_IStateProvider $stateProvider */
    abstract protected function _getStateProvider($sid = null, $waitForLock = false);

    /* @return AnyWay_Interface_IStateProvider $stateProvider */
    protected function getStateProvider()
    {
        if (!$this->stateProvider)
            $this->stateProvider = $this->_getStateProvider($this->sid, $this->waitForLock);
        return $this->stateProvider;
    }

    public function getState()
    {
        return $this->getStateProvider()->getState();
    }

    public function storageDir()
    {
        foreach (array(
                     sys_get_temp_dir(),
                     realpath(ini_get("upload_tmp_dir"))
                 ) as $storage_dir) {

            if (@is_writable($storage_dir) && @is_dir($storage_dir)) {
                return $storage_dir;
            }
        }
        throw new Exception("Unable to find suitable storage dir");
    }

    public function generateBackupFilename($sid)
    {
        if (!$sid)
            throw new Exception("Missing backup id");

        if ($storage_dir = $this->storageDir()) {
            return $storage_dir . DIRECTORY_SEPARATOR . date('YmdHis') . '-' . $sid . '.php';
        }
        return false;
    }

    public function nextStep($deadline, $hardDeadline)
    {
        $globalState = $this->getStateProvider()->getState();
        $manager = new AnyWay_QueueManager($globalState['queueManager']);
        $manager->reemit(null, $this);

        if ($state = $manager->next($deadline, $hardDeadline)) {
            $globalState['queueManager'] = $state;
            $this->getStateProvider()->setState($globalState);
        } else {
            $this->deleteStateOnDestruct = true;
        }
    }


    public function to_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        switch ($last) {
            case 'g':
                $val = (int) $val * 1024;
                break;
            case 'm':
                $val = (int) $val * 1024;
                break;
            case 'k':
                $val = (int) $val * 1024;
                break;
        }

        return $val;
    }

    public function from_bytes($val, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = (int) floor((strlen($val) - 1) / 3);
        return sprintf("%.{$decimals}f", $val / pow(1024, $factor)) . @$sz[$factor];
    }

    public function emit($event)
    {
        $args = func_get_args();
        if ("stats" == $event) {
            $stats = $args[1];
            if ($stats['processed']) {
                $stats['processedRatio'] = $stats['estimate'] / $stats['processed'];
                $stats['compressRatio'] = $stats['written'] / $stats['processed'];
            }
            $stats['estimate_mb'] = (float)sprintf("%.2f", $stats['estimate'] / 1024 / 1024);
            $stats['processed_mb'] = (float)sprintf("%.2f", $stats['processed'] / 1024 / 1024);
            $stats['read_mb'] = (float)sprintf("%.2f", $stats['read'] / 1024 / 1024);
            $stats['written_mb'] = (float)sprintf("%.2f", $stats['written'] / 1024 / 1024);
            $stats['memory_limit_mb'] = (float)sprintf("%.2f", $this->to_bytes(@ini_get("memory_limit")) / 1024 / 1024);
            $stats['memory_peak_usage_mb'] = (float)sprintf("%.2f", $stats['memory_peak_usage'] / 1024 / 1024);
            $stats['php_version_id'] = PHP_VERSION_ID;
            $stats['php_version_version'] = phpversion();
            unset($stats['estimate']);
            unset($stats['processed']);
            unset($stats['read']);
            unset($stats['written']);
            unset($stats['memory_peak_usage']);
            parent::emit("stats", $stats);
        } else {
            call_user_func_array('parent::emit', $args);
        }
    }

    public function stop()
    {
        $this->deleteStateOnDestruct = true;
    }

    public function __destruct()
    {
        if ($this->deleteStateOnDestruct)
            $this->getStateProvider()->deleteState();
        error_reporting($this->error_level);
    }
}

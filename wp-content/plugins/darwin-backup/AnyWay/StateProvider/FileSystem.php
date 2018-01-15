<?php

class AnyWay_StateProvider_FileSystem extends AnyWay_EventEmitter implements AnyWay_Interface_IStateProvider
{

    protected $stateId;
    protected $stateFile;
    protected $fh;
    protected $state;
    protected $dir;

    protected $prefix = "state-";
    protected $postfix = ".php";

    public function __construct($stateId = null, $waitForLock = 1)
    {
        $this->dir = $this->storageDir();

        if (!$stateId) {
            $this->stateId = uniqid('', true);
            $mode = "c+b";
        } else {
            $this->stateId = $stateId;
            $mode = "r+b";
        }

        $this->stateFile = $this->getStateFile($this->stateId);

        if (false === ($this->fh = @fopen($this->stateFile, $mode))) {
            $error = error_get_last();
            throw new Exception($error['message']);
        }

        if (!$waitForLock)
            $waitForLock = 1;

        $deadline = microtime(true) + $waitForLock;
        $locked = false;
        while (!$locked && microtime(true) <= $deadline) {
            $locked = flock($this->fh, LOCK_EX | LOCK_NB);
            if ($locked)
                break;
            usleep(1000); // 100ms
        }
        if (!$locked) {
            throw new Exception("Unable to lock state $stateId for " . $waitForLock . " seconds");
        }
    }

    protected function storageDir()
    {
        $dir = sys_get_temp_dir();
        if (@is_writable($dir))
            return $dir;

        $dir = realpath(ini_get("upload_tmp_dir"));
        if (@is_writable($dir))
            return $dir;

        throw new Exception("Cannot find a suitable temporary directory");
    }

    protected function getStateFile($stateId)
    {
        return join(DIRECTORY_SEPARATOR, array($this->dir, $this->prefix . $stateId . $this->postfix));
    }

    public function setState($state)
    {
        fseek($this->fh, 0);
        ftruncate($this->fh, 0);
        fwrite($this->fh, "<?php return " . var_export(serialize($state), true) . ";");
        fflush($this->fh);

        $this->state = $state;
    }

    public function getState()
    {
        if ($this->state)
            return $this->state;

        $serialized = preg_replace('/^<\?php\s+/', '', file_get_contents($this->stateFile));
        return $this->state = unserialize(eval($serialized));
    }

    public function getStateId()
    {
        return $this->stateId;
    }

    public function deleteState()
    {
        $this->destruct();
        @unlink($this->stateFile);
    }

    public function destruct()
    {
        if (is_resource($this->fh)) {
            flock($this->fh, LOCK_UN);
            fclose($this->fh);
            //chmod($this->stateFile, 0600);
        }
        return true;
    }

    public function __destruct()
    {
        $this->destruct();
    }
}
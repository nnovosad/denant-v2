<?php

class AnyWay_Tasks_EstimateFs extends AnyWay_DirectoryTraversal implements AnyWay_Interface_ITask
{
    public $id = 'estimate-fs';

    public function runPartial($deadline, $hardDeadline)
    {
        if (microtime(true) >= $hardDeadline) {
            return $this->getState();
        }

        $estimate = 0;
        while ($this->getNextFile()) {

            $realFile = $this->realFile($this->file);

            if (is_file($realFile) && false !== ($handle = @fopen($realFile, 'rb'))) {
                fclose($handle);
                $estimate += filesize($realFile);
            }

            if (isset($GLOBALS["anyway_iosleep"])) usleep($GLOBALS["anyway_iosleep"] * 1000000);
            if (microtime(true) >= $hardDeadline) {
                $this->emit("estimate", $estimate);
                return $this->getState();
            }
        }

        $this->emit("estimate", $estimate);
        return null;
    }
}

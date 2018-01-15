<?php

class AnyWay_Tasks_RenameFile extends AnyWay_Tasks_CopyFile
{
    public $id = 'rename';

    /**
     * @param $deadline
     * @return array|null
     */
    public function runPartial($deadline, $hardDeadline)
    {
        $fromStat = stat($this->from);
        $toStat = stat(dirname($this->to));

        // same
        if ((!isset($GLOBALS["anyway_iosleep"]) || !$GLOBALS["anyway_iosleep"]) && ($fromStat['dev'] == $toStat['dev'])) {
            rename($this->from, $this->to);
            $this->emit("processed", filesize($this->to));
            return null;
        }

        if ($state = parent::runPartial($deadline, $hardDeadline)) {
            return $state;
        }

        unlink($this->from);
        return null;
    }
}
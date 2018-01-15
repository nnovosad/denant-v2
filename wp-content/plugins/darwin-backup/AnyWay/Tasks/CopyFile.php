<?php

class AnyWay_Tasks_CopyFile extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    const BUFFER_LENGTH = 102400;

    public $id = 'copy';

    protected $from;
    protected $to;
    protected $position = 0;

    public function __construct($options = array())
    {
        if (!isset($options['from']))
            throw new Exception("From not set");

        if (!isset($options['to']))
            throw new Exception("To not set");

        $this->from = $options['from'];
        $this->to = $options['to'];

        if (isset($options['position']))
            $this->position = $options['position'];
    }

    public function getState()
    {
        return array(
            'from' => $this->from,
            'to' => $this->to,
            'position' => $this->position
        );
    }

    /**
     * @param $deadline
     * @return array|null
     */
    public function runPartial($deadline, $hardDeadline)
    {

        if (microtime(true) >= $deadline) {
            return $this->getState();
        }

        $infh = fopen($this->from, 'rb');
        $outfh = fopen($this->to, 'ab');

        if ($this->position) {
            fseek($infh, $this->position);
            fseek($outfh, $this->position);
        }

        //echo $this->position;
        while (!feof($infh)) {

            $data = fread($infh, self::BUFFER_LENGTH);
            fwrite($outfh, $data);
            $this->emit("processed", strlen($data));

            if (isset($GLOBALS["anyway_iosleep"])) usleep($GLOBALS["anyway_iosleep"] * 1000000);

            if (!feof($infh) && microtime(true) >= $deadline) {
                $position = ftell($infh);
                fclose($outfh);
                fclose($infh);
                return array(
                    'from' => $this->from,
                    'to' => $this->to,
                    'position' => $position
                );
            }
        }

        fclose($outfh);
        fclose($infh);

        return null;
    }
}
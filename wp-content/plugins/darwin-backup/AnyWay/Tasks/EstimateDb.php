<?php

class AnyWay_Tasks_EstimateDb extends AnyWay_EventEmitter implements AnyWay_Interface_ITask
{
    public $id = 'estimate-db';

    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_password;

    public function __construct($options = array())
    {
        $this->db_name = $options['db_name'];
        $this->db_user = $options['db_user'];
        $this->db_password = $options['db_password'];
        $this->db_host = $options['db_host'];
    }

    public function getState()
    {
        return array(
            'db_name' => $this->db_name,
            'db_host' => $this->db_host,
            'db_user' => $this->db_user,
            'db_password' => $this->db_password
        );
    }

    public function runPartial($deadline, $hardDeadline)
    {
        /* @var \mysqli $handler */
        if (2 == count($parts = explode(":", $this->db_host))) {
            if (preg_match('/^\d+$/', $parts[1])) {
                $handler = new mysqli(
                    $parts[0],
                    $this->db_user,
                    $this->db_password,
                    $this->db_name,
                    $parts[1]
                );
            } else {
                $handler = new mysqli(
                    $parts[0],
                    $this->db_user,
                    $this->db_password,
                    $this->db_name,
                    null,
                    $parts[1]
                );
            }
        } else {
            $handler = new mysqli(
                $this->db_host,
                $this->db_user,
                $this->db_password,
                $this->db_name
            );
        }

        if ($handler->connect_errno) {
            throw new Exception("Connection to mysql failed with message: " . $handler->connect_error);
        }

        $total = 0;
        $schema = $handler->escape_string($this->db_name);
        if ($result = $handler->query("SELECT table_name, table_rows, SUM(data_length) as total FROM information_schema.tables WHERE table_schema = '$schema' GROUP BY table_name, table_rows, data_length")) {
            while ($obj = $result->fetch_array(MYSQLI_ASSOC)) {
                $total += $obj['total'];
            }
        } else {
            throw new Exception($handler->error);
        }

        $this->emit("estimate", $total);
        //$this->emit('estimate-db:complete');
        return null;
    }
}

<?php

class AnyWay_Tasks_Mysqlrestore extends AnyWay_PhpEmbeddedFsArchive implements AnyWay_Interface_ITask
{
    public $id = 'mysqlrestore';

    protected $currentBlock;
    protected $unprocessedData;
    protected $db_host;
    protected $db_user;
    protected $db_password;
    protected $db_name;
    protected $table;
    protected $settings;
    protected $replacements;
    protected $verifyOnly = false;

    /* @var mysqli */
    private $dbHandler;
    private $dbVersion;

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        parent::__construct($options['filename']);

        if (isset($options['currentBlock']) && $options['currentBlock'] !== null)
            $this->currentBlock = $options['currentBlock'];

        if (isset($options['unprocessedData']))
            $this->unprocessedData = $options['unprocessedData'];
        else
            $this->unprocessedData = '';

        if (isset($options['db_host']))
            $this->db_host = $options['db_host'];

        if (isset($options['db_user']))
            $this->db_user = $options['db_user'];

        if (isset($options['db_password']))
            $this->db_password = $options['db_password'];

        if (isset($options['db_name']))
            $this->db_name = $options['db_name'];

        if (isset($options['table']))
            $this->table = $options['table'];

        if (isset($options['settings']))
            $this->settings = $options['settings'];

        if (isset($options['replacements']))
            $this->replacements = $options['replacements'];

        if (isset($options['verifyOnly']))
            $this->verifyOnly = $options['verifyOnly'];
        else
            $this->verifyOnly = false;
    }

    public function emit($event, $message = null)
    {
        if ($event == 'read') {
            parent::emit("processed", $message);
        } else {
            call_user_func_array('parent::emit', func_get_args());
        }
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename,
            'currentBlock' => $this->currentBlock,
            'unprocessedData' => $this->unprocessedData,
            'db_host' => $this->db_host,
            'db_user' => $this->db_user,
            'db_password' => $this->db_password,
            'db_name' => $this->db_name,
            'table' => $this->table,
            'settings' => $this->settings,
            'replacements' => $this->replacements,
            'verifyOnly' => $this->verifyOnly
        );
    }

    protected function query($statement)
    {
        if ($this->verifyOnly)
            return true;

        if ($result = $this->dbHandler->query($statement)) {
            return $result;
        }

        if (preg_match('/CREATE TABLE/i', $statement)) {

            // degrading
            $statement = preg_replace('/COLLATE utf8mb4_unicode_520_ci/i', 'COLLATE utf8mb4_unicode_ci', $statement);
            $statement = preg_replace('/COLLATE=utf8mb4_unicode_520_ci/i', 'COLLATE=utf8mb4_unicode_ci', $statement);

            // retrying degraded statement
            if ($result = $this->dbHandler->query($statement)) {
                return $result;
            }

            // degrading even more
            $statement = preg_replace('/CHARSET=utf8mb4/i', 'CHARSET=utf8', $statement);
            $statement = preg_replace('/COLLATE utf8mb4_unicode_ci/i', 'COLLATE utf8_unicode_ci', $statement);
            $statement = preg_replace('/COLLATE=utf8mb4_unicode_ci/i', 'COLLATE=utf8_unicode_ci', $statement);

            // retrying degraded statement
            if ($result = $this->dbHandler->query($statement)) {
                return $result;
            }
        }

        error_log("Failed to run \"$statement\": " . $this->dbHandler->error);
        $this->emit("warning", "Failed to run \"$statement\": " . $this->dbHandler->error);
        return false;
    }

    public function runPartial($deadline, $hardDeadline)
    {

        if (microtime(true) >= $deadline) {
            return $this->getState();
        }

        if (2 == count($parts = explode(":", $this->db_host))) {
            if (preg_match('/^\d+$/', $parts[1])) {
                $this->dbHandler = new mysqli(
                    $parts[0],
                    $this->db_user,
                    $this->db_password,
                    $this->db_name,
                    $parts[1]
                );
            } else {
                $this->dbHandler = new mysqli(
                    $parts[0],
                    $this->db_user,
                    $this->db_password,
                    $this->db_name,
                    null,
                    $parts[1]
                );
            }
        } else {
            $this->dbHandler = new mysqli(
                $this->db_host,
                $this->db_user,
                $this->db_password,
                $this->db_name
            );
        }

        if ($this->dbHandler->connect_errno) {
            throw new Exception($this->dbHandler->connect_error);
        }
        // Fix for always-unicode output
        $this->dbHandler->query("SET NAMES UTF8");
        $this->dbVersion = $this->dbHandler->server_version;

        foreach ((array)$this->settings as $statement) {
            $this->query($statement);
        }

        if (false === ($tarfh = $this->gzopen(AnyWay_Constants::DUMP_SECTION, 'rb'))) {
            throw new Exception("Unable to open " . AnyWay_Constants::DUMP_SECTION . " for read");
        }

        if ($this->currentBlock)
            $this->gzseek($tarfh, $this->currentBlock);

        $data = $this->unprocessedData;

        if (false !== ($chunk = $this->gzreadblock($tarfh))) {
            $data .= $chunk;
        } else {
            if ($data)
                throw new Exception("Unprocessed queries: \"" . $data . "\"");
            $data = false;
        }

        $settings = array();
        $locked = false;

        while (false !== $data) {
            // this will still break on stored procedures
            if ($data) {
                if (";\n" == substr($data, strlen($data) - 2)) {
                    $lines = explode("\n", $data);
                    $data = '';
                } else {
                    $lines = explode("\n", $data);
                    $data = array_pop($lines);
                }
                $statement = '';
                while ($lines) {
                    $line = array_shift($lines);
                    if (strpos($line, '--') !== 0) {
                        if (";" == substr($line, strlen($line) - 1)) {

                            $statement .= $line;

                            while (preg_match('/\/\*\!(\d{5})\s(.*?)\*\//', $statement, $matches)) {
                                if ($this->dbVersion >= $matches[1])
                                    $statement = str_replace($matches[0], $matches[2], $statement);
                            }

                            if (preg_match('/^DROP\s+TABLE\s+IF\s+EXISTS\s+`([^`]+)`/', $statement, $matches))
                                $this->table = $matches[1];

                            if (preg_match('/^LOCK\s+TABLES/', $statement, $matches))
                                $locked = true;

                            if (preg_match('/^INSERT\s+INTO/', $statement, $matches) && !$locked) {
                                $this->query("LOCK TABLES `{$this->table}` WRITE;");
                                if ($this->dbVersion >= 40000)
                                    $this->query("ALTER TABLE `{$this->table}` DISABLE KEYS;");
                                $this->query("SET autocommit=0;");
                                $locked = true;
                            }

                            if (!$this->settings && !$this->table && preg_match('/^SET /i', $statement))
                                $settings[] = $statement;

                            if ($this->replacements && preg_match('/^INSERT\s+INTO/', $statement, $matches)) {
                                $statement = $this->replace($statement);
                            }

                            $this->query($statement);
                            $statement = '';

                        } else {

                            if ($line)
                                $statement .= $line . "\n";

                        }
                    }
                }
            }

            if (microtime(true) >= $deadline) {
                $currentBlock = $this->gztell($tarfh);
                $this->gzclose($tarfh);

                if ($settings)
                    $this->settings = $settings;

                if ($locked) {
                    if ($this->dbVersion > 40000)
                        $this->query("ALTER TABLE `{$this->table}` ENABLE KEYS;");
                    $this->query("UNLOCK TABLES;");
                    $this->query("COMMIT;");
                    // we re going to return, so no need to SET autocommit=1
                }

                $this->dbHandler = null;
                return array(
                    'filename' => $this->filename,
                    'currentBlock' => $currentBlock,
                    'unprocessedData' => $data,
                    'db_host' => $this->db_host,
                    'db_user' => $this->db_user,
                    'db_password' => $this->db_password,
                    'db_name' => $this->db_name,
                    'table' => $this->table,
                    'settings' => $this->settings,
                    'replacements' => $this->replacements,
                    'verifyOnly' => $this->verifyOnly
                );
            }

            if (false !== ($chunk = $this->gzreadblock($tarfh))) {
                $data .= $chunk;
            } else {
                if ($data)
                    throw new Exception("Unprocessed queries: \"" . $data . "\"");
                $data = false;
            }
        }

        return null;
    }

    public function recursive_replace($string)
    {
        $start = 0;
        while (preg_match('/s:(\d+)\:([\"\'])/', $string, $matches, 0, $start)) {
            if (false !== ($pos = strpos($string, $matches[0], $start))) {
                $content = substr($string, $pos + strlen($matches[0]), $matches[1]);
                //$start = $pos + strlen($matches[0]) + $matches[1] + 2;
                $result = $this->recursive_replace($content);
                foreach ($this->replacements as $from => $to) {
                    $result = str_replace($from, $to, $result);
                }
                $string = str_replace($matches[0] . $content . $matches[2], "s:" . strlen($result) . ":" . $matches[2] . $result . $matches[2], $string);
                $start = $pos + 1;
            } else {
                throw new Exception("Should not happen ever");
            }
        }
        return $string;
    }

    public function replace($line)
    {
        $serializeds = array();
        $old_backtrack_limit = ini_set('pcre.backtrack_limit', PHP_INT_MAX);
        $line = preg_replace_callback('/\'(\w:\d+\:(.*?(?<!\\\)))\'/', function ($match) use (&$serializeds) {
            $serializeds[] = $match[1];
            return "'#SERIALIZED#'";
        }, $line);
        ini_set('pcre.backtrack_limit', $old_backtrack_limit);

        foreach ($this->replacements as $from => $to) {
            $line = str_replace($from, $to, $line);
        }

        foreach ($serializeds as $serialized) {
            $obj = $this->recursive_replace(stripcslashes($serialized));
            // See http://php.net/manual/en/mysqli.real-escape-string.php, which says:
            // Characters encoded are NUL (ASCII 0), \n, \r, \, ', ", and Control-Z.
            $line = substr_replace($line, addcslashes($obj, "\000\r\n\\\'\"\032"), strpos($line, '#SERIALIZED#'), strlen('#SERIALIZED#'));
        }

        return $line;
    }
}
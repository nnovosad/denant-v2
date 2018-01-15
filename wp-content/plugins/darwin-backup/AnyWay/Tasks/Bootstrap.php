<?php

class AnyWay_Tasks_Bootstrap extends AnyWay_PhpEmbeddedFs implements AnyWay_Interface_ITask
{
    public $id = 'bootstrap';

    protected $handle;
    protected $classes = array(
        'AnyWay_Interface_IStateProvider',
        'AnyWay_Interface_IRestoreTarget',
        'AnyWay_Interface_IEventEmitter',
        'AnyWay_Interface_ITask',
        'AnyWay_Constants',
        'AnyWay_EventEmitter',
        'AnyWay_StateProvider_FileSystem',
        'AnyWay_FTP_Base',
        'AnyWay_FTP_Pure',
        'AnyWay_FTP_Sockets',
        'AnyWay_RestoreTarget_FileSystem',
        'AnyWay_RestoreTarget_FTP',
        'AnyWay_QueueManager',
        'AnyWay_PhpEmbeddedFs',
        'AnyWay_PhpEmbeddedFsArchive',
        'AnyWay_Tasks_EstimatePhpEmbeddedFs',
        'AnyWay_Tasks_Decompress',
        'AnyWay_Tasks_Mysqlrestore',
        'AnyWay_Tasks_WordpressCleanup',
        'AnyWay_Planner_Restore',
        'AnyWay_Runner_Base',
        'AnyWay_Runner_Restore',
        'AnyWay_Crypt_PasswordHash',
        'AnyWay_Helper_Server',
        'AnyWay_Helper_Filesystem',
        'AnyWay_Helper_FTP',
        'AnyWay_Helper_Mysql',
        /*
                'Rollbar',
                'RollbarNotifier'
        */
    );

    protected $sourcesOfClasses = array();

    protected $server_name;
    protected $source;
    protected $source_dir;
    protected $root;
    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_password;
    protected $send_stats;
    protected $metadata;

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        parent::__construct($options['filename']);

        if (!isset($options['source']))
            throw new Exception("source not set");

        // we need source to pack related files
        if (!file_exists($options['source']))
            throw new Exception('source ' . $options['source'] . ' not found');

        if (!isset($options['server_name']))
            throw new Exception("server name not set");

        if (!isset($options['root']))
            throw new Exception("root not set");

        if (!isset($options['db_host']))
            throw new Exception("db_host not set");

        if (!isset($options['db_name']))
            throw new Exception("db_name not set");

        if (!isset($options['db_user']))
            throw new Exception("db_user user not set");

        if (!isset($options['db_password']))
            throw new Exception("db_password not set");

        if (!isset($options['send_stats']))
            throw new Exception("send_stats not set");

        $this->server_name = $options['server_name'];
        $this->source = $options['source'];
        $this->root = $options['root'];
        $this->db_name = $options['db_name'];
        $this->db_user = $options['db_user'];
        $this->db_password = $options['db_password'];
        $this->db_host = $options['db_host'];
        $this->send_stats = $options['send_stats'];

        if (isset($options['metadata']))
            $this->metadata = $options['metadata'];
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename,
            'server_name' => $this->server_name,
            'source' => $this->source,
            'root' => $this->root,
            'db_name' => $this->db_name,
            'db_user' => $this->db_user,
            'db_password' => $this->db_password,
            'db_host' => $this->db_host,
            'send_stats' => $this->send_stats,
            'metadata' => $this->metadata
        );
    }

    public function emit($event, $message = null)
    {
        if ($event == 'write') {
            parent::emit("processed", $message);
        } else {
            call_user_func_array('parent::emit', func_get_args());
        }
    }

    public function runPartial($deadline, $hardDeadline)
    {
        if (false !== ($handle = $this->fopen(AnyWay_Constants::PHP_SECTION, "ab"))) {
            $this->fseek($handle, 0);
            $this->writeClasses($handle);
            $this->writeSourcesOfClasses($handle);
            //$this->writeLogs($handle);
            $this->writeSource($handle);
            $this->writeHaltCompiler($handle);
            $this->fclose($handle);

            if (false !== ($handle = $this->fopen(AnyWay_Constants::METADATA_SECTION, "ab"))) {
                $this->fwrite($handle, @serialize($this->metadata));
                $this->fclose($handle);
                return null;
            }
            throw new Exception("Unable to write metadata section");
        }
        throw new Exception("Unable to write code section");
    }

    protected function writeClasses($handle)
    {
        foreach ($this->classes as $name) {
            if (class_exists($name) || interface_exists($name)) {
                $class = new ReflectionClass($name);
                if ($filename = $class->getFileName()) {
                    $start = $class->getStartLine() - 1; // getStartLine() seems to start after the {, we want to include the signature
                    $end = $class->getEndLine();
                    $num = $end - $start;
                    // not perfect; if the class starts or ends on the same line as something else, this will be incorrect
                    $source = implode('', array_slice(file($filename), $start, $num));
                    $this->fwrite($handle, $source . "\n");
                } else {
                    throw new Exception("File not found for class $name");
                }
            } else {
                throw new Exception("Class $name not found");
            }
        }
    }

    protected function writeSourcesOfClasses($handle)
    {
        foreach ($this->sourcesOfClasses as $name) {
            if (class_exists($name) || interface_exists($name)) {
                $class = new ReflectionClass($name);
                if ($filename = $class->getFileName()) {
                    $source = file_get_contents($filename);
                    if (false !== ($start = strpos($source, '<?php'))) {
                        $source = substr($source, $start + 5);
                    }
                    $this->fwrite($handle, $source . "\n");
                } else {
                    throw new Exception("File not found for class $name");
                }
            } else {
                throw new Exception("Class $name not found");
            }
        }
    }

    protected function replacePlaceholders($data)
    {
        $data = str_replace('ANYWAY_SERVER_NAME_PLACEHOLDER', var_export($this->server_name, true), $data);
        $data = str_replace('ANYWAY_ROOT_PLACEHOLDER', var_export($this->root, true), $data);
        $data = str_replace('ANYWAY_DB_HOST_PLACEHOLDER', var_export($this->db_host, true), $data);
        $data = str_replace('ANYWAY_DB_NAME_PLACEHOLDER', var_export($this->db_name, true), $data);
        $data = str_replace('ANYWAY_DB_USER_PLACEHOLDER', var_export($this->db_user, true), $data);
        $data = str_replace('ANYWAY_DB_PASSWORD_PLACEHOLDER', var_export($this->db_password, true), $data);
        $data = str_replace('ANYWAY_SEND_STATS_PLACEHOLDER', var_export($this->send_stats, true), $data);
        return $data;
    }

    protected function writeSource($handle)
    {
        $dt = new AnyWay_DirectoryTraversal(array(
            'root' => dirname(realpath($this->source)),
            'exclude' => array(
                basename($this->source)
            )
        ));

        $files = array();
        while ($file = $dt->getNextFile()) {
            if (is_file($realFile = $dt->realFile($file))) {
                $files[$file] = $realFile;
            }
        }

        $data = file_get_contents($this->source);
        if (false !== ($start = strpos($data, '<?php'))) {
            $data = substr($data, $start + 5);
        }

        if ($data) {
            $data = preg_replace('/\/\*\*\s*strip.*?\/strip\s*\*\//ms', '', $data);
            $data = $this->replacePlaceholders($data);
            $data = str_replace('ANYWAY_TEMPLATES_PLACEHOLDER', var_export($files, true), $data);
            foreach ($files as $key => $realFile) {
                $data = str_replace($realFile, base64_encode(file_get_contents($realFile)), $data);
            }
            $this->fwrite($handle, $data);
        }
    }

//    protected function writeLogs($handle)
//    {
//        $this->fwrite($handle, "\n");
//        $this->fwrite($handle, "class AnyWay_Logs {\n");
//        $this->fwrite($handle, "    public static function get() {\n");
//        $this->fwrite($handle, "        return " . var_export($this->logs, true) . ";\n");
//        $this->fwrite($handle, "    }\n");
//        $this->fwrite($handle, "}\n");
//        $this->fwrite($handle, "\n");

//    }

    protected function writeHaltCompiler($handle)
    {
        $this->fwrite($handle, "\n__halt_compiler();\n");
    }
}

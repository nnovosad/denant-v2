<?php

class AnyWay_Tasks_Mysqldump extends AnyWay_PhpEmbeddedFsArchive implements AnyWay_Interface_ITask
{
    const MAXLINESIZE = 90000; // fits data better into 2x65k blocks

    public $id = 'mysqldump';

    protected $db_host;
    protected $db_name;
    protected $db_user;
    protected $db_password;
    protected $table;
    protected $position;
    protected $ignore = false;

    /* @var mysqli */
    protected $dbHandler;
    protected $dbVersion;

    protected $mysqlTypes = array(
        'numerical' => array(
            'bit',
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'integer',
            'bigint',
            'real',
            'double',
            'float',
            'decimal',
            'numeric'
        ),
        'blob' => array(
            'tinyblob',
            'blob',
            'mediumblob',
            'longblob',
            'binary',
            'varbinary',
            'bit'
        )
    );

    protected $tableColumnTypes = array();

    private $tableNames = array();
    private $rowSizes = array();
    private $avgRowSize = 16384;

    public function __construct($options = array())
    {
        if (!isset($options['filename']))
            throw new Exception("filename not set");

        parent::__construct($options['filename']);

        $this->db_host = $options['db_host'];
        $this->db_name = $options['db_name'];
        $this->db_user = $options['db_user'];
        $this->db_password = $options['db_password'];

        if (isset($options['table'])) {
            $this->table = $options['table'];
            $this->position = $options['position'];
            $this->ignore = $options['ignore'];
        }
    }

    public function getState()
    {
        return array(
            'filename' => $this->filename,
            'db_host' => $this->db_host,
            'db_name' => $this->db_name,
            'db_user' => $this->db_user,
            'db_password' => $this->db_password,
            'table' => $this->table,
            'position' => $this->position,
            'ignore' => $this->ignore
        );
    }

    public function getRowSizes()
    {
        $schema = $this->dbHandler->escape_string($this->db_name);
        $lengths = array();
        if ($result = $this->dbHandler->query("SELECT TABLE_NAME AS tbl_name, AVG_ROW_LENGTH as row_length FROM information_schema.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='$schema'")) {
            while ($obj = $result->fetch_array(MYSQLI_ASSOC)) {
                $this->rowSizes[$obj['tbl_name']] = (int)$obj['row_length'];
                $lengths[] = (int)$obj['row_length'];
            }

            $this->avgRowSize = $lengths
                ? array_sum($lengths) / count($lengths)
                : 16384;

            if (!$this->avgRowSize)
                $this->avgRowSize = 16384;

        } else {
            throw new Exception($this->dbHandler->error);
        }
    }

    public function getTableNames($currentTableName = '')
    {
        if (!$this->tableNames) {
            if (!$this->rowSizes)
                $this->getRowSizes();
            foreach ($this->rowSizes as $tableName => $size) {
                if (strcmp($tableName, $currentTableName) > 0) {
                    array_push($this->tableNames, $tableName);
                }
            }
            sort($this->tableNames);
            //$this->getRowSizes();
        } else {
            if ($this->tableNames[0] !== $currentTableName)
                throw new Exception("Top table in the cache is not $currentTableName");
            array_shift($this->tableNames);
        }
        return $this->tableNames;
    }

    public function getNextTable($currentTableName = '')
    {
        if ($tableNames = $this->getTableNames($currentTableName)) {
            return $tableNames[0];
        }
        return false;
    }

    /**
     * Decode column metadata and fill info structure.
     * type, is_numeric and is_blob will always be available.
     *
     * @param array $colType Array returned from "SHOW COLUMNS FROM tableName"
     * @return array
     */
    public function parseColumnType($colType)
    {
        $colInfo = array();
        $colParts = explode(" ", $colType['Type']);

        if ($fparen = strpos($colParts[0], "(")) {
            $colInfo['type'] = substr($colParts[0], 0, $fparen);
            $colInfo['length'] = str_replace(")", "", substr($colParts[0], $fparen + 1));
            $colInfo['attributes'] = isset($colParts[1]) ? $colParts[1] : NULL;
        } else {
            $colInfo['type'] = $colParts[0];
        }
        $colInfo['is_numeric'] = in_array($colInfo['type'], $this->mysqlTypes['numerical']);
        $colInfo['is_blob'] = in_array($colInfo['type'], $this->mysqlTypes['blob']);

        return $colInfo;
    }

    /**
     * Build SQL List of all columns on current table
     *
     * @param string $tableName Name of table to get columns
     *
     * @return string SQL sentence with columns
     */
    function getColumnStmt($tableName)
    {
        $colStmt = array();
        foreach ($this->tableColumnTypes[$tableName] as $colName => $colType) {
            if ($colType['type'] == 'bit') {
                $colStmt[] = "LPAD(HEX(`${colName}`),2,'0') AS `${colName}`";
            } else if ($colType['is_blob']) {
                $colStmt[] = "HEX(`${colName}`) AS `${colName}`";
            } else {
                $colStmt[] = "`${colName}`";
            }
        }
        $colStmt = implode($colStmt, ",");

        return $colStmt;
    }


    /**
     * Escape values with quotes when needed
     *
     * @param string $tableName Name of table which contains rows
     * @param array $row Associative array of column names and values to be quoted
     *
     * @return string
     */
    protected function escape($tableName, $row)
    {
        $ret = array();
        $columnTypes = $this->tableColumnTypes[$tableName];
        foreach ($row as $colName => $colValue) {
            if (is_null($colValue)) {
                $ret[] = "NULL";
            } elseif ($columnTypes[$colName]['is_blob']) {
                if ($columnTypes[$colName]['type'] == 'bit' || !empty($colValue)) {
                    $ret[] = "0x${colValue}";
                } else {
                    $ret[] = "''";
                }
            } elseif ($columnTypes[$colName]['is_numeric']) {
                $ret[] = $colValue;
            } else {
                $ret[] = "'" . $this->dbHandler->real_escape_string($colValue) . "'"; // same as ->real_escape_string($colValue);
            }
        }
        return $ret;
    }

    public function getTableOrderBy($tableName)
    {
        if ($result = $this->dbHandler->query("SHOW INDEXES FROM `$tableName`;")) {
            $columns = array();
            while ($index = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($index['Key_name'] == 'PRIMARY') {
                    foreach (explode(',', $index['Column_name']) as $column) {
                        $columns[] = "`$column` ASC";
                    }
                }
            }
            $result->close();
            return $columns
                ? "ORDER BY " . join(", ", $columns)
                : '';
        }
        throw new Exception($this->dbHandler->error);
    }

    public function readTableStructure($tableName)
    {
        if ($result = $this->dbHandler->query("SHOW COLUMNS FROM `$tableName`;")) {
            $columnTypes = array();
            while ($col = $result->fetch_array(MYSQLI_ASSOC)) {
                $types = $this->parseColumnType($col);
                $columnTypes[$col['Field']] = array(
                    'is_numeric' => $types['is_numeric'],
                    'is_blob' => $types['is_blob'],
                    'type' => $types['type']
                );
            }
            $this->tableColumnTypes[$tableName] = $columnTypes;
            $result->close();
            return;
        }
        throw new Exception($this->dbHandler->error);
    }

    public function getViews()
    {
        $views = '';
        $schema = $this->dbHandler->escape_string($this->db_name);
        if ($result = $this->dbHandler->query("SELECT TABLE_NAME AS tbl_name FROM information_schema.TABLES WHERE TABLE_TYPE='VIEW' AND TABLE_SCHEMA='$schema'")) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $viewName = $row['tbl_name'];
                $views .=
                    "--\n".
                    "-- Table structure for view `$viewName`\n" .
                    "--\n" .
                    "\n" .
                    "DROP TABLE IF EXISTS `$viewName`;\n" .
                    "/*!50001 DROP VIEW IF EXISTS `$viewName`*/;\n";

                if ($result2 = $this->dbHandler->query("SHOW CREATE VIEW `$viewName`")) {
                    while ($row = $result2->fetch_array(MYSQLI_ASSOC)) {
                        $ret = "";
                        if (!isset($row['Create View'])) {
                            throw new Exception("Error getting view structure, unknown output");
                        }

                        $triggerStmt = $row['Create View'];
                        $triggerStmtReplaced1 = str_replace(
                            "CREATE ALGORITHM",
                            "/*!50001 CREATE ALGORITHM",
                            $triggerStmt
                        );
                        $triggerStmtReplaced2 = str_replace(
                            " DEFINER=",
                            " */\n/*!50013 DEFINER=",
                            $triggerStmtReplaced1
                        );
                        $triggerStmtReplaced3 = str_replace(
                            " VIEW ",
                            " */\n/*!50001 VIEW ",
                            $triggerStmtReplaced2
                        );
                        if (false === $triggerStmtReplaced1 ||
                            false === $triggerStmtReplaced2 ||
                            false === $triggerStmtReplaced3
                        ) {
                            $triggerStmtReplaced = $triggerStmt;
                        } else {
                            $triggerStmtReplaced = $triggerStmtReplaced3 . " */;";
                        }

                        $views .= $triggerStmtReplaced . "\n\n";
                    }
                } else {
                    throw new Exception($this->dbHandler->error);
                }
            }
            return $views;
        }
        throw new Exception($this->dbHandler->error);
    }

    public function getTriggers()
    {
        $triggers = '';
        $schema = $this->dbHandler->escape_string($this->db_name);
        if ($result = $this->dbHandler->query("SELECT TABLE_NAME AS tbl_name FROM information_schema.TABLES WHERE TABLE_TYPE='TRIGGER' AND TABLE_SCHEMA='$schema'")) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $triggerName = $row['tbl_name'];
                $triggers .=
                    "--\n" .
                    "-- Structure for trigger `$triggerName`\n" .
                    "--\n" .
                    "\n" .
                    "DROP TRIGGER IF EXISTS `$triggerName`;\n";

                if ($result2 = $this->dbHandler->query("SHOW CREATE TRIGGER `$triggerName`")) {
                    while ($row = $result2->fetch_array(MYSQLI_ASSOC)) {
                        if (!isset($row['SQL Original Statement'])) {
                            throw new Exception("Error getting trigger code, unknown output");
                        }

                        $triggerStmt = $row['SQL Original Statement'];
                        $triggerStmtReplaced = str_replace(
                            "CREATE DEFINER",
                            "/*!50003 CREATE*/ /*!50017 DEFINER",
                            $triggerStmt
                        );
                        $triggerStmtReplaced = str_replace(
                            " TRIGGER",
                            "*/ /*!50003 TRIGGER",
                            $triggerStmtReplaced
                        );
                        if (false === $triggerStmtReplaced) {
                            $triggerStmtReplaced = $triggerStmt;
                        }
                        $triggers .=
                            "DELIMITER ;;\n" .
                            $triggerStmtReplaced . "*/;;\n" .
                            "DELIMITER ;\n\n";
                    }
                } else {
                    throw new Exception($this->dbHandler->error);
                }
            }
            return $triggers;
        }
        throw new Exception($this->dbHandler->error);
    }

    public function connect()
    {
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
            throw new Exception("Connection to mysql failed with message: " . $this->dbHandler->connect_error);
        }
        // Fix for always-unicode output
        $this->dbHandler->query("SET NAMES UTF8");

        // Store server version
        $this->dbVersion = $this->dbHandler->server_info;
    }

    public function getHeader()
    {
        if ($result = $this->dbHandler->query("SHOW VARIABLES LIKE 'character_set_database';")) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $charset = $row['Value'];
            $result->close();
        } else {
            throw new Exception($this->dbHandler->error);
        }

        if ($result = $this->dbHandler->query("SHOW VARIABLES LIKE 'collation_database';")) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $collation = $row['Value'];
            $result->close();
        } else {
            throw new Exception($this->dbHandler->error);
        }

        return
            "-- Darwin Backup Mysql Dump http://darwinapps.com/\n" .
            "--\n" .
            "-- Host: {$this->db_host}\tDatabase: {$this->db_name}\n" .
            "-- ------------------------------------------------------\n" .
            "-- Server version \t" . $this->dbVersion . "\n" .
            "-- Date: " . date('r') . "\n" .
            "\n" .
            "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n" .
            "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n" .
            "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n" .
            "/*!40101 SET NAMES UTF8 */;\n" .
            "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n" .
            "/*!40103 SET TIME_ZONE='+00:00' */;\n" .
            "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n" .
            "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n" .
            "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n" .
            "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n" .
            "\n";
    }

    public function getFooter()
    {
        return
            "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n" .
            "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n" .
            "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n" .
            "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n" .
            "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n" .
            "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n" .
            "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n" .
            "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n" .
            "\n" .
            "-- Dump completed on: " . date('r') . "\n";
    }

    public function getTableHeader($tableName)
    {
        if ($result = $this->dbHandler->query("SHOW CREATE TABLE `$tableName`")) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $result->close();

            if (!isset($row['Create Table'])) {
                throw new Exception("Error getting table code, unknown output");
            }

            return
                "--\n" .
                "-- Table structure for table `$tableName`\n" .
                "--\n\n" .
                "DROP TABLE IF EXISTS `$tableName`;\n" .
                "/*!40101 SET @saved_cs_client     = @@character_set_client */;\n" .
                "/*!40101 SET character_set_client = UTF8 */;\n" .
                $row['Create Table'] . ";\n" .
                "/*!40101 SET character_set_client = @saved_cs_client */;\n" .
                "\n";
        }
        throw new Exception($this->dbHandler->error);
    }

    function getValuesHeader($tableName)
    {
        return
            "--\n" .
            "-- Dumping data for table `$tableName`\n" .
            "--\n" .
            "\n" .
            "LOCK TABLES `$tableName` WRITE;\n" .
            "/*!40000 ALTER TABLE `$tableName` DISABLE KEYS */;\n" .
            "SET autocommit=0;\n";
    }

    function getValuesFooter($tableName)
    {
        return
            "/*!40000 ALTER TABLE `$tableName` ENABLE KEYS */;\n" .
            "UNLOCK TABLES;\n" .
            "COMMIT;\n" .
            "SET autocommit=1;\n" .
            "\n";
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

        // Connect to database
        $this->connect();

        // Create output file
        if (false === ($sqlfh = $this->gzopen(AnyWay_Constants::DUMP_SECTION, 'ab'))) {
            throw new Exception("Unable to open " . AnyWay_Constants::DUMP_SECTION . ' section');
        }

        if (!$this->table) {
            // Header
            $this->gzwrite($sqlfh, $this->getHeader());
            $this->table = $this->getNextTable();
        } else {
            $this->getRowSizes();
        }

        while ($this->table) {

            if (microtime(true) > $deadline) {
                $this->gzclose($sqlfh);
                return $this->getState();
            }

            if ($pos = $this->position) {

            } else {

                $this->gzwrite($sqlfh, $this->getTableHeader($this->table));
                $this->gzwrite($sqlfh, $this->getValuesHeader($this->table));
                $pos = 0;
            }

            $this->readTableStructure($this->table);
            $orderBy = $this->getTableOrderBy($this->table);

            $rowSize = isset($this->rowSizes[$this->table]) && $this->rowSizes[$this->table]
                ? $this->rowSizes[$this->table]
                : $this->avgRowSize;
            $chunkSize = defined('PHPUNIT_RUNNING')
                ? 5000
                : (int)(10000000 / $rowSize); // reading chunks of 10mb of data

            $this->dbHandler->query("LOCK TABLES `{$this->table}` READ LOCAL");
            $hasData = true;

            while ($hasData) {

                // this is from parent class
                $onlyOnce = true;
                $lineSize = 0;

                $colStmt = $this->getColumnStmt($this->table);

                $count = 0;
                if ($result = $this->dbHandler->query("SELECT $colStmt FROM `{$this->table}` $orderBy LIMIT $pos, $chunkSize")) {
                    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $vals = $this->escape($this->table, $row);
                        if ($onlyOnce) {
                            $insert = $this->ignore ? "INSERT IGNORE" : "INSERT";
                            $lineSize += $this->gzwrite($sqlfh, "{$insert} INTO `{$this->table}` VALUES (" . implode(",", $vals) . ")");
                            $onlyOnce = false;
                        } else {
                            $lineSize += $this->gzwrite($sqlfh, ",(" . implode(",", $vals) . ")");
                        }
                        if ($lineSize > self::MAXLINESIZE) {
                            $onlyOnce = true;
                            $lineSize = 0;
                            $this->gzwrite($sqlfh, ";\n");
                            $this->gzflush($sqlfh);
                        }
                        $this->emit("processed", $this->rowSizes[$this->table]);
                        $count++;
                    }
                    if ($count && isset($GLOBALS["anyway_iosleep"])) usleep($GLOBALS["anyway_iosleep"] * 1000000);
                } else {
                    throw new Exception($this->dbHandler->error);
                }

                if (!$onlyOnce) {
                    $this->gzwrite($sqlfh, ";\n");
                    $this->gzflush($sqlfh);
                }

                $pos += $count;
                $hasData = $count == $chunkSize;

                if (microtime(true) > $hardDeadline) {
                    $this->emit("warning", "hard deadline hit at table {$this->table}");
                    $this->dbHandler->query("UNLOCK TABLES");
                    $this->gzclose($sqlfh);
                    return array(
                        'filename' => $this->filename,
                        'db_host' => $this->db_host,
                        'db_user' => $this->db_user,
                        'db_password' => $this->db_password,
                        'db_name' => $this->db_name,
                        'table' => $this->table,
                        'position' => $pos,
                        'ignore' => true
                    );
                }
            }
            $this->gzwrite($sqlfh, $this->getValuesFooter($this->table));

            $this->dbHandler->query("UNLOCK TABLES");
            $this->table = $this->getNextTable($this->table);
            $this->position = 0;
            $this->ignore = false;
        }


        $this->gzwrite($sqlfh, $this->getViews());
        $this->gzwrite($sqlfh, $this->getTriggers());
        $this->gzwrite($sqlfh, $this->getFooter());
        $this->gzclose($sqlfh, true);

        //$this->emit('mysqldump:complete');
        return null;
    }

}
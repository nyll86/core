<?php

namespace Kernel\Core\Model;

use Kernel\Core\Exception;
use Kernel\Core\Service;

class Mysql
{

    /**
     * params for connect in Db: host name
     *
     * @var string
     */
    private $host;

    /**
     * params for connect in Db: user name
     *
     * @var string
     */
    private $user;

    /**
     * params for connect in Db: pass
     *
     * @var string
     */
    private $pass;

    /**
     * params for connect in Db: db name
     *
     * @var string
     */
    private $dbName;

    /**
     * params for connect in Db: charset
     *
     * @var string
     */
    private $charset;

    /**
     * mysqli instance
     *
     * @var \mysqli
     */
    private $mysql;

    /**
     * mysqli instance
     *
     * @var Service\Logger
     */
    private $logger;

    /**
     * stmt prepare
     *
     * @var \mysqli_stmt
     */
    private static $stmt;

    /**
     * last prepare
     *
     * @var
     */
    private $lastPrepare;

    /**
     * set sql mode
     *
     * @var boolean
     */
    private static $sql_mode = false;

    private static $instance = [];

    /**
     * factory DB
     *
     * @param string|null $host
     * @param string|null $user
     * @param string|null $pass
     * @param string|null $dbName
     * @param string $charset
     * @return Mysql|mixed
     * @throws Exception
     */
    public static function factory(string $host, string $user, string $pass, string $dbName, string $charset)
    {
        if (isset(self::$instance[$host])) {
            return self::$instance[$host];
        }

        return self::$instance[$host] = new self($host, $user, $pass, $dbName, $charset);
    }


    /**
     * Mysql constructor.
     * @param string|null $host
     * @param string|null $user
     * @param string|null $pass
     * @param string|null $dbName
     * @param string $charset
     * @throws Exception
     */
    private function __construct(string $host, string $user, string $pass, string $dbName, string $charset)
    {
        $this->mysql = new \mysqli($host, $user, $pass, $dbName);
        if ($this->mysql->connect_errno) {
            $this->errorMessage();
            return;
        }
        $this->setCharset($charset);
        $this->setParams($host, $user, $pass, $dbName, $charset);
    }

    private function setParams(string $host, string $user, string $pass, string $dbName, string $charset): void
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbName = $dbName;
        $this->charset = $charset;
        if (Service\Debug::getInstance()->enable()) {
            $this->logger = Service\Logger::factory(self::class);
        }
    }

    private function setCharset(string $charset): void
    {
        $this->getMysql()->set_charset($charset);
    }

    /**
     * close
     */
    public function __destruct()
    {
        if ($this->getMysql() !== null) {
            $this->getMysql()->close();
        }
    }

    /**
     * @param string $db
     * @throws Exception
     */
    public function selectDb(string $db): void
    {
        $this->dbName = $db;
        if (! $this->getMysql()->select_db($db)) {
            $this->errorMessage();
        }
    }

    /**
     * @param $sql
     * @return bool
     * @throws Exception
     */
    public function query($sql): bool
    {
        $this->checkConnection();
        if ($this->logger) {
            $this->logger->startTimer();
        }
        $res = $this->getMysql()->query($sql);
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }
        if (! $res) {
            $this->errorMessage($sql);
            return false;
        }

        return true;
    }

    /**
     * fetch assoc
     *
     * @param $sql
     * @return array
     * @throws Exception
     */
    public function fetchAssoc($sql): array
    {
        $this->checkConnection();
        if (! self::$sql_mode && stripos($sql, 'GROUP BY') !== false) {
            self::$sql_mode = true;
            $this->clearSqlMode('ONLY_FULL_GROUP_BY');
        }
        if ($this->logger) {
            $this->logger->startTimer();
        }
        $result = $this->getMysql()->query($sql);
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }

        if (! $result) {
            $this->errorMessage($sql);
        }

        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        $result->free();
        return $rows;
    }

    /**
     * show result for multi sql query
     *
     * @param $sql
     * @return array
     * @throws Exception
     */
    public function multiFetchAssoc($sql): array
    {
        $this->checkConnection();
        if (! self::$sql_mode && stripos($sql, 'GROUP BY') !== false) {
            self::$sql_mode = true;
            $this->clearSqlMode('ONLY_FULL_GROUP_BY');
        }
        if ($this->logger) {
            $this->logger->startTimer();
        }
        $this->getMysql()->multi_query($sql);
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }

        $rows = [];
        $i = 0;
        do {
            if ($result = $this->getMysql()->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    $rows[$i][] = $row;
                }
                $result->free();
            }
            $i++;
        } while ($this->getMysql()->more_results() && $this->getMysql()->next_result());
        return $rows;
    }

    /**
     * set prepare sql query and return result
     *
     * @param string $sql
     * @param array $params
     * @return array|bool
     * @throws Exception
     */
    public function prepare_result(string $sql, array $params = [])
    {
        if (stripos($sql, 'GROUP BY') !== false) {
            self::$sql_mode = true;
            $this->clearSqlMode('ONLY_FULL_GROUP_BY');
        }
        if ($this->logger) {
            $this->logger->startTimer();
        }
        self::$stmt = $this->getMysql()->prepare($sql);
        if (! self::$stmt) {
            $this->errorMessage($sql);
        }

        return $this->bind_param($params, true, $sql);
    }

    /**
     * set lart prepare
     *
     * @param string $sql
     * @param array $params
     */
    private function setLastPrepare(string $sql, array $params): void
    {
        $return = '';
        foreach (explode('?', $sql) as $i => $part) {
            $return .= $part;
            $return .= isset($params[$i]) ? "'$params[$i]'" : 'null';
        }
        $return = substr($return, 0, -4);
        $this->lastPrepare = $return;
    }

    /**
     * get last prepare
     *
     * @return mixed
     */
    public function getLastPrepare()
    {
        return $this->lastPrepare;
    }

    /**
     * set prepare sql query
     *
     * @param string $sql
     * @param array $params
     * @return array|bool
     * @throws Exception
     */
    public function prepare_query(string $sql, array $params = [])
    {
        self::$stmt = $this->getMysql()->prepare($sql);
        if (! self::$stmt) {
            $this->errorMessage($sql);
        }
        $this->setLastPrepare($sql, $params);
        return $this->bind_param($params, false, $sql);
    }

    /**
     *  bind params for stmt
     *
     * @param array $params
     * @param bool $return
     * @param string $sql
     * @return array|bool
     * @throws Exception
     */
    private function bind_param(array $params, bool $return, string $sql)
    {
        if ($params) {
            $_params = [];

            $type = '';
            foreach ($params as $item) {
                if (\is_float($item)) {
                    $type .= 'd';
                } elseif (\is_int($item)) {
                    $type .= 'i';
                } else {
                    $type .= 's';
                }
            }
            $_params[] = $type;

            foreach ($params as $key => $item) {
                $_params[] = &$params[$key];
            }
            \call_user_func_array([self::$stmt, 'bind_param'], $_params);
        }
        return $this->stmt_fetch_assoc($return, $sql);
    }

    /**
     * get result stmt
     *
     * @param bool $return
     * @param string $sql
     * @return array|bool
     * @throws Exception
     */
    private function stmt_fetch_assoc(bool $return, string $sql)
    {
        $res = self::$stmt->execute();
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }
        if ($res) {
            if ($return === true) {
                $result = self::$stmt->get_result();
                $res = [];
                while ($data = $result->fetch_assoc()) {
                    $res[] = $data;
                }
                self::$stmt->close();
                return $res;
            }
            self::$stmt->close();
            return true;
        }
        $this->errorMessage();
        return false;
    }

    /**
     * check connection
     */
    private function checkConnection(): void
    {
        if (! $this->getMysql()->ping()) {
            $this->getMysql()->close();
            $this->mysql = new \mysqli($this->host, $this->user, $this->pass, $this->dbName);
        }
    }

    /**
     * excape string
     *
     * @param      $data
     * @param bool $trim
     *
     * @return string
     */
    public function escape($data, $trim = true): string
    {
        if ($trim) {
            $data = trim($data);
        }
        return $this->getMysql()->escape_string($data);
    }

    /**
     * get last ID
     *
     * @return int
     */
    public function getLastId(): int
    {
        return $this->getMysql()->insert_id;
    }

    /**
     * @param string $sql
     * @throws Exception
     */
    private function errorMessage($sql = ''): void
    {
        if ($this->getMysql()->error) {
            throw new Exception($this->getMysql()->error . '<br/>SQL: ' . $sql, $this->getMysql()->connect_errno);
        }
    }

    /**
     * clear sql mode
     *
     * @param $mode
     * @throws Exception
     */
    public function clearSqlMode($mode): void
    {
        $sql = "SET sql_mode=(SELECT REPLACE(@@sql_mode,'$mode',''))";
        if ($this->logger) {
            $this->logger->startTimer();
        }
        $this->getMysql()->query($sql);
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }
    }

    /**
     * get current version MySQL
     *
     * @return string
     * @throws Exception
     */
    public function getVersion(): string
    {
        $sql = 'SELECT VERSION()';
        if ($this->logger) {
            $this->logger->startTimer();
        }
        $result = $this->getMysql()->query($sql)->fetch_array();
        if ($this->logger) {
            $this->logger->endTimer();
            $this->logger->addLog($sql);
        }
        $ver = explode('.', $result[0]);
        return (float)$ver[0] . '.' . $ver[1];
    }

    /**
     * get mysqli instance
     *
     * @return \mysqli
     */
    private function getMysql(): \mysqli
    {
        return $this->mysql;
    }

}


<?php

namespace Kernel\Core\Model\Mysql;

use Kernel\Core\Exception;
use Kernel\Core\Model\Mysql;

class MysqlBuilder
{
    /**
     * DB instance
     *
     * @var Mysql
     */
    private Mysql $mysql;

    /**
     * sql string for query
     *
     * @var string
     */
    private string $sql;

    /**
     * for multi query
     *
     * @var array
     */
    private array $multi = [];

    /**
     * prepare params for prepare query
     *
     * @var array
     */
    private array $prepare_params = [];

    /**
     * CONSTANT
     */
    public const GET = 1;
    public const DROP = 2;
    public const UPDATE = 3;
    public const CREATE = 4;
    public const TRUNCATE = 5;
    public const SET = 6;
    public const MULTI = 7;
    public const RAW = 8;

    /**
     * use mode
     *
     * @var string
     */
    private $mode = 0;

    /**
     * use subQuery
     *
     * @var boolean
     */
    private bool $newQuery = true;

    private bool $isMulti = false;

    private Mysql\Operation\Functions $functions;

    private Mysql\Operation\Condition $condition;

    /**
     * MysqlBuilder constructor.
     * @param Mysql $mysql
     */
    public function __construct(Mysql $mysql)
    {
        $this->mysql = $mysql;
        $this->init();
    }

    /**
     * init this class
     */
    private function init(): void
    {
        $this->functions = new Mysql\Operation\Functions($this);
        $this->condition = new Mysql\Operation\Condition($this);
    }


    /**
     * get Last ID
     *
     * @return int last ID insert
     */
    public function lastID(): int
    {
        return $this->mysql->getLastId();
    }

    /**
     * SELECT mode
     * SELECT mode
     *
     * @param null $params
     * @param bool $create
     * @return MysqlBuilder
     * @throws Exception
     */
    public function get($params = null, $create = true): MysqlBuilder
    {
        // for insert ... select
        if ($this->getMode() === 4) {
            $this->setNewQuery(false);
        }
        if ($create) {
            $this->setMode('GET');
        }

        if (is_array($params)) {
            $attr = [];
            foreach ($params as $key => $value) {
                $value = $this->getFunctions()->check($value, true);
                if (is_string($key)) {
                    $attr[] = "$value as `$key`";
                } else {
                    $attr[] = $value;
                }
            }
            $this->addSql(implode(',', $attr));
            return $this;
        }

        // all columns
        if (!$params) {
            $this->addSql('*');
            return $this;
        }
        if (stripos($params, 'inno') !== false) {
            $params = str_replace('inno_', '', $params);
            $this->addSql("COUNT(`id`) as `$params`");
        } else {
            $this->addSql("COUNT(*) as `$params`");
        }
        return $this;
    }

    /**
     * raw method
     *
     * @param  string $sql sql string
     * @param  array $params prepare params
     *
     * @return MysqlBuilder         this class
     */
    public function raw(string $sql, array $params = []): MysqlBuilder
    {
        $this->setMode('RAW');
        $this->createSql($sql);
        $this->addPrepareParam($params, false);
        return $this;
    }

    /**
     * DELETE mode
     *
     * @return MysqlBuilder this class
     */
    public function drop(): MysqlBuilder
    {
        $this->setMode('DROP');
        return $this;
    }

    /**
     * DELETE mode
     *
     * @param      $tableName
     * @param bool $test
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function truncate($tableName, $test = false)
    {
        $this->setMode('TRUNCATE');
        $this->createSql("TRUNCATE $tableName");
        if ($this->getMode() === 7) {
            return $this;
        }
        if (!$test) {
            return $this->query();
        }
        return true;
    }

    /**
     * UPDATE mode
     *
     * @return MysqlBuilder this class
     */
    public function update(): MysqlBuilder
    {
        $this->setMode('UPDATE');
        return $this;
    }

    /**
     * set multi mode
     *
     * @return MysqlBuilder this class
     */
    public function multi(): MysqlBuilder
    {
        $this->setMode('MULTI');
        return $this;
    }

    /**
     * For multi query
     */
    public function add(): MysqlBuilder
    {
        $this->multi[] = $this->getSql();
        $this->clearSql();
        return $this;
    }

    /**
     * INSERT mode
     *
     * @return MysqlBuilder this class
     */
    public function create(): MysqlBuilder
    {
        $this->setMode('CREATE');
        return $this;
    }

    /**
     * set params
     *
     * @param array $data
     * @param bool $test
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function set(array $data, $test = false)
    {
        $this->setMode('SET');
        [$key, $value] = Mysql\Tools\Helper::getKeyValue($data);

        $this->createSql("SET $key $value");
        if ($this->isMulti()) {
            return $this;
        }
        if (!$test) {
            return $this->query();
        }
        return true;
    }

    /**
     * in table
     *
     * @param mixed $tables
     *
     * @return MysqlBuilder
     */
    public function table($tables = ''): MysqlBuilder
    {
        $mode = $this->getMode();
        if ($mode === 1 || $mode === 2 || $mode === 7) {
            $this->addSql('FROM');
        }

        // for tableName only
        if (\is_string($tables)) {
            $tables = Mysql\Tools\Helper::getColumn($tables);
            $this->addSql($tables);
            return $this;
        }

        $data = [];
        if (\is_array($tables)) {
            foreach ($tables as $key => $table) {
                $table = Mysql\Tools\Helper::getColumn($table);
                // set delete
                if ($this->getMode() === 2) {
                    $this->createSql("DELETE $key FROM $table as `$key`");
                } else {
                    $data[] = "$table as `$key`";
                }
            }
            $this->addSql(implode(',', $data));
            return $this;
        }
        return $this;
    }

    /**
     * for subquery
     *
     * @return $this
     * @throws Exception
     */
    public function from(): self
    {
        if ($this->getMode() !== 1) {
            throw new Exception('from method must be type SELECT');
        }
        $this->addSql('FROM');
        return $this;
    }

    /**
     * sart sub query
     *
     * @return $this
     * @throws Exception
     */
    public function startSubQuery(): self
    {
        if ($this->getMode() !== 1) {
            throw new Exception('from method must be type SELECT');
        }
        $this->addSql('(');
        // for don't create a new sql query
        $this->setNewQuery(false);
        return $this;
    }

    /**
     * end sub query
     *
     * @param string $as
     *
     * @return $this
     * @throws Exception
     */
    public function endSubQuery(string $as): self
    {
        if ($this->getMode() !== 1) {
            throw new Exception('from method must be type SELECT');
        }
        $this->addSql(") as `$as`");
        return $this;
    }

    /**
     * JOIN mode
     *
     * @param array $join
     *
     * @return MysqlBuilder
     * @throws Exception
     * @throws Exception
     */
    public function join(array $join): MysqlBuilder
    {
        foreach ($join as $key => $param) {
            $key = Mysql\Tools\Helper::getColumn($key);
            // name table
            $this->addSql("JOIN $key");
            $this->joinBuild($param);
        }
        return $this;
    }

    /**
     * LEFT JOIN mode
     *
     * @param array $join
     *
     * @return MysqlBuilder
     * @throws Exception
     * @throws Exception
     */
    public function leftjoin(array $join): MysqlBuilder
    {
        foreach ($join as $key => $param) {
            $key = Mysql\Tools\Helper::getColumn($key);
            // name table
            $this->addSql("LEFT JOIN $key");

            $this->join($param);
        }
        return $this;
    }

    /**
     * JOIN builder
     *
     * @param array $param
     *
     * @throws Exception
     * @throws Exception
     */
    private function joinBuild(array $param): void
    {
        // JOIN as ...
        $as = '';
        // params join
        foreach ($param as $key => $value) {

            if ($key === 'as') {
                $as = $value;
                $this->addSql("as `$as`");
            }

            // JOIN AS ... ON () ...
            if ($key === 'on') {
                $on = [];
                if (!\is_array($value)) {
                    throw new Exception('$value must be array');
                }
                foreach ($value as $attr1 => $attr2) {
                    if (\is_array($attr2)) {
                        $attr2 = $this->getCondition()->syntax($attr2);
                    }
                    $on[] = "$as.`$attr1` = $attr2";
                }
                // implode params
                $this->addSql('ON (' . implode(' AND ', $on) . ')');
            }
        }
    }

    /**
     * where method
     *
     * @param array $params
     * @return MysqlBuilder
     * @throws Exception
     */
    public function where(array $params): MysqlBuilder
    {
        $syntax = $this->getCondition()->syntax($params);
        $this->addSql('WHERE ' . $syntax);
        return $this;
    }

    /**
     * having method
     *
     * @param array $params
     * @return MysqlBuilder
     * @throws Exception
     */
    public function having(array $params): MysqlBuilder
    {
        $syntax = $this->getCondition()->syntax($params);
        $this->addSql('HAVING ' . $syntax);
        return $this;
    }

    /**
     * GROUP BY
     *
     * @param  string || array $data    data for group by
     *
     * @return MysqlBuilder                    this class
     */
    public function group($data): MysqlBuilder
    {
        if (\is_string($data)) {
            $group = $data;
            $group = Mysql\Tools\Helper::getColumn($group);
        } else {
            $data = array_map(function ($item) {
                return Mysql\Tools\Helper::getColumn($item);
            }, $data);
            $group = implode(',', $data);
        }
        $this->addSql("GROUP BY $group");
        return $this;
    }

    /**
     * ORDER method
     *
     * @param  array $param params for order
     *
     * @return MysqlBuilder        this class
     */
    public function order($param): MysqlBuilder
    {
        $this->addSql('ORDER BY');
        if (\is_array($param) && isset($param[0])) {
            $sql = [];
            foreach ($param as $item) {
                $sql[] = $this->orderBuild($item);
            }
            $data = implode(',', $sql);
            $this->addSql($data);
            return $this;
        }
        $sql = $this->orderBuild($param);
        $this->addSql($sql);
        return $this;
    }

    /**
     * ORDER builder
     *
     * @param mixed $param param build
     *
     * @return mixed
     */
    private function orderBuild($param)
    {
        if (\is_string($param)) {
            $param = Mysql\Tools\Helper::getColumn($param);
            return $param;
        }
        $str = $param['name'];
        $str = Mysql\Tools\Helper::getColumn($str);
        if (isset($param['desc']) && $param['desc'] === true) {
            $str .= ' DESC';
        }
        return $str;
    }

    /**
     * LIMIT method
     *
     * @param  string || number $num num LIMIT
     *
     * @return MysqlBuilder        this class
     */
    public function limit($num): MysqlBuilder
    {
        $this->addSql("LIMIT $num");
        return $this;
    }

    /**
     * UNION SQL
     *
     * @param  boolean $all for UNION ALL
     *
     * @return MysqlBuilder        this class
     */
    public function union($all = false): MysqlBuilder
    {
        $this->addSql('UNION');
        if ($all) {
            $this->addSql('ALL');
        }
        $this->setNewQuery(false);
        return $this;
    }

    /**
     * UPDATE table SET ...
     *
     * @param array $data
     *
     * @return MysqlBuilder
     * @throws Exception
     * @throws Exception
     */
    public function setUpdate(array $data): MysqlBuilder
    {
        $set = [];
        foreach ($data as $key => $value) {
            $value = $this->getValue($value);
            $key = Mysql\Tools\Helper::getColumn($key);
            $set[] = $key . '=' . $value;
        }
        $this->addSql('SET ' . implode(',', $set));
        return $this;
    }

    /**
     * INSERT INTO `table` ( ?? )
     *
     * @param array $columns
     *
     * @return MysqlBuilder
     */
    public function intoColumn(array $columns): MysqlBuilder
    {
        $attr = [];
        foreach ($columns as $param) {
            $attr[] = "`$param`";
        }
        $this->addSql('(' . implode(',', $attr) . ')');
        return $this;
    }

    /**
     * INSERT INTO `table` (..) VALUES ( ?? )
     *
     * @param array $values
     *
     * @return MysqlBuilder
     */
    public function values(array $values): MysqlBuilder
    {
        $addValues = $this->insert($values);
        $this->addSql('VALUES ' . $addValues);
        return $this;
    }

    /**
     * sql query
     *
     * @return bool
     * @throws \Exception
     */
    public function query(): bool
    {
        $tmp = $this->mysql->prepare_query($this->getSql(), $this->getPrepareParams());
        $this->clear();
        return $tmp;
    }

    /**
     * find rows
     *
     * @return array
     * @throws \Exception
     */
    public function find(): array
    {
        $result = $this->mysql->prepare_result($this->getSql(), $this->getPrepareParams());
        $this->clear();
        return $result;
    }

    /**
     * find nulti result
     *
     * @return array
     */
    public function multiFind(): array
    {
        if ($this->getSql()) {
            $this->add();
        }
        $sql = implode(';', $this->multi);
        $result = $this->mysql->multiFetchAssoc($sql);
        $this->clear();
        return $result;
    }

    /**
     * find one row
     *
     * @return array
     * @throws \Exception
     */
    public function findOne(): array
    {
        if (!stripos($this->getSql(), 'LIMIT') === false) {
            $this->addSql('LIMIT 1');
        }

        $result = $this->mysql->prepare_result($this->getSql(), $this->getPrepareParams());
        $this->clear();
        return $result[0] ?? [];
    }

    /**
     * insert params
     *
     * @param  array $params params for insert
     *
     * @return string        sql insert
     */
    private function insert(array $params): string
    {
        $insert = [];
        $sql = [];
        foreach ($params as $key => $item) {
            if (\is_array($item)) {
                $sql[] = $this->insert($item);
            } else {
                $this->addParams($item, $key);
                $insert[] = '?';
            }
        }
        return $sql ? implode(',', $sql) : '(' . implode(',', $insert) . ')';
    }

    /**
     * clear prepare param
     */
    private function clearEmptyParams(): void
    {
        $this->prepare_params = [];
    }

    /**
     * clear all params (for create a new query)
     */
    public function clear(): void
    {
        $this->clearEmptyParams();
        $this->clearSql();
        $this->multi = [];
        $this->mode = 0;
        $this->setNewQuery(true);
        $this->unsetMulti();
    }

    /**
     * check type add params
     *
     * @param      $param
     * @param bool $typeVar
     *
     * @return bool
     */
    private function addParams($param, $typeVar = false): bool
    {
        // get param value without []
        $param = preg_replace(['/\[/', '/\]/'], '', $param);
        if ($typeVar) {
            // add param for prepare query
            switch ($typeVar) {
                case 'string':
                    $param = (string)$param;
                    break;

                case 'bool':
                    $param = (bool)$param;
                    break;

                case 'int':
                    $param = (int)$param;
                    break;

                case 'float':
                    $param = (float)$param;
                    break;

                default:

                    break;
            }
            $this->addPrepareParam($param);
            return true;
        }

        // check type param
        if (\is_numeric($param)) {
            $param = (int)$param;
            $this->addPrepareParam($param);
            return true;
        }

        if (\is_string($param)) {
            $param = (string)$param;
            $this->addPrepareParam($param);
            return true;
        }

        if (\is_bool($param)) {
            $param = (bool)$param;
            $this->addPrepareParam($param);
            return true;
        }

        if (\is_float($param)) {
            $param = (float)$param;
            $this->addPrepareParam($param);
            return true;
        }
        return false;
    }

    /**
     * @param $value
     *
     * @return string
     * @throws Exception
     */
    public function getValue($value): string
    {
        $value = $this->getFunctions()->check($value);
        preg_match_all('/\[.*?\]/', $value, $arr);
        $value = preg_replace('/\[.*?\]/', '?', $value);
        if (!\is_array($arr[0])) {
            throw new Exception('$arr[0] must be array');
        }
        foreach ($arr[0] as $param) {
            if (!$this->addParams($param)) {
                throw new Exception('Error add prepare params');
            }
        }
        return $value;
    }

    /**
     * expace string
     *
     * @param $string
     *
     * @return string
     */
    public function escape($string): string
    {
        return $this->getMysql()->escape($string);
    }

    /**
     * set use mode
     *
     * @param [type] $mode [description]
     */
    private function setMode($mode): void
    {
        switch ($mode) {
            case 'GET':
                $this->mode = self::GET;
                if ($this->isNewQuery()) {
                    $this->createSql('SELECT');
                } else {
                    $this->addSql('SELECT');
                }
                break;
            case 'UPDATE':
                $this->mode = self::UPDATE;
                $this->createSql('UPDATE');
                break;
            case 'CREATE':
                $this->mode = self::CREATE;
                $this->createSql('INSERT INTO');
                break;
            case 'DROP':
                $this->mode = self::DROP;
                $this->createSql('DELETE');
                break;
            case 'SET':
                $this->mode = self::SET;
                break;
            case 'MULTI':
                $this->mode = self::MULTI;
                $this->setMulti();
                break;

            default:
                $this->mode = 0;
                break;
        }

    }

    /**
     * get mode
     *
     * @return int
     */
    private function getMode(): int
    {
        return $this->mode;
    }

    /**
     * create a new sql
     *
     * @param string $data
     */
    private function createSql(string $data): void
    {
        $this->sql = $data . ' ';
    }

    /**
     * add sql
     *
     * @param string $data
     */
    private function addSql(string $data): void
    {
        $this->sql .= $data . ' ';
    }

    /**
     * get sql data
     *
     * @return string get sql data
     */
    public function getSql(): string
    {
        return trim($this->sql);
    }

    /**
     * get multi sql
     *
     * @return string sql build
     */
    public function getMultiSql(): string
    {
        return trim(implode(';', $this->multi));
    }

    /**
     * clear sql
     */
    private function clearSql(): void
    {
        $this->sql = '';
    }

    /**
     * check use sub query
     *
     * @return boolean isUse
     */
    private function isNewQuery(): bool
    {
        return $this->newQuery;
    }

    /**
     * set status a new query
     *
     * @param bool $status status a new query
     */
    private function setNewQuery(bool $status): void
    {
        $this->newQuery = $status;
    }

    /**
     * add prepare params
     *
     * @param      $param
     * @param bool $add
     */
    private function addPrepareParam($param, $add = true): void
    {
        if ($add) {
            $this->prepare_params[] = $param;
        } else {
            $this->prepare_params = $param;
        }
    }

    /**
     * get prepare params
     *
     * @return array params
     */
    protected function getPrepareParams(): array
    {
        return $this->prepare_params;
    }

    private function isMulti(): bool
    {
        return $this->isMulti;
    }

    private function setMulti(): void
    {
        $this->isMulti = true;
    }

    private function unsetMulti(): void
    {
        $this->isMulti = false;
    }

    private function getFunctions(): Mysql\Operation\Functions
    {
        return $this->functions;
    }

    public function getCondition(): Mysql\Operation\Condition
    {
        return $this->condition;
    }

    private function getMysql(): Mysql
    {
        return $this->mysql;
    }

    public function getLogs(): array
    {
        return $this->getMysql()->getLogs();
    }


}
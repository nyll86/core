<?php

namespace Kernel\Core\Model\Mysql\Operation;

use Kernel\Core\Exception;
use Kernel\Core\Model\Mysql\MysqlBuilder;
use Kernel\Core\Model\Mysql\Tools\Helper;

/**
 * Condition MOdel
 *
 * Class Condition
 * @package Models\Model\Operation
 */
class Condition
{
    /**
     * condition types
     */
    public const TYPE_AND = '__and__';
    public const TYPE_OR = '__or__';
    public const TYPE_IN = '__in__';
    public const TYPE_NOT_IN = '__not_in__';
    public const TYPE_LIKE = '__like__';
    public const TYPE_NOT_LIKE = '__not_like__';
    public const TYPE_REGEX = '__regexp__';
    public const TYPE_GT = '__gt__';
    public const TYPE_GTE = '__gte__';
    public const TYPE_LT = '__lt__';
    public const TYPE_LTE = '__lte__';
    public const TYPE_EQ = '__eq__';
    public const TYPE_NOT_EQ = '__not_eq__';
    public const TYPE_IS = '__is__';
    public const TYPE_NOT_IS = '__not_is__';
    public const TYPE_BETWEEN = '__between__';
    public const TYPE_NOT_BETWEEN = '__not_between__';
    public const TYPE_OWN = '__own__';

    private $mysqlBuilder;

    public function __construct(MysqlBuilder $builder)
    {
        $this->setBuilder($builder);
    }

    /**
     * WHERE data
     *
     * @param array $data
     * @param string $type
     * @return string
     * @throws \Kernel\Core\Exception
     */
    public function syntax(array $data, $type = ''): string
    {
        $sql = '';
        $i = 1;

        foreach ($data as $key => $value) {
            $sql .= $this->parseWhere($key, $value);
            if (($type === '__and__' || $type === '__or__') && $i !== \count($data)) {
                $sql .= ' ' . strtoupper(str_replace('_', '', $type)) . ' ';
            }
            $i++;
        }
        return $sql;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     * @throws \Kernel\Core\Exception
     */
    private function parseWhere($key, $value): string
    {
        switch ($key) {
            case self::TYPE_AND:
            case self::TYPE_OR:
                $sql = '(' . $this->syntax($value, $key) . ')';
                break;

            case self::TYPE_IN:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $values = $data[1];
                if (! \is_array($values)) {
                    throw new Exception('$value must be array');
                }
                $data = [];
                foreach ($values as $val) {
                    $data[] = $this->getMysqlBuilder()->getValue($val);
                }
                $sql = $key . ' IN (' . implode(',', $data) . ') ';
                break;

            case self::TYPE_NOT_IN:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $values = $data[1];
                if (! \is_array($values)) {
                    throw new Exception('$value must be array');
                }
                $data = [];
                foreach ($values as $val) {
                    $data[] = $this->getMysqlBuilder()->getValue($val);
                }
                $sql = $key . ' NOT IN (' . implode(',', $data) . ') ';
                break;

            case self::TYPE_LIKE:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key LIKE '$value'";
                break;

            case self::TYPE_NOT_LIKE:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key NOT LIKE '$value'";
                break;

            case self::TYPE_REGEX:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $values = $data[1];
                if (! \is_array($values)) {
                    throw new Exception('$value must be array');
                }
                $data = [];
                foreach ($values as $val) {
                    $data[] = $this->getMysqlBuilder()->getValue($val);
                }
                $sql = "$key REGEXP '" . implode('|', $data) . "' ";
                break;

            case self::TYPE_GT:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key > $value";
                break;

            case self::TYPE_GTE:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key >= $value";
                break;

            case self::TYPE_LT:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key < $value";
                break;

            case self::TYPE_LTE:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key <= $value";
                break;

            case self::TYPE_EQ:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key = $value";
                break;

            case self::TYPE_NOT_EQ:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                $value = $data[1];
                $value = $this->getMysqlBuilder()->getValue($value);

                $sql = "$key != $value";
                break;

            case self::TYPE_IS:
                if (\is_array($value)) {
                    $data = Helper::getKeyValue($value);
                    $key = $this->getKey($data[0]);
                    $value = $data[1];
                    $value = $this->getMysqlBuilder()->getValue($value);
                } else {
                    $key = $this->getKey($value);
                    $value = 'NULL';
                }

                $sql = "$key IS $value";
                break;

            case self::TYPE_NOT_IS:
                if (\is_array($value)) {
                    $data = Helper::getKeyValue($value);
                    $key = $this->getKey($data[0]);
                    $value = $data[1];
                    $value = $this->getMysqlBuilder()->getValue($value);
                } else {
                    $key = $this->getKey($value);
                    $value = 'NULL';
                }

                $sql = "$key IS NOT $value";
                break;

            case self::TYPE_BETWEEN:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                [$from, $to] = $data[1];
                $from = $this->getMysqlBuilder()->getValue($from);
                $to = $this->getMysqlBuilder()->getValue($to);

                $sql = "$key BETWEEN $from AND $to";
                break;

            case self::TYPE_NOT_BETWEEN:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                [$from, $to] = $data[1];
                $from = $this->getMysqlBuilder()->getValue($from);
                $to = $this->getMysqlBuilder()->getValue($to);

                $sql = "$key NOT BETWEEN $from AND $to";
                break;

            case self::TYPE_OWN:
                $data = Helper::getKeyValue($value);
                $key = $this->getKey($data[0]);
                [$from, $to] = $data[1];
                $from = $this->getMysqlBuilder()->getValue($from);
                $to = $this->getMysqlBuilder()->getValue($to);

                $sql = "$key BETWEEN $from AND $to";
                break;


            default:
                $value = $this->getMysqlBuilder()->getValue($value);
                $key = $this->getKey($key);
                $sql = "$key = $value";
                break;
        }
        return $sql;
    }

    private function getKey(string $key): string
    {
        if (strpos($key, '.') === false) {
            return "`$key`";
        }
        $data = explode('.', $key);
        $last = array_pop($data);
        $last = "`$last`";
        $key = implode('.', $data) . '.' . $last;
        return $key;
    }

    /**
     * set builder
     *
     * @param MysqlBuilder $builder
     */
    private function setBuilder(MysqlBuilder $builder): void
    {
        $this->mysqlBuilder = $builder;
    }

    /**
     * get builder
     *
     * @return MysqlBuilder
     */
    private function getMysqlBuilder(): MysqlBuilder
    {
        return $this->mysqlBuilder;
    }

}
<?php

/**
 * Function tools
 */

namespace Kernel\Core\Model\Mysql\Operation;

use Kernel\Core\Exception;
use Kernel\Core\Model\Mysql\MysqlBuilder;
use Kernel\Core\Model\Mysql\Tools\Helper;

class Functions
{

    public const FUNC_DATE_ADD = 'DATE_ADD';
    public const FUNC_DATE_SUB = 'DATE_SUB';
    public const FUNC_CONCAT = 'CONCAT';
    public const FUNC_GROUP_CONCAT = 'GROUP_CONCAT';
    public const FUNC_COUNT = 'COUNT';
    public const FUNC_SUM = 'SUM';
    public const FUNC_IF = 'IF';
    public const FUNC_SUM_IF = 'SUM_IF';
    public const FUNC_COUNT_IF = 'COUNT_IF';
    public const FUNC_STRING = 'STRING';
    public const FUNC_DISTINCT = 'DISTINCT';
    public const FUNC_MIN = 'MIN';
    public const FUNC_MAX = 'MAX';

    /**
     * @var MysqlBuilder
     */
    private MysqlBuilder $mysqlBuilder;

    /**
     * Functions constructor.
     * @param MysqlBuilder $builder
     */
    public function __construct(MysqlBuilder $builder)
    {
        $this->setBuilder($builder);
    }

    /**
     * @param $data
     * @param bool $checkColumn
     * @return string
     * @throws Exception
     */
    public function check($data, $checkColumn = false): string
    {
        if (! \is_array($data)) {
            if ($checkColumn) {
                $data = Helper::getColumn($data);
            }
            return $data;
        }

        $data = Helper::getKeyValue($data);

        $key = strtoupper($data[0]);
        $value = $data[1];

        switch ($key) {
            case self::FUNC_DATE_ADD:
            case self::FUNC_DATE_SUB:
                $value = $this->dateFunction($key, $value);
                break;

            case self::FUNC_CONCAT:
                if (! \is_array($value)) {
                    throw new Exception('value must be array');
                }
                foreach ($value as $key => &$val) {
                    if ($key === 'string' || $key === 's') {
                        $val = "'$val'";
                    }
                    if (strpos($val, ' ') !== false) {
                        $val = "'$val'";
                    }
                }
                unset($val);
                $value = 'CONCAT(' . implode(',', $value) . ')';
                break;

            case self::FUNC_GROUP_CONCAT:
                $value = Helper::getColumn($value);
                $value = "GROUP_CONCAT($value)";
                break;

            case self::FUNC_COUNT:
                $value = Helper::getColumn($value);
                $value = "COUNT($value)";
                break;

            case self::FUNC_SUM:
                $value = Helper::getColumn($value);
                $value = "SUM($value)";
                break;

            case self::FUNC_IF:
                $syntax = $this->getMysqlBuilder()
                    ->getCondition()
                    ->syntax($value[0]);
                $true = Helper::getTypeValue($value[1]);
                $false = Helper::getTypeValue($value[2]);

                $true = Helper::getColumn($true);
                $value = "IF($syntax, $true, $false)";
                break;

            case self::FUNC_SUM_IF:
                $syntax = $this->getMysqlBuilder()
                    ->getCondition()
                    ->syntax($value[0]);
                $true = Helper::getTypeValue($value[1]);

                $true = Helper::getColumn($true);
                $value = "SUM(IF($syntax, $true, 0))";
                break;

            case self::FUNC_COUNT_IF:
                $syntax = $this->getMysqlBuilder()
                    ->getCondition()
                    ->syntax($value[0]);
                $true = Helper::getTypeValue($value[1]);

                $true = Helper::getColumn($true);
                $value = "COUNT(IF($syntax, $true, NULL))";
                break;

            case self::FUNC_STRING:
                $value = "'$value'";
                break;

            case self::FUNC_DISTINCT:
                $value = Helper::getColumn($value);
                $value = "DISTINCT $value";
                break;

            case self::FUNC_MIN:
                $value = Helper::getColumn($value);
                $value = "MIN($value)";
                break;

            case self::FUNC_MAX:
                $value = Helper::getColumn($value);
                $value = "MAX($value)";
                break;

            default:
                throw new Exception('Function is not found');
                break;
        }
        return $value;

    }

    /**
     * get syndatax date function
     *
     * @param string $functionName
     * @param array $value
     *
     * @return string
     */
    private function dateFunction(string $functionName, array $value): string
    {
        $data = Helper::getKeyValue($value);
        $key = strtoupper($data[0]);
        $value = $data[1];
        return "$functionName(NOW(), INTERVAL $value $key)";
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
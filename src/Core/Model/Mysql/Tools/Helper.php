<?php

namespace Kernel\Core\Model\Mysql\Tools;

/**
 * Helper is Models\Model class
 *
 * Class Helper
 * @package Models\Model\Tools
 */
class Helper
{

    /**
     * get key and value
     *
     * @param array $data
     *
     * @return array
     */
    public static function getKeyValue(array $data): array
    {
        $key = array_keys($data);
        $value = $data[$key[0]];
        $key = $key[0];
        return [$key, $value];
    }

    /**
     * get type value
     *
     * @param $value
     *
     * @return string
     */
    public static function getTypeValue($value): string
    {
        if (\is_array($value)) {
            [$key, $value] = self::getKeyValue($value);
            if ($key === 'string' || $key === 's') {
                return "'$value'";
            }
        }
        if (\strpos($value, ' ') !== false) {
            return "'$value'";
        }
        return $value;
    }

    /**
     * get column name
     *
     * @param string $key
     *
     * @return string
     */
    public static function getColumn(string $key): string
    {
        if (\strpos($key, '.') === false) {
            return ctype_alnum($key) ? "`$key`" : $key;
        }
        $data = explode('.', $key);
        $last = array_pop($data);
        $last = ctype_alnum($last) ? "`$last`" : $last;
        $key = implode('.', $data).'.'.$last;
        return $key;
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 08.12.2018
 * Time: 13:04
 */

namespace Kernel\Core\Model;

/**
 * Class Redis
 * @package Kernel\Core\Model
 */
class Redis
{

    private static $instance;

    /**
     * get instance Redis
     *
     * @param string $host
     * @param int $port
     * @param int $db
     * @return \Redis
     */
    public static function getInstance(string $host, int $port, int $db): \Redis
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = new \Redis();
        self::$instance->pconnect($host, $port);
        self::$instance->select($db);

        return self::$instance;
    }

}
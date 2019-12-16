<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 08.12.2018
 * Time: 13:04
 */

namespace Kernel\Core\Model;

use Kernel\Core\Exception;
use Kernel\Core\Service\Debug;
use Kernel\Core\Service\LoggerDB;

/**
 * Class Redis
 * @package Kernel\Core\Model
 */
class Redis
{

    private static self $instance;

    private static \Redis $connection;

    /**
     * @var LoggerDB
     */
    private static LoggerDB $logger;

    /**
     * get instance Redis
     *
     * @param string $host
     * @param int $port
     * @param int $db
     * @return \Redis|self
     * @throws Exception
     */
    public static function getInstance(string $host, int $port, int $db)
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$connection = new \Redis();
        self::$connection->pconnect($host, $port);
        self::$connection->select($db);
        if (Debug::getInstance()->enable()) {
            self::$logger = LoggerDB::factory(self::class);
        }

        return self::$instance = new self();
    }

    /**
     * call redis method
     *
     * @param $method
     * @param array $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($method, array $arguments = [])
    {
        if (! method_exists(self::$connection, $method)) {
            throw new Exception("Redis method [$method] not exists. Called from " . __METHOD__);
        }

        if (self::$logger) {
            self::$logger->startTimer();
        }

        try {
            $result = call_user_func_array([self::$connection, $method], $arguments);
        } catch (\Exception $e) {
            throw new Exception(
                $e->getMessage(), $e->getCode()
            );
        }
        if (self::$logger) {
            self::$logger->addLog($method);
        }

        return $result;
    }

}
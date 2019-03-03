<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 25.08.2018
 * Time: 17:09
 */

namespace Kernel\Core;


use Kernel\Core\Model\Mongo;
use Kernel\Core\Model\Mysql\MysqlBuilder;
use Kernel\Core\Model\Redis;

class Adapter
{
    /**
     * get mongo instance
     *
     * @param null|string $host
     * @param int|null $port
     * @return Mongo
     * @throws Exception
     */
    public static function getMongo(?string $host = null, ?int $port = null): Mongo
    {
        if ($host === null) {
            $host = Environment::instance()->get('mongo_host');
        }
        if ($port === null) {
            $port = Environment::instance()->get('mongo_port');
        }
        return Model\Mongo::factory($host, $port);
    }

    /**
     * get mysql instance
     *
     * @param null|string $host
     * @param null|string $user
     * @param null|string $pass
     * @param null|string $dbName
     * @param string $charset
     * @return MysqlBuilder
     * @throws Exception
     */
    public static function getMysql(?string $host = null, ?string $user = null, ?string $pass = null, ?string $dbName = null, ?string $charset = null): MysqlBuilder
    {
        if ($host === null) {
            $host = Environment::instance()->get('mysql_host');
        }
        if ($user === null) {
            $user = Environment::instance()->get('mysql_user');
        }
        if ($pass === null) {
            $pass = Environment::instance()->get('mysql_pass');
        }
        if ($dbName === null) {
            $dbName = Environment::instance()->get('mysql_dbName');
        }
        if ($charset === null) {
            $charset = Environment::instance()->get('mysql_charset');
        }
        $mysql = Model\Mysql::factory($host, $user, $pass, $dbName, $charset);

        return new MysqlBuilder($mysql);
    }

    /**
     * get redis instance
     *
     * @param string|null $host
     * @param int|null $port
     * @param int|null $db
     * @return Redis|\Redis
     * @throws Exception
     */
    public static function getRedis(?string $host = null, ?int $port = null, ?int $db = null)
    {
        if ($host === null) {
            $host = Environment::instance()->get('redis_host');
        }
        if ($port === null) {
            $port = Environment::instance()->get('redis_port');
        }
        if ($db === null) {
            $db = Environment::instance()->get('redis_db');
        }
        return Model\Redis::getInstance($host, $port, $db);
    }

}
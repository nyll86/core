<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 25.08.2018
 * Time: 17:10
 */

namespace Kernel\Core\Model;

use Kernel\Core\Environment;
use Kernel\Core\Exception;
use Kernel\Core\Model\Mongo\Builder;
use MongoDB\Client;
use MongoDB\Database;

class Mongo
{
    /**
     * mondoDB Client
     *
     * @var Client
     */
    private Client $client;

    /**
     * mongoDB database
     *
     * @var Database
     */
    private Database $database;

    /**
     * mongoDB collections
     *
     * @var array
     */
    private array $builder = [];

    private static array $instance = [];

    /**
     * factory mongo
     *
     * @param string $host
     * @param int $port
     * @return array|Mongo
     */
    public static function factory(string $host, int $port)
    {
        return self::$instance[$host] ??= new self($host, $port);
    }

    /**
     * Mongo constructor.
     * @param null|string $host
     * @param int|null $port
     */
    public function __construct(string $host, int $port)
    {
        $this->client = new Client('mongodb://' . $host . ':' . $port);
    }

    /**
     * Get all names databases
     *
     * @return array
     */
    public function getDBNames(): array
    {
        $res = [];
        foreach ($this->getClient()->listDatabases() as $databaseInfo) {
            $res[] = $databaseInfo->getName();
        }
        return $res;
    }

    /**
     * get Database mongo
     *
     * @param null|string $name
     * @return Database
     * @throws Exception
     */
    public function getDatabase(?string $name = null): Database
    {
        if ($name === null) {
            $name = Environment::instance()->get('MONGO_DATABASE');
        }
        if ($this->database === null) {
            $this->database = $this->getClient()->selectDatabase($name);
        }

        return $this->database;
    }

    /**
     * get collection mongo
     *
     * @param null|string $name
     * @return Builder
     * @throws Exception
     */
    public function getBuilder(?string $name = null): Builder
    {
        if ($name === null) {
            $name = Environment::instance()->get('MONGO_COLLECTION');
        }

        if (isset($this->builder[$name])) {
            return $this->builder[$name];
        }

        $collection = $this->getDatabase()->selectCollection($name);

        return $this->builder[$name] = new Builder($collection);
    }

    /**
     * get client instance
     *
     * @return Client
     */
    private function getClient(): Client
    {
        return $this->client;
    }

}
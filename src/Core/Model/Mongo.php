<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 25.08.2018
 * Time: 17:10
 */

namespace Kernel\Core\Model;


use Kernel\Core\Environment;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;

class Mongo
{
    /**
     * mondoDB Client
     *
     * @var Client
     */
    private $client;

    /**
     * mongoDB database
     *
     * @var Database
     */
    private $database;

    /**
     * mongoDB collections
     *
     * @var array
     */
    private $collections = [];

    private static $instance = [];

    /**
     * factory mongo
     *
     * @param string $host
     * @param int $port
     * @return array|Mongo
     */
    public static function factory(string $host, int $port)
    {
        if (! isset(self::$instance[$host])) {
            self::$instance = new self($host, $port);
        }
        return self::$instance;
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
     * @throws \Kernel\Core\Exception
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
     * @return Collection
     * @throws \Kernel\Core\Exception
     */
    public function getCollection(?string $name = null): Collection
    {
        if ($name === null) {
            $name = Environment::instance()->get('MONGO_COLLECTION');
        }

        if (isset($this->collections[$name])) {
            return $this->collections[$name];
        }

        return $this->collections[$name] = $this->getDatabase()->selectCollection($name);

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
<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 27.02.2019
 * Time: 23:45
 */

namespace Kernel\Core\Service;

/**
 * Class Cache
 * @package App\Service
 */
class Cache
{

    /**
     * instance
     *
     * @var self
     */
    private static Cache $instance;

    /**
     * available service
     *
     * @var bool
     */
    private bool $available;

    /**
     * get instance
     *
     * @return Cache
     */
    public static function getInstance(): self
    {
        if (! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Cache constructor.
     */
    private function __construct()
    {
        $this->checkAvailable();
    }

    /**
     * set cache
     *
     * @param string $key
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        if ($this->isAvailable() === false) {
            return false;
        }

        // cannot set empty values
        if ($value === false || $value === null) {
            return false;
        }
        return apcu_store($key, $value, $ttl);
    }

    /**
     * get cache
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        if ($this->isAvailable() === false) {
            return $default;
        }
        $value = \apcu_fetch($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * unset cache
     *
     * @param string $key
     * @return bool
     */
    public function unset(string $key): bool
    {
        if ($this->isAvailable() === false) {
            return false;
        }
        return apcu_delete($key);
    }

    /**
     * check key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        if ($this->isAvailable() === false) {
            return false;
        }
        return \apcu_exists($key);
    }

    /**
     * check available service
     */
    private function checkAvailable(): void
    {
        $this->available = \function_exists('\apcu_store');
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function clear(): bool
    {
        if ($this->isAvailable() === false) {
            return false;
        }
        // всегда true
        return \apcu_clear_cache();
    }

    /**
     * доступен ли сервис
     *
     * @return bool
     */
    private function isAvailable(): bool
    {
        return $this->available === true;
    }
}
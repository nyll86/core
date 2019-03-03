<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 03.03.2019
 * Time: 18:04
 */

namespace Kernel\Core\Service;

use Kernel\Core\Exception;

class Logger
{
    private const LOG_TEXT = 0;
    private const LOG_TIME = 1;

    /**
     * instances
     *
     * @var array
     */
    private static $instance = [];

    /**
     * factory
     *
     * @param string $name
     * @return Logger
     */
    public static function factory(string $name): self
    {
        if (isset(self::$instance[$name])) {
            return self::$instance[$name];
        }

        return self::$instance[$name] = new self($name);
    }

    /**
     * log name
     *
     * @var string
     */
    private $name;

    /**
     * timer
     *
     * @var int
     */
    private $timer;

    /**
     * log
     *
     * @var array
     */
    private $log = [];

    /**
     * get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * start timer
     *
     * @throws Exception
     */
    public function startTimer(): void
    {
        if ($this->timer) {
            throw new Exception('timer must be null');
        }
        $this->timer = microtime(true);
    }

    /**
     * end timer
     *
     * @throws Exception
     */
    public function endTimer(): void
    {
        if (! $this->timer) {
            throw new Exception('timer cannot be null');
        }

        $this->timer = microtime(true) - $this->timer;
    }

    /**
     * get log
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->log;
    }

    /**
     * get timer
     *
     * @return int|float|null
     */
    private function getTimer()
    {
        return $this->timer;
    }


    /**
     * clear log
     */
    private function clearTimer(): void
    {
        $this->timer = null;
    }

    /**
     * @param string $query
     */
    public function addLog(string $query): void
    {
        $log = [
            self::LOG_TEXT => $query,
        ];
        if ($timer = $this->getTimer()) {
            $log[self::LOG_TIME] = $timer;
        }

        $this->log[] = $log;
        $this->clearTimer();
    }

    /**
     * Logger constructor.
     * @param string $name
     */
    private function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * set name
     *
     * @param string $name
     */
    private function setName(string $name): void
    {
        $this->name = $name;
    }

}
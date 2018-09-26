<?php

namespace Kernel\Core;

/**
 * Trait fo classes
 */
trait TraitSingleton
{
    /**
     * instance class
     *
     * @var object
     */
    private static $instance;

    /**
     * get instance
     *
     * @return self
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * block new class
     */
    private function __construct()
    {
    }

    /**
     * block wakeup class
     */
    public function __wakeup()
    {
    }

    /**
     * call undefined method
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        throw new Exception('Call method '.$name.', arguments: '.implode(',', $arguments).' is not found');
    }

    /**
     * call undefined static method
     *
     * @param $name
     * @param $arguments
     *
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        throw new Exception('Call static method '.$name.', arguments: '.implode(',', $arguments).' is not found');
    }

    /**
     * get undefined property
     *
     * @param string $name
     *
     * @throws \Exception
     */
    public function __get(string $name)
    {
        throw new Exception('Property '.$name.' cannot get');
    }

    /**
     * set undefuned property
     *
     * @param string $name
     * @param        $value
     *
     * @throws \Exception
     */
    public function __set(string $name, $value)
    {
        throw new Exception('Property '.$name.' cannot set '.$value);
    }

    /**
     * isset param
     *
     * @param string $name
     *
     * @throws \Exception
     */
    public function __isset(string $name)
    {
        throw new Exception('Property '.$name.' cannot isset');
    }
}
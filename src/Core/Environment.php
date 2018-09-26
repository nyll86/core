<?php

namespace Kernel\Core;

/**
 * .env handler
 *
 * Class Dorenv
 */
class Environment
{

    use TraitSingleton;

    /**
     * if true then empty value continue
     */
    public const EMPTY_ENV = false;

    private static $envPath = ROOT . '.env';

    /**
     * load env
     *
     * @param null|string $path
     * @throws Exception
     */
    public static function load(?string $path = null): void
    {
        if ($path === null) {
            $path = self::$envPath;
        }
        if (!self::fileExists($path)) {
            throw new Exception('file .env is not found');
        }
        $data = file_get_contents($path);
        $env = explode("\n", $data);

        self::search($env, function ($name, $val) {
            self::set($name, $val);
        });

        unset($env, $data, $filename);
    }

    /**
     * get env param
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $name)
    {
        $name = strtoupper($name);
        if (!$this->isset($name)) {
            throw new Exception("$name is not set in .env");
        }
        return getenv($name);
    }

    /**
     * search params environment
     *
     * @param          $envs
     * @param callable $callback
     *
     * @throws Exception
     */
    private static function search($envs, callable $callback): void
    {
        if (!\is_array($envs)) {
            throw new Exception('param $envs is not array');
        }
        foreach ($envs as $env) {
            $env = trim($env);
            if (!$env) {
                continue;
            }
            $env_data = explode('=', trim($env));
            $name = trim($env_data[0]);
            if (!isset($env_data[1])) {
                throw new Exception('.env file invalid');
            }
            $val = trim($env_data[1]);
            if (ctype_digit($val)) {
                $val = (int)$val;
            }
            if ($val === 'true') {
                $val = 1;
            }
            if ($val === 'false') {
                $val = 0;
            }
            $callback($name, $val);
            unset($name, $val);
        }
    }

    /**
     * set env params
     *
     * @param string $name
     * @param string $val
     *
     * @throws Exception
     */
    private static function set(string $name, string $val): void
    {
        if (!$val && !self::EMPTY_ENV && $val !== '0') {
            throw new Exception("param $name is empty");
        }
        putenv("$name=$val");
    }

    /**
     * isset env param
     *
     * @param string $name
     *
     * @return bool
     */
    public function isset(string $name): bool
    {
        return getenv($name) !== false;
    }

    private static function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }
}
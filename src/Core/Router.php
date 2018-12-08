<?php

namespace Kernel\Core;

/**
 * Router url
 *
 * Class Router
 * @package PlayerApp
 */
class Router
{

    /**
     * request params
     *
     * @var array
     */
    private static $params = [];

    /**
     * pattern callback
     *
     * @var string
     */
    private static $acceptPattern = 'a-zA-Z0-9';

    /**
     * routes path
     *
     * @var string
     */
    protected static $routesPath = ROOT . 'routes';

    /**
     * type methods
     */
    public const METHOD_GET = 'get';
    public const METHOD_POST = 'post';
    public const METHOD_PUT = 'put';
    public const METHOD_DELETE = 'delete';

    /**
     * register route file
     *
     * @param null|string $path
     */
    public static function run(?string $path = null): void
    {
        if ($path === null) {
            $path = self::$routesPath;
        }
        $path .= SEPARATOR . '*';

        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                self::run($file);
            } elseif (preg_match('/.+\.php$/', $file)) {
                require $file;
            }
        }
    }

    /**
     * get method
     *
     * @param string $uri
     * @param callable $callback
     */
    public static function get(string $uri, callable $callback): void
    {
        if (self::hasMethod(self::METHOD_GET)) {
            $uri_params = self::parseUri($_SERVER['REQUEST_URI']);
            $pattern = '/\/{*[' . self::getAcceptPattern() . ']+}*/';

            preg_match_all($pattern, $uri, $match);
            if (preg_match('/\/{[' . self::getAcceptPattern() . ']+}/', $uri) && \count($match[0]) === \count($uri_params)) {
                self::clearInput();
                $callback_params = self::validationAndGetParam($match[0], $uri_params);
                if ($callback_params === false) {
                    return;
                }
                \call_user_func_array($callback, $callback_params);
                return;
            }
            if ($uri === $_SERVER['REQUEST_URI']) {
                $callback();
                return;
            }
        }
    }

    /**
     * post method
     *
     * @param string $uri
     * @param callable $callback
     */
    public static function post(string $uri, callable $callback): void
    {
        if (self::hasMethod(self::METHOD_POST)) {
            $uri_params = self::parseUri($_SERVER['REQUEST_URI']);
            $pattern = '/\/{*[' . self::getAcceptPattern() . ']+}*/';

            preg_match_all($pattern, $uri, $match);
            if (preg_match('/{[' . self::getAcceptPattern() . ']+}/', $uri) && \count($match[0]) === \count($uri_params)) {
                self::clearInput();
                foreach ($_POST as $key => $val) {
                    self::setParam($key, $val);
                }
                $callback_params = self::validationAndGetParam($match[0], $uri_params);
                if ($callback_params === false) {
                    return;
                }
                array_unshift($callback_params, self::raw());
                \call_user_func_array($callback, $callback_params);
                return;
            }
            if ($uri === $_SERVER['REQUEST_URI']) {
                self::clearInput();
                foreach ($_POST as $key => $val) {
                    self::setParam($key, $val);
                }
                $callback(self::raw());
                return;
            }
        }
    }

    /**
     * any method
     *
     * @param string $uri
     * @param callable $callback
     *
     */
    public static function any(string $uri, callable $callback): void
    {
        if (self::hasMethod(self::METHOD_POST)) {
            self::post($uri, $callback);
        }
        if (self::hasMethod(self::METHOD_GET)) {
            self::get($uri, $callback);
        }
    }

    /**
     * get data input
     *
     * @param string $name
     *
     * @return array|mixed
     */
    public static function raw(string $name = '')
    {
        if ($name === '') {
            return self::$params;
        }
        return self::$params[$name];
    }

    /**
     * clear input data
     */
    private static function clearInput(): void
    {
        self::$params = [];
    }

    /**
     * isset input
     *
     * @param string $name
     *
     * @return bool
     */
    public function isset(string $name): bool
    {
        return isset(self::$params[$name]);
    }

    /**
     * validation route
     *
     * @param array $match
     * @param array $uri_params
     *
     * @return array|bool
     */
    private static function validationAndGetParam(array $match, array $uri_params)
    {
        $callback_params = [];
        foreach ($match as $key => $item) {
            if (preg_match('/{[' . self::getAcceptPattern() . ']+}/', $item)) {
                $param = preg_replace(['/\/{/', '/}/'], '', $item);
                $callback_params[] = $uri_params[$key];
                self::setParam($param, $uri_params[$key]);
            } elseif (preg_replace('/\//', '', $item) !== $uri_params[$key]) {
                return false;
            }
        }
        return $callback_params;
    }

    /**
     * set a new param
     *
     * @param string $name
     * @param        $val
     */
    private static function setParam(string $name, $val): void
    {
        self::$params[$name] = $val;
    }

    /**
     * has request method
     *
     * @param string $method
     *
     * @return bool
     */
    private static function hasMethod(string $method): bool
    {
        return $_SERVER['REQUEST_METHOD'] ?? null === strtoupper($method);
    }

    /**
     * get accept pattern
     *
     * @return string
     */
    private static function getAcceptPattern(): string
    {
        return self::$acceptPattern;
    }


    private static function parseUri($uri): array
    {
        $data = explode('/', $uri);
        $data = array_filter($data, function ($param) {
            return !empty($param);
        });
        return array_values($data);
    }

}
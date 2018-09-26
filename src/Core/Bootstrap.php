<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 25.08.2018
 * Time: 13:04
 */

namespace Kernel\Core;


class Bootstrap
{
    /**
     * register autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * autoload class
     *
     * @param $classname
     */
    public static function autoload($classname): void
    {
        $dir = ROOT;
        $filename = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $classname) . '.php';
        if (file_exists($filename)) {
            require $filename;
        }
    }
}
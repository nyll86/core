<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 21.08.2018
 * Time: 23:39
 */

namespace Kernel\Core;


class FooClass
{
    public static function getPhpInfo()
    {
        phpinfo();
    }

    /**
     *
     *
     * @param $a
     * @return mixed
     */
    public static function bar(int $a)
    {
        return $a;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: NYLL
 * Date: 03.03.2019
 * Time: 18:04
 */

namespace Kernel\Core\Service;

use Kernel\Core\Environment;
use Kernel\Core\Exception;

/**
 * Class Debug
 * @package Kernel\Core\Service
 */
class Debug
{
    /**
     * @var
     */
    private static $instance;

    /**
     * @return Debug
     * @throws Exception
     */
    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }

        return self::$instance = new self();
    }

    /**
     * @var debug mode
     */
    private $debug;

    /**
     * Debug constructor.
     * @throws Exception
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * is enable
     *
     * @return bool
     */
    public function enable(): bool
    {
        return $this->debug === '1';
    }

    /**
     * @throws Exception
     */
    private function init(): void
    {
        $this->debug = Environment::instance()->get('DEBUG');
        if (! $this->debug) {
            $this->debug = '0';
        }
    }

}
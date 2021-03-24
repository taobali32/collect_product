<?php

namespace Gather;

use Gather\Kernel\Support\Str;

/**
 * Class Factory
 *
 * @auther: jtar <3196672779@qq.com>
 * @package Gather
 *
 * @method static \Gather\Adv\Application               adv(array $config)
 * @method static \Gather\Notice\Application            notice(array $config)
 * @method static \Gather\Collect\Application           collect(array $config)
 */
class Factory
{
    /**
     * @param $name
     * @param array $config
     *
     * @return mixed
     */
    public static function make($name, array $config)
    {
        $namespace = Str::studly($name);
        $application = "\\Gather\\{$namespace}\\Application";

        return new $application($config);
    }

    /**
     * Dynamically pass methods to the application.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
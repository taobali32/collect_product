<?php

namespace Gather\Adv;

use Gather\Kernel\ServiceContainer;

/**
 * Class Application
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Adv
 *
 * @property \Gather\Adv\Pangle\Client                        $pangle
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        \Gather\Adv\Pangle\ServiceProvider::class,
    ];

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this['base'],$name],$arguments);
    }
}
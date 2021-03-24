<?php

namespace Gather\Notice;

use Gather\Kernel\ServiceContainer;

/**
 * Class Application
 *
 * @property \Gather\Notice\Email\Client                        $email
 * @package Gather\Notice
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        \Gather\Notice\Email\ServiceProvider::class,
    ];

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this['base'],$name],$arguments);
    }
}
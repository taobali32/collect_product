<?php

namespace Gather\Kernel\Providers;


use Gather\Kernel\Config;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ConfigServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel\Providers
 */
class ConfigServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $pimple
     * @return void
     */
    public function register(Container $pimple)
    {
        !isset($pimple['config']) && $pimple['config'] = function ($app) {
            return new Config($app->getConfig());
        };
    }
}
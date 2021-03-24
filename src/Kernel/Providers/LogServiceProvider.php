<?php

namespace Gather\Kernel\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Gather\Kernel\Log\LogManager;

/**
 * Class LogServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel\Providers
 */
class LogServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Container $pimple
     * @return void
     */
    public function register(Container $pimple)
    {
        !isset($pimple['log']) && $pimple['log'] = function ($app) {
            $config = $app['config']->get('log');

            if (!empty($config)) {
                $app->rebind('config', $app['config']->merge($config));
            }

            return new LogManager($app);
        };

        !isset($pimple['logger']) && $pimple['logger'] = $pimple['log'];
    }
}

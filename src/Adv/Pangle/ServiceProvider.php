<?php

namespace Gather\Adv\Pangle;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     * @return void
     */
    public function register(Container $app)
    {
        $app['pangle'] = function ($app) {
            return new Client($app);
        };
    }
}
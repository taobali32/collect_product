<?php

namespace Gather\Notice\Email;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/2 17:31
 * @package Gather\Notice\Email
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     * @return void
     */
    public function register(Container $app)
    {
        $app['email'] = function ($app) {
            return new Client($app);
        };
    }

}
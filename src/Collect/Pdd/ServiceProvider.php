<?php

namespace Gather\Collect\Pdd;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Pdd
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     *
     * @return void
     */
    public function register(Container $app)
    {
        
        $app['pdd_cate'] = function ($app) {
            return new Cate($app);
        };
    }
}
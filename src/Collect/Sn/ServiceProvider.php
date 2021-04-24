<?php

namespace Gather\Collect\Sn;

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
        $app['sn_cate'] = function ($app) {
            return new Cate($app);
        };

//        $app['pdd_product'] = function ($app) {
//            return new Product($app);
//        };
//
//        $app['pdd_auth'] = function ($app) {
//            return new Auth($app);
//        };
//
//        $app['pdd_order'] = function ($app) {
//            return new Order($app);
//        };
    }
}
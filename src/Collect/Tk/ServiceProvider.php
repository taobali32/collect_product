<?php

namespace Gather\Collect\Tk;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Tk
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
        $app['tk_hot_keyword'] = function ($app) {
            return new HotKeyword($app);
        };

        $app['tk_product'] = function ($app) {
            return new Product($app);
        };

        $app['tk_cate'] = function ($app) {
            return new Cate($app);
        };

        $app['tk_auth'] = function ($app) {
            return new Auth($app);
        };

        $app['tk_order'] = function ($app) {
            return new Order($app);
        };
    }
}
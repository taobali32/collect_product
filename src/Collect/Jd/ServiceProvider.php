<?php


namespace Gather\Collect\Jd;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/10 12:32
 * @package Gather\Collect\Jd
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
        $app['jd_cate'] = function ($app) {
            return new Cate($app);
        };

        $app['jd_hot_keyword'] = function ($app) {
            return new HotKeyword($app);
        };

        $app['jd_product'] = function ($app) {
            return new Product($app);
        };


        $app['jd_auth'] = function ($app) {
            return new Auth($app);
        };
    }
}
<?php

namespace Gather\Kernel\Providers;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class HttpClientServiceProvider
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel\Providers
 */
class HttpClientServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     * @return void
     */
    public function register(Container $pimple)
    {
        !isset($pimple['http_client']) && $pimple['http_client'] = function ($app) {
            return new Client($app['config']->get('http', []));
        };
    }
}

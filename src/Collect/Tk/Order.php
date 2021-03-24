<?php

namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;

/**
 * Class Order
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Tk
 */
class Order extends BaseClient
{
    /**
     * @var string
     */
    protected $cache_name = 'tk_order';

    public function get()
    {
        $config = $this->app['config']['tk'];

        $uri = "http://api.web.21ds.cn/taoke/tbkOrderDetailsGet";

        //  缓存 position_index

    }
}
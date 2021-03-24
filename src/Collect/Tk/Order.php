<?php

namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;

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

    /**
     * 订单同步，建议每2分钟执行一次
     */
    public function syncOrder($page = 1,$position_index = '')
    {
        $config = $this->app['config']['tk']['miao_you_quan'];

        $api_param = [
            'apkey' => $config['apkey'],
            'query_type' => 1,    // 1：创建时间查询，2:付款时间查询，3:结算时间查询
            'page_size' => 100,
            'page_no' => $page,
            'order_scene' => 2,        // 渠道订单
            'tbname' => $config['tbname'],
            'start_time' => date('Y-m-d H:i:s', strtotime("-30 minute")),
            'end_time' => date('Y-m-d H:i:s'),
        ];

        if ($position_index) {
            $api_param['position_index'] = $position_index;
        }

        $uri = "http://api.web.21ds.cn/taoke/tbkOrderDetailsGet";

        $response =  $this->httpGet($uri,$api_param);

        if ($response['code'] == 200){
            return $response['data'];
        }

        throw new Exception($response['msg'],$response['code']);
    }
}
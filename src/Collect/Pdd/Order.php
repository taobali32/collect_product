<?php



namespace Gather\Collect\Pdd;

use Carbon\Carbon;
use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;

/**
 * 拼多多订单
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Jd
 */
class Order extends BaseClient
{
    use InteractsWithCache;

    protected $uri = "http://gw-api.pinduoduo.com/api/router";

    /**
     * 同步订单
     * @see http://www.jingtuitui.com/api_item?id=28
     * @param array $param
     * @param string $mark
     * @return mixed
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function syncOrder($param = [],$mark = '',$clear = false)
    {
        date_default_timezone_set("PRC");

        $config = $this->app['config']['pdd'];

        $cache_pageNo = 'pdd_' . __FUNCTION__ . $mark;

        if ($clear){
            $this->getCache()->delete($cache_pageNo);
        }

        $page  = $this->getCache()->has($cache_pageNo) ? $this->getCache()->get($cache_pageNo): 1;

        $defaultConfig = [
            'type'              =>  'pdd.ddk.order.list.increment.get',
            'client_id'         =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp'         =>  (string)time(),
            'data_type'         =>  'JSON',

            'end_update_time'   =>  time(),
            'page'              =>  $page,
            'page_size'         =>  100,
            'query_order_type'  =>  1,
            'start_update_time' =>  time() - 7200
        ];

        $margeConfig = array_merge($defaultConfig,$param);

        $margeConfig['sign']    = $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response =  $this->httpGet( $this->uri, $margeConfig );

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        $this->getCache()->set( $cache_pageNo, ++$page ,10);

        return ($this->app['config']['original_data'] == true) ? $response : $response['order_list_get_response']['order_list'];
    }

}
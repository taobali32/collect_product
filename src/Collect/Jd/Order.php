<?php



namespace Gather\Collect\Jd;

use Carbon\Carbon;
use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;

/**
 * 京东订单
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Jd
 */
class Order extends BaseClient
{
    use InteractsWithCache;

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

        $config = $this->app['config']['jd'];

        $uri = "http://japi.jingtuitui.com/api/get_order_row";

        $cache_pageNo = 'jd_' . __FUNCTION__ . $mark;

        if ($clear){
            $this->getCache()->delete($cache_pageNo);
        }

        $page  = $this->getCache()->has($cache_pageNo) ? $this->getCache()->get($cache_pageNo): 1;

        $default = [
            'appid'     => 	$config['jing_tui_tui']['appid'],
            'appkey'    => 	$config['jing_tui_tui']['appkey'],
            'v'         =>  'v2',
            'unionid'   =>  $config['lian_meng']['unionid'],
            'key'       =>  $config['lian_meng']['key'],
            'startTime' =>  date('Y-m-d H:i:s',time() - 1800),
            'endTime'   =>  date('Y-m-d H:i:s'),
            'pageIndex' =>  $page,
            'pageSize'  =>  50,
            'type'      =>  1,
            'fields'    =>  'goodsInfo'
        ];

        $default = array_merge($default,$param);

        $response =  $this->httpGet( $uri, $default );

        if ($response['return'] != 0) throw new Exception($response['result'], $response['return']);

        return ($this->app['config']['original_data'] == true) ? $response : $response['result']['data'];
    }

}
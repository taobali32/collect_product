<?php

namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;

/**
 * Class Order
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Tk
 */
class Order extends BaseClient
{
    use InteractsWithCache;

    /**
     * @var string
     */
    protected $cache_name = 'tk_order';



    public function get()
    {
        $config = $this->app['config']['tk'];

        $min_id = $this->getCache()->has($this->cacheMinIdName) ? $this->getCache()->get($this->cacheMinIdName): 1;

        $uri = "http://v2.api.haodanku.com/itemlist/apikey/{$config['hao_dan_ku']['api_key']}/nav/3/cid/0/back/{$config['product']['back']}/min_id/{$min_id}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $this->getCache()->set($this->cacheMinIdName,$response['min_id'],10);

        return $this->returnData($response['data']);
    }
    
    /**
     * 同步订单
     * @see https://www.ecapi.cn/index/index/openapi/id/83.shtml?ptype=1
     * @param array $param
     * @param string $mark
     * @return mixed
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function syncOrder($param = [],$mark = '',$clear = false)
    {
        $config = $this->app['config']['tk']['miao_you_quan'];

        $default = [
            'start_time' => date('Y-m-d H:i:s', strtotime("-30 minute")),
            'end_time'   => date('Y-m-d H:i:s'),
//            'position_index'    =>  1,
            'page_no'    => 1,
            'page_size'  => 20,
            'tbname' => $config['tbname'],
            'apkey' => $config['apkey'],
            'order_scene'   =>  2, // 渠道订单
            'query_type' => 1,    // 1：创建时间查询，2:付款时间查询，3:结算时间查询
        ];

        $default = array_merge($default,$param);

        $position_index_cache_name = $mark . '_' . 'position_index';
        $page_no_cache_name = $mark . '_' . 'page_no';

        if ($clear){
            $this->getCache()->delete($position_index_cache_name);
            $this->getCache()->delete($page_no_cache_name);
        }

        $position_index  = $this->getCache()->has($position_index_cache_name) ? $this->getCache()->get($position_index_cache_name): '';

        if ($position_index) {
            $default['position_index'] = $position_index;
        }

        if (isset($param['position_index'])){
            $default['position_index'] = $param['position_index'];
        }

        $default['page_no'] = $this->getCache()->has($position_index_cache_name) ? $this->getCache()->get($position_index_cache_name): 1;

        $uri = "http://api.web.21ds.cn/taoke/tbkOrderDetailsGet";

        $response =  $this->httpGet( $uri, $default );

        if (isset($response['data']['position_index'])){
            $this->getCache()->set($position_index_cache_name,$response['data']['position_index'],10);
            $this->getCache()->set($page_no_cache_name,$response['data']['page_no'],10);
        }

        if ($response['code'] == 200){
            return ($this->app['config']['original_data'] == true) ? $response : $response['data'];
        }
        
        throw new Exception($response['msg'], $response['code']);
    }

}
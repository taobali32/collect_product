<?php


namespace Gather\Collect\Tk;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;

/**
 * Class Cate
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/6 14:25
 * @package Gather\Collect\Tk
 */
class Cate extends BaseClient
{
    /**
     * 获取淘客分类
     * @see https://www.haodanku.com/Openapi/api_detail?id=18
     * @return array
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function get()
    {
        $config = $this->app['config']['tk']['hao_dan_ku']['api_key'];

        $uri = "http://v2.api.haodanku.com/super_classify/apikey/{$config}";

        $response = $this->httpGet($uri);

        if ($response['code'] == 0){
            throw new Exception($response['msg'],$response['code']);
        }

        $arr = [];
        
        if ($this->app['config']['original_data']){
            return $response['general_classify'];
        }

        foreach ($response['general_classify'] as $item => $value){
            $arr[$value['cid']] = $value['main_name'];
        }

        return $arr;
    }
}
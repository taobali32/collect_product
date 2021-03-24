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
     * get tk cate
     * @return void
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

        foreach ($response['general_classify'] as $item => $value){
            $arr[$value['cid']] = $value['main_name'];
        }

        return $arr;
    }
}
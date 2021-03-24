<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;

/**
 * Class Cate
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/10 12:27
 * @package Gather\Collect\Jd
 */
class Cate extends BaseClient
{
    /**
     * get jd cate
     * @return void
     */
    public function get()
    {
        $config = $this->app['config']['jd']['jing_tui_tui'];

        $uri = "http://japi.jingtuitui.com/api/get_super_category?appid={$config['appid']}&appkey={$config['appkey']}";

        $response = $this->httpPost($uri);

        if ($response['return'] != 0){
            throw new Exception($response['result'],$response['return']);
        }

        $arr = [];

        foreach ($response['result']['data'] as $item => $value){
            $arr[$value['cid']] = $value['cname'];
        }

        return $arr;
    }
}
<?php

namespace Gather\Collect\Pdd;

use Gather\Kernel\BaseClient;

class Cate extends BaseClient
{
    /**
     * 获取拼多多分类
     * @see https://open.21ds.cn/index/index/openapi/id/60.shtml?ptype=3
     * @return array
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function get($parent_opt_id = 0)
    {
        $config = $this->app['config']['pdd']['miao_you_quan'];
        
        $param = [
            'apkey'   =>  $config['apkey'],
            'parent_opt_id' =>  $parent_opt_id
        ];

        $uri = "http://api.web.21ds.cn/pinduoduo/getPddOpts";
        
        $response = $this->httpGet($uri,$param);

        if ($response['code'] != 200){
            throw new Exception($response['msg'],$response['code']);
        }

        if ($this->app['config']['original_data']){
            return $response;
        }
        
        $arr = [];
        
        foreach ($response['data'] as $item => $value) {
            if ( array_search($value['opt_name'],$arr) == false){
                $arr[$value['opt_id']] = $value['opt_name'];
            }
        }

        return $arr;
    }
}
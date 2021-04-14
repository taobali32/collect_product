<?php


namespace Gather\Collect\Pdd;


use Gather\Kernel\BaseClient;

class Auth extends BaseClient
{
    /**
     * 拼多多授权
     * @see https://open.21ds.cn/index/index/openapi/id/16.shtml?ptype=3
     * @param array $param
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     */
    public function auth($param = [])
    {
        $uri = 'http://api.web.21ds.cn/pinduoduo/createPid';

        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'apkey'     =>  $config['miao_you_quan']['apkey'],
            'pdname'    =>  $config['miao_you_quan']['pdname'],
            'number'    =>  1,
        ];

        $mergeConfig = array_merge($defaultConfig,$param);

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['code'] != 200){
            throw new \Exception($response['msg'],$response['code']);
        }

        return $this->app['config']['original_data'] == true ? $response : $response['data']['p_id_list'][0]['p_id'];
    }
}
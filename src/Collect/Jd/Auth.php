<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;

class Auth extends BaseClient
{
    /**
     * 京东授权
     * @see http://www.jingtuitui.com/api_item?id=20
     * @param array $param
     * @param $user_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     */
    public function auth($param = [],$user_id)
    {
        $uri = 'http://japi.jingtuitui.com/api/new_positionid';

        $config = $this->app['config']['jd'];

        $defaultConfig = [
            'appid' =>  $config['jing_tui_tui']['appid'],
            'appkey'    =>  $config['jing_tui_tui']['appkey'],
            'return_type' =>  'json',

            'unionid'   =>  $config['lian_meng']['unionid'],
            'key'       =>  $config['lian_meng']['key'],
            'unionType' =>  1,
            'type'      =>  1,
            'name'      =>  'jd_' . $user_id,
            'siteId'    =>  $config['lian_meng']['siteId']
        ];

        $mergeConfig = array_merge($defaultConfig,$param);

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0){
            throw new \Exception($response['result'],$response['return']);
        }

        return $this->app['config']['original_data'] == true ? $response : $response['result'];
    }
}
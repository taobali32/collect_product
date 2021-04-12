<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;

class Auth extends BaseClient
{
    /**
     * 京东授权
     * @see https://www.ecapi.cn/index/index/openapi/id/57.shtml?ptype=2
     */
    public function auth($param = [],$user_id)
    {
        $uri = 'http://api.web.21ds.cn/jingdong/createUnionPosition';

        $config = $this->app['config']['jd'];
        
        $defaultConfig = [
            'apkey' =>  $config['miao_you_quan']['apkey'],
            'key_id'    =>  $config['lian_meng']['key_id'],
            'unionType' =>  1,
            'type'  =>  2,
            'spaceNameList' =>  'jd_' . $user_id,
            'siteId'    =>   $config['lian_meng']['site_id'],
        ];

        $mergeConfig = array_merge($defaultConfig,$param);

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['code'] == -1){
            throw new \Exception($response['msg'],$response['code']);
        }

        return $response['data']['resultList']['jd_' . $user_id];
    }
}
<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;

class Auth extends BaseClient
{
    /**
     * 京东授权
     * @see https://www.ecapi.cn/index/index/openapi/id/57.shtml?ptype=2
     */
//    public function auth($param = [],$user_id)
//    {
//        $uri = 'http://api.web.21ds.cn/jingdong/createUnionPosition';
//
//        $config = $this->app['config']['jd'];
//
//        $defaultConfig = [
//            'apkey' =>  $config['miao_you_quan']['apkey'],
//            'key_id'    =>  $config['lian_meng']['key_id'],
//            'unionType' =>  1,
//            'type'  =>  2,
//            'spaceNameList' =>  'jd_' . $user_id,
//            'siteId'    =>   $config['lian_meng']['site_id'],
//        ];
//
//        $mergeConfig = array_merge($defaultConfig,$param);
//
////        dd($mergeConfig);
//
//        $response = $this->httpGet($uri,$mergeConfig);
//
//        dd($response);
//        if ($response['code'] == -1){
//            throw new \Exception($response['msg'],$response['code']);
//        }
//
//        return $response['data']['resultList']['jd_' . $user_id];
//    }


    /**
     * auth
     * @see http://www.jingtuitui.com/api_item?id=20
     * @param array $param
     * @param $user_id
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     */
    public function auth($param = [],$user_id)
    {
        $uri = 'http://japi.jingtuitui.com/api/new_positionid';

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

//        dd($mergeConfig);

        $response = $this->httpGet($uri,$mergeConfig);

        dd($response);
        if ($response['code'] == -1){
            throw new \Exception($response['msg'],$response['code']);
        }

        return $response['data']['resultList']['jd_' . $user_id];
    }
}
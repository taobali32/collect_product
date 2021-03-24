<?php


namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;

class Auth extends BaseClient
{
    /**
     * 获取邀请码
     *
     * @return void
     */
    public function inviteCode()
    {
        $uri = "http://api.web.ecapi.cn/taoke/getInviteCode";

        $config = $this->app['config']['tk'];

        $config = array_merge($config['auth']['invite_code'],$config['miao_you_quan'],[]);

        $response = $this->httpGet($uri,$config);

        if ($response['code'] != 200){
            throw new Exception($response['msg'],$response['code']);
        }

        return $response['data'];
    }

    /**
     * web页面渠道备案
     * @param $code
     * @param $rtag
     * @return mixed
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws Exception
     */
    public function web($code,$rtag)
    {
        $uri = "http://api.web.ecapi.cn/taoke/getRelationOauthTpwd";

        $key = $this->app['config']['tk']['miao_you_quan']['apkey'];

        $arr = [
            'content'       =>  '一键授权备案',
            'rtag'          =>  $rtag,
            'invitercode'   =>  $code,
            'apkey'         =>  $key
        ];

        $response = $this->httpGet($uri,$arr);

        if ($response['code'] == 200 ){
            return $response['data'];
        }

        throw new Exception($response['msg'],$response['code']);
    }

    /**
     * 淘宝客渠道商信息查询API
     * @return mixed
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws Exception
     */
    public function getTbkPublisherInfo()
    {
        $uri = "http://api.web.ecapi.cn/taoke/getTbkPublisherInfo";

        $config = $this->app['config']['tk']['miao_you_quan'];

        $config = array_merge($config,['info_type' => 1,'relation_app' => 'common','page_no' => 1, 'page_size' => 100]);

        $response =  $this->httpGet($uri,$config);

        if ($response['code'] == 200){
            return $response['data'];
        }

        throw new Exception($response['msg'],$response['code']);
    }

    /**
     * APP唤醒备案
     * @return void
     */
    public function app()
    {

    }

    /**
     * App唤醒备案回调
     * @return void
     */
    public function appCallBack()
    {

    }
}
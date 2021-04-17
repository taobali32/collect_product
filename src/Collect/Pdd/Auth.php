<?php


namespace Gather\Collect\Pdd;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use function Gather\Kernel\array_to_json;

class Auth extends BaseClient
{
    protected $uri = "http://gw-api.pinduoduo.com/api/router";

    /**
     * 查询是否绑定备案
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.member.authority.query
     * @param array $param
     * @return array|\Gather\Kernel\Support\Collection|mixed|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function query($param = [])
    {
        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'type'      =>  'pdd.ddk.member.authority.query',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),

            'pid'  =>  '',
        ];

        $margeConfig            =   array_merge($defaultConfig,$param);

        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if ($this->app['config']['original_data']) return $response;

        return $response['authority_query_response']['bind'];
    }

    /**
     * 生成营销工具推广链接/备案
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.rp.prom.url.generate
     * @param array $param
     * @return array|\Gather\Kernel\Support\Collection|mixed|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     * @return string
     */
    public function generate($param = [])
    {
        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'type'      =>  'pdd.ddk.rp.prom.url.generate',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),

            'channel_type'  =>  10,
            'p_id_list' =>  []
        ];

        $margeConfig            =   array_merge($defaultConfig,$param);

        $margeConfig['p_id_list'] = array_to_json($margeConfig['p_id_list']);
        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if ($this->app['config']['original_data']) return $response;

        return $response['rp_promotion_url_generate_response']['url_list'][0]['mobile_url'];
    }

    /**
     * 批量绑定推广位的媒体id
     *
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.pid.mediaid.bind
     * @param array $param
     * @return array|\Gather\Kernel\Support\Collection|mixed|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     * @return boolean
     */
    public function bind($param = [])
    {
        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'type'      =>  'pdd.ddk.pid.mediaid.bind',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),

            'media_id'  =>  $config['duo_duo_jin_bao']['media_id'],
            'pid_list'  =>  []
        ];
        
        $margeConfig            =   array_merge($defaultConfig,$param);

        $margeConfig['pid_list'] = array_to_json($margeConfig['pid_list']);
        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if ($this->app['config']['original_data']) return $response;

        return $response['p_id_bind_response']['result']['result'];
    }

    /**
     * 创建推广位
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.goods.pid.generate
     * @param array $param
     * @return array|\Gather\Kernel\Support\Collection|mixed|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function pdd($param = [])
    {
        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'type'      =>  'pdd.ddk.goods.pid.generate',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),

            'number'      =>  1,
            'media_id'  =>  $config['duo_duo_jin_bao']['media_id']
        ];

        $margeConfig            =   array_merge($defaultConfig,$param);

        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if ($this->app['config']['original_data']) return $response;

        if (count($response['p_id_generate_response']['p_id_list']) == 1) return $response['p_id_generate_response']['p_id_list'][0]['p_id'];

        return $response['p_id_generate_response']['p_id_list'];

    }

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
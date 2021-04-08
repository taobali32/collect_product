<?php


namespace Gather\Collect\Tk;

use app\common\services\ConfigService;
use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use function Gather\Kernel\build_request_param;

class Auth extends BaseClient
{

    /**
     * 获取邀请码
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
     * APP唤醒授权
     * @see https://open.taobao.com/doc.htm?docId=102635&docType=1
     */
    public function app(string $mobile) : string
    {
        $config = $this->app['config']['tk']['bai_chuan'];
        
        $params = [
            'response_type' => 'code',
            'client_id'     => $config['key'],
            'redirect_uri'  => $config['call_back_url'],
            'state'         => $mobile,
            'view'          => 'wap'
        ];

        $baseUrl = "https://oauth.m.taobao.com/authorize";
        $url = build_request_param($baseUrl,$params);

        return $url;
    }


    /**
     * App唤醒备案回调
     * @param string $code 阿里百川请求返回code 
     * @param string $state  自定义
     * @param \Closure $callback
     * @return void
     * @throws Exception
     */
    public function appCallBack($code,$state = '',\Closure $callback)
    {
        if(!empty($code) && !empty($state))
        {
            $relation_id = $this->getSessionKeyByCode($code, $state);

            $str = <<<EOF
                        <script>
                        window.alert = function(name){
        var iframe = document.createElement("IFRAME");
        iframe.style.display="none";
        iframe.setAttribute("src", 'data:text/plain,');
        document.documentElement.appendChild(iframe);
        window.frames[0].window.alert(name);
        iframe.parentNode.removeChild(iframe);
    };
    </script>
EOF;
            echo $str;
            echo "<script src='https://g.alicdn.com/mtb/lib_BC/0.1.0/p/index/index.js'></script>";

            if ($relation_id){
                 $callback($relation_id);
                echo "<script>alert('授权成功');Baichuan.closeWebView();</script>";
            }else{
                echo "<script>alert('授权失败，请重新授权');Baichuan.closeWebView();</script>";
            }
        }
    }
    
    /**
     * 回调获取sessionkey
     * @param string $code
     * @param string $node
     * @return false|mixed
     */
    public function getSessionKeyByCode($code = '',$node = '')
    {
        $config = $this->app['config']['tk']['bai_chuan'];

        include_once  __DIR__ . "/../../Extend/sdk/taobao/TopSdk.php";
        include_once  __DIR__ . "/../../Extend/sdk/taobao/top/request/TbkScPublisherInfoSaveRequest.php";

        try {
            $url = 'https://oauth.taobao.com/token';

            $postfields = [
                'client_id'     => $config['key'],
                'client_secret' => $config['secret'],
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $config['call_back_url'],
                'view'          => 'wap'
            ];

            $post_data = '';
            foreach ($postfields as $key => $value) {
                $post_data .= "$key=" . urlencode($value) . "&";
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($post_data, 0, -1));
            $output = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($output, true);

            if (!isset($result['access_token'])){
                throw new \Exception('获取access_token失败');
            }

            $access_token = $result['access_token'];

            $client = new \TopClient;

            $client->appkey = $config['key'];
            $client->secretKey = $config['secret'];

            $req = new \TbkScPublisherInfoSaveRequest;

            //邀请码
            $req->setInviterCode( $this->inviteCode() );
            //固定值
            $req->setInfoType("1");
            //备案时填写的备注，一般情况下为手机号
            $req->setNote($node);
            //$req->setRegisterInfo("{\"phoneNumber\":\"18801088599\",\"city\":\"江苏省\",\"province\":\"南京市\",\"location\":\"玄武区花园小区\",\"detailAddress\":\"5号楼3单元101室\",\"shopType\":\"社区店\",\"shopName\":\"全家便利店\",\"shopCertifyType\":\"营业执照\",\"certifyNumber\":\"111100299001\"}");

//            $resp = $c->execute($req, $access_token);

            $result = $client->execute($req, $access_token);

            $arr =  json_decode(json_encode($result, JSON_FORCE_OBJECT), true);

            if(isset($arr['data']['relation_id'])) {
                return $arr['data']['relation_id'];
            }

            return '';

        } catch (\Exception $response) {
            
            throw new Exception($response->getMessage(),$response->getCode());
        }

        /*
        SimpleXMLElement Object
        (
            [data] => SimpleXMLElement Object
                (
                    [account_name] => m**2
                    [desc] => 绑定成功
                    [relation_id] => 2536005503
                )

            [request_id] => vt2vapo1e45b
        )
        */

        /*
         * access_token
        Array
        (
            [w1_expires_in] => 2592000
            [refresh_token_valid_time] => 1596018822133
            [taobao_user_nick] => ma7725162
            [re_expires_in] => 2592000
            [expire_time] => 1596018822133
            [token_type] => Bearer
            [access_token] => 62016038245ZZa5a94e9f2935facdee3cb493782d7f4ce71700011068
            [taobao_open_uid] => AAH6DC5qAHMv5Ql5IFba2AE8
            [w1_valid] => 1596018822133
            [refresh_token] => 6200e03f206ZZ3e0becab6c366d71e30d524faf2e7f530c1700011068
            [w2_expires_in] => 300
            [w2_valid] => 1593427122133
            [r1_expires_in] => 2592000
            [r2_expires_in] => 86400
            [r2_valid] => 1593513222133
            [r1_valid] => 1596018822133
            [taobao_user_id] => 1700011068
            [expires_in] => 2592000
        )
            */
    }
}
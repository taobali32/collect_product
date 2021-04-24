<?php


namespace Gather\Collect\Sn;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Exceptions\InvalidConfigException;
use Gather\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
use OpenSDK\Suning\Client;
use OpenSDK\Suning\Requests\Netalliance\SearchcommodityQueryRequest;
use PhpParser\Node\Stmt\DeclareDeclare;
use function Gather\Kernel\array_to_json;

class Product extends BaseClient
{
    use InteractsWithCache;

    protected $uri = "http://gw-api.pinduoduo.com/api/router";
    
    /**
     * 商品推广/转链？
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.goods.promotion.url.generate
     */
    public function generate($param = [])
    {
        $config = $this->app['config']['pdd'];

        $defaultConfig = [
            'type'      =>  'pdd.ddk.goods.promotion.url.generate',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),

            'data_type' =>  'JSON',
            'version'   =>  'V1',
//            'custom_parameters' =>  '{"new":2}',

            'p_id'      =>  '',
            'goods_sign_list'   =>  []  //  # 需要转为json
        ];

        $margeConfig            =   array_merge($defaultConfig,$param);
        
        $margeConfig['goods_sign_list'] = array_to_json($margeConfig['goods_sign_list']);
        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);
        
//        dd($margeConfig);
        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        return $this->app['config']['original_data'] == true ? $response :  $response['goods_promotion_url_generate_response']['goods_promotion_url_list'][0]['mobile_short_url'];

    }


    /**
     * 商品搜索/商品详情(keyword)
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.goods.search
     * @param array $param
     * @param string $mark
     * @param false $clear
     * @return array|\Gather\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws Exception
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function search($param = [],$mark = '',$clear = false )
    {
        ini_set("display_errors", "On");//打开错误提示
        ini_set("error_reporting",E_ALL);//显示所有错误

        $config = $this->app['config']['sn'];

        $cache_page = 'sn_page_' . __FUNCTION__ . $mark;

        if ($clear){
            $this->getCache()->delete($cache_page);
        }

        $page    = $this->getCache()->has($cache_page) ? $this->getCache()->get($cache_page): 1;

        $c = new Client();
        $c->appKey = $config['AppKey'];
        $c->appSecret = $config['AppSecret'];

        $req = new SearchcommodityQueryRequest();
        $req->setPageIndex($page);

        if (isset($param['cate_name']) && !empty($param['cate_name']) ){
            $req->setSaleCategoryCode($param['cate_name']);
        }

        if (isset($param['keyword']) && !empty($param['keyword']) ){
            $req->setKeyword($param['keyword']);
        }
        if (isset($param['size']) && !empty($param['size'])){
            $req->setSize($param['size']);
        }else{
            $req->setSize(40);
        }


        $arr = $req->apiParams;

        $arr['coupon'] = (string)1;
        $arr['couponMark'] = (string)1;
        $req->apiParams = $arr;

        $c->setRequest($req);
        $response = $c->execute();

        if ( $clear == false ){
            $this->getCache()->set($cache_page, ++$page  ,600);
        }else{
            $this->getCache()->set($cache_page, 1  ,600);
        }


        file_put_contents('./data1.php',"<?php return " . var_export($response['sn_responseContent']['sn_body']['querySearchcommodity'],true) . ";");

        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['sn_responseContent']['sn_body']['querySearchcommodity'] );
    }
    
    
    /**
     * returnData
     * @param $response
     * @return array
     */
    protected function returnData($response)
    {
        $arr = [];

        var_dump(count($response));
        foreach ($response as $k => $v){

            $imgs = [];

            foreach ($v['commodityInfo']['pictureUrl'] as $Kk => $Vv){
                $imgs[] = $Vv['picUrl'];
            }



            $arr[] = [
                'product_id'            =>  $v['commodityInfo']['commodityCode'],
                'sale'                  =>  $v['commodityInfo']['monthSales'],
                'coupon_url'            =>  $v['couponInfo']['couponUrl'],
                'coupon_money'          =>  $v['couponInfo']['couponValue'],
                'coupon_explain'        =>  $v['commodityInfo']['commodityName'],
                'guide_article'         =>  $v['commodityInfo']['commodityName'],
                'item_title'            =>  $v['commodityInfo']['commodityName'],
                'item_desc'             =>  $v['commodityInfo']['commodityName'],
                'shop_type'             =>  $v['commodityInfo']['commodityType'],
                'cate'                  =>  $v['categoryInfo']['firstSaleCategoryName'],
                'start_time'            =>  $v['couponInfo']['startTime'],
                'end_time'              =>  $v['couponInfo']['endTime'],
                'slide_image'           =>  $imgs,
                'cover'                 =>  $v['commodityInfo']['pictureUrl'][0]['picUrl'],
                'item_end_price'        =>  $v['couponInfo']['afterCouponPrice'],
                'item_price'            =>  $v['commodityInfo']['commodityPrice'],
                'predict_money'         =>  round($v['commodityInfo']['commodityPrice']*$v['commodityInfo']['rate'] / 100,2),
                'rate'                  =>  $v['commodityInfo']['rate'],
                'item_detail'           =>  [],
                'item_detail_type'      =>  2
            ];
        }

//        dd($arr);
        return $arr;
    }


}
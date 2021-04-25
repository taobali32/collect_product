<?php


namespace Gather\Collect\Sn;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Exceptions\InvalidConfigException;
use Gather\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
use OpenSDK\Suning\Client;
use OpenSDK\Suning\Requests\Netalliance\CommoditydetailQueryRequest;
use OpenSDK\Suning\Requests\Netalliance\ExtensionlinkGetRequest;
use OpenSDK\Suning\Requests\Netalliance\OrderQueryRequest;
use OpenSDK\Suning\Requests\Netalliance\SearchcommodityQueryRequest;
use PhpParser\Node\Stmt\DeclareDeclare;
use function Gather\Kernel\array_to_json;

class Product extends BaseClient
{
    use InteractsWithCache;

    
    public function order($param = [],$mark = '',$clear = false)
    {
        $cache_page = 'sn_order_' . __FUNCTION__ . $mark;

        if ($clear){
            $this->getCache()->delete($cache_page);
        }

        $page_no    = $this->getCache()->has($cache_page) ? $this->getCache()->get($cache_page): 1;

        $config = $this->app['config']['sn'];

        $c = new Client();
        $c->appKey = $config['AppKey'];
        $c->appSecret = $config['AppSecret'];

        $req = new OrderQueryRequest();

        $defaultConfig = [
            'page_no'   =>  $page_no,
            'page_size' =>  50,
            'start_time'    =>  date('Y-m-d H:i:s',time() - 600),
            'end_time'      =>  date('Y-m-d H:i:s',time()),
            'status'        =>  0
        ];

        $defaultConfig = array_merge($defaultConfig,$param);

        $req->setPageNo($defaultConfig['page_no']);
        $req->setPageSize($defaultConfig['page_size']);
        $req->setStartTime($defaultConfig['start_time']);
        $req->setEndTime($defaultConfig['end_time']);
        $req->setOrderLineStatus($defaultConfig['status']);

        $c->setRequest($req);
        $response = $c->execute();

//        file_put_contents('./dat1a.php',"<?php return " . var_export($response,true) . ";");


        if ( $clear == false ){
            $this->getCache()->set($cache_page, ++$page_no  ,600);
        }else{
            $this->getCache()->set($cache_page, 1  ,600);
        }

        return $this->app['config']['original_data'] == true ? $response : $response['sn_responseContent']['sn_body']['queryOrder'];

    }


    public function ticket2($urls,$user_id)
    {
        $url = 'http://api.web.ecapi.cn/suning/doCustomPromotionUrl';

        $config = $this->app['config']['sn'];

        $param = [
            'apkey'     =>  $config['miao_you_quan']['apkey'],
            'visitUrl'  =>  $urls,
            'subUser'   =>  $user_id
        ];

        $response = $this->httpGet($url,$param);

        if (isset($response['code']) && $response['code'] == 200){
            return $this->app['config']['original_data'] == true ?
                $response : $response['data']['shortUrl'];
        }
        
    }


    /**
     * 关键字查询
     * @param array $param
     * @return array|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     */
    public function detail($param = [])
    {
        $config = $this->app['config']['sn'];

        $c = new Client();
        $c->appKey = $config['AppKey'];
        $c->appSecret = $config['AppSecret'];

        $req = new CommoditydetailQueryRequest();

        $req->setCommodityStr($param['product_id']);


        $c->setRequest($req);
        $response = $c->execute();

        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['sn_responseContent']['sn_body']['queryCommoditydetail'] )[0];

    }


    /**
     * 商品搜索
     * @see
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

        foreach ($response as $k => $v){

            $imgs = [];

            foreach ($v['commodityInfo']['pictureUrl'] as $Kk => $Vv){
                $imgs[] = $Vv['picUrl'];
            }

            $arr[] = [
                'product_url'           =>  $v['pgInfo']['pgUrl'],
                'product_id'            =>  $v['commodityInfo']['commodityCode'] . '-' . $v['commodityInfo']['supplierCode'],
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

        return $arr;
    }
}
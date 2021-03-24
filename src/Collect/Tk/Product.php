<?php

namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Exceptions\InvalidConfigException;
use Gather\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
use function Couchbase\defaultDecoder;

/**
 * Class Product
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Tk
 */
class Product extends BaseClient
{
    use InteractsWithCache;

    protected $cacheMinIdName = 'cache_tk_min_id';

    protected $cacheUpdateMinIdName = 'cache_update_min_id_name_id';

    protected $cacheNowProductMinIdName = 'cache_now_product_min_id_name';


    // 分享
    public function productLinkId($product_id,$relationid)
    {
        $uri = 'http://api.web.ecapi.cn/taoke/doItemHighCommissionPromotionLinkByAll';

        $config = $this->app['config']['tk']['miao_you_quan'];

        $param = [
            'apkey' =>  $config['apkey'],
            'tbname'    =>  $config['tbname'],
            'pid'       =>  $config['pid'],
            'shorturl'  =>  1,
            'tpwd'      =>  1,
            'content'   =>  $product_id,
            'relationid'    =>  $relationid,
            'hasiteminfo'   =>  1
        ];

        $response =  $this->httpGet($uri,$param);

        if ($response['code'] == 200){
            return $response['data'];
        }

        throw new Exception($response['msg'],$response['code']);
    }

    /**
     * 各大榜单
     *
     * @param int $min_id
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function bangDan($min_id = 1): array
    {
        $config = $this->app['config']['tk'];
        
        $defaultConfig = [
            'sale_type' =>  1,
            'cid'       =>  0,
            'back'      =>  100,
            'item_type' =>  1
        ];

        $mergeConfig = array_merge($defaultConfig,$config['product']);
        
        $mergeConfig['min_id'] = $min_id;

        $str = '';
        foreach ($mergeConfig as $k => $v){
            $str .= '/' . $k . '/' . $v;
        }

        $uri = "http://v2.api.haodanku.com/sales_list/apikey/{$config['hao_dan_ku']['api_key']}" . $str;
        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        return ['min_id' => $response['min_id'], 'data' => $this->returnData($response['data'])];
    }

    /**
     * 今日值得买
     *
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function dayWorth()
    {
        $config = $this->app['config']['tk'];

        $uri = "http://v2.api.haodanku.com/get_deserve_item/apikey/{$config['hao_dan_ku']['api_key']}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        return $this->returnData($response['item_info']);
    }


    /**
     * Detail product.
     *
     * @param string $itemid
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function detail($itemid = '')
    {
        $config = $this->app['config']['tk'];

        $uri = "http://v2.api.haodanku.com/item_detail/apikey/{$config['hao_dan_ku']['api_key']}/itemid/{$itemid}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $arr = [$response['data']];

        return $this->returnData($arr);
    }

    /**
     * Search products.
     *
     * @param array $param
     *
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function search($param = [])
    {
        $config = $this->app['config']['tk'];

        $defaultConfig = [
            'is_tmall'  =>  0,
            'back'      =>  100,
            'is_coupon' =>  1,
            'sort'      =>  0,
            'is_out'    =>  true
        ];

        $mergeConfig = array_merge($defaultConfig,$config['product']);

        if (isset($param['keyword'])){
            $param['keyword'] = urlencode(urlencode($param['keyword']));
        }

        if (!isset($param['min_id'])){
            $param['min_id'] = 1;
        }

        $p = array_merge($mergeConfig,$param);

        $str = '';
        foreach ($p as $k => $v){
            $str .= '/' . $k . '/' . $v;
        }
        
        $uri = "http://v2.api.haodanku.com/supersearch/apikey/{$config['hao_dan_ku']['api_key']}" . $str;

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $arr = [];

        foreach ($response['data'] as $k => $v){
            $arr[] = [
                'product_id'            =>  $v['itemid'],
                'sale'                  =>  $v['itemsale'],
                'coupon_url'            =>  $v['couponurl'],
                'coupon_money'          =>  $v['couponmoney'],
                'coupon_explain'        =>  '',
                'guide_article'         =>  '',
                'item_title'            =>  $v['itemtitle'],
                'item_desc'             =>   $v['itemdesc'],
                'shop_type'             =>  ($v['shoptype'] == 'B') ? 'tm' : 'tb',
                'cate'                  =>  0,
                'start_time'            =>  $v['couponstarttime'],
                'end_time'              =>  $v['couponendtime'],
                'slide_image'           =>  isset($v['taobao_image']) ? explode(',',$v['taobao_image']) : [],
                'cover'                 =>  $v['itempic'],
                'item_end_price'        =>  $v['itemendprice'],
                'item_price'            =>  $v['itemprice'],
                'predict_money'         =>  empty($v['tkmoney']) ? ($v['itemendprice'] * $v['tkrates'] / 100) : $v['tkmoney'],
                'rate'                  =>  $v['tkrates'],
                'item_detail'           =>  [$v['itempic']],
                'item_detail_type'      =>  1
            ];
        }
        
        return ['min_id' => $response['min_id'],'data' => $arr];
    }

    /**
     * Down products.
     *
     * @param array $param
     *
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function down($param = [])
    {
        $param = array_merge(['start' => 0, 'end' => 23],$param);

        $config = $this->app['config']['tk'];

        $uri = "http://v2.api.haodanku.com/get_down_items/apikey/{$config['hao_dan_ku']['api_key']}/start/{$param['start']}/end/{$param['end']}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $arr = [];
        foreach ($response['data'] as $k => $v){
            $arr[] = $v['itemid'];
        }

        return $arr;
    }

    /**
     * New products.
     *
     * @param array $param
     *
     * @return array
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws Exception
     */
    public function new_products($param = [])
    {
        $param = array_merge(['start' => 0, 'end' => 23],$param);

        $config = $this->app['config']['tk'];

        $min_id = $this->getCache()->has($this->cacheNowProductMinIdName) ? $this->getCache()->get($this->cacheNowProductMinIdName): 1;

        $item_type = isset($config['product']['item_type']) ? $config['product']['item_type'] : 1;

        $uri = "http://v2.api.haodanku.com/timing_items/apikey/{$config['hao_dan_ku']['api_key']}/start/{$param['start']}/end/{$param['end']}/min_id/{$min_id}/back/{$config['product']['back']}/item_type/{$item_type}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $this->getCache()->set($this->cacheNowProductMinIdName,$response['min_id'],10);

        return $this->returnData($response['data']);
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

            if (isset($v['itempic_copy'])){
                $d = ["http://img-haodanku-com.cdn.fudaiapp.com" . $v['itempic_copy']];
            }else{
                $d = [$v['itempic']];
            }
            $arr[] = [
                'product_id'            =>  $v['itemid'],
                'sale'                  =>  $v['itemsale'],
                'coupon_url'            =>  $v['couponurl'],
                'coupon_money'          =>  $v['couponmoney'],
                'coupon_explain'        =>  '',
                'guide_article'         =>  isset($v['guide_article']) ? $v['guide_article'] : '',
                'item_title'            =>  $v['itemtitle'],
                'item_desc'             =>  $v['itemdesc'],
                'shop_type'             =>  ($v['shoptype'] == 'B') ? 'tm' : 'tb',
                'cate'                  =>  isset($v['fqcat']) ? $v['fqcat'] : 0,
                'start_time'            =>  isset($v['start_time']) ? $v['start_time'] : $v['couponstarttime'],
                'end_time'              =>  isset($v['end_time']) ? $v['end_time'] : $v['couponendtime'],
                'slide_image'           =>  isset($v['taobao_image']) ? explode(',',$v['taobao_image']) : [],
                'cover'                 =>  $v['itempic'],
                'item_end_price'        =>  $v['itemendprice'],
                'item_price'            =>  $v['itemprice'],
                'predict_money'         =>  $v['tkmoney'],
                'rate'                  =>  $v['tkrates'],
                'item_detail'           =>  $d,
                'item_detail_type'      =>  1
            ];
        }

        return $arr;
    }

    /**
     * Update Products.
     *
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Exception
     */
    public function update()
    {
        $config = $this->app['config']['tk'];

        $min_id = $this->getCache()->has($this->cacheUpdateMinIdName) ? $this->getCache()->get($this->cacheUpdateMinIdName) : 1;

        $uri = "http://v2.api.haodanku.com/update_item/apikey/{$config['hao_dan_ku']['api_key']}/sort/{$config['product']['sort']}/back/{$config['product']['back']}/min_id/{$min_id}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){

            throw new Exception($response['msg'],$response['code']);
        }
        
        $this->getCache()->set($this->cacheUpdateMinIdName,$response['min_id'],10);

        $arr = [];

        foreach ($response['data'] as $k => $v){
            $arr[] = [
                'product_id'            =>  $v['itemid'],
                'sale'                  =>  $v['itemsale'],
                'coupon_url'            =>  $v['couponurl'],
                'coupon_money'          =>  $v['couponmoney'],
                'item_end_price'        =>  $v['itemendprice'],
                'item_price'            =>  $v['itemprice'],
                'predict_money'         =>  $v['tkmoney'],
                'rate'                  =>  $v['tkrates'],
            ];
        }

        return $arr;
    }

    /**
     * Get product lists.
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws Exception
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get()
    {
        $config = $this->app['config']['tk'];

        $min_id = $this->getCache()->has($this->cacheMinIdName) ? $this->getCache()->get($this->cacheMinIdName): 1;

        $uri = "http://v2.api.haodanku.com/itemlist/apikey/{$config['hao_dan_ku']['api_key']}/nav/3/cid/0/back/{$config['product']['back']}/min_id/{$min_id}";

        $response = $this->httpGet($uri);

        if ($response['code'] != 1 ){
            throw new Exception($response['msg'],$response['code']);
        }

        $this->getCache()->set($this->cacheMinIdName,$response['min_id'],10);

        return $this->returnData($response['data']);
    }
}
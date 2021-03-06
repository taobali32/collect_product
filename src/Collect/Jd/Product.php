<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Exceptions\InvalidConfigException;
use Gather\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
use function Gather\Kernel\json_to_array;

/**
 * Class Product
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/10 19:49
 * @package Gather\Collect\Jd
 */
class Product extends BaseClient
{
    use InteractsWithCache;

    /**
     * 相似商品推荐
     * @deprecated 
     * @see http://www.jingtuitui.com/api_item?id=24
     * @param array $param
     * @return array|\Gather\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function relatedGoods($param = [])
    {
        $uri = "http://japi.jingtuitui.com/api/related_goods";

        $config = $this->app['config']['jd'];

        $defaulutParam = [
            'appid'     =>  $config['jing_tui_tui']['appid'],
            'appkey'    =>  $config['jing_tui_tui']['appkey'],
            'v'         =>  'v2',
            'return_type'   =>  'json',
            'jd_type'    => 2
        ];

        $mergeConfig = array_merge($defaulutParam,$param);

        $response =  $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        dd($response['result']);
        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['result']);
    }


    /**
     * 商品转链
     * @see http://www.jingtuitui.com/api_item?id=14
     * @param $product_id
     * @param $relationid
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function productLinkId($product_id,$relationid,$param = [])
    {
        $uri = 'http://japi.jingtuitui.com/api/get_goods_link';

        $config = $this->app['config']['jd'];

        $defaulutParam = [
            'appid'     =>  $config['jing_tui_tui']['appid'],
            'appkey'    =>  $config['jing_tui_tui']['appkey'],
            'return_type'   =>  'json',

            'unionid'   =>  $config['lian_meng']['unionid'],
            'gid'       =>  $product_id,
            'positionid'    =>  $relationid,
            'chainType' =>  2
        ];

        $margeParam = array_merge($defaulutParam,$param);

        $response =  $this->httpGet($uri,$margeParam);

        if ($response['return'] != 0){
            throw new Exception($response['msg'],$response['return']);
        }

        return $this->app['config']['original_data'] == true ? $response : $response['result']['link'];
    }

    /**
     * 获取商品详情
     * @see https://www.haodanku.com/Openapi/api_detail?id=17
     * @return array
     * @throws GuzzleException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function detail($param = [])
    {
        $config = $this->app['config']['jd'];

        $uri = "http://japi.jingtuitui.com/api/jd_goods_query";

        $defaultConfig = [
            'appid'         =>  $config['jing_tui_tui']['appid'],
            'appkey'        =>   $config['jing_tui_tui']['appkey'],
            'v'             =>  'v3',
            'return_type'   =>  'json',
        ];

        $mergeConfig = array_merge($defaultConfig,$param);

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        return $this->app['config']['original_data'] == true ? $response : $response['result']['goods'];
    }


    /**
     * 京东超市
     * @see  http://www.jingtuitui.com/api_item?id=32
     *
     * @param array $param
     * @param string $mark
     * @param bool  $clear
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function supermarket($param = [],$mark = '',$clear = false): array
    {
        $uri = 'http://japi.jingtuitui.com/api/get_goods_list';

        $config = $this->app['config']['jd'];

        $defaultConfig = [
            'eliteId'       =>  'jdMarket',
            'v'             =>  'v2',
            'return_type'   =>  'json',
            'appid'         =>  '',
            'appkey'        =>  '',
            'pageIndex'     =>  1,
            'pageSize'      =>  100,
        ];

        $min_id_cache = __FUNCTION__ . 'pageIndex' . $mark;

        if ($clear){
            $this->getCache()->delete($min_id_cache);
        }

        $defaultConfig['pageIndex'] = $this->getCache()->has($min_id_cache) ? $this->getCache()->get($min_id_cache): 1;

        $mergeConfig = array_merge($defaultConfig,$param);
        $mergeConfig['appid'] = $config['jing_tui_tui']['appid'];
        $mergeConfig['appkey'] = $config['jing_tui_tui']['appkey'];

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        $this->getCache()->set( $min_id_cache, ++$mergeConfig['pageIndex'] , 3600 );

        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['result']['data']);
    }



    /**
     * 京东拼团
     *
     * @param array $param
     * @param string $mark
     * @param bool  $clear
     * @see http://www.jingtuitui.com/api_item?id=17
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function tuan($param = [],$mark = '',$clear = false): array
    {
        $uri = 'http://japi.jingtuitui.com/api/get_goods_list';

        $config = $this->app['config']['jd'];

        $defaultConfig = [
            'eliteId'       =>  'collage',
            'v'             =>  'v2',
            'return_type'   =>  'json',
            'appid'         =>  '',
            'appkey'        =>  '',
            'pageIndex'     =>  1,
            'pageSize'      =>  100,
        ];

        $min_id_cache = __FUNCTION__ . 'pageIndex' . $mark;

        if ($clear){
            $this->getCache()->delete($min_id_cache);
        }
        
        $defaultConfig['pageIndex'] = $this->getCache()->has($min_id_cache) ? $this->getCache()->get($min_id_cache): 1;

        $mergeConfig = array_merge($defaultConfig,$param);
        $mergeConfig['appid'] = $config['jing_tui_tui']['appid'];
        $mergeConfig['appkey'] = $config['jing_tui_tui']['appkey'];

        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        $this->getCache()->set( $min_id_cache, ++$mergeConfig['pageIndex'] , 3600 );
        
        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['result']['data']);
    }


    /**
     * 9.9专场
     *
     * @param array $param
     * @param string $mark
     * @param bool  $clear
     * @see http://www.jingtuitui.com/api_item?id=13
     * @return array
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function get9($param = [],$mark = '',$clear = false): array
    {
        $uri = 'http://japi.jingtuitui.com/api/get_goods_list';

        $config = $this->app['config']['jd'];

        $defaultConfig = [
            'eliteId'       =>  'nineSift',
            'v'             =>  'v2',
            'return_type'   =>  'json',
            'appid'         =>  '',
            'appkey'        =>  '',
            'pageIndex'     =>  1,
            'pageSize'      =>  100,
        ];

        $min_id_cache = __FUNCTION__ . 'pageIndex' . $mark;

        if ($clear){
            $this->getCache()->delete($min_id_cache);
        }

        $defaultConfig['pageIndex'] = $this->getCache()->has($min_id_cache) ? $this->getCache()->get($min_id_cache): 1;

        $mergeConfig = array_merge($defaultConfig,$param);
        $mergeConfig['appid'] = $config['jing_tui_tui']['appid'];
        $mergeConfig['appkey'] = $config['jing_tui_tui']['appkey'];


        $response = $this->httpGet($uri,$mergeConfig);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        $this->getCache()->set( $min_id_cache, ++$mergeConfig['pageIndex'] , 3600 );
        
        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['result']['data']);
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

            $slide_images = [];

            if ($v['J_state'] == 2){
                $arr[] = [
                    'product_id'            =>  $v['goods_id'],
                    'sale'                  =>  $v['inOrderCount30Days'],
                    'coupon_url'            =>  $v['discount_link'],
                    'coupon_money'          =>  $v['discount_price'],
                    'coupon_explain'        =>  '',
                    'guide_article'         =>  isset($v['goods_content']) ? $v['goods_content'] : '',
                    'item_title'            =>  $v['goods_name'],
                    'item_desc'             =>  $v['goods_content'],
                    'shop_type'             =>  $v['jd_type'],
                    'cate'                  =>  $v['cid1'],
                    'start_time'            =>  $v['get_start_time'] / 1000,
                    'end_time'              =>  $v['get_end_time'] / 1000,

                    'slide_image'           =>  [$v['goods_img']],
                    'cover'                 =>  $v['goods_img'],
                    'item_end_price'        =>  $v['final_price'],
                    'item_price'            =>  $v['goods_price'],
                    'predict_money'         =>  $v['goods_price'] * $v['commissionShare'] / 100,
                    'rate'                  =>  $v['commissionShare'],
                    'item_detail'           =>  '',
                    'item_detail_type'      =>  2
                ];
            }
        }

        return $arr;
    }

    /**
     * 商品获取
     * @see http://www.jingtuitui.com/api_item?id=1
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     */
    public function get($param = [],$mark = '', $clear = false )
    {
        $config = $this->app['config']['jd'];
        
        $cacheMinIdName = 'JD_' . __FUNCTION__  . $mark;
        
        
        if ($clear) $this->getCache()->delete($cacheMinIdName);

        $pageIndex = $this->getCache()->has($cacheMinIdName) ?  $this->getCache()->get($cacheMinIdName) : 1;

        $config = [
            'v'         =>  'v2',
            'appid'     =>  $config['jing_tui_tui']['appid'],
            'appkey'    =>  $config['jing_tui_tui']['appkey'],
            'pageIndex' =>  $pageIndex,
            'pageSize'  =>  $config['product']['pageSize']
        ];

        $uri = "http://japi.jingtuitui.com/api/get_goods_list";

        $config = array_merge($config,$param);

        $response = $this->httpGet($uri,$config);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }
        
        $this->getCache()->set($cacheMinIdName,$response['result']['current_page'] + 1,10);
        
        return ($this->app['config']['original_data'] == true) ? $response : $this->returnData($response['result']['data']);


        $arr = [];

        foreach ($response['result']['data'] as $k => $v){
            $imageList = [];
            foreach (json_to_array($v['imageList']) as $item){
                $imageList[] = $item['url'];
            }

            if ($v['J_state'] == 2){
                $arr[] = [
                    'product_id'            =>  $v['goods_id'],
                    'sale'                  =>  $v['inOrderCount30Days'],
                    'coupon_url'            =>  $v['discount_link'],
                    'coupon_money'          =>  $v['discount_price'],
                    'coupon_explain'        =>  '',
                    'guide_article'         =>  $v['circle_content'],
                    'item_title'            =>  $v['goods_name'],
                    'item_desc'             =>  $v['goods_content'],
                    'shop_type'             => ($v['owner'] == 'g') ? '自营' : '其他',
                    'cate'                  =>  $v['goods_type'],
                    'start_time'            =>  $v['get_start_time'] / 1000,
                    'end_time'              =>  $v['get_end_time'] / 1000,
                    'slide_image'           =>  $imageList,
                    'cover'                 =>  $v['goods_img'],
                    'item_end_price'        =>  $v['final_price'],
                    'item_price'            =>  $v['goods_price'],
                    'predict_money'         =>  bcmul($v['final_price'],$v['commissionShare'],2) / 100,
                    'rate'                  =>  $v['commissionShare'],
                    'item_detail'           =>  $v['pc_ware_style'],
                    'item_detail_type'      =>  2,
                    'state'                 =>  $v['J_state']
                ];
            }

        }

        return $arr;
    }
}
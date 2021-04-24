<?php


namespace Gather\Collect\Pdd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Exceptions\InvalidConfigException;
use Gather\Kernel\Traits\InteractsWithCache;
use GuzzleHttp\Exception\GuzzleException;
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
        $config = $this->app['config']['pdd'];

        $cache_name = 'pdd_' . __FUNCTION__ . $mark;
        $cache_page = 'pdd_page_' . __FUNCTION__ . $mark;

        if ($clear){
            $this->getCache()->delete($cache_name);
            $this->getCache()->delete($cache_page);
        }

        $list_id = $this->getCache()->has($cache_name) ? $this->getCache()->get($cache_name): '';
        $page    = $this->getCache()->has($cache_page) ? $this->getCache()->get($cache_page): 1;

        $defaultConfig = [
            'type'      =>  'pdd.ddk.goods.search',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),
            'data_type' =>  'JSON',
            'version'   =>  'V1',
            'with_coupon'   =>  true,
            'page'      =>  $page,
            'custom_parameters' =>  '{"new":1}'
//            'list_id'   =>  '',
//            'keyword'   =>  ''
        ];

        if ($list_id){
            $defaultConfig['list_id'] = $list_id;
        }

        $margeConfig            =   array_merge($defaultConfig,$param);
        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if (isset( $response['goods_search_response']['list_id'] )){
            $this->getCache()->set($cache_page, ++$page  ,600);
            $this->getCache()->set($cache_name, $response['goods_search_response']['list_id']  ,600);
        }

        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['goods_search_response']['goods_list'] );
    }

    /**
     * 商品推荐
     * @see https://jinbao.pinduoduo.com/third-party/api-detail?apiName=pdd.ddk.goods.recommend.get
     * @param array $param
     * @return void
     */
    public function tui($param = [],$mark = '',$clear = false)
    {
        $config = $this->app['config']['pdd'];

        $cache_name = 'pdd_' . __FUNCTION__ . $mark;

        $defaultConfig = [
            'type'              =>  'pdd.ddk.goods.recommend.get',
            'client_id'         =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp'         =>  (string)time(),
            'data_type'         =>  'JSON',
//            'sign'              =>  '',
//            'channel_type'      =>  1, // 搜索类型
            'limit'             =>  50,
//            'list_id'           =>  ''   //   下一页id
        ];

        $margeConfig = array_merge($defaultConfig,$param);

        if(isset($margeConfig['activity_tags'])){
            $margeConfig['activity_tags'] = array_to_json( $margeConfig['activity_tags'] );
        }

        $margeConfig['sign']    = $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);
        
        if ($clear){
            $this->getCache()->delete($cache_name);
        }

        $list_id = $this->getCache()->has($cache_name) ? $this->getCache()->get($cache_name): '';

        if ($list_id){
            $margeConfig['list_id'] = $list_id;
        }

        $response = $this->httpGet($this->uri,$margeConfig);

        if (isset( $response['error_response'])){
            throw new Exception($response['error_response']['error_msg'],$response['error_response']['error_code']);
        }

        if (isset($response['goods_basic_detail_response']['list_id'])){
            $this->getCache()->set($cache_name,$response['goods_basic_detail_response']['list_id'],600);
        }

        return $this->app['config']['original_data'] == true ? $response : $this->returnData( $response['goods_basic_detail_response']['list'] );

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

            if (isset($v['has_coupon']) && $v['has_coupon'] == true){
                $arr[] = [
                    'product_id'            =>  $v['goods_sign'],
                    'sale'                  =>  isset($v['sales_tip']) ? $v['sales_tip'] : 0,
                    'coupon_url'            =>  '',
                    'coupon_money'          =>  $v['coupon_discount'] / 100,
                    'coupon_explain'        =>  '',
                    'guide_article'         =>  $v['goods_desc'],
                    'item_title'            =>  $v['goods_name'],
                    'item_desc'             =>  $v['goods_desc'],
                    'shop_type'             =>  '',
                    'cate'                  =>  $v['opt_name'],
                    'start_time'            =>  $v['coupon_start_time'],
                    'end_time'              =>  $v['coupon_end_time'],
                    'slide_image'           =>  [$v['goods_image_url']],
                    'cover'                 =>  $v['goods_image_url'],
                    'item_end_price'        =>   round($v['min_group_price'] / 100),
                    'item_price'            =>  round($v['min_normal_price'] / 100, 2),
                    'predict_money'         =>   round(($v['promotion_rate'] / 1000 * ($v['min_group_price'] / 100)),2),
                    'rate'                  =>  $v['promotion_rate']/ 1000,
                    'item_detail'           =>  [],
                    'item_detail_type'      =>  2
                ];
            }
        }

        return $arr;
    }


}
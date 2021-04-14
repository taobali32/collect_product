<?php


namespace Gather\Collect\Pdd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;
use function Gather\Kernel\array_to_json;

class Product extends BaseClient
{
    use InteractsWithCache;

    protected $uri = "http://gw-api.pinduoduo.com/api/router";

    //  商品搜索
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
        $page = $this->getCache()->has($cache_page) ? $this->getCache()->get($cache_page): 1;

//        dd($list_id);   //  1618413871789_5d09ccdd2e36738809b5108850dc01ae
        $defaultConfig = [];
        if ($list_id){
            $defaultConfig['list_id'] = $list_id;
        }

        $defaultConfig = [
            'type'      =>  'pdd.ddk.goods.search',
            'client_id' =>  $config['duo_duo_jin_bao']['client_id'],
            'timestamp' =>  time(),
            'data_type' =>  'JSON',
            'version'   =>  'V1',
            'with_coupon'   =>  true,
            'page'      =>  $page,
//            'list_id'   =>  '',
//            'keyword'   =>  ''
        ];

        $margeConfig            =   array_merge($defaultConfig,$param);
        $margeConfig['sign']    =   $this->pddSign($margeConfig,$config['kai_fang_ping_tai']['client_secret']);

//        dd($margeConfig);
        $response = $this->httpGet($this->uri,$margeConfig);

        dd($response);
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
                    'product_id'            =>  $v['goods_id'],
                    'sale'                  =>  isset($v['sales_tip']) ? $v['sales_tip'] : 0,
                    'coupon_url'            =>  '',
                    'coupon_money'          =>  $v['coupon_discount'] / 100,
                    'coupon_explain'        =>  '',
                    'guide_article'         =>  $v['goods_desc'],
                    'item_title'            =>  $v['goods_name'],
                    'item_desc'             =>  $v['goods_name'],
                    'shop_type'             =>  '',
                    'cate'                  =>  $v['opt_name'],
                    'start_time'            =>  $v['coupon_start_time'],
                    'end_time'              =>  $v['coupon_end_time'],
                    'slide_image'           =>  [$v['goods_image_url']],
                    'cover'                 =>  $v['goods_image_url'],
                    'item_end_price'        =>   $v['min_group_price'] * 0.1,
                    'item_price'            =>  round($v['min_normal_price']*0.01, 2),
                    'predict_money'         =>   ($v['promotion_rate']*0.1 * $v['min_group_price'] * 0.1 - $v['coupon_discount']*0.01) / 100,
                    'rate'                  =>  $v['promotion_rate']/ 1000,
                    'item_detail'           =>  [],
                    'item_detail_type'      =>  2
                ];
            }
        }

        return $arr;
    }

    /**
     * 签名
     * @param $param
     * @param $secret
     * @return string
     */
    public function pddSign($param,$secret)
    {
        $param = func_get_args()[0];



        ksort($param);    //  排序

        $client_secret = func_get_args()[1];

        $str = '';      //  拼接的字符串


//        dd($param);
        foreach ($param as $k => $v) {

            if (is_array($v)){
                dd(2);
            }else{
                $str .= $k . $v;
            }
        }

        $sign = strtoupper(md5($client_secret. $str . $client_secret));    //  生成签名    MD5加密转大写
        return $sign;
    }
}
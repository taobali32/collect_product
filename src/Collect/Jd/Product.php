<?php


namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;
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
    protected $cache_name = 'cache_jd_min_id';

    /**
     * gets
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     */
    public function get()
    {
        $config = $this->app['config']['jd'];

        $pageIndex = $this->getCache()->has($this->cache_name) ?? 1;

        $config = [
            'v'         =>  'v2',
            'appid'     =>  $config['jing_tui_tui']['appid'],
            'appkey'    =>  $config['jing_tui_tui']['appkey'],
            'pageIndex' =>  $pageIndex,
            'pageSize'  =>  $config['product']['pageSize']
        ];

        $uri = "http://japi.jingtuitui.com/api/get_goods_list";

        $response = $this->httpGet($uri,$config);

        if ($response['return'] != 0 ){
            throw new Exception($response['result'],$response['return']);
        }

        $this->getCache()->set($this->cache_name,10,$response['result']['current_page'] + 1);

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
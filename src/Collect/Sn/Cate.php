<?php

namespace Gather\Collect\Sn;

use Gather\Kernel\BaseClient;
use GuzzleHttp\Client;

class Cate extends BaseClient
{

    /**
     * 获取苏宁分类
     * @see http://sums.suning.com/openPlatform/index.html#/home
     * @see https://open.suning.com/ospos/apipage/toApiMethodDetailMenuNew.do?interCode=suning.netalliance.commoditycategory.query
     * @return array
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function get($param = [])
    {
        $config = $this->app['config']['sn'];

        $c = new \OpenSDK\Suning\Client();

        $c->appKey = $config['AppKey'];
        $c->appSecret = $config['AppSecret'];


        $req = new \OpenSDK\Suning\Requests\Netalliance\CommoditycategoryQueryRequest();

        $z = new \OpenSDK\Suning\Params\Netalliance\CommoditycategoryItem();
        $z->setGrade(1);
        $z->setParentId(1);
        $req->setCommodityCategoryList($z);

        $c->setRequest($req);
        $response = $c->execute();

        if ($this->app['config']['original_data']){
            return $response;
        }

        $arr = [];

        foreach ($response['sn_responseContent']['sn_body']['queryCommoditycategory']['resultList'][0]['catalogList'] as $item => $value) {
            $arr[$value['childCategoryCode']] = $value['childCategoryName'];
        }
        
        return $arr;
    }
}
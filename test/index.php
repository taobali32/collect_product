<?php

require '../vendor/autoload.php';

use Gather\Factory;


$config = [
    'tk'    =>  [
        'miao_you_quan' =>  [
            'apkey'  =>  'be935b07-a84f-d9b0-c686-70c6e16b0e6d',
            'tbname' => 'lwk290367159',
            'pid'    => 'mm_1637290136_2239400333_111218850173',
        ]
    ],
];


try {
    $result = Factory::collect($config)->tk_product->productLinkId(627430191595,'2702510978');

    var_dump($result);
//    while (count($product) > 0){
//        //  写入数据库...
//        $product = Factory::collect($config)->tk_product->new_products(['start' => 0, 'end' => 23]);
//
//
//    }

}catch (Exception $exception){
    //  打印日志
    $exception->getMessage();
}


//$config = [
//    'tk'    =>  [
//        'hot_key_word_cache_time'   =>  100,
//        'hao_dan_ku'  =>  [
//            'api_key'   =>  '84C2C33F8C1F',
//        ],
//        'product'   =>  [
//            'back'  =>  500,
//            'item_type' => 0
//        ],
//    ],
//
//];
//
//try {
//    $product = Factory::collect($config)->tk_product->new_products(['start' => 0, 'end' => 23]);
//
//    var_dump($product);
////    while (count($product) > 0){
////        //  写入数据库...
////        $product = Factory::collect($config)->tk_product->new_products(['start' => 0, 'end' => 23]);
////
////
////    }
//
//}catch (Exception $exception){
//    //  打印日志
//    $exception->getMessage();
//}





//$config = [
//    'tk'    =>  [
//        'miao_you_quan' =>  [
//            'apkey'     =>  'be935b07-a84f-d9b0-c686-70c6e16b0e6d',
//            'tbname'    => 'lwk290367159'
//        ]
//    ]
//];
//
////$code = 'CK77IQ';
////$rtag = 'user_18888888888';
//
////$result = Factory::collect($config)->tk_auth->web($code,$rtag);
//
//$result = Factory::collect($config)->tk_auth->getTbkPublisherInfo();
//
//var_dump($result);
//

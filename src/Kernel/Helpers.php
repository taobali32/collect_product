<?php

namespace Gather\Kernel;

if (!function_exists('array_to_json')){

    function array_to_json($arr){
        return json_encode($arr,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('json_to_array')){
    function json_to_array($json){
        return  json_decode($json,true);
    }
}

/**
 * 组合参数 /
 */
if (!function_exists('comb')){
    /**
     * comb
     * @param array $param
     * @return string
     */
    function comb($param){
        $str = '';
        foreach ($param as $item => $value){
            $str .= "{$item}/{$value}/";
        }

        return trim($str,'/');
    }
}


if (!function_exists('vdd')){
    function vdd($param){
        echo '<pre>';
        print_r($param);
        die;
    }
}

if (!function_exists('build_request_param'))
{

    function build_request_param($baseUrl, $params)
    {
        if(empty($params))
            return $baseUrl;

        $baseUrl .= "?";
        $demo = 0;
        foreach($params as $k=>$v)
        {
            if($demo==0)
            {
                $baseUrl .= "{$k}={$v}";
            }
            else
            {
                $baseUrl .= "&{$k}={$v}";
            }
            $demo++;
        }
        return trim($baseUrl, "&");
    }
}



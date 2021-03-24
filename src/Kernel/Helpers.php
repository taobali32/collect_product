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

if (!function_exists('vdd')){
    function vdd($param){
        echo '<pre>';
        print_r($param);
        die;
    }
}



<?php

namespace Gather\Collect\Tk;

use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;
use Psr\Cache\InvalidArgumentException;

/**
 * Class HotKeyword
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Tk
 */
class HotKeyword extends BaseClient
{
    use InteractsWithCache;

    /**
     * @var string
     */
    protected $cache_name = 'TkHotKeyword';

    /**
     * Get hot key words.
     *
     * @return array|mixed
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Exception
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get()
    {
        $config = $this->app['config']['tk'];

        $url = "http://v2.api.haodanku.com/hot_key/apikey/{$config['hao_dan_ku']['api_key']}";

        $arr = [];

        if ($config['hot_key_word_cache_time'] != 0 ){

            $arr = $this->getCache()->get($this->cache_name);

            if (!empty($arr)){
                return $arr;
            }
        }

        $response = $this->httpGet($url);

        if ($response['code'] == 1 && $response['msg'] == 'SUCCESS'){

            foreach ($response['data'] as $item => $value){
                $arr[$item] = $value['keyword'];
            }

            $this->getCache()->set($this->cache_name,$arr,$config['hot_key_word_cache_time'] * 60);
            return $arr;
        }

        throw new Exception($response['msg'],$response['code']);
    }

    /**
     * clear
     *
     * @return bool
     *
     * @throws \Gather\Kernel\Exceptions\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function clear()
    {
        $this->getCache()->deleteItem($this->cache_name);
    }
}
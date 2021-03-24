<?php

namespace Gather\Collect\Jd;


use Gather\Kernel\BaseClient;
use Gather\Kernel\Exceptions\Exception;
use Gather\Kernel\Traits\InteractsWithCache;
use Psr\Cache\InvalidArgumentException;

/**
 * Class HotKeyword
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Collect\Jd
 */
class HotKeyword extends BaseClient
{
    use InteractsWithCache;
    /**
     * @var string
     */
    protected $cache_name = 'JdHotKeyword';

    /**
     * Get hot key words.
     *
     * @return array|mixed
     *
     * @throws Exception
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get()
    {
        $config = $this->app['config']['jd'];

        $uri = "http://japi.jingtuitui.com/api/hot_search?appid={$config['jing_tui_tui']['appid']}&appkey={$config['jing_tui_tui']['appkey']}";

        if ($config['hot_key_word_cache_time'] != 0 ){

            $arr = $this->getCache()->get($this->cache_name);

            if (!empty($arr)){
                return $arr;
            }
        }

        $response = $this->httpPost($uri);

        if ( $response['return'] == 0 ){

            $arr = $response['result']['hotWords'];

            $this->getCache()->set($this->cache_name,$arr,$config['hot_key_word_cache_time'] * 60);

            return $arr;
        }

        throw new Exception($response['result'],$response['return']);
    }

    /**
     * clear
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function clear()
    {
        $this->getCache()->deleteItem($this->cache_name);
    }
}
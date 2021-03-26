<?php

namespace Gather\Kernel;

use Gather\Kernel\Providers\ConfigServiceProvider;
use Gather\Kernel\Providers\HttpClientServiceProvider;
use Gather\Kernel\Providers\LogServiceProvider;
use Pimple\Container;

/**
 * Class ServiceContainer
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel
 */
class ServiceContainer extends Container
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $defaultConfig = [];

    /**
     * Constructor.
     *
     * @param array       $config
     * @param array       $prepends
     * @param string|null $id
     */
    public function __construct(array $config = [], array $prepends = [])
    {
        $this->config = $config;

        parent::__construct($prepends);

        $this->registerProviders($this->getProviders());
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $base = [
            // http://docs.guzzlephp.org/en/stable/request-options.html
            'http' => [
                'timeout' => 30.0,
                'base_uri' => 'http://getConfig.com/',
            ],
            'log' => [
                'default' => 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
                'channels' => [
                    // 测试环境
                    'dev' => [
                        'driver' => 'single',
                        'path' => './gather_jtar_dev.log',
                        'level' => 'debug',
                    ],
                    // 生产环境
                    'prod' => [
                        'driver' => 'daily',
                        'path' => './gather_jtar_prod.log',
                        'level' => 'info',
                    ],
                ],
            ],
            'email'    =>  [
                'Host'        =>  'smtp.163.com',
                'SMTPAuth'    =>  true,
                'Username'    =>  '',
                'Password'    =>  '',
                'Port'        =>  '465',
                'SMTPDebug'   =>  false,
                'ErrorLog'    =>  './email.log',
                'IsInterval'  =>  false,
                'IntervalTime'    =>  30,
                'IntervalName' => 'exceptions',
                'IsEx'        =>  false
            ],
            'original_data' =>  false,
            'tk'    =>  [
                'hot_key_word_cache_time'   =>  0,
                'hao_dan_ku'  =>  [
                    'api_key'   =>  '',
                ],
                'product'   =>  [
                    'sort'  =>  1,
                    'back'  =>  100,
                ],
                'auth'  =>  [
                    'invite_code'  =>  [
                        'relationapp'   =>  'common',
                        'codetype'      =>  1
                    ]
                ],
                'miao_you_quan' =>  [
                    'apkey'     =>  '',
                    'tbname'    =>  ''
                ],
                'order'     =>  [
                    'query_type'    =>  1,
                    'order_scene'   =>  2,
                    'start_time' => date('Y-m-d H:i:s', strtotime("-10 minute")),
                    'end_time' => date('Y-m-d H:i:s'),
                    'page_size' => 100,
                    'page_no'   =>  1
                ]
            ],
            'jd'    =>  [
                'hot_key_word_cache_time'   =>  0,
                'jing_tui_tui'  =>  [
                    'appid'     =>  '',
                    'appkey'    =>  ''
                ],
                'product'   =>  [
                    'pageSize'  =>  100,
                ]
            ]
        ];
        return array_replace_recursive($base, $this->config,[]);
    }

    /**
     * Magic get access.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Magic set access.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * @param string $id
     * @param mixed  $value
     */
    public function rebind($id, $value)
    {
        $this->offsetUnset($id);
        $this->offsetSet($id, $value);
    }

    /**
     * Return all providers.
     *
     * @return array
     */
    public function getProviders()
    {
        return array_merge([
            ConfigServiceProvider::class,
            HttpClientServiceProvider::class,
            LogServiceProvider::class,
        ],$this->providers);
    }

    /**
     * @param array $providers
     */
    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider());
        }
    }
}
<?php

namespace Gather\Collect;

use Gather\Kernel\ServiceContainer;

/**
 * Class Application
 *
 * @property \Gather\Collect\Tk\HotKeyword                        $tk_hot_keyword
 * @property \Gather\Collect\Tk\Order                             $tk_order
 * @property \Gather\Collect\Tk\Product                           $tk_product
 * @property \Gather\Collect\Tk\Cate                              $tk_cate
 * @property \Gather\Collect\Tk\Auth                              $tk_auth
 *
 * @property \Gather\Collect\Jd\Cate                              $jd_cate
 * @property \Gather\Collect\Jd\Order                             $jd_order
 * @property \Gather\Collect\Jd\HotKeyword                        $jd_hot_keyword
 * @property \Gather\Collect\Jd\Product                           $jd_product
 * @property \Gather\Collect\Jd\Auth                              $jd_auth

 * @property \Gather\Collect\Pdd\Cate                              $pdd_cate
 * @property \Gather\Collect\Pdd\Product                           $pdd_product
 * @property \Gather\Collect\Pdd\Auth                              $pdd_auth
 * @package Gather\Collect
 */
class Application extends ServiceContainer
{
    /**
     * @var array
     */
    protected $providers = [
        \Gather\Collect\Tk\ServiceProvider::class,
        \Gather\Collect\Jd\ServiceProvider::class,
        \Gather\Collect\Pdd\ServiceProvider::class,
    ];

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this['base'],$name],$arguments);
    }
}
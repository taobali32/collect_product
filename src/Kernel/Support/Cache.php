<?php


namespace Gather\Kernel\Support;


use Gather\Kernel\Exceptions\Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class Cache
 * @auther: jtar <3196672779@qq.com>
 * Time: 2020/11/10 19:00
 * @package Gather\Kernel\Support
 */
class Cache
{
    protected $dir = 'cache';

    protected $time = 0;

    protected $cache;

    /**
     * get
     * @param string $key
     * @return array|mixed
     * @throws Exception
     */
    protected function get($key = '')
    {
        try {
            $get = $this->cache->getItem($key);

            if (!$get->isHit()) {
                return [];
            }

            return $get->get();

        } catch (InvalidArgumentException $e) {

            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    /**
     * set
     * @param $cacheAt
     * @param $data
     * @param $key
     * @return void
     * @throws InvalidArgumentException
     */
    protected  function set($cacheAt, $data,$key)
    {
        if ($cacheAt != 0){
            $get = $this->cache->getItem($key);

            $get->set($data);
            $get->expiresAfter($cacheAt);

            $this->cache->save($get);
        }
    }

    /**
     * remove
     * @param $key
     * @return bool
     * @throws InvalidArgumentException
     */
    protected  function remove($key)
    {
        return $this->cache->deleteItem($key);
    }

    /**
     * has
     * @param $key
     * @return bool
     * @throws InvalidArgumentException
     */
    protected  function has($key)
    {
        return $this->cache->getItem($key)->isHit();
    }

    protected function __construct()
    {
        if (empty($this->cache)){
            $this->cache = new FilesystemAdapter('', $this->time, $this->dir);
        }
    }

    protected function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->{$method}(...$parameters);
    }

    /**
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return (new self())->{$method}(...$parameters);
    }

}
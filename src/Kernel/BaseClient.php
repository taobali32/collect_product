<?php

namespace Gather\Kernel;


use Gather\Kernel\Traits\HasHttpRequests;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LogLevel;

/**
 * Class BaseClient
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var \Gather\Kernel\ServiceContainer
     */
    protected $app;

    /**
     * @var string
     */
    protected $baseUri;

    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\Gather\Kernel\Support\Collection|array|object|string
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet( $url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * httpPost
     * @param $url
     * @param array $data
     * @return array|Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost($url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * httpPostJson
     * @param $url
     * @param array $data
     * @param array $query
     * @return array|Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPostJson( $url, array $data = [], array $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    public function httpUpload( $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request(
            $url,
            'POST',
            ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30]
        );
    }

    public function request($url,  $method = 'GET', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $response = $this->performRequest($url, $method, $options);

//        $this->app->events->dispatch(new Events\HttpResponseCreated($response));

        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        $this->pushMiddleware($this->logMiddleware(), 'log');
    }

    /**
     * Log the request. MessageFormatter
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        $formatter = new MessageFormatter($this->app['config']['http.log_template'] ?? MessageFormatter::DEBUG);

        return Middleware::log($this->app['logger'], $formatter, LogLevel::DEBUG);
    }
    /**
     * 签名
     * @param $param
     * @param $secret
     * @return string
     */
    public function pddSign($param,$secret)
    {
        $param = func_get_args()[0];

        ksort($param);    //  排序

        $client_secret = func_get_args()[1];

        $str = '';      //  拼接的字符串

        if (is_array($param)){
            foreach ($param as $k => $v) {
                $str .= $k . $v;
            }
        }else{
            $str .= $param;
        }

        $sign = strtoupper(md5($client_secret. $str . $client_secret));    //  生成签名    MD5加密转大写
        return $sign;
    }
}
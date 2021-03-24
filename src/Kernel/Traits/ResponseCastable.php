<?php

namespace Gather\Kernel\Traits;

use Gather\Kernel\Contracts\Arrayable;
use Gather\Kernel\Http\Response;
use Pimple\Exception\InvalidServiceIdentifierException;
use Psr\Http\Message\ResponseInterface;
use function Gather\Kernel\vdd;

/**
 * Trait ResponseCastable
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel\Traits
 */
trait ResponseCastable
{
    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string|null                         $type
     *
     * @return array|\Gather\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     *
     * @throws \Gather\Kernel\Exceptions\InvalidConfigException ResponseInterface
     */
    protected function castResponseToType(ResponseInterface $response, $type = null)
    {
        $response = Response::buildFromPsrResponse($response);

        $response->getBody()->rewind();

        switch (!empty($type) ? $type : 'array') {
            case 'collection':
                return $response->toCollection();
            case 'array':
                return $response->toArray();
            case 'object':
                return $response->toObject();
            case 'raw':
                return $response;
            default:
                if (!is_subclass_of($type, Arrayable::class)) {
                    throw new InvalidServiceIdentifierException(sprintf('Config key "response_type" classname must be an instanceof %s', Arrayable::class));
                }

                return new $type($response);
        }
    }
}
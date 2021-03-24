<?php

namespace Gather\Kernel\Contracts;

use ArrayAccess;

/**
 * Interface Arrayable
 * @auther: jtar <3196672779@qq.com>
 * @package Gather\Kernel\Contracts
 */
interface Arrayable extends ArrayAccess
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
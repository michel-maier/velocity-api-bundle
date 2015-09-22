<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

/**
 * RedisAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait RedisAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected abstract function setService($key, $service);
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected abstract function getService($key);
    /**
     * @param \Redis $service
     *
     * @return $this
     */
    public function setRedis(\Redis $service)
    {
        return $this->setService('redis', $service);
    }
    /**
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->getService('redis');
    }
}

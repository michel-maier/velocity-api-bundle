<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Storage;

use Velocity\Bundle\ApiBundle\StorageInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\RedisAwareTrait;

/**
 * Redis Storage
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class RedisStorage implements StorageInterface
{
    use ServiceTrait;
    use RedisAwareTrait;
    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->setRedis($redis);
    }
    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     */
    public function set($key, $value, $options = [])
    {
        $realKey         = $this->formatKey($key);
        $serializedValue = $this->serializeValue($value);

        if (!isset($options['ttl'])) {
            $this->getRedis()->set($realKey, $serializedValue);
        } else {
            $this->getRedis()->setex($realKey, (int) $options['ttl'], $serializedValue);
        }

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return $this
     */
    public function clear($key, $options = [])
    {
        unset($options);

        $realKey = $this->formatKey($key);

        $this->getRedis()->del($realKey);

        return $this;
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return string
     *
     * @throws \Exception
     */
    public function get($key, $options = [])
    {
        return $this->unserializeValue($this->getRedis()->get($this->formatKey($key)));
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return bool
     */
    public function has($key, $options = [])
    {
        return $this->getRedis()->exists($this->formatKey($key));
    }
    /**
     * @param string $key
     *
     * @return string
     */
    protected function formatKey($key)
    {
        return $key;
    }
    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function serializeValue($value)
    {
        return serialize($value);
    }
    /**
     * @param mixed $serialized
     *
     * @return mixed
     */
    protected function unserializeValue($serialized)
    {
        if (!$serialized) {
            return null;
        }

        return unserialize($serialized);
    }
}

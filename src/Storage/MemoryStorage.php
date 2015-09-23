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
use Velocity\Core\Traits\ServiceTrait;

/**
 * Local File Storage
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class MemoryStorage implements StorageInterface
{
    use ServiceTrait;
    /**
     * @param array $objects
     */
    public function __construct(array $objects = [])
    {
        $this->setParameter('objects', $objects);
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
        unset($options);

        return $this->setArrayParameterKey('objects', md5($key), $value);
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

        return $this->unsetArrayParameterKey('objects', md5($key));
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
        unset($options);

        return $this->getArrayParameterKey('objects', md5($key));
    }
    /**
     * @param string $key
     * @param array  $options
     *
     * @return bool
     */
    public function has($key, $options = [])
    {
        unset($options);

        return $this->hasArrayParameterKey('objects', md5($key));
    }
}

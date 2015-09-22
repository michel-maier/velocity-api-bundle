<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle;

/**
 * Storage Interface.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
interface StorageInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     * @param array  $options
     *
     * @return $this
     */
    public function set($key, $value, $options = []);
    /**
     * @param string $key
     * @param array  $options
     *
     * @return $this
     */
    public function clear($key, $options = []);
    /**
     * @param string $key
     * @param array  $options
     *
     * @return mixed
     */
    public function get($key, $options = []);
    /**
     * @param string $key
     * @param array  $options
     *
     * @return mixed
     */
    public function has($key, $options = []);
}

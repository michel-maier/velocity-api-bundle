<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ParameterAware;

/**
 * Environment ParameterAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait EnvironmentParameterAwareTrait
{
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected abstract function setParameter($key, $value);
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected abstract function getParameter($key, $default = null);
    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getParameter('environment');
    }
    /**
     * @param string $environment
     *
     * @return $this
     */
    public function setEnvironment($environment)
    {
        return $this->setParameter('environment', $environment);
    }
}

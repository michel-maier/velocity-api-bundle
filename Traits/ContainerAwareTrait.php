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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Container Aware Trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ContainerAwareTrait
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
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        return $this->setService('container', $container);
    }
    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->getService('container');
    }
}
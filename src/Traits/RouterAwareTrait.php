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

use Symfony\Component\Routing\Router;

/**
 * RouterAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait RouterAwareTrait
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
     * @param Router $router
     *
     * @return $this
     */
    public function setRouter(Router $router)
    {
        return $this->setService('router', $router);
    }
    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->getService('router');
    }
}

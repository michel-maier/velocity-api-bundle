<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\ServiceAware;

use Velocity\Bundle\ApiBundle\Service\CallableService;

/**
 * CallableServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait CallableServiceAwareTrait
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
     * @return CallableService
     */
    public function getCallableService()
    {
        return $this->getService('callable');
    }
    /**
     * @param CallableService $service
     *
     * @return $this
     */
    public function setCallableService(CallableService $service)
    {
        return $this->setService('callable', $service);
    }
}

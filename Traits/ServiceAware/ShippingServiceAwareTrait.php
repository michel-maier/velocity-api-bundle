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

use Velocity\Bundle\ApiBundle\Service\ShippingService;

/**
 * ShippingServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ShippingServiceAwareTrait
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
     * @param ShippingService $service
     *
     * @return $this
     */
    public function setShippingService(ShippingService $service)
    {
        return $this->setService('shipping', $service);
    }
    /**
     * @return ShippingService
     */
    public function getShippingService()
    {
        return $this->getService('shipping');
    }
}

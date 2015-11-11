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

use Velocity\Bundle\ApiBundle\Service\StripeService;

/**
 * StripeServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait StripeServiceAwareTrait
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
     * @return StripeService
     */
    public function getStripeService()
    {
        return $this->getService('stripe');
    }
    /**
     * @param StripeService $service
     *
     * @return $this
     */
    public function setStripeService(StripeService $service)
    {
        return $this->setService('stripe', $service);
    }
}

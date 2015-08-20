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

use Velocity\Bundle\ApiBundle\Service\PayPalService;

/**
 * PayPalServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait PayPalServiceAwareTrait
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
     * @return PayPalService
     */
    public function getPayPalService()
    {
        return $this->getService('payPal');
    }
    /**
     * @param PayPalService $service
     *
     * @return $this
     */
    public function setPayPalService(PayPalService $service)
    {
        return $this->setService('payPal', $service);
    }
}
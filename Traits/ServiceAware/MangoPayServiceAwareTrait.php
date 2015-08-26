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

use Velocity\Bundle\ApiBundle\Service\MangoPayService;

/**
 * MangoPayServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait MangoPayServiceAwareTrait
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
     * @return MangoPayService
     */
    public function getMangoPayService()
    {
        return $this->getService('mangoPay');
    }
    /**
     * @param MangoPayService $service
     *
     * @return $this
     */
    public function setMangoPayService(MangoPayService $service)
    {
        return $this->setService('mangoPay', $service);
    }
}

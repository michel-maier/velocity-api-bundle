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

use MangoPay\MangoPayApi;

/**
 * MangoPayApiAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait MangoPayApiAwareTrait
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
     * @param MangoPayApi $mangoPayApi
     *
     * @return $this
     */
    public function setMangoPayApi(MangoPayApi $mangoPayApi)
    {
        return $this->setService('mangoPayApi', $mangoPayApi);
    }
    /**
     * @return MangoPayApi
     */
    public function getMangoPayApi()
    {
        return $this->getService('mangoPayApi');
    }
}

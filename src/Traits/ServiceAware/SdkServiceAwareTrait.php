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

use Velocity\Bundle\ApiBundle\Service\SdkService;

/**
 * SdkServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait SdkServiceAwareTrait
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
     * @return SdkService
     */
    public function getSdkService()
    {
        return $this->getService('sdk');
    }
    /**
     * @param SdkService $service
     *
     * @return $this
     */
    public function setSdkService(SdkService $service)
    {
        return $this->setService('sdk', $service);
    }
}

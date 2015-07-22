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

use Velocity\Bundle\ApiBundle\Service\RequestService;

/**
 * RequestServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait RequestServiceAwareTrait
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
     * @param RequestService $service
     *
     * @return $this
     */
    public function setRequestService(RequestService $service)
    {
        return $this->setService('request', $service);
    }
    /**
     * @return RequestService
     */
    public function getRequestService()
    {
        return $this->getService('request');
    }
}
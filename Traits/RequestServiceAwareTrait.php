<?php

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Bundle\ApiBundle\Service\RequestService;

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
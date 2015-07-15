<?php

namespace Velocity\Bundle\ApiBundle\Traits;

use Velocity\Bundle\ApiBundle\Service\FormService;

trait FormServiceAwareTrait
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
     * @param FormService $service
     *
     * @return $this
     */
    public function setFormService(FormService $service)
    {
        return $this->setService('form', $service);
    }
    /**
     * @return FormService
     */
    public function getFormService()
    {
        return $this->getService('form');
    }
}
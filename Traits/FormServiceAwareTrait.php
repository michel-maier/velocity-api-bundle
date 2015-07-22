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

use Velocity\Bundle\ApiBundle\Service\FormService;

/**
 * FormServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
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
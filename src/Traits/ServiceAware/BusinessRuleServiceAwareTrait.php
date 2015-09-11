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

use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * BusinessRuleServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait BusinessRuleServiceAwareTrait
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
     * @return BusinessRuleService
     */
    public function getBusinessRuleService()
    {
        return $this->getService('businessRule');
    }
    /**
     * @param BusinessRuleService $service
     *
     * @return $this
     */
    public function setBusinessRuleService(BusinessRuleService $service)
    {
        return $this->setService('businessRule', $service);
    }
}

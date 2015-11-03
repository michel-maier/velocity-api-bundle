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

use Velocity\Bundle\ApiBundle\Service\WorkflowService;

/**
 * WorkflowServiceAware trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait WorkflowServiceAwareTrait
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
     * @return WorkflowService
     */
    public function getWorkflowService()
    {
        return $this->getService('workflow');
    }
    /**
     * @param WorkflowService $service
     *
     * @return $this
     */
    public function setWorkflowService(WorkflowService $service)
    {
        return $this->setService('workflow', $service);
    }
}

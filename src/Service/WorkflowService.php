<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Workflow;
use Velocity\Bundle\ApiBundle\WorkflowInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * Workflow Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class WorkflowService
{
    use ServiceTrait;
    use ServiceAware\BusinessRuleServiceAwareTrait;
    /**
     * @param BusinessRuleService $businessRule
     */
    public function __construct(BusinessRuleService $businessRule)
    {
        $this->setBusinessRuleService($businessRule);
    }
    /**
     * @param string $id
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->hasArrayParameterKey('workflows', $id);
    }
    /**
     * @param string $id
     *
     * @return WorkflowInterface
     */
    public function get($id)
    {
        return $this->getArrayParameterKey('workflows', $id);
    }
    /**
     * @param string $id
     * @param array  $definition
     *
     * @return $this
     */
    public function registerFromDefinition($id, array $definition)
    {
        return $this->register($id, new Workflow($definition));
    }
    /**
     * @param string            $id
     * @param WorkflowInterface $workflow
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($id, WorkflowInterface $workflow)
    {
        return $this->setArrayParameterKey('workflows', $id, $workflow);
    }
    /**
     * @param string $id
     * @param string $currentStep
     * @param string $targetStep
     *
     * @return bool
     */
    public function hasTransition($id, $currentStep, $targetStep)
    {
        $workflow = $this->get($id);

        return $workflow->hasTransition($currentStep, $targetStep);
    }
    /**
     * @param string $id
     * @param string $currentStep
     * @param string $targetStep
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function checkTransitionExist($id, $currentStep, $targetStep)
    {
        if (!$this->hasTransition($id, $currentStep, $targetStep)) {
            throw $this->createRequiredException("No transitions from step '%s' to step '%s' in workflow '%s'", $currentStep, $targetStep, $id);
        }

        return $this;
    }
    public function transitionModelProperty($model, $property, $currentStep, $targetStep, $id)
    {
        $this->checkTransitionExist($id, $currentStep, $targetStep);

        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation();
    }
}

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
            if ($currentStep === $targetStep) {
                throw $this->createRequiredException("Already %s", $targetStep);
            }

            throw $this->createRequiredException("Transitionning to %s is not allowed", $targetStep);
        }

        return $this;
    }
    /**
     * @param string $modelName
     * @param mixed  $model
     * @param string $property
     * @param mixed  $previousModel
     * @param string $id
     * @param array  $options
     *
     * @return array
     *
     * @throws \Exception
     */
    public function transitionModelProperty($modelName, $model, $property, $previousModel, $id, array $options = [])
    {
        $this->checkTransitionExist($id, $previousModel->$property, $model->$property);

        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation($modelName, $property.'.'.$previousModel->$property.'.leaved', $previousModel, $options);
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation($modelName, $property.'.'.$model->$property.'.entered', $model, $options);

        $workflow = $this->get($id);

        foreach ($workflow->getTransitionAliases($previousModel->$property.'->'.$model->$property) as $alias) {
            $this->getBusinessRuleService()->executeBusinessRulesForModelOperation($modelName, $this->replaceVariables($alias, (array) $previousModel), $model, $options + ['old' => $previousModel]);
        }

        return [
            $property.'.'.$previousModel->$property.'.leaved',
            $property.'.'.$model->$property.'.entered',
        ];
    }
    /**
     * @param string $value
     * @param array  $vars
     *
     * @return string
     */
    protected function replaceVariables($value, $vars = [])
    {
        $matches = null;

        if (0 >= preg_match_all('/\{([^\}]+)\}/', $value, $matches)) {
            return $value;
        }

        foreach ($matches[1] as $i => $match) {
            $value = str_replace($matches[0][$i], isset($vars[$match]) ? $vars[$match] : null, $value);
        }

        return $value;
    }
}

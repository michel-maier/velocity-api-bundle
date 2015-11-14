<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits\SubDocument;

use Velocity\Bundle\ApiBundle\Event;
use Velocity\Bundle\ApiBundle\Service\MetaDataService;
use Velocity\Bundle\ApiBundle\Service\WorkflowService;
use Velocity\Bundle\ApiBundle\Service\BusinessRuleService;

/**
 * Helper trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait HelperTrait
{
    /**
     * @return int
     */
    public function getExpectedTypeCount()
    {
        return 2;
    }
    /**
     * @return MetaDataService
     */
    public abstract function getMetaDataService();
    /**
     * @return BusinessRuleService
     */
    public abstract function getBusinessRuleService();
    /**
     * @return WorkflowService
     */
    public abstract function getWorkflowService();
    /**
     * @return string
     */
    protected abstract function getModelName();
    /**
     * Build the full event name.
     *
     * @param string $event
     *
     * @return string
     */
    protected abstract function buildEventName($event);
    /**
     * @param string $event
     * @param null   $data
     *
     * @return $this
     */
    protected abstract function dispatch($event, $data = null);
    /**
     * Trigger the specified document event if listener are registered.
     *
     * @param mixed  $parentId
     * @param string $event
     * @param mixed  $data
     *
     * @return $this
     */
    protected function event($parentId, $event, $data = null)
    {
        return $this->dispatch(
            $this->buildEventName($event),
            new Event\DocumentEvent($data, $this->buildTypeVars([$parentId]))
        );
    }
    /**
     * Execute the registered callback and return the updated subject.
     *
     * @param mixed  $parentId
     * @param string $key
     * @param mixed  $subject
     * @param array  $options
     *
     * @return mixed
     */
    protected function callback($parentId, $key, $subject = null, $options = [])
    {
        return $this->getMetaDataService()->callback(
            $this->buildEventName($key),
            $subject,
            $this->buildTypeVars([$parentId]) + $options
        );
    }
    /**
     * @param string $parentId
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function applyBusinessRules($parentId, $operation, $model, array $options = [])
    {
        $this->getBusinessRuleService()->executeBusinessRulesForModelOperation(
            $this->getModelName(),
            $operation,
            $model,
            $this->buildTypeVars([$parentId]) + $options
        );

        return $this;
    }
    /**
     * @param string $parentId
     * @param mixed  $model
     * @param array  $options
     *
     * @return bool
     */
    protected function hasActiveWorkflows($parentId, $model, array $options = [])
    {
        foreach ($this->getMetaDataService()->getModelWorkflows($model) as $property => $definition) {
            if (!isset($model->$property)) {
                continue;
            }

            return true;
        }

        unset($parentId, $options);

        return false;
    }
    /**
     * @param string $parentId
     * @param mixed  $model
     * @param array  $options
     *
     * @return bool
     */
    protected function getActiveWorkflowsRequiredFields($parentId, $model, array $options = [])
    {
        $requiredFields = [];

        foreach ($this->getMetaDataService()->getModelWorkflows($model) as $property => $definition) {
            if (!isset($model->$property)) {
                continue;
            }

            if (isset($definition['requiredFields'])) {
                if (!is_array($definition['requiredFields'])) {
                    $definition['requiredFields'] = [$definition['requiredFields']];
                }
                $requiredFields = array_merge($requiredFields, $definition['requiredFields']);
            }
        }

        $requiredFields = array_unique($requiredFields);

        sort($requiredFields);

        unset($options, $parentId);

        return $requiredFields;
    }
    /**
     * @param string $parentId
     * @param mixed  $model
     * @param mixed  $previousModel
     * @param array  $options
     *
     * @return array
     */
    protected function applyActiveWorkflows($parentId, $model, $previousModel, array $options = [])
    {
        $transitions = [];

        foreach ($this->getMetaDataService()->getModelWorkflows($model) as $property => $definition) {
            if (!isset($model->$property)) {
                continue;
            }
            $transitions = array_merge(
                $transitions,
                $this->getWorkflowService()->transitionModelProperty(
                    $this->getModelName(),
                    $model,
                    $property,
                    $previousModel,
                    $definition['id'],
                    $this->buildTypeVars([$parentId]) + $options
                )
            );
        }

        return $transitions;
    }
    /**
     * @param array $values
     * @param array $options
     *
     * @return array
     */
    protected abstract function buildTypeVars($values, $options = []);
}

<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source id.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Exception;
use Velocity\Bundle\ApiBundle\Exception\BusinessRuleException;
use Velocity\Bundle\ApiBundle\Exception\NamedBusinessRuleException;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;

/**
 * Business Rule Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class BusinessRuleService
{
    use ServiceTrait;
    /**
     * List of business rules.
     *
     * @var callable[]
     */
    protected $businessRules = ['models' => [], 'ids'];
    /**
     * Return the list of registered business rules.
     *
     * @return callable[]
     */
    public function getBusinessRules()
    {
        return $this->businessRules;
    }
    /**
     * @param string $modelName
     *
     * @return array
     */
    public function getModelBusinessRules($modelName)
    {
        return isset($this->businessRules['models'][$modelName]) ? $this->businessRules['models'][$modelName]  :[];
    }
    /**
     * @param string $modelName
     * @param string $operation
     *
     * @return array
     */
    public function getModelOperationBusinessRules($modelName, $operation)
    {
        return isset($this->businessRules['models'][$modelName][$operation]) ? $this->businessRules['models'][$modelName][$operation]  :[];
    }
    /**
     * @param string $id
     *
     * @return array
     *
     * @throws Exception
     */
    public function getBusinessRuleById($id)
    {
        if (!isset($this->businessRules['ids'][$id])) {
            throw $this->createNotFoundException("Unknown business rule '%s'", $id);
        }

        return $this->businessRules['ids'][$id];
    }
    /**
     * Register an event action for the specified name (replace if exist).
     *
     * @param string   $id
     * @param string   $name
     * @param callable $callable
     * @param array    $params
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($id, $name, $callable, array $params = [])
    {
        if (!is_callable($callable)) {
            throw $this->createUnexpectedException("Registered business rule must be a callable for '%s'", $id);
        }

        if (isset($params['model'])) {
            $model = $params['model'];
            unset($params['model']);
            $params += ['operation' => '*'];
            $operation = $params['operation'];
            unset($params['operation']);
            if (!isset($this->businessRules['models'][$model])) {
                $this->businessRules['models'][$model] = [];
            }
            if (!isset($this->businessRules['models'][$model][$operation])) {
                $this->businessRules['models'][$model][$operation] = [];
            }
            $this->businessRules['models'][$model][$operation][$id] = ['callable' => $callable, 'params' => $params, 'id' => $id, 'name' => $name];

            $this->businessRules['ids'][$id] = &$this->businessRules['models'][$model][$operation][$id];

            return $this;
        }

        throw $this->createUnexpectedException("Unsupported business rule type for id '%s'", $id);
    }
    /**
     * @param string $modelName
     * @param string $operation
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    public function executeBusinessRulesForModelOperation($modelName, $operation, $model, array $options = [])
    {
        foreach ($this->getModelOperationBusinessRules($modelName, $operation) as $businessRule) {
            $this->executeBusinessRuleForModelOperation($modelName, $operation, $businessRule, $model, $options);
        }

        foreach ($this->getModelOperationBusinessRules($modelName, '*') as $businessRule) {
            $this->executeBusinessRuleForModelOperation($modelName, $operation, $businessRule, $model, $options);
        }

        foreach ($this->getModelOperationBusinessRules('*', $operation) as $businessRule) {
            $this->executeBusinessRuleForModelOperation($modelName, $operation, $businessRule, $model, $options);
        }

        foreach ($this->getModelOperationBusinessRules('*', '*') as $businessRule) {
            $this->executeBusinessRuleForModelOperation($modelName, $operation, $businessRule, $model, $options);
        }

        return $this;
    }
    /**
     * @param string $id
     * @param array  $params
     * @param array  $options
     *
     * @return $this
     */
    public function executeBusinessRuleById($id, array $params = [], array $options = [])
    {
        $businessRule = $this->getBusinessRuleById($id);

        try {
            call_user_func_array($businessRule['callable'], array_merge($params, [$businessRule['params'], $options]));
        } catch (BusinessRuleException $e) {
            throw new NamedBusinessRuleException($businessRule['id'], $businessRule['name'], $e);
        }

        return $this;
    }
    /**
     * @param string $modelName
     * @param string $operation
     * @param array  $businessRule
     * @param mixed  $model
     * @param array  $options
     *
     * @return $this
     */
    protected function executeBusinessRuleForModelOperation($modelName, $operation, array $businessRule, $model, array $options = [])
    {
        try {
            call_user_func_array($businessRule['callable'], [$model, $operation, $modelName, $businessRule['params'], $options]);
        } catch (BusinessRuleException $e) {
            throw new NamedBusinessRuleException($businessRule['id'], $businessRule['name'], $e);
        }

        return $this;
    }
}

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

use Exception;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

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
    protected $businessRules = ['models' => [], 'codes'];
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
     * @param string $code
     *
     * @return array
     *
     * @throws Exception
     */
    public function getBusinessRuleByCode($code)
    {
        if (!isset($this->businessRules['codes'][$code])) {
            throw $this->createException(404, "Unknown business rule '%s'", $code);
        }

        return $this->businessRules['codes'][$code];
    }
    /**
     * Register an event action for the specified name (replace if exist).
     *
     * @param string   $code
     * @param callable $callable
     * @param array    $params
     *
     * @return $this
     */
    public function addBusinessRule($code, $callable, array $params = [])
    {
        if (!is_callable($callable)) {
            throw $this->createException(500, "Registered business rule must be a callable for '%s'", $code);
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
            $this->businessRules['models'][$model][$operation][$code] = ['callable' => $callable, 'params' => $params, 'code' => $code];

            $this->businessRules['codes'][$code] = &$this->businessRules['models'][$model][$operation][$code];

            return $this;
        }

        throw $this->createException(500, "Unsupported business rule type for code '%s'", $code);
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
        call_user_func_array($businessRule['callable'], [$model, $operation, $modelName, $businessRule['params'], $options]);

        return $this;
    }
}

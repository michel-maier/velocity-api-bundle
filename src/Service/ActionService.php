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
 * Action Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ActionService
{
    use ServiceTrait;
    /**
     * Return the list of registered actions.
     *
     * @return array
     */
    public function getActions()
    {
        return $this->getArrayParameter('actions');
    }
    /**
     * Register an action for the specified name (replace if exist).
     *
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function register($name, $callable, array $options = [])
    {
        if (!is_callable($callable)) {
            throw $this->createUnexpectedException("Registered action must be a callable for '%s'", $name);
        }

        return $this->setArrayParameterKey(
            'actions',
            $name,
            ['type' => 'action', 'callable' => $callable, 'options' => $options]
        );
    }
    /**
     * Register an action set for the specified name (replace if exist).
     *
     * @param string $name
     * @param array  $actions
     * @param array  $options
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function registerSet($name, array $actions, array $options = [])
    {
        foreach ($actions as $k => $action) {
            if (!is_array($action)) {
                $action = [];
            }
            if (!isset($action['action'])) {
                throw $this->createRequiredException("Missing action name for action #%d in set '%s'", $k, $name);
            }
            $actions[$k] = $action;
        }

        return $this->setArrayParameterKey(
            'actions',
            $name,
            ['type' => 'set', 'actions' => $actions, 'options' => $options]
        );
    }
    /**
     * Return the action registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws Exception if no action registered for this name
     */
    public function getActionByName($name)
    {
        return $this->getArrayParameterKey('actions', $name);
    }
    /**
     * @param string $name
     * @param array  $params
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function executeAction($name, array $params = [])
    {
        $action = $this->getActionByName($name);

        $params += ['ignoreOnException' => false];

        try {
            switch ($action['type']) {
                case 'action':
                    call_user_func_array($action['callable'], [$action['options']]);
                    break;
                case 'set':
                    foreach ($action['actions'] as $action) {
                        $actionName = $action['action'];
                        unset($action['action']);
                        $this->executeAction($actionName, $action);
                    }
                    break;
                default:
                    throw $this->createUnexpectedException("Unsupported action type '%s'", $action['type']);
            }
        } catch (\Exception $e) {
            if (true !== $params['ignoreOnException']) {
                throw $e;
            }
        }

        return $this;
    }
    /**
     * @param array $sequence
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function executeActionSequence(array $sequence)
    {
        $i = 0;

        foreach ($sequence as $step) {
            if (!is_array($step)) {
                $step = [];
            }

            if (!isset($step['name'])) {
                throw $this->createRequiredException('Missing event action sequence step name (step #%d)', $i);
            }

            if (!isset($step['params']) || !is_array($step['params'])) {
                $step['params'] = [];
            }

            $this->executeAction($step['name'], $step['params']);

            $i++;
        }

        return $this;
    }
}

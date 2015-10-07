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

use Velocity\Core\Bag;
use Velocity\Core\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;

/**
 * Action Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ActionService
{
    use ServiceTrait;
    use ServiceAware\CallableServiceAwareTrait;
    use ServiceAware\ExpressionServiceAwareTrait;
    /**
     * @param CallableService   $callableService
     * @param ExpressionService $expressionService
     */
    public function __construct(CallableService $callableService, ExpressionService $expressionService)
    {
        $this->setCallableService($callableService);
        $this->setExpressionService($expressionService);
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
        $this->getCallableService()->registerByType('action', $name, $callable, $options);

        return $this;
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
        $this->getCallableService()->registerSetByType('action', $name, $actions, $options);

        return $this;
    }
    /**
     * Return the action registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws \Exception if no action registered for this name
     */
    public function get($name)
    {
        return $this->getCallableService()->getByType('action', $name);
    }
    /**
     * @param string $name
     * @param Bag    $params
     * @param Bag    $context
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function execute($name, Bag $params, Bag $context)
    {
        return $this->getCallableService()->executeByType('action', $name, [$params, $context]);
    }
    /**
     * @param array $actions
     * @param Bag   $params
     * @param Bag   $context
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function executeBulk(array $actions, Bag $params, Bag $context)
    {
        $that = $this;

        return $this->getCallableService()->executeListByType(
            'action',
            $actions,
            function ($callableParams) use ($params, $context, $that) {
                $p = clone $params;
                $p->setDefault($callableParams);
                $vars = $p->all() + $context->all();

                return [new Bag($that->getExpressionService()->evaluate($p->all(), $vars)), $context];
            }
        );
    }
}

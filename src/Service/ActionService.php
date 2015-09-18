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

use Velocity\Bundle\ApiBundle\Bag;
use Symfony\Component\Templating\EngineInterface;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\TemplatingAwareTrait;

/**
 * Action Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class ActionService
{
    use ServiceTrait;
    use TemplatingAwareTrait;
    use ServiceAware\CallableServiceAwareTrait;
    /**
     * @param CallableService $callableService
     * @param EngineInterface $templating
     */
    public function __construct(CallableService $callableService, EngineInterface $templating)
    {
        $this->setCallableService($callableService);
        $this->setTemplating($templating);
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
                return [new Bag($that->replaceVars($p->all(), $vars)), $context];
            }
        );
    }
    /**
     * @param $raw
     * @param $vars
     *
     * @return mixed
     */
    protected function replaceVars($raw, &$vars)
    {
        if (is_array($raw)) {
            foreach ($raw as $k => $v) {
                unset($raw[$k]);
                $raw[$this->replaceVars($k, $vars)] = $this->replaceVars($v, $vars);
            }

            return $raw;
        }

        if (is_object($raw) || is_numeric($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            return $this->getTemplating()->render('VelocityApiBundle::expression.txt.twig', ['_expression' => $raw] + $vars);
        }

        return $raw;
    }
}

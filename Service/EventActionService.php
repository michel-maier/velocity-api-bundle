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
use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\EventAction\Context;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\EventAction\ContextAwareTrait;

/**
 * Event Action Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class EventActionService
{
    use ServiceTrait;
    use ContextAwareTrait;
    /**
     * List of event actions.
     *
     * @var callable[]
     */
    protected $eventActions = [];
    /**
     * @var array
     */
    protected $sequences = [];
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->setContext($context);
    }
    /**
     * Return the list of registered event actions.
     *
     * @return callable[]
     */
    public function getEventActions()
    {
        return $this->eventActions;
    }
    /**
     * Register an event action for the specified name (replace if exist).
     *
     * @param string   $name
     * @param callable $callable
     * @param array    $options
     *
     * @return $this
     */
    public function register($name, $callable, $options = [])
    {
        if (!is_callable($callable)) {
            throw $this->createException(500, "Registered event action must be a callable for '%s'", $name);
        }

        $this->eventActions[$name] = ['callable' => $callable, 'options' => $options];

        return $this;
    }
    /**
     * @param string $name
     *
     * @return $this
     */
    public function checkEventActionExist($name)
    {
        if (!isset($this->eventActions[$name])) {
            throw $this->createException(
                412,
                "No event action registered for '%s'",
                $name
            );
        }

        return $this;
    }
    /**
     * Return the event action registered for the specified name.
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws Exception if no event action registered for this name
     */
    public function getEventActionByName($name)
    {
        $this->checkEventActionExist($name);

        return $this->eventActions[$name];
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function executeEventAction(Event $event, $eventName, $name, array $params = [])
    {
        $eventAction = $this->getEventActionByName($name);

        $this->getContext()->setCurrentEventVariables($event, $eventName, $params);

        call_user_func_array($eventAction['callable'], [$eventAction['options']]);

        return $this;
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $sequence
     *
     * @return $this
     */
    public function executeEventActionSequence(Event $event, $eventName, array $sequence)
    {
        $i = 0;

        foreach ($sequence as $step) {
            if (!is_array($step)) {
                $step = [];
            }

            if (!isset($step['name'])) {
                throw $this->createException(500, 'Missing event action sequence step name (step #%d)', $i);
            }

            if (!isset($step['params']) || !is_array($step['params'])) {
                $step['params'] = [];
            }

            $this->executeEventAction($event, $eventName, $step['name'], $step['params']);

            $i++;
        }

        return $this;
    }
    /**
     * @param string $eventName
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function addEventAction($eventName, $name, array $params = [])
    {
        $this->checkEventActionExist($name);

        if (!isset($this->sequences[$eventName])) {
            $this->sequences[$eventName] = [];
        }

        $this->sequences[$eventName][] = ['name' => $name, 'params' => $params];

        return $this;
    }
    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getEventActionSequenceByEventName($eventName)
    {
        if (!isset($this->sequences[$eventName])) {
            return [];
        }

        return $this->sequences[$eventName];
    }
    /**
     * @param Event  $event
     * @param string $eventName
     *
     * @return $this
     */
    public function consume(Event $event, $eventName)
    {
        $this->executeEventActionSequence($event, $eventName, $this->getEventActionSequenceByEventName($eventName));
    }
}

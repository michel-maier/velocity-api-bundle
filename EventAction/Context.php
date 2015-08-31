<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\EventAction;

use Exception;
use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\Traits\ArrayizerTrait;
use Velocity\Bundle\ApiBundle\Event as VelocityEvent;

/**
 * Event Action Context
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class Context
{
    use ServiceTrait;
    use ArrayizerTrait;
    /**
     * @var array
     */
    protected $variables;
    /**
     * @param array $variables
     */
    public function __construct(array $variables = [])
    {
        $this->variables = [];
        $this->setVariables($variables);
    }
    /**
     * @return Event
     *
     * @throws Exception
     */
    public function getCurrentEvent()
    {
        if (!$this->hasVariable('event')) {
            throw $this->createException(500, 'No active event yet');
        }

        $event = $this->getRequiredVariable('event');

        if (!($event instanceof Event)) {
            throw $this->createException(500, "Context event should be a valid event (found: %s)", get_class($event));
        }

        return $event;
    }
    /**
     * @return string
     */
    public function getCurrentEventName()
    {
        return $this->getRequiredVariable('eventName');
    }
    /**
     * @param Event  $event
     * @param string $eventName
     * @param array  $params
     *
     * @return $this
     */
    public function setCurrentEventVariables(Event $event, $eventName, array $params = [])
    {
        return $this
            ->setVariable('eventName', $eventName)
            ->setVariable('event', $event)
            ->setVariables($params)
            ->setVariablesFromEvent($event)
        ;
    }
    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables + $this->variables;

        return $this;
    }
    /**
     * @return $this
     */
    public function resetVariables()
    {
        $this->variables = [];

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;

        return $this;
    }
    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasVariable($key)
    {
        return isset($this->variables[$key]);
    }
    /**
     * @param string     $key
     * @param null|mixed $defaultValue
     *
     * @return mixed
     */
    public function getVariable($key, $defaultValue = null)
    {

        return isset($this->variables[$key]) ? $this->variables[$key] : $defaultValue;
    }
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getRequiredVariable($key)
    {
        if (!$this->hasVariable($key)) {
            throw $this->createException(412, "Context variable '%s' does not exist", $key);
        }

        return $this->getVariable($key);
    }
    /**
     * @param Event $event
     *
     * @return $this
     */
    protected function setVariablesFromEvent(Event $event)
    {
        $data = [];

        if ($event instanceof VelocityEvent\DocumentEvent) {
            $data = $this->arrayize($event->getData(), 1) + $this->arrayize($event->getContext(), 1);
        } elseif ($event instanceof GenericEvent) {
            $data = $this->arrayize($event->getSubject(), 1) + $this->arrayize($event->getArguments(), 1);
        }

        foreach ($data as $k => $v) {
            $this->setVariable($k, $v);
        }

        return $this;
    }
}

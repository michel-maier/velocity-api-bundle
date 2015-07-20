<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Traits;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service trait.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
trait ServiceTrait
{
    use MissingMethodCatcherTrait;
    use TranslatedExceptionThrowerTrait;
    /**
     * @var array
     */
    protected $services = [];
    /**
     * @var array
     */
    protected $parameters = [];
    /**
     * @param string $key
     * @param mixed  $service
     *
     * @return $this
     */
    protected function setService($key, $service)
    {
        $this->services[$key] = $service;

        return $this;
    }
    /**
     * @param string $key
     *
     * @return bool
     */
    protected function hasService($key)
    {
        return (bool) isset($this->services[$key]);
    }
    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getService($key)
    {
        if (!$this->hasService($key)) {
            throw new \RuntimeException(sprintf("Service %s not set", $key), 500);
        }

        return $this->services[$key];
    }
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getParameter($key, $default = null)
    {
        $value = $this->getParameterIfExists($key, $default);

        if (null === $value) {
            throw new \RuntimeException(sprintf("Parameter %s not set", $key), 500);
        }

        return $value;
    }
    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getParameterIfExists($key, $default = null)
    {
        if (!isset($this->parameters[$key])) {
            return $default;
        }

        return $this->parameters[$key];
    }
    /**
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return $this
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        return $this->setService('eventDispatcher', $eventDispatcher);
    }
    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getService('eventDispatcher');
    }
    /**
     * @param string $event
     *
     * @return bool
     */
    protected function hasListeners($event)
    {
        return $this->getEventDispatcher()->hasListeners($event);
    }
    /**
     * @param string $event
     * @param null   $data
     *
     * @return $this
     */
    protected function dispatch($event, $data = null)
    {
        $this->getEventDispatcher()->dispatch($event, $data instanceof Event ? $data : new GenericEvent($data));

        return $this;
    }
}
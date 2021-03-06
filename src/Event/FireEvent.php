<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Sms Event.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class FireEvent extends Event
{
    /**
     * @var string
     */
    protected $eventName;
    /**
     * @var Event
     */
    protected $event;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @param string $eventName
     * @param Event  $event
     */
    public function __construct($eventName, Event $event)
    {
        $this->setEventName($eventName);
        $this->setEvent($event);

        $data  = null;

        if ($event instanceof DocumentEvent) {
            $data = $event->getData();
        } elseif ($event instanceof GenericEvent) {
            $data = $event->getSubject();
        }

        $this->setData($data);
    }
    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }
    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param string $eventName
     *
     * @return $this
     */
    protected function setEventName($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }
    /**
     * @param Event $event
     *
     * @return $this
     */
    protected function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }
    /**
     * @param mixed $data
     *
     * @return $this
     */
    protected function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}

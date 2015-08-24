<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;
use Symfony\Component\EventDispatcher\GenericEvent;
use Velocity\Bundle\ApiBundle\Event\Sms\UserSmsEvent;
use Velocity\Bundle\ApiBundle\Event\Sms\AdminSmsEvent;
use Velocity\Bundle\ApiBundle\Event\Mail\UserMailEvent;
use Velocity\Bundle\ApiBundle\Event\FireAndForgetEvent;
use Velocity\Bundle\ApiBundle\Event\Mail\AdminMailEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Event Converter Listener.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class EventConverterListener
{
    use ServiceTrait
    /**
     * Construct a new listener.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->setEventDispatcher($eventDispatcher);
    }
    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function mailUser(Event $event, $eventName)
    {
        $this->dispatch('mail', new UserMailEvent($eventName, $this->buildVarsFromEvent($event)));
    }
    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function mailAdmin(Event $event, $eventName)
    {
        $this->dispatch('mail', new AdminMailEvent($eventName, $this->buildVarsFromEvent($event)));
    }
    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function smsUser(Event $event, $eventName)
    {
        $this->dispatch('sms', new UserSmsEvent($eventName, $this->buildVarsFromEvent($event)));
    }
    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function smsAdmin(Event $event, $eventName)
    {
        $this->dispatch('sms', new AdminSmsEvent($eventName, $this->buildVarsFromEvent($event)));
    }
    /**
     * @param Event  $event
     * @param string $eventName
     */
    public function fireAndForget(Event $event, $eventName)
    {
        $this->dispatch('fireAndForget', new FireAndForgetEvent($eventName, $this->buildVarsFromEvent($event)));
    }
    /**
     * @param Event $event
     *
     * @return array
     */
    protected function buildVarsFromEvent(Event $event)
    {
        $vars = [];

        if ($event instanceof GenericEvent) {
            $vars['subject'] = $event->getSubject();
            $vars += $event->getArguments();
        }

        return $vars;
    }
}
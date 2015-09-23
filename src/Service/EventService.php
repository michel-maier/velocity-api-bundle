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
use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Event as Events;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Core\Traits\ServiceTrait;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Event Action Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class EventService
{
    use ServiceTrait;
    use ServiceAware\ActionServiceAwareTrait;
    /**
     * @param ActionService $actionService
     */
    public function __construct(ActionService $actionService)
    {
        $this->setActionService($actionService);
    }
    /**
     * @param string $eventName
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function register($eventName, $name, array $params = [])
    {
        $this->pushArrayParameterKeyItem('sequences', $eventName, ['name' => $name, 'params' => $params]);

        return $this;
    }
    /**
     * @param string $eventName
     *
     * @return array
     */
    public function getSequence($eventName)
    {
        return $this->getArrayParameterListKey('sequences', $eventName);
    }
    /**
     * @return array
     */
    public function getSequences()
    {
        return $this->getArrayParameter('sequences');
    }
    /**
     * @param Event  $event
     * @param string $eventName
     *
     * @return $this
     */
    public function consume(Event $event, $eventName)
    {
        $params  = new Bag();
        $context = new Bag(['eventName' => $eventName, 'event' => $event]);

        if ($event instanceof Events\DocumentEvent) {
            $context->set('doc', $event->getData());
        } elseif ($event instanceof GenericEvent) {
            $context->set('doc', $event->getSubject());
        }

        $this->getActionService()->executeBulk($this->getSequence($eventName), $params, $context);
    }
}

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

use Symfony\Component\EventDispatcher\Event;
use Velocity\Bundle\ApiBundle\Action\Context;
use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

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
     * @var array
     */
    protected $sequences = [];
    /**
     * @param string $eventName
     * @param string $name
     * @param array  $params
     *
     * @return $this
     */
    public function addAction($eventName, $name, array $params = [])
    {
        $this->getActionService()->checkActionExist($name);

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
    public function getActionSequence($eventName)
    {
        if (!isset($this->sequences[$eventName])) {
            return [];
        }

        return $this->sequences[$eventName];
    }
    /**
     * @return array
     */
    public function getActionSequences()
    {
        return $this->sequences;
    }
    /**
     * @param Event  $event
     * @param string $eventName
     *
     * @return $this
     */
    public function consume(Event $event, $eventName)
    {
        $event   = $context->getCurrentEvent();

        if ($event instanceof Event\DocumentEvent) {
            $doc  = $event->getData();
        } elseif ($event instanceof GenericEvent) {
            $doc = $event->getSubject();
        } else {
            throw $this->createRequiredException(
                'Unable to archive, document required but not provided (event: %s)',
                get_class($event)
            );
        }


        $context = new Context();
        // @todo replace setCurrentEventVariables by basic variable without event
        $context->setCurrentEventVariables($event, $eventName);

        $this->getActionService()->executeSequence($this->getActionSequence($eventName), $context);
    }
}

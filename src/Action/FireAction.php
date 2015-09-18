<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Velocity\Bundle\ApiBundle\Action;

use Velocity\Bundle\ApiBundle\Bag;
use Velocity\Bundle\ApiBundle\Event\FireEvent;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class FireAction extends AbstractAction
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->setEventDispatcher($eventDispatcher);
    }
    /**
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("fire", description="dispatch the event in the platform")
     */
    public function fire(Bag $params, Bag $context)
    {
        unset($params);

        $this->dispatch('fire', new FireEvent($context->get('eventName'), $context->get('event')));
    }
}

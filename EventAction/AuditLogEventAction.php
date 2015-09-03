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

use Velocity\Bundle\ApiBundle\Event\AuditLogEvent;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Traits\TokenStorageAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Velocity\Bundle\ApiBundle\EventAction\Base\AbstractEventAction;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Olivier Hoareau <olivier@tomboladirecte.com>
 */
class AuditLogEventAction extends AbstractEventAction
{
    use TokenStorageAwareTrait;
    /**
     * @param TokenStorageInterface    $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(TokenStorageInterface $tokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->setTokenStorage($tokenStorage);
        $this->setEventDispatcher($eventDispatcher);
    }
    /**
     * @Velocity\EventAction("audit_log", ignoreOnException=true)
     */
    public function auditLog()
    {
        $context   = $this->getContext();
        $eventName = $context->getVariable('type', $context->getCurrentEventName());
        $params    = $context->getVariable('params', []);
        $eventData = [] + (is_array($params) ? $params : []);
        $date      = new \DateTime();
        $token     = $this->getTokenStorage()->getToken();
        $user      = null === $token ? null : $token->getUser();

        $this->dispatch('audit.log', new AuditLogEvent($eventName, $eventData, $date, $user));
    }
}

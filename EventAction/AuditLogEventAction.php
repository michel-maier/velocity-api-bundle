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
     * @Velocity\EventAction("audit_log", ignoreOnException=true, description="add an audit log")
     */
    public function auditLog()
    {
        $context     = $this->getContext();
        $type        = $context->getVariable('type', $context->getCurrentEventName());
        $contextType = $context->getVariable('contextType');
        $contextId   = $context->getVariable('contextId');
        $date        = new \DateTime();
        $params      = $context->getVariable('params', []);
        $data        = [] + (is_array($params) ? $params : []);
        $token       = $this->getTokenStorage()->getToken();
        $user        = null === $token ? null : $token->getUser();
        $userId      = null !== $user ? $user->getId() : null;

        $this->dispatch('audit.log', new AuditLogEvent($type, $contextType, $contextId, $userId, $date, $data));
    }
}

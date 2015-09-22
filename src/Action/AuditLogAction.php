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
use Velocity\Bundle\ApiBundle\Event\AuditLogEvent;
use Velocity\Bundle\ApiBundle\Annotation as Velocity;
use Velocity\Bundle\ApiBundle\Action\Base\AbstractAction;
use Velocity\Bundle\ApiBundle\Traits\TokenStorageAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class AuditLogAction extends AbstractAction
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
     * @param Bag $params
     * @param Bag $context
     *
     * @Velocity\Action("audit_log", ignoreOnException=true, description="add an audit log")
     */
    public function auditLog(Bag $params, Bag $context)
    {
        $type        = $params->get('type', $context->get('eventName', null));
        $contextType = $params->get('contextType', null);
        $contextId   = $params->get('contextId', null);
        $date        = new \DateTime();
        $params      = $params->get('params', []);
        $data        = [] + (is_array($params) ? $params : []);
        $token       = $this->getTokenStorage()->getToken();
        $user        = null === $token ? null : $token->getUser();
        $userId      = null !== $user && method_exists($user, 'getId') ? $user->getId() : null;

        $this->dispatch('audit.log', new AuditLogEvent($type, $contextType, $contextId, $userId, $date, $data));
    }
}

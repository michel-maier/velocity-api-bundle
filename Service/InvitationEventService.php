<?php

/*
 * This file is part of the VELOCITY package.
 *
 * (c) PHPPRO <opensource@phppro.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source id.
 */

namespace Velocity\Bundle\ApiBundle\Service;

use Velocity\Bundle\ApiBundle\Traits\ServiceAware;
use Velocity\Bundle\ApiBundle\Traits\ServiceTrait;

/**
 * Invitation Event Service.
 *
 * @author Olivier Hoareau <olivier@phppro.fr>
 */
class InvitationEventService
{
    use ServiceTrait;
    /**
     * List of invitation events.
     *
     * @var callable[]
     */
    protected $invitationEvents = ['types' => []];
    /**
     * Return the list of registered invitation events.
     *
     * @return callable[]
     */
    public function getInvitationEvents()
    {
        return $this->invitationEvents;
    }
    /**
     * @param string $type
     *
     * @return array
     */
    public function getTypeInvitationEvents($type)
    {
        return isset($this->invitationEvents['types'][$type]) ? $this->invitationEvents['types'][$type] : [];
    }
    /**
     * @param string $type
     * @param string $transition
     *
     * @return array
     */
    public function getTypeTransitionInvitationEvents($type, $transition)
    {
        return isset($this->invitationEvents['types'][$type][$transition]) ? $this->invitationEvents['types'][$type][$transition]  :[];
    }
    /**
     * Register an invitation event for the specified type transition.
     *
     * @param string   $type
     * @param string   $transition
     * @param callable $callable
     * @param array    $params
     *
     * @return $this
     */
    public function addInvitationEvent($type = '*', $transition = '*', $callable = null, array $params = [])
    {
        if (!is_callable($callable)) {
            throw $this->createException(500, 'Registered invitation event must be a callable for');
        }

        if (!isset($this->invitationEvents['types'][$type])) {
            $this->invitationEvents['types'][$type] = [];
        }
        if (!isset($this->invitationEvents['types'][$type][$transition])) {
            $this->invitationEvents['types'][$type][$transition] = [];
        }
        $this->invitationEvents['types'][$type][$transition][] = ['callable' => $callable, 'params' => $params];

        return $this;
    }
    /**
     * @param string $type
     * @param string $transition
     * @param mixed  $invitation
     * @param array  $options
     *
     * @return $this
     */
    public function executeInvitationEventsForTypeTransition($type, $transition, $invitation, array $options = [])
    {
        $result = null;

        foreach ($this->getTypeTransitionInvitationEvents('*', '*') as $invitationEvent) {
            $result = $this->executeInvitationEventForModelOperation($type, $transition, $invitationEvent, $invitation, $options);
        }

        foreach ($this->getTypeTransitionInvitationEvents('*', $transition) as $invitationEvent) {
            $result = $this->executeInvitationEventForModelOperation($type, $transition, $invitationEvent, $invitation, $options);
        }

        foreach ($this->getTypeTransitionInvitationEvents($type, '*') as $invitationEvent) {
            $result = $this->executeInvitationEventForModelOperation($type, $transition, $invitationEvent, $invitation, $options);
        }

        foreach ($this->getTypeTransitionInvitationEvents($type, $transition) as $invitationEvent) {
            $result = $this->executeInvitationEventForModelOperation($type, $transition, $invitationEvent, $invitation, $options);
        }

        return $result;
    }
    /**
     * @param string $type
     * @param string $transition
     * @param array  $invitationEvent
     * @param mixed  $invitation
     * @param array  $options
     *
     * @return $this
     */
    protected function executeInvitationEventForModelOperation($type, $transition, array $invitationEvent, $invitation, array $options = [])
    {
        return call_user_func_array($invitationEvent['callable'], [$invitation, $transition, $type, $invitationEvent['params'], $options]);
    }
}
